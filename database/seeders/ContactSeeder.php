<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Contact\Enums\ContactStatusEnum;
use Botble\Contact\Models\Contact;
use Illuminate\Support\Arr;

class ContactSeeder extends BaseSeeder
{
    public function run(): void
    {
        Contact::query()->truncate();

        $names = ['John Doe', 'Jane Smith', 'Robert Brown', 'Emily White', 'Michael Green'];
        $subjects = ['General Inquiry', 'Support Request', 'Feedback', 'Other'];
        $statuses = [ContactStatusEnum::READ, ContactStatusEnum::UNREAD];

        for ($i = 0; $i < 10; $i++) {
            Contact::query()->create([
                'name' => Arr::random($names),
                'email' => 'contact_' . ($i + 1) . '@example.com',
                'phone' => '+1555' . rand(1000000, 9999999),
                'address' => '123 Main St, New York, US',
                'subject' => Arr::random($subjects),
                'content' => 'This is a sample contact message content for testing purposes.',
                'status' => Arr::random($statuses),
            ]);
        }
    }
}
