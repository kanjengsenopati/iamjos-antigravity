# Dokumen Requirements

## Pendahuluan

Fitur ini mengimplementasikan ekspor JATS XML (Journal Article Tag Suite) untuk platform IAMJOS. JATS adalah standar XML ISO 9573-13 yang digunakan oleh PubMed Central, Crossref, Scopus, dan Web of Science untuk indexing dan harvesting metadata artikel jurnal akademik.

Fitur ini menyediakan tiga komponen utama: (1) `JatsXmlService` yang menghasilkan JATS 1.3 XML valid dari data submission, (2) route publik untuk download JATS XML artikel yang sudah dipublikasikan, dan (3) route admin untuk preview/download JATS XML dari halaman workflow. Selain itu, halaman artikel publik akan menampilkan tombol "Download JATS XML" di sidebar.

Implementasi mengikuti pola yang sudah ada di codebase (lihat `crossref_xml.blade.php` dan `article.blade.php`) dan kompatibel dengan standar OJS untuk interoperabilitas akademik.

---

## Glosarium

- **JATS**: Journal Article Tag Suite — standar XML ISO 9573-13 untuk representasi artikel jurnal akademik.
- **JatsXmlService**: Service class Laravel yang bertanggung jawab menghasilkan string JATS XML dari data submission.
- **Submission**: Model Eloquent yang merepresentasikan naskah yang disubmit ke jurnal, memiliki relasi ke `Publication`, `Issue`, `Section`, dan `Journal`.
- **Publication**: Model Eloquent yang merepresentasikan versi publikasi dari sebuah submission, berisi metadata seperti `title`, `abstract`, `doi`, `keywords`, `references`, `license_url`.
- **SubmissionAuthor**: Model Eloquent yang merepresentasikan penulis artikel, berisi `first_name`, `last_name`, `email`, `affiliation`, `country`, `orcid`, `is_corresponding`, `sort_order`.
- **Issue**: Model Eloquent yang merepresentasikan terbitan jurnal, berisi `volume`, `number`, `year`, `published_at`.
- **Journal**: Model Eloquent yang merepresentasikan jurnal, berisi `name`, `abbreviation`, `publisher`, `issn_print`, `issn_online`.
- **Section**: Model Eloquent yang merepresentasikan seksi/rubrik jurnal (misal: Research Article, Review).
- **ORCID**: Open Researcher and Contributor ID — identifikasi unik untuk peneliti.
- **DOI**: Digital Object Identifier — identifikasi unik untuk objek digital akademik.
- **article-type**: Atribut JATS pada elemen `<article>` yang menentukan tipe artikel (misal: `research-article`, `review-article`).
- **Editor/Manager**: Pengguna dengan peran editor atau manager pada jurnal tertentu di IAMJOS.
- **seq_id**: Nomor urut artikel dalam jurnal, digunakan sebagai identifier publik.
- **Galley**: File lampiran artikel (PDF, HTML, dll) yang tersedia untuk diunduh.

---

## Requirements

### Requirement 1: Generate JATS XML dari Data Submission

**User Story:** Sebagai developer platform, saya ingin ada service yang dapat menghasilkan JATS 1.3 XML valid dari data submission, sehingga output XML dapat digunakan oleh berbagai komponen sistem (route publik, route admin, dan integrasi masa depan).

#### Acceptance Criteria

1. THE `JatsXmlService` SHALL menyediakan method `generate(Submission $submission): string` yang mengembalikan string JATS 1.3 XML valid.
2. WHEN method `generate` dipanggil, THE `JatsXmlService` SHALL menghasilkan elemen root `<article>` dengan atribut `article-type`, `xml:lang` (dari `submission->locale` dalam format BCP47, misal `id_ID` → `id`), dan `dtd-version="1.3"`.
3. WHEN data `section->title` tersedia, THE `JatsXmlService` SHALL memetakan nilai section ke nilai `article-type` JATS yang sesuai (misal: "Research Article" → `research-article`, "Review" → `review-article`); IF tidak ada pemetaan yang cocok, THEN THE `JatsXmlService` SHALL menggunakan nilai default `research-article`.
4. THE `JatsXmlService` SHALL menghasilkan elemen `<front>` yang berisi `<journal-meta>` dan `<article-meta>`.
5. THE `JatsXmlService` SHALL menghasilkan `<journal-meta>` yang berisi: `<journal-id>` (dengan `journal-id-type="publisher-id"` berisi `journal->slug`), `<journal-title-group>` (berisi `<journal-title>` dan opsional `<abbrev-journal-title>`), `<issn>` untuk print dan/atau online jika tersedia, serta `<publisher>` berisi `<publisher-name>`.
6. THE `JatsXmlService` SHALL menghasilkan `<article-meta>` yang berisi semua sub-elemen wajib sesuai Requirement 2.
7. THE `JatsXmlService` SHALL menghasilkan elemen `<body/>` kosong (self-closing atau dengan komentar) karena konten artikel ada di PDF galley.
8. THE `JatsXmlService` SHALL menghasilkan elemen `<back>` yang berisi `<ref-list>` sesuai Requirement 5.
9. WHEN nilai string apapun dimasukkan ke dalam XML, THE `JatsXmlService` SHALL melakukan XML escaping yang benar (karakter `<`, `>`, `&`, `"`, `'`) untuk mencegah XML yang tidak valid.
10. THE `JatsXmlService` SHALL menghasilkan XML dengan deklarasi `<?xml version="1.0" encoding="UTF-8"?>` di baris pertama.
11. THE `JatsXmlService` SHALL menggunakan PHP `DOMDocument` atau `SimpleXMLElement` untuk membangun XML (bukan string concatenation manual) guna memastikan well-formedness.

