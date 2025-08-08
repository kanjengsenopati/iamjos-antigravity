@extends('layouts.master', ['main' => 'Data Produk', 'title' => request()->routeIs('shop-product.create') ?
'Tambah
Produk' : 'Edit Produk'])
@push('style')
<style>
    .cursor-pointer {
        cursor: pointer;
    }
</style>
@endpush
@section('content')
<!--begin::Container-->
<div id="kt_content_container" class="app-container container-xxl pt-6">
    <!--begin::Contacts App- Add New Contact-->
    <div class="row g-7">
        <!--begin::Content-->
        <div class="col-xl-12">
            <!--begin::Contacts-->
            <div class="card h-lg-100" id="kt_contacts_main">
                <!--begin::Card header-->
                <div class="card-header" id="kt_chat_contacts_header">
                    <!--begin::Card title-->
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3">{{ request()->routeIs('shop-product.create') ? 'Tambah
                            Produk'
                            :
                            'Edit Produk' }}</span>
                    </h3>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-5">
                    <!--begin::Form-->
                    <x-alert.alert-validation />
                    <form id="shop-product"
                        action="{{ request()->routeIs('shop-product.create') ? route('shop-product.store') : route('shop-product.update', @$shopProduct->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <x-form.put-method />
                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="image">
                                <span class="required text-dark">Foto Produk</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan Foto Produk"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <div class="row d-flex justify-content-center">
                                @if (isset($shopProduct->shopProductImages))
                                @php $imageCount = count($shopProduct->shopProductImages); @endphp
                                @foreach ($shopProduct->shopProductImages as $key => $image)
                                <div class="col-lg-4 col-sm-6">
                                    <x-form.image-upload label="Foto {{ $key + 1 }}" name="image[{{ $image->id }}]"
                                        :value="$image->image ?? null" nullable='1' id="imageupload{{ $key + 1 }}"
                                        :isDelete="true"
                                        :deleteRoute="route('shop-product.delete-image', $image->id)" />
                                </div>
                                @endforeach
                                @else
                                @php $imageCount = 0; @endphp
                                @endif

                                {{-- Tambahkan formulir pengunggahan gambar kosong jika jumlah gambar kurang
                                dari 5 --}}
                                @php $remainingImages = 5 - $imageCount; @endphp
                                @foreach (range(1, $remainingImages) as $key)
                                <div class="col-lg-4 col-sm-6">
                                    <x-form.image-upload label="Foto {{ $imageCount + $key }}" name="image[]"
                                        :value="$shopProduct->image ?? null" nullable='1'
                                        id="imageupload{{ $imageCount + $key }}" :isDelete="true" />
                                </div>
                                @endforeach

                            </div>
                            <!--end::Input-->
                        </div>

                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="name">
                                <span class="required text-dark">Kategori</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan Nama Produk"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="shop_category_id" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ @$shopProduct->shop_category_id ==
                                    $category->id
                                    ? 'selected' : '' }}>
                                    {{ $category->name }}</option>
                                @endforeach
                            </select>
                            <!--end::Input-->
                        </div>

                        @if (@$shopProduct->gym_place_id == null)
                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="gym_place_id">
                                <span class="required text-dark">Tempat Gym</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Pilih Tempat Gym"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="gym_place_id" id="" class="form-select" required>
                                <option value="">Pilih Tempat Gym</option>
                                @foreach ($gym_places as $gymPlace)
                                <option value="{{ $gymPlace->id }}"
                                    {{@$gymPlace->id == (@$shopProduct->gym_place_id ?? auth()->user()->gym_place_id) ? 'selected' : ''}}>
                                    {{ $gymPlace->name }}
                                </option>
                                @endforeach
                            </select>
                            {{-- <x-form.gym-place :value="@$shopProduct->gym_place_id ?? null"
                                class="form-control" /> --}}
                        </div>
                        @endif
                        <!--begin::Separator-->

                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="name">
                                <span class="required text-dark">Nama Produk</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan Nama Produk"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" class="form-control" id="name" placeholder="Contoh: Minuman Protein"
                                name="name" value="{{ @$shopProduct->name ?? old('name') }}" required />
                            <!--end::Input-->
                        </div>

                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="name_en">
                                <span class="required text-dark">Nama Produk (English)</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan Nama Produk (English)"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <div class="row">
                                <div class="col-10">
                                    <input type="text" class="form-control" id="name_en" placeholder="Contoh: Sport Equipment"
                                        name="name_en" value="{{ @$shopProduct->name_en ?? old('name_en') }}" />
                                </div>
                                <div class="col-2">
                                    <button type="button" onclick="translateNameEnglish()" class="btn btn-translate">Translate English</button>
                                </div>
                            </div>
                            <!--end::Input-->
                        </div>

                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="name_cn">
                                <span class="required text-dark">Nama Produk (Chinese)</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan Nama Produk (Chinese)"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <div class="row">
                                <div class="col-10">
                                    <input type="text" class="form-control" id="name_cn" placeholder=""
                                    name="name_cn" value="{{ @$shopProduct->name_cn ?? old('name_cn') }}" />
                                </div>
                                <div class="col-2">
                                    <button type="button" onclick="translateNameChinese()" class="btn btn-translate">Translate Chinese</button>
                                </div>
                            </div>
                            <!--end::Input-->
                        </div>

                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="description">
                                <span class="required text-dark">Deskripsi Produk</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan Deskripsi Produk"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <textarea class="form-control" id="description" placeholder="Contoh: Minuman Protein 500ml"
                                rows="6"
                                name="description">{{ @$shopProduct->description ?? old('description') }}</textarea>
                            <!--end::Input-->
                        </div>

                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="description_en">
                                <span class="required text-dark">Deskripsi Produk (English)</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan Deskripsi Produk dalam Bahasa Inggris"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <div class="row">
                                 <div class="col-10">
                                    <textarea class="form-control" id="description_en"
                                        placeholder="Contoh: Minuman Protein 500ml" rows="6"
                                        name="description_en">{{ @$shopProduct->description_en ?? old('description_en') }}</textarea>
                                </div>
                                <div class="col-2">
                                    <button type="button" onclick="translateDescriptionEnglish()" class="btn btn-translate">Translate English</button>
                                </div>
                            </div>
                            <!--end::Input-->
                        </div>
                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="description_en">
                                <span class="required text-dark">Deskripsi Produk (Chinese)</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan Deskripsi Produk dalam Bahasa China"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <div class="row">
                                <div class="col-10">
                                    <textarea class="form-control" id="description_cn"
                                        placeholder="Contoh: Minuman Protein 500ml" rows="6"
                                        name="description_cn">{{ @$shopProduct->description_cn ?? old('description_cn') }}</textarea>
                                </div>
                                <div class="col-2">
                                    <button type="button" onclick="translateDescriptionChinese()" class="btn btn-translate">Translate Chinese</button>
                                </div>
                            </div>
                            <!--end::Input-->
                        </div>
                        <div class="fv-row mb-6">
                            <div class="row">
                                <div class="col-lg-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="price">
                                        <span class="required text-dark">Harga Produk</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Harga Produk"></i>
                                    </label>
                                    <!--end::Label-->
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="price" value="{{ old('price', @$shopProduct->price) }}"
                                            class="form-control input-money" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="discount_price">
                                        <span class=" text-dark">Harga Diskon</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Harga Diskon"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="discount_price"
                                            value="{{ old('discount_price', @$shopProduct->discount_price) }}"
                                            class="form-control input-money">
                                    </div>
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6 mt-3">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label" for="start_date_discount">
                                                <span class="text-dark">Mulai Diskon</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Masukkan Tanggal Mulai Diskon"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="datetime-local" class="form-control" id="start_date_discount"
                                                name="start_date_discount"
                                                value="{{ @$shopProduct->start_date_discount ?? old('start_date_discount') }}" />
                                        </div>
                                        <div class="col-lg-6">
                                            <label class="fs-6 fw-bold form-label" for="end_date_discount">
                                                <span class="text-dark">Selesai Diskon</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Masukkan Tanggal Selesai Diskon"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="datetime-local" class="form-control" id="end_date_discount"
                                                name="end_date_discount"
                                                value="{{ @$shopProduct->end_date_discount ?? old('end_date_discount') }}" />
                                        </div>
                                    </div>

                                    <!--end::Input-->
                                </div>
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="is_rent">
                                        <span class="text-dark">Apakah Produk Bisa Disewa?</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Pilih jika produk bisa disewa"></i>
                                    </label>
                                    <!--end::Label-->
                                    @php
                                        $isRentValue = old('is_rent', isset($shopProduct) ? $shopProduct->is_rent : '');
                                    @endphp
                                    <select name="is_rent" id="is_rent" class="form-control">
                                        <option value="" {{ $isRentValue === '' ? 'selected' : '' }}>--Pilih Opsi--</option>
                                        <option value="1" {{ $isRentValue == 1 ? 'selected' : '' }}>Bisa Disewa</option>
                                        <option value="0" {{ $isRentValue == 0 || $isRentValue === '0' ? 'selected' : '' }}>Tidak Bisa Disewa</option>
                                    </select>
                                </div>
                                <div class="fv-row mb-6" id="input-stock">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label" for="stock">
                                        <span class="required text-dark">Stok Produk</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Stok Produk"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="number" class="form-control" id="stock" placeholder="Contoh: 100"
                                        name="stock" value="{{ @$shopProduct->stock ?? old('stock') }}" />
                                    <!--end::Input-->
                                </div>
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="is_active">
                                        <span class="required">Status Publish</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Pilih status publish"></i>
                                    </label>
                                    <!--end::Label-->
                                    <select name="is_active" id="is_active" class="form-control" required>
                                        <option value="">--Pilih Status--</option>
                                        @if (request()->routeIs('shop-product.create'))
                                            <option value="1" selected>di Publish</option>
                                            <option value="0">di Draft
                                        @else
                                            <option {{@$shopProduct->is_active == 1 ? 'selected' : ''}} value="1">di Publish</option>
                                            <option {{@$shopProduct->is_active == 0 ? 'selected' : ''}} value="0">di Draft
                                        @endif
                                        </option>
                                    </select>
                                </div>
                                <div class="fv-row mb-6">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <button type="button" class="btn btn-primary btn-sm" id="add_variant">
                                                <i class="fa fa-plus text-white"></i>Variasi
                                            </button>
                                            {{-- add button delete --}}
                                            <button type="button" class="btn btn-danger btn-sm" id="delete_variant">
                                                <i class="fa fa-trash text-white"></i>Hapus Variasi
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="variant-form">
                                    @if (isset($shopProduct->shopProductVariants))
                                    @foreach ($shopProduct->shopProductVariants as $variant)
                                    <div class="variant-menu">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="fv-row mb-6">
                                                    <label class="fs-6 fw-bold form-label" for="variant_name">
                                                        <span class="text-dark">Nama Variasi</span>
                                                    </label>
                                                    <input type="text" class="form-control" id="variant_name"
                                                        placeholder="Contoh: Ukuran" name="variant_name[]"
                                                        value="{{ $variant->name }}" />
                                                </div>
                                            </div>
                                            <div class="col-lg-6 en-feature">
                                                <div class="fv-row mb-6">
                                                    <label class="fs-6 fw-bold form-label" for="variant_name_en">
                                                        <span class="text-dark">Nama Variasi (English)</span>
                                                    </label>
                                                    <input type="text" class="form-control" id="variant_name_en"
                                                        placeholder="Contoh: Size" name="variant_name_en[]"
                                                        value="{{ $variant->name_en }}" />
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="fv-row mb-6">
                                                    <label class="fs-6 fw-bold form-label" for="variant_stock">
                                                        <span class=" text-dark">Stok Variasi</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7"
                                                            data-bs-toggle="tooltip"
                                                            title="Masukkan Stok Variasi Produk"></i>
                                                    </label>
                                                    <input type="number" class="form-control" id="variant_stock"
                                                        placeholder="Contoh: 100" name="variant_stock[]"
                                                        value="{{ $variant->stock }}" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    @endif
                                </div>

                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('shop-product.index') }}">
                                        <button type="button" data-kt-contacts-type="cancel"
                                            class="btn btn-secondary me-3">Batal</button>
                                    </a>
                                    <!--end::Button-->
                                    <!--begin::Button-->
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-primary btn-sm"
                                        id="btn-submit">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Mohon Tunggu...
                                            <span
                                                class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                    <!--end::Button-->
                                </div>
                                <!--end::Action buttons-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Contacts-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Contacts App- Add New Contact-->
