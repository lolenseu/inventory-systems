document.addEventListener('DOMContentLoaded', function(){
  const typeFilter = document.getElementById('typeFilter');
  const searchInput = document.getElementById('searchInput');

  function getRows(){ return document.querySelectorAll('tbody tr'); }

  function filterRows() {
    const selected = typeFilter ? typeFilter.value : 'all';
    const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
    getRows().forEach(row => {
      const skuCell = row.querySelector('.student-id-cell');
      const nameCell = row.querySelector('.name-cell');
      const typeCell = row.querySelector('td:first-child');
      if (!skuCell || !nameCell || !typeCell) return;
      const sku = skuCell.textContent.trim().toLowerCase();
      const name = nameCell.textContent.trim().toLowerCase();
      const type = typeCell.textContent.trim().toLowerCase();

      const matchesFilter = (selected === 'all' || type === selected);
      const matchesSearch = q === '' || sku.includes(q) || name.includes(q);
      row.style.display = (matchesFilter && matchesSearch) ? '' : 'none';
    });
  }

  if (typeFilter) typeFilter.addEventListener('change', filterRows);
  if (searchInput) {
    searchInput.addEventListener('input', filterRows);
    searchInput.addEventListener('keyup', (e) => { if (e.key === 'Enter') filterRows(); });
  }

  window.addEventListener('click', (e) => {
    const targets = ['addModal','viewModal','editModal'];
    targets.forEach(id => { const el = document.getElementById(id); if (el && e.target === el) el.style.display = 'none'; });
  });
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
