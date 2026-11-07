<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Str;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Categories
        $electronics = Category::firstOrCreate(
            ['slug' => 'laptops-computers'],
            [
                'name' => 'Laptops & Computers',
                'description' => 'High-performance laptops, workstations, and computing hardware',
                'image_url' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500',
                'is_active' => true,
            ]
        );

        $smartphones = Category::firstOrCreate(
            ['slug' => 'smartphones-tablets'],
            [
                'name' => 'Smartphones & Tablets',
                'description' => 'Flagship mobile phones, portable tablets, and digital accessories',
                'image_url' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=500',
                'is_active' => true,
            ]
        );

        $accessories = Category::firstOrCreate(
            ['slug' => 'audio-accessories'],
            [
                'name' => 'Audio & Accessories',
                'description' => 'Noise-cancelling headphones, wireless earbuds, and peripherals',
                'image_url' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500',
                'is_active' => true,
            ]
        );

        // 2. Products
        $macbook = Product::firstOrCreate(
            ['sku' => 'APL-MBP-16'],
            [
                'name' => 'MacBook Pro 16" M3 Max',
                'slug' => 'macbook-pro-16-m3-max',
                'description' => '16-core CPU, 40-core GPU, 48GB Unified Memory, 1TB SSD Storage with Liquid Retina XDR display.',
                'price' => 3499.00,
                'stock' => 14,
                'category_id' => (string) $electronics->id,
                'images' => [
                    'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800'
                ],
                'tags' => ['apple', 'laptop', 'pro', 'm3'],
                'is_featured' => true,
                'is_active' => true,
            ]
        );

        $thinkpad = Product::firstOrCreate(
            ['sku' => 'LEN-TP-X1'],
            [
                'name' => 'ThinkPad X1 Carbon Gen 11',
                'slug' => 'thinkpad-x1-carbon-gen-11',
                'description' => 'Intel Core i7-1365U, 32GB LPDDR5, 1TB PCIe 4.0 SSD, Ultralight business laptop.',
                'price' => 1899.50,
                'stock' => 3, // Low stock demo!
                'category_id' => (string) $electronics->id,
                'images' => [
                    'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800'
                ],
                'tags' => ['lenovo', 'thinkpad', 'ultrabook'],
                'is_featured' => false,
                'is_active' => true,
            ]
        );

        $iphone = Product::firstOrCreate(
            ['sku' => 'APL-IP15P-256'],
            [
                'name' => 'iPhone 15 Pro Max 256GB',
                'slug' => 'iphone-15-pro-max-256gb',
                'description' => 'Aerospace-grade titanium design, A17 Pro chip, 48MP Main camera with 5x Telephoto.',
                'price' => 1199.00,
                'stock' => 28,
                'category_id' => (string) $smartphones->id,
                'images' => [
                    'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=800'
                ],
                'tags' => ['apple', 'smartphone', '5g', 'titanium'],
                'is_featured' => true,
                'is_active' => true,
            ]
        );

        $headphones = Product::firstOrCreate(
            ['sku' => 'SNY-WH1000XM5'],
            [
                'name' => 'Sony WH-1000XM5 Wireless Headphones',
                'slug' => 'sony-wh-1000xm5-wireless-headphones',
                'description' => 'Industry-leading noise canceling with two processors and 8 microphones for clear hands-free calling.',
                'price' => 399.99,
                'stock' => 2, // Low stock demo!
                'category_id' => (string) $accessories->id,
                'images' => [
                    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800'
                ],
                'tags' => ['sony', 'audio', 'anc', 'wireless'],
                'is_featured' => true,
                'is_active' => true,
            ]
        );

        // 3. Demo Orders
        $customer = User::where('role', 'customer')->first();
        if ($customer) {
            Order::firstOrCreate(
                ['order_number' => 'ORD-DEMO0001'],
                [
                    'user_id' => $customer->id,
                    'items' => [
                        [
                            'product_id' => (string) $macbook->id,
                            'name' => $macbook->name,
                            'sku' => $macbook->sku,
                            'price' => 3499.00,
                            'quantity' => 1,
                            'subtotal' => 3499.00,
                        ],
                        [
                            'product_id' => (string) $headphones->id,
                            'name' => $headphones->name,
                            'sku' => $headphones->sku,
                            'price' => 399.99,
                            'quantity' => 1,
                            'subtotal' => 399.99,
                        ]
                    ],
                    'subtotal' => 3898.99,
                    'tax_amount' => 311.92,
                    'shipping_fee' => 0.00,
                    'total_amount' => 4210.91,
                    'status' => 'delivered',
                    'shipping_address' => [
                        'street' => '742 Evergreen Terrace',
                        'city' => 'Springfield',
                        'state' => 'OR',
                        'postal_code' => '97477',
                        'country' => 'USA'
                    ],
                    'payment_method' => 'credit_card',
                    'payment_status' => 'paid',
                    'notes' => 'Please leave at front door.'
                ]
            );

            Order::firstOrCreate(
                ['order_number' => 'ORD-DEMO0002'],
                [
                    'user_id' => $customer->id,
                    'items' => [
                        [
                            'product_id' => (string) $iphone->id,
                            'name' => $iphone->name,
                            'sku' => $iphone->sku,
                            'price' => 1199.00,
                            'quantity' => 1,
                            'subtotal' => 1199.00,
                        ]
                    ],
                    'subtotal' => 1199.00,
                    'tax_amount' => 95.92,
                    'shipping_fee' => 0.00,
                    'total_amount' => 1294.92,
                    'status' => 'processing',
                    'shipping_address' => [
                        'street' => '742 Evergreen Terrace',
                        'city' => 'Springfield',
                        'state' => 'OR',
                        'postal_code' => '97477',
                        'country' => 'USA'
                    ],
                    'payment_method' => 'paypal',
                    'payment_status' => 'paid',
                    'notes' => 'Urgent expedited processing requested.'
                ]
            );

            Order::firstOrCreate(
                ['order_number' => 'ORD-DEMO0003'],
                [
                    'user_id' => $customer->id,
                    'items' => [
                        [
                            'product_id' => (string) $thinkpad->id,
                            'name' => $thinkpad->name,
                            'sku' => $thinkpad->sku,
                            'price' => 1899.50,
                            'quantity' => 1,
                            'subtotal' => 1899.50,
                        ]
                    ],
                    'subtotal' => 1899.50,
                    'tax_amount' => 151.96,
                    'shipping_fee' => 0.00,
                    'total_amount' => 2051.46,
                    'status' => 'pending',
                    'shipping_address' => [
                        'street' => '102 Ocean Drive',
                        'city' => 'Miami',
                        'state' => 'FL',
                        'postal_code' => '33139',
                        'country' => 'USA'
                    ],
                    'payment_method' => 'bank_transfer',
                    'payment_status' => 'unpaid',
                    'notes' => 'Awaiting bank wire transfer confirmation.'
                ]
            );

            // Audit Logs
            AuditLog::record($customer->id, 'seeder_initialized', 'System', '0', ['message' => 'Store catalog and seed data populated']);
        }
    }
}

