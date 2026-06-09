<?php

namespace Botble\Auction\Http\Requests;

class UpdateAuctionRequest extends CreateAuctionRequest
{
    public function rules(): array
    {
        $auction = $this->route('auction');

        if ($auction && ! $auction->canVendorEditCriticalFields()) {
            return [
                'short_description' => ['required', 'string'],
                'description' => ['required', 'string'],
                'images' => ['required', 'array', 'min:1'],
                'images.*' => ['nullable', 'string'],
            ];
        }

        return parent::rules();
    }
}
