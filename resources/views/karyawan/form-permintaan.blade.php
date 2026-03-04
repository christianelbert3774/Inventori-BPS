@extends('layouts.app')

@section('title', 'Form Permintaan Pemakaian Barang')
@section('page-title', 'Permintaan Pemakaian Barang')

@section('content')

{{-- ── Breadcrumb ─────────────────────────────────────────── --}}
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bps-breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('karyawan.dashboard') }}" style="color:inherit;text-decoration:none;">Dashboard</a></li>
        <li class="breadcrumb-item active">Permintaan Pemakaian Barang</li>
    </ol>
</nav>

{{-- ── Page Header ─────────────────────────────────────────── --}}
<div class="page-header">
    <h1>Form Permintaan Pemakaian</h1>
    <p>Ajukan permintaan pemakaian barang inventori yang tersedia di gudang</p>
</div>

{{-- Data $daftarBarang dan $selectedBarangId dari PermintaanController::create() --}}

<div class="row g-4">
    <div class="col-lg-8">

        {{-- ── Form Card ──────────────────────────────────────── --}}
        <div class="bps-card">
            <div class="card-header-bps">
                <h5 class="card-title-bps">
                    <i class="bi bi-cart-plus-fill"></i>
                    Detail Permintaan Pemakaian
                </h5>
                <span style="font-size:.75rem;color:#94a3b8;">
                    <i class="bi bi-asterisk text-danger" style="font-size:.6rem;"></i>
                    Wajib diisi
                </span>
            </div>

            <div class="p-4">
                <form method="POST" action="{{ route('karyawan.form-permintaan.store') }}" id="form-permintaan">
                    @csrf

                    {{-- Info Pemohon --}}
                    <div class="row g-3 mb-4 pb-4" style="border-bottom:1px solid #f1f5f9;">
                        <div class="col-12">
                            <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:.8rem;font-weight:700;
                                color:var(--bps-blue);letter-spacing:.5px;text-transform:uppercase;margin-bottom:14px;">
                                <i class="bi bi-person-fill me-1"></i> Informasi Pemohon
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-bps">Nama Pemohon</label>
                            <input type="text" class="form-control form-control-bps"
                                value="{{ Auth::user()->name ?? 'Nama Karyawan' }}"
                                readonly style="background:#f8fafc;cursor:not-allowed;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-bps">Tanggal Pengajuan</label>
                            <input type="text" class="form-control form-control-bps"
                                value="{{ now()->translatedFormat('d F Y') }}"
                                readonly style="background:#f8fafc;cursor:not-allowed;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-bps">Bagian / Seksi <span class="text-danger">*</span></label>
                            <input type="text" name="bagian" class="form-control form-control-bps"
                                placeholder="Contoh: Seksi Distribusi"
                                value="{{ old('bagian') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-bps">Keperluan Umum <span class="text-danger">*</span></label>
                            <input type="text" name="keperluan" class="form-control form-control-bps"
                                placeholder="Contoh: Kebutuhan operasional bulanan"
                                value="{{ old('keperluan') }}" required>
                        </div>
                    </div>

                    {{-- Daftar Item --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:.8rem;font-weight:700;
                                color:var(--bps-blue);letter-spacing:.5px;text-transform:uppercase;">
                                <i class="bi bi-list-ul me-1"></i> Daftar Barang yang Diminta
                            </div>
                            <button type="button" class="btn btn-bps btn-sm" id="btn-tambah-barang" onclick="tambahItemBarang()">
                                <i class="bi bi-plus-circle-fill me-1"></i> Tambah Barang
                            </button>
                        </div>

                        {{-- Container item rows --}}
                        <div id="container-items">
                            {{-- Item pertama (default) --}}
                            <div class="item-row mb-3" id="item-row-1">
                                <span class="row-number">Barang #1</span>
                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <label class="form-label-bps">Nama Barang <span class="text-danger">*</span></label>
                                        <select name="items[0][barang_id]" class="form-select form-select-bps select-barang" required onchange="updateInfoBarang(this, 1)">
                                            <option value="">-- Pilih Barang --</option>
                                            @foreach ($daftarBarang as $b)
                                            <option value="{{ $b->id }}"
                                                data-stok="{{ $b->stok }}"
                                                data-satuan="{{ $b->satuan }}"
                                                {{ $selectedBarangId == $b->id ? 'selected' : '' }}>
                                                {{ $b->nama }} (Stok: {{ $b->stok }} {{ $b->satuan }})
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label-bps">Jumlah <span class="text-danger">*</span></label>
                                        <input type="number" name="items[0][jumlah]"
                                            class="form-control form-control-bps input-jumlah"
                                            placeholder="0" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label-bps">Satuan</label>
                                        <input type="text" class="form-control form-control-bps info-satuan"
                                            placeholder="-" readonly style="background:#f8fafc;">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger-soft w-100" onclick="hapusItem(1)" style="display:none;" id="hapus-1">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label-bps">Keterangan / Alasan</label>
                                        <input type="text" name="items[0][keterangan]"
                                            class="form-control form-control-bps"
                                            placeholder="Opsional — tuliskan alasan atau keperluan spesifik">
                                    </div>
                                    {{-- Info stok --}}
                                    <div class="col-12" id="info-stok-1" style="display:none;">
                                        <div class="d-flex align-items-center gap-2"
                                             style="background:#f0f7ff;border:1px solid #bfdbfe;border-radius:8px;padding:8px 12px;font-size:.8rem;color:var(--bps-blue);">
                                            <i class="bi bi-info-circle-fill"></i>
                                            <span id="teks-stok-1">Stok tersedia: -</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Pesan jumlah item --}}
                        <div id="info-total-item" style="font-size:.78rem;color:#64748b;text-align:right;margin-top:6px;">
                            Total: <strong id="count-item">1</strong> jenis barang
                        </div>
                    </div>

                    {{-- Catatan Tambahan --}}
                    <div class="mb-4">
                        <label class="form-label-bps">Catatan Tambahan</label>
                        <textarea name="catatan" class="form-control form-control-bps" rows="3"
                            placeholder="Catatan tambahan untuk Admin Gudang (opsional)">{{ old('catatan') }}</textarea>
                    </div>

                    {{-- Submit --}}
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('karyawan.dashboard') }}" class="btn btn-bps-outline">
                            <i class="bi bi-arrow-left me-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-bps">
                            <i class="bi bi-send-fill me-2"></i> Kirim Permintaan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- ── Sidebar Panduan ──────────────────────────────────── --}}
    <div class="col-lg-4 d-flex flex-column gap-4">

        {{-- Alur Permintaan --}}
        <div class="bps-card">
            <div class="card-header-bps">
                <h5 class="card-title-bps">
                    <i class="bi bi-diagram-3-fill"></i>
                    Alur Permintaan
                </h5>
            </div>
            <div class="p-4">
                @php
                $alur = [
                    ['icon'=>'bi-pencil-square','warna'=>'#e0edff','color'=>'var(--bps-blue)','judul'=>'Isi Form','desc'=>'Pilih barang dan isi jumlah yang dibutuhkan'],
                    ['icon'=>'bi-send-fill','warna'=>'#fef9c3','color'=>'#ca8a04','judul'=>'Kirim Permintaan','desc'=>'Permintaan dikirim ke Admin Gudang'],
                    ['icon'=>'bi-person-check-fill','warna'=>'#dcfce7','color'=>'#16a34a','judul'=>'Persetujuan','desc'=>'Admin Gudang meninjau dan memutuskan'],
                    ['icon'=>'bi-box-seam','warna'=>'#f3e8ff','color'=>'#7c3aed','judul'=>'Pengambilan','desc'=>'Barang siap diambil jika disetujui'],
                ];
                @endphp
                @foreach ($alur as $i => $step)
                <div class="d-flex gap-3 {{ !$loop->last ? 'mb-3' : '' }}">
                    <div style="display:flex;flex-direction:column;align-items:center;gap:0;">
                        <div style="width:36px;height:36px;border-radius:50%;background:{{ $step['warna'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi {{ $step['icon'] }}" style="color:{{ $step['color'] }};font-size:.9rem;"></i>
                        </div>
                        @if (!$loop->last)
                        <div style="width:2px;height:24px;background:#e2e8f0;margin-top:2px;"></div>
                        @endif
                    </div>
                    <div style="padding-top:6px;">
                        <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:.82rem;color:#1a2340;">{{ $step['judul'] }}</div>
                        <div style="font-size:.75rem;color:#64748b;margin-top:2px;">{{ $step['desc'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Peraturan --}}
        <div class="bps-card">
            <div class="card-header-bps">
                <h5 class="card-title-bps">
                    <i class="bi bi-shield-check-fill"></i>
                    Ketentuan
                </h5>
            </div>
            <div class="p-4">
                <ul style="padding-left:18px;margin:0;font-size:.82rem;color:#475569;line-height:1.8;">
                    <li>Permintaan hanya untuk barang yang tersedia di stok</li>
                    <li>Maksimal 10 jenis barang per pengajuan</li>
                    <li>Jumlah yang diminta tidak boleh melebihi stok tersedia</li>
                    <li>Permintaan dapat dibatalkan sebelum disetujui</li>
                    <li>Untuk barang habis, gunakan form Permintaan Pengadaan</li>
                </ul>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    // ── Counter & Template ──────────────────────────────────────
    let itemCount = 1;
    const MAX_ITEMS = 10;

    // Data barang (dari PHP → JS untuk validasi stok)
    const daftarBarang = @json($daftarBarang->map(fn($b) => [
        'id'     => $b->id,
        'nama'   => $b->nama,
        'stok'   => $b->stok,
        'satuan' => $b->satuan,
    ]));

    function tambahItemBarang() {
        if (itemCount >= MAX_ITEMS) {
            alert('Maksimal ' + MAX_ITEMS + ' jenis barang per permintaan.');
            return;
        }

        itemCount++;
        const idx  = itemCount - 1; // 0-based untuk nama field
        const rowId = itemCount;

        // Buat opsi barang
        let optionHtml = '<option value="">-- Pilih Barang --</option>';
        daftarBarang.forEach(b => {
            optionHtml += `<option value="${b.id}" data-stok="${b.stok}" data-satuan="${b.satuan}">
                ${b.nama} (Stok: ${b.stok} ${b.satuan})
            </option>`;
        });

        const rowHtml = `
        <div class="item-row mb-3" id="item-row-${rowId}">
            <span class="row-number">Barang #${rowId}</span>
            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <label class="form-label-bps">Nama Barang <span class="text-danger">*</span></label>
                    <select name="items[${idx}][barang_id]" class="form-select form-select-bps select-barang" required onchange="updateInfoBarang(this, ${rowId})">
                        ${optionHtml}
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label-bps">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" name="items[${idx}][jumlah]"
                        class="form-control form-control-bps input-jumlah"
                        placeholder="0" min="1" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label-bps">Satuan</label>
                    <input type="text" class="form-control form-control-bps info-satuan"
                        placeholder="-" readonly style="background:#f8fafc;">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger-soft w-100" onclick="hapusItem(${rowId})">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                </div>
                <div class="col-12">
                    <label class="form-label-bps">Keterangan / Alasan</label>
                    <input type="text" name="items[${idx}][keterangan]"
                        class="form-control form-control-bps"
                        placeholder="Opsional — tuliskan alasan atau keperluan spesifik">
                </div>
                <div class="col-12" id="info-stok-${rowId}" style="display:none;">
                    <div class="d-flex align-items-center gap-2"
                         style="background:#f0f7ff;border:1px solid #bfdbfe;border-radius:8px;padding:8px 12px;font-size:.8rem;color:var(--bps-blue);">
                        <i class="bi bi-info-circle-fill"></i>
                        <span id="teks-stok-${rowId}">Stok tersedia: -</span>
                    </div>
                </div>
            </div>
        </div>`;

        document.getElementById('container-items').insertAdjacentHTML('beforeend', rowHtml);

        // Tampilkan tombol hapus pada item pertama jika > 1 item
        if (itemCount > 1) {
            const hapusBtn1 = document.getElementById('hapus-1');
            if (hapusBtn1) hapusBtn1.style.display = '';
        }

        updateCounter();
    }

    function hapusItem(rowId) {
        const row = document.getElementById('item-row-' + rowId);
        if (row) row.remove();

        // Hitung ulang jumlah item aktif
        const rows = document.querySelectorAll('#container-items .item-row');
        itemCount = rows.length;

        // Sembunyikan tombol hapus jika hanya 1 item
        if (rows.length === 1) {
            const allHapusBtns = document.querySelectorAll('.btn-danger-soft');
            allHapusBtns.forEach(b => b.style.display = 'none');
        }

        // Perbarui label nomor
        rows.forEach((r, i) => {
            const label = r.querySelector('.row-number');
            if (label) label.textContent = 'Barang #' + (i + 1);
        });

        updateCounter();
    }

    function updateInfoBarang(selectEl, rowId) {
        const opt    = selectEl.selectedOptions[0];
        const stok   = opt ? opt.dataset.stok   : '';
        const satuan = opt ? opt.dataset.satuan : '';
        const row    = document.getElementById('item-row-' + rowId);

        if (row) {
            const satuanInput = row.querySelector('.info-satuan');
            if (satuanInput) satuanInput.value = satuan || '';

            const infoBox = document.getElementById('info-stok-' + rowId);
            const infoTxt = document.getElementById('teks-stok-' + rowId);
            if (infoBox && infoTxt && stok !== '') {
                infoTxt.textContent = 'Stok tersedia: ' + stok + ' ' + satuan;
                infoBox.style.display = '';
            } else if (infoBox) {
                infoBox.style.display = 'none';
            }

            // Set max jumlah
            const jumlahInput = row.querySelector('.input-jumlah');
            if (jumlahInput && stok) jumlahInput.max = stok;
        }
    }

    function updateCounter() {
        const rows = document.querySelectorAll('#container-items .item-row');
        document.getElementById('count-item').textContent = rows.length;
    }

    // Init: update info jika ada pre-selected (dari query param)
    document.addEventListener('DOMContentLoaded', () => {
        const firstSelect = document.querySelector('#item-row-1 .select-barang');
        if (firstSelect && firstSelect.value) {
            updateInfoBarang(firstSelect, 1);
        }
    });
</script>
@endpush
