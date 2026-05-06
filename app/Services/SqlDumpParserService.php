<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SqlDumpParserService
{
    protected $filePath;
    protected $handle;
    protected $storage;

    public function __construct(MigrationStorageService $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Set the SQL file path
     */
    public function setFile(string $path)
    {
        if (!file_exists($path)) {
            throw new \Exception("File SQL tidak ditemukan: $path");
        }
        $this->filePath = $path;
    }

    /**
     * Index the whole SQL file into SQLite for high-integrity ETL
     */
    public function indexFile()
    {
        if (!$this->filePath) return;

        $this->storage->clear();
        $this->handle = fopen($this->filePath, 'r');
        
        $buffer = '';
        $inInsert = false;
        $currentTable = '';
        $currentColumns = [];

        while (($line = fgets($this->handle)) !== false) {
            $trimmedLine = trim($line);
            if (empty($trimmedLine) || str_starts_with($trimmedLine, '--') || str_starts_with($trimmedLine, '/*')) {
                continue;
            }

            if (!$inInsert) {
                // Look for INSERT INTO [table] (cols) VALUES
                if (preg_match('/INSERT INTO [\"\'`]?(?<table>[a-zA-Z0-9_]+)[\"\'`]?\s*(?:\((?<cols>[^)]+)\))?\s*VALUES/i', $line, $matches)) {
                    $currentTable = $matches['table'];
                    $colString = $matches['cols'] ?? '';
                    $currentColumns = $colString ? array_map(fn($c) => trim($c, " \"'`"), explode(',', $colString)) : [];
                    
                    $buffer = $line;
                    $inInsert = true;
                }
            } else {
                $buffer .= $line;
            }

            if ($inInsert && str_contains($line, ';')) {
                $trimmed = rtrim(trim($buffer));
                if (str_ends_with($trimmed, ';')) {
                    if ($this->isStatementComplete($buffer)) {
                        $this->processInsertBlock($currentTable, $currentColumns, $buffer);
                        $buffer = '';
                        $inInsert = false;
                    }
                }
            }
        }

        fclose($this->handle);
    }

    /**
     * Process a full INSERT statement and load into SQLite
     */
    protected function processInsertBlock($tableName, $columns, $buffer)
    {
        $valuesIndex = stripos($buffer, 'VALUES');
        if ($valuesIndex === false) return;

        $valuesPart = substr($buffer, $valuesIndex + 6);
        $valuesPart = rtrim(trim($valuesPart), ';');
        
        $rows = $this->parseValues($valuesPart);
        if (empty($rows)) return;

        // If we don't have column names, create generic ones col_0, col_1...
        $detectedColumns = $columns;
        if (empty($detectedColumns)) {
            $colCount = count($rows[0]);
            for ($i = 0; $i < $colCount; $i++) {
                $detectedColumns[] = "col_$i";
            }
        }

        $this->storage->ensureTable($tableName, $detectedColumns);

        // Prepare rows for SQLite bulk insert
        $dataToInsert = [];
        foreach ($rows as $row) {
            $item = [];
            foreach ($detectedColumns as $idx => $colName) {
                // Ensure we don't overflow if row is shorter than columns
                $item[$colName] = $row[$idx] ?? null;
            }
            $dataToInsert[] = $item;
        }

        $this->storage->bulkInsert($tableName, $dataToInsert);
    }


    /**
     * Parse the (val1, val2), (val3, val4) string into arrays
     */
    protected function parseValues(string $valuesString)
    {
        $rows = [];
        $currentPos = 0;
        $length = strlen($valuesString);
        
        while ($currentPos < $length) {
            $start = strpos($valuesString, '(', $currentPos);
            if ($start === false) break;
            
            $end = $this->findClosingParenthesis($valuesString, $start);
            if ($end === false) break;
            
            $rowString = substr($valuesString, $start + 1, $end - $start - 1);
            $rows[] = $this->splitColumns($rowString);
            
            $currentPos = $end + 1;
        }
        
        return $rows;
    }

    /**
     * Find the closing parenthesis while respecting strings and escapes
     */
    protected function findClosingParenthesis(string $str, int $start)
    {
        $len = strlen($str);
        $inString = false;
        $quoteChar = '';
        $escaped = false;

        for ($i = $start + 1; $i < $len; $i++) {
            $char = $str[$i];
            if ($escaped) {
                $escaped = false;
                continue;
            }
            if ($char === '\\') {
                $escaped = true;
                continue;
            }
            if (($char === "'" || $char === '"') && !$inString) {
                $inString = true;
                $quoteChar = $char;
            } elseif ($char === $quoteChar && $inString) {
                $inString = false;
            }
            if (!$inString && $char === ')') {
                return $i;
            }
        }
        return false;
    }

    /**
     * Split a row string into columns
     */
    protected function splitColumns(string $rowStr)
    {
        $cols = [];
        $len = strlen($rowStr);
        $current = '';
        $inString = false;
        $quoteChar = '';
        $escaped = false;

        for ($i = 0; $i < $len; $i++) {
            $char = $rowStr[$i];
            if ($escaped) {
                $current .= $char;
                $escaped = false;
                continue;
            }
            if ($char === '\\') {
                $escaped = true;
                continue;
            }
            if (($char === "'" || $char === '"') && !$inString) {
                $inString = true;
                $quoteChar = $char;
                continue;
            } elseif ($char === $quoteChar && $inString) {
                $inString = false;
                continue;
            }
            if (!$inString && $char === ',') {
                $cols[] = $this->cleanValue($current);
                $current = '';
                continue;
            }
            $current .= $char;
        }
        $cols[] = $this->cleanValue($current);
        return $cols;
    }

    /**
     * Clean up parsed value
     */
    protected function cleanValue(string $val)
    {
        $val = trim($val);
        if (strtoupper($val) === 'NULL') return null;
        
        // Remove surrounding quotes if any
        if ((str_starts_with($val, "'") && str_ends_with($val, "'")) || 
            (str_starts_with($val, '"') && str_ends_with($val, '"'))) {
            $val = substr($val, 1, -1);
        }
        
        return stripcslashes($val);
    }

    /**
     * Check if an INSERT statement is complete
     */
    protected function isStatementComplete(string $buffer)
    {
        $inString = false;
        $quoteChar = '';
        $escaped = false;
        $len = strlen($buffer);

        for ($i = 0; $i < $len; $i++) {
            $char = $buffer[$i];
            if ($escaped) {
                $escaped = false;
                continue;
            }
            if ($char === '\\') {
                $escaped = true;
                continue;
            }
            if (($char === "'" || $char === '"') && !$inString) {
                $inString = true;
                $quoteChar = $char;
            } elseif ($char === $quoteChar && $inString) {
                $inString = false;
            }
        }
        return !$inString;
    }
}
