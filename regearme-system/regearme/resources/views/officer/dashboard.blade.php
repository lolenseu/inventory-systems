@extends('layout')
@section('title', 'Officer Dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/officer/dashboard.css') }}">

<div class="dashboard-page">
  <div class="dashboard-header">
    <div class="header-left">
      <h2>Officer Dashboard</h2>
      <a id="printCv" class="print-cv-link">Print Equipment</a>
    </div>
    <div class="header-actions">
      <label class="filter-label">Filter:</label>
      <select id="statusFilter" class="filter-select">
        <option value="all">All</option>
        <option value="available">Available</option>
        <option value="unavailable">Unavailable</option>
        <option value="requested">Requested</option>
        <option value="approved">Approved</option>
        <option value="denied">Denied</option>
      </select>
      <label class="filter-label" for="searchInput">Search:</label>
      <input id="searchInput" class="search-input" type="text" placeholder="Item Name or Type">
      <button class="add-btn" id="openModalBtn">Add Equipment</button>
    </div>
  </div>

  <div class="dashboard-cards">
    <div class="card">
      <h3>Total Equipment</h3>
      <div class="value">{{ $totalEquipment ?? 0 }}</div>
      <div class="label">All Equipment Items</div>
    </div>
    <div class="card">
      <h3>Available</h3>
      <div class="value">{{ $availableCount ?? 0 }}</div>
      <div class="label">Ready for Request</div>
    </div>
    <div class="card">
      <h3>Requested</h3>
      <div class="value">{{ $requestedCount ?? 0 }}</div>
      <div class="label">Pending Approval</div>
    </div>
    <div class="card">
      <h3>Approved</h3>
      <div class="value">{{ $approvedCount ?? 0 }}</div>
      <div class="label">Approved Requests</div>
    </div>
  </div>

  <div class="dashboard-container">
    <table>
      <thead>
        <tr>
          <th>Item Name</th>
          <th>Type</th>
          <th>Quantity</th>
          <th>Status</th>
          <th>Requester</th>
          <th>Request Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @if(isset($equipment) && !$equipment->isEmpty())
          @foreach($equipment as $item)
            @php
              $statusClass = '';
              $statusLabel = '';
              switch($item->status) {
                case 'available':
                  $statusClass = 'status-available';
                  $statusLabel = 'Available';
                  break;
                case 'unavailable':
                  $statusClass = 'status-unavailable';
                  $statusLabel = 'Unavailable';
                  break;
                case 'requested':
                  $statusClass = 'status-requested';
                  $statusLabel = 'Requested';
                  break;
                case 'approved':
                  $statusClass = 'status-approved';
                  $statusLabel = 'Approved';
                  break;
                case 'denied':
                  $statusClass = 'status-denied';
                  $statusLabel = 'Denied';
                  break;
                default:
                  $statusClass = 'status-available';
                  $statusLabel = 'Available';
              }
            @endphp
            <tr>
              <td class="name-cell">{{ $item->item_name }}</td>
              <td>{{ $item->type }}</td>
              <td>{{ $item->quantity }}</td>
              <td class="status-cell {{ $statusClass }}">{{ $statusLabel }}</td>
              <td>{{ $item->user ? $item->user->in_game_name : 'N/A' }}</td>
              <td>{{ $item->request_date ? $item->request_date->format('M d, Y') : 'N/A' }}</td>
              <td>
                <button type="button" class="view-btn"
                  data-id="{{ $item->id }}"
                  data-item-name="{{ $item->item_name }}"
                  data-type="{{ $item->type }}"
                  data-quantity="{{ $item->quantity }}"
                  data-status="{{ $item->status }}"
                  data-user="{{ $item->user ? $item->user->in_game_name : 'N/A' }}"
                  data-request-date="{{ $item->request_date ? $item->request_date->format('M d, Y') : 'N/A' }}">View</button>
                @if($item->status === 'requested')
                  <button type="button" class="approve-btn" data-id="{{ $item->id }}">Approve</button>
                  <button type="button" class="deny-btn" data-id="{{ $item->id }}">Deny</button>
                @endif
                <button type="button" class="delete-btn" data-id="{{ $item->id }}">Delete</button>
              </td>
            </tr>
          @endforeach
        @else
          <tr>
            <td colspan="7">
              <div class="no-data">
                <p>No Equipment Found</p>
              </div>
            </td>
          </tr>
        @endif
      </tbody>
    </table>
  </div>

  <!-- Add Modal -->
  <div id="addModal" class="modal">
    <div class="modal-content two-column-modal">
      <span class="close-btn" id="closeModalBtn">×</span>
      <h3>Add New Equipment</h3>
      <form method="POST" action="{{ route('officer.equipment.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body-split">
          <div class="modal-left">
            <label class="modal-label">Equipment Image</label>
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
              <label class="modal-label">Item Name</label>
              <input type="text" name="item_name" id="addItemName" placeholder="Equipment name" required>
            </div>
            <div class="form-group">
              <label class="modal-label">Type</label>
              <input type="text" name="type" id="addType" placeholder="Equipment type" required>
            </div>
            <div class="form-group">
              <label class="modal-label">Quantity</label>
              <input type="number" name="quantity" id="addQuantity" min="0" value="0" required>
            </div>
            <div class="form-group">
              <label class="modal-label">Status</label>
              <select name="status" id="addStatus" required>
                <option value="available">Available</option>
                <option value="unavailable">Unavailable</option>
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
      <h3>Equipment Details</h3>
      <div class="product-image-view" id="viewImageView" style="margin-bottom: 20px;"></div>
      <p><strong>Item Name:</strong> <span id="viewItemName"></span></p>
      <p><strong>Type:</strong> <span id="viewType"></span></p>
      <p><strong>Quantity:</strong> <span id="viewQuantity"></span></p>
      <p><strong>Status:</strong> <span id="viewStatus"></span></p>
      <p><strong>Requester:</strong> <span id="viewUser"></span></p>
      <p><strong>Request Date:</strong> <span id="viewRequestDate"></span></p>
    </div>
  </div>

  <!-- Approve Modal -->
  <div id="approveModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" id="closeApproveBtn">×</span>
      <h3>Approve Request</h3>
      <p>Are you sure you want to approve this equipment request?</p>
      <form id="approveForm" method="POST">
        @csrf
        @method('PUT')
        <div class="delete-actions">
          <button type="submit" class="approve-confirm-btn">Approve</button>
          <button type="button" class="cancel-confirm-btn" id="cancelApproveBtn">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Deny Modal -->
  <div id="denyModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" id="closeDenyBtn">×</span>
      <h3>Deny Request</h3>
      <p>Are you sure you want to deny this equipment request?</p>
      <form id="denyForm" method="POST">
        @csrf
        @method('PUT')
        <div class="delete-actions">
          <button type="submit" class="deny-confirm-btn">Deny</button>
          <button type="button" class="cancel-confirm-btn" id="cancelDenyBtn">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Modal -->
  <div id="deleteModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" id="closeDeleteBtn">×</span>
      <h3>Confirm Delete</h3>
      <p>Are you sure you want to delete this equipment?</p>
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

<script src="{{ asset('js/officer/dashboard.js') }}"></script>
@endsection