<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ShopAIController extends Controller
{
    /**
     * Show the main ShopAI page
     */
    public function index()
    {
        // Get top 5 most expensive products for slideshow
        $slideshowProducts = Product::where('quantity', '>', 0)
            ->where('price', '>', 0)
            ->orderBy('price', 'desc')
            ->limit(5)
            ->get();

        // Get all products for the products section
        $products = Product::where('quantity', '>', 0)
            ->where('price', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('shopai', compact('slideshowProducts', 'products'));
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'error' => 'Search query is required'
            ], 400);
        }

        $products = Product::where('quantity', '>', 0)
            ->where('price', '>', 0)
            ->where('name', 'like', '%' . $query . '%')
            ->orWhere('description', 'like', '%' . $query . '%')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'products' => $products,
            'query' => $query,
            'count' => $products->count()
        ]);
    }

    /**
     * Handle customer registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = Customer::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone ?? null,
            'address' => $request->address ?? null,
            'verification_token' => Str::random(32),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful!',
            'customer' => [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
            ]
        ], 201);
    }

    /**
     * Handle customer login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid credentials'
            ], 401);
        }

        if (!Hash::check($request->password, $customer->password)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid credentials'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'customer' => [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
            ]
        ]);
    }

    /**
     * Get single product details for modal
     */
    public function getProduct($id)
    {
        $product = Product::where('id', $id)
            ->where('quantity', '>', 0)
            ->first(['id', 'name', 'description', 'price', 'image_url', 'quantity']);

        if (!$product) {
            return response()->json([
                'success' => false,
                'error' => 'Product not found or out of stock'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }
}