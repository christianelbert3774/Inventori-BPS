@extends('layouts.pbj')
@section('title', 'Notifikasi')
@section('topbar-title') <span>Notifikasi</span> @endsection

@section('content')

<div class="page-header">
  <div class="breadcrumb">
    <a href="{{ route('pbj.dashboard') }}" style="color:inherit;text-decoration:none;">Dashboard</a>
    <span class="sep">/</span><span class="current">Notifikasi</span>
  </div>
  <h2>Notifikasi</h2>
  <p>Pembaruan status pengadaan yang masuk dan perlu ditindaklanjuti.</p>
</div>

{{-- SUMMARY BADGES --}}
<div class="notif-summary">
  <div class="notif-summary-item total">
    <i class="bi bi-bell-fill"></i>
    <div><div class="ns-num">{{ $notifikasis->count() }}</div><div class="ns-lbl">Total Notifikasi</div></div>
  </div>
  <div class="notif-summary-item new">
    <i class="bi bi-stars"></i>
    <div><div class="ns-num">{{ $unreadCount }}</div><div class="ns-lbl">7 Hari Terakhir</div></div>
  </div>
  <div class="notif-summary-item" style="background:rgba(245,158,11,.06);border-color:rgba(245,158,11,.15);">
    <i class="bi bi-hourglass-split" style="color:#D97706;"></i>
    <div><div class="ns-num" style="color:#D97706;">{{ $totalPending }}</div><div class="ns-lbl">Menunggu Diproses</div></div>
  </div>
  <div class="notif-summary-item approved">
    <i class="bi bi-check-circle-fill"></i>
    <div><div class="ns-num">{{ $totalSelesai }}</div><div class="ns-lbl">Selesai</div></div>
  </div>
  <div class="notif-summary-item rejected">
    <i class="bi bi-x-circle-fill"></i>
    <div><div class="ns-num">{{ $totalDitolak }}</div><div class="ns-lbl">Ditolak</div></div>
  </div>
</div>

{{-- NOTIFIKASI LIST --}}
<div class="card">
  <div class="card-header">
    <div><h3>Semua Notifikasi</h3><div class="card-sub">Diurutkan dari yang terbaru</div></div>
    <div class="card-actions">
      <div class="filter-tabs">
        <button class="filter-tab active" onclick="filterNotif('semua',this)">Semua</button>
        <button class="filter-tab" onclick="filterNotif('pending',this)">Menunggu</button>
        <button class="filter-tab" onclick="filterNotif('selesai',this)">Selesai</button>
        <button class="filter-tab" onclick="filterNotif('ditolak',this)">Ditolak</button>
      </div>
    </div>
  </div>

  <div id="notif-list">
    @forelse($notifikasis as $notif)
      @php
        $isNew = $notif['time']->diffInDays(now()) <= 7;
        switch($notif['status']) {
          case 'pending':
            $icon='bi-bag-plus-fill';$iconColor='var(--bps-orange)';$iconBg='rgba(240,125,0,.1)';
            $title='Pengadaan Baru — Perlu Ditindaklanjuti';
            $badgeClass='badge-pending';$badgeLabel='Menunggu';
            $filterGroup = 'pending';
            break;
          case 'menunggu_verifikasi':
            $icon='bi-shield-exclamation';$iconColor='#D97706';$iconBg='rgba(217,119,6,.12)';
            $title='Selesai Diproses — Menunggu Verifikasi Admin';
            $badgeClass='badge-verify';$badgeLabel='Menunggu Verifikasi';
            $filterGroup = 'selesai';
            break;
          case 'verified':
            $icon='bi-shield-fill-check';$iconColor='#059669';$iconBg='rgba(5,150,105,.1)';
            $title='Pengadaan Terverifikasi oleh Admin';
            $badgeClass='badge-approved';$badgeLabel='Terverifikasi';
            $filterGroup = 'selesai';
            break;
          case 'verifikasi_ditolak':
            $icon='bi-shield-x';$iconColor='#DC2626';$iconBg='rgba(220,38,38,.1)';
            $title='Verifikasi Ditolak oleh Admin';
            $badgeClass='badge-rejected';$badgeLabel='Verifikasi Ditolak';
            $filterGroup = 'ditolak';
            break;
          case 'rejected':
            $icon='bi-bag-x-fill';$iconColor='#DC2626';$iconBg='rgba(220,38,38,.1)';
            $title='Pengadaan Ditolak';
            $badgeClass='badge-rejected';$badgeLabel='Ditolak';
            $filterGroup = 'ditolak';
            break;
          default:
            $icon='bi-bag-plus-fill';$iconColor='var(--bps-orange)';$iconBg='rgba(240,125,0,.1)';
            $title='Pengadaan';
            $badgeClass='badge-pending';$badgeLabel='Menunggu';
            $filterGroup = 'pending';
        }
        $routeDetail = route('pbj.pengadaan.show', $notif['id']);
      @endphp

      <div class="notif-item" data-group="{{ $filterGroup }}">
        <div class="notif-icon-wrap" style="background:{{ $iconBg }}">
          <i class="bi {{ $icon }}" style="color:{{ $iconColor }};font-size:20px"></i>
        </div>
        <div class="notif-body">
          <div class="notif-header-row">
            <div class="notif-title">
              {{ $title }}
              @if($isNew)<span class="notif-new-badge">Baru</span>@endif
            </div>
            <div class="notif-time"><i class="bi bi-clock"></i> {{ $notif['time']->diffForHumans() }}</div>
          </div>
          <div class="notif-desc">
            <strong>{{ $notif['pemohon'] }}</strong> — {{ $notif['barangs'] }}
          </div>
          <div class="notif-footer-row">
            <span class="notif-type-tag orange">
              <i class="bi bi-bag"></i>
              Pengadaan #{{ str_pad($notif['id'], 5, '0', STR_PAD_LEFT) }}
            </span>
            <span class="badge-status {{ $badgeClass }}">{{ $badgeLabel }}</span>
            <a href="{{ $routeDetail }}" class="btn-detail" style="padding:4px 10px;font-size:11px;">
              <i class="bi bi-eye"></i> Detail
            </a>
          </div>
        </div>
      </div>
    @empty
      <div class="empty-state" style="padding:56px 24px">
        <i class="bi bi-bell-slash" style="font-size:52px;opacity:.25;display:block;margin-bottom:12px"></i>
        <h4>Belum Ada Notifikasi</h4>
        <p>Notifikasi akan muncul ketika ada pengadaan baru yang perlu Anda tindaklanjuti.</p>
      </div>
    @endforelse
  </div>
</div>
@endsection

@push('scripts')
<script>
function filterNotif(type, btn) {
  document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.notif-item').forEach(item => {
    if (type === 'semua' || item.dataset.group === type) {
      item.style.display = 'flex';
    } else {
      item.style.display = 'none';
    }
  });
}
</script>
@endpush
