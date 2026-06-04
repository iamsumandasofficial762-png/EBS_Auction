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
            'description' => ['nullable', 'string'],
            'images' => ['nullable'],
            'category_id' => ['nullable', 'integer', 'exists:ec_product_categories,id'],
            'starting_bid' => ['required', 'numeric', 'min:0'],
            'bid_increment' => ['required', 'numeric', 'min:0.01'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'status' => ['required', Rule::in(['draft', 'scheduled', 'live', 'closed', 'cancelled'])],
        ];
    }
}
