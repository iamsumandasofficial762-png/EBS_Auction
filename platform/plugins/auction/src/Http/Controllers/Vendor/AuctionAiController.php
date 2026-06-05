<?php

namespace Botble\Auction\Http\Controllers\Vendor;

use Botble\Auction\Services\GeminiAuctionAssistantService;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuctionAiController extends BaseController
{
    public function __construct(protected GeminiAuctionAssistantService $assistant)
    {
    }

    public function suggestPrice(Request $request): JsonResponse
    {
        $data = $this->validateRequest($request);

        $result = $this->assistant->suggestPrice($data);

        if ($error = $result['error'] ?? null) {
            return response()->json([
                'success' => false,
                'message' => $error,
            ], 422);
        }

        $priceData = $result['data'];

        return response()->json([
            'success' => true,
            'data' => [
                'suggested_starting_bid' => (float) $priceData['suggested_starting_bid'],
                'market_price_min' => (float) $priceData['market_price_min'],
                'market_price_max' => (float) $priceData['market_price_max'],
                'reason' => (string) $priceData['reason'],
                'confidence' => (string) $priceData['confidence'],
            ],
        ]);
    }

    public function generateDescription(Request $request): JsonResponse
    {
        $data = $this->validateRequest($request);

        $result = $this->assistant->generateDescription($data);

        if ($error = $result['error'] ?? null) {
            return response()->json([
                'success' => false,
                'message' => $error,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'fallback' => (bool) ($result['fallback'] ?? false),
            'message' => ($result['fallback'] ?? false)
                ? __('Gemini quota is temporarily reached, so a basic draft was generated from your product details.')
                : null,
            'data' => [
                'short_description' => (string) $result['data']['short_description'],
                'full_description' => (string) $result['data']['full_description'],
            ],
        ]);
    }

    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:150'],
            'model' => ['nullable', 'string', 'max:150'],
            'condition' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
        ]);
    }
}
