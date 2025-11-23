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
     * Handle customer registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
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
            'password' => $request->password,
            'phone' => $request->phone ?? null,
            'address' => $request->address ?? null,
            'verification_token' => Str::random(32),
        ]);

        // You can add email verification logic here

        return response()->json([
            'success' => true,
            'message' => 'Registration successful! Please check your email for verification.',
            'customer' => $customer
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

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid credentials'
            ], 401);
        }

        if (!$customer->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'Your account has been deactivated'
            ], 403);
        }

        // Create session or token (for simplicity, we'll just return customer info)
        // In a real application, you might use Laravel Sanctum or Passport for API tokens

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
     * Handle customer logout
     */
    public function logout(Request $request)
    {
        // Clear session or token
        // For API, you might revoke tokens here

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get customer profile
     */
    public function profile(Request $request)
    {
        $customer = auth()->user(); // Assuming you have authentication set up

        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => 'Customer not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
    }

    /**
     * Update customer profile
     */
    public function updateProfile(Request $request)
    {
        $customer = auth()->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => 'Customer not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:500',
            'city' => 'sometimes|nullable|string|max:100',
            'country' => 'sometimes|nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $customer->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'customer' => $customer
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $customer = auth()->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => 'Customer not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->current_password, $customer->password)) {
            return response()->json([
                'success' => false,
                'error' => 'Current password is incorrect'
            ], 422);
        }

        $customer->update([
            'password' => $request->new_password
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * API endpoint to get top expensive products for slideshow
     */
    public function getTopProducts()
    {
        $products = Product::where('quantity', '>', 0)
            ->where('price', '>', 0)
            ->orderBy('price', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'description', 'price', 'image_url']);

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

    /**
     * API endpoint to get all products
     */
    public function getProducts()
    {
        $products = Product::where('quantity', '>', 0)
            ->where('price', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'description', 'price', 'image_url', 'quantity']);

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }
}