@extends('layouts.master')
@push('css')
    <style>
        .menu-item {
            display: none !important;
        }
    </style>
@endpush
@section('content')
    <h1>Jaringan internet tidak tersedia, anda dalam mode Offline.</h1>
    <div class="card">
        <div class="card-body p-2">
            <h4 class="text-center">Menu Tersedia</h4>
            <div class="row">
                <div class="col-sm-3">
                    <a href="{{ route('offline-scan') }}" class="btn btn-lg btn-primary">SCAN MEMBERSHIP</a>
                </div>
            </div>
        </div>
    </div>
@endsection
