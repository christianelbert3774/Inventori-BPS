<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Laporan Aktivitas — {{ $karyawan->name }} — {{ $bulanLabel }}</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --blue:   #0055A5;
      --blue-dk:#003D7A;
      --orange: #F07D00;
      --green:  #3DAA35;
      --red:    #DC2626;
      --gray:   #5B6F8A;
      --border: #D8E3F0;
      --bg:     #F0F4FA;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg); color: #0D1F3C;
      padding: 0;
    }

    /* ── TOOLBAR ── */
    .print-toolbar {
      background: var(--blue-dk);
      padding: 14px 32px;
      display: flex; align-items: center; justify-content: space-between;
      position: sticky; top: 0; z-index: 100;
      box-shadow: 0 2px 12px rgba(0,0,0,.25);
    }
    .print-toolbar-left {
      display: flex; align-items: center; gap: 14px;
    }
    .print-toolbar-left span {
      color: rgba(255,255,255,.75); font-size: 13.5px;
    }
    .print-toolbar-right { display: flex; gap: 10px; align-items: center; }

    .toolbar-btn {
      padding: 9px 18px; border-radius: 9px; font-family: inherit;
      font-size: 13px; font-weight: 700; cursor: pointer; border: none;
      display: flex; align-items: center; gap: 7px; transition: .2s;
      text-decoration: none;
    }
    .btn-print {
      background: white; color: var(--blue);
    }
    .btn-print:hover { background: #E8F0FC; }
    .btn-back {
      background: rgba(255,255,255,.12); color: #fff;
      border: 1.5px solid rgba(255,255,255,.2);
    }
    .btn-back:hover { background: rgba(255,255,255,.2); }

    /* ── FILTER ── */
    .bulan-select, .jenis-select {
      padding: 8px 12px; border-radius: 8px; font-family: inherit;
      font-size: 13px; border: 1.5px solid rgba(255,255,255,.25);
      background: rgba(255,255,255,.1); color: #fff; cursor: pointer;
    }
    .bulan-select option, .jenis-select option { color: #000; background: #fff; }
    .filter-label {
      color: rgba(255,255,255,.7); font-size: 13px;
    }

    /* ── DOKUMEN ── */
    .dokumen {
      max-width: 820px; margin: 28px auto;
      background: #fff; border-radius: 12px;
      box-shadow: 0 4px 24px rgba(0,85,165,.12);
      overflow: hidden;
    }

    /* KOP SURAT */
    .kop {
      background: linear-gradient(135deg, var(--blue-dk), var(--blue));
      padding: 28px 36px;
      display: flex; align-items: center; gap: 20px;
    }
    .kop-logo {
      width: 60px; height: 60px; border-radius: 50%;
      background: rgba(255,255,255,.15); border: 2px solid rgba(255,255,255,.3);
      display: flex; align-items: center; justify-content: center;
      font-size: 22px; font-weight: 800; color: #fff; flex-shrink: 0;
    }
    .kop-text h1 {
      font-size: 17px; font-weight: 800; color: #fff; letter-spacing: -.3px;
    }
    .kop-text p {
      font-size: 12px; color: rgba(255,255,255,.7); margin-top: 2px;
    }
    .kop-right {
      margin-left: auto; text-align: right;
    }
    .kop-right .doc-label {
      font-size: 11px; color: rgba(255,255,255,.6); text-transform: uppercase;
      letter-spacing: .8px;
    }
    .kop-right .doc-bulan {
      font-size: 16px; font-weight: 800; color: #fff; margin-top: 3px;
    }
    .kop-right .doc-tanggal {
      font-size: 11px; color: rgba(255,255,255,.65); margin-top: 2px;
    }

    /* IDENTITAS */
    .identitas {
      padding: 20px 36px;
      background: #F7FAFE;
      border-bottom: 1.5px solid var(--border);
      display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;
    }
    .id-item label {
      font-size: 10.5px; font-weight: 700; color: var(--gray);
      text-transform: uppercase; letter-spacing: .6px; display: block; margin-bottom: 3px;
    }
    .id-item span {
      font-size: 13px; font-weight: 600; color: #0D1F3C;
    }

    /* RINGKASAN */
    .ringkasan {
      display: grid; grid-template-columns: repeat(4, 1fr);
      gap: 0; border-bottom: 1.5px solid var(--border);
    }
    .rsum-item {
      padding: 16px 20px; text-align: center;
      border-right: 1px solid var(--border);
    }
    .rsum-item:last-child { border-right: none; }
    .rsum-num {
      font-size: 28px; font-weight: 800; font-family: 'DM Mono', monospace;
      line-height: 1; color: #0D1F3C;
    }
    .rsum-num.blue   { color: var(--blue); }
    .rsum-num.orange { color: var(--orange); }
    .rsum-num.green  { color: var(--green); }
    .rsum-num.red    { color: var(--red); }
    .rsum-lbl {
      font-size: 11px; color: var(--gray); margin-top: 5px; font-weight: 500;
    }

    /* SEKSI */
    .seksi { padding: 24px 36px; }
    .seksi + .seksi { border-top: 1.5px solid var(--border); }
    .seksi-title {
      font-size: 14px; font-weight: 800; color: #0D1F3C;
      display: flex; align-items: center; gap: 8px; margin-bottom: 14px;
    }
    .seksi-title i {
      width: 28px; height: 28px; border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-size: 14px; flex-shrink: 0;
    }
    .seksi-title i.blue   { background: rgba(0,85,165,.1);  color: var(--blue); }
    .seksi-title i.orange { background: rgba(240,125,0,.1); color: var(--orange); }

    /* TABEL */
    .tbl { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .tbl thead th {
      padding: 9px 12px; text-align: left;
      background: #F0F4FA; font-size: 10.5px; font-weight: 700;
      color: var(--gray); text-transform: uppercase; letter-spacing: .5px;
      border-bottom: 1.5px solid var(--border);
    }
    .tbl tbody td {
      padding: 11px 12px; border-bottom: 1px solid var(--border);
      color: #0D1F3C; vertical-align: top;
    }
    .tbl tbody tr:last-child td { border-bottom: none; }
    .tbl tbody tr:nth-child(even) td { background: #FAFCFF; }
    .mono { font-family: 'DM Mono', monospace; font-size: 11.5px; color: var(--gray); }
    .fw6  { font-weight: 600; }

    /* BADGE */
    .badge {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 600;
    }
    .badge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; }
    .badge-ok  { background: rgba(61,170,53,.1);  color: #1E6B1A; }
    .badge-ok::before  { background: var(--green); }
    .badge-no  { background: rgba(220,38,38,.1);  color: var(--red); }
    .badge-no::before  { background: var(--red); }
    .badge-wait{ background: rgba(240,125,0,.1);  color: #C06000; }
    .badge-wait::before{ background: var(--orange); }

    .badge-baru {
      font-size: 9px; font-weight: 700; padding: 2px 7px;
      border-radius: 10px; background: #FEF3C7; color: #92400E;
      margin-left: 4px;
    }

    /* KOSONG */
    .kosong {
      text-align: center; padding: 28px; color: var(--gray);
      font-size: 13px; background: #FAFCFF; border-radius: 8px;
      border: 1.5px dashed var(--border);
    }

    /* ADMIN TAG */
    .admin-tag {
      display: inline-flex; align-items: center; gap: 5px;
      background: rgba(0,85,165,.08); color: var(--blue);
      font-size: 10.5px; font-weight: 700; padding: 3px 10px;
      border-radius: 20px; letter-spacing: .3px;
    }

    /* FOOTER */
    .doc-footer {
      padding: 16px 36px;
      border-top: 1.5px solid var(--border);
      background: #F7FAFE;
      display: flex; align-items: center; justify-content: space-between;
      font-size: 11px; color: var(--gray);
    }

    /* ── PRINT MEDIA ── */
    @media print {
      body { background: #fff; padding: 0; }
      .print-toolbar { display: none !important; }
      .dokumen {
        max-width: 100%; margin: 0;
        box-shadow: none; border-radius: 0;
      }
      @page { margin: 1.2cm; size: A4; }
    }
  </style>
</head>
<body>

{{-- ── TOOLBAR ── --}}
<div class="print-toolbar">
  <div class="print-toolbar-left">
    <span>📄 Laporan Aktivitas Karyawan — {{ $karyawan->name }}</span>
  </div>
  <div class="print-toolbar-right">
    {{-- Filter Bulan --}}
    <form id="filter-form" method="GET" action="{{ route('admin.karyawan.print', $karyawan->id) }}" style="display:flex;gap:8px;align-items:center">
      <label class="filter-label">Bulan:</label>
      <input type="month" name="bulan" value="{{ $bulan }}"
             class="bulan-select"
             onchange="this.form.submit()"/>

      <label class="filter-label" style="margin-left:8px;">Jenis:</label>
      <select name="jenis" class="jenis-select" onchange="this.form.submit()">
        <option value="semua" {{ $jenis === 'semua' ? 'selected' : '' }}>Semua</option>
        <option value="pemakaian" {{ $jenis === 'pemakaian' ? 'selected' : '' }}>Pemakaian</option>
        <option value="pengadaan" {{ $jenis === 'pengadaan' ? 'selected' : '' }}>Pengadaan</option>
      </select>
    </form>

    <a href="{{ route('admin.karyawan.history', $karyawan->id) }}" class="toolbar-btn btn-back">
      ← Kembali
    </a>
    <button class="toolbar-btn btn-print" onclick="window.print()">
      🖨️ Print / Simpan PDF
    </button>
  </div>
</div>

{{-- ── DOKUMEN ── --}}
<div class="dokumen">

  {{-- KOP --}}
  <div class="kop">
    <div class="kop-logo">BPS</div>
    <div class="kop-text">
      <h1>Badan Pusat Statistik</h1>
      <p>Laporan Aktivitas Permintaan Barang Inventori (SIBAS)</p>
    </div>
    <div class="kop-right">
      <div class="doc-label">Periode</div>
      <div class="doc-bulan">{{ $bulanLabel }}</div>
      <div class="doc-tanggal">Dicetak: {{ now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }}</div>
    </div>
  </div>

  {{-- IDENTITAS --}}
  <div class="identitas">
    <div class="id-item">
      <label>Nama</label>
      <span>{{ $karyawan->name }}</span>
    </div>
    <div class="id-item">
      <label>NIP</label>
      <span>{{ $karyawan->nip ?? '—' }}</span>
    </div>
    <div class="id-item">
      <label>Email</label>
      <span>{{ $karyawan->email }}</span>
    </div>
    <div class="id-item">
      <label>Bagian</label>
      <span>{{ $karyawan->bagian ?? '—' }}</span>
    </div>
    <div class="id-item">
      <label>Jabatan</label>
      <span>{{ $karyawan->jabatan ?? '—' }}</span>
    </div>
    <div class="id-item">
      <label>Dicetak Oleh</label>
      <span class="admin-tag">🛡️ {{ auth()->user()->name }}</span>
    </div>
  </div>

  {{-- RINGKASAN --}}
  @php
    $totalP  = $pemakaians->count();
    $approvedP = $pemakaians->where('status', 'approved')->count();
    $totalPg = $pengadaans->count();
    $approvedPg = $pengadaans->where('status_level2', 'approved')->count()
                + $pengadaans->where('status_level3', 'completed')->count();
  @endphp
  <div class="ringkasan">
    <div class="rsum-item">
      <div class="rsum-num blue">{{ $totalP }}</div>
      <div class="rsum-lbl">Permintaan Pemakaian</div>
    </div>
    <div class="rsum-item">
      <div class="rsum-num green">{{ $approvedP }}</div>
      <div class="rsum-lbl">Pemakaian Disetujui</div>
    </div>
    <div class="rsum-item">
      <div class="rsum-num orange">{{ $totalPg }}</div>
      <div class="rsum-lbl">Permintaan Pengadaan</div>
    </div>
    <div class="rsum-item">
      <div class="rsum-num green">{{ $approvedPg }}</div>
      <div class="rsum-lbl">Pengadaan Disetujui</div>
    </div>
  </div>

  {{-- TABEL PEMAKAIAN --}}
  @if($jenis === 'semua' || $jenis === 'pemakaian')
  <div class="seksi">
    <div class="seksi-title">
      <i class="blue">🛒</i>
      Rincian Permintaan Pemakaian — {{ $bulanLabel }}
    </div>

    @if($pemakaians->isEmpty())
      <div class="kosong">Tidak ada permintaan pemakaian pada bulan ini.</div>
    @else
      <table class="tbl">
        <thead>
          <tr>
            <th style="width:60px">#</th>
            <th>Barang yang Diminta</th>
            <th style="width:120px">Tanggal</th>
            <th style="width:100px">Diproses Oleh</th>
            <th style="width:90px">Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach($pemakaians as $p)
            <tr>
              <td class="mono">#{{ str_pad($p->id, 5, '0', STR_PAD_LEFT) }}</td>
              <td>
                @foreach($p->details as $d)
                  <div class="fw6">{{ $d->barang->nama_barang ?? $d->nama_barang_baru ?? 'Barang Baru' }}
                    <span style="font-weight:400;color:var(--gray)"> × {{ $d->jumlah }} {{ $d->barang->satuan ?? $d->satuan_baru ?? '' }}</span>
                  </div>
                @endforeach
              </td>
              <td class="mono" style="font-size:11px">
                {{ $p->created_at->format('d M Y') }}<br>
                <span style="color:var(--gray)">{{ $p->created_at->format('H:i') }}</span>
              </td>
              <td style="font-size:12px">{{ $p->approvedBy?->name ?? '—' }}</td>
              <td>
                @if($p->status === 'approved')
                  <span class="badge badge-ok">Disetujui</span>
                @elseif($p->status === 'rejected')
                  <span class="badge badge-no">Ditolak</span>
                @else
                  <span class="badge badge-wait">Menunggu</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
  @endif

  {{-- TABEL PENGADAAN --}}
  @if($jenis === 'semua' || $jenis === 'pengadaan')
  <div class="seksi">
    <div class="seksi-title">
      <i class="orange">📦</i>
      Rincian Permintaan Pengadaan — {{ $bulanLabel }}
    </div>

    @if($pengadaans->isEmpty())
      <div class="kosong">Tidak ada permintaan pengadaan pada bulan ini.</div>
    @else
      <table class="tbl">
        <thead>
          <tr>
            <th style="width:60px">#</th>
            <th>Barang yang Diajukan</th>
            <th style="width:120px">Tanggal</th>
            <th style="width:110px">Status Divisi</th>
            <th style="width:90px">Status PBJ</th>
          </tr>
        </thead>
        <tbody>
          @foreach($pengadaans as $p)
            <tr>
              <td class="mono">#{{ str_pad($p->id, 5, '0', STR_PAD_LEFT) }}</td>
              <td>
                @foreach($p->details as $d)
                  <div class="fw6">
                    @if($d->tipe_item === 'baru' && !$d->barang_id)
                      {{ $d->nama_barang_baru }}
                      <span class="badge-baru">BARU</span>
                    @else
                      {{ $d->barang->nama_barang ?? '-' }}
                    @endif
                    <span style="font-weight:400;color:var(--gray)"> × {{ $d->jumlah }} {{ ($d->tipe_item === 'baru' && !$d->barang_id) ? $d->satuan_baru : ($d->barang->satuan ?? '') }}</span>
                  </div>
                @endforeach
              </td>
              <td class="mono" style="font-size:11px">
                {{ $p->created_at->format('d M Y') }}<br>
                <span style="color:var(--gray)">{{ $p->created_at->format('H:i') }}</span>
              </td>
              <td>
                @if($p->status_level2 === 'approved')
                  <span class="badge badge-ok">Disetujui</span>
                @elseif($p->status_level2 === 'rejected')
                  <span class="badge badge-no">Ditolak</span>
                @else
                  <span class="badge badge-wait">Menunggu</span>
                @endif
              </td>
              <td>
                @if($p->status_level2 === 'rejected')
                  <span style="color:var(--gray)">-</span>
                @elseif($p->status_level3 === 'completed')
                  @if($p->status_admin_verifikasi === 'verified')
                    <span class="badge badge-ok">Terverifikasi</span>
                  @elseif($p->status_admin_verifikasi === 'rejected')
                    <span class="badge badge-no">Verifikasi Ditolak</span>
                  @else
                    <span class="badge badge-wait">Menunggu Verifikasi</span>
                  @endif
                @elseif($p->status_level3 === 'rejected')
                  <span class="badge badge-no">Ditolak PBJ</span>
                @else
                  <span class="badge badge-wait">Diproses PBJ</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
  @endif

  {{-- FOOTER --}}
  <div class="doc-footer">
    <span>SIBAS — Sistem Inventori Barang BPS</span>
    <span>Laporan {{ $bulanLabel }} · {{ $karyawan->name }}</span>
    <span>Dicetak {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}</span>
  </div>

</div>

</body>
</html>
