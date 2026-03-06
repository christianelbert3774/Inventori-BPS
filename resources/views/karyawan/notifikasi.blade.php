@extends('layouts.app')

@section('title', 'Notifikasi')

@section('topbar-title')
  <span>Notifikasi</span>
@endsection

@section('content')
<div class="page-header">
  <div class="breadcrumb">
    <a href="{{ route('karyawan.dashboard') }}">Dashboard</a>
    <span class="sep">/</span>
    <span class="current">Notifikasi</span>
  </div>
  <h2>Notifikasi</h2>
  <p>Pembaruan status permintaan pemakaian dan pengadaan Anda.</p>
</div>

{{-- SUMMARY BADGES --}}
<div class="notif-summary">
  <div class="notif-summary-item total">
    <i class="bi bi-bell-fill"></i>
    <div>
      <div class="ns-num">{{ $notifikasis->count() }}</div>
      <div class="ns-lbl">Total Notifikasi</div>
    </div>
  </div>
  <div class="notif-summary-item new">
    <i class="bi bi-stars"></i>
    <div>
      <div class="ns-num">{{ $unreadCount }}</div>
      <div class="ns-lbl">7 Hari Terakhir</div>
    </div>
  </div>
  <div class="notif-summary-item approved">
    <i class="bi bi-check-circle-fill"></i>
    <div>
      <div class="ns-num">{{ $notifikasis->where('status', 'approved')->count() + $notifikasis->where('status', 'approved_l2')->count() + $notifikasis->where('status', 'completed')->count() }}</div>
      <div class="ns-lbl">Disetujui</div>
    </div>
  </div>
  <div class="notif-summary-item rejected">
    <i class="bi bi-x-circle-fill"></i>
    <div>
      <div class="ns-num">{{ $notifikasis->where('status', 'rejected')->count() }}</div>
      <div class="ns-lbl">Ditolak</div>
    </div>
  </div>
</div>

{{-- NOTIFIKASI LIST --}}
<div class="card">
  <div class="card-header">
    <div>
      <h3>Semua Notifikasi</h3>
      <div class="card-sub">Diurutkan dari yang terbaru</div>
    </div>
    <div class="card-actions">
      <div class="filter-tabs">
        <button class="filter-tab active" onclick="filterNotif('semua', this)">Semua</button>
        <button class="filter-tab" onclick="filterNotif('pemakaian', this)">Pemakaian</button>
        <button class="filter-tab" onclick="filterNotif('pengadaan', this)">Pengadaan</button>
      </div>
    </div>
  </div>

  <div id="notif-list">
    @forelse($notifikasis as $notif)
      @php
        $isNew = $notif['time']->diffInDays(now()) <= 7;

        if ($notif['type'] === 'pemakaian') {
          switch ($notif['status']) {
            case 'approved':
              $icon = 'bi-check-circle-fill'; $iconColor = 'var(--bps-green)';
              $iconBg = 'rgba(61,170,53,.1)';
              $title = 'Permintaan Pemakaian Disetujui';
              $badgeClass = 'badge-approved'; $badgeLabel = 'Disetujui';
              break;
            case 'rejected':
              $icon = 'bi-x-circle-fill'; $iconColor = '#DC2626';
              $iconBg = 'rgba(220,38,38,.1)';
              $title = 'Permintaan Pemakaian Ditolak';
              $badgeClass = 'badge-rejected'; $badgeLabel = 'Ditolak';
              break;
            default:
              $icon = 'bi-clock-fill'; $iconColor = 'var(--bps-orange)';
              $iconBg = 'rgba(240,125,0,.1)';
              $title = 'Permintaan Pemakaian Menunggu';
              $badgeClass = 'badge-pending'; $badgeLabel = 'Menunggu';
          }
        } else {
          switch ($notif['status']) {
            case 'completed':
              $icon = 'bi-bag-check-fill'; $iconColor = 'var(--bps-green)';
              $iconBg = 'rgba(61,170,53,.1)';
              $title = 'Pengadaan Selesai Diproses';
              $badgeClass = 'badge-approved'; $badgeLabel = 'Selesai';
              break;
            case 'approved_l2':
              $icon = 'bi-bag-check'; $iconColor = 'var(--bps-blue)';
              $iconBg = 'rgba(0,85,165,.1)';
              $title = 'Pengadaan Disetujui Divisi Umum';
              $badgeClass = 'badge-approved'; $badgeLabel = 'Disetujui';
              break;
            case 'rejected':
              $icon = 'bi-bag-x-fill'; $iconColor = '#DC2626';
              $iconBg = 'rgba(220,38,38,.1)';
              $title = 'Permintaan Pengadaan Ditolak';
              $badgeClass = 'badge-rejected'; $badgeLabel = 'Ditolak';
              break;
            default:
              $icon = 'bi-clock-fill'; $iconColor = 'var(--bps-orange)';
              $iconBg = 'rgba(240,125,0,.1)';
              $title = 'Permintaan Pengadaan Menunggu';
              $badgeClass = 'badge-pending'; $badgeLabel = 'Menunggu';
          }
        }
      @endphp

      <div class="notif-item {{ $notif['type'] }}" data-type="{{ $notif['type'] }}">
        <div class="notif-icon-wrap" style="background:{{ $iconBg }}">
          <i class="bi {{ $icon }}" style="color:{{ $iconColor }};font-size:20px"></i>
        </div>
        <div class="notif-body">
          <div class="notif-header-row">
            <div class="notif-title">
              {{ $title }}
              @if($isNew)
                <span class="notif-new-badge">Baru</span>
              @endif
            </div>
            <div class="notif-time">
              <i class="bi bi-clock"></i>
              {{ $notif['time']->diffForHumans() }}
            </div>
          </div>
          <div class="notif-desc">{{ $notif['barangs'] }}</div>
          <div class="notif-footer-row">
            <span class="notif-type-tag {{ $notif['type'] === 'pemakaian' ? 'blue' : 'orange' }}">
              <i class="bi {{ $notif['type'] === 'pemakaian' ? 'bi-cart3' : 'bi-bag' }}"></i>
              {{ $notif['type'] === 'pemakaian' ? 'Pemakaian' : 'Pengadaan' }}
              #{{ str_pad($notif['id'], 5, '0', STR_PAD_LEFT) }}
            </span>
            <span class="badge-status {{ $badgeClass }}">{{ $badgeLabel }}</span>
          </div>
        </div>
      </div>
    @empty
      <div class="empty-state" style="padding:56px 24px">
        <i class="bi bi-bell-slash" style="font-size:52px;opacity:.25;display:block;margin-bottom:12px"></i>
        <h4>Belum Ada Notifikasi</h4>
        <p>Notifikasi akan muncul ketika status permintaan Anda diperbarui oleh admin.</p>
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
    if (type === 'semua' || item.dataset.type === type) {
      item.style.display = 'flex';
    } else {
      item.style.display = 'none';
    }
  });
}
</script>
@endpush
