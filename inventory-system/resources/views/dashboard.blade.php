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
            <!-- Buy order content will be added here -->
          </tbody>
        </table>
      </div>

      @if($items->isEmpty())
      <div class="no-dashboard">
        <p>No Orders found</p>
      </div>
      @endif

    </div>
  </section>
</div>

<button id="backToTop" class="back-to-top">Back to Top</button>

<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection