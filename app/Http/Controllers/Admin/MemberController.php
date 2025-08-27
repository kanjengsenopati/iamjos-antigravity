<?php

namespace App\Http\Controllers\Admin;

use App\Models\Member;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MemberRequest;
use App\Services\ImageService;
use App\Services\ExcelService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = Member::latest();
            return DataTables::of($data)
                ->addColumn('image', function ($data) {
                    if ($data->image) {
                        return "<img src='" . asset($data->image) . "' alt='Member' style='width: 50px; height: 50px; object-fit: cover;' class='rounded'>";
                    }
                    return '<span class="text-gray-500">No Image</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('member.edit', $data->id);
                    $actionDelete = route('member.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['image', 'action'])
                ->make(true);
        }
        return view('admins.member.index');
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'organization');
        return view('admins.member.create-edit', compact('type'));
    }

    public function store(MemberRequest $request, ImageService $imageService)
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

        // Logic to create a new member
        $member = Member::create($data);

        if ($member->type === Member::TYPE_BPP) {
            return redirect()->route('bpp-organization.index')->with('success', 'Anggota BPP berhasil ditambahkan.');
        } else {
            return redirect()->route('organization.index')->with('success', 'Anggota berhasil ditambahkan.');
        }
    }

    public function edit($id)
    {
        // Logic to find the member by ID
        $member = Member::findOrFail($id);

        return view('admins.member.create-edit', compact('member'));
    }

    public function update(MemberRequest $request, $id, ImageService $imageService)
    {
        $data = $request->validated();

        // Logic to update the member
        $member = Member::findOrFail($id);
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if (file_exists($member->image)) {
                unlink($member->image);
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
        $member->update($data);

        if ($member->type === Member::TYPE_BPP) {
            return redirect()->route('bpp-organization.index')->with('success', 'Anggota BPP berhasil ditambahkan.');
        } else {
            return redirect()->route('organization.index')->with('success', 'Anggota berhasil ditambahkan.');
        }
    }

    public function destroy($id)
    {
        // Logic to delete the member
        $member = Member::findOrFail($id);
        if (file_exists($member->image)) {
            unlink($member->image);
        }
        $member->delete();

        if ($member->type === Member::TYPE_BPP) {
            return redirect()->route('bpp-organization.index')->with('success', 'Anggota BPP berhasil ditambahkan.');
        } else {
            return redirect()->route('organization.index')->with('success', 'Anggota berhasil ditambahkan.');
        }
    }

    /**
     * Export members to Excel
     */
    public function exportMembers(ExcelService $excelService)
    {
        $members = Member::get();

        $data = $members->map(function ($member) {
            return [
                'ID' => $member->id,
                'Nama' => $member->name,
                'Gambar' => $member->image ?? '',
                'Type' => $member->type ?? 'organization',
                'Dibuat' => $member->created_at?->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        $headers = ['ID', 'Nama', 'Gambar', 'Type', 'Dibuat'];

        $filename = 'members_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        $filePath = $excelService->writeExcel($data, $headers, $filename);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Import members from Excel
     */
    public function importMembers(Request $request, ExcelService $excelService)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:2048'
        ]);

        try {
            $data = $excelService->readExcel($request->file('file'));

            DB::beginTransaction();

            $imported = 0;
            $errors = [];

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 karena index dimulai dari 0 dan ada header

                // Validasi data required
                if (empty($row['Nama'])) {
                    $errors[] = "Baris {$rowNumber}: Nama wajib diisi";
                    continue;
                }

                // Set default type jika tidak ada
                $type = $row['Type'] ?? 'organization';
                if (!in_array($type, ['organization', 'bpp'])) {
                    $type = 'organization';
                }

                // Buat atau update member
                Member::updateOrCreate(
                    ['name' => $row['Nama'], 'type' => $type],
                    [
                        'image' => $row['Gambar'] ?? null,
                        'type' => $type,
                    ]
                );

                $imported++;
            }

            DB::commit();

            $message = "Berhasil import {$imported} anggota.";
            if (!empty($errors)) {
                $message .= " Terdapat " . count($errors) . " error: " . implode(', ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " dan " . (count($errors) - 3) . " error lainnya.";
                }
            }

            return redirect()->route('member.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Download template Excel for members
     */
    public function downloadMemberTemplate(ExcelService $excelService)
    {
        $headers = ['Nama', 'Gambar', 'Type'];

        $filename = 'template_members.xlsx';
        $filePath = $excelService->generateTemplate($headers, $filename);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
