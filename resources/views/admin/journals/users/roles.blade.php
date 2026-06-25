@extends('layouts.app')

@section('title', 'Roles & Permissions')

@section('content')
<!-- Header -->
@include('admin.journals.users._header', ['activeTab' => 'roles'])

<!-- Roles Table (Alpine + Axios) -->
@include('livewire.admin.users.roles-table', [
'roles' => $roles,
'journal' => $journal,
'routePrefix' => $routePrefix
])

@endsection