---

### Requirement 2: Struktur Article-Meta yang Lengkap

**User Story:** Sebagai operator jurnal, saya ingin metadata artikel dalam JATS XML mencakup semua field yang dibutuhkan oleh indexer akademik (PMC, Scopus, WoS), sehingga artikel dapat diindeks dengan benar.

#### Acceptance Criteria

1. WHEN `publication->doi` tersedia, THE `JatsXmlService` SHALL menghasilkan `<article-id pub-id-type="doi">` berisi nilai DOI.
2. THE `JatsXmlService` SHALL menghasilkan `<title-group>` berisi `<article-title>` dari `publication->title`; WHEN `publication->subtitle` tersedia, THE `JatsXmlService` SHALL menyertakan `<subtitle>`.
3. THE `JatsXmlService` SHALL menghasilkan `<contrib-group>` berisi satu `<contrib contrib-type="author">` untuk setiap author dalam `publication->authors`, diurutkan berdasarkan `sort_order`.
4. WHEN data author dihasilkan, THE `JatsXmlService` SHALL menghasilkan `<name>` berisi `<surname>` dari `last_name` dan `<given-names>` dari `first_name`.
5. WHEN `author->affiliation` tersedia, THE `JatsXmlService` SHALL menghasilkan `<aff>` yang terhubung ke contrib melalui `xref` atau inline sesuai JATS 1.3.
6. WHEN `author->orcid` tersedia, THE `JatsXmlService` SHALL menghasilkan `<contrib-id contrib-id-type="orcid">` berisi URL ORCID lengkap (`https://orcid.org/{orcid}`), dengan membersihkan prefix URL yang mungkin sudah ada di nilai field.
7. WHEN `author->is_corresponding` bernilai true, THE `JatsXmlService` SHALL menambahkan atribut `corresp="yes"` pada elemen `<contrib>` yang bersangkutan.
8. THE `JatsXmlService` SHALL menghasilkan `<pub-date publication-format="electronic">` berisi `<year>`, `<month>`, dan `<day>` dari `publication->date_published` atau `issue->published_at` (dengan prioritas ke `publication->date_published`).
9. WHEN `issue->volume` tersedia, THE `JatsXmlService` SHALL menghasilkan `<volume>` berisi nilai volume.
10. WHEN `issue->number` tersedia, THE `JatsXmlService` SHALL menghasilkan `<issue>` berisi nilai nomor terbitan.
11. WHEN `publication->pages` tersedia dan mengandung tanda `-`, THE `JatsXmlService` SHALL memisahkan nilai menjadi `<fpage>` dan `<lpage>`; IF hanya ada satu nilai halaman, THEN THE `JatsXmlService` SHALL menghasilkan `<fpage>` saja.
12. THE `JatsXmlService` SHALL menghasilkan `<permissions>` berisi `<copyright-statement>` (format: "Copyright (c) {year} {holder}"), `<copyright-year>`, `<copyright-holder>`, dan WHEN `publication->license_url` tersedia, `<license>` berisi `<ali:license_ref>` dengan namespace `ali` (`http://www.niso.org/schemas/ali/1.0/`).
13. WHEN `publication->abstract` tersedia, THE `JatsXmlService` SHALL menghasilkan `<abstract>` berisi `<p>` dengan teks abstract yang sudah di-strip dari HTML tags.
14. WHEN `publication->keywords` tersedia, THE `JatsXmlService` SHALL menghasilkan `<kwd-group kwd-group-type="author-keywords">` berisi satu `<kwd>` per keyword; THE `JatsXmlService` SHALL menangani format keywords baik berupa string CSV, JSON array, maupun iterable object (mengikuti pola yang sudah ada di `article.blade.php`).

