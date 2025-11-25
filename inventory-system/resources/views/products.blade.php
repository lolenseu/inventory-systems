@extends('layout')
@section('title', 'Products')

@section('content')
<link rel="stylesheet" href="{{ asset('css/products.css') }}">

<div class="products-page">
  <div class="products-header">
    <div class="header-left">
      <h2>Products</h2>
      <a id="printCv" class="print-cv-link">Print Product</a>
    </div>
    <div class="header-actions">
      <label class="filter-label">Filter:</label>
      <select id="statusFilter" class="filter-select">
        <option value="all">All</option>
        <option value="in-stock">In Stock</option>
        <option value="low-stock">Low Stock</option>
        <option value="out-of-stock">Out of Stock</option>
      </select>
      <label class="filter-label" for="searchInput">Search:</label>
      <input id="searchInput" class="search-input" type="text" placeholder="SKU or Name">
      <button class="add-btn" id="openModalBtn">Add Product</button>
    </div>
  </div>

  <div class="products-container">
    <table>
      <thead>
        <tr>
          <th>SKU</th>
          <th>Name</th>
          <th>Description</th>
          <th>Quantity</th>
          <th>Unit Price</th>
          <th>Supplier</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($products as $product)
        @php
          $qty = (int) $product->quantity;
          if ($qty === 0) {
            $statusClass = 'status-out-of-stock';
            $statusLabel = 'Out of Stock';
          } elseif ($qty <= 20) {
            $statusClass = 'status-low-stock';
            $statusLabel = 'Low Stock';
          } else {
            $statusClass = 'status-in-stock';
            $statusLabel = 'In Stock';
          }
        @endphp
        <tr>
          <td class="student-id-cell">{{ $product->sku }}</td>
          <td class="name-cell">{{ $product->name }}</td>
          <td>{{ Str::limit($product->description, 30) ?? 'N/A' }}</td>
          <td>{{ $product->quantity }}</td>
          <td>₱{{ number_format($product->price, 2) }}</td>
          <td>{{ $product->supplier ? $product->supplier->name : 'N/A' }}</td>
          <td class="status-cell {{ $statusClass }}">{{ $statusLabel }}</td>
          <td>
            <button type="button" class="view-btn"
              data-id="{{ $product->id }}"
              data-sku="{{ $product->sku }}"
              data-name="{{ $product->name }}"
              data-description="{{ $product->description }}"
              data-quantity="{{ $product->quantity }}"
              data-price="{{ $product->price }}"
              data-supplier-id="{{ $product->supplier_id }}"
              data-supplier-name="{{ $product->supplier ? $product->supplier->name : '' }}"
              data-image-url="{{ $product->image && $product->image_type ? 'data:' . $product->image_type . ';base64,' . base64_encode($product->image) : '' }}">View</button>
            <button type="button" class="edit-btn"
              data-id="{{ $product->id }}"
              data-sku="{{ $product->sku }}"
              data-name="{{ $product->name }}"
              data-description="{{ $product->description }}"
              data-quantity="{{ $product->quantity }}"
              data-price="{{ $product->price }}"
              data-supplier-id="{{ $product->supplier_id }}"
              data-supplier-name="{{ $product->supplier ? $product->supplier->name : '' }}"
              data-image-url="{{ $product->image && $product->image_type ? 'data:' . $product->image_type . ';base64,' . base64_encode($product->image) : '' }}">Edit</button>
            <button type="button" class="delete-btn" data-id="{{ $product->id }}">Delete</button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    @if($products->isEmpty())
      <div class="no-products">
      <p>No Products Found</p>
    </div>
    @endif
  </div>

  <!-- Add Modal -->
  <div id="addModal" class="modal">
    <div class="modal-content two-column-modal">
      
      <span class="close-btn" id="closeModalBtn">×</span>
      <h3>Add New Product</h3>

      <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="modal-body-split">

          <div class="modal-left">
            <label class="modal-label">Product Image</label>

            <div class="image-upload-container">
              <label class="image-upload-area" for="addImage">
                <div class="image-upload-placeholder" id="addPlaceholder">
                  <div class="upload-icon">📁</div>
                  <span>Click to upload image</span>
                </div>

                <img id="addImagePreview" style="display:none;">
              </label>

              <input type="file" name="image" id="addImage" class="image-input" accept="image/*">
            </div>
          </div>

          <div class="modal-right">

            <div class="form-group">
              <label class="modal-label">SKU</label>
              <input type="text" name="sku" id="addSku" placeholder="SKU" required>
            </div>

            <div class="form-group">
              <label class="modal-label">Name</label>
              <input type="text" name="name" id="addName" placeholder="Product name" required>
            </div>

            <div class="form-group full-width">
              <label class="modal-label">Description</label>
              <textarea name="description" id="addDescription" placeholder="Product description" rows="3"></textarea>
            </div>

            <div class="form-row" style="display:flex; gap:15px;">
              <div class="form-group">
                <label class="modal-label">Quantity</label>
                <input type="number" name="quantity" id="addQuantity" min="0" value="0" required>
              </div>

              <div class="form-group">
                <label class="modal-label">Unit Price</label>
                <input type="number" step="0.01" name="price" id="addPrice" value="0.00" required>
              </div>
            </div>

            <div class="form-group">
              <label class="modal-label">Supplier</label>
              <select name="supplier_id" id="addSupplier">
                <option value="">-- Select Supplier --</option>
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
              </select>
            </div>

          </div>
        </div>

        <button type="submit" class="submit-btn">Submit</button>
      </form>
    </div>
  </div>


  <!-- View Modal -->
  <div id="viewModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" id="closeViewBtn">×</span>
      <h3>Product Details</h3>
      <div class="product-image-view" id="viewImageView" style="margin-bottom: 20px;"></div>
      <p><strong>SKU:</strong> <span id="viewSku"></span></p>
      <p><strong>Name:</strong> <span id="viewName"></span></p>
      <p><strong>Description:</strong></p>
      <p id="viewDescription" class="modal-description"></p>
      <p><strong>Quantity:</strong> <span id="viewQuantity"></span></p>
      <p><strong>Unit Price:</strong> ₱<span id="viewPrice"></span></p>
      <p><strong>Supplier:</strong> <span id="viewSupplier"></span></p>
    </div>
  </div>

  <!-- Edit Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content two-column-modal">
      <span class="close-btn" id="closeEditBtn">×</span>
      <h3>Edit Product</h3>

      <form id="editForm" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="modal-body-split">

          <!-- LEFT SIDE – IMAGE UPLOAD -->
          <div class="modal-left">
            <label class="modal-label">Product Image</label>

            <div class="image-upload-container">
              <label class="image-upload-area" for="editImage">
                <div class="image-upload-placeholder" id="editPlaceholder">
                  <div class="upload-icon">📁</div>
                  <span>Click to upload image</span>
                </div>
                <img id="editImagePreview" style="display:none;">
              </label>

              <input type="file" name="image" id="editImage" class="image-input" accept="image/*">
            </div>
          </div>

          <!-- RIGHT SIDE – INPUT FIELDS -->
          <div class="modal-right">

            <div class="form-group">
              <label class="modal-label">SKU</label>
              <input type="text" name="sku" id="editSku" placeholder="SKU" required>
            </div>

            <div class="form-group">
              <label class="modal-label">Name</label>
              <input type="text" name="name" id="editName" placeholder="Product name" required>
            </div>

            <div class="form-group full-width">
              <label class="modal-label">Description</label>
              <textarea name="description" id="editDescription" placeholder="Product description" rows="3"></textarea>
            </div>

            <div class="form-row" style="display:flex; gap:15px;">
              <div class="form-group">
                <label class="modal-label">Quantity</label>
                <input type="number" name="quantity" id="editQuantity" min="0" required>
              </div>

              <div class="form-group">
                <label class="modal-label">Unit Price</label>
                <input type="number" step="0.01" name="price" id="editPrice" required>
              </div>
            </div>

            <div class="form-group">
              <label class="modal-label">Supplier</label>
              <select name="supplier_id" id="editSupplier">
                <option value="">-- Select Supplier --</option>
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
              </select>
            </div>

          </div>
        </div>

        <button type="submit" class="submit-btn">Save Changes</button>
      </form>
    </div>
  </div>

  <!-- Delete Modal -->
  <div id="deleteModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" id="closeDeleteBtn">×</span>
      <h3>Confirm Delete</h3>
      <p>Are you sure you want to delete this product?</p>
      <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="delete-actions">
          <button type="submit" class="delete-confirm-btn">Delete</button>
          <button type="button" class="cancel-confirm-btn" id="cancelDeleteBtn">Cancel</button>
        </div>
      </form>
    </div>
  </div>

</div>

<button id="backToTop" class="back-to-top">Back to Top</button>

<script src="{{ asset('js/products.js') }}"></script>
@endsection