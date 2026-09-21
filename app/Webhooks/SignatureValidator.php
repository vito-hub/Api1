<?php

namespace App\Webhooks;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookClient\Exceptions\InvalidConfig;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator as SignatureValidatorContract;
use Spatie\WebhookClient\WebhookConfig;

class SignatureValidator implements SignatureValidatorContract
{
    private const TIMESTAMP_TOLERANCE = 300;

    /**
     * @throws InvalidConfig
     */

    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $signature      = $request->header($config->signatureHeaderName);
        $timestamp      = $request->header('timestamp');
        $payload        = $request->getContent();
        $signingSecret  = $config->signingSecret;

        $computedSignature = hash_hmac(
            'sha256',
            $timestamp . $payload,
            $signingSecret
        );

        return hash_equals($computedSignature, $signature);
    }
}
