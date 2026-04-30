<?php

namespace App\Http\Controllers\Admin;

use App\Models\Position;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrganizationRequest;
use App\Models\Member;
use App\Services\ExcelService;
use Illuminate\Support\Facades\DB;

class OrganizationController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            if (request()->type === 'position') {
                $data = Position::latest();
                return DataTables::of($data)
                    ->addColumn('parent_name', function ($data) {
                        return $data->parent ? $data->parent?->name : '-';
                    })
                    ->addColumn('member_name', function ($data) {
                        if (!$data->member) {
                            return '<span class="text-gray-500">— Kosong —</span>';
                        }

                        $p = $data->member?->image;
                        $m = $data->member?->name;

                        if ($p) {
                            return '
                            <div class="d-flex align-items-center">
                                <img src="' . asset($p) . '" 
                                    class="rounded me-2" 
                                    style="width:36px;height:48px;object-fit:cover">
                                <span>' . e($m) . '</span>
                            </div>';
                        }

                        return e($m);
                    })
                    ->addColumn('action', function ($data) {
                        $actionEdit = route('organization.edit', $data->id);
                        $actionDelete = route('organization.destroy', $data->id);
                        return "<div class='d-flex justify-content-center'>" .
                            view('components.action.edit', ['action' => $actionEdit]) .
                            view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                            "</div>";
                    })
                    ->rawColumns(['action', 'member_name'])
                    ->make(true);
            }
            if (request()->type === 'member') {
                $data = Member::organization()->latest();
                return DataTables::of($data)
                    ->addColumn('action', function ($data) {
                        $actionEdit = route('member.edit', $data->id);
                        $actionDelete = route('member.destroy', $data->id);
                        return "<div class='d-flex justify-content-center'>" .
                            view('components.action.edit', ['action' => $actionEdit]) .
                            view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                            "</div>";
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
        }
        return view('admins.organization.index');
    }

    public function create()
    {
        $positions = Position::orderBy('name')->get();
        $members = Member::orderBy('name')->get();
        return view('admins.organization.create-edit', compact('positions', 'members'));
    }

    public function store(OrganizationRequest $request)
    {
        $data = $request->validated();

        Position::create($data);
        return redirect()->route('organization.index')->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $organization = Position::findOrFail($id);

        $positions = Position::orderBy('name')->get();
        $members = Member::orderBy('name')->get();
        return view('admins.organization.create-edit', compact('organization', 'positions', 'members'));
    }

    public function update(OrganizationRequest $request, $id)
    {
        $organization = Position::findOrFail($id);
        $data = $request->validated();

        $organization->update($data);
        return redirect()->route('organization.index')->with('success', 'Jabatan berhasil diperbarui.');
    }


    public function destroy($id)
    {
        $organization = Position::findOrFail($id);
        $organization->delete();
        return redirect()->route('organization.index')->with('success', 'Jabatan berhasil dihapus.');
    }

    /**
     * Export positions to Excel
     */
    public function exportPositions(ExcelService $excelService)
    {
        $positions = Position::with(['parent', 'member'])->get();

        $data = $positions->map(function ($position) {
            return [
                'ID' => $position->id,
                'Nama Jabatan' => $position->name,
                'Nama Jabatan (EN)' => $position->name_en,
                'Jabatan Induk' => $position->parent?->name ?? '',
                'Nama Anggota' => $position->member?->name ?? '',
                'Urutan' => $position->order,
                'Dibuat' => $position->created_at?->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        $headers = [
            'ID',
            'Nama Jabatan',
            'Nama Jabatan (EN)',
            'Jabatan Induk',
            'Nama Anggota',
            'Urutan',
            'Dibuat'
        ];

        $filename = 'positions_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        $filePath = $excelService->writeExcel($data, $headers, $filename);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Import positions from Excel
     */
    public function importPositions(Request $request, ExcelService $excelService)
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
                if (empty($row['Nama Jabatan'])) {
                    $errors[] = "Baris {$rowNumber}: Nama Jabatan wajib diisi";
                    continue;
                }

                // Cari jabatan induk jika ada
                $parentId = null;
                if (!empty($row['Jabatan Induk'])) {
                    $parent = Position::where('name', $row['Jabatan Induk'])->first();
                    if (!$parent) {
                        $errors[] = "Baris {$rowNumber}: Jabatan Induk '{$row['Jabatan Induk']}' tidak ditemukan";
                        continue;
                    }
                    $parentId = $parent->id;
                }

                // Cari member jika ada
                $memberId = null;
                if (!empty($row['Nama Anggota'])) {
                    $member = Member::where('name', $row['Nama Anggota'])->first();
                    if (!$member) {
                        $errors[] = "Baris {$rowNumber}: Anggota '{$row['Nama Anggota']}' tidak ditemukan";
                        continue;
                    }
                    $memberId = $member->id;
                }

                // Buat atau update position
                Position::updateOrCreate(
                    ['name' => $row['Nama Jabatan']],
                    [
                        'name_en' => $row['Nama Jabatan (EN)'] ?? null,
                        'parent_id' => $parentId,
                        'member_id' => $memberId,
                        'order' => is_numeric($row['Urutan']) ? (int)$row['Urutan'] : 0,
                    ]
                );

                $imported++;
            }

            DB::commit();

            $message = "Berhasil import {$imported} jabatan.";
            if (!empty($errors)) {
                $message .= " Terdapat " . count($errors) . " error: " . implode(', ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " dan " . (count($errors) - 3) . " error lainnya.";
                }
            }

            return redirect()->route('organization.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Download template Excel for positions
     */
    public function downloadPositionTemplate(ExcelService $excelService)
    {
        $headers = [
            'Nama Jabatan',
            'Nama Jabatan (EN)',
            'Jabatan Induk',
            'Nama Anggota',
            'Urutan'
        ];

        $filename = 'template_positions.xlsx';
        $filePath = $excelService->generateTemplate($headers, $filename);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Export organization members to Excel
     */
    public function exportMembers(ExcelService $excelService)
    {
        $members = Member::organization()->get();

        $data = $members->map(function ($member) {
            return [
                'ID' => $member->id,
                'Nama' => $member->name,
                'Gambar' => $member->image ?? '',
                'Type' => $member->type,
                'Dibuat' => $member->created_at?->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        $headers = ['ID', 'Nama', 'Gambar', 'Type', 'Dibuat'];

        $filename = 'organization_members_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        $filePath = $excelService->writeExcel($data, $headers, $filename);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Import organization members from Excel
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

                // Buat atau update member dengan type organization
                Member::updateOrCreate(
                    ['name' => $row['Nama'], 'type' => 'organization'],
                    [
                        'image' => $row['Gambar'] ?? null,
                        'type' => 'organization',
                    ]
                );

                $imported++;
            }

            DB::commit();

            $message = "Berhasil import {$imported} anggota organisasi.";
            if (!empty($errors)) {
                $message .= " Terdapat " . count($errors) . " error: " . implode(', ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " dan " . (count($errors) - 3) . " error lainnya.";
                }
            }

            return redirect()->route('organization.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Download template Excel for organization members
     */
    public function downloadMemberTemplate(ExcelService $excelService)
    {
        $headers = ['Nama', 'Gambar'];

        $filename = 'template_organization_members.xlsx';
        $filePath = $excelService->generateTemplate($headers, $filename);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
