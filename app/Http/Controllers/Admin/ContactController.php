<?php

namespace App\Http\Controllers\Admin;

use App\Models\Contact;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactRequest;
use App\Services\ImageService;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $court = Contact::latest();
            return DataTables::of($court)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('contact.edit', $data->id);
                    $actionDelete = route('contact.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.contact.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.contact.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContactRequest $request, ImageService $imageService)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            // Delete old image if exists
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'members'
            );

            $data['image'] = 'storage/' . $saved['path'];
        }
        Contact::create($data);
        return redirect()->route('contact.index')->with('success', 'Kontak berhasil ditambahkan.');
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
        $contact = Contact::findOrFail($id);
        return view('admins.contact.create-edit', compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContactRequest $request, string $id, ImageService $imageService)
    {
        $data = $request->validated();
        $contact = Contact::findOrFail($id);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if (file_exists($contact->image)) {
                unlink($contact->image);
            }
            $saved = $imageService->storeSingleWebp(
                file: $request->file('image'),
                maxWidth: null,
                quality: 50,
                disk: 'public',
                dir: 'members'
            );

            $data['image'] = 'storage/' . $saved['path'];
        }

        $contact->update($data);
        return redirect()->route('contact.index')->with('success', 'Kontak berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, ImageService $imageService)
    {
        $contact = Contact::findOrFail($id);
        if (file_exists($contact->image)) {
            unlink($contact->image);
        }
        $contact->delete();
        return redirect()->route('contact.index')->with('success', 'Kontak berhasil dihapus.');
    }
}