---

### Requirement 3: Route Publik Download JATS XML

**User Story:** Sebagai harvester akademik (PMC, Scopus, repository), saya ingin dapat mengunduh JATS XML artikel yang sudah dipublikasikan melalui URL yang dapat diprediksi, sehingga proses harvesting metadata dapat diotomasi.

#### Acceptance Criteria

1. THE `Router` SHALL mendaftarkan route `GET /{journal}/article/{article}/jats` yang dapat diakses publik tanpa autentikasi.
2. WHEN route diakses dengan `{article}` yang merupakan `seq_id` dari submission yang sudah dipublikasikan, THE `JatsXmlController` SHALL mengembalikan response dengan Content-Type `application/xml` dan body berisi JATS XML yang dihasilkan oleh `JatsXmlService`.
3. THE `JatsXmlController` SHALL menyertakan header `Content-Disposition: attachment; filename="{journal-slug}-{seq_id}.xml"` pada response download.
4. IF submission dengan `seq_id` yang diberikan tidak ditemukan dalam jurnal yang bersangkutan, THEN THE `JatsXmlController` SHALL mengembalikan HTTP 404.
5. IF submission ditemukan tetapi belum dipublikasikan (status bukan published), THEN THE `JatsXmlController` SHALL mengembalikan HTTP 404.
6. IF submission tidak memiliki `currentPublication`, THEN THE `JatsXmlController` SHALL mengembalikan HTTP 404.
7. WHEN route diakses, THE `JatsXmlController` SHALL me-resolve `{journal}` menggunakan slug jurnal dan `{article}` menggunakan `seq_id` submission, konsisten dengan pola routing yang sudah ada di platform.

---

### Requirement 4: Route Admin Preview/Download JATS XML dari Workflow

**User Story:** Sebagai editor atau manager jurnal, saya ingin dapat melihat preview JATS XML dari halaman workflow submission sebelum artikel dipublikasikan, sehingga saya dapat memverifikasi kelengkapan metadata sebelum indexing.

#### Acceptance Criteria

1. THE `Router` SHALL mendaftarkan route `GET /{journal}/workflow/{submission}/jats` yang hanya dapat diakses oleh pengguna yang terautentikasi.
2. WHEN route diakses oleh pengguna dengan peran editor atau manager pada jurnal yang bersangkutan, THE `JatsXmlController` SHALL mengembalikan response JATS XML dari submission tersebut.
3. IF pengguna yang mengakses route tidak memiliki peran editor atau manager pada jurnal yang bersangkutan, THEN THE `JatsXmlController` SHALL mengembalikan HTTP 403.
4. IF submission dengan ID yang diberikan tidak ditemukan atau tidak termasuk dalam jurnal yang bersangkutan, THEN THE `JatsXmlController` SHALL mengembalikan HTTP 404.
5. WHEN route admin diakses, THE `JatsXmlController` SHALL menggunakan `JatsXmlService` yang sama dengan route publik untuk menghasilkan XML, tanpa duplikasi logika.
6. THE `JatsXmlController` SHALL menyertakan header `Content-Disposition: attachment; filename="{journal-slug}-{seq_id}.xml"` pada response route admin, konsisten dengan route publik.
7. WHILE submission belum dipublikasikan, THE `JatsXmlController` SHALL tetap menghasilkan JATS XML menggunakan data yang tersedia (preview mode), tanpa mengembalikan error karena status belum published.

---

### Requirement 5: Struktur Back Matter dan Daftar Referensi

**User Story:** Sebagai indexer akademik, saya ingin daftar referensi artikel tersedia dalam format JATS XML yang terstruktur, sehingga citation linking dapat dilakukan secara otomatis.

#### Acceptance Criteria

1. WHEN `publication->references` tersedia dan tidak kosong, THE `JatsXmlService` SHALL menghasilkan elemen `<back>` berisi `<ref-list>`.
2. THE `JatsXmlService` SHALL memisahkan nilai `publication->references` menjadi referensi individual dengan memisahkan berdasarkan newline (`\n`), mengikuti pola yang sudah ada di `article.blade.php`.
3. THE `JatsXmlService` SHALL memfilter baris kosong dan baris dengan panjang kurang dari 5 karakter dari daftar referensi.
4. WHEN setiap referensi diproses, THE `JatsXmlService` SHALL menghasilkan `<ref id="ref-{n}">` (dimana `n` adalah nomor urut mulai dari 1) berisi `<mixed-citation>` dengan teks referensi mentah.
5. WHEN teks referensi mengandung URL DOI (pola `10.\d{4,}/`), THE `JatsXmlService` SHALL mengekstrak DOI tersebut dan menyertakannya sebagai `<pub-id pub-id-type="doi">` di dalam `<mixed-citation>`.
6. IF `publication->references` kosong atau null, THEN THE `JatsXmlService` SHALL menghasilkan elemen `<back/>` kosong atau menghilangkan elemen `<back>` sama sekali.

