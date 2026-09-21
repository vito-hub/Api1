<?php

namespace App\Jobs;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob as SpatieProcessWebhookJob;

class ProcessWebhookJob extends SpatieProcessWebhookJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $payload = $this->webhookCall->payload ?? [];

        $eventType = $payload['type'] ?? null;

        match ($eventType) {
            'user.created' => $this->handleUserCreated($payload),
            'user.updated' => $this->handleUserUpdate($payload),
            default => $this->handleUnknownEvent($eventType),
        };
    }

    private function handleUserCreated(array $payload): void
    {
        $data = $payload['data'] ?? [];

        logger()->info('user.created webhook processed.', [
            'webhook_call_id'   => $this->webhookCall->id,
            'event_id'          => $payload['id'] ?? null,
            'user_id'           => $data['user_id'] ?? null,
        ]);

    }
    private function handleUserUpdate(array $payload): void
    {
        $data = $payload['data'] ?? [];

        logger()->info('user.created webhook processed.', [
            'webhook_call_id'   => $this->webhookCall->id,
            'event_id'          => $payload['id'] ?? null,
            'user_id'           => $data['user_id'] ?? null,
        ]);
    }

    private function handleUnknownEvent(?string $eventType): void
    {
        logger()->warning('Unknown webhook event received.', [
            'event_type'        => $eventType,
            'webhook_call_id'   => $this->webhookCall->id,
        ]);
    }
}
