<?php

namespace Botble\Auction\Services;

use Botble\Media\Facades\RvMedia;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

class GeminiAuctionAssistantService
{
    public function suggestPrice(array $details): array
    {
        return $this->generate($this->pricePrompt($details), Arr::get($details, 'image'), [
            'suggested_starting_bid',
            'market_price_min',
            'market_price_max',
            'reason',
            'confidence',
        ]);
    }

    public function generateDescription(array $details): array
    {
        return $this->generate($this->descriptionPrompt($details), Arr::get($details, 'image'), [
            'short_description',
            'full_description',
        ]);
    }

    public function isConfigured(): bool
    {
        return (bool) $this->apiKey();
    }

    protected function generate(string $prompt, ?string $image, array $requiredKeys): array
    {
        if (! $this->isConfigured()) {
            return [
                'error' => __('Gemini API key is not configured.'),
            ];
        }

        $textParts = [
            ['text' => $prompt],
        ];

        $parts = $textParts;

        if ($imagePart = $this->imagePart($image)) {
            $parts[] = $imagePart;
        }

        $result = $this->requestGemini($parts);

        if (($result['quota'] ?? false) && $imagePart) {
            $result = $this->requestGemini($textParts);
        }

        if ($error = ($result['error'] ?? null)) {
            return [
                'error' => $error,
            ];
        }

        $data = $this->parseJson((string) data_get($result['response']->json(), 'candidates.0.content.parts.0.text'));

        if (! $data || array_diff($requiredKeys, array_keys($data))) {
            return [
                'error' => __('AI could not generate a valid response. Please try again.'),
            ];
        }

        return [
            'data' => $data,
        ];
    }

    protected function requestGemini(array $parts): array
    {
        $lastResponse = null;

        foreach ($this->models() as $model) {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(30)
                    ->acceptJson()
                    ->post($this->endpoint($model), [
                        'contents' => [
                            [
                                'parts' => $parts,
                            ],
                        ],
                        'generationConfig' => [
                            'temperature' => 0.4,
                            'maxOutputTokens' => 768,
                            'responseMimeType' => 'application/json',
                        ],
                    ]);
            } catch (Throwable) {
                return [
                    'error' => __('AI service is temporarily unavailable.'),
                ];
            }

            $lastResponse = $response;

            if ($response->successful()) {
                return [
                    'response' => $response,
                ];
            }

            $message = (string) data_get($response->json(), 'error.message');

            if (! $this->isModelError($response->status(), $message)) {
                return [
                    'error' => $this->apiErrorMessage($response->status(), $message),
                    'quota' => $this->isQuotaError($response->status(), $message),
                ];
            }
        }

        if (! $lastResponse || ! $lastResponse->successful()) {
            return [
                'error' => __('Gemini model is not available. Please set GEMINI_MODEL=gemini-2.0-flash in .env.'),
            ];
        }

