# Publisher Import/Export Documentation

## Overview

Fitur import/export memungkinkan administrator untuk:
- ✅ **Export** data publisher ke file Excel dengan format profesional
- ✅ **Import** data publisher dari file Excel/CSV dengan validasi lengkap
- ✅ **Download Template** file Excel kosong sebagai panduan format
- ✅ **Update & Bulk Create** - Jika kode publisher sudah ada, data akan di-update, jika tidak ada akan dibuat baru

## File-File yang Dibuat

### Classes

#### 1. `app/Exports/PublisherExport.php`
Class untuk export data publisher ke Excel dengan:
- Header berwarna biru dengan white text
- Auto-sizing columns
- Text wrapping untuk data panjang
- Professional styling dan borders

**Output Columns:**
- Kode Publisher
- Alias
- Nama Publisher
- Email
- Tipe
- Nama Kontak
- Telepon/WA
- Kota
- Alamat
- Website
- Prefix DOI
- Prefix DOI Tambahan (comma-separated)
- Link SK Kemenkumham
- Link AKTA Notaris
- Status (Aktif/Nonaktif)
- Dibuat Pada

#### 2. `app/Exports/PublisherTemplateExport.php`
Class untuk download template Excel kosong dengan:
- Header berwarna hijau
- Contoh data di baris ke-2 (untuk referensi)
- Proper column widths
- Professional styling

#### 3. `app/Imports/PublisherImport.php`
Class untuk import data dari Excel/CSV dengan:
- **Validasi lengkap** semua field wajib
- **Smart handling** - Update jika publisher sudah ada, create jika baru
- **Automatic account creation** - Otomatis create admin account jika email baru
- **Error collection** - Mengumpulkan semua error untuk reporting
- **Transaction safety** - Setiap import dalam transaction

**Validasi Rules:**
- `kode_publisher`: required, unique, max 50 (jika create)
- `alias`: required, string, max 255
- `nama_publisher`: required, string, max 255
- `email`: required, email, unique (jika create)
- `tipe`: required, in [Institusi, Asosiasi, Yayasan, CV, PT]
- `nama_kontak`: required, string, max 255
- `telepon_wa`: required, string, max 20
- `kota`: required, string, max 100
- `alamat`: required, string, max 500
- `website`: optional, max 255
- `prefix_doi`: required, string, max 100
- `prefix_doi_tambahan`: optional, comma-separated values
- `link_sk_kemenkumham`: optional, max 500
- `link_akta_notaris`: optional, max 500

### Controller Methods

#### 1. `exportData()`
```php
Route::get('admin/publisher/export-data', [PublisherController::class, 'exportData'])->name('publisher.export');
```
- Mengexport semua data publisher ke file Excel
- Filename: `Publishers_YYYY-MM-DD_HH-MM-SS.xlsx`
- Automatic download ke browser

#### 2. `downloadTemplate()`
```php
Route::get('admin/publisher/download-template', [PublisherController::class, 'downloadTemplate'])->name('publisher.template');
```
- Download template kosong dengan contoh data
- Filename: `Template_Publisher.xlsx`
- Untuk referensi format data yang akan di-import

#### 3. `importData()`
```php
Route::post('admin/publisher/import-data', [PublisherController::class, 'importData'])->name('publisher.import');
```
- Upload dan import file Excel/CSV
- Validasi file type (.xlsx, .xls, .csv)
- Menampilkan error messages jika ada
- Redirect ke index dengan success message

### Routes

```php
// File: routes/web.php (dalam route group middleware auth & prefix admin)

Route::resource('publisher', PublisherController::class);
Route::post('publisher/import-data', [PublisherController::class, 'importData'])->name('publisher.import');
Route::get('publisher/export-data', [PublisherController::class, 'exportData'])->name('publisher.export');
Route::get('publisher/download-template', [PublisherController::class, 'downloadTemplate'])->name('publisher.template');
```

### UI Components

#### Buttons di Index Page
```blade
<!-- Button 1: Import -->
<button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
    <i class="fa fa-upload me-1"></i>Import
</button>

<!-- Button 2: Export -->
<a href="{{ route('publisher.export') }}" class="btn btn-success btn-sm">
    <i class="fa fa-download me-1"></i>Export
</a>

<!-- Button 3: Download Template -->
<a href="{{ route('publisher.template') }}" class="btn btn-secondary btn-sm">
    <i class="fa fa-file-excel me-1"></i>Download Template
</a>

<!-- Button 4: Tambah Publisher -->
<a href="{{ route('publisher.create') }}" class="btn btn-primary btn-sm btn-create">
    <i class="fa fa-plus me-1"></i>Tambah Publisher
</a>
```

#### Import Modal
Modal Bootstrap untuk upload file import dengan:
- File input dengan validasi extension
- Info alerts dengan catatan penting
- Warning tentang behavior (update/create)
- Cancel dan Submit buttons

