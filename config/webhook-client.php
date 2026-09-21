<?php

return [
    'configs' => [
        // ===== User Created =====
        [
            'name'                  => 'user-created',
            'signing_secret'        => env('WEBHOOK_CLIENT_SECRET_ADD_USER'),
            'signature_header_name' => 'Signature',
            'signature_validator'   => \App\Webhooks\SignatureValidator::class,
            'webhook_profile'       => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response'      => \App\Webhooks\WebhookResponse::class,
            'webhook_model'         => \Spatie\WebhookClient\Models\WebhookCall::class,
            'store_headers' => [
                'Signature',
                'timestamp',
            ],
            'store_attachments'     => true,
            'process_webhook_job'   => \App\Jobs\ProcessWebhookJob::class,
        ],

        // ===== User Updated =====
        [
            'name'                  => 'user-updated',
            'signing_secret'        => env('WEBHOOK_CLIENT_SECRET_UPDATE_USER'),
            'signature_header_name' => 'Signature',
            'signature_validator'   => \App\Webhooks\SignatureValidator::class,
            'webhook_profile'       => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response'      => \App\Webhooks\WebhookResponse::class,
            'webhook_model'         => \Spatie\WebhookClient\Models\WebhookCall::class,
            'store_headers' => [
                'Signature',
                'timestamp',
            ],
            'store_attachments'     => true,
            'process_webhook_job'   => \App\Jobs\ProcessWebhookJob::class,
        ],
    ],

    'delete_after_days' => 30,
    'add_unique_token_to_route_name' => false,
];
