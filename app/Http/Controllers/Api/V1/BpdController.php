<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\PhriContentService;
use Illuminate\Pagination\LengthAwarePaginator;

class BpdController extends Controller
{
    // public function index(PhriContentService $svc)
    // {
    //     $recordsOnly = Arr::get($svc->getBpd(), 'RECORDS', []);
    //     return $this->getSuccessResponse(array_values($recordsOnly));
    // }

    public function index(Request $request, PhriContentService $svc)
    {
        // Validasi & ambil query params
        $request->validate([
            'q'        => ['nullable', 'string'],          // alias: search
            'search'   => ['nullable', 'string'],
            'page'     => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort'     => ['nullable', 'in:publish_at,title,id,source'],
            'order'    => ['nullable', 'in:asc,desc'],
        ]);

        $q        = trim((string) ($request->input('q') ?? $request->input('search') ?? ''));
        $page     = (int) $request->input('page', 1);
        $perPage  = (int) $request->input('per_page', 20);
        $sort     = $request->input('sort', 'publish_at');
        $order    = $request->input('order', 'desc');

        // Ambil data mentah dari service
        $records = Arr::get($svc->getBpd(), 'RECORDS', []);
        if (!is_array($records)) {
            $records = [];
        }

        // --- FILTER: Search sederhana di beberapa field umum ---
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $records = array_values(array_filter($records, function ($row) use ($needle) {
                foreach (['nama', 'alamat', 'nama_ketua', 'nama_sekretaris', 'telp', 'email', 'provinsi', 'kota'] as $field) {
                    if (isset($row[$field]) && stripos((string) $row[$field], $needle) !== false) {
                        return true;
                    }
                }
                return false;
            }));
        }

        // --- SORT: default berdasarkan publish_at (tanggal) desc ---
        usort($records, function ($a, $b) use ($sort, $order) {
            $va = $a[$sort] ?? null;
            $vb = $b[$sort] ?? null;

            if ($sort === 'publish_at') {
                $ta = $va ? strtotime((string)$va) : 0;
                $tb = $vb ? strtotime((string)$vb) : 0;
                $cmp = $ta <=> $tb;
            } elseif (is_numeric($va) && is_numeric($vb)) {
                $cmp = $va <=> $vb;
            } else {
                $cmp = strnatcasecmp((string)$va, (string)$vb);
            }

            return $order === 'asc' ? $cmp : -$cmp;
        });

        // --- PAGINASI manual untuk array ---
        $total  = count($records);
        $offset = ($page - 1) * $perPage;
        $items  = array_slice($records, $offset, $perPage);

        // Opsi 1: respons kustom + meta (paling ringan untuk API)
        // return $this->getSuccessResponse([
        //     'data' => array_values($items),
        //     'meta' => [
        //         'page'       => $page,
        //         'per_page'   => $perPage,
        //         'total'      => $total,
        //         'last_page'  => (int) ceil(max($total, 1) / max($perPage, 1)),
        //         'has_more'   => ($offset + $perPage) < $total,
        //         'query'      => $q,
        //         'sort'       => $sort,
        //         'order'      => $order,
        //     ],
        // ]);

        // --- Jika Anda ingin format paginator Laravel standar (opsional) ---
        $paginator = new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path'  => $request->url(),
            'query' => $request->query(),
        ]);
        return $this->getSuccessResponse($paginator);
    }
}
