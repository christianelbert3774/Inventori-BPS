// SIBAS — Main JavaScript

// Toggle password visibility
function togglePassword(inputId, iconEl) {
  const input = document.getElementById(inputId);
  if (!input) return;
  if (input.type === 'password') {
    input.type = 'text';
    iconEl.className = 'bi bi-eye-slash eye-toggle';
  } else {
    input.type = 'password';
    iconEl.className = 'bi bi-eye eye-toggle';
  }
}

// ── PEMAKAIAN: Dynamic barang rows ──
let barangCount = 1;

function addBarang() {
  barangCount++;
  const list = document.getElementById('barang-list');
  if (!list) return;

  // Clone dari barangOptions yang sudah dirender via blade
  const options = document.getElementById('barang-options-template')?.innerHTML || '';

  const div = document.createElement('div');
  div.className = 'barang-item';
  div.id = 'barang-row-' + barangCount;
  div.innerHTML = `
    <div class="barang-item-header">
      <span class="barang-item-num">Barang #${barangCount}</span>
      <button type="button" class="btn-remove-barang" onclick="removeBarang(${barangCount})">
        <i class="bi bi-trash3"></i>
      </button>
    </div>
    <div class="form-grid-2">
      <div class="form-group">
        <label>Pilih Barang <span class="req">*</span></label>
        <select class="form-control" name="barang_id[]" required data-custom-select>
          <option value="">-- Pilih Barang --</option>
          ${options}
        </select>
      </div>
      <div class="form-group">
        <label>Jumlah <span class="req">*</span></label>
        <input class="form-control" type="number" name="jumlah[]" min="1" placeholder="Masukkan jumlah" required/>
      </div>
    </div>
  `;
  list.appendChild(div);

  // Init custom select on the new select element
  const newSelect = div.querySelector('select[data-custom-select]');
  if (newSelect) initCustomSelect(newSelect);
}

function removeBarang(id) {
  const el = document.getElementById('barang-row-' + id);
  if (el) el.remove();
}

// ── PENGADAAN: Toggle between restock vs barang baru ──
function setPengadaanType(type) {
  const btnRestock = document.getElementById('btn-type-restock');
  const btnBaru = document.getElementById('btn-type-baru');
  const formRestock = document.getElementById('form-restock');
  const formBaru = document.getElementById('form-baru');
  const inputType = document.getElementById('pengadaan-type-input');

  if (!btnRestock) return;

  if (type === 'restock') {
    btnRestock.className = 'type-btn active';
    btnBaru.className = 'type-btn';
    formRestock.style.display = 'block';
    formBaru.style.display = 'none';
    if (inputType) inputType.value = 'restock';
    formRestock.querySelectorAll('[data-required]').forEach(el => el.required = true);
    formBaru.querySelectorAll('[data-required]').forEach(el => el.required = false);
  } else {
    btnBaru.className = 'type-btn active-orange';
    btnRestock.className = 'type-btn';
    formRestock.style.display = 'none';
    formBaru.style.display = 'block';
    if (inputType) inputType.value = 'baru';
    formRestock.querySelectorAll('[data-required]').forEach(el => el.required = false);
    formBaru.querySelectorAll('[data-required]').forEach(el => el.required = true);
  }
}

