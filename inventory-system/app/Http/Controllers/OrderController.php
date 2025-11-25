<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
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
                   ->orWhere('order_number', 'like', "%{$q}%")
                   ->orWhereHas('customer', function($subQuery) use ($q) {
                       $subQuery->where('full_name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%");
                   });
            });
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            $query->where('status', $status);
        }

        $orders = $query->with('customer', 'items.product')->orderBy('created_at', 'desc')->paginate(25)->withQueryString();
        $customers = Customer::all();
        $products = Product::all();

        return view('orders', compact('orders', 'customers', 'products'));
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer|exists:customers,id',
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

        // Generate order number
        $data['order_number'] = Order::generateOrderNumber();
        $data['total_amount'] = $data['total_price'];

        // Create the order
        $order = Order::create($data);

        // Create order item
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'price' => $data['unit_price'],
        ]);

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
            'customer_id' => 'required|integer|exists:customers,id',
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

        // Get the order item
        $orderItem = $order->items->first();
        if (!$orderItem) {
            return redirect()->back()
                ->withErrors(['order_item' => 'Order item not found'])
                ->withInput();
        }

        // Handle stock adjustment
        $oldQuantity = $orderItem->quantity;
        $newQuantity = $data['quantity'];
        $oldProductId = $orderItem->product_id;
        $newProductId = $data['product_id'];

        // If order is delivered, don't allow editing
        if ($order->status === 'delivered') {
            return redirect()->back()
                ->withErrors(['status' => 'Cannot edit delivered orders'])
                ->withInput();
        }

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
        $data['total_amount'] = $data['total_price'];
        $order->update($data);

        // Update the order item
        $orderItem->update([
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'price' => $data['unit_price'],
        ]);

        return redirect()->route('orders.index')->with('success', 'Order updated successfully.');
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $order)
    {
        // Restore stock before deleting
        foreach ($order->items as $item) {
            $product = $item->product;
            if ($product && !in_array($order->status, ['delivered'])) {
                // Only restore stock if order is not delivered
                $product->quantity += $item->quantity;
                $product->save();
            }
        }

        // Delete order items first
        $order->items()->delete();
        
        // Then delete the order
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

        // Handle stock adjustments based on status changes
        foreach ($order->items as $item) {
            $product = $item->product;
            if (!$product) continue;

            if ($product && in_array($oldStatus, ['approved', 'delivered']) && $newStatus === 'declined') {
                // Return stock when order is declined from approved/delivered
                $product->quantity += $item->quantity;
                $product->save();
            } elseif ($product && in_array($oldStatus, ['pending', 'declined']) && $newStatus === 'approved') {
                // Deduct stock when order is approved
                if ($product->quantity < $item->quantity) {
                    return redirect()->back()->with('error', 'Insufficient stock to approve this order.');
                }
                $product->quantity -= $item->quantity;
                $product->save();
            }
            // Note: delivered status doesn't change stock (already deducted on approval)
        }

        $order->status = $newStatus;
        $order->save();

        return redirect()->route('orders.index')->with('success', 'Order status updated successfully.');
    }
}