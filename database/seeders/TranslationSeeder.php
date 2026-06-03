<?php

namespace Database\Seeders;

use Botble\Ecommerce\Models\ProductCategory;
use Botble\Language\Models\LanguageMeta;
use Botble\LanguageAdvanced\Database\Seeders\BaseTranslationSeeder;
use Botble\LanguageAdvanced\Database\Seeders\Traits\HasMenuTranslationSeeder;
use Botble\LanguageAdvanced\Database\Seeders\Traits\HasPageTranslation;
use Botble\LanguageAdvanced\Database\Seeders\Traits\HasThemeOptionSeeder;
use Botble\LanguageAdvanced\Database\Seeders\Traits\HasWidgetSeeder;
use Botble\Menu\Facades\Menu;
use Botble\Menu\Models\Menu as MenuModel;
use Botble\Menu\Models\MenuLocation;
use Botble\Menu\Models\MenuNode;
use Botble\Page\Models\Page;
use Botble\SimpleSlider\Models\SimpleSlider;
use Botble\SimpleSlider\Models\SimpleSliderItem;
use Illuminate\Support\Arr;

class TranslationSeeder extends BaseTranslationSeeder
{
    use HasMenuTranslationSeeder;
    use HasPageTranslation;
    use HasThemeOptionSeeder;
    use HasWidgetSeeder;

    public function run(): void
    {
        $locales = ['ar', 'vi', 'fr', 'id', 'tr'];

        $this->seedPageTranslations($locales);
        $this->seedThemeOptions($locales);
        $this->seedAllTranslatableModelsFromJson($locales);
        $this->seedMenuTranslations($locales);
        $this->seedSimpleSliderTranslations($locales);
        $this->seedWidgets($locales);
    }

    protected function getSkippedTables(): array
    {
        return ['pages'];
    }

    protected function seedMenuTranslations(array $locales): void
    {
        $mainMenu = MenuModel::query()->where('slug', 'main-menu')->first();
        $quickLinksMenu = MenuModel::query()->where('slug', 'quick-links')->first();
        $companyMenu = MenuModel::query()->where('slug', 'company')->first();
        $businessMenu = MenuModel::query()->where('slug', 'business')->first();

        if (! $mainMenu && ! $quickLinksMenu && ! $companyMenu && ! $businessMenu) {
            return;
        }

        $mainMenuOrigin = $mainMenu ? $this->getLanguageMetaOrigin($mainMenu) : null;
        $quickLinksMenuOrigin = $quickLinksMenu ? $this->getLanguageMetaOrigin($quickLinksMenu) : null;
        $companyMenuOrigin = $companyMenu ? $this->getLanguageMetaOrigin($companyMenu) : null;
        $businessMenuOrigin = $businessMenu ? $this->getLanguageMetaOrigin($businessMenu) : null;

        $mainMenuLocation = $mainMenu
            ? MenuLocation::query()
                ->where('menu_id', $mainMenu->getKey())
                ->where('location', 'main-menu')
                ->first()
            : null;

        $locationOrigin = $mainMenuLocation ? $this->getLanguageMetaOrigin($mainMenuLocation) : null;

        $pageIds = Page::query()->pluck('id', 'name')->all();

        foreach ($locales as $locale) {
            $translations = $this->loadMenuTranslations($locale);

            if (empty($translations)) {
                continue;
            }

            if ($mainMenu) {
                $this->createMenuTranslation(
                    $locale,
                    'main-menu',
                    $translations['name'],
                    $this->buildMainMenuItems($translations, $pageIds),
                    $mainMenuOrigin,
                    $locationOrigin
                );
            }

            if ($quickLinksMenu) {
                $this->createMenuTranslation(
                    $locale,
                    'quick-links',
                    $translations['quick_links'] ?? 'Quick links',
                    $this->buildQuickLinksMenuItems($translations, $pageIds),
                    $quickLinksMenuOrigin,
                    null
                );
            }

            if ($companyMenu) {
                $this->createMenuTranslation(
                    $locale,
                    'company',
                    $translations['company'] ?? 'Company',
                    $this->buildCompanyMenuItems($translations, $pageIds),
                    $companyMenuOrigin,
                    null
                );
            }

            if ($businessMenu) {
                $this->createMenuTranslation(
                    $locale,
                    'business',
                    $translations['business'] ?? 'Business',
                    $this->buildBusinessMenuItems($translations, $pageIds),
                    $businessMenuOrigin,
                    null
                );
            }
        }

        Menu::clearCacheMenuItems();
    }

