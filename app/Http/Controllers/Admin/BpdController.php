<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\PhriContentService;


class BpdController extends Controller
{
    public function index(PhriContentService $svc)
    {
        $raw   = $svc->getBpd();
        $last  = $svc->lastFetchedAt();
        $items = array_values(Arr::get($raw, 'RECORDS', $raw)); // langsung array record

        return view('admins.bpd.index', compact('items', 'last'));
    }

    public function refresh(Request $request, PhriContentService $svc)
    {
        $svc->refreshBpd();
        return back()->with('status', 'Cache berhasil diperbarui.');
    }
}
