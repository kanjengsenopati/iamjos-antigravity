<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SqlDumpParserService
{
    protected $filePath;
    protected $handle;

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
     * Get data for a specific table
     * Returns a Generator for memory efficiency
     */
    public function getTableData(string $tableName)
    {
        if (!$this->filePath) {
            throw new \Exception("File belum diset.");
        }

        $this->handle = fopen($this->filePath, 'r');
        // Support both: 
        // INSERT INTO table VALUES ...
        // INSERT INTO table (col1, col2) VALUES ...
        $pattern = "/INSERT INTO [\"'`]?" . preg_quote($tableName, '/') . "[\"'`]?.*VALUES/i";
        
        $buffer = '';
        $inInsert = false;

        while (($line = fgets($this->handle)) !== false) {
            if (!$inInsert) {
                if (preg_match($pattern, $line)) {
                    $buffer = $line;
                    $inInsert = true;
                }
            } else {
                $buffer .= $line;
            }
            
            if ($inInsert) {
                $trimmed = rtrim($buffer);
                if (substr($trimmed, -1) === ';') {
                    if ($this->isStatementComplete($buffer)) {
                        $valuesIndex = stripos($buffer, 'VALUES');
                        $valuesPart = substr($buffer, $valuesIndex + 6);
                        $valuesPart = rtrim(trim($valuesPart), ';');
                        
                        $rows = $this->parseValues($valuesPart);
                        foreach ($rows as $row) {
                            yield $row;
                        }
                        
                        $buffer = '';
                        $inInsert = false;
                    }
                }
            }
        }

        fclose($this->handle);
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
     * Clean up parsed value (convert NULL, numbers, etc)
     */
    protected function cleanValue(string $val)
    {
        $val = trim($val);
        if (strtoupper($val) === 'NULL') return null;
        if (is_numeric($val)) {
            return strpos($val, '.') !== false ? (float)$val : (int)$val;
        }
        return stripcslashes($val);
    }

    /**
     * Check if an INSERT statement is complete (balanced quotes)
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
