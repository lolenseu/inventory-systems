@extends('layout')
@section('title', 'Sales Reports')

@section('content')

<link rel="stylesheet" href="{{ asset('css/reports.css') }}">

<div class="reports-page">
  <div class="reports-header">
    <div class="header-left">
      <h2>Sales Reports</h2>
      <a id="printReportsCv" class="print-cv-link">Print Report</a>
    </div>
    <div class="header-actions">
      <label class="filter-label">Year:</label>
      <select id="yearFilter" class="filter-select">
        @for ($year = now()->year; $year >= now()->year - 5; $year--)
          <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>{{ $year }}</option>
        @endfor
      </select>
      <label class="filter-label" for="monthFilter">Month:</label>
      <select id="monthFilter" class="filter-select">
        <option value="all">All Months</option>
        <option value="1">January</option>
        <option value="2">February</option>
        <option value="3">March</option>
        <option value="4">April</option>
        <option value="5">May</option>
        <option value="6">June</option>
        <option value="7">July</option>
        <option value="8">August</option>
        <option value="9">September</option>
        <option value="10">October</option>
        <option value="11">November</option>
        <option value="12">December</option>
      </select>
      <label class="filter-label" for="searchInput">Search:</label>
      <input id="searchInput" class="search-input" type="text" placeholder="Customer, Product, Order #">
      <button class="refresh-btn" id="refreshReportsBtn">Refresh</button>
    </div>
  </div>

  <!-- Summary Cards -->
  <section class="summary-section">
    <div class="summary-card">
      <h3 class="summary-title">Total Orders</h3>
      <p class="summary-value" id="totalOrders">{{ $summary['total_orders'] ?? 0 }}</p>
    </div>
    <div class="summary-card">
      <h3 class="summary-title">Total Sales</h3>
      <p class="summary-value" id="totalSales">₱{{ number_format($summary['total_sales'] ?? 0, 2) }}</p>
    </div>
    <div class="summary-card">
      <h3 class="summary-title">Avg Order Value</h3>
      <p class="summary-value" id="avgOrderValue">₱{{ number_format($summary['avg_order_value'] ?? 0, 2) }}</p>
    </div>
    <div class="summary-card">
      <h3 class="summary-title">Pending Orders</h3>
      <p class="summary-value" id="pendingOrders">{{ $summary['pending_orders'] ?? 0 }}</p>
    </div>
  </section>

  <!-- Monthly Sales Chart -->
  <section class="chart-section">
    <div class="chart-container">
      <h3 class="chart-title">Monthly Sales Trend</h3>
      <div class="chart-wrapper">
        <canvas id="monthlySalesChart" width="800" height="400"></canvas>
      </div>
    </div>
  </section>

  <!-- Sales Data Table -->
  <section class="table-section">
    <div class="reports-container">
      <div class="table-header">
        <h3>Monthly Sales Breakdown</h3>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Month</th>
              <th>Orders Count</th>
              <th>Total Sales</th>
              <th>Avg Order Value</th>
              <th>Top Product</th>
              <th>Best Customer</th>
            </tr>
          </thead>
          <tbody id="reportsTableBody">
            @foreach($monthlyData as $data)
            <tr>
              <td class="month-cell">{{ $data['month_name'] }}</td>
              <td class="orders-cell">{{ $data['order_count'] }}</td>
              <td class="sales-cell">₱{{ number_format($data['total_sales'], 2) }}</td>
              <td class="avg-cell">₱{{ number_format($data['avg_order_value'], 2) }}</td>
              <td class="product-cell">{{ $data['top_product'] }}</td>
              <td class="customer-cell">{{ $data['best_customer'] }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if(empty($monthlyData))
        <div class="no-reports">
          <p>No sales data found</p>
        </div>
      @endif
    </div>
  </section>

  <!-- Detailed Orders Table -->
  <section class="table-section" style="margin-top: 30px;">
    <div class="reports-container">
      <div class="table-header">
        <h3>Recent Orders</h3>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Products</th>
              <th>Total Amount</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody id="ordersTableBody">
            @foreach($recentOrders as $order)
            @php
              $statusClass = match($order->status) {
                'pending' => 'status-pending',
                'approved' => 'status-approved',
                'declined' => 'status-declined',
                'delivered' => 'status-delivered',
                default => 'status-pending'
              };
            @endphp
            <tr>
              <td class="order-id-cell">{{ $order->order_number }}</td>
              <td class="customer-cell">{{ $order->customer ? $order->customer->full_name : 'N/A' }}</td>
              <td class="products-cell">
                @foreach($order->items as $item)
                  <div class="product-item">
                    {{ $item->product->name }} ({{ $item->quantity }})
                  </div>
                @endforeach
              </td>
              <td class="amount-cell">₱{{ number_format($order->total_amount, 2) }}</td>
              <td class="status-cell {{ $statusClass }}">{{ ucfirst($order->status) }}</td>
              <td class="date-cell">{{ $order->created_at->format('M d, Y - h:i A') }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($recentOrders->isEmpty())
        <div class="no-reports">
          <p>No recent orders found</p>
        </div>
      @endif
    </div>
  </section>
</div>

<button id="backToTop" class="back-to-top">Back to Top</button>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/reports.js') }}"></script>
@endsection