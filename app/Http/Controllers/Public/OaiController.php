<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Journal;
use App\Models\Submission;
use Carbon\Carbon;

class OaiController extends Controller
{
    public function handle(Request $request)
    {
        try {
            // 1. Resolve Journal (From Route Parameter)
            $journalPath = $request->route('journal');
            $journal = Journal::where('slug', $journalPath)
                ->orWhere('path', $journalPath)
                ->firstOrFail();
            
            // 1.2. Cek OAI Enabled
            if (!$journal->enable_oai) {
                return $this->oaiError('noRecordsMatch', 'OAI-PMH is not enabled for this journal.');
            }
            
            // 1.5 Validasi Verb (dan Landing Page)
            if (!$request->has('verb')) {
                if (empty($request->query())) {
                    return view('journal.oai.landing');
                }
                return $this->oaiError('badVerb', 'Missing OAI verb');
            }

            $verb = $request->input('verb');
            $validVerbs = ['Identify', 'ListRecords', 'ListSets', 'ListMetadataFormats', 'ListIdentifiers', 'GetRecord'];
            if (!in_array($verb, $validVerbs)) {
                return $this->oaiError('badVerb', 'Illegal OAI verb');
            }
            
            // Check for illegal parameters
            $allowedKeys = ['verb', 'identifier', 'metadataPrefix', 'from', 'until', 'set', 'resumptionToken'];
            foreach ($request->query() as $key => $value) {
                if (!in_array($key, $allowedKeys)) {
                    return $this->oaiError('badArgument', 'Illegal parameter: ' . $key);
                }
            }

        // 4. Resumption Token — exclusive argument check
        if ($request->has('resumptionToken')) {
            $paramCount = count($request->query());
            if ($paramCount > 2) {
                return $this->oaiError('badArgument', 'resumptionToken is an exclusive argument');
            }
            // Decode and validate token: base64(json{verb,cursor,from,until,set,journalId})
            $tokenData = $this->decodeResumptionToken($request->input('resumptionToken'));
            if (!$tokenData) {
                return $this->oaiError('badResumptionToken', 'Invalid or expired resumptionToken');
            }
            // Re-dispatch with token data
            return $this->handleResumptionToken($request, $journal, $tokenData);
        }

        // 5. Validasi MetadataPrefix
        $supportedFormats = ['oai_dc', 'marc21', 'rfc1807'];
        if (in_array($verb, ['ListRecords', 'ListIdentifiers', 'GetRecord'])) {
            if (!$request->has('metadataPrefix')) {
                return $this->oaiError('badArgument', 'Missing metadataPrefix');
            }
            if (!in_array($request->input('metadataPrefix'), $supportedFormats)) {
                return $this->oaiError('cannotDisseminateFormat', 'Supported formats: ' . implode(', ', $supportedFormats));
            }
        }

       // 6. Validasi Tanggal (from & until)
        if ($request->has('from') || $request->has('until')) {
            $from = $request->input('from');
            $until = $request->input('until');

            // PERBAIKAN: Cek Granularity Mismatch
            // Jika panjang string beda jauh (misal 10 karakter vs 20 karakter), return error
            if ($from && $until && abs(strlen($from) - strlen($until)) > 5) {
                return $this->oaiError('badArgument', 'Granularity mismatch between from and until');
            }

            try {
                if ($from) Carbon::parse($from);
                if ($until) Carbon::parse($until);
            } catch (\Exception $e) {
                return $this->oaiError('badArgument', 'Invalid date format');
            }
        }

        // 7. Validasi Identifier
        if (in_array($verb, ['GetRecord', 'ListMetadataFormats'])) {
            if ($verb === 'GetRecord' && !$request->has('identifier')) {
                return $this->oaiError('badArgument', 'Missing identifier');
            }
        }

        // ===============================================
        // DISPATCHER
        // ===============================================
        switch ($verb) {
            case 'Identify':
                $earliestDate = Submission::where('journal_id', $journal->id)
                    ->where('status', Submission::STATUS_PUBLISHED)
                    ->min('published_at'); // Use published_at, not updated_at
                
                $earliestDate = $earliestDate
                    ? \Carbon\Carbon::parse($earliestDate)
                    : now();

                $xmlContent = view('journal.public.oai.identify', [
                    'journal' => $journal,
                    'earliestDate' => $earliestDate->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z'),
                    'baseUrl' => route('journal.oai', $journal->slug)
                ])->render();

                return \Illuminate\Support\Facades\Response::make($xmlContent, 200, [
                    'Content-Type' => 'text/xml'
                ]);

            case 'ListMetadataFormats':
                if ($request->has('identifier')) {
                     $id = $this->extractId($request->input('identifier'));
                     if (!$id) {
                         return $this->oaiError('idDoesNotExist', 'Invalid identifier format');
                     }
                     $exists = Submission::where('journal_id', $journal->id)
                        ->where(function($q) use ($id) {
                            if (is_numeric($id)) {
                                $q->where('seq_id', $id);
                            } elseif (\Illuminate\Support\Str::isUuid($id)) {
                                $q->where('id', $id);
                            } else {
                                $q->where('slug', $id);
                            }
                        })->exists();

                     if (!$exists) {
                         return $this->oaiError('idDoesNotExist', 'The specified identifier does not exist');
                     }
                }
                return response()->view('journal.public.oai.metadata_formats', compact('journal'))
                    ->header('Content-Type', 'text/xml');

            case 'ListSets':
                 $sets = [
                    (object)['spec' => strtoupper($journal->abbreviation ?? 'JRN'), 'name' => $journal->name],
                    (object)['spec' => strtoupper($journal->abbreviation ?? 'JRN') . ':ART', 'name' => 'Articles']
                ];
                return response()->view('journal.public.oai.list_sets', compact('journal', 'sets'))
                    ->header('Content-Type', 'text/xml');

            case 'ListIdentifiers':
            case 'ListRecords':
                $query = Submission::where('journal_id', $journal->id)
                    ->where('status', Submission::STATUS_PUBLISHED);
                
                // Date range filtering (OAI-PMH 2.0 inclusive)
                if ($request->has('from')) {
                    $from = Carbon::parse($request->input('from'))->utc();
                    $query->where('updated_at', '>=', $from);
                }
                if ($request->has('until')) {
                    $until = Carbon::parse($request->input('until'))->utc()->endOfSecond();
                    $query->where('updated_at', '<=', $until);
                }

                // Set filtering
                if ($request->has('set')) {
                    $set = $request->input('set');
                    $journalAbbr = strtoupper($journal->abbreviation ?? 'JRN');
                    if ($set !== $journalAbbr && $set !== $journalAbbr . ':ART') {
                        $query->whereRaw('1 = 0');
                    }
                }

                $query->with(['currentPublication', 'authors', 'keywords', 'issue', 'journal', 'galleys']);

                // Pagination with resumption tokens (OAI-PMH 2.0 compliance)
                $pageSize    = 100;
                $cursor      = 0;
                $totalRecords = $query->count();
                $records     = $query->orderBy('updated_at', 'desc')
                                     ->skip($cursor)
                                     ->take($pageSize)
                                     ->get();

                if ($records->isEmpty()) {
                    return $this->oaiError('noRecordsMatch', 'No records found');
                }

                // Build resumption token if there are more records
                $resumptionToken = null;
                if ($totalRecords > $pageSize) {
                    $resumptionToken = $this->buildResumptionToken([
                        'verb'     => $verb,
                        'cursor'   => $pageSize,
                        'from'     => $request->input('from'),
                        'until'    => $request->input('until'),
                        'set'      => $request->input('set'),
                        'journal'  => $journal->slug,
                    ]);
                }

                $view = ($verb === 'ListIdentifiers')
                    ? 'journal.public.oai.list_identifiers'
                    : 'journal.public.oai.list_records';

                $metadataPrefix = $request->input('metadataPrefix', 'oai_dc');

                return response()->view($view, compact('records', 'journal', 'verb', 'resumptionToken', 'totalRecords', 'cursor', 'metadataPrefix'))
                    ->header('Content-Type', 'text/xml; charset=UTF-8');

            case 'GetRecord':
                $id = $this->extractId($request->input('identifier'));
                if (!$id) {
                     return $this->oaiError('idDoesNotExist', 'Invalid identifier format');
                }

                $recordRaw = Submission::where('journal_id', $journal->id)
                    ->where(function($q) use ($id) {
                        if (is_numeric($id)) {
                            $q->where('seq_id', $id);
                        } elseif (\Illuminate\Support\Str::isUuid($id)) {
                            $q->where('id', $id);
                        } else {
                            $q->where('slug', $id);
                        }
                    })
                    ->where('status', Submission::STATUS_PUBLISHED)
                    ->with(['currentPublication', 'authors', 'keywords', 'issue', 'journal', 'galleys'])
                    ->first();

                if (!$recordRaw) {
                    return $this->oaiError('idDoesNotExist', 'Identifier not found');
                }
                
                $record = $recordRaw;
                $metadataPrefix = $request->input('metadataPrefix', 'oai_dc');

                return response()->view('journal.public.oai.get_record', compact('record', 'journal', 'metadataPrefix'))
                    ->header('Content-Type', 'text/xml; charset=UTF-8');
        }
        
        } catch (\Exception $e) {
            return $this->oaiError('badArgument', 'Invalid OAI-PMH request: ' . $e->getMessage());
        }
    }