// ── CUSTOM SEARCHABLE SELECT ──────────────────────────────────────────
function initCustomSelect(selectEl) {
  if (!selectEl || selectEl._customSelectInit) return;
  selectEl._customSelectInit = true;
  selectEl.style.display = 'none';

  const options = Array.from(selectEl.options).slice(1);
  const placeholder = selectEl.options[0]?.text || '-- Pilih Barang --';

  const wrap = document.createElement('div');
  wrap.className = 'custom-select-wrap';
  selectEl.parentNode.insertBefore(wrap, selectEl);
  wrap.appendChild(selectEl);

  const trigger = document.createElement('div');
  trigger.className = 'custom-select-trigger';
  trigger.setAttribute('tabindex', '0');
  trigger.setAttribute('role', 'combobox');
  trigger.innerHTML = `<span class="cs-placeholder">${placeholder}</span>`;
  wrap.insertBefore(trigger, selectEl);

  const dropdown = document.createElement('div');
  dropdown.className = 'custom-select-dropdown';
  dropdown.innerHTML = `
    <div class="cs-search-wrap">
      <i class="bi bi-search"></i>
      <input type="text" class="cs-search-input" placeholder="Cari barang...">
    </div>
    <div class="cs-options-list"></div>
    <div class="cs-count"></div>
  `;
  wrap.appendChild(dropdown);

  const searchInput = dropdown.querySelector('.cs-search-input');
  const optionsList = dropdown.querySelector('.cs-options-list');
  const countEl = dropdown.querySelector('.cs-count');

  function getStockClass(stok) {
    const n = parseInt(stok, 10);
    if (n <= 0) return 'stock-empty';
    if (n <= 10) return 'stock-low';
    return 'stock-ok';
  }
  function getStockIcon(stok) {
    const n = parseInt(stok, 10);
    if (n <= 0) return 'bi-x-circle-fill';
    if (n <= 10) return 'bi-exclamation-triangle-fill';
    return 'bi-box-seam-fill';
  }

  options.forEach(opt => {
    const stok = opt.dataset.stok !== undefined ? opt.dataset.stok : (opt.text.match(/\d+/) || ['?'])[0];
    const satuan = opt.dataset.satuan !== undefined ? opt.dataset.satuan : (opt.text.split(' ').pop() || '');
    const nama = opt.dataset.nama !== undefined ? opt.dataset.nama : opt.text.split('—')[0].trim().split('(')[0].trim();
    const isDisabled = opt.disabled;
    const sc = getStockClass(stok);

    const item = document.createElement('div');
    item.className = 'cs-option' + (isDisabled ? ' disabled' : '');
    item.dataset.value = opt.value;
    item.dataset.nama = nama;
    item.dataset.stok = stok;
    item.dataset.satuan = satuan;
    item.innerHTML = `
      <div class="cs-option-info">
        <div class="cs-option-name">${nama}</div>
        <div class="cs-option-meta">${satuan || ''}</div>
      </div>
      <span class="cs-option-stock ${sc}">
        <i class="bi ${getStockIcon(stok)}"></i>
        ${stok} ${satuan}
      </span>
    `;
    if (!isDisabled) {
      item.addEventListener('click', () => selectOption(item));
    }
    optionsList.appendChild(item);
  });

  updateCount();

  function selectOption(item) {
    optionsList.querySelectorAll('.cs-option.selected').forEach(el => el.classList.remove('selected'));
    item.classList.add('selected');

    const val = item.dataset.value;
    const nama = item.dataset.nama;
    const stok = item.dataset.stok;
    const satuan = item.dataset.satuan;
    const sc = getStockClass(stok);

    selectEl.value = val;
    selectEl.dispatchEvent(new Event('change', { bubbles: true }));

    trigger.innerHTML = `
      <div class="cs-selected-item">
        <span class="cs-selected-name">${nama}</span>
        <span class="cs-selected-badge ${sc}">${stok} ${satuan}</span>
      </div>
    `;

    closeDropdown();
  }

  searchInput.addEventListener('input', () => {
    const q = searchInput.value.toLowerCase().trim();
    let visible = 0;
    optionsList.querySelectorAll('.cs-option').forEach(item => {
      const name = (item.dataset.nama || '').toLowerCase();
      if (q === '' || name.includes(q)) {
        item.classList.remove('hidden');
        visible++;
      } else {
        item.classList.add('hidden');
      }
    });

    let emptyEl = optionsList.querySelector('.cs-empty');
    if (visible === 0) {
      if (!emptyEl) {
        emptyEl = document.createElement('div');
        emptyEl.className = 'cs-empty';
        optionsList.appendChild(emptyEl);
      }
      emptyEl.innerHTML = `<i class="bi bi-search"></i>Barang "<strong>${searchInput.value}</strong>" tidak ditemukan`;
    } else {
      if (emptyEl) emptyEl.remove();
    }

    updateCount();
  });

  function updateCount() {
    const total = optionsList.querySelectorAll('.cs-option:not(.hidden)').length;
    const enabled = optionsList.querySelectorAll('.cs-option:not(.hidden):not(.disabled)').length;
    countEl.textContent = `${total} barang · ${enabled} dapat dipilih`;
  }

  function openDropdown() {
    document.querySelectorAll('.custom-select-wrap.open').forEach(w => {
      if (w !== wrap) w.classList.remove('open');
    });
    wrap.classList.add('open');
    searchInput.value = '';
    optionsList.querySelectorAll('.cs-option.hidden').forEach(el => el.classList.remove('hidden'));
    const emptyEl = optionsList.querySelector('.cs-empty');
    if (emptyEl) emptyEl.remove();
    updateCount();
    setTimeout(() => searchInput.focus(), 50);
  }

  function closeDropdown() {
    wrap.classList.remove('open');
  }

  trigger.addEventListener('click', e => {
    e.stopPropagation();
    wrap.classList.contains('open') ? closeDropdown() : openDropdown();
  });

  trigger.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openDropdown(); }
    if (e.key === 'Escape') closeDropdown();
  });

  searchInput.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeDropdown();
  });

  dropdown.addEventListener('click', e => e.stopPropagation());
  document.addEventListener('click', () => closeDropdown());
}

// ── Init on DOM load ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  // Default pengadaan type
  const typeInput = document.getElementById('pengadaan-type-input');
  if (typeInput) {
    setPengadaanType(typeInput.value || 'restock');
  }

  // Init all custom selects
  document.querySelectorAll('select[data-custom-select]').forEach(sel => {
    initCustomSelect(sel);
  });

  // Auto-dismiss alerts after 5s
  document.querySelectorAll('.alert').forEach(function (alert) {
    setTimeout(function () {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity .4s';
      setTimeout(function () { alert.remove(); }, 400);
    }, 5000);
  });
});
