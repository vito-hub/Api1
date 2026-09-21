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
//    public function isValid(Request $request, WebhookConfig $config): bool
//    {
//        Log::info('CUSTOM SIGNATURE VALIDATOR CALLED');
//
//        $signature = $request->header($config->signatureHeaderName);
//
//        if (! $signature) {
//            return false;
//        }
//
//        $timestamp = $request->header('timestamp');
//
//        if (! $timestamp) {
//            return false;
//        }
//
//        $signingSecret = $config->signingSecret;
//
//        if (empty($signingSecret)) {
//            throw InvalidConfig::signingSecretNotSet();
//        }
//
//        $payload = $request->getContent();
//
//        $computedSignature = hash_hmac(
//            'sha256',
//            $timestamp . $payload,
//            $signingSecret
//        );
//        $isValid = hash_equals($computedSignature, $signature);
//
//        Log::info('Webhook signature result', [
//            'is_valid' => $isValid,
//        ]);
//
//        return hash_equals($computedSignature, $signature);
//    }


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
