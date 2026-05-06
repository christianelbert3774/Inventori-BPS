<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Print Pemakaian #{{ str_pad($pemakaian->id, 5, '0', STR_PAD_LEFT) }}</title>
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
    .kop-right .doc-id {
      font-size: 18px; font-weight: 800; color: #fff; margin-top: 3px;
      font-family: 'DM Mono', monospace;
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
    .seksi-title i.blue { background: rgba(0,85,165,.1); color: var(--blue); }

    /* DETAIL GRID */
    .detail-grid {
      display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
      margin-bottom: 20px;
    }
    .detail-box {
      background: #F7FAFE; border-radius: 10px; padding: 14px 18px;
      border: 1px solid var(--border);
    }
    .detail-box label {
      font-size: 10.5px; font-weight: 700; color: var(--gray);
      text-transform: uppercase; letter-spacing: .6px; display: block; margin-bottom: 4px;
    }
    .detail-box .val {
      font-size: 14px; font-weight: 600; color: #0D1F3C;
    }

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
    <span>📄 Print Permintaan Pemakaian #{{ str_pad($pemakaian->id, 5, '0', STR_PAD_LEFT) }}</span>
  </div>
  <div class="print-toolbar-right">
    <a href="{{ route('karyawan.pemakaian.index') }}" class="toolbar-btn btn-back">
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
      <p>Bukti Permintaan Pemakaian Barang Inventori (SIBAS)</p>
    </div>
    <div class="kop-right">
      <div class="doc-label">No. Permintaan</div>
      <div class="doc-id">#{{ str_pad($pemakaian->id, 5, '0', STR_PAD_LEFT) }}</div>
      <div class="doc-tanggal">Dicetak: {{ now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }}</div>
    </div>
  </div>

  {{-- IDENTITAS --}}
  <div class="identitas">
    <div class="id-item">
      <label>Nama</label>
      <span>{{ $user->name }}</span>
    </div>
    <div class="id-item">
      <label>NIP</label>
      <span>{{ $user->nip ?? '—' }}</span>
    </div>
    <div class="id-item">
      <label>Email</label>
      <span>{{ $user->email }}</span>
    </div>
    <div class="id-item">
      <label>Bagian</label>
      <span>{{ $user->bagian ?? '—' }}</span>
    </div>
    <div class="id-item">
      <label>Jabatan</label>
      <span>{{ $user->jabatan ?? '—' }}</span>
    </div>
    <div class="id-item">
      <label>Tanggal Pengajuan</label>
      <span>{{ $pemakaian->created_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }}</span>
    </div>
  </div>

  {{-- DETAIL PERMINTAAN --}}
  <div class="seksi">
    <div class="seksi-title">
      <i class="blue">🛒</i>
      Detail Permintaan Pemakaian
    </div>

    <div class="detail-grid">
      <div class="detail-box">
        <label>Status</label>
        <div class="val">
          @if($pemakaian->status === 'approved')
            <span class="badge badge-ok">Disetujui</span>
          @elseif($pemakaian->status === 'rejected')
            <span class="badge badge-no">Ditolak</span>
          @else
            <span class="badge badge-wait">Menunggu</span>
          @endif
        </div>
      </div>
      <div class="detail-box">
        <label>Diproses Oleh</label>
        <div class="val">{{ $pemakaian->approvedBy->name ?? '—' }}</div>
      </div>
      @if($pemakaian->approved_at)
      <div class="detail-box">
        <label>Tanggal Diproses</label>
        <div class="val">{{ \Carbon\Carbon::parse($pemakaian->approved_at)->locale('id')->isoFormat('D MMMM Y, HH:mm') }}</div>
      </div>
      @endif
      @if($pemakaian->catatan)
      <div class="detail-box">
        <label>Catatan</label>
        <div class="val">{{ $pemakaian->catatan }}</div>
      </div>
      @endif
    </div>

    <table class="tbl">
      <thead>
        <tr>
          <th style="width:50px">#</th>
          <th>Nama Barang</th>
          <th style="width:100px">Jumlah</th>
          <th style="width:100px">Satuan</th>
        </tr>
      </thead>
      <tbody>
        @foreach($pemakaian->details as $idx => $d)
          <tr>
            <td class="mono">{{ $idx + 1 }}</td>
            <td class="fw6">{{ $d->barang->nama_barang ?? $d->nama_barang_baru ?? 'Barang Baru' }}</td>
            <td class="mono" style="font-size:13px; font-weight:600;">{{ $d->jumlah }}</td>
            <td>{{ $d->barang->satuan ?? $d->satuan_baru ?? '-' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- FOOTER --}}
  <div class="doc-footer">
    <span>SIBAS — Sistem Inventori Barang BPS</span>
    <span>Pemakaian #{{ str_pad($pemakaian->id, 5, '0', STR_PAD_LEFT) }} · {{ $user->name }}</span>
    <span>Dicetak {{ now()->format('d/m/Y H:i') }}</span>
  </div>

</div>

</body>
</html>
