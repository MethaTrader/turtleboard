{{-- resources/views/proxies/edit.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-text-primary">Edit Proxy</h2>
                <a href="{{ route('accounts.proxy') }}" class="text-secondary hover:text-secondary/80">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>

            @if(session('error'))
                <div class="bg-danger/10 text-danger p-4 border-l-4 border-danger mb-6 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-card rounded-card shadow-card p-6">
                <form action="{{ route('accounts.proxy.update', $proxy) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="ip_address" class="block text-sm font-medium text-text-secondary mb-1">IP Address</label>
                            <input type="text" id="ip_address" name="ip_address" value="{{ old('ip_address', $proxy->ip_address) }}"
                                   class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                            @error('ip_address')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="port" class="block text-sm font-medium text-text-secondary mb-1">Port</label>
                            <input type="number" id="port" name="port" value="{{ old('port', $proxy->port) }}"
                                   class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20"
                                   min="1" max="65535">
                            @error('port')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="username" class="block text-sm font-medium text-text-secondary mb-1">Username (Optional)</label>
                            <input type="text" id="username" name="username" value="{{ old('username', $proxy->username) }}"
                                   class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                            @error('username')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-text-secondary mb-1">Password (Optional)</label>
                            <input type="text" id="password" name="password" value="{{ old('password', $proxy->password) }}"
                                   class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                            @error('password')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center bg-gray-50 p-4 rounded-md">
                            <div class="mr-4">
                                <div class="text-lg font-semibold">Current Status:</div>
                                @if($proxy->validation_status === 'valid')
                                    <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-success/10 text-success">
                                        <i class="fas fa-check-circle mr-1"></i> Valid
                                    </span>
                                @elseif($proxy->validation_status === 'invalid')
                                    <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-danger/10 text-danger">
                                        <i class="fas fa-times-circle mr-1"></i> Invalid
                                    </span>
                                @else
                                    <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-warning/10 text-warning">
                                        <i class="fas fa-hourglass-half mr-1"></i> Pending
                                    </span>
                                @endif
                            </div>

                            <div class="flex-1">
                                <div class="text-sm">
                                    @if($proxy->last_validation_date)
                                        <p>Last Validated: {{ $proxy->last_validation_date->format('M d, Y H:i') }}</p>
                                    @else
                                        <p>Never Validated</p>
                                    @endif

                                    @if($proxy->response_time)
                                        <p>Response Time: {{ $proxy->response_time }} ms</p>
                                    @endif

                                    @if($proxy->geolocation)
                                        <p>Location: {{ $proxy->geolocation }}</p>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <form action="{{ route('accounts.proxy.validate', $proxy) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button">
                                        <i class="fas fa-sync-alt mr-2"></i> Validate Now
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    @if($proxy->emailAccount)
                        <div class="mb-6">
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-blue-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            This proxy is currently in use by email account: <strong>{{ $proxy->emailAccount->email_address }}</strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('accounts.proxy') }}" class="bg-gray-200 hover:bg-gray-300 text-text-primary py-2 px-4 rounded-button">
                            Cancel
                        </a>
                        <button type="submit" class="bg-primary hover:bg-primary/90 text-white py-2 px-4 rounded-button">
                            <i class="fas fa-save mr-2"></i> Update Proxy
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection