<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\HomeAds;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\HomeAdsRequest;

class HomeAdsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = HomeAds::latest();
            return DataTables::of($data)
                ->addColumn('date', function ($d) {
                    $parse = function ($v) {
                        if (!$v) return null;
                        return $v instanceof CarbonInterface ? $v->copy() : Carbon::parse($v);
                    };

                    $s = $parse($d->start_date);
                    $e = $parse($d->end_date);

                    $full  = fn(CarbonInterface $c) => $c->locale('id')->isoFormat('D MMMM YYYY');
                    $dOnly = fn(CarbonInterface $c) => $c->locale('id')->isoFormat('D');
                    $dm    = fn(CarbonInterface $c) => $c->locale('id')->isoFormat('D MMMM');

                    if ($s && $e) {
                        if ($s->isSameDay($e)) {
                            // 1 Agustus 2025
                            return $full($s);
                        }
                        if ($s->isSameMonth($e) && $s->isSameYear($e)) {
                            // 1–31 Agustus 2025
                            return $dOnly($s) . '–' . $full($e);
                        }
                        if ($s->isSameYear($e)) {
                            // 28 Agustus – 2 September 2025
                            return $dm($s) . ' – ' . $full($e);
                        }
                        // 28 Desember 2024 – 3 Januari 2025
                        return $full($s) . ' – ' . $full($e);
                    }

                    // Salah siji kosong
                    if ($s) return $full($s);
                    if ($e) return $full($e);

                    return '–';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('home-ads.edit', $data->id);
                    $actionDelete = route('home-ads.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.home-ads.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.home-ads.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HomeAdsRequest $request)
    {
        $data = $request->validated();
        // deteksi media type apakah 'image' atau 'video'
        $data['media_type'] = $request->hasFile('media') ?
            ($request->file('media')->getClientMimeType() === 'video/mp4' ? 'video' : 'image') : 'image';
        // simpan data iklan
        if ($request->hasFile('media')) {
            $data['media_url'] = 'storage/' . $request->file('media')->store('home_ads', ['disk' => 'public']);
        }
        HomeAds::create($data);
        return redirect()->route('home-ads.index')->with('success', 'Iklan Berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $homeAds = HomeAds::findOrFail($id);
        return view('admins.home-ads.create-edit', compact('homeAds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HomeAdsRequest $request, $id)
    {
        $homeAds = HomeAds::findOrFail($id);
        $data = $request->validated();
        $file = $request->file('media');

        if ($file) {
            // Deteksi tipe media secara generik
            $mime = $file->getClientMimeType() ?: $file->getMimeType();
            $data['media_type'] = Str::startsWith($mime, 'video/') ? 'video' : 'image';

            // Simpan file BARU terlebih dulu
            $newPath = $file->store('home_ads', 'public');     // ex: home_ads/abc.jpg
            $data['media_url'] = Storage::url($newPath);       // ex: /storage/home_ads/abc.jpg

            // Simpan untuk dihapus setelah update sukses
            $oldUrl = $homeAds->media_url;

            // Update model + data baru
            $homeAds->fill($data)->save();

            // Bersihkan file lama (best-effort)
            if ($oldUrl) {
                // Normalisasi ke path di disk 'public'
                $oldPath = Str::of(parse_url($oldUrl, PHP_URL_PATH) ?? '')
                    ->after('/storage/')
                    ->value();

                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        } else {
            // Tidak ada file baru: biarkan media_type & media_url tetap, update field lain saja
            unset($data['media_type'], $data['media_url']);
            $homeAds->update($data);
        }

        return redirect()
            ->route('home-ads.index')
            ->with('success', 'Iklan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $homeAds = HomeAds::findOrFail($id);
        if ($homeAds->media_url && file_exists($homeAds->media_url)) {
            unlink($homeAds->media_url);
        }
        $homeAds->delete();
        return redirect()->route('home-ads.index')->with('success', 'Iklan Berhasil dihapus');
    }
}
