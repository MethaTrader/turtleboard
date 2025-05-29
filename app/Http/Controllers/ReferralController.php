<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReferralRequest;
use App\Models\MexcAccount;
use App\Models\MexcReferral;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReferralController extends Controller
{
    protected $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Display the referrals dashboard and visualization.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Get referrals with pagination
        $query = MexcReferral::with(['inviterAccount.emailAccount', 'inviteeAccount.emailAccount']);

        // Filter by status if requested
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by promotion period if requested
        if ($request->has('promotion_period') && $request->promotion_period) {
            $query->where('promotion_period', $request->promotion_period);
        }

        // Default sorting by newest first
        $referrals = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get statistics for the dashboard
        $stats = [
            'total' => MexcReferral::count(),
            'pending' => MexcReferral::where('status', 'pending')->count(),
            'completed' => MexcReferral::where('status', 'completed')->count(),
            'failed' => MexcReferral::where('status', 'failed')->count(),
            'total_rewards' => MexcReferral::where('status', 'completed')->count() * 40, // $40 per completed referral ($20 inviter + $20 invitee)
        ];

        // Get all MEXC accounts for the referral form
        $mexcAccounts = MexcAccount::with('emailAccount')
            ->where('status', 'active')
            ->get();

        // Get current promotion period
        $currentPromotionPeriod = MexcReferral::getCurrentPromotionPeriod();

        // Prepare promotion period options for filtering
        $promotionPeriods = MexcReferral::select('promotion_period')
            ->distinct()
            ->orderBy('promotion_period', 'desc')
            ->pluck('promotion_period')
            ->toArray();

        // Add current period if not in the list
        if (!in_array($currentPromotionPeriod, $promotionPeriods)) {
            $promotionPeriods[] = $currentPromotionPeriod;
            sort($promotionPeriods);
        }

        return view('referrals.index', [
            'referrals' => $referrals,
            'stats' => $stats,
            'mexcAccounts' => $mexcAccounts,
            'currentPromotionPeriod' => $currentPromotionPeriod,
            'promotionPeriods' => $promotionPeriods,
            'filters' => $request->only(['status', 'promotion_period']),
        ]);
    }

    /**
     * Show the form for creating a new referral.
     *
     * @return View
     */
    public function create(): View
    {
        // Get active MEXC accounts for selection
        $mexcAccounts = MexcAccount::with('emailAccount')
            ->where('status', 'active')
            ->get();

        return view('referrals.create', [
            'mexcAccounts' => $mexcAccounts,
            'currentPromotionPeriod' => MexcReferral::getCurrentPromotionPeriod(),
        ]);
    }

    /**
     * Store a newly created referral.
     *
     * @param ReferralRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ReferralRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Check if inviter has reached the limit of 5 invitations
            if (MexcReferral::hasReachedInviteLimit($validatedData['inviter_account_id'])) {
                return redirect()->back()->withInput()->with('error', 'This inviter account has already reached the maximum limit of 5 invitations.');
            }

            // Check if invitee is already invited by someone else
            $inviteeAccount = MexcAccount::find($validatedData['invitee_account_id']);
            if ($inviteeAccount->isAlreadyInvited()) {
                return redirect()->back()->withInput()->with('error', 'This account has already been invited by another account.');
            }

            // Add current user ID as creator
            $validatedData['created_by'] = Auth::id();

            // Create the referral
            $referral = MexcReferral::create($validatedData);

            // Log the activity
            $this->activityService->log(
                'create',
                'mexc_referral',
                $referral,
                [
                    'inviter' => $referral->inviterAccount->emailAccount->email_address,
                    'invitee' => $referral->inviteeAccount->emailAccount->email_address,
                    'promotion_period' => $referral->promotion_period,
                ]
            );

            return redirect()->route('referrals.index')->with('success', 'Referral created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error creating referral: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified referral.
     *
     * @param MexcReferral $referral
     * @return View
     */
    public function edit(MexcReferral $referral): View
    {
        return view('referrals.edit', [
            'referral' => $referral,
            'mexcAccounts' => MexcAccount::with('emailAccount')->where('status', 'active')->get(),
        ]);
    }

    /**
     * Update the specified referral.
     *
     * @param ReferralRequest $request
     * @param MexcReferral $referral
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ReferralRequest $request, MexcReferral $referral)
    {
        try {
            $validatedData = $request->validated();

            // Update the referral
            $referral->update($validatedData);

            // Log the activity
            $this->activityService->log(
                'update',
                'mexc_referral',
                $referral,
                [
                    'status' => $referral->status,
                    'inviter_rewarded' => $referral->inviter_rewarded,
                    'invitee_rewarded' => $referral->invitee_rewarded,
                ]
            );

            return redirect()->route('referrals.index')->with('success', 'Referral updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error updating referral: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified referral.
     *
     * @param MexcReferral $referral
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(MexcReferral $referral)
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
                ]
            );

            // Delete the referral
            $referral->delete();

            return redirect()->route('referrals.index')->with('success', 'Referral deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting referral: ' . $e->getMessage());
        }
    }

    /**
     * Get the referral network data for visualization.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function networkData()
    {
        $data = [];

        // Get all active MEXC accounts
        $accounts = MexcAccount::with(['emailAccount', 'sentInvitations.inviteeAccount.emailAccount'])
            ->where('status', 'active')
            ->get();

        $nodes = [];
        $edges = [];

        // Create nodes for all accounts
        foreach ($accounts as $account) {
            $nodes[] = [
                'id' => $account->id,
                'label' => $account->emailAccount->email_address,
                'group' => $account->isAlreadyInvited() ? 'invitee' : 'root',
                'title' => $account->emailAccount->email_address,
                'value' => $account->sentInvitations->count() + 1, // Node size based on number of invitations
                'data' => [
                    'totalRewards' => $account->getTotalRewards(),
                    'remainingSlots' => $account->getRemainingInvitationSlots(),
                ]
            ];

            // Create edges for invitations
            foreach ($account->sentInvitations as $invitation) {
                $edges[] = [
                    'from' => $account->id,
                    'to' => $invitation->invitee_account_id,
                    'value' => 1,
                    'title' => 'Invited on ' . $invitation->created_at->format('M d, Y'),
                    'arrows' => 'to',
                    'color' => [
                        'color' => $invitation->status === 'completed' ? '#00DEA3' :
                            ($invitation->status === 'pending' ? '#5A55D2' : '#F56565')
                    ]
                ];
            }
        }

        $data = [
            'nodes' => $nodes,
            'edges' => $edges
        ];

        return response()->json($data);
    }

    /**
     * Mark a referral as completed.
     *
     * @param MexcReferral $referral
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsCompleted(MexcReferral $referral)
    {
        try {
            $referral->update([
                'status' => 'completed',
                'inviter_rewarded' => true,
                'invitee_rewarded' => true,
            ]);

            $this->activityService->log(
                'update',
                'mexc_referral',
                $referral,
                [
                    'status' => 'completed',
                    'rewards' => 'Both inviter and invitee rewarded $20 each',
                ]
            );

            return redirect()->route('referrals.index')->with('success', 'Referral marked as completed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating referral: ' . $e->getMessage());
        }
    }

    /**
     * Mark a referral as failed.
     *
     * @param MexcReferral $referral
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsFailed(MexcReferral $referral)
    {
        try {
            $referral->update([
                'status' => 'failed',
                'inviter_rewarded' => false,
                'invitee_rewarded' => false,
            ]);

            $this->activityService->log(
                'update',
                'mexc_referral',
                $referral,
                [
                    'status' => 'failed',
                    'message' => 'Referral requirements not met',
                ]
            );

            return redirect()->route('referrals.index')->with('success', 'Referral marked as failed.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating referral: ' . $e->getMessage());
        }
    }
}