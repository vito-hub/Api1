<?php

namespace App\Webhooks;

use Illuminate\Http\Response;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookResponse\RespondsToWebhook;
use Illuminate\Http\Request;

class WebhookResponse implements RespondsToWebhook
{
    public function respondToValidWebhook(
        Request $request,
        WebhookConfig $config
    ): \Symfony\Component\HttpFoundation\Response
    {
        $payload = $request->json()->all();

        if (isset($payload['challenge'])) {
            return response($payload['challenge'], 200)
                ->header('Content-Type', 'text/plain');
        }

        return response()->json([
            'message' => 'ok',
        ], 200);
    }
}
