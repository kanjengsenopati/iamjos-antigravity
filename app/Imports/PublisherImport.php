<?php

namespace App\Imports;

use App\Models\Admin;
use App\Models\Publisher;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Failures\Failure;

class PublisherImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    protected $errors = [];
    protected $skipped = 0;

    public function model(array $row)
    {
        try {
            return DB::transaction(function () use ($row) {
                // Check if admin email already exists
                $admin = Admin::where('email', $row['email'])->first();

                if (!$admin) {
                    // Create new admin
                    $admin = Admin::create([
                        'name' => $row['nama_publisher'],
                        'email' => $row['email'],
                        'password' => bcrypt('password123'), // Default password
                        'type' => Admin::TYPE_PUBLISHER,
                        'is_active' => true,
                    ]);
                }

                // Check if publisher code already exists
                $existingPublisher = Publisher::where('code', $row['kode_publisher'])->first();

                if ($existingPublisher) {
                    // Update existing publisher
                    $existingPublisher->update([
                        'alias' => $row['alias'],
                        'type' => $row['tipe'],
                        'contact_name' => $row['nama_kontak'],
                        'phone' => $row['telepon_wa'],
                        'city' => $row['kota'],
                        'address' => $row['alamat'],
                        'website' => $row['website'] != '-' ? $row['website'] : null,
                        'prefix_doi' => $row['prefix_doi'],
                        'additional_prefixes' => $this->parseAdditionalPrefixes($row['prefix_doi_tambahan']),
                        'sk_kemenkumham_link' => $row['link_sk_kemenkumham'] != '-' ? $row['link_sk_kemenkumham'] : null,
                        'akta_notaris_link' => $row['link_akta_notaris'] != '-' ? $row['link_akta_notaris'] : null,
                    ]);
                    $existingPublisher->admin()->update(['name' => $row['nama_publisher']]);

                    return $existingPublisher;
                } else {
                    // Create new publisher
                    return Publisher::create([
                        'admin_id' => $admin->id,
                        'code' => $row['kode_publisher'],
                        'alias' => $row['alias'],
                        'type' => $row['tipe'],
                        'contact_name' => $row['nama_kontak'],
                        'phone' => $row['telepon_wa'],
                        'city' => $row['kota'],
                        'address' => $row['alamat'],
                        'website' => $row['website'] != '-' ? $row['website'] : null,
                        'prefix_doi' => $row['prefix_doi'],
                        'additional_prefixes' => $this->parseAdditionalPrefixes($row['prefix_doi_tambahan']),
                        'sk_kemenkumham_link' => $row['link_sk_kemenkumham'] != '-' ? $row['link_sk_kemenkumham'] : null,
                        'akta_notaris_link' => $row['link_akta_notaris'] != '-' ? $row['link_akta_notaris'] : null,
                    ]);
                }
            });
        } catch (\Exception $e) {
            $this->errors[] = [
                'row' => $row,
                'error' => $e->getMessage(),
            ];
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'kode_publisher' => 'required|string|max:50',
            'alias' => 'required|string|max:255',
            'nama_publisher' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'tipe' => 'required|in:Institusi,Asosiasi,Yayasan,CV,PT',
            'nama_kontak' => 'required|string|max:255',
            'telepon_wa' => 'required|string|max:20',
            'kota' => 'required|string|max:100',
            'alamat' => 'required|string|max:500',
            'website' => 'nullable|max:255',
            'prefix_doi' => 'required|string|max:100',
            'prefix_doi_tambahan' => 'nullable|string',
            'link_sk_kemenkumham' => 'nullable|max:500',
            'link_akta_notaris' => 'nullable|max:500',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'kode_publisher.required' => 'Kode publisher harus diisi',
            'alias.required' => 'Alias harus diisi',
            'nama_publisher.required' => 'Nama publisher harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'tipe.required' => 'Tipe publisher harus diisi',
            'tipe.in' => 'Tipe publisher hanya boleh: Institusi, Asosiasi, Yayasan, CV, PT',
            'nama_kontak.required' => 'Nama kontak harus diisi',
            'telepon_wa.required' => 'Telepon/WA harus diisi',
            'kota.required' => 'Kota harus diisi',
            'alamat.required' => 'Alamat harus diisi',
            'prefix_doi.required' => 'Prefix DOI harus diisi',
        ];
    }

    public function onError(\Throwable $e)
    {
        // Handle error
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ];
        }
    }

    /**
     * Parse additional prefixes from string
     */
    private function parseAdditionalPrefixes($prefixesString)
    {
        if (empty($prefixesString) || $prefixesString == '-') {
            return null;
        }

        $prefixes = array_map('trim', explode(',', $prefixesString));
        $prefixes = array_filter($prefixes, fn($p) => !empty($p));

        return count($prefixes) > 0 ? $prefixes : null;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
