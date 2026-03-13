@extends('layouts.admin')

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
    <p>Isi jumlah barang yang berhasil dibeli dan klik "Selesaikan" untuk memperbarui stok.</p>
  </div>

  @if(session('error'))
    <div style="margin-bottom:16px;padding:12px 16px;background:#FEE2E2;border-radius:8px;color:#991B1B;border-left:4px solid #DC2626;">
      <i class="bi bi-x-circle-fill"></i> {{ session('error') }}
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
            <div class="detail-label">Nama</div>
            <div class="detail-value">{{ $pengadaan->user->name ?? '-' }}</div>
          </div>
          <div>
            <div class="detail-label">Divisi</div>
            <div class="detail-value">{{ $pengadaan->user->bagian ?? '-' }}</div>
          </div>
          <div>
            <div class="detail-label">Tanggal Pengajuan</div>
            <div class="detail-value">{{ $pengadaan->created_at->format('d/m/Y H:i') }}</div>
          </div>
          <div>
            <div class="detail-label">Status</div>
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
          <form method="POST" action="{{ route('pbj.pengadaan.complete', $pengadaan->id) }}">
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
                               max="{{ $detail->jumlah }}"
                               value="{{ $detail->jumlah }}"
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

            <div style="padding:16px 24px;border-top:1px solid #F1F5F9;">
              <p style="font-size:12px;color:#64748B;margin-bottom:12px;">
                <i class="bi bi-info-circle"></i>
                Isi jumlah barang yang <strong>benar-benar berhasil dibeli</strong>.
                Untuk barang baru (label BARU), barang akan otomatis ditambahkan ke sistem setelah klik Selesaikan.
                Isi 0 jika barang tidak berhasil dibeli.
              </p>
              <button type="submit"
                      style="padding:12px 24px;background:#059669;color:#fff;border:none;border-radius:8px;
                             font-weight:700;font-size:14px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;">
                <i class="bi bi-check-circle-fill"></i> Selesaikan Pengadaan & Update Stok
              </button>
            </div>
          </form>

        @else
          {{-- Sudah selesai / ditolak, tampilkan saja tanpa form --}}
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
                          ? $detail->nama_barang_baru . ' (' . ($detail->barang->kode_barang ?? 'sudah dibuat') . ')'
                          : ($detail->barang->nama_barang ?? '-') }}
                    </td>
                    <td style="font-size:12px;color:#64748B;">
                      {{ $detail->tipe_item === 'baru' ? $detail->satuan_baru : ($detail->barang->satuan ?? '-') }}
                    </td>
                    <td style="font-weight:700;color:#1D4ED8;">{{ $detail->jumlah }}</td>
                    <td style="font-size:12px;color:#475569;">{{ $detail->alasan ?? '-' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
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
          @if($pengadaan->completed_at)
            <div style="display:flex;justify-content:space-between;">
              <span style="color:#64748B;">Selesai pada</span>
              <span>{{ $pengadaan->completed_at->format('d/m/Y H:i') }}</span>
            </div>
          @endif
        </div>

        <hr style="border:none;border-top:1px solid #F1F5F9;margin:16px 0;">
        <a href="{{ route('pbj.pengadaan.index') }}"
           style="display:flex;align-items:center;justify-content:center;gap:6px;color:#64748B;font-size:13px;text-decoration:none;">
          <i class="bi bi-arrow-left"></i> Kembali
        </a>
      </div>
    </div>

  </div>

@endsection
