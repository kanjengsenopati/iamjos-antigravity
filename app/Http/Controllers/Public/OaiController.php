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
        // 1. Resolve Journal (From Route Parameter)
        $journalPath = $request->route('journal');
        $journal = Journal::where('slug', $journalPath)
            ->orWhere('path', $journalPath)
            ->firstOrFail();
        
        // 2. Ambil Parameter
        $verb = $request->input('verb');
        
        // 3. Validasi Verb
        $validVerbs = ['Identify', 'ListRecords', 'ListSets', 'ListMetadataFormats', 'ListIdentifiers', 'GetRecord'];
        if (!$verb || !in_array($verb, $validVerbs)) {
            return $this->oaiError('badVerb', 'Illegal OAI verb');
        }

        // 4. Cek Resumption Token (Eksklusivitas)
        if ($request->has('resumptionToken')) {
            // Count arguments. verb + resumptionToken = 2.
            $paramCount = count($request->query()); 
            if ($paramCount > 2) { 
                return $this->oaiError('badArgument', 'resumptionToken is an exclusive argument');
            }
            return $this->oaiError('badResumptionToken', 'Invalid resumptionToken');
        }

        // 5. Validasi MetadataPrefix
        if (in_array($verb, ['ListRecords', 'ListIdentifiers', 'GetRecord'])) {
            if (!$request->has('metadataPrefix')) {
                return $this->oaiError('badArgument', 'Missing metadataPrefix');
            }
            if ($request->input('metadataPrefix') !== 'oai_dc') {
                return $this->oaiError('cannotDisseminateFormat', 'Only oai_dc is supported');
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
                    ->min('updated_at'); 
                
                $earliestDate = $earliestDate ? Carbon::parse($earliestDate) : now();

                return response()->view('journal.public.oai.identify', [
                    'journal' => $journal,
                    'earliestDate' => $earliestDate->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z'),
                    'baseUrl' => route('journal.oai', $journal->slug)
                ])->header('Content-Type', 'text/xml');

            case 'ListMetadataFormats':
                if ($request->has('identifier')) {
                     $id = $this->extractId($request->input('identifier'));
                     if (!$id) {
                         return $this->oaiError('idDoesNotExist', 'Invalid identifier format');
                     }
                     $exists = Submission::where('journal_id', $journal->id)
                        ->where(function($q) use ($id) {
                            $q->where('id', $id)->orWhere('slug', $id);
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
                
                // Filter Tanggal
                if ($request->has('from')) {
                    $query->where('updated_at', '>=', Carbon::parse($request->input('from')));
                }
                if ($request->has('until')) {
                    // PERBAIKAN: Gunakan endOfSecond()
                    // Ini memaksa waktu menjadi .999999 agar mencakup seluruh milidetik di database
                    $dateUntil = Carbon::parse($request->input('until'));
                    
                    // Cek jika formatnya tanggal saja (YYYY-MM-DD), ambil sampai akhir hari
                    if (strlen($request->input('until')) <= 10) {
                        $dateUntil->endOfDay();
                    } else {
                        // Jika format lengkap jam/menit, ambil sampai akhir detik
                        $dateUntil->endOfSecond(); 
                    }
                    $query->where('updated_at', '<=', $dateUntil);
                }
                
                // Eager Load
                $query->with(['publication', 'authors', 'issue', 'journal', 'galleys']);

                $records = $query->orderBy('updated_at', 'desc')->take(100)->get();

                if ($records->isEmpty()) {
                    return $this->oaiError('noRecordsMatch', 'No records found');
                }

                $view = ($verb === 'ListIdentifiers') ? 'journal.public.oai.list_identifiers' : 'journal.public.oai.list_records';
                return response()->view($view, compact('records', 'journal', 'verb'))
                    ->header('Content-Type', 'text/xml');

            case 'GetRecord':
                $id = $this->extractId($request->input('identifier'));
                if (!$id) {
                     return $this->oaiError('idDoesNotExist', 'Invalid identifier format');
                }

                $recordRaw = Submission::where('journal_id', $journal->id)
                    ->where(function($q) use ($id) {
                        $q->where('id', $id)->orWhere('slug', $id);
                    })
                    ->where('status', Submission::STATUS_PUBLISHED)
                    ->with(['publication', 'authors', 'issue', 'journal', 'galleys'])
                    ->first();

                if (!$recordRaw) {
                    return $this->oaiError('idDoesNotExist', 'Identifier not found');
                }
                
                $record = $recordRaw;

                return response()->view('journal.public.oai.get_record', compact('record', 'journal'))
                    ->header('Content-Type', 'text/xml');
        }
    }

    // --- HELPER METHODS ---

    private function extractId($oaiString) {
        if (!$oaiString) return null;
        $parts = explode('/', $oaiString);
        return end($parts);
    }

   private function oaiError($code, $message)
    {
        $baseUrl = url()->current(); 
        $requestAttributes = '';
        
        // PERBAIKAN: Tambahkan parameter ENT_QUOTES
        // Ini mengubah tanda kutip " menjadi &quot; agar XML tidak rusak
        
        $verb = request('verb');
        if ($verb) {
            $requestAttributes .= ' verb="' . htmlspecialchars($verb, ENT_QUOTES) . '"';
        }
        
        $identifier = request('identifier');
        if ($identifier) {
            $requestAttributes .= ' identifier="' . htmlspecialchars($identifier, ENT_QUOTES) . '"';
        }

        $metadataPrefix = request('metadataPrefix');
        if ($metadataPrefix) {
             $requestAttributes .= ' metadataPrefix="' . htmlspecialchars($metadataPrefix, ENT_QUOTES) . '"';
        }

        // XML String Manual
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                 xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
            <responseDate>' . now()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') . '</responseDate>
            <request' . $requestAttributes . '>' . htmlspecialchars($baseUrl) . '</request>
            <error code="' . $code . '">' . htmlspecialchars($message) . '</error>
        </OAI-PMH>';

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }
}
