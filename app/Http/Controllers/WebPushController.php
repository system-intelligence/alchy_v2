<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebPushController extends Controller
{
    /**
     * Store or update a push subscription for the authenticated user.
     * Expected payload from PushManager.subscribe():
     * {
     *   endpoint: string,
     *   expirationTime: null|int,
     *   keys: { p256dh: string, auth: string }
     * }
     */
    public function subscribe(Request $request)
    {
        $this->validate($request, [
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $endpoint = $request->input('endpoint');
        $key = $request->input('keys.p256dh');
        $token = $request->input('keys.auth');

        // Modern browsers use aes128gcm, some older ones might use aesgcm
        $contentEncoding = $request->input('contentEncoding', 'aes128gcm');

        $request->user()->updatePushSubscription(
            endpoint: $endpoint,
            key: $key,
            token: $token,
            contentEncoding: $contentEncoding
        );

        return response()->json(['status' => 'subscribed'], Response::HTTP_CREATED);
    }

    /**
     * Delete a push subscription for the authenticated user.
     * Accepts either:
     * - endpoint in body
     * - or deletes all if no endpoint provided
     */
    public function unsubscribe(Request $request)
    {
        $endpoint = $request->input('endpoint');

        if ($endpoint) {
            $request->user()->deletePushSubscription($endpoint);
        } else {
            // Remove all subscriptions for this user (e.g., when logging out)
            foreach ($request->user()->pushSubscriptions as $sub) {
                $request->user()->deletePushSubscription($sub->endpoint);
            }
        }

        return response()->json(['status' => 'unsubscribed'], Response::HTTP_OK);
    }
}