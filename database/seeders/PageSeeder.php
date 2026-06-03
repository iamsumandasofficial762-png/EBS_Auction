<?php

namespace Database\Seeders;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\BaseSeeder;
use Botble\Page\Models\Page;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Illuminate\Support\Str;

class PageSeeder extends BaseSeeder
{
    public function run(): void
    {
        $pages = [
            [
                'name' => 'Home',
                'content' =>
                    Html::tag(
                        'div',
                        '[simple-slider key="home-slider" ads_1="VC2C8Q1UGCBG" ads_2="NBDWRXTSVZ8N"][/simple-slider]'
                    ) .
                    Html::tag(
                        'div',
                        '[site-features icon1="icon-rocket" title1="Free Delivery" subtitle1="For all orders over $99" icon2="icon-sync" title2="90 Days Return" subtitle2="If goods have problems" icon3="icon-credit-card" title3="Secure Payment" subtitle3="100% secure payment" icon4="icon-bubbles" title4="24/7 Support" subtitle4="Dedicated support" icon5="icon-gift" title5="Gift Service" subtitle5="Support gift service" enable_lazy_loading="yes"][/site-features]'
                    ) .
                    Html::tag('div', '[flash-sale title="Deal of the day" flash_sale_id="1" enable_lazy_loading="yes"][/flash-sale]') .
                    Html::tag(
                        'div',
                        '[featured-product-categories title="Top Categories" enable_lazy_loading="yes"][/featured-product-categories]'
                    ) .
                    Html::tag(
                        'div',
                        '[theme-ads key_1="IZ6WU8KUALYD" key_2="ILSFJVYFGCPZ" key_3="ZDOZUZZIU7FT" enable_lazy_loading="yes"][/theme-ads]'
                    ) .
                    Html::tag('div', '[featured-products title="Featured products" enable_lazy_loading="yes"][/featured-products]') .
                    Html::tag(
                        'div',
                        '[theme-ads key_1="Q9YDUIC9HSWS" key_2="IZ6WU8KUALYE" enable_lazy_loading="yes"][/theme-ads]'
                    ) .
                    Html::tag('div', '[product-collections title="Exclusive Products" enable_lazy_loading="yes"][/product-collections]') .
                    Html::tag('div', '[product-category-products category_id="18" enable_lazy_loading="yes"][/product-category-products]') .
                    '[testimonials title="What our Clients say" subtitle="Customers Review" testimonial_ids="1,2,3,4" enable_lazy_loading="yes"][/testimonials]' .
                    Html::tag(
                        'div',
                        '[download-app title="Download Martfury App Now!" subtitle="Shopping fastly and easily more with our app. Get a link to download the app on your phone." screenshot="general/app.png" android_app_url="https://www.appstore.com" ios_app_url="https://play.google.com/store" enable_lazy_loading="yes"][/download-app]'
                    ) .
                    Html::tag('div', '[product-category-products category_id="23" enable_lazy_loading="yes"][/product-category-products]') .
                    Html::tag(
                        'div',
                        '[newsletter-form title="Join Our Newsletter Now" subtitle="Subscribe to get information about products and coupons" enable_lazy_loading="yes"][/newsletter-form]'
                    )
                ,
                'template' => 'homepage',
            ],
            [
                'name' => 'About us',
            ],
            [
                'name' => 'Terms Of Use',
            ],
            [
                'name' => 'Terms & Conditions',
            ],
            [
                'name' => 'Refund Policy',
            ],
            [
                'name' => 'Blog',
                'content' => Html::tag('p', '---'),
                'template' => 'blog-sidebar',
            ],
            [
                'name' => 'FAQs',
                'content' => Html::tag('div', '[faq title="Frequently Asked Questions"][/faq]'),
            ],
            [
                'name' => 'Contact',
                'content' => Html::tag('div', '[google-map]502 New Street, Brighton VIC, Australia[/google-map]') .
                    Html::tag(
                        'div',
                        '[contact-info-boxes title="Contact Us For Any Questions"][/contact-info-boxes]'
                    ) .
                    Html::tag('div', '[contact-form][/contact-form]')
                ,
                'template' => 'full-width',
            ],
            [
                'name' => 'Cookie Policy',
                'content' => Html::tag('h3', 'EU Cookie Consent') .
                    Html::tag(
                        'p',
                        'To use this Website we are using Cookies and collecting some Data. To be compliant with the EU GDPR we give you to choose if you allow us to use certain Cookies and to collect some Data.'
                    ) .
                    Html::tag('h4', 'Essential Data') .
                    Html::tag(
                        'p',
                        'The Essential Data is needed to run the Site you are visiting technically. You can not deactivate them.'
                    ) .
                    Html::tag(
                        'p',
                        '- Session Cookie: PHP uses a Cookie to identify user sessions. Without this Cookie the Website is not working.'
                    ) .
                    Html::tag(
                        'p',
                        '- XSRF-Token Cookie: Laravel automatically generates a CSRF "token" for each active user session managed by the application. This token is used to verify that the authenticated user is the one actually making the requests to the application.'
                    ),
            ],
            [
                'name' => 'Affiliate',
            ],
            [
                'name' => 'Career',
            ],
            [
                'name' => 'Coming soon',
                'content' => Html::tag(
                    'p',
                    'Condimentum ipsum a adipiscing hac dolor set consectetur urna commodo elit parturient <br/>molestie ut nisl partu convallier ullamcorpe.'
                ) .
                    Html::tag(
                        'div',
                        sprintf('[coming-soon time="%s" image="general/coming-soon.jpg"][/coming-soon]', $this->now()->addYear()->toDateTimeString())
                    ),
                'template' => 'coming-soon',
            ],
        ];

        Page::query()->truncate();

        foreach ($pages as $item) {
            $item['user_id'] = 1;

            if (! isset($item['template'])) {
                $item['template'] = 'default';
            }

            if (! isset($item['content'])) {
                $content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
                $item['content'] = Html::tag('p', $content) . Html::tag('p', $content) .
                    Html::tag('p', $content) . Html::tag('p', $content);
            }

            $page = Page::query()->create($item);

            Slug::query()->create([
                'reference_type' => Page::class,
                'reference_id' => $page->id,
                'key' => Str::slug($page->name),
                'prefix' => SlugHelper::getPrefix(Page::class),
            ]);
        }
    }
}
