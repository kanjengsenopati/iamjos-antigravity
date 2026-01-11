# Publisher Module Documentation

## Struktur Database

### Tabel: publishers
Tabel terpisah yang berelasi dengan tabel `admins` untuk menyimpan data detail publisher.

**Fields:**
- `id` (UUID, Primary Key)
- `admin_id` (UUID, Foreign Key → admins.id) - Relasi dengan akun admin
- `code` (String, Unique) - Kode unik publisher
- `alias` (String) - Nama alias/singkatan publisher
- `type` (Enum) - Tipe publisher: Institusi, Asosiasi, Yayasan, CV, PT
- `sk_kemenkumham_link` (String, Nullable) - Link Google Drive SK Kemenkumham
- `akta_notaris_link` (String, Nullable) - Link Google Drive AKTA Notaris
- `address` (Text) - Alamat lengkap publisher
- `city` (String) - Kota publisher
- `website` (String, Nullable) - Website/URL publisher
- `contact_name` (String) - Nama contact person
- `phone` (String) - Nomor telepon/WhatsApp
- `prefix_doi` (String) - Prefix DOI utama
- `additional_prefixes` (JSON) - Array prefix DOI tambahan
- `created_at`, `updated_at`, `deleted_at` (Timestamps)

## Struktur File

### Models
- `App\Models\Publisher` - Model Publisher dengan relasi ke Admin

### Controllers
- `App\Http\Controllers\Admin\PublisherController` - CRUD operations

### Requests (Validation)
- `App\Http\Requests\Admin\PublisherRequest` - Validasi untuk create/update

### Views
- `resources/views/admins/publisher/index.blade.php` - Daftar publisher (DataTables)
- `resources/views/admins/publisher/create-edit.blade.php` - Form tambah/edit dengan tabs
- `resources/views/admins/publisher/show.blade.php` - Detail publisher

### Migrations
- `database/migrations/2025_11_16_104500_create_publishers_table.php` - Membuat tabel publishers

## Routes

All routes berprefiks `/admin` dan memerlukan middleware `auth`:

```
GET|HEAD   /admin/publisher              publisher.index
GET|HEAD   /admin/publisher/create       publisher.create
POST       /admin/publisher              publisher.store
GET|HEAD   /admin/publisher/{publisher}  publisher.show
GET|HEAD   /admin/publisher/{publisher}/edit publisher.edit
PUT|PATCH  /admin/publisher/{publisher}  publisher.update
DELETE     /admin/publisher/{publisher}  publisher.destroy
```

## Fitur Utama

### 1. Index (Daftar Publisher)
- Tampilkan daftar semua publisher dengan DataTables
- Kolom: No, Avatar, Nama, Kode, Email, Tipe, Kota, Status, Aksi
- Responsive design
- Action buttons: Show, Edit, Delete

### 2. Create/Edit Publisher
- Organized dengan Tab Interface:
  - **Tab 1: Data Admin** - Email, Nama, Password, Avatar
  - **Tab 2: Informasi Publisher** - Kode, Alias, Tipe, Website, Alamat, Kota, Kontak
  - **Tab 3: Dokumen & Legal** - Link SK Kemenkumham, AKTA Notaris
  - **Tab 4: Konfigurasi DOI** - Prefix DOI dan prefix tambahan (dynamic fields)

- Fitur Dynamic Fields untuk "Prefix DOI Tambahan":
  - Tombol "Tambah Prefix" untuk menambah field baru
  - Tombol hapus per field
  - Minimal 1 field harus ada

- Image Preview untuk Avatar
- Client-side dan server-side validation
- Error messages yang user-friendly

### 3. Show/Detail Publisher
- Tampilkan detail lengkap dengan Tab:
  - Overview (Avatar, Nama, Kode, Email, Tipe, Status)
  - Info Publisher (Alias, Tipe, Website, Kontak, Lokasi)
  - Dokumen (Link ke SK Kemenkumham, AKTA Notaris)
  - Konfigurasi DOI (Prefix utama dan tambahan)

- Link ke Google Drive untuk dokumen
- Edit button untuk mengubah data
- Back button untuk kembali ke index

## Relasi Database

```
Admin (1) ──── (1) Publisher
```

- Ketika publisher dihapus, akun admin juga dihapus (cascade delete)
- Ketika membuat publisher, sistem otomatis membuat akun admin dengan type = 'PUBLISHER'

## Validation Rules

### Admin Data
- Email: required, email, unique
- Name: required, string, max 255
- Password: required (create), optional (update), min 8, confirmed

### Publisher Data
- Code: required, unique, max 50
- Alias: required, max 255
- Type: required, must be one of: Institusi, Asosiasi, Yayasan, CV, PT
- Address: required, max 500
- City: required, max 100
- Contact Name: required, max 255
- Phone: required, regex untuk format telepon/WA
- Prefix DOI: required, max 100
- Additional Prefixes: array, max 100 per item
- Links: optional, must be valid URLs

## Best Practices Applied

✅ **English naming convention** - Semua column names dalam bahasa Inggris
✅ **Separate tables** - Publishers sebagai detail table terpisah dari admins
✅ **Relationships** - Menggunakan Eloquent relationships
✅ **Migrations** - Versioned migrations dengan rollback support
✅ **Validation** - Server-side validation dengan FormRequest
✅ **Responsive UI** - Mobile-friendly design
✅ **Security** - CSRF protection, input validation, authorization checks
✅ **User Experience** - Tab interface, dynamic fields, preview images
✅ **Error Handling** - Try-catch dengan logging
✅ **Transaction Safety** - DB::transaction untuk data consistency

## Penggunaan

### Menambah Publisher
1. Klik tombol "Tambah Publisher" di halaman index
2. Isi semua tab form (Data Admin, Info Publisher, Dokumen, DOI)
3. Klik "Tambah Publisher"
4. Sistem akan membuat akun admin dan detail publisher secara bersamaan

### Mengubah Publisher
1. Klik tombol "Edit" di halaman index atau detail
2. Ubah data yang diperlukan
3. Password bersifat optional (biarkan kosong jika tidak ingin mengubah)
4. Klik "Update Publisher"

### Menghapus Publisher
1. Klik tombol "Delete" di halaman index
2. Konfirmasi penghapusan
3. Sistem akan menghapus publisher dan akun admin terkait

### Melihat Detail Publisher
1. Klik tombol "Show" di halaman index
2. Lihat detail lengkap di beberapa tab
3. Klik link untuk membuka dokumen atau website