    protected function buildMainMenuItems(array $labels, array $pageIds): array
    {
        return [
            [
                'title' => $labels['home'] ?? 'Home',
                'url' => '/',
            ],
            [
                'title' => $labels['pages'] ?? 'Pages',
                'url' => '#',
                'children' => [
                    [
                        'title' => $labels['about_us'] ?? 'About us',
                        'reference_id' => $pageIds['About us'] ?? 2,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => $labels['terms_of_use'] ?? 'Terms Of Use',
                        'reference_id' => $pageIds['Terms Of Use'] ?? 3,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => $labels['terms_conditions'] ?? 'Terms & Conditions',
                        'reference_id' => $pageIds['Terms & Conditions'] ?? 4,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => $labels['refund_policy'] ?? 'Refund Policy',
                        'reference_id' => $pageIds['Refund Policy'] ?? 5,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => $labels['coming_soon'] ?? 'Coming soon',
                        'reference_id' => $pageIds['Coming soon'] ?? 12,
                        'reference_type' => Page::class,
                    ],
                ],
            ],
            [
                'title' => $labels['products'] ?? 'Products',
                'url' => '/products',
                'children' => [
                    [
                        'title' => $labels['all_products'] ?? 'All products',
                        'url' => '/products',
                    ],
                    [
                        'title' => $labels['products_of_category'] ?? 'Products Of Category',
                        'reference_id' => 15,
                        'reference_type' => ProductCategory::class,
                    ],
                    [
                        'title' => $labels['product_single'] ?? 'Product Single',
                        'url' => '/products/headphone-ultra-bass',
                    ],
                ],
            ],
            [
                'title' => $labels['stores'] ?? 'Stores',
                'url' => '/stores',
            ],
            [
                'title' => $labels['blog'] ?? 'Blog',
                'reference_id' => $pageIds['Blog'] ?? 6,
                'reference_type' => Page::class,
            ],
            [
                'title' => $labels['faq'] ?? 'FAQs',
                'reference_id' => $pageIds['FAQs'] ?? 7,
                'reference_type' => Page::class,
            ],
            [
                'title' => $labels['contact'] ?? 'Contact',
                'reference_id' => $pageIds['Contact'] ?? 8,
                'reference_type' => Page::class,
            ],
        ];
    }

    protected function buildQuickLinksMenuItems(array $labels, array $pageIds): array
    {
        return [
            [
                'title' => $labels['terms_of_use'] ?? 'Terms Of Use',
                'reference_id' => $pageIds['Terms Of Use'] ?? 3,
                'reference_type' => Page::class,
            ],
            [
                'title' => $labels['terms_conditions'] ?? 'Terms & Conditions',
                'reference_id' => $pageIds['Terms & Conditions'] ?? 4,
                'reference_type' => Page::class,
            ],
            [
                'title' => $labels['refund_policy'] ?? 'Refund Policy',
                'reference_id' => $pageIds['Refund Policy'] ?? 5,
                'reference_type' => Page::class,
            ],
            [
                'title' => $labels['faq'] ?? 'FAQs',
                'reference_id' => $pageIds['FAQs'] ?? 7,
                'reference_type' => Page::class,
            ],
            [
                'title' => $labels['404_page'] ?? '404 Page',
                'url' => '/nothing',
            ],
        ];
    }

    protected function buildCompanyMenuItems(array $labels, array $pageIds): array
    {
        return [
            [
                'title' => $labels['about_us'] ?? 'About us',
                'reference_id' => $pageIds['About us'] ?? 2,
                'reference_type' => Page::class,
            ],
            [
                'title' => $labels['affiliate'] ?? 'Affiliate',
                'reference_id' => $pageIds['Affiliate'] ?? 10,
                'reference_type' => Page::class,
            ],
            [
                'title' => $labels['career'] ?? 'Career',
                'reference_id' => $pageIds['Career'] ?? 11,
                'reference_type' => Page::class,
            ],
            [
                'title' => $labels['contact_us'] ?? 'Contact us',
                'reference_id' => $pageIds['Contact'] ?? 8,
                'reference_type' => Page::class,
            ],
        ];
    }

    protected function buildBusinessMenuItems(array $labels, array $pageIds): array
    {
        return [
            [
                'title' => $labels['our_blog'] ?? 'Our blog',
                'reference_id' => $pageIds['Blog'] ?? 6,
                'reference_type' => Page::class,
            ],
            [
                'title' => $labels['cart'] ?? 'Cart',
                'url' => '/cart',
            ],
            [
                'title' => $labels['my_account'] ?? 'My account',
                'url' => '/customer/overview',
            ],
            [
                'title' => $labels['shop'] ?? 'Shop',
                'url' => '/products',
            ],
        ];
    }

