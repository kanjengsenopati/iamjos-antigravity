@extends('layouts.master', ['title' => 'Data Reward Membership'])
@section('content')
<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <!--begin::Toolbar container-->
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <!--begin::Page title-->
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <!--begin::Title-->
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Data Reward Membership
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('membership.index') }}" class="text-muted text-hover-primary">Data
                                Membership</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">{{ request()->routeIs('membership-product.create') ?
                            'Tambah Reward Membership' :
                            'Edit Reward Membership' }}</li>
                        <!--end::Item-->
                    </ul>
                    <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
        </div>
        <!--end::Toolbar container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container container-xxl">
            <!--begin::Basic info-->
            <div class="card mb-5 mb-xl-10">
                <!--begin::Card header-->
                <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                    data-bs-target="#kt_account_profile_details" aria-expanded="true"
                    aria-controls="kt_account_profile_details">
                    <!--begin::Card title-->
                    <div class="card-title m-0">
                        <h3 class="fw-bold m-0">{{ request()->routeIs('membership-product.create') ? 'Tambah Reward
                            Membership' : 'Edit Reward Membership' }}</h3>
                        </h3>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--begin::Card header-->
                <!--begin::Content-->
                <div id="kt_account_settings_profile_details" class="collapse show">
                    <!--begin::Form-->
                    <form class="form" method="POST" enctype="multipart/form-data"
                        action="{{
                    request()->routeIs('membership-product.create') ? route('membership-product.store') : route('membership-product.update', @$membershipShopProduct->id) }}">
                        @csrf
                        <x-form.put-method />
                        <x-alert.alert-validation />
                        <!--begin::Card body-->
                        <div class="card-body border-top p-9">

                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6" for="name">Nama
                                    Produk</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-12">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <select name="shop_product_id" id="shop_product_id"
                                                class="form-select form-select-solid" required>
                                                <option value="">Pilih Produk</option>
                                                @foreach ($shopProducts as $shopProduct)
                                                <option value="{{ $shopProduct->id
                                                    }}" {{ @$membershipShopProduct->shop_product_id ==
                                                    $shopProduct->id ? 'selected' : '' }}>
                                                    {{ $shopProduct->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>

                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6" for="quantity">Jumlah
                                    Produk</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-12">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <input type="number" name="quantity" id="quantity"
                                            class="form-control form-control-solid" placeholder="Jumlah Produk"
                                            value="{{ @$membershipShopProduct->quantity ?? old('quantity') }}"
                                            required />
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Row-->
                            </div>
                            @if (request()->routeIs('membership-product.create'))
                            <input type="hidden" name="membership_id"
                                value="{{ request()->get('membership_id') ?? '' }}">
                            <input type="hidden" name="gym_class_bundling_id"
                                value="{{ request()->get('gym_class_bundling_id') ?? '' }}">
                            @endif
                            <!--end::Col-->
                        </div>
                </div>
                <!--end::Card body-->
                <!--begin::Actions-->
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ route('membership.index') }}"
                        class="btn btn-light btn-active-light-primary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">Simpan</button>
                </div>
                <!--end::Actions-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Basic info-->
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->
</div>
<!--end::Content wrapper-->
@endsection