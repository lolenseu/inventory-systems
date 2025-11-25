document.addEventListener('DOMContentLoaded', function(){
  const statusFilter = document.getElementById('statusFilter');
  const searchInput = document.getElementById('searchInput');
  const printOrdersCv = document.getElementById('printOrdersCv');

  function getRows(){ return document.querySelectorAll('.orders-container tbody tr'); }

  function filterRows() {
    const selected = statusFilter ? statusFilter.value : 'all';
    const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
    getRows().forEach(row => {
      const orderIdCell = row.querySelector('.order-id-cell');
      const customerCell = row.querySelector('.customer-cell');
      const productCell = row.querySelector('.products-cell');
      const statusCell = row.querySelector('.status-cell');
      
      if (!orderIdCell || !customerCell || !productCell || !statusCell) return;
      
      const orderId = orderIdCell.textContent.trim().toLowerCase();
      const customer = customerCell.textContent.trim().toLowerCase();
      const product = productCell.textContent.trim().toLowerCase();
      const status = statusCell.textContent.trim().toLowerCase();

      // Determine status category for filtering
      const statusCategory = status.toLowerCase();

      const matchesFilter = (selected === 'all' || statusCategory === selected);
      const matchesSearch = q === '' || 
                           orderId.includes(q) || 
                           customer.includes(q) || 
                           product.includes(q);
      
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
      const orderId = row.querySelector('td:nth-child(1)')?.textContent.trim() || '';
      const customer = row.querySelector('td:nth-child(2)')?.textContent.trim() || '';
      const product = row.querySelector('td:nth-child(3)')?.textContent.trim() || '';
      const price = row.querySelector('td:nth-child(4)')?.textContent.trim() || '';
      const status = row.querySelector('td:nth-child(5)')?.textContent.trim() || '';
      const date = row.querySelector('td:nth-child(6)')?.textContent.trim() || '';

      rowsHtml += `<tr>
        <td style="padding:8px;border:1px solid #ccc;text-align:center">${orderId}</td>
        <td style="padding:8px;border:1px solid #ccc;text-align:left">${customer}</td>
        <td style="padding:8px;border:1px solid #ccc;text-align:left">${product}</td>
        <td style="padding:8px;border:1px solid #ccc;text-align:right">${price}</td>
        <td style="padding:8px;border:1px solid #ccc;text-align:center">${status}</td>
        <td style="padding:8px;border:1px solid #ccc;text-align:center">${date}</td>
      </tr>`;
    });
    return `<!doctype html>
      <html>
        <head>
          <meta charset="utf-8">
          <title>Orders List</title>
          <style>
            body{font-family:Arial,sans-serif;color:#222;padding:15px;font-size:12px;}
            table{border-collapse:collapse;width:100%;font-size:11px;}
            th{background:#f4f4f4;padding:8px;border:1px solid #ccc;text-align:center;font-weight:bold;}
            td{padding:6px;border:1px solid #ccc;white-space:pre-line;}
            h2{text-align:center;margin-bottom:15px;font-size:16px;}
          </style>
        </head>
        <body>
          <h2>Orders Management</h2>
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
              ${rowsHtml || '<tr><td colspan="6" style="padding:8px;border:1px solid #ccc;text-align:center">No records found</td></tr>'}
            </tbody>
          </table>
        </body>
      </html>`;
  }

  if (printOrdersCv) {
    printOrdersCv.addEventListener('click', function(){
      const html = buildPrintableHTML();
      const w = window.open('', '_blank');
      w.document.write(html);
      w.document.close();
      w.focus();
      setTimeout(() => { w.print(); w.close(); }, 300);
    });
  }

  // Order Modal Handlers
  const addOrderModal = document.getElementById('addOrderModal');
  const openAddOrderModalBtn = document.getElementById('openAddOrderModalBtn');
  const closeAddOrderModalBtn = document.getElementById('closeAddOrderModalBtn');
  
  const viewOrderModal = document.getElementById('viewOrderModal');
  const closeViewOrderBtn = document.getElementById('closeViewOrderBtn');
  
  const editOrderModal = document.getElementById('editOrderModal');
  const closeEditOrderBtn = document.getElementById('closeEditOrderBtn');
  
  const deleteOrderModal = document.getElementById('deleteOrderModal');
  const closeDeleteOrderBtn = document.getElementById('closeDeleteOrderBtn');
  const cancelDeleteOrderBtn = document.getElementById('cancelDeleteOrderBtn');

  // Open Add Order Modal
  if (openAddOrderModalBtn) {
    openAddOrderModalBtn.addEventListener('click', () => {
      addOrderModal.style.display = 'block';
      // Reset form
      document.getElementById('addOrderForm').reset();
      document.getElementById('addUnitPrice').value = '0.00';
      document.getElementById('addTotalPrice').value = '0.00';
    });
  }

  // Close Add Order Modal
  if (closeAddOrderModalBtn) {
    closeAddOrderModalBtn.addEventListener('click', () => {
      addOrderModal.style.display = 'none';
    });
  }

  // Product selection and price calculation for Add Order
  const addProductId = document.getElementById('addProductId');
  const addQuantity = document.getElementById('addQuantity');
  const addUnitPrice = document.getElementById('addUnitPrice');
  const addTotalPrice = document.getElementById('addTotalPrice');

  if (addProductId) {
    addProductId.addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      const price = selectedOption ? parseFloat(selectedOption.getAttribute('data-price')) || 0 : 0;
      addUnitPrice.value = price.toFixed(2);
      calculateAddTotal();
    });
  }

  if (addQuantity) {
    addQuantity.addEventListener('input', calculateAddTotal);
  }

  function calculateAddTotal() {
    const unitPrice = parseFloat(addUnitPrice.value) || 0;
    const quantity = parseInt(addQuantity.value) || 0;
    const total = unitPrice * quantity;
    addTotalPrice.value = total.toFixed(2);
  }

  // View Order Handler
  document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const viewOrderNumber = document.getElementById('viewOrderNumber');
      const viewCustomer = document.getElementById('viewCustomer');
      const viewCustomerEmail = document.getElementById('viewCustomerEmail');
      const viewProducts = document.getElementById('viewProducts');
      const viewTotal = document.getElementById('viewTotal');
      const viewStatus = document.getElementById('viewStatus');
      const viewDate = document.getElementById('viewDate');
      const viewNotes = document.getElementById('viewNotes');

      if (viewOrderNumber) viewOrderNumber.textContent = btn.getAttribute('data-order-number') || '';
      if (viewCustomer) viewCustomer.textContent = btn.getAttribute('data-customer') || '';
      if (viewCustomerEmail) viewCustomerEmail.textContent = btn.getAttribute('data-customer-email') || '';
      if (viewTotal) viewTotal.textContent = '₱' + (parseFloat(btn.getAttribute('data-total')) || 0).toFixed(2);
      if (viewStatus) viewStatus.textContent = btn.getAttribute('data-status') || '';
      if (viewDate) viewDate.textContent = btn.getAttribute('data-created') || '';
      if (viewNotes) viewNotes.textContent = btn.getAttribute('data-notes') || 'No notes';

      // Display products with quantities
      if (viewProducts) {
        const itemsData = btn.getAttribute('data-items');
        if (!itemsData) {
          viewProducts.innerHTML = '<div class="no-products">No products found</div>';
          return;
        }
        
        try {
          const items = JSON.parse(itemsData);
          viewProducts.innerHTML = '';
          items.forEach(item => {
            const productDiv = document.createElement('div');
            productDiv.className = 'product-detail-item';
            productDiv.innerHTML = `
              <span class="product-name">${item.product_name || 'N/A'}</span>
              <span class="product-quantity">× ${item.quantity}</span>
              <span class="product-price">₱${(item.price || 0).toFixed(2)}</span>
              <span class="product-subtotal">= ₱${(item.subtotal || 0).toFixed(2)}</span>
            `;
            viewProducts.appendChild(productDiv);
          });
        } catch (e) {
          console.error('Error parsing items data:', e);
          viewProducts.innerHTML = '<div class="no-products">Error loading products</div>';
        }
      }

      // Show the modal
      const viewOrderModal = document.getElementById('viewOrderModal');
      if (viewOrderModal) viewOrderModal.style.display = 'block';
    });
  });

  if (closeViewOrderBtn) {
    closeViewOrderBtn.addEventListener('click', () => {
      viewOrderModal.style.display = 'none';
    });
  }

  // Edit Order Handler
  const editOrderForm = document.getElementById('editOrderForm');
  document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-id');
      if (editOrderForm) editOrderForm.action = `/orders/${id}`;

      const editCustomerId = document.getElementById('editCustomerId');
      const editProductId = document.getElementById('editProductId');
      const editQuantity = document.getElementById('editQuantity');
      const editUnitPrice = document.getElementById('editUnitPrice');
      const editTotalPrice = document.getElementById('editTotalPrice');
      const editStatus = document.getElementById('editStatus');
      const editNotes = document.getElementById('editNotes');

      if (editCustomerId) editCustomerId.value = btn.getAttribute('data-customer-id') || '';
      if (editProductId) editProductId.value = btn.getAttribute('data-product-id') || '';
      if (editQuantity) editQuantity.value = btn.getAttribute('data-quantity') || 1;
      if (editStatus) editStatus.value = btn.getAttribute('data-status') || 'pending';
      if (editNotes) editNotes.value = btn.getAttribute('data-notes') || '';

      // Calculate unit price and total
      const selectedOption = editProductId.options[editProductId.selectedIndex];
      const price = selectedOption ? parseFloat(selectedOption.getAttribute('data-price')) || 0 : 0;
      editUnitPrice.value = price.toFixed(2);
      editTotalPrice.value = (price * (parseInt(editQuantity.value) || 1)).toFixed(2);

      editOrderModal.style.display = 'block';
    });
  });

  // Product selection and price calculation for Edit Order
  const editProductId = document.getElementById('editProductId');
  const editQuantityEdit = document.getElementById('editQuantity');
  const editUnitPriceEdit = document.getElementById('editUnitPrice');
  const editTotalPriceEdit = document.getElementById('editTotalPrice');

  if (editProductId) {
    editProductId.addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];
      const price = selectedOption ? parseFloat(selectedOption.getAttribute('data-price')) || 0 : 0;
      editUnitPriceEdit.value = price.toFixed(2);
      calculateEditTotal();
    });
  }

  if (editQuantityEdit) {
    editQuantityEdit.addEventListener('input', calculateEditTotal);
  }

  function calculateEditTotal() {
    const unitPrice = parseFloat(editUnitPriceEdit.value) || 0;
    const quantity = parseInt(editQuantityEdit.value) || 0;
    const total = unitPrice * quantity;
    editTotalPriceEdit.value = total.toFixed(2);
  }

  if (closeEditOrderBtn) {
    closeEditOrderBtn.addEventListener('click', () => {
      editOrderModal.style.display = 'none';
    });
  }

  // Modal click outside close
  window.addEventListener('click', (e) => {
    const targets = ['addOrderModal','viewOrderModal','editOrderModal','deleteOrderModal'];
    targets.forEach(id => { 
      const el = document.getElementById(id); 
      if (el && e.target === el) el.style.display = 'none'; 
    });
  });

  // Back to top button
  const backToTop = document.getElementById("backToTop");
  if (backToTop) {
    window.addEventListener("scroll", () => { 
      backToTop.style.display = window.scrollY > 200 ? "block" : "none"; 
    });
    backToTop.addEventListener("click", () => { 
      window.scrollTo({ top: 0, behavior: "smooth" }); 
    });
  }

  filterRows();
});