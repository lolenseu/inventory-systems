<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::query();

        // Search functionality
        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->where(function($qB) use ($q) {
                $qB->where('id', 'like', "%{$q}%")
                   ->orWhere('customer_name', 'like', "%{$q}%")
                   ->orWhereHas('product', function($subQuery) use ($q) {
                       $subQuery->where('name', 'like', "%{$q}%");
                   });
            });
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            $query->where('status', $status);
        }

        $orders = $query->with('product')->orderBy('created_at', 'desc')->paginate(25)->withQueryString();
        $products = Product::all();

        return view('orders', compact('orders', 'products'));
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,approved,declined,delivered',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        
        // Get the product to check stock
        $product = Product::find($data['product_id']);
        
        if (!$product) {
            return redirect()->back()
                ->withErrors(['product_id' => 'Product not found'])
                ->withInput();
        }

        // Check if there's enough stock
        if ($product->quantity < $data['quantity']) {
            return redirect()->back()
                ->withErrors(['quantity' => 'Insufficient stock. Only ' . $product->quantity . ' items available'])
                ->withInput();
        }

        // Create the order
        $order = Order::create($data);

        // Reduce the product quantity
        $product->quantity -= $data['quantity'];
        $product->save();

        return redirect()->route('orders.index')->with('success', 'Order created successfully.');
    }

    /**
     * Update the specified order in storage.
     */
    public function update(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,approved,declined,delivered',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        
        // Get the product to check stock
        $product = Product::find($data['product_id']);
        
        if (!$product) {
            return redirect()->back()
                ->withErrors(['product_id' => 'Product not found'])
                ->withInput();
        }

        // Handle stock adjustment
        $oldQuantity = $order->quantity;
        $newQuantity = $data['quantity'];
        $oldProductId = $order->product_id;
        $newProductId = $data['product_id'];

        // If product changed, restore old product stock and check new product stock
        if ($oldProductId != $newProductId) {
            // Restore old product stock
            $oldProduct = Product::find($oldProductId);
            if ($oldProduct) {
                $oldProduct->quantity += $oldQuantity;
                $oldProduct->save();
            }

            // Check new product stock
            if ($product->quantity < $newQuantity) {
                return redirect()->back()
                    ->withErrors(['quantity' => 'Insufficient stock. Only ' . $product->quantity . ' items available'])
                    ->withInput();
            }

            // Deduct new product stock
            $product->quantity -= $newQuantity;
            $product->save();
        } else {
            // Same product, adjust stock difference
            $quantityDiff = $newQuantity - $oldQuantity;
            if ($quantityDiff > 0 && $product->quantity < $quantityDiff) {
                return redirect()->back()
                    ->withErrors(['quantity' => 'Insufficient stock. Only ' . ($product->quantity - $oldQuantity) . ' more items available'])
                    ->withInput();
            }
            
            $product->quantity -= $quantityDiff;
            $product->save();
        }

        // Update the order
        $order->update($data);

        return redirect()->route('orders.index')->with('success', 'Order updated successfully.');
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $order)
    {
        // Restore stock before deleting
        $product = $order->product;
        if ($product) {
            $product->quantity += $order->quantity;
            $product->save();
        }

        $order->delete();

        return redirect()->route('orders.index')->with('success', 'Order deleted successfully.');
    }

    /**
     * Update order status with stock management.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,approved,declined,delivered',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;
        $product = $order->product;

        // Handle stock adjustments based on status changes
        if ($product && in_array($oldStatus, ['approved', 'delivered']) && $newStatus === 'declined') {
            // Return stock when order is declined
            $product->quantity += $order->quantity;
            $product->save();
        } elseif ($product && in_array($oldStatus, ['pending', 'declined']) && $newStatus === 'approved') {
            // Deduct stock when order is approved
            if ($product->quantity < $order->quantity) {
                return redirect()->back()->with('error', 'Insufficient stock to approve this order.');
            }
            $product->quantity -= $order->quantity;
            $product->save();
        }
        // Note: delivered status doesn't change stock (already deducted on approval)

        $order->status = $newStatus;
        $order->save();

        return redirect()->route('orders.index')->with('success', 'Order status updated successfully.');
    }
}