## Workflow

### 1. Download Template
1. Klik tombol "Download Template"
2. File `Template_Publisher.xlsx` akan diunduh
3. Buka file dan lihat contoh data di baris ke-2
4. Gunakan sebagai referensi format

### 2. Persiapan Data
1. Copy template atau siapkan file Excel dengan struktur yang sama
2. Isi data publisher sesuai dengan format yang ditunjukkan
3. Untuk Prefix DOI Tambahan, pisahkan dengan koma (,)
4. Untuk link yang tidak ada, isi dengan `-` (tanda minus)
5. Pastikan email unik atau akan update publisher yang sudah ada

### 3. Import Data
1. Klik tombol "Import"
2. Modal akan terbuka
3. Pilih file Excel/CSV yang sudah disiapkan
4. Klik "Import Data"
5. Sistem akan memproses dan menampilkan hasil

### 4. Export Data
1. Klik tombol "Export"
2. File Excel akan otomatis diunduh
3. File berisi semua data publisher saat ini
4. Data sudah terformat dan siap untuk digunakan

## Contoh Format Excel Import

| Kode Publisher | Alias | Nama Publisher | Email | Tipe | Nama Kontak | Telepon/WA | Kota | Alamat | Website | Prefix DOI | Prefix DOI Tambahan | Link SK Kemenkumham | Link AKTA Notaris |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| PUB001 | Jaya Pub | PT Jaya Publisher | publisher@jayapub.com | PT | Budi Santoso | +6281234567890 | Jakarta | Jl. Sudirman No. 123, Jakarta Pusat | https://www.jayapub.com | 10.5555 | 10.6666, 10.7777 | https://drive.google.com/file/d/... | https://drive.google.com/file/d/... |
| PUB002 | Karya Pub | CV Karya Publisher | publisher@karyapub.com | CV | Ani Wijaya | 08123456789 | Bandung | Jl. Ahmad Yani No. 45, Bandung | https://www.karyapub.com | 10.8888 | - | - | - |

## Fitur Smart Features

### 1. Auto Update pada Kode Duplikat
Jika `kode_publisher` yang di-import sudah ada di database:
- Data publisher akan di-**update** dengan data baru
- Nama publisher di akun admin akan di-update
- Kode publisher tetap sama
- Admin ID tetap sama

### 2. Auto Create Akun Admin
Jika `email` yang di-import belum ada di database:
- Sistem otomatis membuat akun admin baru
- Type otomatis diset ke "PUBLISHER"
- Password default: `password123`
- User dapat ubah password setelah login

### 3. Validation & Error Handling
- Setiap field divalidasi sesuai rules
- Error messages yang user-friendly ditampilkan
- Baris yang error tetap di-process jika ada sebelumnya
- Warning notifications jika ada error dalam proses

## Package Dependencies

```json
{
  "maatwebsite/excel": "^3.1"
}
```

### Sub-dependencies:
- `phpoffice/phpspreadsheet` - Untuk membaca/menulis Excel
- `ezyang/htmlpurifier` - Untuk sanitasi HTML

## Error Messages

### Import Error Examples

```
Kode Publisher harus diisi
Tipe publisher hanya boleh: Institusi, Asosiasi, Yayasan, CV, PT
Email harus berupa email yang valid
```

### File Error Examples

```
Format file harus Excel (.xlsx, .xls) atau CSV
File tidak ditemukan
```

## Performance Notes

- ✅ Efficient untuk ratusan baris data
- ✅ Memory-optimized dengan streaming
- ✅ Transaction-based untuk data consistency
- ✅ Proper error handling dan logging

## Security Features

- ✅ CSRF protection (form token)
- ✅ File type validation
- ✅ Input validation & sanitization
- ✅ Database transaction rollback on error
- ✅ Proper logging untuk audit trail

## Troubleshooting

### "Format file harus Excel (.xlsx, .xls) atau CSV"
- Pastikan file adalah Excel (.xlsx, .xls) atau CSV
- Jangan gunakan format lain seperti .pdf

### "Email sudah terdaftar"
- Email sudah digunakan admin lain
- Gunakan email unik atau update email yang sudah ada

### Data tidak semua terimpor
- Cek warning messages untuk melihat baris mana yang error
- Perbaiki data di file dan import ulang
- File akan menampilkan rincian error per baris

## Tips & Best Practices

1. **Selalu download template terlebih dahulu** untuk memastikan format sesuai
2. **Validasi data sebelum import** - Check email dan kode yang unik
3. **Backup database** sebelum melakukan import data besar
4. **Test dengan data kecil dulu** sebelum import massal
5. **Simpan file export** sebagai backup berkala
6. **Untuk prefix DOI tambahan**, gunakan koma tanpa spasi: `10.6666,10.7777,10.8888`