---

### Requirement 6: Tombol Download JATS XML di Halaman Artikel Publik

**User Story:** Sebagai pembaca atau peneliti, saya ingin ada tombol "Download JATS XML" yang mudah ditemukan di halaman artikel, sehingga saya dapat mengunduh metadata artikel dalam format standar untuk digunakan di reference manager atau repository.

#### Acceptance Criteria

1. THE `ArticleView` SHALL menampilkan tombol atau link "Download JATS XML" di sidebar halaman artikel publik untuk setiap artikel yang sudah dipublikasikan.
2. WHEN tombol "Download JATS XML" diklik, THE `ArticleView` SHALL mengarahkan pengguna ke route `GET /{journal}/article/{article}/jats` yang menghasilkan file XML untuk diunduh.
3. THE `ArticleView` SHALL menempatkan tombol "Download JATS XML" di bagian sidebar yang berisi link download lainnya (seperti PDF galley), konsisten dengan desain OJS flat yang sudah ada.
4. THE `ArticleView` SHALL menggunakan ikon yang sesuai (misal: ikon file XML atau ikon download) dan label teks "JATS XML" yang jelas.
5. WHILE pengguna mengakses halaman artikel yang sudah dipublikasikan, THE `ArticleView` SHALL selalu menampilkan tombol JATS XML tanpa memerlukan autentikasi.

---

### Requirement 7: Validitas dan Kompatibilitas XML

**User Story:** Sebagai operator jurnal, saya ingin JATS XML yang dihasilkan valid secara struktural dan kompatibel dengan validator PMC dan Crossref, sehingga submission ke indexer tidak ditolak karena error format.

#### Acceptance Criteria

1. THE `JatsXmlService` SHALL menghasilkan XML yang well-formed sesuai spesifikasi XML 1.0 (semua tag ditutup, atribut dikutip, karakter spesial di-escape).
2. THE `JatsXmlService` SHALL menyertakan namespace declaration yang diperlukan pada elemen root `<article>`: `xmlns:xlink="http://www.w3.org/1999/xlink"` dan `xmlns:ali="http://www.niso.org/schemas/ali/1.0/"`.
3. THE `JatsXmlService` SHALL menyertakan atribut `dtd-version="1.3"` pada elemen root `<article>`.
4. WHEN field yang opsional (seperti DOI, ORCID, subtitle, pages) tidak tersedia, THE `JatsXmlService` SHALL menghilangkan elemen tersebut dari output XML (tidak menghasilkan elemen kosong).
5. THE `JatsXmlService` SHALL menghasilkan XML yang dapat di-parse ulang oleh PHP `DOMDocument::loadXML()` tanpa error (round-trip parse test).
6. WHEN abstract mengandung HTML tags, THE `JatsXmlService` SHALL melakukan strip_tags sebelum memasukkan konten ke dalam elemen `<abstract><p>`, konsisten dengan pola di `crossref_xml.blade.php`.

---

### Requirement 8: Penanganan Error dan Edge Cases

**User Story:** Sebagai developer platform, saya ingin `JatsXmlService` menangani data yang tidak lengkap atau tidak valid dengan graceful, sehingga sistem tidak crash ketika data submission tidak sempurna.

#### Acceptance Criteria

1. IF `submission->currentPublication` bernilai null, THEN THE `JatsXmlService` SHALL melempar `InvalidArgumentException` dengan pesan yang deskriptif.
2. IF `submission->journal` bernilai null, THEN THE `JatsXmlService` SHALL melempar `InvalidArgumentException` dengan pesan yang deskriptif.
3. WHEN `publication->authors` kosong atau null, THE `JatsXmlService` SHALL menghasilkan `<contrib-group>` kosong tanpa error.
4. WHEN nilai locale submission tidak valid atau null, THE `JatsXmlService` SHALL menggunakan nilai default `"en"` untuk atribut `xml:lang`.
5. WHEN `publication->keywords` berformat tidak dikenal, THE `JatsXmlService` SHALL menghasilkan `<kwd-group>` kosong tanpa melempar exception.
6. WHEN `publication->pages` berformat tidak standar (tidak mengandung `-`), THE `JatsXmlService` SHALL menghasilkan `<fpage>` dengan nilai penuh tanpa `<lpage>`.
