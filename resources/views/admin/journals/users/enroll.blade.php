@extends('layouts.app')

@section('title', 'Enroll User')

@section('content')
    <!-- Header -->
    <div class="mb-8">
        <nav class="flex mb-2" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <span class="text-gray-400 text-sm font-medium">Journal Manager</span>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fa-solid fa-chevron-right text-gray-300 mx-2 text-xs"></i>
                        <a href="{{ route($routePrefix . '.index', ['journal' => $journal->slug]) }}"
                            class="text-sm font-medium text-gray-500 hover:text-indigo-600">Users</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fa-solid fa-chevron-right text-gray-300 mx-2 text-xs"></i>
                        <span class="text-sm font-medium text-indigo-600">Enroll User</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">Enroll Existing User</h1>
        <p class="text-sm text-gray-500 mt-1">Add an existing user to <strong>{{ $journal->name }}</strong> with specific roles.</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm max-w-2xl">
        <form action="{{ route($routePrefix . '.enroll.store', ['journal' => $journal->slug]) }}" method="POST">
            @csrf

            <div class="p-6 space-y-6">
                <!-- User Selection -->
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select User <span class="text-red-500">*</span>
                    </label>
                    @if($availableUsers->isEmpty())
                        <div class="px-4 py-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
                            <i class="fa-solid fa-info-circle mr-2"></i>
                            All users are already enrolled in this journal.
                        </div>
                    @else
                        <select name="user_id" id="user_id" required
                            class="block w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('user_id') border-red-300 @enderror">
                            <option value="">-- Select a user --</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                <!-- Roles Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Assign Roles <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-3">Select one or more roles for this user in {{ $journal->name }}.</p>
                    
                    <div class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-4">
                        @foreach($roles as $role)
                            @php
                                $badgeClass = match($role->name) {
                                    'Admin', 'Journal Manager' => 'bg-red-50 text-red-700 border-red-200',
                                    'Editor', 'Section Editor' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'Reviewer' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'Author' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Reader' => 'bg-gray-50 text-gray-600 border-gray-200',
                                    default => 'bg-gray-50 text-gray-600 border-gray-200',
                                };
                            @endphp
                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                    {{ in_array($role->name, old('roles', ['Reader'])) ? 'checked' : '' }}
                                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium border {{ $badgeClass }}">
                                    {{ $role->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl flex justify-end gap-3">
                <a href="{{ route($routePrefix . '.index', ['journal' => $journal->slug]) }}"
                    class="px-4 py-2 border border-gray-300 text-gray-700 bg-white rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors">
                    Cancel
                </a>
                @if(!$availableUsers->isEmpty())
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium transition-colors shadow-sm shadow-indigo-200">
                        <i class="fa-solid fa-user-plus mr-2"></i>
                        Enroll User
                    </button>
                @endif
            </div>
        </form>
    </div>
@endsection
