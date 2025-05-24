{{-- resources/views/proxies/create.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-text-primary">Add Proxies</h2>
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
                <form action="{{ route('accounts.proxy.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Enter Proxy List</h3>
                        <p class="text-text-secondary mb-4">
                            Enter proxies in the format: <span class="font-mono bg-gray-100 p-1 rounded">IP:PORT:USERNAME:PASSWORD</span> or <span class="font-mono bg-gray-100 p-1 rounded">IP:PORT</span><br>
                            Each proxy should be on a new line.
                        </p>

                        <div class="mb-4">
                            <label for="proxy_list" class="block text-sm font-medium text-text-secondary mb-1">Proxy List</label>
                            <textarea
                                    id="proxy_list"
                                    name="proxy_list"
                                    rows="10"
                                    class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20"
                                    placeholder="192.168.1.1:8080:username:password
10.0.0.1:3128
..."
                            >{{ old('proxy_list') }}</textarea>
                            @error('proxy_list')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center">
                                <div class="h-px bg-gray-200 flex-grow"></div>
                                <span class="px-4 text-text-secondary text-sm">OR</span>
                                <div class="h-px bg-gray-200 flex-grow"></div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="proxy_file" class="block text-sm font-medium text-text-secondary mb-1">Upload Proxy List File (.txt)</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <i class="fas fa-file-upload text-4xl text-gray-400"></i>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="proxy_file" class="relative cursor-pointer bg-white rounded-md font-medium text-secondary hover:text-secondary/80 focus-within:outline-none">
                                            <span>Upload a file</span>
                                            <input id="proxy_file" name="proxy_file" type="file" class="sr-only" accept=".txt">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        TXT file up to 2MB
                                    </p>
                                </div>
                            </div>
                            <div id="file_name" class="mt-2 text-sm text-secondary hidden">
                                <i class="fas fa-file-alt mr-1"></i>
                                <span id="selected_file_name"></span>
                            </div>
                            @error('proxy_file')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        All proxies will be added with <strong>Pending</strong> status initially. You can validate them after adding.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('accounts.proxy') }}" class="bg-gray-200 hover:bg-gray-300 text-text-primary py-2 px-4 rounded-button">
                            Cancel
                        </a>
                        <button type="submit" class="bg-primary hover:bg-primary/90 text-white py-2 px-4 rounded-button">
                            <i class="fas fa-save mr-2"></i> Add Proxies
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('proxy_file');
            const fileNameDisplay = document.getElementById('file_name');
            const selectedFileName = document.getElementById('selected_file_name');
            const dropZone = document.querySelector('.border-dashed');

            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    if (fileInput.files.length > 0) {
                        const fileName = fileInput.files[0].name;
                        selectedFileName.textContent = fileName;
                        fileNameDisplay.classList.remove('hidden');
                        dropZone.classList.add('border-secondary', 'bg-secondary/5');
                        dropZone.classList.remove('border-gray-300');
                    } else {
                        fileNameDisplay.classList.add('hidden');
                        dropZone.classList.remove('border-secondary', 'bg-secondary/5');
                        dropZone.classList.add('border-gray-300');
                    }
                });

                // Drag and drop functionality
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, unhighlight, false);
                });

                function highlight() {
                    dropZone.classList.add('border-secondary', 'bg-secondary/5');
                    dropZone.classList.remove('border-gray-300');
                }

                function unhighlight() {
                    dropZone.classList.remove('border-secondary', 'bg-secondary/5');
                    dropZone.classList.add('border-gray-300');
                }

                dropZone.addEventListener('drop', handleDrop, false);

                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;

                    if (files.length > 0) {
                        fileInput.files = files;
                        const fileName = files[0].name;
                        selectedFileName.textContent = fileName;
                        fileNameDisplay.classList.remove('hidden');
                    }
                }
            }
        });
    </script>
@endpush