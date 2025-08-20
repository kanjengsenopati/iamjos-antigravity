<?php

namespace App\Http\Controllers\Admin;

use App\Models\ContactUs;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ContactUsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $court = ContactUs::latest();
            return DataTables::of($court)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('contact-us.update', $data->id);
                    $actionDelete = route('contact-us.destroy', $data->id);
                    if (!$data->is_read) {
                        return "<div class='d-flex justify-content-center'>" .
                            view('components.action.confirm-contact', ['action' => $actionEdit]) .
                            "</div>";
                    } else {
                        return "<div class='d-flex justify-content-center'>" .
                            view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                            "</div>";
                    }
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.contact-us.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        try {
            $contactUs = ContactUs::findOrFail($id);
            $contactUs->update(['is_read' => true]);

            return redirect()->route('contact-us.index')
                ->with('success', 'Berhasil memperbarui data');
        } catch (\Throwable $e) {
            Log::error('Gagal update ContactUs: ' . $e->getMessage());

            return redirect()->route('contact-us.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui data');
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $contactUs = ContactUs::findOrFail($id);

        if (!$contactUs) {
            return redirect()->route('contact-us.index')->with('error', 'Data tidak ditemukan');
        }
        try {
            $contactUs->delete();
            return redirect()->route('contact-us.index')->with('success', 'Berhasil menghapus data');
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus ContactUs: ' . $e->getMessage());
            return redirect()->route('contact-us.index')->with('error', 'Terjadi kesalahan saat menghapus data');
        }
    }
}
