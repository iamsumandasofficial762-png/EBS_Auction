<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Database\Seeders\Traits\HasProductCategorySeeder;
use Botble\Ecommerce\Models\ProductCategory;

class ProductCategorySeeder extends BaseSeeder
{
    use HasProductCategorySeeder;

    public function run(): void
    {
        $this->uploadFiles('product-categories');

        $categories = [
            [
                'name' => 'Hot Promotions',
                'icon' => 'icon-star',
            ],
            [
                'name' => 'Electronics',
                'icon' => 'icon-laundry',
                'image' => 'product-categories/1.jpg',
                'is_featured' => true,
                'children' => [
                    [
                        'name' => 'Consumer Electronic',
                        'children' => [
                            [
                                'name' => 'Home Audio & Theaters',
                            ],
                            [
                                'name' => 'TV & Videos',
                            ],
                            [
                                'name' => 'Camera, Photos & Videos',
                            ],
                            [
                                'name' => 'Cellphones & Accessories',
                            ],
                            [
                                'name' => 'Headphones',
                            ],
                            [
                                'name' => 'Videos games',
                            ],
                            [
                                'name' => 'Wireless Speakers',
                            ],
                            [
                                'name' => 'Office Electronic',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Accessories & Parts',
                        'children' => [
                            [
                                'name' => 'Digital Cables',
                            ],
                            [
                                'name' => 'Audio & Video Cables',
                            ],
                            [
                                'name' => 'Batteries',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Clothing',
                'icon' => 'icon-shirt',
                'image' => 'product-categories/2.jpg',
                'is_featured' => true,
            ],
            [
                'name' => 'Computers',
                'icon' => 'icon-desktop',
                'image' => 'product-categories/3.jpg',
                'is_featured' => true,
                'children' => [
                    [
                        'name' => 'Computer & Technologies',
                        'children' => [
                            [
                                'name' => 'Computer & Tablets',
                            ],
                            [
                                'name' => 'Laptop',
                            ],
                            [
                                'name' => 'Monitors',
                            ],
                            [
                                'name' => 'Computer Components',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Networking',
                        'children' => [
                            [
                                'name' => 'Drive & Storages',
                            ],
                            [
                                'name' => 'Gaming Laptop',
                            ],
                            [
                                'name' => 'Security & Protection',
                            ],
                            [
                                'name' => 'Accessories',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Home & Kitchen',
                'icon' => 'icon-lampshade',
                'image' => 'product-categories/4.jpg',
                'is_featured' => true,
            ],
            [
                'name' => 'Health & Beauty',
                'icon' => 'icon-heart-pulse',
                'image' => 'product-categories/5.jpg',
                'is_featured' => true,
            ],
            [
                'name' => 'Jewelry & Watch',
                'icon' => 'icon-diamond2',
                'image' => 'product-categories/6.jpg',
                'is_featured' => true,
            ],
            [
                'name' => 'Technology Toys',
                'icon' => 'icon-desktop',
                'image' => 'product-categories/7.jpg',
                'is_featured' => true,
            ],
            [
                'name' => 'Phones',
                'icon' => 'icon-smartphone',
                'image' => 'product-categories/8.jpg',
                'is_featured' => true,
            ],
            [
                'name' => 'Babies & Moms',
                'icon' => 'icon-baby-bottle',
            ],
            [
                'name' => 'Sport & Outdoor',
                'icon' => 'icon-baseball',
            ],
            [
                'name' => 'Books & Office',
                'icon' => 'icon-book2',
            ],
            [
                'name' => 'Cars & Motorcycles',
                'icon' => 'icon-car-siren',
            ],
            [
                'name' => 'Home Improvements',
                'icon' => 'icon-wrench',
            ],
        ];

        ProductCategory::query()->truncate();

        foreach ($categories as $index => $item) {
            $this->createCategoryItem($index, $item);
        }
    }
}
