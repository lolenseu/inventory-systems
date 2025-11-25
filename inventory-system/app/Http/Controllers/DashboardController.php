<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the inventory dashboard.
     */
    public function index()
    {
        // Summary stats for the year (delivered orders only)
        $year = now()->year;
        $startDate = Carbon::createFromDate($year)->startOfYear();
        $endDate = Carbon::createFromDate($year)->endOfYear();

        $summary = [
            'total_items' => Product::count(),
            'low_stock' => Product::where('quantity', '<=', 20)->count(),
            'out_of_stock' => Product::where('quantity', 0)->count(),
            'total_value' => Product::sum(DB::raw('quantity * price')),
            'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_sales' => Order::whereBetween('created_at', [$startDate, $endDate])
                                ->where('status', 'delivered')
                                ->sum('total_amount'),
            'avg_order_value' => Order::whereBetween('created_at', [$startDate, $endDate])
                                ->where('status', 'delivered')
                                ->avg('total_amount') ?? 0,
            'pending_orders' => Order::whereBetween('created_at', [$startDate, $endDate])
                                ->where('status', 'pending')
                                ->count(),
        ];

        $items = Product::orderBy('created_at', 'desc')->paginate(10);
        $orders = Order::with('customer', 'items.product')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard', compact('summary', 'items', 'orders'));
    }

    /**
     * Display the inventory dashboard (alternative method).
     */
    public function showInventory()
    {
        // Paginate products for the table
        $items = Product::orderBy('created_at', 'desc')->paginate(20);

        // Get orders sorted by price (expensive first)
        $orders = Order::with('customer', 'items.product')
            ->orderBy('total_amount', 'desc')
            ->limit(10)
            ->get();

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

        return view('dashboard', compact('items', 'summary', 'orders'));
    }
}