</div>
<!--end::Container-->
</div>
<!--end::Content-->
<!--end::Wrapper-->
@endsection
@push('js')
<script>
    // Function to handle translation
    function handleTranslation(selector, targetSelector) {
        $(selector).on('change', () => translate(selector, targetSelector));
    }

    // Function to toggle stock visibility based on variant-menu existence
    function toggleStockVisibility() {
        if ($('.variant-menu').length > 0) {
            $('#input-stock').hide();
        } else {
            $('#input-stock').show();
        }
    }

    // Function to add variant form dynamically
    function addVariantForm() {
        $('#variant-form').append(`
            <div class="variant-menu">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="fv-row mb-6">
                            <label class="fs-6 fw-bold form-label" for="variant_name">
                                <span class="text-dark">Nama Variasi</span>
                            </label>
                            <input type="text" class="form-control" id="variant_name" placeholder="Contoh: Ukuran" name="variant_name[]" value="{{ @$shopProduct->variant_name }}" />
                        </div>
                    </div>
                    <div class="col-lg-6 en-feature">
                        <div class="fv-row mb-6">
                            <label class="fs-6 fw-bold form-label" for="variant_name">
                                <span class="text-dark">Nama Variasi (English)</span>
                            </label>
                            <input type="text" class="form-control" id="variant_name_en" placeholder="Contoh: Size" name="variant_name_en[]" value="{{ @$shopProduct->variant_name_en }}" />
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="fv-row mb-6">
                            <label class="fs-6 fw-bold form-label" for="variant_stock">
                                <span class=" text-dark">Stok Variasi</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Masukkan Stok Variasi Produk"></i>
                            </label>
                            <input type="number" class="form-control" id="variant_stock" placeholder="Contoh: 100" name="variant_stock[]" value="{{ @$shopProduct->variant_stock }}" />
                        </div>
                    </div>
                </div>
            </div>`
        );
        toggleStockVisibility(); // Toggle stock visibility
    }

    // Function to delete last variant form
    function deleteLastVariantForm() {
        $('#variant-form').children('.variant-menu').last().remove();
        toggleStockVisibility(); // Toggle stock visibility
    }

    // Event handlers
    $(document).ready(function() {
        // handleTranslation('#name', '#name_en');
        // handleTranslation('#description', '#description_en');
        toggleStockVisibility(); // Toggle stock visibility
        $('#add_variant').on('click', addVariantForm);
        $('#delete_variant').on('click', deleteLastVariantForm);
        $('#btn-submit').on('click', function () {
            $('.input-money').unmask();
        });

        // delete image product
        $('.delete-img').on('click', function(e) {
            e.preventDefault()
            Swal.fire({
                title: 'Hapus Data',
                text: 'Anda yakin akan menghapus data ini? Data yang telah dihapus tidak dapat dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-sm fw-semibold btn-primary',
                    cancelButton: 'btn btn-sm fw-semibold btn-active-light-primary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const id = $(this).data('delete')
                    const el = $(`#imagepreview${id}`)
                    const input = $(`#imageupload${id}`)
                    el.css('background', '')
                    input.val('')
                }
            });
        })

        if(window.location.href.includes('/shop-product')) {
            let indexNotHaveBg = []
            let result = null
            let isUpdate = false

            function iSAnyBg(bg) {
                return bg.includes('storage/images/shop-product') || bg.includes('data:image') 
            }

            // function upload image produk
            $('.imgUpload').on('change', function() {
                const file = $(this)[0].files[0]
                const preview = $(this).parent()
                const presetBg = preview.css('background') // Adjust selector as needed
                // console.log(presetBg)
                // console.log(iSAnyBg(presetBg))

                // jika ada bg maka akan mengupdate 
                if(iSAnyBg(presetBg)) {
                    generateImage(preview, file)
                } else {
                    indexNotHaveBg = []
                    const data = $('.img-preview')
    
                    $('.img-preview').each(function(i, obj) {
                        var background = $(obj).css('background');
                        if(!iSAnyBg(background)) indexNotHaveBg.push(i+1)
                    });

                    // console.log(indexNotHaveBg)
                    if(indexNotHaveBg.length>0) {
                         const image_holder = $(`#imagepreviewimageupload${indexNotHaveBg[0]}`)
                         generateImage(image_holder, file)
                    } 
                }
            })

            // preview image
            function generateImage(image, file) {
                if (typeof(FileReader) != "undefined") {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        image.attr('style', 'background:url(' + e.target.result + ')')
                    }
                    image.show();
                    reader.readAsDataURL(file);
                } else {
                    console.log("This browser does not support FileReader.");
                }
            }
        } else {
            console.log('not shop page');
        }
    });

    function translateNameEnglish() {
        translate('#name', '#name_en');
    }
    
    function translateNameChinese() {
        translateChinese('#name', '#name_cn');
    }

    function translateDescriptionEnglish() {
        translator('#description', '#description_en');
    }
    
    function translateDescriptionChinese() {
        translateChinesePost('#description', '#description_cn');
    }

    $('form').on('submit', function(e) {
        $(".input-money").each(function() {
            var str = $(this).val();
            var newValue = str.replace(/,/g, '');
            $(this).val(newValue);
        });
    });
</script>
@endpush