<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('User Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="flex justify-end mb-4">
                        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Add New User</a>
                    </div>

                    <table class="min-w-full bg-white dark:bg-gray-700 border dark:border-gray-600">
                        <thead>
                        <tr>
                            <th class="py-2 px-4 border-b dark:border-gray-600 text-left">Name</th>
                            <th class="py-2 px-4 border-b dark:border-gray-600 text-left">Email</th>
                            <th class="py-2 px-4 border-b dark:border-gray-600 text-left">Role</th>
                            <th class="py-2 px-4 border-b dark:border-gray-600 text-left">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="py-2 px-4 border-b dark:border-gray-600">{{ $user->name }}</td>
                                <td class="py-2 px-4 border-b dark:border-gray-600">{{ $user->email }}</td>
                                <td class="py-2 px-4 border-b dark:border-gray-600">{{ ucfirst($user->role) }}</td>
                                <td class="py-2 px-4 border-b dark:border-gray-600">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-500 hover:underline mr-2">Edit</a>

                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:underline" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>