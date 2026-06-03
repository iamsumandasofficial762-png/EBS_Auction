<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\Address;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('customers');

        Customer::query()->truncate();
        Address::query()->truncate();

        $names = ['John Doe', 'Jane Smith', 'Robert Brown', 'Emily White', 'Michael Green', 'Sarah Black', 'David Blue', 'Jennifer Red', 'Chris Mint', 'Anna Orange'];
        $cities = ['New York', 'London', 'Paris', 'Berlin', 'Tokyo', 'Hanoi', 'Seoul', 'Melbourne'];
        $states = ['California', 'New York', 'Texas', 'Washington', 'Florida'];
        $countries = ['US', 'UK', 'VN', 'JP', 'KR', 'AU'];
        $streets = ['123 Main St', '456 Elm St', '789 Pine St', '101 Oak St', '202 Maple St'];
        $zipCodes = ['10001', '20002', '30003', '40004', '50005'];

        $customers = [
            'customer@botble.com',
            'vendor@botble.com',
        ];

        foreach ($customers as $item) {
            $customer = Customer::query()->create([
                'name' => Arr::random($names),
                'email' => $item,
                'password' => Hash::make('12345678'),
                'phone' => '+1555' . rand(1000000, 9999999),
                'avatar' => 'customers/' . rand(1, 10) . '.jpg',
                'dob' => now()->subYears(rand(20, 50))->subDays(rand(1, 30)),
            ]);

            $customer->confirmed_at = now();
            $customer->save();

            Address::query()->create([
                'name' => $customer->name,
                'phone' => '+1555' . rand(1000000, 9999999),
                'email' => $customer->email,
                'country' => Arr::random($countries),
                'state' => Arr::random($states),
                'city' => Arr::random($cities),
                'address' => Arr::random($streets),
                'zip_code' => Arr::random($zipCodes),
                'customer_id' => $customer->id,
                'is_default' => true,
            ]);

            Address::query()->create([
                'name' => $customer->name,
                'phone' => '+1555' . rand(1000000, 9999999),
                'email' => $customer->email,
                'country' => Arr::random($countries),
                'state' => Arr::random($states),
                'city' => Arr::random($cities),
                'address' => Arr::random($streets),
                'zip_code' => Arr::random($zipCodes),
                'customer_id' => $customer->id,
                'is_default' => false,
            ]);
        }

        for ($i = 0; $i < 8; $i++) {
            $customer = Customer::query()->create([
                'name' => Arr::random($names),
                'email' => 'customer_' . ($i + 1) . '@example.com',
                'password' => Hash::make('12345678'),
                'phone' => '+1555' . rand(1000000, 9999999),
                'avatar' => 'customers/' . ($i + 1) . '.jpg',
                'dob' => now()->subYears(rand(20, 50))->subDays(rand(1, 30)),
            ]);

            $customer->confirmed_at = now();
            $customer->save();

            Address::query()->create([
                'name' => $customer->name,
                'phone' => '+1555' . rand(1000000, 9999999),
                'email' => $customer->email,
                'country' => Arr::random($countries),
                'state' => Arr::random($states),
                'city' => Arr::random($cities),
                'address' => Arr::random($streets),
                'zip_code' => Arr::random($zipCodes),
                'customer_id' => $customer->id,
                'is_default' => true,
            ]);
        }
    }
}
