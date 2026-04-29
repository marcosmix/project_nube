<?php

namespace App\Http\Controllers\WhatsApp;

use App\Http\Controllers\Controller;
use App\Jobs\WhatsApp\ProcessIncomingWhatsAppMessageJob;
use App\Models\WhatsappSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    public function verify(Request $request): Response
    {
        $settings = WhatsappSetting::current();

        if (! $settings->enabled || ! $settings->isConfigured()) {
            abort(404);
        }

        if ($request->query('hub_verify_token') !== $settings->webhook_verify_token) {
            abort(403);
        }

        return response((string) $request->query('hub_challenge', ''), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function handle(Request $request): JsonResponse
    {
        $settings = WhatsappSetting::current();

        if (! $settings->enabled) {
            return response()->json(['status' => 'disabled'], 202);
        }

        ProcessIncomingWhatsAppMessageJob::dispatch($request->all());

        return response()->json(['status' => 'accepted']);
    }
}
