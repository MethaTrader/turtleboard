<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OnboardingController extends Controller
{
    /**
     * Mark onboarding as completed for the user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function complete(Request $request): JsonResponse
    {
        // Get the authenticated user
        $user = $request->user();

        // Mark onboarding as completed
        $user->has_completed_onboarding = true;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Onboarding marked as completed'
        ]);
    }

    /**
     * Reset onboarding status for the user (for testing)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reset(Request $request): JsonResponse
    {
        // Only allow in non-production environments
        if (app()->environment('production')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reset onboarding in production'
            ], 403);
        }

        // Get the authenticated user
        $user = $request->user();

        // Reset onboarding status
        $user->has_completed_onboarding = false;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Onboarding status has been reset'
        ]);
    }
}