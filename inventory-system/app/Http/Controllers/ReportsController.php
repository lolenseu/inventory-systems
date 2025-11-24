<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order; // Adjust if your Order model is elsewhere
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $startDate = Carbon::createFromDate($year)->startOfYear();
        $endDate = Carbon::createFromDate($year)->endOfYear();

        // Summary stats for the year
        $summary = [
            'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_sales' => Order::whereBetween('created_at', [$startDate, $endDate])->sum('total_amount'),
            'avg_order_value' => Order::whereBetween('created_at', [$startDate, $endDate])->avg('total_amount') ?? 0,
            'pending_orders' => Order::whereBetween('created_at', [$startDate, $endDate])
                                     ->where('status', 'pending')
                                     ->count(),
        ];

        // Monthly data: aggregate by month
        $monthlyRaw = DB::table('orders')
            ->select(
                DB::raw('MONTH(created_at) as month_num'),
                DB::raw('YEAR(created_at) as year_num'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('AVG(total_amount) as avg_order_value')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('year_num', 'month_num')
            ->orderBy('year_num')
            ->orderBy('month_num')
            ->get();

        $monthlyData = [];
        foreach ($monthlyRaw as $month) {
            $monthName = Carbon::createFromDate($month->year_num, $month->month_num, 1)->format('F Y');
            
            // Top product: most revenue in this month
            $topProduct = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->whereMonth('orders.created_at', $month->month_num)
                ->whereYear('orders.created_at', $month->year_num)
                ->select('products.name', DB::raw('SUM(order_items.quantity * order_items.price) as revenue'))
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('revenue')
                ->first();

            // Best customer: most orders or revenue
            $bestCustomer = DB::table('orders')
                ->join('customers', 'orders.customer_id', '=', 'customers.id')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereMonth('created_at', $month->month_num)
                ->whereYear('created_at', $month->year_num)
                ->select('customers.full_name', DB::raw('COUNT(*) as order_count'))
                ->groupBy('customers.id', 'customers.full_name')
                ->orderByDesc('order_count')
                ->first();

            $monthlyData[] = [
                'month_name' => $monthName,
                'order_count' => $month->order_count,
                'total_sales' => $month->total_sales ?? 0,
                'avg_order_value' => round($month->avg_order_value ?? 0, 2),
                'top_product' => $topProduct->name ?? 'N/A',
                'best_customer' => $bestCustomer->full_name ?? 'N/A',
            ];
        }

        // Recent orders (last 20, with relations)
        $recentOrders = Order::with(['customer', 'items.product'])
            ->whereBetween('created_at', [$startDate->copy()->subYear(), now()])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('reports', compact('summary', 'monthlyData', 'recentOrders', 'year'));
    }
}
