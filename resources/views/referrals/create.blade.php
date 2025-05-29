@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-text-primary">Create MEXC Referral</h2>
                <a href="{{ route('referrals.index') }}" class="text-secondary hover:text-secondary/80">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Referrals
                </a>
            </div>

            @if(session('error'))
                <div class="bg-danger/10 text-danger p-4 border-l-4 border-danger mb-6 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-card rounded-card shadow-card p-6">
                <form action="{{ route('referrals.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Inviter Account -->
                        <div class="md:col-span-1">
                            <label for="inviter_account_id" class="block text-sm font-medium text-text-secondary mb-1">Inviter Account</label>
                            <select id="inviter_account_id" name="inviter_account_id"
                                    class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                                <option value="">-- Select Inviter Account --</option>
                                @foreach($mexcAccounts as $account)
                                    @if($account->canInviteMore())
                                        <option value="{{ $account->id }}" {{ old('inviter_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->emailAccount->email_address }}
                                            ({{ $account->getRemainingInvitationSlots() }}/5 slots available)
                                        </option>
                                    @else
                                        <option value="{{ $account->id }}" disabled>
                                            {{ $account->emailAccount->email_address }} (No slots available)
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('inviter_account_id')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Invitee Account -->
                        <div class="md:col-span-1">
                            <label for="invitee_account_id" class="block text-sm font-medium text-text-secondary mb-1">Invitee Account</label>
                            <select id="invitee_account_id" name="invitee_account_id"
                                    class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                                <option value="">-- Select Invitee Account --</option>
                                @foreach($mexcAccounts as $account)
                                    @if(!$account->isAlreadyInvited())
                                        <option value="{{ $account->id }}" {{ old('invitee_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->emailAccount->email_address }}
                                        </option>
                                    @else
                                        <option value="{{ $account->id }}" disabled>
                                            {{ $account->emailAccount->email_address }} (Already invited)
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('invitee_account_id')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="md:col-span-1">
                            <label for="status" class="block text-sm font-medium text-text-secondary mb-1">Status</label>
                            <select id="status" name="status" class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                                <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ old('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                            @error('status')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Promotion Period -->
                        <div class="md:col-span-1">
                            <label for="promotion_period" class="block text-sm font-medium text-text-secondary mb-1">Promotion Period</label>
                            <select id="promotion_period" name="promotion_period" class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                                <!-- Current period -->
                                <option value="{{ $currentPromotionPeriod }}" selected>
                                    {{ date('F Y', strtotime($currentPromotionPeriod)) }}
                                    {{ strpos($currentPromotionPeriod, '-01-') ? '(1st Half)' : '(2nd Half)' }}
                                </option>

                                <!-- Previous period (1st half of the month) -->
                                @php
                                    $prevMonth1 = date('Y-m-01', strtotime($currentPromotionPeriod . ' -1 month'));
                                @endphp
                                <option value="{{ $prevMonth1 }}">
                                    {{ date('F Y', strtotime($prevMonth1)) }} (1st Half)
                                </option>

                                <!-- Previous period (2nd half of the month) -->
                                @php
                                    $prevMonth2 = date('Y-m-16', strtotime($currentPromotionPeriod . ' -1 month'));
                                @endphp
                                <option value="{{ $prevMonth2 }}">
                                    {{ date('F Y', strtotime($prevMonth2)) }} (2nd Half)
                                </option>
                            </select>
                            @error('promotion_period')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Deposit Amount -->
                        <div class="md:col-span-1">
                            <label for="deposit_amount" class="block text-sm font-medium text-text-secondary mb-1">Deposit Amount ($)</label>
                            <input type="number" step="0.01" min="0" id="deposit_amount" name="deposit_amount" value="{{ old('deposit_amount', 100) }}"
                                   class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                            @error('deposit_amount')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Deposit Date -->
                        <div class="md:col-span-1">
                            <label for="deposit_date" class="block text-sm font-medium text-text-secondary mb-1">Deposit Date</label>
                            <input type="date" id="deposit_date" name="deposit_date" value="{{ old('deposit_date') }}"
                                   class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                            @error('deposit_date')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Withdrawal Date -->
                        <div class="md:col-span-1">
                            <label for="withdrawal_date" class="block text-sm font-medium text-text-secondary mb-1">Withdrawal Date</label>
                            <input type="date" id="withdrawal_date" name="withdrawal_date" value="{{ old('withdrawal_date') }}"
                                   class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                            @error('withdrawal_date')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Rewards Checkboxes -->
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-text-secondary mb-2">Rewards Status</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="checkbox" id="inviter_rewarded" name="inviter_rewarded" value="1" {{ old('inviter_rewarded') ? 'checked' : '' }}
                                    class="w-4 h-4 text-secondary focus:ring-secondary/20 border-gray-300 rounded">
                                    <label for="inviter_rewarded" class="ml-2 text-sm text-text-primary">
                                        Inviter received $20 reward
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="invitee_rewarded" name="invitee_rewarded" value="1" {{ old('invitee_rewarded') ? 'checked' : '' }}
                                    class="w-4 h-4 text-secondary focus:ring-secondary/20 border-gray-300 rounded">
                                    <label for="invitee_rewarded" class="ml-2 text-sm text-text-primary">
                                        Invitee received $20 reward
                                    </label>
                                </div>
                            </div>
                            @error('inviter_rewarded')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                            @error('invitee_rewarded')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-text-secondary mb-1">Notes</label>
                            <textarea id="notes" name="notes" rows="3"
                                      class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">{{ old('notes') }}</textarea>
                            @error('notes')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('referrals.index') }}" class="bg-gray-200 hover:bg-gray-300 text-text-primary py-2 px-4 rounded-button">
                            Cancel
                        </a>
                        <button type="submit" class="bg-primary hover:bg-primary/90 text-white py-2 px-4 rounded-button">
                            <i class="fas fa-save mr-2"></i> Create Referral
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
            // Dynamic form handling
            const statusSelect = document.getElementById('status');
            const inviterRewardedCheckbox = document.getElementById('inviter_rewarded');
            const inviteeRewardedCheckbox = document.getElementById('invitee_rewarded');

            // Update rewards checkboxes based on status
            statusSelect.addEventListener('change', function() {
                if (this.value === 'completed') {
                    inviterRewardedCheckbox.checked = true;
                    inviteeRewardedCheckbox.checked = true;
                } else if (this.value === 'failed') {
                    inviterRewardedCheckbox.checked = false;
                    inviteeRewardedCheckbox.checked = false;
                }
            });

            // Handle inviter selection to update invitee options
            const inviterSelect = document.getElementById('inviter_account_id');
            const inviteeSelect = document.getElementById('invitee_account_id');

            inviterSelect.addEventListener('change', function() {
                const inviterId = this.value;

                // Reset invitee options
                for (let option of inviteeSelect.options) {
                    if (option.value === '') continue; // Skip the placeholder

                    // Disable selecting the inviter as invitee
                    if (option.value === inviterId) {
                        option.disabled = true;
                    } else {
                        // Enable other options if they're not already invited
                        if (option.textContent.indexOf('Already invited') === -1) {
                            option.disabled = false;
                        }
                    }
                }

                // Clear the invitee selection if it's the same as the inviter
                if (inviteeSelect.value === inviterId) {
                    inviteeSelect.value = '';
                }
            });
        });
    </script>
@endpush