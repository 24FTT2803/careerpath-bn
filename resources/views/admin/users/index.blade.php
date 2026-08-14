@extends('admin.layouts.admin')

@section('title', 'Users')

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">👤 Manage Users</h1>
            <p class="text-gray-600">Create, edit, and manage user accounts</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
            <i class="fas fa-user-plus"></i> Add User
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by name or email..."
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
            </div>
            <div>
                <select name="role" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                    <option value="">All Roles</option>
                    <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Student</option>
                    <option value="lecturer" {{ request('role') == 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition">
                <i class="fas fa-undo"></i> Reset
            </a>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">#</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Name</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Email</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Role</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Programme</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4">{{ $users->firstItem() + $index }}</td>
                            <td class="py-3 px-4 font-medium">{{ $user->name }}</td>
                            <td class="py-3 px-4">{{ $user->email }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded-full text-xs
                                    {{ $user->role == 'admin' ? 'bg-red-100 text-red-800' : 
                                       ($user->role == 'lecturer' ? 'bg-blue-100 text-blue-800' : 
                                       'bg-green-100 text-green-800') }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">{{ $user->programme ?? '-' }}</td>
                            <td class="text-center py-3 px-4">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-800 text-sm mr-2">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">
                                <i class="fas fa-users text-4xl text-gray-300 block mb-2"></i>
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $users->withQueryString()->links() }}
    </div>
</div>
@endsection