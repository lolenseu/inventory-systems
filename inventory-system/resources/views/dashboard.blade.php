@extends('layout')
@section('title', 'Dashboard')

@section('content')

<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

<div class="dashboard-page">
  <div class="dashboard-header">
    <div class="header-left">
      <h2>Dashboard</h2>
    </div>
  </div>

  <section class="summary-section">
    <div class="summary-card">
      <h3 class="summary-title">Total Products</h3>
      <p class="summary-value">{{ $summary['total_items'] ?? 0 }}</p>
    </div>
    <div class="summary-card">
      <h3 class="summary-title">Low Stock</h3>
      <p class="summary-value">{{ $summary['low_stock'] ?? 0 }}</p>
    </div>
    <div class="summary-card">
      <h3 class="summary-title">Out of Stock</h3>
      <p class="summary-value">{{ $summary['out_of_stock'] ?? 0 }}</p>
    </div>
    <div class="summary-card">
      <h3 class="summary-title">Total Value</h3>
      <p class="summary-value">₱{{ number_format($summary['total_value'] ?? 0, 2) }}</p>
    </div>
  </section>

  <section class="table-area">
    <div class="dashboard-container">
      <div class="table-header">
        <h3>Top Products</h3>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>SKU</th>
              <th>Product</th>
              <th>Quantity</th>
              <th>Unit Price</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($items->sortByDesc('price') as $item)
            @php
              if ($item->quantity == 0) {
                  $status = 'Out of Stock';
                  $statusClass = 'status-out';
              } elseif ($item->quantity <= 20) {
                  $status = 'Low Stock';
                  $statusClass = 'status-low';
              } else {
                  $status = 'In Stock';
                  $statusClass = 'status-in';
              }
            @endphp
            <tr>
              <td class="student-id-cell">{{ $item->sku }}</td>
              <td class="name-cell">{{ $item->name }}</td>
              <td>{{ $item->quantity }}</td>
              <td>₱{{ number_format($item->price, 2) }}</td>
              <td class="status-cell {{ $statusClass }}">{{ $status }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if($items->isEmpty())
      <div class="no-dashboard">
        <p>No items found</p>
      </div>
      @endif

    </div>
  </section>

  <section class="table-area" style="margin-top: 30px;">
    <div class="dashboard-container">
      <div class="table-header">
        <h3>Top Orders</h3>
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
          <tbody>
            @forelse($orders as $order)
            @php
              $statusClass = match($order->status) {
                'pending' => 'status-warning',
                'approved' => 'status-success',
                'declined' => 'status-danger',
                'delivered' => 'status-info',
                default => 'status-secondary'
              };
            @endphp
            <tr>
              <td class="order-id-cell">{{ $order->order_number }}</td>
              <td class="customer-cell">{{ $order->customer->full_name ?? 'N/A' }}</td>
              <td class="products-cell">
                @foreach($order->items as $item)
                  <div>{{ $item->product->name }} ({{ $item->quantity }})</div>
                @endforeach
              </td>
              <td class="amount-cell">₱{{ number_format($order->total_amount, 2) }}</td>
              <td class="status-cell {{ $statusClass }}">{{ ucfirst($order->status) }}</td>
              <td class="date-cell">{{ $order->created_at->format('M d, Y H:i') }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center">No orders found</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if($orders->isEmpty())
      <div class="no-dashboard">
        <p>No orders found</p>
      </div>
      @endif

    </div>
  </section>
</div>

<button id="backToTop" class="back-to-top">Back to Top</button>

<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection