<?php

namespace App\Http\Controllers\Admin;

use App\Models\HomeMember;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomeMemberRequest;

class HomeMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = HomeMember::latest();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionEdit = route('home-member.edit', $data->id);
                    $actionDelete = route('home-member.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.home-member.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.home-member.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HomeMemberRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = 'storage/' . $request->file('image')->store('home-members', ['disk' => 'public']);
        }
        HomeMember::create($data);
        return redirect()->route('home-member.index')->with('success', 'Keanggotan berhasil ditambahkan.');
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
    public function edit(HomeMember $homeMember)
    {
        return view('admins.home-member.create-edit', compact('homeMember'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HomeMemberRequest $request, HomeMember $homeMember)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if (file_exists($homeMember->image)) {
                unlink($homeMember->image);
            }
            $data['image'] = 'storage/' . $request->file('image')->store('home-members', ['disk' => 'public']);
        }
        $homeMember->update($data);
        return redirect()->route('home-member.index')->with('success', 'Keanggotan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HomeMember $homeMember)
    {
        // Delete image if exists
        if (file_exists($homeMember->image)) {
            unlink($homeMember->image);
        }
        $homeMember->delete();
        return redirect()->route('home-member.index')->with('success', 'Keanggotan berhasil dihapus.');
    }
}
