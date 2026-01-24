@extends('layouts.app')

@section('title', 'Roles & Permissions')

@section('content')
    <!-- Header -->
    <div class="flex justify-between items-start mb-8">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <span class="text-gray-400 text-sm font-medium">Users</span>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fa-solid fa-chevron-right text-gray-300 mx-2 text-xs"></i>
                            <span class="text-sm font-medium text-indigo-600">Roles & Permissions</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900">Roles</h1>
            <p class="text-sm text-gray-500 mt-1">Configure user roles and their workflow access levels.</p>
        </div>
        <div>
            <a href="{{ route($routePrefix . '.roles.create', ['journal' => $journal->slug]) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium transition-colors shadow-sm shadow-indigo-200">
                <i class="fa-solid fa-plus"></i>
                Create New Role
            </a>
        </div>
    </div>

    <!-- OJS-Style Matrix Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/4">
                            Role Name
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Permission Level
                        </th>
                        <th scope="col" class="px-4 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">
                            <div class="flex flex-col items-center gap-1" title="Submission Stage Access">
                                <i class="fa-solid fa-file-upload text-gray-400 text-sm"></i>
                                <span>Subm.</span>
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">
                            <div class="flex flex-col items-center gap-1" title="Review Stage Access">
                                <i class="fa-solid fa-glasses text-gray-400 text-sm"></i>
                                <span>Review</span>
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">
                            <div class="flex flex-col items-center gap-1" title="Copyediting Stage Access">
                                <i class="fa-solid fa-pen-nib text-gray-400 text-sm"></i>
                                <span>Copy.</span>
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">
                            <div class="flex flex-col items-center gap-1" title="Production Stage Access">
                                <i class="fa-solid fa-print text-gray-400 text-sm"></i>
                                <span>Prod.</span>
                            </div>
                        </th>
                        <th scope="col" class="relative px-6 py-4 w-20">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($roles as $role)
                        @php
                            // Determine Level Text
                            $levelText = match($role->permission_level ?? 3) {
                                1 => 'Journal Manager',
                                2 => 'Section Editor',
                                3 => 'Assistant',
                                0 => 'Site Admin',
                                default => 'Assistant'
                            };

                            // Role specific styling
                            $isManager = $role->permission_level <= 1;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors group {{ $loop->last ? '' : 'border-b border-gray-100' }}">
                            {{-- Role Name --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                        {{ $role->name }}
                                    </span>
                                </div>
                            </td>

                            {{-- Permission Level --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium {{ $isManager ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-slate-600' }}">
                                    {{ $levelText }}
                                </span>
                            </td>

                            {{-- STAGE 1: SUBMISSION --}}
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($role->permit_submission)
                                    <i class="fa-solid fa-square-check text-blue-600 text-lg"></i>
                                @else
                                    <span class="text-gray-200 font-light text-xl">-</span>
                                @endif
                            </td>

                            {{-- STAGE 2: REVIEW --}}
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($role->permit_review)
                                    <i class="fa-solid fa-square-check text-blue-600 text-lg"></i>
                                @else
                                    <span class="text-gray-200 font-light text-xl">-</span>
                                @endif
                            </td>

                            {{-- STAGE 3: COPYEDITING --}}
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($role->permit_copyediting)
                                    <i class="fa-solid fa-square-check text-blue-600 text-lg"></i>
                                @else
                                    <span class="text-gray-200 font-light text-xl">-</span>
                                @endif
                            </td>

                            {{-- STAGE 4: PRODUCTION --}}
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($role->permit_production)
                                    <i class="fa-solid fa-square-check text-blue-600 text-lg"></i>
                                @else
                                    <span class="text-gray-200 font-light text-xl">-</span>
                                @endif
                            </td>

                            {{-- ACTIONS --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end items-center gap-4">
                                    <a href="{{ route($routePrefix . '.roles.edit', ['journal' => $journal->slug, 'role' => $role->id]) }}" 
                                       class="text-blue-600 hover:text-blue-900 transition-colors" title="Edit Role">
                                        <i class="fa-solid fa-pen-to-square text-base"></i>
                                    </a>
                                    
                                    <form action="{{ route($routePrefix . '.roles.destroy', ['journal' => $journal->slug, 'role' => $role->id]) }}" 
                                          method="POST" 
                                          class="inline-block"
                                          onsubmit="return confirm('Are you sure you want to delete this role? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 transition-colors" title="Delete Role">
                                            <i class="fa-solid fa-trash-can text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                No roles defined.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
