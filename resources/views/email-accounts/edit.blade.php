@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-text-primary">Edit Email Account</h2>
                <a href="{{ route('accounts.email') }}" class="text-secondary hover:text-secondary/80">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>

            @if(session('error'))
                <div class="bg-danger/10 text-danger p-4 border-l-4 border-danger mb-6 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-card rounded-card shadow-card p-6">
                <form action="{{ route('accounts.email.update', $emailAccount) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="provider" class="block text-sm font-medium text-text-secondary mb-1">Provider</label>
                            <select id="provider" name="provider" class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                                @foreach($providers as $provider)
                                    <option value="{{ $provider }}" {{ $emailAccount->provider == $provider ? 'selected' : '' }}>{{ $provider }}</option>
                                @endforeach
                            </select>
                            @error('provider')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="email_address" class="block text-sm font-medium text-text-secondary mb-1">Email Address</label>
                            <input type="email" id="email_address" name="email_address" value="{{ old('email_address', $emailAccount->email_address) }}"
                                   class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                            @error('email_address')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-text-secondary mb-1">Password (leave blank to keep current)</label>
                            <div class="relative">
                                <input type="password" id="password" name="password"
                                       class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20"
                                       placeholder="Enter new password">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePasswordVisibility()">
                                    <i class="fas fa-eye" id="togglePassword"></i>
                                </div>
                            </div>
                            @error('password')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-text-secondary mb-1">Status</label>
                            <select id="status" name="status" class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                                <option value="active" {{ $emailAccount->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $emailAccount->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ $emailAccount->status == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="proxy_id" class="block text-sm font-medium text-text-secondary mb-1">Proxy (Optional)</label>
                            <select id="proxy_id" name="proxy_id" class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                                <option value="">No Proxy</option>
                                @foreach($availableProxies as $proxy)
                                    <option value="{{ $proxy->id }}" {{ $emailAccount->proxy_id == $proxy->id ? 'selected' : '' }}>
                                        {{ $proxy->ip_address }}:{{ $proxy->port }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proxy_id')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('accounts.email') }}" class="bg-gray-200 hover:bg-gray-300 text-text-primary py-2 px-4 rounded-button">
                            Cancel
                        </a>
                        <button type="submit" class="bg-primary hover:bg-primary/90 text-white py-2 px-4 rounded-button">
                            <i class="fas fa-save mr-2"></i> Update Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePassword');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
@endpush