@extends('layouts.pbj')

@section('title', 'Selesaikan Pengadaan - PBJ')

@section('topbar-title')
  Selesaikan <span>Pengadaan</span>
@endsection

@section('content')

  <div class="page-header">
    <div class="breadcrumb">
      <span>SIBAS</span><span class="sep">/</span>
      <a href="{{ route('pbj.pengadaan.index') }}" style="color:inherit;text-decoration:none;">Pengadaan PBJ</a>
      <span class="sep">/</span>
      <span class="current">Detail #{{ $pengadaan->id }}</span>
    </div>
    <h2>Selesaikan Pengadaan #{{ $pengadaan->id }}</h2>
    <p>Isi jumlah barang yang berhasil dibeli, upload foto bukti, lalu klik "Selesaikan".</p>
  </div>

  @if(session('error'))
    <div style="margin-bottom:16px;padding:12px 16px;background:#FEE2E2;border-radius:8px;color:#991B1B;border-left:4px solid #DC2626;">
      <i class="bi bi-x-circle-fill"></i> {{ session('error') }}
    </div>
  @endif

  @if($errors->any())
    <div style="margin-bottom:16px;padding:12px 16px;background:#FEE2E2;border-radius:8px;color:#991B1B;border-left:4px solid #DC2626;">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <ul style="margin:4px 0 0 16px;padding:0;">
        @foreach($errors->all() as $err)
          <li style="font-size:13px;">{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

    {{-- KIRI: Info Pemohon + Form Barang --}}
    <div>
      {{-- Info Pemohon --}}
      <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
          <h3><i class="bi bi-person-circle" style="color:#0055A5;margin-right:8px;"></i>Informasi Pemohon</h3>
        </div>
        <div style="padding:20px 24px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div>
            <div class="detail-label">Nama Lengkap</div>
            <div class="detail-value">{{ $pengadaan->user->name ?? '-' }}</div>
          </div>
          <div>
            <div class="detail-label">Tanggal Pengajuan</div>
            <div class="detail-value">{{ $pengadaan->created_at->format('d/m/Y H:i') }}</div>
          </div>
          <div>
            <div class="detail-label">Bagian / Divisi</div>
            <div class="detail-value">{{ $pengadaan->user->bagian ?? '-' }}</div>
          </div>        
          <div>
            <div class="detail-label">Status PBJ</div>
            <div class="detail-value">
              @php
                $s3 = ['pending'=>['Menunggu PBJ','#D97706'], 'completed'=>['Selesai','#059669'], 'rejected'=>['Ditolak','#DC2626']];
                [$lbl3, $clr3] = $s3[$pengadaan->status_level3] ?? ['-','#94A3B8'];
              @endphp
              <span style="font-weight:700;color:{{ $clr3 }};">{{ $lbl3 }}</span>
            </div>
          </div>
        </div>
      </div>

      {{-- Form Barang --}}
      <div class="card">
        <div class="card-header">
          <h3><i class="bi bi-bag-plus" style="color:#0055A5;margin-right:8px;"></i>Daftar Barang yang Diminta</h3>
        </div>

        @if($pengadaan->status_level3 === 'pending')
          <form method="POST" action="{{ route('pbj.pengadaan.complete', $pengadaan->id) }}" enctype="multipart/form-data" id="form-complete-pengadaan">
            @csrf @method('PATCH')

            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Jenis</th>
                    <th>Nama Barang</th>
                    <th>Satuan</th>
                    <th>Diminta</th>
                    <th>Alasan</th>
                    <th>Jumlah Realisasi <span style="color:#DC2626;">*</span></th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($pengadaan->details as $i => $detail)
                    <tr>
                      <td style="color:#94A3B8;">{{ $i + 1 }}</td>
                      <td>
                        @if($detail->tipe_item === 'baru')
                          <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:12px;background:#FEF3C7;color:#92400E;">
                            BARU
                          </span>
                        @else
                          <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:12px;background:#DBEAFE;color:#1E40AF;">
                            RESTOCK
                          </span>
                        @endif
                      </td>
                      <td style="font-weight:600;">
                        @if($detail->tipe_item === 'baru')
                          {{ $detail->nama_barang_baru }}
                          <div style="font-size:11px;color:#94A3B8;">Belum ada di sistem</div>
                        @else
                          {{ $detail->barang->nama_barang ?? '-' }}
                          <div style="font-size:11px;color:#64748B;font-family:'DM Mono',monospace;">
                            {{ $detail->barang->kode_barang ?? '' }} | Stok: {{ $detail->barang->stok ?? 0 }}
                          </div>
                        @endif
                      </td>
                      <td style="font-size:12px;color:#64748B;">
                        {{ $detail->tipe_item === 'baru' ? $detail->satuan_baru : ($detail->barang->satuan ?? '-') }}
                      </td>
                      <td style="font-weight:700;color:#1D4ED8;">{{ $detail->jumlah }}</td>
                      <td style="font-size:12px;color:#475569;max-width:200px;">{{ $detail->alasan ?? '-' }}</td>
                      <td>
                        <input type="number"
                               name="jumlah_realisasi[{{ $detail->id }}]"
                               min="0"
                               max="999"
                               value="{{ old("jumlah_realisasi.{$detail->id}", $detail->jumlah) }}"
                               onkeydown="return /[0-9]/.test(event.key) || ['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(event.key)"
                               oninput="if(this.value > 999) this.value = 999; if(this.value < 0) this.value = 0;"
                               style="width:80px;padding:6px 8px;border:1.5px solid #CBD5E1;border-radius:6px;font-size:13px;text-align:center;"
                               required>
                        @error("jumlah_realisasi.{$detail->id}")
                          <div style="font-size:11px;color:#DC2626;margin-top:4px;">{{ $message }}</div>
                        @enderror
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            {{-- UPLOAD FOTO BUKTI --}}
            <div style="padding:20px 24px;border-top:1px solid #F1F5F9;">
              <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:700;color:#1E293B;margin-bottom:8px;">
                  <i class="bi bi-camera" style="color:#0055A5;margin-right:4px;"></i>
                  Foto Bukti Pembelian <span style="color:#DC2626;">*</span>
                </label>
                <div id="drop-zone"
                     style="border:2px dashed #CBD5E1;border-radius:12px;padding:24px;text-align:center;
                            cursor:pointer;transition:all .2s ease;background:#F8FAFC;"
                     onclick="document.getElementById('foto_bukti_input').click()"
                     ondragover="event.preventDefault();this.style.borderColor='#0055A5';this.style.background='#EFF6FF';"
                     ondragleave="this.style.borderColor='#CBD5E1';this.style.background='#F8FAFC';"
                     ondrop="event.preventDefault();this.style.borderColor='#CBD5E1';this.style.background='#F8FAFC';handleFileDrop(event);">
                  <div id="drop-zone-content">
                    <i class="bi bi-cloud-arrow-up" style="font-size:32px;color:#94A3B8;"></i>
                    <div style="font-size:13px;color:#64748B;margin-top:8px;">
                      <strong style="color:#0055A5;">Klik untuk pilih file</strong> atau seret file ke sini
                    </div>
                    <div style="font-size:11px;color:#94A3B8;margin-top:4px;">JPG, PNG, WebP · Maks. 5 MB</div>
                  </div>
                  <div id="preview-container" style="display:none;">
                    <img id="preview-img" style="max-height:200px;border-radius:8px;object-fit:contain;" alt="Preview"/>
                    <div id="preview-name" style="font-size:12px;color:#64748B;margin-top:8px;"></div>
                    <button type="button" onclick="event.stopPropagation();clearPreview();"
                            style="margin-top:8px;padding:4px 12px;border:1px solid #FCA5A5;background:#FEF2F2;
                                   color:#DC2626;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;">
                      <i class="bi bi-trash"></i> Hapus
                    </button>
                  </div>
                </div>
                <input type="file" name="foto_bukti" id="foto_bukti_input" accept="image/jpeg,image/png,image/webp"
                       style="display:none;" onchange="handleFileSelect(this)">
                @error('foto_bukti')
                  <div style="font-size:12px;color:#DC2626;margin-top:6px;"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                @enderror
              </div>

              {{-- CATATAN PBJ --}}
              <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:700;color:#1E293B;margin-bottom:8px;">
                  <i class="bi bi-chat-left-text" style="color:#0055A5;margin-right:4px;"></i>
                  Catatan (Opsional)
                </label>
                <textarea name="catatan_pbj" rows="3" placeholder="Tambahkan catatan mengenai pembelian..."
                          style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;
                                 font-size:13px;font-family:inherit;resize:vertical;box-sizing:border-box;">{{ old('catatan_pbj') }}</textarea>
              </div>

              <p style="font-size:12px;color:#64748B;margin-bottom:12px;">
                <i class="bi bi-info-circle"></i>
                Isi jumlah barang yang <strong>benar-benar berhasil dibeli</strong>.
                Setelah klik Selesaikan, data akan dikirim ke Admin untuk <strong>diverifikasi</strong> sebelum stok masuk ke sistem.
              </p>
              <button type="button"
                      onclick="showConfirm({
                        title: 'Selesaikan Pengadaan?',
                        message: 'Data realisasi dan foto bukti akan dikirim ke Admin untuk diverifikasi.',
                        icon: 'bi-check-circle-fill', iconColor: '#059669',
                        confirmText: 'Ya, Selesaikan', confirmClass: 'confirm-btn-success',
                        onConfirm: function() { document.getElementById('form-complete-pengadaan').submit(); }
                      })"
                      style="padding:12px 24px;background:#059669;color:#fff;border:none;border-radius:8px;
                             font-weight:700;font-size:14px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;">
                <i class="bi bi-check-circle-fill"></i> Selesaikan Pengadaan
              </button>
            </div>
          </form>

        @else
          {{-- Sudah selesai / ditolak --}}
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Jenis</th>
                  <th>Nama Barang</th>
                  <th>Satuan</th>
                  <th>Diminta</th>
                  <th>Realisasi</th>
                  <th>Alasan</th>
                </tr>
              </thead>
              <tbody>
                @foreach($pengadaan->details as $i => $detail)
                  <tr>
                    <td style="color:#94A3B8;">{{ $i + 1 }}</td>
                    <td>
                      <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:12px;
                                   background:{{ $detail->tipe_item==='baru' ? '#FEF3C7' : '#DBEAFE' }};
                                   color:{{ $detail->tipe_item==='baru' ? '#92400E' : '#1E40AF' }};">
                        {{ strtoupper($detail->tipe_item) }}
                      </span>
                    </td>
                    <td style="font-weight:600;">
                      {{ $detail->tipe_item === 'baru'
                          ? $detail->nama_barang_baru . ' (' . ($detail->barang->kode_barang ?? 'belum dibuat') . ')'
                          : ($detail->barang->nama_barang ?? '-') }}
                    </td>
                    <td style="font-size:12px;color:#64748B;">
                      {{ $detail->tipe_item === 'baru' ? $detail->satuan_baru : ($detail->barang->satuan ?? '-') }}
                    </td>
                    <td style="font-weight:700;color:#1D4ED8;">{{ $detail->jumlah }}</td>
                    <td style="font-weight:700;color:#059669;">{{ $detail->jumlah_realisasi ?? 0 }}</td>
                    <td style="font-size:12px;color:#475569;">{{ $detail->alasan ?? '-' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          {{-- Foto Bukti & Catatan --}}
          @if($pengadaan->foto_bukti)
            <div style="padding:20px 24px;border-top:1px solid #F1F5F9;">
              <div class="detail-label">Foto Bukti Pembelian</div>
              <div style="margin-top:8px;">
                <img src="{{ asset('storage/' . $pengadaan->foto_bukti) }}" alt="Foto Bukti"
                     style="max-height:300px;border-radius:8px;border:1px solid #E2E8F0;cursor:pointer;"
                     onclick="window.open(this.src, '_blank')"/>
              </div>
              @if($pengadaan->catatan_pbj)
                <div style="margin-top:12px;">
                  <div class="detail-label">Catatan PBJ</div>
                  <div class="detail-value">{{ $pengadaan->catatan_pbj }}</div>
                </div>
              @endif
            </div>
          @endif
        @endif
      </div>
    </div>

    {{-- KANAN: Panel Status --}}
    <div>
      <div class="card" style="position:sticky;top:24px;padding:20px 24px;">
        <h3 style="font-size:14px;font-weight:700;color:#1E293B;margin-bottom:16px;">
          <i class="bi bi-info-circle" style="color:#0055A5;margin-right:6px;"></i>Status Pengadaan
        </h3>

        <div style="display:flex;flex-direction:column;gap:12px;font-size:13px;">
          <div style="display:flex;justify-content:space-between;">
            <span style="color:#64748B;">Divisi Umum</span>
            <span style="font-weight:700;color:#059669;">✓ Disetujui</span>
          </div>
          <div style="display:flex;justify-content:space-between;">
            <span style="color:#64748B;">PBJ</span>
            <span style="font-weight:700;color:{{ $clr3 }};">{{ $lbl3 }}</span>
          </div>
          @if($pengadaan->status_level3 === 'completed')
            <div style="display:flex;justify-content:space-between;">
              <span style="color:#64748B;">Verifikasi Admin</span>
              @php
                $vMap = ['belum'=>['Menunggu','#D97706'], 'verified'=>['Terverifikasi','#059669'], 'rejected'=>['Ditolak','#DC2626']];
                [$vLbl, $vClr] = $vMap[$pengadaan->status_admin_verifikasi] ?? ['-','#94A3B8'];
              @endphp
              <span style="font-weight:700;color:{{ $vClr }};">{{ $vLbl }}</span>
            </div>
          @endif
          @if($pengadaan->completed_at)
            <div style="display:flex;justify-content:space-between;">
              <span style="color:#64748B;">Selesai pada</span>
              <span>{{ $pengadaan->completed_at->format('d/m/Y H:i') }}</span>
            </div>
          @endif
        </div>

        @if($pengadaan->status_level3 === 'pending')
          <hr style="border:none;border-top:1px solid #F1F5F9;margin:16px 0;">
          <button type="button"
                  onclick="showConfirm({
                    title: 'Tolak Pengadaan?',
                    message: 'Pengadaan ini akan ditolak. Tindakan ini tidak dapat dibatalkan.',
                    icon: 'bi-x-circle-fill', iconColor: '#DC2626',
                    confirmText: 'Ya, Tolak', confirmClass: 'confirm-btn-danger',
                    onConfirm: function() { document.getElementById('form-reject-pengadaan').submit(); }
                  })"
                  style="width:100%;padding:10px;border-radius:8px;border:1.5px solid #FCA5A5;background:#FEF2F2;
                         color:#DC2626;font-weight:600;font-size:13px;cursor:pointer;font-family:inherit;
                         display:flex;align-items:center;justify-content:center;gap:6px;">
            <i class="bi bi-x-circle"></i> Tolak Pengadaan
          </button>
          <form id="form-reject-pengadaan" method="POST"
                action="{{ route('pbj.pengadaan.reject', $pengadaan->id) }}">
            @csrf @method('PATCH')
          </form>
        @endif

        <hr style="border:none;border-top:1px solid #F1F5F9;margin:16px 0;">
        <a href="{{ route('pbj.pengadaan.index') }}"
           style="display:flex;align-items:center;justify-content:center;gap:6px;color:#64748B;font-size:13px;text-decoration:none;">
          <i class="bi bi-arrow-left"></i> Kembali
        </a>
      </div>
    </div>

  </div>

@endsection

@push('scripts')
<script>
function handleFileSelect(input) {
  if (input.files && input.files[0]) {
    showPreview(input.files[0]);
  }
}

function handleFileDrop(e) {
  var files = e.dataTransfer.files;
  if (files && files[0]) {
    var input = document.getElementById('foto_bukti_input');
    // Create a DataTransfer to set files on input
    var dt = new DataTransfer();
    dt.items.add(files[0]);
    input.files = dt.files;
    showPreview(files[0]);
  }
}

function showPreview(file) {
  var reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById('preview-img').src = e.target.result;
    document.getElementById('preview-name').textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
    document.getElementById('drop-zone-content').style.display = 'none';
    document.getElementById('preview-container').style.display = 'block';
  };
  reader.readAsDataURL(file);
}

function clearPreview() {
  document.getElementById('foto_bukti_input').value = '';
  document.getElementById('drop-zone-content').style.display = 'block';
  document.getElementById('preview-container').style.display = 'none';
}
</script>
@endpush
