<?php

namespace App\Http\Controllers\Admin;

use App\Models\HomeAds;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
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
    public function update(HomeAdsRequest $request, HomeAds $homeAds)
    {
        $data = $request->validated();
        // deteksi media type apakah 'image' atau 'video'
        $data['media_type'] = $request->hasFile('media') ?
            ($request->file('media')->getClientMimeType() === 'video/mp4' ? 'video' : 'image') : $homeAds->media_type;
        // simpan data iklan
        if ($request->hasFile('media')) {
            if ($homeAds->media_url && file_exists($homeAds->media_url)) {
                unlink($homeAds->media_url);
            }
            $data['media_url'] = 'storage/' . $request->file('media')->store('home_ads', ['disk' => 'public']);
        }
        $homeAds->update($data);
        return redirect()->route('home-ads.index')->with('success', 'Iklan Berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HomeAds $homeAds)
    {
        if ($homeAds->media_url && file_exists($homeAds->media_url)) {
            unlink($homeAds->media_url);
        }
        $homeAds->delete();
        return redirect()->route('home-ads.index')->with('success', 'Iklan Berhasil dihapus');
    }
}
