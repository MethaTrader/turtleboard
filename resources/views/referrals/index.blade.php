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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                <div class="rounded-full w-12 h-12 bg-warning/10 flex items-center justify-center mr-4">
                    <i class="fas fa-clock text-warning text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Pending</p>
                    <p class="text-2xl font-bold text-warning">{{ $stats['pending'] }}</p>
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
                <div class="rounded-full w-12 h-12 bg-primary/10 flex items-center justify-center mr-4">
                    <i class="fas fa-percentage text-primary text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Success Rate</p>
                    <p class="text-2xl font-bold text-primary">{{ $stats['completion_rate'] }}%</p>
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
                    <!-- Loading State -->
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
                        <span class="text-text-secondary">Pending Connection</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full mr-2" style="background-color: #00DEA3;"></div>
                        <span class="text-text-secondary">Completed Connection</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full mr-2" style="background-color: #F56565;"></div>
                        <span class="text-text-secondary">Cancelled Connection</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Accounts Summary -->
        <div class="bg-card rounded-card shadow-card p-6">
            <h3 class="text-lg font-semibold text-text-primary mb-4">Available Accounts</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($mexcAccounts as $account)
                    @php
                        $sentInvitations = $account->sentInvitations->count();
                        $remainingSlots = 5 - $sentInvitations;
                        $isInvited = \App\Models\MexcReferral::where('invitee_account_id', $account->id)->exists();
                    @endphp
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-secondary/50 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full {{ $isInvited ? 'bg-success/10 text-success' : 'bg-secondary/10 text-secondary' }} flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-text-primary text-sm">{{ $account->emailAccount->email_address }}</p>
                                    <p class="text-xs text-text-secondary">{{ $isInvited ? 'Invited Account' : 'Root Account' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-text-secondary">Invitations: {{ $sentInvitations }}/5</span>
                            <span class="px-2 py-1 rounded-full {{ $remainingSlots > 0 ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                                {{ $remainingSlots }} slots left
                            </span>
                        </div>
                    </div>
                @endforeach
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
    @vite('resources/js/interactive-referral-network.js')

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
                location.reload();
            });

            // Close modal with Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !helpModal.classList.contains('hidden')) {
                    helpModal.classList.add('hidden');
                }
            });
        });
    </script>
@endpush