<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item; // Assumes App\Models\Item exists (or rename to Product)
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    /**
     * Display a listing of items.
     */
    public function index(Request $request)
    {
        $query = Item::query();

        // Filters
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('low_stock')) {
            $query->where('quantity', '<=', 20);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'low') {
                $query->where('quantity', '>', 0)->where('quantity', '<=', 20);
            } elseif ($request->status === 'out') {
                $query->where('quantity', 0);
            } elseif ($request->status === 'in_stock') {
                $query->where('quantity', '>', 20);
            }
        }

        $items = $query->orderBy('quantity', 'asc')->paginate(20);

        return view('items.index', compact('items'));
    }

    /**
     * Show the form for creating a new item.
     */
    public function create()
    {
        return view('items.create');
    }

    /**
     * Store a newly created item in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|unique:items,sku|max:255',
            'name' => 'required|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('items', 'public');
        }

        Item::create($data);

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    /**
     * Display the specified item.
     */
    public function show(Item $item)
    {
        return view('items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified item.
     */
    public function edit(Item $item)
    {
        return view('items.edit', compact('item'));
    }

    /**
     * Update the specified item in storage.
     */
    public function update(Request $request, Item $item)
    {
        $request->validate([
            'sku' => 'required|max:255|unique:items,sku,' . $item->id,
            'name' => 'required|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();
        if ($request->hasFile('image')) {
            // Delete old image
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $data['image'] = $request->file('image')->store('items', 'public');
        }

        $item->update($data);

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified item from storage.
     */
    public function destroy(Item $item)
    {
        // Delete image if exists
        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
    }

    /**
     * Low stock alert items (for dashboard/sidebar).
     */
    public function lowStock()
    {
        $lowStock = Item::where('quantity', '<=', 20)->where('quantity', '>', 0)->count();
        $outStock = Item::where('quantity', 0)->count();

        return compact('lowStock', 'outStock');
    }
}
