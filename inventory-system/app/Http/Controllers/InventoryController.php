<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->where(function($qB) use ($q) {
                $qB->where('sku', 'like', "%{$q}%")
                   ->orWhere('name', 'like', "%{$q}%");
            });
        }

        // Server-side status filter (compute from quantity):
        // - out-of-stock: quantity = 0
        // - low-stock: quantity between 1 and 20 (inclusive)
        // - in-stock: quantity > 20
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'out-of-stock') {
                $query->where('quantity', 0);
            } elseif ($status === 'low-stock') {
                $query->whereBetween('quantity', [1, 20]);
            } elseif ($status === 'in-stock') {
                $query->where('quantity', '>', 20);
            }
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        return view('inventory', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        Product::create($data);

        return redirect()->route('inventory.index')->with('success', 'Product added.');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'sku' => "required|string|unique:products,sku,{$product->id}",
            'name' => 'required|string',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        $product->update($data);

        return redirect()->route('inventory.index')->with('success', 'Product updated.');
    }

    public function destroy(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('inventory.index')->with('success', 'Product deleted.');
    }
}
