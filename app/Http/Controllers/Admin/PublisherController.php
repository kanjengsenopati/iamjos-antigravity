<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\Publisher;
use App\Exports\PublisherExport;
use App\Exports\PublisherTemplateExport;
use App\Imports\PublisherImport;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PublisherRequest;
use App\Models\Role;
use Maatwebsite\Excel\Facades\Excel;

class PublisherController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = Publisher::with('admin')->latest();

            return DataTables::of($data)
                ->addColumn('name', function ($query) {
                    return $query->admin->name;
                })
                ->addColumn('email', function ($query) {
                    return $query->admin->email;
                })
                ->addColumn('avatar', function ($query) {
                    return $query->admin->avatar;
                })
                ->addColumn('is_active', function ($query) {
                    return $query->admin->is_active;
                })
                ->addColumn('type_badge', function ($query) {
                    return "<span class='badge badge-light-info'>{$query->type}</span>";
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('publisher.edit', $data->id);
                    $actionShow = route('publisher.show', $data->id);
                    $actionDelete = route('publisher.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.show', ['action' => $actionShow]) .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action', 'type_badge'])
                ->make(true);
        }
        return view('admins.publisher.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.publisher.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PublisherRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                // Create admin user
                $adminData = $request->only('name', 'email', 'password', 'avatar');
                $adminData['type'] = Admin::TYPE_PUBLISHER;
                $adminData['is_active'] = true;

                if (!empty($request->password)) {
                    $adminData['password'] = bcrypt($request->password);
                }

                if ($avatar = $request->file('avatar')) {
                    $adminData['avatar'] = 'storage/' . $avatar->store('images/avatars', 'public');
                }

                // check di table roles apakah ada role publisher kalau belum ada buat baru dan assign ke admin
                $roles = Role::where('name', Role::ROLE_PUBLISHER)->first();
                if (!$roles) {
                    $roleId = Role::create(['name' => Role::ROLE_PUBLISHER])->id;
                } else {
                    $roleId = $roles->id;
                }
                $adminData['role_id'] = $roleId;
                $admin = Admin::create($adminData);
                $admin->assignRole(Role::find($roleId)->name);

                // Create publisher detail
                $publisherData = $request->only(
                    'code',
                    'alias',
                    'type',
                    'sk_kemenkumham_link',
                    'akta_notaris_link',
                    'address',
                    'city',
                    'website',
                    'contact_name',
                    'phone',
                    'prefix_doi',
                    'additional_prefixes'
                );

                $publisherData['admin_id'] = $admin->id;

                // Handle additional_prefixes as array
                if ($request->has('additional_prefixes') && is_array($request->additional_prefixes)) {
                    $publisherData['additional_prefixes'] = array_filter($request->additional_prefixes, fn($item) => !empty($item));
                }

                Publisher::create($publisherData);
            });

            return redirect()->route('publisher.index')->with('success', 'Berhasil menambah publisher');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Gagal menambah publisher: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Publisher $publisher)
    {
        $publisher->load('admin');
        return view('admins.publisher.show', compact('publisher'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Publisher $publisher)
    {
        $publisher->load('admin');
        return view('admins.publisher.create-edit', compact('publisher'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PublisherRequest $request, Publisher $publisher)
    {
        DB::beginTransaction();
        try {
            $admin = $publisher->admin;

            // Update admin user data
            $adminData = $request->only('name', 'email', 'password', 'avatar');

            if (!empty($request->password)) {
                $adminData['password'] = bcrypt($request->password);
            }

            if (!empty($avatar = $request->avatar)) {
                if ($admin->avatar && file_exists($admin->avatar)) {
                    unlink($admin->avatar);
                }
                $adminData['avatar'] = 'storage/' . $avatar->store('images/avatars', ['disk' => 'public']);
            }

            $admin->update($adminData);

            // Update publisher detail
            $publisherData = $request->only(
                'code',
                'alias',
                'type',
                'sk_kemenkumham_link',
                'akta_notaris_link',
                'address',
                'city',
                'website',
                'contact_name',
                'phone',
                'prefix_doi',
                'additional_prefixes'
            );

            // Handle additional_prefixes as array
            if ($request->has('additional_prefixes') && is_array($request->additional_prefixes)) {
                $publisherData['additional_prefixes'] = array_filter($request->additional_prefixes, fn($item) => !empty($item));
            }

            $publisher->update($publisherData);

            DB::commit();
            return redirect()->route('publisher.index')->with('success', 'Berhasil update data publisher');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return redirect()->back()->with('error', 'Gagal update publisher: ' . $th->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Publisher $publisher)
    {
        try {
            DB::transaction(function () use ($publisher) {
                $admin = $publisher->admin;

                if ($admin->avatar && file_exists($admin->avatar)) {
                    unlink($admin->avatar);
                }

                $publisher->delete();
                $admin->delete();
            });

            return redirect()->route('publisher.index')->with('success', 'Berhasil menghapus publisher');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Gagal menghapus publisher: ' . $e->getMessage());
        }
    }

    /**
     * Export publishers to Excel
     */
    public function exportData()
    {
        try {
            return Excel::download(
                new PublisherExport(),
                'Publishers_' . date('Y-m-d_H-i-s') . '.xlsx'
            );
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }

    /**
     * Download template import
     */
    public function downloadTemplate()
    {
        try {
            return Excel::download(
                new PublisherTemplateExport(),
                'Template_Publisher.xlsx'
            );
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Gagal download template: ' . $e->getMessage());
        }
    }

    /**
     * Import publishers from Excel
     */
    public function importData()
    {
        try {
            if (!request()->hasFile('import_file')) {
                return redirect()->back()->with('error', 'File tidak ditemukan');
            }

            $file = request()->file('import_file');

            // Validate file type
            if (!in_array($file->getClientOriginalExtension(), ['xlsx', 'xls', 'csv'])) {
                return redirect()->back()->with('error', 'Format file harus Excel (.xlsx, .xls) atau CSV');
            }

            $import = new PublisherImport();
            Excel::import($import, $file);

            $errors = $import->getErrors();

            if (count($errors) > 0) {
                $errorMessages = '';
                foreach ($errors as $error) {
                    if (isset($error['errors'])) {
                        $errorMessages .= "Baris {$error['row']}: " . implode(', ', $error['errors']) . '; ';
                    } else {
                        $errorMessages .= "Baris {$error['row']}: {$error['error']}; ";
                    }
                }
                return redirect()->back()->with('warning', 'Data berhasil diimport dengan beberapa error. ' . $errorMessages);
            }

            return redirect()->route('publisher.index')->with('success', 'Data publisher berhasil diimport');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }
}
