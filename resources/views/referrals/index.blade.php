@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-text-primary">Interactive Referral Network</h2>
                <p class="text-text-secondary">Visualize and manage referral connections between MEXC accounts</p>
            </div>
            <div class="flex items-center space-x-3">
                <button id="helpButton" class="bg-blue-100 hover:bg-blue-200 text-blue-600 px-4 py-2 rounded-button text-sm transition-colors">
                    <i class="fas fa-question-circle mr-2"></i>
                    How to Use
                </button>
                <button id="refreshNetwork" class="bg-gray-200 hover:bg-gray-300 text-text-primary px-4 py-2 rounded-button text-sm transition-colors">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-card rounded-card shadow-card p-6 flex items-center">
                <div class="rounded-full w-12 h-12 bg-secondary/10 flex items-center justify-center mr-4">
                    <i class="fas fa-users text-secondary text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Total Connections</p>
                    <p class="text-2xl font-bold text-text-primary">{{ $stats['total'] }}</p>
                </div>
            </div>

            <div class="bg-card rounded-card shadow-card p-6 flex items-center">
                <div class="rounded-full w-12 h-12 bg-success/10 flex items-center justify-center mr-4">
                    <i class="fas fa-check-circle text-success text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Completed</p>
                    <p class="text-2xl font-bold text-success">{{ $stats['completed'] }}</p>
                </div>
            </div>

            <div class="bg-card rounded-card shadow-card p-6 flex items-center">
                <div class="rounded-full w-12 h-12 bg-warning/10 flex items-center justify-center mr-4">
                    <i class="fas fa-clock text-warning text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Pending</p>
                    <p class="text-2xl font-bold text-warning">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>

        <!-- Interactive Network Visualization -->
        <div class="bg-card rounded-card shadow-card overflow-hidden">
            <!-- Header -->
            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-project-diagram text-secondary mr-3 text-xl"></i>
                    <h3 class="text-lg font-semibold text-text-primary">Referral Network</h3>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Period Filter -->
                    <div class="flex items-center space-x-2">
                        <label for="promotion-period" class="text-sm font-medium text-text-secondary">Period:</label>
                        @php
                            $currentMonth = date('Y-m');
                            $periods = [];

                            // Generate periods for the last 3 months (6 half-month periods)
                            for ($i = 0; $i < 3; $i++) {
                                $month = date('Y-m', strtotime("-$i months"));
                                $periods[] = [
                                    'value' => $month . '-01',
                                    'label' => date('F Y', strtotime($month)) . ' (1st Half)'
                                ];
                                $periods[] = [
                                    'value' => $month . '-16',
                                    'label' => date('F Y', strtotime($month)) . ' (2nd Half)'
                                ];
                            }
                        @endphp

                        <select id="promotion-period" class="rounded-md border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20 text-sm">
                            <option value="all">All Periods</option>
                            @foreach($periods as $period)
                                <option value="{{ $period['value'] }}">{{ $period['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center space-x-3 text-sm">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-secondary mr-2"></div>
                            <span class="text-text-secondary">Root Account</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-primary mr-2"></div>
                            <span class="text-text-secondary">Invited Account</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Network Visualization Container -->
            <div class="relative">
                <div id="interactive-network-visualization"
                     class="h-[700px] w-full"
                     data-data-url="{{ route('referrals.network-data') }}"
                     data-create-url="{{ route('referrals.store') }}">
                    <!-- Loading State (will be replaced by JS) -->
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-secondary mx-auto mb-4"></div>
                            <p class="text-text-secondary">Loading network visualization...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Legend -->
            <div class="bg-gray-50 border-t border-gray-200 px-6 py-4">
                <div class="flex flex-wrap items-center justify-center gap-6 text-sm">
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full mr-2" style="background-color: #F59E0B;"></div>
                        <span class="text-text-secondary">Pending</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full mr-2" style="background-color: #00DEA3;"></div>
                        <span class="text-text-secondary">Completed</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full mr-2" style="background-color: #F56565;"></div>
                        <span class="text-text-secondary">Cancelled</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Modal -->
    <div id="helpModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-text-primary">How to Use Interactive Referral Network</h3>
                <button id="closeHelp" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-6">
                <div class="flex items-start">
                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center mr-4 mt-1">
                        <span class="text-sm font-bold">1</span>
                    </div>
                    <div>
                        <h4 class="font-semibold text-text-primary mb-2">Add Connections</h4>
                        <p class="text-text-secondary">Click the "Add Connection" button to enter connection mode. Then click two accounts to create a referral link between them.</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center mr-4 mt-1">
                        <span class="text-sm font-bold">2</span>
                    </div>
                    <div>
                        <h4 class="font-semibold text-text-primary mb-2">View Details</h4>
                        <p class="text-text-secondary">Double-click any account node to view detailed information including invitation statistics and remaining slots.</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center mr-4 mt-1">
                        <span class="text-sm font-bold">3</span>
                    </div>
                    <div>
                        <h4 class="font-semibold text-text-primary mb-2">Navigate the Network</h4>
                        <p class="text-text-secondary">Drag to move around, scroll to zoom in/out, and use the control panel to reset view or freeze the layout.</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center mr-4 mt-1">
                        <span class="text-sm font-bold">4</span>
                    </div>
                    <div>
                        <h4 class="font-semibold text-text-primary mb-2">Delete Connections</h4>
                        <p class="text-text-secondary">Right-click on any connection line to open a context menu with the option to delete the connection.</p>
                    </div>
                </div>

                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="flex items-center text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span class="font-semibold">Rules:</span>
                    </div>
                    <ul class="mt-2 text-blue-600 text-sm space-y-1">
                        <li>• Each account can invite up to 5 other accounts</li>
                        <li>• An account can only be invited once</li>
                        <li>• Accounts cannot invite themselves</li>
                        <li>• Only active accounts can participate in referrals</li>
                    </ul>
                </div>
            </div>

            <div class="flex justify-end mt-8">
                <button id="closeHelpFooter" class="bg-secondary hover:bg-secondary/90 text-white px-6 py-2 rounded-button">
                    Got it!
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite([
        'resources/css/animations.css',
        'resources/js/interactive-referral-network.js'
    ])

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Help modal functionality
            const helpButton = document.getElementById('helpButton');
            const helpModal = document.getElementById('helpModal');
            const closeHelp = document.getElementById('closeHelp');
            const closeHelpFooter = document.getElementById('closeHelpFooter');

            helpButton?.addEventListener('click', () => {
                helpModal.classList.remove('hidden');
            });

            [closeHelp, closeHelpFooter].forEach(button => {
                button?.addEventListener('click', () => {
                    helpModal.classList.add('hidden');
                });
            });

            // Close modal when clicking outside
            helpModal?.addEventListener('click', (e) => {
                if (e.target === helpModal) {
                    helpModal.classList.add('hidden');
                }
            });

            // Refresh network functionality
            const refreshButton = document.getElementById('refreshNetwork');
            refreshButton?.addEventListener('click', () => {
                if (window.referralNetwork && typeof window.referralNetwork.refresh === 'function') {
                    window.referralNetwork.refresh();
                } else {
                    location.reload();
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !helpModal.classList.contains('hidden')) {
                    helpModal.classList.add('hidden');
                }
            });

            // Period filter functionality
            const periodSelector = document.getElementById('promotion-period');
            if (periodSelector) {
                periodSelector.addEventListener('change', function() {
                    const period = this.value;

                    if (window.referralNetwork && typeof window.referralNetwork.filterByPeriod === 'function') {
                        window.referralNetwork.filterByPeriod(period === 'all' ? null : period);
                    }
                });
            }

            // Listen for network optimization events
            document.addEventListener('networkOptimized', function(e) {
                const { analysis, optimized } = e.detail;

                if (optimized) {
                    // Show toast notification about optimization
                    const toast = document.createElement('div');
                    toast.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                    toast.innerHTML = `
                        <div class="flex items-center">
                            <i class="fas fa-rocket mr-2"></i>
                            <span>Performance mode enabled for better responsiveness</span>
                        </div>
                    `;
                    document.body.appendChild(toast);

                    setTimeout(() => {
                        toast.remove();
                    }, 5000);
                }
            });
        });
    </script>
@endpush