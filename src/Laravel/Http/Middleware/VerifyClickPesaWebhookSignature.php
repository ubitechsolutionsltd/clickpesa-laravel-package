<?php

declare(strict_types=1);

namespace ClickPesa\Laravel\Http\Middleware;

use Closure;
use ClickPesa\Security\Checksum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyClickPesaWebhookSignature
{
    /**
     * Handle an incoming webhook request and verify signature.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $checksumKey = config('clickpesa.checksum_key');

        if (!empty($checksumKey)) {
            $payload = $request->all();
            $receivedChecksum = $request->header('x-clickpesa-checksum')
                ?? $request->header('checksum')
                ?? ($payload['checksum'] ?? null);

            if (empty($receivedChecksum) || !Checksum::verify($checksumKey, $payload, (string) $receivedChecksum)) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid ClickPesa webhook signature.',
                ], 400);
            }
        }

        return $next($request);
    }
}
