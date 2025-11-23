<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Supplier;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Ensure we have some suppliers first
        $suppliers = Supplier::factory()->count(5)->create();

        // Create sample products with images
        Product::factory()->count(10)->create([
            'supplier_id' => $suppliers->random()->id,
        ]);

        // Create a product with a sample image
        $sampleImagePath = public_path('images/sample-product.jpg');
        if (file_exists($sampleImagePath)) {
            $imageData = file_get_contents($sampleImagePath);
            Product::create([
                'sku' => 'SAMPLE-001',
                'name' => 'Sample Product with Image',
                'description' => 'This is a sample product with an image for testing purposes.',
                'quantity' => 50,
                'price' => 25.99,
                'supplier_id' => $suppliers->first()->id,
                'image' => base64_encode($imageData),
                'image_type' => 'image/jpeg',
            ]);
        }

        // Create products without images
        Product::factory()->count(5)->create([
            'supplier_id' => $suppliers->random()->id,
            'image' => null,
            'image_type' => null,
        ]);
    }
}