<?php

namespace Botble\Auction\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAuctionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('customer')->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('auction_items', 'slug')->ignore($this->route('auction')),
            ],
            'short_description' => ['required', 'string'],
            'description' => ['required', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:ec_product_categories,id'],
            'condition' => ['required', Rule::in(['new', 'used', 'refurbished'])],
            'brand' => ['nullable', 'string', 'max:150'],
            'model' => ['nullable', 'string', 'max:150'],
            'starting_bid' => ['required', 'numeric', 'min:1'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'status' => ['required', Rule::in(['draft', 'published', 'scheduled', 'closed'])],
            'auto_winner_delay_hours' => ['required', 'integer', 'min:1', 'max:168'],
        ];
    }
}
