@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
        <p class="text-sm text-gray-500 mt-1">Update profile information for {{ $user->name }}.</p>
    </div>

    <form action="{{ route($routePrefix . '.update', ['journal' => $journal->slug, 'user' => $user->id]) }}" method="POST"
        class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 max-w-3xl">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Multi-Role Selection --}}
            <div class="border-t border-gray-100 pt-6">
                <h3 class="text-sm font-medium text-gray-900 mb-2">Roles</h3>
                <p class="text-xs text-gray-500 mb-4">Select one or more roles for this user. A user can be an Author,
                    Reviewer, and Editor simultaneously.</p>

                @error('roles')
                    <p class="mb-3 text-xs text-red-500">{{ $message }}</p>
                @enderror

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @php
                        $roleDescriptions = [
                            'Journal Manager' => 'Full administrative access to the journal',
                            'Editor' => 'Can manage submissions and make editorial decisions',
                            'Section Editor' => 'Manages submissions within assigned sections',
                            'Reviewer' => 'Can review submissions and provide recommendations',
                            'Author' => 'Can submit manuscripts to the journal',
                            'Reader' => 'Basic access to published content',
                            'Admin' => 'Administrative access across all journals',
                        ];
                        $roleColors = [
                            'Journal Manager' => 'border-red-200 bg-red-50 hover:bg-red-100',
                            'Editor' => 'border-blue-200 bg-blue-50 hover:bg-blue-100',
                            'Section Editor' => 'border-blue-200 bg-blue-50 hover:bg-blue-100',
                            'Reviewer' => 'border-amber-200 bg-amber-50 hover:bg-amber-100',
                            'Author' => 'border-emerald-200 bg-emerald-50 hover:bg-emerald-100',
                            'Reader' => 'border-gray-200 bg-gray-50 hover:bg-gray-100',
                            'Admin' => 'border-purple-200 bg-purple-50 hover:bg-purple-100',
                        ];
                        // Use old values if there was a validation error, otherwise use current user roles
                        $selectedRoles = old('roles', $userRoleNames ?? []);
                    @endphp

                    @foreach ($roles as $role)
                        <label
                            class="relative flex items-start p-4 rounded-lg border cursor-pointer transition-all {{ $roleColors[$role->name] ?? 'border-gray-200 bg-gray-50 hover:bg-gray-100' }} {{ in_array($role->name, $selectedRoles) ? 'ring-2 ring-indigo-500' : '' }}">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                    {{ in_array($role->name, $selectedRoles) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            </div>
                            <div class="ml-3">
                                <span class="text-sm font-medium text-gray-900">{{ $role->name }}</span>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $roleDescriptions[$role->name] ?? 'Standard access role' }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route($routePrefix . '.index', ['journal' => $journal->slug]) }}"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium">Cancel</a>
            <button type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium shadow-sm">Save
                Changes</button>
        </div>
    </form>
@endsection
