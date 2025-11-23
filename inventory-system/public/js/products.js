document.addEventListener('DOMContentLoaded', function(){
  const statusFilter = document.getElementById('statusFilter');
  const searchInput = document.getElementById('searchInput');
  const printCv = document.getElementById('printCv');

  function getRows(){ return document.querySelectorAll('tbody tr'); }

  function filterRows() {
    const selected = statusFilter ? statusFilter.value : 'all';
    const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
    getRows().forEach(row => {
      const skuCell = row.querySelector('.student-id-cell');
      const nameCell = row.querySelector('.name-cell');
      const qtyCell = row.querySelector('td:nth-child(4)');
      const statusCell = row.querySelector('td:nth-child(7)');
      if (!skuCell || !nameCell || !statusCell) return;
      const sku = skuCell.textContent.trim().toLowerCase();
      const name = nameCell.textContent.trim().toLowerCase();
      const qty = parseInt(qtyCell?.textContent.trim()) || 0;

      // Compute status from quantity only (ignore DB status)
      let computedStatus = 'in-stock';
      if (qty === 0) computedStatus = 'out-of-stock';
      else if (qty <= 20) computedStatus = 'low-stock';

      const matchesFilter = (selected === 'all' || computedStatus === selected);
      const matchesSearch = q === '' || sku.includes(q) || name.includes(q);
      row.style.display = (matchesFilter && matchesSearch) ? '' : 'none';
    });
  }

  if (statusFilter) statusFilter.addEventListener('change', filterRows);
  if (searchInput) {
    searchInput.addEventListener('input', filterRows);
    searchInput.addEventListener('keyup', (e) => { if (e.key === 'Enter') filterRows(); });
  }

  function buildPrintableHTML() {
    let rowsHtml = '';
    getRows().forEach(row => {
      if (window.getComputedStyle(row).display === 'none') return;
      const sku = row.querySelector('td:nth-child(1)')?.textContent.trim() || '';
      const name = row.querySelector('td:nth-child(2)')?.textContent.trim() || '';
      const description = row.querySelector('td:nth-child(3)')?.textContent.trim() || '';
      const qtyText = row.querySelector('td:nth-child(4)')?.textContent.trim() || '';
      const price = row.querySelector('td:nth-child(5)')?.textContent.trim() || '';
      const supplier = row.querySelector('td:nth-child(6)')?.textContent.trim() || '';
      const statusCell = row.querySelector('td:nth-child(7)');
      const statusText = statusCell ? statusCell.textContent.trim() || '' : '';
      const qtyNum = parseInt(qtyText.replace(/[^0-9-]/g, '')) || 0;
      let statusLabel = 'In Stock';
      if (qtyNum === 0) statusLabel = 'Out of Stock';
      else if (qtyNum <= 20) statusLabel = 'Low Stock';

      rowsHtml += `<tr>
        <td style="padding:8px;border:1px solid #ccc;text-align:center">${sku}</td>
        <td style="padding:8px;border:1px solid #ccc;text-align:left">${name}</td>
        <td style="padding:8px;border:1px solid #ccc;text-align:left">${description}</td>
        <td style="padding:8px;border:1px solid #ccc;text-align:center">${supplier}</td>
        <td style="padding:8px;border:1px solid #ccc;text-align:center">${statusLabel}</td>
        <td style="padding:8px;border:1px solid #ccc;text-align:center">${qtyText}</td>
        <td style="padding:8px;border:1px solid #ccc;text-align:right">${price}</td>
      </tr>`;
    });
    return `<!doctype html>
      <html>
        <head>
          <meta charset="utf-8">
          <title>Product List</title>
          <style>
            body{font-family:Arial,sans-serif;color:#222;padding:15px;font-size:12px;}
            table{border-collapse:collapse;width:100%;font-size:11px;}
            th{background:#f4f4f4;padding:8px;border:1px solid #ccc;text-align:center;font-weight:bold;}
            td{padding:6px;border:1px solid #ccc;white-space:pre-line;}
            h2{text-align:center;margin-bottom:15px;font-size:16px;}
          </style>
        </head>
        <body>
          <h2>Product List</h2>
          <table>
            <thead>
              <tr>
                <th>SKU</th>
                <th>Name</th>
                <th>Description</th>
                <th>Supplier</th>
                <th>Status</th>
                <th>Quantity</th>
                <th>Unit Price</th>
              </tr>
            </thead>
            <tbody>
              ${rowsHtml || '<tr><td colspan="7" style="padding:8px;border:1px solid #ccc;text-align:center">No records found</td></tr>'}
            </tbody>
          </table>
        </body>
      </html>`;
  }

  if (printCv) {
    printCv.addEventListener('click', function(){
      const html = buildPrintableHTML();
      const w = window.open('', '_blank');
      w.document.write(html);
      w.document.close();
      w.focus();
      setTimeout(() => { w.print(); w.close(); }, 300);
    });
  }

  // Image upload functionality
  function setupImageUpload(inputId, uploadAreaId, previewId) {
    const imageInput = document.getElementById(inputId);
    const uploadArea = document.getElementById(uploadAreaId);
    const preview = document.getElementById(previewId);

    if (!imageInput || !uploadArea) return;

    uploadArea.addEventListener('click', () => imageInput.click());

    uploadArea.addEventListener('dragover', (e) => {
      e.preventDefault();
      uploadArea.style.borderColor = '#0078d7';
      uploadArea.style.backgroundColor = '#f0f8ff';
    });

    uploadArea.addEventListener('dragleave', () => {
      uploadArea.style.borderColor = '#ccc';
      uploadArea.style.backgroundColor = '#fafafa';
    });

    uploadArea.addEventListener('drop', (e) => {
      e.preventDefault();
      uploadArea.style.borderColor = '#ccc';
      uploadArea.style.backgroundColor = '#fafafa';
      
      if (e.dataTransfer.files && e.dataTransfer.files[0]) {
        imageInput.files = e.dataTransfer.files;
        previewImage(imageInput, preview);
      }
    });

    imageInput.addEventListener('change', () => {
      previewImage(imageInput, preview);
    });
  }

  function previewImage(input, preview) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
        if (preview) {
          preview.src = e.target.result;
          preview.style.display = 'block';
        }
        
        // Update the upload area
        const uploadArea = preview.parentElement;
        const placeholder = uploadArea.querySelector('.image-upload-placeholder');
        if (placeholder) {
          placeholder.style.display = 'none';
        }
      };
      
      reader.readAsDataURL(input.files[0]);
    }
  }

  // Setup image uploads for add and edit modals
  setupImageUpload('addImage', 'addImageUploadArea', 'addImagePreview');
  setupImageUpload('editImage', 'editImageUploadArea', 'editImagePreview');

  // Direct image upload event listeners
  document.getElementById('addImage').addEventListener('change', function(e) {
    const preview = document.getElementById('addImagePreview');
    const placeholder = document.getElementById('addPlaceholder');
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = "block";
        preview.style.maxWidth = "100%";
        preview.style.maxHeight = "180px";
        preview.style.borderRadius = "10px";
        placeholder.style.display = "none";
      };
      reader.readAsDataURL(file);
    }
  });
  
  document.getElementById('editImage').addEventListener('change', function(e) {
    const preview = document.getElementById('editImagePreview');
    const placeholder = document.getElementById('editPlaceholder');
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = "block";
        preview.style.maxWidth = "100%";
        preview.style.maxHeight = "160px";
        placeholder.style.display = "none";
      };
      reader.readAsDataURL(file);
    }
  });

  // Add modal open/close
  const addModal = document.getElementById('addModal');
  const openBtn = document.getElementById('openModalBtn');
  const closeBtn = document.getElementById('closeModalBtn');
  if (openBtn) openBtn.addEventListener('click', () => addModal && (addModal.style.display = 'block'));
  if (closeBtn) closeBtn.addEventListener('click', () => addModal && (addModal.style.display = 'none'));

  // Reset image preview when modal closes
  if (closeBtn) {
    closeBtn.addEventListener('click', () => {
      const imagePreview = document.getElementById('addImagePreview');
      const imageUploadArea = document.getElementById('addImageUploadArea');
      const placeholder = imageUploadArea?.querySelector('.image-upload-placeholder');
      
      if (imagePreview && imageUploadArea && placeholder) {
        imagePreview.style.display = 'none';
        imagePreview.src = '';
        placeholder.style.display = 'flex';
      }
    });
  }

  // Edit handling
  const editForm = document.getElementById('editForm');
  document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-id');
      if (editForm) editForm.action = `/products/${id}`;
      const editSku = document.getElementById('editSku');
      const editName = document.getElementById('editName');
      const editDescription = document.getElementById('editDescription');
      const editQuantity = document.getElementById('editQuantity');
      const editPrice = document.getElementById('editPrice');
      const editSupplier = document.getElementById('editSupplier');

      if (editSku) editSku.value = btn.getAttribute('data-sku') || '';
      if (editName) editName.value = btn.getAttribute('data-name') || '';
      if (editDescription) editDescription.value = btn.getAttribute('data-description') || '';
      if (editQuantity) editQuantity.value = btn.getAttribute('data-quantity') || 0;
      if (editPrice) editPrice.value = btn.getAttribute('data-price') || 0.00;
      if (editSupplier) editSupplier.value = btn.getAttribute('data-supplier-id') || '';

      // Handle image preview for edit
      const imagePreview = document.getElementById('editImagePreview');
      const imageUploadArea = document.getElementById('editImageUploadArea');
      const placeholder = imageUploadArea?.querySelector('.image-upload-placeholder');
      
      const imageUrl = btn.getAttribute('data-image-url');
      
      // Reset image preview first
      if (imagePreview && imageUploadArea && placeholder) {
          imagePreview.style.display = 'none';
          imagePreview.src = '';
          placeholder.style.display = 'flex';
      }
      
      // Show existing image if available
      if (imageUrl && imageUrl !== 'null' && imageUrl !== '' && imagePreview && imageUploadArea && placeholder) {
          imagePreview.src = imageUrl;
          imagePreview.style.display = 'block';
          placeholder.style.display = 'none';
      }

      const editModal = document.getElementById('editModal');
      if (editModal) editModal.style.display = 'block';
    });
  });

  const closeEditBtn = document.getElementById('closeEditBtn');
  if (closeEditBtn) closeEditBtn.addEventListener('click', () => {
    const editModal = document.getElementById('editModal');
    if (editModal) editModal.style.display = 'none';
    
    // Reset image preview
    const imagePreview = document.getElementById('editImagePreview');
    const imageUploadArea = document.getElementById('editImageUploadArea');
    const placeholder = imageUploadArea?.querySelector('.image-upload-placeholder');
    
    if (imagePreview && imageUploadArea && placeholder) {
      imagePreview.style.display = 'none';
      imagePreview.src = '';
      placeholder.style.display = 'flex';
    }
  });

  // View handling
  document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const viewSku = document.getElementById('viewSku');
      const viewName = document.getElementById('viewName');
      const viewDescription = document.getElementById('viewDescription');
      const viewQuantity = document.getElementById('viewQuantity');
      const viewPrice = document.getElementById('viewPrice');
      const viewSupplier = document.getElementById('viewSupplier');
      const viewImageView = document.getElementById('viewImageView');

      if (viewSku) viewSku.textContent = btn.getAttribute('data-sku') || '';
      if (viewName) viewName.textContent = btn.getAttribute('data-name') || '';
      if (viewDescription) viewDescription.textContent = btn.getAttribute('data-description') || 'N/A';
      if (viewQuantity) viewQuantity.textContent = btn.getAttribute('data-quantity') || '';
      if (viewPrice) viewPrice.textContent = btn.getAttribute('data-price') || '';
      
      // Prefer showing supplier name; fall back to ID if name not available
      const supplierName = btn.getAttribute('data-supplier-name');
      const supplierId = btn.getAttribute('data-supplier-id');
      if (viewSupplier) {
        viewSupplier.textContent = supplierName ? supplierName : (supplierId ? 'ID: ' + supplierId : 'N/A');
      }

      // Handle image display for view
      const imageUrl = btn.getAttribute('data-image-url');
      
      if (imageUrl && imageUrl !== 'null' && imageUrl !== '' && viewImageView) {
        viewImageView.innerHTML = `
          <div style="margin-bottom: 15px; text-align: center;">
            <strong>Product Image</strong>
          </div>
          <img src="${imageUrl}" 
            style="max-width: 200px; max-height: 200px; border-radius: 12px; object-fit: cover;" 
            alt="Product Image">
        `;
      } else if (viewImageView) {
        viewImageView.innerHTML = `
          <div style="margin-bottom: 15px; text-align: center;">
            <strong>Product Image</strong>
          </div>
          <div style="width: 200px; height: 200px; background: #f0f0f0; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 14px; margin: 0 auto;">
            No Image Available
          </div>
        `;
      }

      const viewModal = document.getElementById('viewModal');
      if (viewModal) viewModal.style.display = 'block';
    });
  });

  const closeViewBtn = document.getElementById('closeViewBtn');
  if (closeViewBtn) closeViewBtn.addEventListener('click', () => {
    const viewModal = document.getElementById('viewModal');
    if (viewModal) viewModal.style.display = 'none';
  });

  // Delete handling
  document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-id');
      const deleteForm = document.getElementById('deleteForm');
      if (deleteForm) deleteForm.action = `/products/${id}`;
      const deleteModal = document.getElementById('deleteModal');
      if (deleteModal) deleteModal.style.display = 'block';
    });
  });

  const closeDeleteBtn = document.getElementById('closeDeleteBtn');
  const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
  if (closeDeleteBtn) closeDeleteBtn.addEventListener('click', () => { const m = document.getElementById('deleteModal'); if (m) m.style.display = 'none'; });
  if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', () => { const m = document.getElementById('deleteModal'); if (m) m.style.display = 'none'; });

  window.addEventListener('click', (e) => {
    const targets = ['addModal','viewModal','editModal','deleteModal'];
    targets.forEach(id => { const el = document.getElementById(id); if (el && e.target === el) el.style.display = 'none'; });
  });

  const backToTop = document.getElementById("backToTop");
  if (backToTop) {
    window.addEventListener("scroll", () => { backToTop.style.display = window.scrollY > 200 ? "block" : "none"; });
    backToTop.addEventListener("click", () => { window.scrollTo({ top: 0, behavior: "smooth" }); });
  }

  filterRows();
});