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
        <select class="form-control" name="barang_id[]" required>
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
}

function removeBarang(id) {
  const el = document.getElementById('barang-row-' + id);
  if (el) el.remove();
}

// ── PENGADAAN: Toggle between restock vs barang baru ──
function setPengadaanType(type) {
  const btnRestock = document.getElementById('btn-type-restock');
  const btnBaru    = document.getElementById('btn-type-baru');
  const formRestock = document.getElementById('form-restock');
  const formBaru    = document.getElementById('form-baru');
  const inputType   = document.getElementById('pengadaan-type-input');

  if (!btnRestock) return;

  if (type === 'restock') {
    btnRestock.className = 'type-btn active';
    btnBaru.className    = 'type-btn';
    formRestock.style.display = 'block';
    formBaru.style.display    = 'none';
    if (inputType) inputType.value = 'restock';
    // required toggle
    formRestock.querySelectorAll('[data-required]').forEach(el => el.required = true);
    formBaru.querySelectorAll('[data-required]').forEach(el => el.required = false);
  } else {
    btnBaru.className    = 'type-btn active-orange';
    btnRestock.className = 'type-btn';
    formRestock.style.display = 'none';
    formBaru.style.display    = 'block';
    if (inputType) inputType.value = 'baru';
    formRestock.querySelectorAll('[data-required]').forEach(el => el.required = false);
    formBaru.querySelectorAll('[data-required]').forEach(el => el.required = true);
  }
}

// Init on DOM load
document.addEventListener('DOMContentLoaded', function () {
  // Default pengadaan type
  const typeInput = document.getElementById('pengadaan-type-input');
  if (typeInput) {
    setPengadaanType(typeInput.value || 'restock');
  }

  // Auto-dismiss alerts after 5s
  document.querySelectorAll('.alert').forEach(function(alert) {
    setTimeout(function() {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity .4s';
      setTimeout(function() { alert.remove(); }, 400);
    }, 5000);
  });
});
