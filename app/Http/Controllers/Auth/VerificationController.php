<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function verify(EmailVerificationRequest $request)
{
    // Ensure the user is authenticated
    if (!$request->user()) {
        return response()->json(['message' => 'User  not authenticated.'], 401);
    }

    // Fulfill the email verification request
    $request->fulfill();

    // Return a JSON response with a redirect URL
    return response()->json([
        'message' => 'Email verified successfully.',
        'redirect_url' => 'http://localhost:5173/' // The URL to redirect to
    ]);
}

    public function resend(Request $request)
    {
        // Ensure the user is authenticated
        if (!$request->user()) {
            return response()->json(['message' => 'User  not authenticated.'], 401);
        }

        // Check if the email is already verified
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        // Send the email verification notification
        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent!']);
    }
}