    protected function seedSimpleSliderTranslations(array $locales): void
    {
        $slider = SimpleSlider::query()->where('key', 'home-slider')->first();

        if (! $slider) {
            return;
        }

        $sliderOrigin = $this->getLanguageMetaOrigin($slider);
        $sliderItems = SimpleSliderItem::query()
            ->where('simple_slider_id', $slider->id)
            ->orderBy('order')
            ->get();

        $sliderTranslations = [
            'ar' => [
                'name' => 'سلايدر الرئيسية',
                'items' => [
                    ['title' => 'سلايدر 1'],
                    ['title' => 'سلايدر 2'],
                    ['title' => 'سلايدر 3'],
                ],
            ],
            'vi' => [
                'name' => 'Slider trang chủ',
                'items' => [
                    ['title' => 'Slider 1'],
                    ['title' => 'Slider 2'],
                    ['title' => 'Slider 3'],
                ],
            ],
            'fr' => [
                'name' => 'Slider accueil',
                'items' => [
                    ['title' => 'Slider 1'],
                    ['title' => 'Slider 2'],
                    ['title' => 'Slider 3'],
                ],
            ],
            'id' => [
                'name' => 'Slider beranda',
                'items' => [
                    ['title' => 'Slider 1'],
                    ['title' => 'Slider 2'],
                    ['title' => 'Slider 3'],
                ],
            ],
            'tr' => [
                'name' => 'Ana sayfa slider',
                'items' => [
                    ['title' => 'Slider 1'],
                    ['title' => 'Slider 2'],
                    ['title' => 'Slider 3'],
                ],
            ],
        ];

        foreach ($locales as $locale) {
            $data = $sliderTranslations[$locale] ?? null;

            if (! $data) {
                continue;
            }

            $translatedSlider = SimpleSlider::query()->create([
                'name' => $data['name'],
                'key' => 'home-slider-' . $locale,
            ]);

            LanguageMeta::saveMetaData($translatedSlider, $locale, $sliderOrigin);

            foreach ($data['items'] as $index => $itemData) {
                $originalItem = $sliderItems[$index] ?? null;

                if (! $originalItem) {
                    continue;
                }

                SimpleSliderItem::query()->create([
                    'title' => $itemData['title'],
                    'link' => $originalItem->link,
                    'image' => $originalItem->image,
                    'order' => $originalItem->order,
                    'simple_slider_id' => $translatedSlider->id,
                ]);
            }
        }
    }

    protected function createMenuNode(int $position, array $menuNode, int|string $menuId, int|string $parentId = 0): void
    {
        $menuNode['menu_id'] = $menuId;
        $menuNode['parent_id'] = $parentId;
        $menuNode['position'] = $position;

        if (isset($menuNode['url'])) {
            $menuNode['url'] = str_replace(url(''), '', $menuNode['url']);
        }

        if (Arr::has($menuNode, 'children') && ! empty($menuNode['children'])) {
            $children = $menuNode['children'];
            $menuNode['has_child'] = true;
        } else {
            $children = [];
            $menuNode['has_child'] = false;
        }

        Arr::forget($menuNode, 'children');

        $createdNode = MenuNode::query()->create($menuNode);

        foreach ($children as $childPosition => $child) {
            $this->createMenuNode($childPosition, $child, $menuId, $createdNode->getKey());
        }
    }

    protected function applyWidgetTranslations(array $data, array $translations, string $locale): array
    {
        foreach (['name', 'title', 'subtitle', 'about'] as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                $data[$key] = $this->translateValue($translations, $data[$key]);
            }
        }

        // Localize menu slugs for footer and social menus
        $localizeMenuSlugs = ['quick-links', 'company', 'business', 'social'];
        if (isset($data['menu_id']) && in_array($data['menu_id'], $localizeMenuSlugs, true)) {
            $data['menu_id'] = $this->localizedSlug($data['menu_id'], $locale);
        }

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemIndex => $item) {
                foreach ($item as $fieldIndex => $field) {
                    $key = Arr::get($field, 'key');
                    $value = Arr::get($field, 'value');

                    if (! is_string($value)) {
                        continue;
                    }

                    if (in_array($key, ['label', 'text'], true)) {
                        $data['items'][$itemIndex][$fieldIndex]['value'] = $this->translateValue(
                            $translations,
                            $value
                        );
                    }
                }
            }
        }

        return $data;
    }
}
