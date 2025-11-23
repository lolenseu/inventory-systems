<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
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
            'password' => Hash::make($request->password),
            'phone' => $request->phone ?? null,
            'address' => $request->address ?? null,
            'verification_token' => Str::random(32),
        ]);

        // Create session or token (for simplicity, we'll just return customer info)
        // In a real application, you might use Laravel Sanctum or Passport for API tokens

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
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * API endpoint to get top expensive products for slideshow
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

    /**
     * Add product to cart
     */
    public function addToCart(Request $request)
    {
        $customer = auth()->user();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => 'Please login to add items to cart'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::find($request->product_id);
        if (!$product || $product->quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'error' => 'Product not available or insufficient quantity'
            ], 400);
        }

        $cartItem = Cart::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'product_id' => $request->product_id,
            ],
            [
                'quantity' => $request->quantity,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully',
            'cart_item' => $cartItem
        ]);
    }

    /**
     * Get customer's cart
     */
    public function getCart(Request $request)
    {
        $customer = auth()->user();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => 'Please login to view cart'
            ], 401);
        }

        $cartItems = Cart::with('product')
            ->where('customer_id', $customer->id)
            ->get();

        $total = $cartItems->sum(function($item) {
            return $item->product->price * $item->quantity;
        });

        return response()->json([
            'success' => true,
            'cart_items' => $cartItems,
            'total' => $total
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function updateCart(Request $request)
    {
        $customer = auth()->user();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => 'Please login to update cart'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'cart_id' => 'required|exists:carts,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cartItem = Cart::where('id', $request->cart_id)
            ->where('customer_id', $customer->id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'error' => 'Cart item not found'
            ], 404);
        }

        $product = $cartItem->product;
        if ($product->quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'error' => 'Insufficient product quantity available'
            ], 400);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully',
            'cart_item' => $cartItem
        ]);
    }

    /**
     * Remove item from cart
     */
    public function removeCartItem(Request $request)
    {
        $customer = auth()->user();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => 'Please login to remove items from cart'
            ], 401);
        }

        $cartItem = Cart::where('id', $request->cart_id)
            ->where('customer_id', $customer->id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'error' => 'Cart item not found'
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully'
        ]);
    }

    /**
     * Checkout - Create order from cart
     */
    public function checkout(Request $request)
    {
        $customer = auth()->user();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => 'Please login to checkout'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'shipping_address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cartItems = Cart::with('product')
            ->where('customer_id', $customer->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'error' => 'Cart is empty'
            ], 400);
        }

        // Check if all items are still available
        foreach ($cartItems as $cartItem) {
            if ($cartItem->product->quantity < $cartItem->quantity) {
                return response()->json([
                    'success' => false,
                    'error' => "Product '{$cartItem->product->name}' is no longer available in the requested quantity"
                ], 400);
            }
        }

        // Create order
        $order = Order::create([
            'customer_id' => $customer->id,
            'order_number' => Order::generateOrderNumber(),
            'total_amount' => $cartItems->sum(function($item) {
                return $item->product->price * $item->quantity;
            }),
            'shipping_address' => $request->shipping_address,
            'phone' => $request->phone,
            'notes' => $request->notes,
        ]);

        // Create order items and update product quantities
        foreach ($cartItems as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->product->price,
            ]);

            // Update product quantity
            $product = $cartItem->product;
            $product->quantity -= $cartItem->quantity;
            $product->save();
        }

        // Clear cart
        Cart::where('customer_id', $customer->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully',
            'order' => $order->load('items.product'),
            'order_number' => $order->order_number
        ]);
    }

    /**
     * Get customer's orders
     */
    public function getOrders(Request $request)
    {
        $customer = auth()->user();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => 'Please login to view orders'
            ], 401);
        }

        $orders = Order::with('items.product')
            ->where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }

    /**
     * Get customer's specific order
     */
    public function getOrder($orderId)
    {
        $customer = auth()->user();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => 'Please login to view order'
            ], 401);
        }

        $order = Order::with('items.product')
            ->where('id', $orderId)
            ->where('customer_id', $customer->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => $order
        ]);
    }
}