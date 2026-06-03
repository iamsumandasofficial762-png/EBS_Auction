<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Faq\Models\Faq;
use Botble\Faq\Models\FaqCategory;

class FaqSeeder extends BaseSeeder
{
    public function run(): void
    {
        Faq::query()->truncate();
        FaqCategory::query()->truncate();

        $categories = [
            [
                'name' => 'SHIPPING',
            ],
            [
                'name' => 'PAYMENT',
            ],
            [
                'name' => 'ORDER & RETURNS',
            ],
        ];

        foreach ($categories as $index => $value) {
            $value['order'] = $index;
            FaqCategory::query()->create($value);
        }

        $faqItems = [
            [
                'question' => 'What Shipping Methods Are Available?',
                'answer' => 'We offer several shipping methods including Standard Shipping, Express Shipping, and Overnight Delivery. You can select your preferred method at checkout.',
                'category_id' => 1,
            ],
            [
                'question' => 'Do You Ship Internationally?',
                'answer' => 'Yes, we ship to over 200 countries worldwide. International shipping rates and delivery times vary by location.',
                'category_id' => 1,
            ],
            [
                'question' => 'How Long Will It Take To Get My Package?',
                'answer' => 'Standard shipping typically takes 3-5 business days. Express shipping takes 1-2 business days. International orders may take 7-14 business days depending on customs.',
                'category_id' => 1,
            ],
            [
                'question' => 'What Payment Methods Are Accepted?',
                'answer' => 'We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and Apple Pay. We also support local payment methods depending on your region.',
                'category_id' => 2,
            ],
            [
                'question' => 'Is Buying On-Line Safe?',
                'answer' => 'Yes, buying online with us is completely safe. We use SSL encryption to protect your personal and payment information. We do not store your credit card details.',
                'category_id' => 2,
            ],
            [
                'question' => 'How do I place an Order?',
                'answer' => 'To place an order, browse our catalog, select the items you like, and add them to your cart. Then proceed to checkout, enter your shipping and payment details, and confirm your order.',
                'category_id' => 3,
            ],
            [
                'question' => 'How Can I Cancel Or Change My Order?',
                'answer' => 'You can cancel or change your order within 1 hour of placing it by contacting our customer support. After that, we may have already processed it, but we will do our best to assist you.',
                'category_id' => 3,
            ],
            [
                'question' => 'Do I need an account to place an order?',
                'answer' => 'No, you can place an order as a guest. However, creating an account allows you to track your orders, save your address for faster checkout, and access exclusive offers.',
                'category_id' => 3,
            ],
            [
                'question' => 'How Do I Track My Order?',
                'answer' => 'Once your order is shipped, we will send you a tracking number via email. You can use this number to track your package on our website or the carrier\'s tracking page.',
                'category_id' => 3,
            ],
            [
                'question' => 'How Can I Return a Product?',
                'answer' => 'We accept returns within 30 days of purchase. The item must be unused and in its original packaging. Please contact our support team to initiate a return and get a return shipping label.',
                'category_id' => 3,
            ],
        ];

        foreach ($faqItems as $value) {
            Faq::query()->create($value);
        }
    }
}
