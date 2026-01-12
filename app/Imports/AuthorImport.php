<?php

namespace App\Imports;

use App\Models\Admin;
use App\Models\Author;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Failures\Failure;

class AuthorImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    protected $errors = [];

    public function model(array $row)
    {
        try {
            return DB::transaction(function () use ($row) {
                // Check if admin email already exists
                $admin = Admin::where('email', $row['email'])->first();

                if (!$admin) {
                    // Create new admin
                    $admin = Admin::create([
                        'name' => $row['nama_author'],
                        'email' => $row['email'],
                        'password' => bcrypt('password123'),
                        'type' => Admin::TYPE_AUTHOR,
                        'is_active' => true,
                    ]);
                } else {
                    // Ensure admin type is AUTHOR
                    $admin->update(['type' => Admin::TYPE_AUTHOR]);
                }

                // Check if author registration number exists
                $existing = Author::where('registration_number', $row['no_registrasi'])->first();

                if ($existing) {
                    // update
                    $existing->update([
                        'admin_id' => $admin->id,
                        'avatar' => $admin->avatar ?? null,
                        'institution' => $row['institusi'] ?? null,
                        'phone' => $row['telepon_wa'] ?? null,
                    ]);
                    $existing->admin()->update(['name' => $row['nama_author']]);
                    return $existing;
                } else {
                    // create author
                    return Author::create([
                        'admin_id' => $admin->id,
                        'avatar' => $admin->avatar ?? null,
                        'registration_number' => $row['no_registrasi'],
                        'institution' => $row['institusi'] ?? null,
                        'phone' => $row['telepon_wa'] ?? null,
                    ]);
                }
            });
        } catch (\Exception $e) {
            $this->errors[] = ['row' => $row, 'error' => $e->getMessage()];
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'no_registrasi' => 'required|string|max:50',
            'nama_author' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'institusi' => 'nullable|string|max:255',
            'telepon_wa' => 'nullable|string|max:20',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'no_registrasi.required' => 'No. Registrasi harus diisi',
            'nama_author.required' => 'Nama author harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
        ];
    }

    public function onError(\Throwable $e)
    {
        // handle error
    }

    public function onFailure(Failure ...$fails)
    {
        foreach ($fails as $failure) {
            $this->errors[] = [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ];
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
