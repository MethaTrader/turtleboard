<?php
// app/Http/Controllers/ReferralController.php

namespace App\Http\Controllers;

use App\Http\Requests\ReferralRequest;
use App\Models\MexcAccount;
use App\Models\MexcReferral;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ReferralController extends Controller
{
    protected $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Display the interactive referrals network.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Get statistics for the dashboard
        $stats = MexcReferral::getStatistics();

        // Get all MEXC accounts for reference
        $mexcAccounts = MexcAccount::with('emailAccount')
            ->where('status', 'active')
            ->get();

        return view('referrals.index', [
            'stats' => $stats,
            'mexcAccounts' => $mexcAccounts,
        ]);
    }

    /**
     * Get the referral network data for visualization.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function networkData(Request $request): JsonResponse
    {
        try {
            // Apply period filter if provided
            $period = $request->input('period');
            $query = MexcAccount::with(['emailAccount', 'sentInvitations.inviteeAccount.emailAccount'])
                ->where('status', 'active');

            $nodes = [];
            $edges = [];

            // Get all active MEXC accounts
            $accounts = $query->get();

            // Create nodes for all accounts
            foreach ($accounts as $account) {
                $isInvited = MexcReferral::where('invitee_account_id', $account->id)->exists();
                $sentInvitationsCount = $account->sentInvitations->count();
                $remainingSlots = 5 - $sentInvitationsCount;

                // Get email provider for icon
                $providerIcon = $this->getProviderIcon($account->emailAccount->provider);

                $nodes[] = [
                    'id' => $account->id,
                    'label' => $account->emailAccount->email_address,
                    'group' => $isInvited ? 'invitee' : 'root',
                    'title' => $this->generateNodeTooltip($account, $sentInvitationsCount, $remainingSlots),
                    'value' => max(15, min(35, 15 + ($sentInvitationsCount * 4))), // Node size based on activity
                    'shape' => 'circularImage',  // Use image-based node
                    'image' => $providerIcon,    // Set the provider icon as image
                    'data' => [
                        'email' => $account->emailAccount->email_address,
                        'provider' => $account->emailAccount->provider,
                        'sentInvitations' => $sentInvitationsCount,
                        'remainingSlots' => $remainingSlots,
                        'isInvited' => $isInvited,
                    ]
                ];
            }

            // Get referrals with optional period filtering
            $referralsQuery = MexcReferral::with(['inviterAccount', 'inviteeAccount']);

            if ($period) {
                // Filter by period if specified
                $referralsQuery->where('created_at', '>=', $period);
                $nextPeriod = date('Y-m-d', strtotime($period . ' +15 days'));
                $referralsQuery->where('created_at', '<', $nextPeriod);
            }

            $referrals = $referralsQuery->get();

            // Create edges for invitations
            foreach ($referrals as $referral) {
                $edges[] = [
                    'id' => $referral->id,
                    'from' => $referral->inviter_account_id,
                    'to' => $referral->invitee_account_id,
                    'color' => ['color' => $referral->getStatusColor()],
                    'title' => $this->generateEdgeTooltip($referral),
                    'arrows' => 'to',
                    'width' => 3,
                    'data' => [
                        'status' => $referral->status,
                        'created_at' => $referral->created_at->format('M d, Y'),
                        'referral_link' => $referral->referral_link,
                    ]
                ];
            }

            return response()->json([
                'success' => true,
                'nodes' => $nodes,
                'edges' => $edges,
                'stats' => MexcReferral::getStatistics(),
                'filter' => [
                    'period' => $period ?: 'all'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating network data: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading network data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Create a new referral connection via API.
     *
     * @param ReferralRequest $request
     * @return JsonResponse
     */
    public function store(ReferralRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();
            $validatedData['created_by'] = Auth::id();

            // Create the referral
            $referral = MexcReferral::create($validatedData);

            // Load relationships for response
            $referral->load(['inviterAccount.emailAccount', 'inviteeAccount.emailAccount']);

            // Log the activity
            $this->activityService->log(
                'create',
                'mexc_referral',
                $referral,
                [
                    'inviter' => $referral->inviterAccount->emailAccount->email_address,
                    'invitee' => $referral->inviteeAccount->emailAccount->email_address,
                    'status' => $referral->status,
                    'referral_link' => $referral->referral_link,
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Referral connection created successfully',
                'referral' => [
                    'id' => $referral->id,
                    'inviter_account_id' => $referral->inviter_account_id,
                    'invitee_account_id' => $referral->invitee_account_id,
                    'status' => $referral->status,
                    'status_color' => $referral->getStatusColor(),
                    'referral_link' => $referral->referral_link,
                    'created_at' => $referral->created_at->format('M d, Y'),
                    'inviter_email' => $referral->inviterAccount->emailAccount->email_address,
                    'invitee_email' => $referral->inviteeAccount->emailAccount->email_address,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating referral: ' . $e->getMessage(), [
                'request_data' => $request->validated(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create referral connection',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update the status of a referral.
     *
     * @param Request $request
     * @param MexcReferral $referral
     * @return JsonResponse
     */
    public function updateStatus(Request $request, MexcReferral $referral): JsonResponse
    {
        try {
            $request->validate([
                'status' => ['required', 'in:pending,completed,cancelled']
            ]);

            $oldStatus = $referral->status;
            $referral->update(['status' => $request->status]);

            // Log the activity
            $this->activityService->log(
                'update',
                'mexc_referral',
                $referral,
                [
                    'old_status' => $oldStatus,
                    'new_status' => $referral->status,
                    'inviter' => $referral->inviterAccount->emailAccount->email_address,
                    'invitee' => $referral->inviteeAccount->emailAccount->email_address,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Referral status updated successfully',
                'referral' => [
                    'id' => $referral->id,
                    'status' => $referral->status,
                    'status_color' => $referral->getStatusColor(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating referral status: ' . $e->getMessage(), [
                'referral_id' => $referral->id,
                'request_data' => $request->all(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update referral status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove a referral connection.
     *
     * @param MexcReferral $referral
     * @return JsonResponse
     */
    public function destroy(MexcReferral $referral): JsonResponse
    {
        try {
            // Log the activity before deleting
            $this->activityService->log(
                'delete',
                'mexc_referral',
                $referral,
                [
                    'inviter' => $referral->inviterAccount->emailAccount->email_address,
                    'invitee' => $referral->inviteeAccount->emailAccount->email_address,
                    'status' => $referral->status,
                ]
            );

            $referral->delete();

            return response()->json([
                'success' => true,
                'message' => 'Referral connection removed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting referral: ' . $e->getMessage(), [
                'referral_id' => $referral->id,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove referral connection',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get account details for the network.
     *
     * @param MexcAccount $account
     * @return JsonResponse
     */
    public function accountDetails(MexcAccount $account): JsonResponse
    {
        try {
            $account->load(['emailAccount', 'sentInvitations', 'web3Wallet']);

            $sentInvitations = $account->sentInvitations->count();
            $isInvited = MexcReferral::where('invitee_account_id', $account->id)->exists();

            return response()->json([
                'success' => true,
                'account' => [
                    'id' => $account->id,
                    'email' => $account->emailAccount->email_address,
                    'provider' => $account->emailAccount->provider,
                    'status' => $account->status,
                    'sent_invitations' => $sentInvitations,
                    'remaining_slots' => 5 - $sentInvitations,
                    'is_invited' => $isInvited,
                    'has_wallet' => $account->web3Wallet !== null,
                    'created_at' => $account->created_at->format('M d, Y'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load account details'
            ], 500);
        }
    }

    /**
     * Generate tooltip content for a node.
     *
     * @param MexcAccount $account
     * @param int $sentInvitations
     * @param int $remainingSlots
     * @return string
     */
    private function generateNodeTooltip(MexcAccount $account, int $sentInvitations, int $remainingSlots): string
    {
        return sprintf(
            "%s\nProvider: %s\nSent: %d/5 invitations\nRemaining: %d slots\nStatus: %s",
            $account->emailAccount->email_address,
            $account->emailAccount->provider,
            $sentInvitations,
            $remainingSlots,
            ucfirst($account->status)
        );
    }

    /**
     * Generate tooltip content for an edge.
     *
     * @param MexcReferral $referral
     * @return string
     */
    private function generateEdgeTooltip(MexcReferral $referral): string
    {
        $tooltip = sprintf(
            "Referral: %s → %s\nStatus: %s\nCreated: %s",
            $referral->inviterAccount->emailAccount->email_address,
            $referral->inviteeAccount->emailAccount->email_address,
            $referral->getStatusLabel(),
            $referral->created_at->format('M d, Y')
        );

        // Add referral link if available
        if ($referral->referral_link) {
            $tooltip .= sprintf("\nReferral Link: %s", $referral->referral_link);
        }

        return $tooltip;
    }

    /**
     * Get provider icon URL based on email provider.
     *
     * @param string $provider
     * @return string
     */
    private function getProviderIcon(string $provider): string
    {
        return match (strtolower($provider)) {
            'gmail' => '/images/providers/google.png',
            'outlook' => '/images/providers/microsoft.png',
            'yahoo' => '/images/providers/yahoo.png',
            'apple' => '/images/providers/apple.png',
            default => '/images/providers/default.png',
        };
    }
}