    // --- HELPER METHODS ---

    private function extractId($oaiString) {
        if (!$oaiString) return null;
        $parts = explode('/', $oaiString);
        return end($parts);
    }

    /**
     * Build a resumption token (base64-encoded JSON payload, expires in 24h).
     */
    private function buildResumptionToken(array $data): string
    {
        $data['expires'] = now()->addHours(24)->timestamp;
        return base64_encode(json_encode($data));
    }

    /**
     * Decode and validate a resumption token. Returns null if invalid or expired.
     */
    private function decodeResumptionToken(string $token): ?array
    {
        $decoded = base64_decode($token, true);
        if (!$decoded) return null;
        $data = json_decode($decoded, true);
        if (!is_array($data)) return null;
        if (isset($data['expires']) && $data['expires'] < now()->timestamp) {
            return null;
        }
        return $data;
    }

    /**
     * Handle ListRecords/ListIdentifiers continuation via resumption token.
     */
    private function handleResumptionToken(Request $request, $journal, array $tokenData)
    {
        $verb   = $tokenData['verb'] ?? 'ListRecords';
        $cursor = (int) ($tokenData['cursor'] ?? 0);

        $query = Submission::where('journal_id', $journal->id)
            ->where('status', Submission::STATUS_PUBLISHED);

        if (!empty($tokenData['from'])) {
            $query->where('updated_at', '>=', Carbon::parse($tokenData['from'])->utc());
        }
        if (!empty($tokenData['until'])) {
            $query->where('updated_at', '<=', Carbon::parse($tokenData['until'])->utc()->endOfSecond());
        }
        if (!empty($tokenData['set'])) {
            $journalAbbr = strtoupper($journal->abbreviation ?? 'JRN');
            $set = $tokenData['set'];
            if ($set !== $journalAbbr && $set !== $journalAbbr . ':ART') {
                $query->whereRaw('1 = 0');
            }
        }

        $query->with(['currentPublication', 'authors', 'keywords', 'issue', 'journal', 'galleys']);

        $pageSize     = 100;
        $totalRecords = $query->count();
        $records      = $query->orderBy('updated_at', 'desc')->skip($cursor)->take($pageSize)->get();

        if ($records->isEmpty()) {
            return $this->oaiError('noRecordsMatch', 'No records found');
        }

        $nextCursor      = $cursor + $pageSize;
        $resumptionToken = null;
        if ($nextCursor < $totalRecords) {
            $resumptionToken = $this->buildResumptionToken(array_merge($tokenData, ['cursor' => $nextCursor]));
        }

        $view = ($verb === 'ListIdentifiers')
            ? 'journal.public.oai.list_identifiers'
            : 'journal.public.oai.list_records';

        return response()->view($view, compact('records', 'journal', 'verb', 'resumptionToken', 'totalRecords', 'cursor'))
            ->header('Content-Type', 'text/xml; charset=UTF-8');
    }

    public static function getRequestAttributes()
    {
        $allowedKeys = ['verb', 'identifier', 'metadataPrefix', 'from', 'until', 'set', 'resumptionToken'];
        $requestAttributes = '';
        foreach (request()->only($allowedKeys) as $key => $value) {
            if ($value !== null && $value !== '') {
                $requestAttributes .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
            }
        }
        return $requestAttributes;
    }

   private function oaiError($code, $message)
    {
        $baseUrl = url()->current(); 
        $requestAttributes = self::getRequestAttributes();

        // XML String Manual
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                 xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
            <responseDate>' . now()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') . '</responseDate>
            <request' . $requestAttributes . '>' . htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . '</request>
            <error code="' . $code . '">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</error>
        </OAI-PMH>';

        return response($xml, 200)->header('Content-Type', 'text/xml; charset=UTF-8');
    }
}
