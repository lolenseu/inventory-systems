@extends('layout')
@section('title', 'User Dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/user/dashboard.css') }}">

<div class="user-dashboard-page">
  <div class="user-dashboard-header">
    <div class="header-left">
      <h2>User Dashboard</h2>
      <a id="printCv" class="print-cv-link">Print Requests</a>
    </div>
    <div class="header-actions">
      <label class="filter-label">Filter:</label>
      <select id="statusFilter" class="filter-select">
        <option value="all">All</option>
        <option value="available">Available</option>
        <option value="requested">Requested</option>
        <option value="approved">Approved</option>
        <option value="denied">Denied</option>
      </select>
      <label class="filter-label" for="searchInput">Search:</label>
      <input id="searchInput" class="search-input" type="text" placeholder="Item Name or Type">
    </div>
  </div>

  <div class="dashboard-cards">
    <div class="card">
      <h3>Total Requests</h3>
      <div class="value">{{ $totalRequests ?? 0 }}</div>
      <div class="label">All Your Requests</div>
    </div>
    <div class="card">
      <h3>Available</h3>
      <div class="value">{{ $availableCount ?? 0 }}</div>
      <div class="label">Ready to Request</div>
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

  <div class="user-dashboard-container">
    <table>
      <thead>
        <tr>
          <th>Item Name</th>
          <th>Type</th>
          <th>Quantity</th>
          <th>Status</th>
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
              <td>{{ $item->request_date ? $item->request_date->format('M d, Y') : 'N/A' }}</td>
              <td>
                <button type="button" class="view-btn"
                  data-id="{{ $item->id }}"
                  data-item-name="{{ $item->item_name }}"
                  data-type="{{ $item->type }}"
                  data-quantity="{{ $item->quantity }}"
                  data-status="{{ $item->status }}"
                  data-request-date="{{ $item->request_date ? $item->request_date->format('M d, Y') : 'N/A' }}">View</button>
                @if($item->status === 'available')
                  <button type="button" class="request-btn" data-id="{{ $item->id }}">Request</button>
                @elseif($item->status === 'requested')
                  <button type="button" class="cancel-btn" data-id="{{ $item->id }}">Cancel</button>
                @endif
              </td>
            </tr>
          @endforeach
        @else
          <tr>
            <td colspan="6">
              <div class="no-data">
                <p>No Equipment Found</p>
              </div>
            </td>
          </tr>
        @endif
      </tbody>
    </table>
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
      <p><strong>Request Date:</strong> <span id="viewRequestDate"></span></p>
    </div>
  </div>

  <!-- Request Modal -->
  <div id="requestModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" id="closeRequestBtn">×</span>
      <h3>Request Equipment</h3>
      <p>Are you sure you want to request this equipment?</p>
      <form id="requestForm" method="POST">
        @csrf
        <div class="delete-actions">
          <button type="submit" class="request-confirm-btn">Request</button>
          <button type="button" class="cancel-confirm-btn" id="cancelRequestBtn">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Cancel Modal -->
  <div id="cancelModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" id="closeCancelBtn">×</span>
      <h3>Cancel Request</h3>
      <p>Are you sure you want to cancel this equipment request?</p>
      <form id="cancelForm" method="POST">
        @csrf
        @method('PUT')
        <div class="delete-actions">
          <button type="submit" class="cancel-confirm-btn">Cancel</button>
          <button type="button" class="cancel-confirm-btn" id="cancelCancelBtn">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<button id="backToTop" class="back-to-top">Back to Top</button>

<script src="{{ asset('js/user/dashboard.js') }}"></script>
@endsection