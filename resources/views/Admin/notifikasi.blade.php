@extends('layouts.admin')
@section('title', 'Notifikasi')
@section('topbar-title') <span>Notifikasi</span> @endsection

@section('content')
{{--
  BARU — admin/notifikasi.blade.php
  Notifikasi untuk Level 2: daftar semua permintaan masuk dari karyawan.
  Badge dot merah hilang saat halaman ini dibuka (notif_read_at dicatat di controller).
--}}
<div class="page-header">
  <div class="breadcrumb">
    <a href="{{ route('admin.dashboard') }}" style="color:inherit;text-decoration:none;">Dashboard</a>
    <span class="sep">/</span><span class="current">Notifikasi</span>
  </div>
  <h2>Notifikasi</h2>
  <p>Permintaan pemakaian dan pengadaan yang masuk dari karyawan.</p>
</div>

{{-- SUMMARY --}}
<div class="notif-summary">
  <div class="notif-summary-item total">
    <i class="bi bi-bell-fill"></i>
    <div><div class="ns-num">{{ $notifikasis->count() }}</div><div class="ns-lbl">Total Notifikasi</div></div>
  </div>
  <div class="notif-summary-item new">
    <i class="bi bi-stars"></i>
    <div><div class="ns-num">{{ $unreadCount }}</div><div class="ns-lbl">7 Hari Terakhir</div></div>
  </div>
  <div class="notif-summary-item approved">
    <i class="bi bi-cart-check-fill"></i>
    <div>
      <div class="ns-num">{{ $notifikasis->where('type','pemakaian')->count() }}</div>
      <div class="ns-lbl">Permintaan Pemakaian</div>
    </div>
  </div>
  <div class="notif-summary-item rejected">
    <i class="bi bi-bag-check-fill"></i>
    <div>
      <div class="ns-num">{{ $notifikasis->where('type','pengadaan')->count() }}</div>
      <div class="ns-lbl">Permintaan Pengadaan</div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div><h3>Semua Notifikasi</h3><div class="card-sub">Diurutkan dari yang terbaru</div></div>
    <div class="card-actions">
      <div class="filter-tabs">
        <button class="filter-tab active" onclick="filterNotif('semua',this)">Semua</button>
        <button class="filter-tab" onclick="filterNotif('pemakaian',this)">Pemakaian</button>
        <button class="filter-tab" onclick="filterNotif('pengadaan',this)">Pengadaan</button>
      </div>
    </div>
  </div>

  <div id="notif-list">
    @forelse($notifikasis as $notif)
      @php
        $isNew = $notif['time']->diffInDays(now()) <= 7;
        if ($notif['type'] === 'pemakaian') {
          switch($notif['status']) {
            case 'approved': $icon='bi-check-circle-fill';$iconColor='var(--bps-green)';$iconBg='rgba(61,170,53,.1)';$title='Permintaan Pemakaian Disetujui';$badgeClass='badge-approved';$badgeLabel='Disetujui';break;
            case 'rejected': $icon='bi-x-circle-fill';$iconColor='#DC2626';$iconBg='rgba(220,38,38,.1)';$title='Permintaan Pemakaian Ditolak';$badgeClass='badge-rejected';$badgeLabel='Ditolak';break;
            default:         $icon='bi-clock-fill';$iconColor='var(--bps-orange)';$iconBg='rgba(240,125,0,.1)';$title='Permintaan Pemakaian Baru Masuk';$badgeClass='badge-pending';$badgeLabel='Menunggu';
          }
        } else {
          switch($notif['status']) {
            case 'approved': $icon='bi-send-fill';$iconColor='var(--bps-blue)';$iconBg='rgba(0,85,165,.1)';$title='Pengadaan Diteruskan ke PBJ';$badgeClass='badge-forwarded';$badgeLabel='Diteruskan';break;
            case 'rejected': $icon='bi-bag-x-fill';$iconColor='#DC2626';$iconBg='rgba(220,38,38,.1)';$title='Permintaan Pengadaan Ditolak';$badgeClass='badge-rejected';$badgeLabel='Ditolak';break;
            default:         $icon='bi-bag-plus-fill';$iconColor='var(--bps-orange)';$iconBg='rgba(240,125,0,.1)';$title='Permintaan Pengadaan Baru Masuk';$badgeClass='badge-pending';$badgeLabel='Menunggu';
          }
        }
        $routeDetail = $notif['type']==='pemakaian' ? route('admin.pemakaian.show',$notif['id']) : route('admin.pengadaan.show',$notif['id']);
      @endphp

      <div class="notif-item {{ $notif['type'] }}" data-type="{{ $notif['type'] }}">
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
            <span class="notif-type-tag {{ $notif['type']==='pemakaian' ? 'blue' : 'orange' }}">
              <i class="bi {{ $notif['type']==='pemakaian' ? 'bi-cart3' : 'bi-bag' }}"></i>
              {{ $notif['type']==='pemakaian' ? 'Pemakaian' : 'Pengadaan' }}
              #{{ str_pad($notif['id'],5,'0',STR_PAD_LEFT) }}
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
        <p>Notifikasi akan muncul ketika ada permintaan baru dari karyawan.</p>
      </div>
    @endforelse
  </div>
</div>
@endsection

@push('scripts')
<script>
function filterNotif(type,btn){
  document.querySelectorAll('.filter-tab').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.notif-item').forEach(item=>{
    item.style.display=(type==='semua'||item.dataset.type===type)?'flex':'none';
  });
}
</script>
@endpush
