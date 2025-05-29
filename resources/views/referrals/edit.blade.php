@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-text-primary">Edit MEXC Referral</h2>
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
                <form action="{{ route('referrals.update', $referral) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Inviter Account (Read-only) -->
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-text-secondary mb-1">Inviter Account</label>
                            <div class="flex items-center p-2 bg-gray-50 rounded-md border border-gray-200">
                                <div class="h-8 w-8 rounded-full
                                @if($referral->inviterAccount->emailAccount->provider == 'Gmail') bg-red-100 text-red-500
                                @elseif($referral->inviterAccount->emailAccount->provider == 'Outlook') bg-blue-100 text-blue-500
                                @elseif($referral->inviterAccount->emailAccount->provider == 'Yahoo') bg-purple-100 text-purple-500
                                @else bg-amber-100 text-amber-500
                                @endif flex items-center justify-center mr-2">
                                    <i class="
                                    @if($referral->inviterAccount->emailAccount->provider == 'Gmail') fab fa-google
                                    @elseif($referral->inviterAccount->emailAccount->provider == 'Outlook') fab fa-microsoft
                                    @elseif($referral->inviterAccount->emailAccount->provider == 'Yahoo') fab fa-yahoo
                                    @else fas fa-envelope
                                    @endif text-sm"></i>
                                </div>
                                <span class="text-text-primary">
                                    {{ $referral->inviterAccount->emailAccount->email_address }}
                                </span>
                            </div>
                            <input type="hidden" name="inviter_account_id" value="{{ $referral->inviter_account_id }}">
                        </div>

                        <!-- Invitee Account (Read-only) -->
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-text-secondary mb-1">Invitee Account</label>
                            <div class="flex items-center p-2 bg-gray-50 rounded-md border border-gray-200">
                                <div class="h-8 w-8 rounded-full
                                @if($referral->inviteeAccount->emailAccount->provider == 'Gmail') bg-red-100 text-red-500
                                @elseif($referral->inviteeAccount->emailAccount->provider == 'Outlook') bg-blue-100 text-blue-500
                                @elseif($referral->inviteeAccount->emailAccount->provider == 'Yahoo') bg-purple-100 text-purple-500
                                @else bg-amber-100 text-amber-500
                                @endif flex items-center justify-center mr-2">
                                    <i class="
                                    @if($referral->inviteeAccount->emailAccount->provider == 'Gmail') fab fa-google
                                    @elseif($referral->inviteeAccount->emailAccount->provider == 'Outlook') fab fa-microsoft
                                    @elseif($referral->inviteeAccount->emailAccount->provider == 'Yahoo') fab fa-yahoo
                                    @else fas fa-envelope
                                    @endif text-sm"></i>
                                </div>
                                <span class="text-text-primary">
                                    {{ $referral->inviteeAccount->emailAccount->email_address }}
                                </span>
                            </div>
                            <input type="hidden" name="invitee_account_id" value="{{ $referral->invitee_account_id }}">
                        </div>

                        <!-- Status -->
                        <div class="md:col-span-1">
                            <label for="status" class="block text-sm font-medium text-text-secondary mb-1">Status</label>
                            <select id="status" name="status" class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                                <option value="pending" {{ old('status', $referral->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="completed" {{ old('status', $referral->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ old('status', $referral->status) == 'failed' ? 'selected' : '' }}>Failed</option>
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
                                <option value="{{ $referral->promotion_period }}" selected>
                                    {{ date('F Y', strtotime($referral->promotion_period)) }}
                                    {{ strpos($referral->promotion_period, '-01-') ? '(1st Half)' : '(2nd Half)' }}
                                </option>

                                <!-- Other periods -->
                                @php
                                    $currentMonth = date('Y-m-01');
                                    $currentMonth2 = date('Y-m-16');
                                    $prevMonth1 = date('Y-m-01', strtotime('-1 month'));
                                    $prevMonth2 = date('Y-m-16', strtotime('-1 month'));
                                @endphp

                                @if($referral->promotion_period != $currentMonth)
                                    <option value="{{ $currentMonth }}">
                                        {{ date('F Y', strtotime($currentMonth)) }} (1st Half)
                                    </option>
                                @endif

                                @if($referral->promotion_period != $currentMonth2)
                                    <option value="{{ $currentMonth2 }}">
                                        {{ date('F Y', strtotime($currentMonth2)) }} (2nd Half)
                                    </option>
                                @endif

                                @if($referral->promotion_period != $prevMonth1)
                                    <option value="{{ $prevMonth1 }}">
                                        {{ date('F Y', strtotime($prevMonth1)) }} (1st Half)
                                    </option>
                                @endif

                                @if($referral->promotion_period != $prevMonth2)
                                    <option value="{{ $prevMonth2 }}">
                                        {{ date('F Y', strtotime($prevMonth2)) }} (2nd Half)
                                    </option>
                                @endif
                            </select>
                            @error('promotion_period')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Deposit Amount -->
                        <div class="md:col-span-1">
                            <label for="deposit_amount" class="block text-sm font-medium text-text-secondary mb-1">Deposit Amount ($)</label>
                            <input type="number" step="0.01" min="0" id="deposit_amount" name="deposit_amount"
                                   value="{{ old('deposit_amount', $referral->deposit_amount) }}"
                                   class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                            @error('deposit_amount')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Deposit Date -->
                        <div class="md:col-span-1">
                            <label for="deposit_date" class="block text-sm font-medium text-text-secondary mb-1">Deposit Date</label>
                            <input type="date" id="deposit_date" name="deposit_date"
                                   value="{{ old('deposit_date', $referral->deposit_date ? $referral->deposit_date->format('Y-m-d') : '') }}"
                                   class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">
                            @error('deposit_date')
                            <div class="text-danger text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Withdrawal Date -->
                        <div class="md:col-span-1">
                            <label for="withdrawal_date" class="block text-sm font-medium text-text-secondary mb-1">Withdrawal Date</label>
                            <input type="date" id="withdrawal_date" name="withdrawal_date"
                                   value="{{ old('withdrawal_date', $referral->withdrawal_date ? $referral->withdrawal_date->format('Y-m-d') : '') }}"
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
                                    <input type="checkbox" id="inviter_rewarded" name="inviter_rewarded" value="1"
                                           {{ old('inviter_rewarded', $referral->inviter_rewarded) ? 'checked' : '' }}
                                           class="w-4 h-4 text-secondary focus:ring-secondary/20 border-gray-300 rounded">
                                    <label for="inviter_rewarded" class="ml-2 text-sm text-text-primary">
                                        Inviter received $20 reward
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="invitee_rewarded" name="invitee_rewarded" value="1"
                                           {{ old('invitee_rewarded', $referral->invitee_rewarded) ? 'checked' : '' }}
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
                                      class="block w-full rounded-button border-gray-300 focus:border-secondary focus:ring focus:ring-secondary/20">{{ old('notes', $referral->notes) }}</textarea>
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
                            <i class="fas fa-save mr-2"></i> Update Referral
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
        });
    </script>
@endpush