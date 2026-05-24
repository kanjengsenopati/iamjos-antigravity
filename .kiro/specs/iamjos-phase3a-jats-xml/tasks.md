# Rencana Implementasi: JATS XML Export (iamjos-phase3a-jats-xml)

## Overview

Implementasi ekspor JATS 1.3 XML untuk platform IAMJOS. Terdiri dari tiga komponen utama: `JatsXmlService` (menggunakan `DOMDocument`), `JatsXmlController` (route publik + admin), dan update view `article.blade.php` untuk menampilkan tombol download JATS XML.

## Tasks

- [x] 1. Buat `JatsXmlService` — struktur dasar dan helper methods
  - Buat file `app/Services/JatsXmlService.php` dengan namespace `App\Services`
  - Deklarasikan property `private DOMDocument $dom` dan `private DOMElement $root`
  - Implementasikan method `generate(Submission $submission): string` sebagai entry point utama
    - Validasi: lempar `InvalidArgumentException` jika `$submission->currentPublication` null
    - Validasi: lempar `InvalidArgumentException` jika `$submission->journal` null
    - Inisialisasi `DOMDocument` dengan `version="1.0"` dan `encoding="UTF-8"`
    - Set `$dom->formatOutput = true`
    - Buat elemen root `<article>` dengan atribut `article-type`, `xml:lang`, `dtd-version="1.3"`, `xmlns:xlink`, `xmlns:ali`
    - Panggil `buildFront()` dan `buildBack()`, append ke root
    - Return `$dom->saveXML()`
  - Implementasikan helper `toBcp47(string $locale): string`
    - Gunakan `preg_replace('/_[A-Z]{2}$/', '', $locale)` untuk menghapus kode negara
    - Fallback ke `"en"` jika locale null atau kosong
  - Implementasikan helper `sectionToArticleType(?string $sectionTitle): string`
    - Mapping case-insensitive: "research article" / "original article" / "original research" → `research-article`
    - "review" / "review article" / "literature review" → `review-article`
    - "case report" / "case study" → `case-report`
    - "editorial" → `editorial`
    - "letter" / "letter to the editor" → `letter`
    - "brief report" / "brief communication" → `brief-report`
    - Default: `research-article`
  - Implementasikan helper `cleanOrcid(string $orcid): string`
    - Strip prefix `https://orcid.org/` atau `http://orcid.org/` menggunakan `preg_replace`
  - Implementasikan helper `extractDoi(string $refText): ?string`
    - Gunakan regex `/\b(10\.\d{4,}\/\S+)/i` untuk ekstrak DOI
    - Strip trailing punctuation (`.,;)`) dari hasil match
  - Implementasikan helper `parseKeywords(mixed $keywords): array`
    - Jika string dan dimulai dengan `[`: decode sebagai JSON array
    - Jika string biasa: `explode(',', $keywords)`
    - Jika iterable: iterasi langsung
    - Untuk setiap item: ekstrak nilai dari array/object (`value`, `content`, `name`) atau cast ke string
    - Filter: skip item yang kosong atau hanya whitespace setelah `trim()`
  - _Requirements: 1.1, 1.9, 1.10, 1.11, 7.1, 8.1, 8.2, 8.4, 8.5_

- [x] 2. Implementasikan `buildJournalMeta()` dan `buildFront()`
  - Implementasikan `buildJournalMeta(Submission $submission): DOMElement`
    - Buat elemen `<journal-meta>`
    - Tambahkan `<journal-id journal-id-type="publisher-id">` berisi `$journal->slug`
    - Tambahkan `<journal-title-group>` berisi `<journal-title>` dari `$journal->name`
    - Jika `$journal->abbreviation` tersedia: tambahkan `<abbrev-journal-title>` di dalam `<journal-title-group>`
    - Jika `$journal->issn_print` tersedia: tambahkan `<issn pub-type="ppub">`
    - Jika `$journal->issn_online` tersedia: tambahkan `<issn pub-type="epub">`
    - Tambahkan `<publisher>` berisi `<publisher-name>` dari `$journal->publisher`
  - Implementasikan `buildFront(Submission $submission): DOMElement`
    - Buat elemen `<front>`
    - Append hasil `buildJournalMeta()` dan `buildArticleMeta()`
  - _Requirements: 1.4, 1.5, 7.4_