        return [
            'response' => $lastResponse,
        ];
    }

    protected function pricePrompt(array $details): string
    {
        return sprintf(
            "You are helping a vendor create an online auction listing in India.\nAnalyze the product details and image if provided.\nSuggest a fair starting bid in INR, not the full retail price.\nThe starting bid should attract bidders but still protect seller value.\nReturn JSON only with:\nsuggested_starting_bid,\nmarket_price_min,\nmarket_price_max,\nreason,\nconfidence.\nProduct details:\nTitle: %s\nBrand: %s\nModel: %s\nCondition: %s\nCategory: %s\nCurrency: INR\n\nRules:\n- suggested_starting_bid must be numeric.\n- suggested_starting_bid should normally be lower than market_price_min.\n- For used item, suggest lower price.\n- For new item, suggest stronger price.\n- If unsure, say confidence low.",
            Arr::get($details, 'title', ''),
            Arr::get($details, 'brand', ''),
            Arr::get($details, 'model', ''),
            Arr::get($details, 'condition', ''),
            Arr::get($details, 'category', '')
        );
    }

    protected function descriptionPrompt(array $details): string
    {
        return sprintf(
            "You are helping a vendor write an auction listing.\nGenerate an attractive but honest auction description.\nDo not invent technical details that are not provided.\nReturn JSON only with:\nshort_description,\nfull_description.\nShort description max 220 characters.\nFull description should be clear, professional, and suitable for an ecommerce auction page.\nProduct details:\nTitle: %s\nBrand: %s\nModel: %s\nCondition: %s\nCategory: %s",
            Arr::get($details, 'title', ''),
            Arr::get($details, 'brand', ''),
            Arr::get($details, 'model', ''),
            Arr::get($details, 'condition', ''),
            Arr::get($details, 'category', '')
        );
    }

    protected function endpoint(string $model): string
    {
        $model = str($model)->replaceStart('models/', '')->toString();

        return sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            rawurlencode($model),
            urlencode($this->apiKey())
        );
    }

    protected function apiKey(): ?string
    {
        $apiKey = config('plugins.auction.auction.gemini_api_key');

        return $apiKey ? trim((string) $apiKey) : null;
    }

    protected function models(): array
    {
        return collect([
            config('plugins.auction.auction.gemini_model', 'gemini-2.0-flash'),
            'gemini-2.0-flash',
            'gemini-1.5-flash-latest',
        ])
            ->filter()
            ->map(fn ($model) => str((string) $model)->replaceStart('models/', '')->toString())
            ->unique()
            ->values()
            ->all();
    }

    protected function parseJson(string $text): ?array
    {
        $text = trim($text);

        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $text) ?: $text;
        }

        if (! str_starts_with($text, '{') && preg_match('/\{.*}/s', $text, $matches)) {
            $text = $matches[0];
        }

        $decoded = json_decode($text, true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function apiErrorMessage(int $status, string $message): string
    {
        $message = strtolower($message);

        if (in_array($status, [400, 404]) && str_contains($message, 'model')) {
            return __('Gemini model is not available. Please check GEMINI_MODEL in .env.');
        }

        if (in_array($status, [400, 401, 403]) && (str_contains($message, 'api key') || str_contains($message, 'permission') || str_contains($message, 'auth'))) {
            return __('Gemini API key is invalid. Please use a Google AI Studio API key.');
        }

        if ($this->isQuotaError($status, $message)) {
            return __('Gemini quota is temporarily reached. Please wait a few minutes and try again.');
        }

        return __('AI service is temporarily unavailable.');
    }

    protected function isQuotaError(int $status, string $message): bool
    {
        $message = strtolower($message);

        return $status === 429 || str_contains($message, 'quota') || str_contains($message, 'rate limit');
    }

    protected function isModelError(int $status, string $message): bool
    {
        $message = strtolower($message);

        return in_array($status, [400, 404]) && str_contains($message, 'model');
    }

    protected function imagePart(?string $image): ?array
    {
        $path = $this->resolveImagePath($image);

        if (! $path || ! File::exists($path) || ! File::isFile($path)) {
            return null;
        }

        $content = File::get($path);
        $mimeType = File::mimeType($path) ?: 'image/jpeg';

        if (! str_starts_with($mimeType, 'image/')) {
            return null;
        }

        return [
            'inline_data' => [
                'mime_type' => $mimeType,
                'data' => base64_encode($content),
            ],
        ];
    }

    protected function resolveImagePath(?string $image): ?string
    {
        if (! $image) {
            return null;
        }

        if (filter_var($image, FILTER_VALIDATE_URL)) {
            $urlPath = parse_url($image, PHP_URL_PATH);
            $image = $urlPath ? ltrim($urlPath, '/') : $image;
        }

        $image = ltrim($image, '/');

        $paths = array_filter([
            rescue(fn () => RvMedia::getRealPath($image), null, false),
            public_path($image),
            public_path('storage/' . $image),
            storage_path('app/public/' . $image),
        ]);

        foreach ($paths as $path) {
            if (File::exists($path) && File::isFile($path)) {
                return $path;
            }
        }

        return null;
    }
}
