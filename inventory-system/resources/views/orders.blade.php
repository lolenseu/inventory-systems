@extends('layout')
@section('title', 'Orders Management')

@section('content')

<link rel="stylesheet" href="{{ asset('css/orders.css') }}">

<div class="orders-page">
  <div class="orders-header">
    <div class="header-left">
      <h2>Orders</h2>
      <a id="printOrdersCv" class="print-cv-link">Print CV</a>
    </div>
    <div class="header-actions">
      <label class="filter-label">Filter:</label>
      <select id="statusFilter" class="filter-select">
        <option value="all">All Orders</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="declined">Declined</option>
        <option value="delivered">Delivered</option>
      </select>
      <label class="filter-label" for="searchInput">Search:</label>
      <input id="searchInput" class="search-input" type="text" placeholder="Order ID, Customer, Product">
      <button class="add-btn" id="openAddOrderModalBtn">Add Order</button>
    </div>
  </div>

  <div class="orders-container">
    <table>
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Product</th>
          <th>Quantity</th>
          <th>Total Price</th>
          <th>Status</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($orders as $order)
        @php
          $statusClass = '';
          switch($order->status) {
            case 'pending':
              $statusClass = 'status-pending';
              break;
            case 'approved':
              $statusClass = 'status-approved';
              break;
            case 'declined':
              $statusClass = 'status-declined';
              break;
            case 'delivered':
              $statusClass = 'status-delivered';
              break;
          }
        @endphp
        <tr>
          <td class="order-id-cell">{{ $order->id }}</td>
          <td class="customer-cell">{{ $order->customer_name }}</td>
          <td class="product-cell">{{ $order->product ? $order->product->name : 'N/A' }}</td>
          <td>{{ $order->quantity }}</td>
          <td>₱{{ number_format($order->total_price, 2) }}</td>
          <td class="status-cell {{ $statusClass }}">{{ ucfirst($order->status) }}</td>
          <td>{{ $order->created_at->format('M d, Y') }}</td>
          <td>
            <button type="button" class="view-btn"
              data-id="{{ $order->id }}"
              data-customer="{{ $order->customer_name }}"
              data-product-id="{{ $order->product_id }}"
              data-product-name="{{ $order->product ? $order->product->name : 'N/A' }}"
              data-quantity="{{ $order->quantity }}"
              data-price="{{ $order->product ? $order->product->price : 0 }}"
              data-total="{{ $order->total_price }}"
              data-status="{{ $order->status }}"
              data-notes="{{ $order->notes }}"
              data-created="{{ $order->created_at->format('M d, Y - h:i A') }}">View</button>
            <button type="button" class="edit-btn"
              data-id="{{ $order->id }}"
              data-customer="{{ $order->customer_name }}"
              data-product-id="{{ $order->product_id }}"
              data-quantity="{{ $order->quantity }}"
              data-status="{{ $order->status }}"
              data-notes="{{ $order->notes }}">Edit</button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    @if($orders->isEmpty())
      <div class="no-orders">
        <p>No Orders Found</p>
      </div>
    @endif
  </div>

  <!-- Add Order Modal -->
  <div id="addOrderModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" id="closeAddOrderModalBtn">×</span>
      <h3>Add New Order</h3>

      <form id="addOrderForm" method="POST" action="{{ route('orders.store') }}">
        @csrf

        <div class="form-group">
          <label class="modal-label">Customer Name</label>
          <input type="text" name="customer_name" id="addCustomerName" placeholder="Customer name" required>
        </div>

        <div class="form-group">
          <label class="modal-label">Product</label>
          <select name="product_id" id="addProductId" required>
            <option value="">-- Select Product --</option>
            @foreach($products as $product)
              <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }} (₱{{ number_format($product->price, 2) }})</option>
            @endforeach
          </select>
        </div>

        <div class="form-row" style="display:flex; gap:15px;">
          <div class="form-group">
            <label class="modal-label">Quantity</label>
            <input type="number" name="quantity" id="addQuantity" min="1" value="1" required>
          </div>

          <div class="form-group">
            <label class="modal-label">Unit Price</label>
            <input type="number" step="0.01" name="unit_price" id="addUnitPrice" value="0.00" readonly required>
          </div>
        </div>

        <div class="form-group">
          <label class="modal-label">Total Price</label>
          <input type="number" step="0.01" name="total_price" id="addTotalPrice" value="0.00" readonly required>
        </div>

        <div class="form-group">
          <label class="modal-label">Status</label>
          <select name="status" id="addStatus">
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="declined">Declined</option>
            <option value="delivered">Delivered</option>
          </select>
        </div>

        <div class="form-group">
          <label class="modal-label">Notes</label>
          <textarea name="notes" id="addNotes" placeholder="Order notes (optional)" rows="3"></textarea>
        </div>

        <button type="submit" class="submit-btn">Create Order</button>
      </form>
    </div>
  </div>

  <!-- View Order Modal -->
  <div id="viewOrderModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" id="closeViewOrderBtn">×</span>
      <h3>Order Details</h3>
      <div class="order-details">
        <div class="detail-row">
          <span class="detail-label">Order ID:</span>
          <span id="viewOrderId"></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Customer:</span>
          <span id="viewCustomer"></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Product:</span>
          <span id="viewProduct"></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Quantity:</span>
          <span id="viewQuantity"></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Unit Price:</span>
          <span id="viewUnitPrice"></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Total Price:</span>
          <span id="viewTotal"></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Status:</span>
          <span id="viewStatus"></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Date:</span>
          <span id="viewDate"></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Notes:</span>
          <span id="viewNotes" class="modal-description"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Order Modal -->
  <div id="editOrderModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" id="closeEditOrderBtn">×</span>
      <h3>Edit Order</h3>

      <form id="editOrderForm" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
          <label class="modal-label">Customer Name</label>
          <input type="text" name="customer_name" id="editCustomerName" placeholder="Customer name" required>
        </div>

        <div class="form-group">
          <label class="modal-label">Product</label>
          <select name="product_id" id="editProductId" required>
            <option value="">-- Select Product --</option>
            @foreach($products as $product)
              <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }} (₱{{ number_format($product->price, 2) }})</option>
            @endforeach
          </select>
        </div>

        <div class="form-row" style="display:flex; gap:15px;">
          <div class="form-group">
            <label class="modal-label">Quantity</label>
            <input type="number" name="quantity" id="editQuantity" min="1" required>
          </div>

          <div class="form-group">
            <label class="modal-label">Unit Price</label>
            <input type="number" step="0.01" name="unit_price" id="editUnitPrice" value="0.00" readonly required>
          </div>
        </div>

        <div class="form-group">
          <label class="modal-label">Total Price</label>
          <input type="number" step="0.01" name="total_price" id="editTotalPrice" value="0.00" readonly required>
        </div>

        <div class="form-group">
          <label class="modal-label">Status</label>
          <select name="status" id="editStatus">
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="declined">Declined</option>
            <option value="delivered">Delivered</option>
          </select>
        </div>

        <div class="form-group">
          <label class="modal-label">Notes</label>
          <textarea name="notes" id="editNotes" placeholder="Order notes (optional)" rows="3"></textarea>
        </div>

        <div class="modal-actions">
          <button type="submit" class="submit-btn">Save Changes</button>
          <button type="button" class="delete-btn" id="deleteOrderBtn">Delete Order</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Order Modal -->
  <div id="deleteOrderModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" id="closeDeleteOrderBtn">×</span>
      <h3>Confirm Delete</h3>
      <p>Are you sure you want to delete this order? This action cannot be undone.</p>
      <form id="deleteOrderForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="delete-actions">
          <button type="submit" class="delete-confirm-btn">Delete</button>
          <button type="button" class="cancel-confirm-btn" id="cancelDeleteOrderBtn">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<button id="backToTop" class="back-to-top">Back to Top</button>

<script src="{{ asset('js/orders.js') }}"></script>
@endsection