- [x] 3. Implementasikan `buildArticleMeta()` — identifikasi, judul, tanggal, volume, halaman
  - Implementasikan `buildArticleMeta(Submission $submission): DOMElement`
    - Buat elemen `<article-meta>`
    - Jika `$publication->doi` tersedia: tambahkan `<article-id pub-id-type="doi">`
    - Tambahkan `<title-group>` berisi `<article-title>` dari `$publication->title`
    - Jika `$publication->subtitle` tersedia: tambahkan `<subtitle>` di dalam `<title-group>`
    - Append hasil `buildContribGroup()`
    - Tentukan tanggal publikasi: prioritas `$publication->date_published`, fallback `$issue->published_at`
    - Jika tanggal tersedia: tambahkan `<pub-date publication-format="electronic">` berisi `<year>`, `<month>`, `<day>`
    - Jika `$issue->volume` tersedia: tambahkan `<volume>`
    - Jika `$issue->number` tersedia: tambahkan `<issue>`
    - Proses `$publication->pages`: jika mengandung `-`, split menjadi `<fpage>` dan `<lpage>`; jika tidak, hanya `<fpage>`
    - Tambahkan `<permissions>` (lihat task 4)
    - Jika `$publication->abstract` tersedia: tambahkan `<abstract><p>` dengan `strip_tags($abstract)`
    - Jika keywords tersedia: append hasil `buildKwdGroup()`
  - _Requirements: 2.1, 2.2, 2.8, 2.9, 2.10, 2.11, 2.13, 2.14, 7.4_

- [x] 4. Implementasikan `buildContribGroup()` dan elemen `<permissions>`
  - Implementasikan `buildContribGroup(Submission $submission): DOMElement`
    - Buat elemen `<contrib-group>`
    - Ambil authors dari `$publication->authors`, urutkan berdasarkan `sort_order`
    - Untuk setiap author: buat `<contrib contrib-type="author">`
      - Jika `$author->is_corresponding` true: set atribut `corresp="yes"`
      - Tambahkan `<name>` berisi `<surname>` (dari `last_name`) dan `<given-names>` (dari `first_name`)
      - Jika `$author->orcid` tersedia: tambahkan `<contrib-id contrib-id-type="orcid">` berisi URL lengkap `https://orcid.org/{cleanOrcid}`
      - Jika `$author->affiliation` tersedia: tambahkan `<aff>` inline di dalam `<contrib>`
    - Jika authors kosong/null: kembalikan `<contrib-group>` kosong tanpa error
  - Implementasikan blok `<permissions>` di dalam `buildArticleMeta()`
    - Tentukan `$year` dari `$publication->copyright_year` atau `$issue->year` atau `date('Y')`
    - Tentukan `$holder` dari `$publication->copyright_holder` atau `$journal->publisher` atau `$journal->name`
    - Tambahkan `<copyright-statement>` dengan format `"Copyright (c) {year} {holder}"`
    - Tambahkan `<copyright-year>` dan `<copyright-holder>`
    - Jika `$publication->license_url` tersedia: tambahkan `<license>` berisi `<ali:license_ref>` dengan namespace `ali`
  - _Requirements: 2.3, 2.4, 2.5, 2.6, 2.7, 2.12, 7.4, 8.3_

- [ ]* 4.1 Tulis property test untuk Property 5: Author ordering dipertahankan
  - **Property 5: Author ordering dipertahankan**
  - Buat test di `tests/Unit/Services/JatsXmlServiceTest.php`
  - Generate submission dengan beberapa author dengan `sort_order` acak
  - Assert: urutan `<contrib>` dalam `<contrib-group>` sesuai urutan `sort_order` ascending
  - **Validates: Requirements 2.3**

- [x] 5. Implementasikan `buildBack()` dan `buildRefList()`
  - Implementasikan `buildRefList(string $references): DOMElement`
    - Buat elemen `<ref-list>`
    - Split `$references` berdasarkan `\n`
    - Filter: hapus baris kosong dan baris dengan panjang < 5 karakter setelah `trim()`
    - Untuk setiap referensi (index mulai dari 1):
      - Buat `<ref id="ref-{n}">`
      - Buat `<mixed-citation>` berisi teks referensi mentah
      - Jika `extractDoi($refText)` mengembalikan nilai: tambahkan `<pub-id pub-id-type="doi">` di dalam `<mixed-citation>`
  - Implementasikan `buildBack(Submission $submission): ?DOMElement`
    - Jika `$publication->references` kosong atau null: return null (elemen `<back>` tidak ditambahkan)
    - Jika tersedia: buat `<back>` berisi hasil `buildRefList()`
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 1.8_

- [ ]* 5.1 Tulis property test untuk Property 6: Reference round-trip
  - **Property 6: Reference count matches input lines**
  - Buat test di `tests/Unit/Services/JatsXmlServiceTest.php`
  - Generate string referensi multi-baris dengan variasi baris kosong dan baris pendek
  - Assert: `count(<ref>)` == jumlah baris dengan `strlen(trim($line)) > 5` setelah split by `\n`
  - **Validates: Requirements 5.2, 5.3, 5.4**

- [x] 6. Tulis unit tests untuk `JatsXmlService`
  - Buat file `tests/Unit/Services/JatsXmlServiceTest.php`
  - Test `generate()` menghasilkan XML yang dapat di-parse oleh `DOMDocument::loadXML()` (round-trip)
  - Test namespace declarations hadir di elemen root: `xmlns:xlink`, `xmlns:ali`, `dtd-version="1.3"`
  - Test konversi BCP47: `id_ID` → `id`, `en_US` → `en`, `pt_BR` → `pt`
  - Test mapping section title: "Research Article" → `research-article`, "Review" → `review-article`, unknown → `research-article`
  - Test elemen opsional tidak muncul saat null: DOI, ORCID, subtitle, pages, license_url
  - Test `corresp="yes"` hanya pada corresponding author
  - Test `strip_tags` diterapkan pada abstract
  - Test DOI extraction dari teks referensi
  - Test ORCID cleaning dari URL prefix
  - Test keyword parsing: string CSV, JSON array
  - Test `InvalidArgumentException` saat `currentPublication` null
  - Test `InvalidArgumentException` saat `journal` null
  - _Requirements: 1.1, 1.2, 1.9, 2.1, 2.6, 2.7, 2.13, 5.5, 7.1, 7.2, 7.3, 7.4, 8.1, 8.2_

- [ ]* 6.1 Tulis property test untuk Property 1: Round-trip XML parse
  - **Property 1: Round-trip XML parse**
  - Buat test di `tests/Unit/Services/JatsXmlServiceTest.php`
  - Generate submission dengan variasi kelengkapan data (semua field, sebagian field, field minimal)
  - Assert: `DOMDocument::loadXML($xml)` tidak menghasilkan error untuk setiap variasi
  - **Validates: Requirements 7.1, 7.5, 1.11**

- [ ]* 6.2 Tulis property test untuk Property 2: Namespace declarations selalu hadir
  - **Property 2: Namespace declarations selalu hadir**
  - Generate submission dengan berbagai kombinasi data
  - Assert: elemen root selalu memiliki `xmlns:xlink`, `xmlns:ali`, dan `dtd-version="1.3"`
  - **Validates: Requirements 7.2, 7.3**

- [ ]* 6.3 Tulis property test untuk Property 3: BCP47 locale conversion
  - **Property 3: BCP47 locale conversion**
  - Generate locale string acak dalam format `xx_XX` (misal `id_ID`, `en_US`, `fr_FR`)
  - Assert: hasil `toBcp47()` cocok dengan regex `/^[a-z]{2,3}$/` (tanpa underscore, tanpa kode negara)
  - **Validates: Requirements 1.2**

- [ ]* 6.4 Tulis property test untuk Property 4: Elemen opsional tidak muncul saat data kosong
  - **Property 4: Optional elements absent when data is null**
  - Generate submission dengan subset acak dari field opsional yang di-set null
  - Assert: untuk setiap field null, XPath query ke elemen JATS yang bersesuaian mengembalikan NodeList kosong
  - **Validates: Requirements 7.4**

- [ ]* 6.5 Tulis property test untuk Property 7: Whitespace-only keywords ditolak
  - **Property 7: Whitespace-only keywords ditolak**
  - Generate keyword list yang mengandung string whitespace-only (spasi, tab, newline)
  - Assert: tidak ada elemen `<kwd>` yang berisi hanya whitespace dalam output XML
  - **Validates: Requirements 2.14**

- [ ]* 6.6 Tulis property test untuk Property 8: XML escaping karakter spesial
  - **Property 8: Special characters are properly escaped**
  - Generate string acak yang mengandung `<`, `>`, `&`, `"`, `'`
  - Inject ke title, abstract, nama author, affiliation
  - Assert: `DOMDocument::loadXML()` tetap berhasil (XML tetap well-formed)
  - **Validates: Requirements 1.9**

- [x] 7. Checkpoint — Pastikan semua tests lulus
  - Pastikan semua tests lulus, tanyakan kepada user jika ada pertanyaan.

- [x] 8. Buat `JatsXmlController`
  - Buat file `app/Http/Controllers/Public/JatsXmlController.php` dengan namespace `App\Http\Controllers\Public`
  - Inject `JatsXmlService` melalui constructor
  - Implementasikan method `article(string $journalSlug, mixed $article): Response`
    - Resolve `Journal` berdasarkan `$journalSlug` (slug), return 404 jika tidak ditemukan
    - Set journal context menggunakan helper `current_journal()` atau binding yang sesuai
    - Cari `Submission` berdasarkan `seq_id = $article` dan `journal_id`, return 404 jika tidak ditemukan
    - Return 404 jika `$submission->status !== Submission::STATUS_PUBLISHED`
    - Return 404 jika `$submission->currentPublication` null
    - Load relasi yang dibutuhkan: `currentPublication.authors`, `issue`, `journal`, `section`
    - Panggil `$this->jatsService->generate($submission)`
    - Return `xmlResponse($xml, "{$journal->slug}-{$submission->seq_id}.xml")`
  - Implementasikan method `workflowPreview(string $journalSlug, Submission $submission): Response`
    - Resolve `Journal` berdasarkan `$journalSlug`, return 404 jika tidak ditemukan
    - Verifikasi `$submission->journal_id === $journal->id`, return 404 jika tidak cocok
    - Load relasi yang dibutuhkan: `currentPublication.authors`, `issue`, `journal`, `section`
    - Panggil `$this->jatsService->generate($submission)`
    - Return `xmlResponse($xml, "{$journal->slug}-{$submission->seq_id}.xml")`
  - Implementasikan helper private `xmlResponse(string $xml, string $filename): Response`
    - Return `response($xml, 200, ['Content-Type' => 'application/xml', 'Content-Disposition' => "attachment; filename=\"{$filename}\""])`
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 4.1, 4.2, 4.4, 4.5, 4.6, 4.7_

