document.addEventListener('DOMContentLoaded', function(){
  const yearFilter = document.getElementById('yearFilter');
  const monthFilter = document.getElementById('monthFilter');
  const searchInput = document.getElementById('searchInput');
  const printReportsCv = document.getElementById('printReportsCv');
  const refreshBtn = document.getElementById('refreshReportsBtn');

  const monthNamesFull = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
  const monthNamesShort = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

  function getMonthNum(monthStr) {
    const lower = monthStr.toLowerCase().trim();
    const indexFull = monthNamesFull.findIndex(name => name.startsWith(lower));
    if (indexFull !== -1) return indexFull + 1;
    const indexShort = monthNamesShort.findIndex(name => name.startsWith(lower));
    if (indexShort !== -1) return indexShort + 1;
    return 0;
  }

  function getRowsMonthly() { return document.querySelectorAll('#reportsTableBody tr'); }
  function getRowsOrders() { return document.querySelectorAll('#ordersTableBody tr'); }

  function filterRows() {
    const selectedYear = yearFilter ? yearFilter.value : '';
    const selectedMonth = monthFilter ? monthFilter.value : 'all';
    const q = (searchInput ? searchInput.value.trim() : '').toLowerCase();

    // Filter monthly reports table
    getRowsMonthly().forEach(row => {
      const monthCell = row.querySelector('.month-cell');
      const allText = row.textContent.toLowerCase();

      if (!monthCell) return;

      const monthText = monthCell.textContent.trim();
      const parts = monthText.split(/\s+/);
      const rowMonthName = parts[0].toLowerCase();
      const rowYear = parts[parts.length - 1];

      const rowMonthNum = getMonthNum(rowMonthName);

      const matchesYear = !selectedYear || rowYear === selectedYear;
      const matchesMonth = selectedMonth === 'all' || rowMonthNum.toString() === selectedMonth;
      const matchesSearch = q === '' || allText.includes(q);

      row.style.display = (matchesYear && matchesMonth && matchesSearch) ? '' : 'none';
    });

    // Filter recent orders table (by date)
    getRowsOrders().forEach(row => {
      const dateCell = row.querySelector('.date-cell');
      const customerCell = row.querySelector('.customer-cell');
      const productCell = row.querySelector('.products-cell');
      const orderIdCell = row.querySelector('.order-id-cell');
      const statusCell = row.querySelector('.status-cell');

      if (!dateCell) return;

      const dateText = dateCell.textContent.trim().toLowerCase();
      const monthMatch = dateText.match(/^(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)/i);
      const yearMatch = dateText.match(/20\d{2}/i);
      const rowMonthAbbr = monthMatch ? monthMatch[1].toLowerCase() : '';
      const rowYear = yearMatch ? yearMatch[0] : '';

      const rowMonthNum = getMonthNum(rowMonthAbbr);
      const allText = row.textContent.toLowerCase();

      const matchesYear = !selectedYear || rowYear === selectedYear;
      const matchesMonth = selectedMonth === 'all' || rowMonthNum.toString() === selectedMonth;
      const matchesSearch = q === '' || 
                           (orderIdCell?.textContent.toLowerCase().includes(q) || '') ||
                           (customerCell?.textContent.toLowerCase().includes(q) || '') ||
                           (productCell?.textContent.toLowerCase().includes(q) || '');

      row.style.display = (matchesYear && matchesMonth && matchesSearch) ? '' : 'none';
    });
  }

  // Event listeners for filters
  if (yearFilter) yearFilter.addEventListener('change', filterRows);
  if (monthFilter) monthFilter.addEventListener('change', filterRows);
  if (searchInput) {
    searchInput.addEventListener('input', filterRows);
    searchInput.addEventListener('keyup', (e) => { if (e.key === 'Enter') filterRows(); });
  }
  if (refreshBtn) {
    refreshBtn.addEventListener('click', filterRows);
  }

  // Print functionality for monthly table
  function buildPrintableHTML() {
    let rowsHtml = '';
    getRowsMonthly().forEach(row => {
      if (window.getComputedStyle(row).display !== 'none') {
        const cells = Array.from(row.querySelectorAll('td'));
        if (cells.length >= 6) {
          rowsHtml += `<tr>`;
          cells.forEach((cell, index) => {
            let textAlign = 'left';
            if (index === 1 || index === 2) textAlign = 'right'; // Orders Count, Total Sales
            if (index === 3) textAlign = 'right'; // Avg
            rowsHtml += `<td style="padding:8px;border:1px solid #ccc;text-align:${textAlign};white-space:pre-line">${cell.textContent.trim()}</td>`;
          });
          rowsHtml += `</tr>`;
        }
      }
    });

    const totalOrders = document.getElementById('totalOrders')?.textContent || '0';
    const totalSales = document.getElementById('totalSales')?.textContent || '0';
    const avgOrderValue = document.getElementById('avgOrderValue')?.textContent || '0';
    const pendingOrders = document.getElementById('pendingOrders')?.textContent || '0';

    return `<!doctype html>
      <html>
        <head>
          <meta charset="utf-8">
          <title>Sales Reports</title>
          <style>
            body { font-family: Arial, sans-serif; color: #222; padding: 15px; font-size: 12px; }
            .summary-print { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; font-size: 14px; }
            .summary-print div { background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center; }
            table { border-collapse: collapse; width: 100%; font-size: 11px; }
            th { background: #505050; color: #fff; padding: 8px; border: 1px solid #ccc; text-align: center; font-weight: bold; }
            td { padding: 8px; border: 1px solid #ccc; }
            h2 { text-align: center; margin-bottom: 15px; font-size: 18px; }
          </style>
        </head>
        <body>
          <h2>Monthly Sales Report</h2>
          <div class="summary-print">
            <div><strong>Total Orders:</strong> ${totalOrders}</div>
            <div><strong>Total Sales:</strong> ${totalSales}</div>
            <div><strong>Avg Order Value:</strong> ${avgOrderValue}</div>
            <div><strong>Pending Orders:</strong> ${pendingOrders}</div>
          </div>
          <table>
            <thead>
              <tr>
                <th>Month</th>
                <th>Delivered</th>
                <th>Total Sales</th>
                <th>Avg Order Value</th>
                <th>Top Product</th>
                <th>Best Customer</th>
              </tr>
            </thead>
            <tbody>
              ${rowsHtml || '<tr><td colspan="6" style="padding:8px;border:1px solid #ccc;text-align:center">No records found</td></tr>'}
            </tbody>
          </table>
        </body>
      </html>`;
  }

  if (printReportsCv) {
    printReportsCv.addEventListener('click', function() {
      const html = buildPrintableHTML();
      const w = window.open('', '_blank');
      w.document.write(html);
      w.document.close();
      w.focus();
      setTimeout(() => { w.print(); w.close(); }, 300);
    });
  }

  // Chart.js initialization
  const chartCtx = document.getElementById('monthlySalesChart');
  if (chartCtx) {
    const labels = [];
    const salesData = [];
    getRowsMonthly().forEach(row => {
      const monthCell = row.querySelector('.month-cell');
      const salesCell = row.querySelector('.sales-cell');
      if (monthCell && salesCell) {
        labels.push(monthCell.textContent.trim());
        const salesNum = parseFloat(salesCell.textContent.replace(/[₱,]/g, '')) || 0;
        salesData.push(salesNum);
      }
    });

    new Chart(chartCtx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Total Sales (₱)',
          data: salesData,
          borderColor: '#3498db',
          backgroundColor: 'rgba(52, 152, 219, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return '₱' + value.toLocaleString();
              }
            }
          }
        },
        plugins: {
          legend: {
            display: true,
            position: 'top'
          }
        }
      }
    });
  }

  if (refreshBtn) {
    refreshBtn.addEventListener('click', () => {
      window.location.reload();
    });
  }

  // Back to top button
  const backToTop = document.getElementById('backToTop');
  if (backToTop) {
    window.addEventListener('scroll', () => {
      backToTop.style.display = (window.scrollY > 200) ? 'block' : 'none';
    });
    backToTop.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // Initial filter
  filterRows();
});