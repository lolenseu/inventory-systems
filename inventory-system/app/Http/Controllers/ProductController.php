<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // List products and show view
    public function index(Request $request)
    {
        $query = Product::with('supplier:id,name')
            ->select('id', 'sku', 'name', 'description', 'quantity', 'price', 'supplier_id', 'created_at'); // Exclude 'image', 'image_type' to save memory

        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->where(function($qB) use ($q) {
                $qB->where('sku', 'like', "%{$q}%")
                   ->orWhere('name', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            if ($status === 'out-of-stock') {
                $query->where('quantity', 0);
            } elseif ($status === 'low-stock') {
                $query->whereBetween('quantity', [1, 20]);
            } elseif ($status === 'in-stock') {
                $query->where('quantity', '>', 20);
            }
        }

        $products = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_products' => Product::count(),
            'in_stock' => Product::where('quantity', '>', 20)->count(),
            'low_stock' => Product::whereBetween('quantity', [1, 20])->count(),
            'out_of_stock' => Product::where('quantity', 0)->count(),
        ];

        $suppliers = \App\Models\Supplier::select('id', 'name')->get(); // Minimal suppliers

        return view('products', compact('products', 'summary', 'suppliers'));
    }

    // Store new product
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        
        // Handle image upload with compression
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            
            // Compress and resize image to reduce memory usage
            $compressedImage = $this->compressImage($image);
            
            $data['image'] = $compressedImage;
            $data['image_type'] = $image->getMimeType();
        } else {
            $data['image'] = null;
            $data['image_type'] = null;
        }

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }

    // Update existing product
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'sku' => "required|string|unique:products,sku,{$product->id}",
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        
        // Handle image upload with compression
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            
            // Compress and resize image to reduce memory usage
            $compressedImage = $this->compressImage($image);
            
            $data['image'] = $compressedImage;
            $data['image_type'] = $image->getMimeType();
        }

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    // Delete product
    public function destroy(Request $request, Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    /**
     * Compress and resize image to reduce memory usage
     * 
     * @param \Illuminate\Http\UploadedFile $image
     * @return string base64 encoded compressed image
     */
    private function compressImage($image)
    {
        // Set memory limit temporarily
        ini_set('memory_limit', '256M');
        
        // Read the image
        $imageResource = imagecreatefromstring(file_get_contents($image->getPathname()));
        
        if (!$imageResource) {
            throw new \Exception('Failed to create image resource');
        }
        
        // Get original dimensions
        $originalWidth = imagesx($imageResource);
        $originalHeight = imagesy($imageResource);
        
        // Calculate new dimensions (max width/height: 800px)
        $maxDimension = 800;
        if ($originalWidth > $maxDimension || $originalHeight > $maxDimension) {
            if ($originalWidth > $originalHeight) {
                $newWidth = $maxDimension;
                $newHeight = (int)(($maxDimension / $originalWidth) * $originalHeight);
            } else {
                $newHeight = $maxDimension;
                $newWidth = (int)(($maxDimension / $originalHeight) * $originalWidth);
            }
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }
        
        // Create new image with new dimensions
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($image->getClientOriginalExtension() === 'png' || $image->getClientOriginalExtension() === 'gif') {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
            imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize the image
        imagecopyresampled($resizedImage, $imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        // Start output buffering
        ob_start();
        
        // Output compressed image
        $extension = strtolower($image->getClientOriginalExtension());
        switch ($extension) {
            case 'png':
                imagepng($resizedImage, null, 6); // Compression level 6 (0-9)
                break;
            case 'gif':
                imagegif($resizedImage);
                break;
            default: // jpeg, jpg
                imagejpeg($resizedImage, null, 75); // 75% quality
                break;
        }
        
        // Get the image data
        $imageData = ob_get_clean();
        
        // Free memory
        imagedestroy($imageResource);
        imagedestroy($resizedImage);
        
        // Return base64 encoded image
        return base64_encode($imageData);
    }
}