- [x] 9. Daftarkan routes di `routes/web.php`
  - Tambahkan `use` statement untuk `App\Http\Controllers\Public\JatsXmlController` di bagian atas file
  - Di dalam blok `// 7. JOURNAL PUBLIC ROUTES` (prefix `{journal}`), tambahkan route publik setelah route `citation.bibtex`:
    ```php
    Route::get('/article/{article}/jats', [JatsXmlController::class, 'article'])
        ->name('journal.article.jats');
    ```
  - Di dalam blok `// 8. JOURNAL DASHBOARD & AUTH` (prefix `{journal}`), di dalam group `workflow` yang sudah ada (middleware `role:Editor|Section Editor|Journal Manager|Admin|Super Admin`), tambahkan route admin:
    ```php
    Route::get('/{submission}/jats', [JatsXmlController::class, 'workflowPreview'])
        ->name('journal.workflow.jats');
    ```
  - _Requirements: 3.1, 4.1_

- [ ]* 9.1 Tulis unit tests untuk `JatsXmlController`
  - Buat file `tests/Feature/Controllers/JatsXmlControllerTest.php`
  - Test route publik mengembalikan 200 + `Content-Type: application/xml` untuk artikel published
  - Test route publik mengembalikan 404 untuk artikel belum published
  - Test route publik mengembalikan 404 untuk `seq_id` yang tidak ada
  - Test header `Content-Disposition` berisi nama file format `{slug}-{seq_id}.xml`
  - Test route admin mengembalikan 200 untuk user dengan role editor/manager
  - Test route admin mengembalikan 403 untuk user tanpa role editor/manager
  - Test route admin dapat mengakses submission yang belum published (preview mode)
  - _Requirements: 3.2, 3.3, 3.4, 3.5, 4.2, 4.3, 4.7_

- [ ]* 9.2 Tulis property test untuk Property 9: Route publik mengembalikan 404 untuk submission tidak published
  - **Property 9: Route publik mengembalikan 404 untuk submission tidak published**
  - Generate request ke route publik dengan `seq_id` yang merujuk ke submission belum published atau tidak ada
  - Assert: response selalu HTTP 404
  - **Validates: Requirements 3.4, 3.5, 3.6**

- [ ]* 9.3 Tulis property test untuk Property 10: Route admin dapat mengakses submission belum published
  - **Property 10: Route admin dapat mengakses submission belum published**
  - Generate request ke route admin oleh editor/manager dengan submission dalam jurnal yang sama (berbagai status)
  - Assert: response selalu mengembalikan XML valid (bukan 404)
  - **Validates: Requirements 4.7**

- [x] 10. Tambahkan tombol "JATS XML" di `article.blade.php`
  - Buka file `resources/views/journal/public/article.blade.php`
  - Cari bagian sidebar yang berisi link download galley (PDF) dan citation (RIS, BibTeX)
  - Tambahkan link "JATS XML" setelah link download yang sudah ada, menggunakan pola yang konsisten:
    ```blade
    {{-- JATS XML Download --}}
    <a href="{{ route('journal.article.jats', ['journal' => $journal->slug, 'article' => $article->seq_id]) }}"
       class="flex items-center gap-2 text-sm text-slate-700 hover:text-primary-600 hover:underline">
        <i class="fa-solid fa-file-code text-slate-400"></i>
        JATS XML
    </a>
    ```
  - Pastikan link selalu tampil untuk semua artikel published tanpa kondisi autentikasi
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

- [x] 11. Checkpoint akhir — Pastikan semua tests lulus
  - Pastikan semua tests lulus, tanyakan kepada user jika ada pertanyaan.

## Catatan

- Tasks bertanda `*` bersifat opsional dan dapat dilewati untuk implementasi MVP yang lebih cepat
- Setiap task mereferensikan requirements spesifik untuk traceability
- `JatsXmlService` menggunakan `DOMDocument` (bukan string concatenation) untuk menjamin well-formedness XML
- Pola implementasi mengikuti konvensi yang sudah ada: `CrossrefExportController` untuk pola controller XML, `crossref_xml.blade.php` untuk pola pemrosesan data, `article.blade.php` untuk pola keyword dan reference parsing
- Route publik ditempatkan di section 7 (public routes), route admin di dalam group `workflow` section 8
