<?php

namespace Database\Seeders;

use Botble\ACL\Database\Seeders\UserSeeder;
use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Database\Seeders\CurrencySeeder;
use Botble\Ecommerce\Database\Seeders\DiscountSeeder;
use Botble\Ecommerce\Database\Seeders\ProductSpecificationSeeder;
use Botble\Ecommerce\Database\Seeders\ReviewSeeder;
use Botble\Ecommerce\Database\Seeders\ShippingSeeder;
use Botble\Ecommerce\Database\Seeders\TaxSeeder;

class DatabaseSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->prepareRun();

        $this->call([
            LanguageSeeder::class,
            BrandSeeder::class,
            CurrencySeeder::class,
            ProductCategorySeeder::class,
            ProductCollectionSeeder::class,
            ProductLabelSeeder::class,
            ProductAttributeSeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            ReviewSeeder::class,
            TaxSeeder::class,
            ProductTagSeeder::class,
            FlashSaleSeeder::class,
            DiscountSeeder::class,
            ShippingSeeder::class,
            ContactSeeder::class,
            UserSeeder::class,
            BlogSeeder::class,
            SimpleSliderSeeder::class,
            PageSeeder::class,
            AdsSeeder::class,
            FaqSeeder::class,
            SettingSeeder::class,
            ProductSpecificationSeeder::class,
            StoreLocatorSeeder::class,
            MenuSeeder::class,
            ThemeOptionSeeder::class,
            WidgetSeeder::class,
            ProductOptionSeeder::class,
            MarketplaceSeeder::class,
            TranslationSeeder::class,
        ]);

        $this->finished();
    }
}
