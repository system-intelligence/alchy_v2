<?php

namespace App\Http\Controllers;

use App\Models\ReceiptVerification;
use Illuminate\Http\Request;

class ReceiptVerificationController extends Controller
{
    public function verify($hash)
    {
        $verification = ReceiptVerification::with('project.client')
            ->where('verification_hash', $hash)
            ->first();

        if (!$verification) {
            return view('verify-receipt', [
                'status' => 'invalid',
                'message' => 'Invalid verification code. This receipt could not be verified.',
                'verification' => null,
            ]);
        }

        // Increment verification count
        $verification->incrementVerification();

        return view('verify-receipt', [
            'status' => 'valid',
            'message' => 'This receipt is authentic and verified.',
            'verification' => $verification,
        ]);
    }

    public function scanner()
    {
        return view('receipt-scanner');
    }
}
