<?php

declare(strict_types=1);

namespace Iprote\TcbCms\Http\Controllers;

use Iprote\TcbCms\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WebhookController extends Controller
{
    public function __construct(
        protected WebhookService $webhookService,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $result = $this->webhookService->handle($request);

        return response()->json($result);
    }
}
