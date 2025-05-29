@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-text-primary">MEXC Referrals</h2>
                <p class="text-text-secondary">Manage your "Invite a friend and earn $20" promotions</p>
            </div>
            <a href="{{ route('referrals.create') }}" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button flex items-center">
                <i class="fas fa-plus mr-2"></i>
                <span>Create Referral</span>
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-card rounded-card shadow-card p-4 flex items-center">
                <div class="rounded-full w-12 h-12 bg-secondary/10 flex items-center justify-center mr-4">
                    <i class="fas fa-users text-secondary text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Total Referrals</p>
                    <p class="text-2xl font-bold text-text-primary">{{ $stats['total'] }}</p>
                </div>
            </div>

            <div class="bg-card rounded-card shadow-card p-4 flex items-center">
                <div class="rounded-full w-12 h-12 bg-warning/10 flex items-center justify-center mr-4">
                    <i class="fas fa-clock text-warning text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Pending</p>
                    <p class="text-2xl font-bold text-warning">{{ $stats['pending'] }}</p>
                </div>
            </div>

            <div class="bg-card rounded-card shadow-card p-4 flex items-center">
                <div class="rounded-full w-12 h-12 bg-success/10 flex items-center justify-center mr-4">
                    <i class="fas fa-check-circle text-success text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Completed</p>
                    <p class="text-2xl font-bold text-success">{{ $stats['completed'] }}</p>
                </div>
            </div>

            <div class="bg-card rounded-card shadow-card p-4 flex items-center">
                <div class="rounded-full w-12 h-12 bg-danger/10 flex items-center justify-center mr-4">
                    <i class="fas fa-times-circle text-danger text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Failed</p>
                    <p class="text-2xl font-bold text-danger">{{ $stats['failed'] }}</p>
                </div>
            </div>

            <div class="bg-card rounded-card shadow-card p-4 flex items-center">
                <div class="rounded-full w-12 h-12 bg-primary/10 flex items-center justify-center mr-4">
                    <i class="fas fa-coins text-primary text-xl"></i>
                </div>
                <div>
                    <p class="text-text-secondary text-sm">Total Rewards</p>
                    <p class="text-2xl font-bold text-primary">${{ $stats['total_rewards'] }}</p>
                </div>
            </div>
        </div>

        <!-- Visualization & List View Tabs -->
        <div class="bg-card rounded-card shadow-card overflow-hidden" x-data="{ activeTab: 'visualization' }">
            <!-- Tab Navigation -->
            <div class="bg-gray-50 border-b border-gray-200 px-4 flex">
                <button @click="activeTab = 'visualization'"
                        :class="{ 'border-b-2 border-secondary text-secondary': activeTab === 'visualization', 'text-gray-500': activeTab !== 'visualization' }"
                        class="px-4 py-3 font-medium">
                    <i class="fas fa-project-diagram mr-2"></i> Visualization
                </button>
                <button @click="activeTab = 'list'"
                        :class="{ 'border-b-2 border-secondary text-secondary': activeTab === 'list', 'text-gray-500': activeTab !== 'list' }"
                        class="px-4 py-3 font-medium">
                    <i class="fas fa-list mr-2"></i> List View
                </button>
            </div>

            <!-- Network Visualization -->
            <div x-show="activeTab === 'visualization'" class="p-4">
                <!-- Filters -->
                <div class="mb-4 flex items-center gap-4">
                    <label class="text-sm font-medium text-text-secondary">Promotion Period:</label>
                    <select id="visualization-period" class="border-gray-300 rounded-md text-sm">
                        <option value="all">All Periods</option>
                        @foreach($promotionPeriods as $period)
                            <option value="{{ $period }}" {{ $period === $currentPromotionPeriod ? 'selected' : '' }}>
                                {{ date('F Y', strtotime($period)) }} {{ strpos($period, '-01-') ? '(1st Half)' : '(2nd Half)' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Network Visualization Container -->
                <div id="network-visualization"
                     class="border border-gray-200 rounded-lg h-[600px] w-full"
                     data-url="{{ route('referrals.network-data') }}"></div>

                <!-- Legend -->
                <div class="mt-4 flex flex-wrap gap-4 justify-center">
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-secondary mr-2"></div>
                        <span class="text-sm text-text-secondary">Root Account</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-primary mr-2"></div>
                        <span class="text-sm text-text-secondary">Invited Account</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-success mr-2"></div>
                        <span class="text-sm text-text-secondary">Completed Referral</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-warning mr-2"></div>
                        <span class="text-sm text-text-secondary">Pending Referral</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-danger mr-2"></div>
                        <span class="text-sm text-text-secondary">Failed Referral</span>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div x-show="activeTab === 'list'" class="p-4">
                <!-- Filters Section -->
                <div class="bg-gray-50 p-4 rounded-md mb-4">
                    <form action="{{ route('referrals.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-text-secondary mb-1">Status</label>
                            <select id="status" name="status" class="w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div>
                            <label for="promotion_period" class="block text-sm font-medium text-text-secondary mb-1">Promotion Period</label>
                            <select id="promotion_period" name="promotion_period" class="w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                                <option value="">All Periods</option>
                                @foreach($promotionPeriods as $period)
                                    <option value="{{ $period }}" {{ request('promotion_period') == $period ? 'selected' : '' }}>
                                        {{ date('F Y', strtotime($period)) }} {{ strpos($period, '-01-') ? '(1st Half)' : '(2nd Half)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button">
                                <i class="fas fa-filter mr-2"></i> Filter
                            </button>
                            <a href="{{ route('referrals.index') }}" class="bg-gray-200 hover:bg-gray-300 text-text-primary py-2 px-4 rounded-button">
                                <i class="fas fa-times mr-2"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>

                @if(session('success'))
                    <div class="bg-success/10 text-success p-4 border-l-4 border-success mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-danger/10 text-danger p-4 border-l-4 border-danger mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Referrals Table -->
                @if($referrals->isEmpty())
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-users text-gray-400 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-text-primary mb-2">No Referrals Found</h3>
                        <p class="text-text-secondary mb-4">No referrals match your current filters or you haven't created any yet.</p>
                        <a href="{{ route('referrals.create') }}" class="bg-secondary hover:bg-secondary/90 text-white py-2 px-4 rounded-button">
                            <i class="fas fa-plus mr-2"></i> Create Your First Referral
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                    Inviter
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                    Invitee
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                    Rewards
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                    Promotion Period
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                                    Created
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-text-secondary uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($referrals as $referral)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full
                                                @if($referral->inviterAccount->emailAccount->provider == 'Gmail') bg-red-100 text-red-500
                                                @elseif($referral->inviterAccount->emailAccount->provider == 'Outlook') bg-blue-100 text-blue-500
                                                @elseif($referral->inviterAccount->emailAccount->provider == 'Yahoo') bg-purple-100 text-purple-500
                                                @else bg-amber-100 text-amber-500
                                                @endif flex items-center justify-center">
                                                <i class="
                                                    @if($referral->inviterAccount->emailAccount->provider == 'Gmail') fab fa-google
                                                    @elseif($referral->inviterAccount->emailAccount->provider == 'Outlook') fab fa-microsoft
                                                    @elseif($referral->inviterAccount->emailAccount->provider == 'Yahoo') fab fa-yahoo
                                                    @else fas fa-envelope
                                                    @endif"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-text-primary">
                                                    {{ $referral->inviterAccount->emailAccount->email_address }}
                                                </div>
                                                <div class="text-xs text-text-secondary">
                                                    <span class="mr-1">Slots:</span>
                                                    <span class="px-2 py-0.5 rounded-full text-xs
                                                            @if($referral->inviterAccount->getRemainingInvitationSlots() > 0)
                                                                bg-success/10 text-success
                                                            @else
                                                                bg-danger/10 text-danger
                                                            @endif
                                                        ">
                                                            {{ 5 - $referral->inviterAccount->sentInvitations->count() }}/5
                                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full
                                                @if($referral->inviteeAccount->emailAccount->provider == 'Gmail') bg-red-100 text-red-500
                                                @elseif($referral->inviteeAccount->emailAccount->provider == 'Outlook') bg-blue-100 text-blue-500
                                                @elseif($referral->inviteeAccount->emailAccount->provider == 'Yahoo') bg-purple-100 text-purple-500
                                                @else bg-amber-100 text-amber-500
                                                @endif flex items-center justify-center">
                                                <i class="
                                                    @if($referral->inviteeAccount->emailAccount->provider == 'Gmail') fab fa-google
                                                    @elseif($referral->inviteeAccount->emailAccount->provider == 'Outlook') fab fa-microsoft
                                                    @elseif($referral->inviteeAccount->emailAccount->provider == 'Yahoo') fab fa-yahoo
                                                    @else fas fa-envelope
                                                    @endif"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-text-primary">
                                                    {{ $referral->inviteeAccount->emailAccount->email_address }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if($referral->status === 'pending')
                                                    bg-warning/10 text-warning
                                                @elseif($referral->status === 'completed')
                                                    bg-success/10 text-success
                                                @else
                                                    bg-danger/10 text-danger
                                                @endif">
                                                {{ ucfirst($referral->status) }}
                                            </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-text-secondary">
                                            <div class="flex flex-col space-y-1">
                                                <div class="flex items-center">
                                                    <span class="mr-1">Inviter:</span>
                                                    @if($referral->inviter_rewarded)
                                                        <span class="text-success flex items-center">
                                                                <i class="fas fa-check-circle mr-1"></i> $20
                                                            </span>
                                                    @else
                                                        <span class="text-text-secondary">
                                                                <i class="fas fa-circle mr-1"></i> $0
                                                            </span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="mr-1">Invitee:</span>
                                                    @if($referral->invitee_rewarded)
                                                        <span class="text-success flex items-center">
                                                                <i class="fas fa-check-circle mr-1"></i> $20
                                                            </span>
                                                    @else
                                                        <span class="text-text-secondary">
                                                                <i class="fas fa-circle mr-1"></i> $0
                                                            </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                                        {{ date('F Y', strtotime($referral->promotion_period)) }}
                                        <div class="text-xs">
                                            {{ strpos($referral->promotion_period, '-01-') ? '(1st Half)' : '(2nd Half)' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                                        {{ $referral->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            @if($referral->status === 'pending')
                                                <form action="{{ route('referrals.complete', $referral) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="text-success hover:text-success/80" title="Mark as Completed">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('referrals.fail', $referral) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="text-danger hover:text-danger/80" title="Mark as Failed">
                                                        <i class="fas fa-times-circle"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('referrals.edit', $referral) }}" class="text-secondary hover:text-secondary/80" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('referrals.destroy', $referral) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-danger hover:text-danger/80" title="Delete" onclick="return confirm('Are you sure you want to delete this referral?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="py-3 px-6 border-t border-gray-200">
                        {{ $referrals->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/referral-network.js')
@endpush