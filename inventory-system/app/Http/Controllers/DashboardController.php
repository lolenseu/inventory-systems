<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the inventory dashboard.
     */
    public function index(Request $request)
    {
        // Paginate products for the table
        $items = Product::orderBy('created_at', 'desc')->paginate(20);

        // Compute summary values based on products/quantity
        $totalItems = Product::count();
        $lowStock = Product::where('quantity', '>', 0)->where('quantity', '<=', 20)->count();
        $outOfStock = Product::where('quantity', 0)->count();

        // Sum of quantity * price; fallback to 0.00 if null
        $totalValue = (float) DB::table('products')
            ->select(DB::raw('COALESCE(SUM(quantity * price), 0) as total'))
            ->value('total');

        $summary = [
            'total_items' => $totalItems,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'total_value' => $totalValue,
        ];

        return view('dashboard', compact('items', 'summary'));
    }
}
