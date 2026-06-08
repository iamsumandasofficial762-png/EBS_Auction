<?php

namespace Botble\Auction\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'start_time' => ['nullable', 'date', 'required_if:status,scheduled'],
            'end_time' => ['required', 'date'],
            'status' => ['required', Rule::in(['draft', 'published', 'scheduled', 'closed'])],
            'auto_winner_delay_hours' => ['required', 'integer', 'min:1', 'max:168'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $status = $this->input('status');
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');

            if (! in_array($status, ['published', 'scheduled'])) {
                return;
            }

            if (! $endTime) {
                return;
            }

            $now = Carbon::now(config('app.timezone'));
            $end = Carbon::parse($endTime);

            if ($status === 'published') {
                if ($end->lessThanOrEqualTo($now)) {
                    $validator->errors()->add('end_time', __('End time must be in the future for a published auction.'));
                }

                return;
            }

            if (! $startTime) {
                return;
            }

            $start = Carbon::parse($startTime);

            if ($start->lessThan($now->copy()->subMinute())) {
                $validator->errors()->add('start_time', __('Start time must be now or later for a scheduled auction.'));
            }

            if ($end->lessThanOrEqualTo($start)) {
                $validator->errors()->add('end_time', __('End time must be after start time for a scheduled auction.'));
            }
        });
    }
}
