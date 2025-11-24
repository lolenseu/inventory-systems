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
      <input id="searchInput" class="search-input" type="text" placeholder="Order #, Customer, Product">
      <button class="add-btn" id="openAddOrderModalBtn">Add Order</button>
    </div>
  </div>

  <div class="orders-container">
    <table>
      <thead>
        <tr>
          <th>Order #</th>
          <th>Customer</th>
          <th>Products</th>
          <th>Total Amount</th>
          <th>Status</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($orders as $order)
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
          <td class="date-cell">{{ $order->created_at->format('M d, Y') }}</td>
          <td class="action-cell">
            <button type="button" class="view-btn"
              data-id="{{ $order->id }}"
              data-order-number="{{ $order->order_number }}"
              data-customer-id="{{ $order->customer_id }}"
              data-customer="{{ $order->customer ? $order->customer->full_name : 'N/A' }}"
              data-customer-email="{{ $order->customer ? $order->customer->email : 'N/A' }}"
              data-total="{{ $order->total_amount }}"
              data-status="{{ $order->status }}"
              data-notes="{{ $order->notes }}"
              data-items="{{ json_encode($order->items->map(function($item) {
                return [
                  'product_id' => $item->product_id,
                  'product_name' => $item->product->name,
                  'quantity' => $item->quantity,
                  'price' => $item->price,
                  'subtotal' => $item->quantity * $item->price
                ];
              })) }}"
              data-created="{{ $order->created_at->format('M d, Y - h:i A') }}">View</button>
            <button type="button" class="edit-btn"
              data-id="{{ $order->id }}"
              data-order-number="{{ $order->order_number }}"
              data-customer-id="{{ $order->customer_id }}"
              data-product-id="{{ $order->items->first() ? $order->items->first()->product_id : '' }}"
              data-quantity="{{ $order->items->first() ? $order->items->first()->quantity : 1 }}"
              data-price="{{ $order->items->first() ? $order->items->first()->price : 0 }}"
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

        <div class="form-row">
          <div class="form-group">
            <label class="modal-label">Customer</label>
            <select name="customer_id" id="addCustomerId" required>
              <option value="">-- Select Customer --</option>
              @foreach($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->full_name }} ({{ $customer->email }})</option>
              @endforeach
            </select>
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
        </div>

        <div class="form-row">
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
          <span class="detail-label">Order #:</span>
          <span id="viewOrderNumber"></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Customer:</span>
          <span id="viewCustomer"></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Customer Email:</span>
          <span id="viewCustomerEmail"></span>
        </div>
        <div class="detail-row full-width">
          <span class="detail-label">Products:</span>
          <div id="viewProducts" class="products-list"></div>
        </div>
        <div class="detail-row">
          <span class="detail-label">Total Amount:</span>
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

        <div class="form-row">
          <div class="form-group">
            <label class="modal-label">Customer</label>
            <select name="customer_id" id="editCustomerId" required>
              <option value="">-- Select Customer --</option>
              @foreach($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->full_name }} ({{ $customer->email }})</option>
              @endforeach
            </select>
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
        </div>

        <div class="form-row">
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
      <p>Are you sure you want to delete this order? This action cannot be undone and will restore the product quantities to inventory.</p>
      <form id="deleteOrderForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="delete-actions">
          <button type="submit" class="delete-confirm-btn">Delete Order</button>
          <button type="button" class="cancel-confirm-btn" id="cancelDeleteOrderBtn">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<button id="backToTop" class="back-to-top">Back to Top</button>

<script src="{{ asset('js/orders.js') }}"></script>
@endsection