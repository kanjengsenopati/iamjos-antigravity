@php
    $isId = app()->getLocale() === 'id';
    // Gunakan URL untuk menentukan konteks Super Admin (tanpa jurnal di URL)
    $isSuperAdminContext = !request()->route('journal') && auth()->check() && auth()->user()->hasRole(\App\Models\Role::ROLE_SUPERADMIN);
@endphp

@if($isSuperAdminContext)
    @extends('layouts.admin')
    @section('title', $isId ? 'Pengaturan Profil' : 'Profile Settings')
    @section('content')
        @include('profile.partials.edit-content')
    @endsection
@else
    <x-app-layout :journal="$journal ?? null">
        <x-slot name="title">{{ $isId ? 'Pengaturan Profil' : 'Profile Settings' }}</x-slot>
        @include('profile.partials.edit-content')
    </x-app-layout>
@endif
