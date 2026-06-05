<?php

return [
    'gemini_api_key' => env('GEMINI_API_KEY'),
    'gemini_model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    'closed_visible_hours' => env('AUCTION_CLOSED_VISIBLE_HOURS', 8),
    'lost_visible_hours' => env('AUCTION_LOST_VISIBLE_HOURS', 8),
];
