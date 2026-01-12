@extends('layouts.app')

@section('title', 'Roles')

@section('content')
    <!-- Header -->
    <div class="flex justify-between items-start mb-8">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <span class="text-gray-400 text-sm font-medium">Journal Manager</span>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fa-solid fa-chevron-right text-gray-300 mx-2 text-xs"></i>
                            <span class="text-sm font-medium text-gray-500">Users & Roles</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fa-solid fa-chevron-right text-gray-300 mx-2 text-xs"></i>
                            <span class="text-sm font-medium text-indigo-600">Roles</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900">Roles & Permissions</h1>
            <p class="text-sm text-gray-500 mt-1">Manage user roles and their access levels within this journal.</p>
        </div>
        <div>
            <a href="{{ route($routePrefix . '.roles.create', ['journal' => $journal->slug]) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium transition-colors shadow-sm shadow-indigo-200">
                <i class="fa-solid fa-plus"></i>
                Create Role
            </a>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role Name
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Abbreviation</th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Permission
                        Level</th>
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php
                    // Display real roles if exist, otherwise mockup defaults
                    $rolesList =
                        $roles->count() > 0
                            ? $roles
                            : collect([
                                (object) ['name' => 'Journal Manager', 'id' => 1, 'is_dummy' => true],
                                (object) ['name' => 'Editor', 'id' => 2, 'is_dummy' => true],
                                (object) ['name' => 'Section Editor', 'id' => 3, 'is_dummy' => true],
                                (object) ['name' => 'Reviewer', 'id' => 4, 'is_dummy' => true],
                                (object) ['name' => 'Author', 'id' => 5, 'is_dummy' => true],
                                (object) ['name' => 'Reader', 'id' => 6, 'is_dummy' => true],
                                (object) ['name' => 'Subscription Manager', 'id' => 7, 'is_dummy' => true],
                            ]);
                @endphp

                @foreach ($rolesList as $role)
                    @php
                        // Determine visual properties based on role name
                        $roleName = $role->name;
                        $abbreviation = strtoupper(substr($roleName, 0, 2));
                        if (str_contains($roleName, 'Journal')) {
                            $abbreviation = 'JM';
                        }
                        if (str_contains($roleName, 'Section')) {
                            $abbreviation = 'SE';
                        }
                        if (str_contains($roleName, 'Subscription')) {
                            $abbreviation = 'SM';
                        }

                        $colorClass = match (true) {
                            str_contains($roleName, 'Manager') || str_contains($roleName, 'Admin')
                                => 'bg-red-50 text-red-700 border-red-100',
                            str_contains($roleName, 'Editor') => 'bg-blue-50 text-blue-700 border-blue-100',
                            $roleName == 'Reviewer' => 'bg-amber-50 text-amber-700 border-amber-100',
                            $roleName == 'Author' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                            default => 'bg-gray-50 text-gray-600 border-gray-100',
                        };

                        $levelName = match (true) {
                            str_contains($roleName, 'Manager') || str_contains($roleName, 'Admin') => 'Admin Level',
                            str_contains($roleName, 'Editor') => 'Editorial Level',
                            $roleName == 'Reviewer' => 'Reviewer Level',
                            default => 'Submission Level',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span
                                    class="inline-flex items-center justify-center h-8 w-8 rounded-full {{ $colorClass }} font-bold text-xs ring-4 ring-white">
                                    {{ $abbreviation }}
                                </span>
                                <span class="ml-3 text-sm font-medium text-gray-900">{{ $roleName }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $abbreviation }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $levelName == 'Admin Level' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $levelName }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end gap-3 items-center">
                                @if (isset($role->is_dummy) && $role->is_dummy)
                                    <span class="text-gray-400 cursor-default"
                                        title="Default System Role (Display Only)">Edit</span>
                                    <span class="text-gray-300">|</span>
                                    <span class="text-gray-400 cursor-default">Delete</span>
                                @else
                                    <a href="{{ route($routePrefix . '.roles.edit', ['journal' => $journal->slug, 'role' => $role->id]) }}"
                                        class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</a>
                                    <span class="text-gray-300">|</span>
                                    <form
                                        action="{{ route($routePrefix . '.roles.destroy', ['journal' => $journal->slug, 'role' => $role->id]) }}"
                                        method="POST" class="inline"
                                        onsubmit="return confirm('Delete this role? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
