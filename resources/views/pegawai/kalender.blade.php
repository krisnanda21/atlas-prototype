@extends('layouts.app')
@section('title', 'Kalender Saya')
@section('header_title', 'Kalender Kegiatan Bangkom')

@section('content')
<div class="page-header">
    <h1 style="font-size:30px">📅 Kalender Kegiatan Bangkom</h1>
</div>

<div class="card">
    <div class="card-title" style="font-size:18px;">Jadwal Kegiatan Bangkom Saya</div>
    
    {{-- Legend --}}
    <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:6px;font-size:12px;"><div style="width:12px;height:12px;border-radius:2px;background:#f59e0b;"></div>Pelatihan Formal</div>
        <div style="display:flex;align-items:center;gap:6px;font-size:12px;"><div style="width:12px;height:12px;border-radius:2px;background:#3b82f6;"></div>Bangkom Unit</div>
        <div style="display:flex;align-items:center;gap:6px;font-size:12px;"><div style="width:12px;height:12px;border-radius:2px;background:#10b981;"></div>Mandiri / KMS</div>
        <div style="display:flex;align-items:center;gap:6px;font-size:12px;"><div style="width:12px;height:12px;border-radius:2px;background:#8b5cf6;"></div>Diklat SIMPEL</div>
    </div>

    {{-- Calendar Container --}}
    <div id="calendar-wrapper" style="background:rgba(255,255,255,0.01); border:1px solid rgba(255,255,255,0.05); border-radius:12px; padding:20px; min-height:500px;">
        <div id="calendar"></div>
    </div>
</div>

{{-- Event Detail Modal --}}
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--card-bg); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px;">
            <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                <h5 class="modal-title" id="eventDetailModalLabel" style="color: var(--text-primary); font-weight: 600;">Detail Kegiatan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="color: var(--text-secondary); padding: 24px;">
                <h4 id="modalEventTitle" style="color: var(--text-primary); margin-bottom: 20px; font-weight: 700;">-</h4>
                
                <div style="display: grid; grid-template-columns: 140px 1fr; gap: 12px; margin-bottom: 16px;">
                    <div style="font-weight: 500;">Jenis Kegiatan</div>
                    <div>: <span id="modalEventJenis" class="badge" style="background: rgba(255,255,255,0.1); color: var(--text-primary); font-weight: normal;">-</span></div>
                    
                    <div style="font-weight: 500;">Status</div>
                    <div>: <span id="modalEventStatus" style="color: var(--text-primary);">-</span></div>
                    
                    <div style="font-weight: 500;">Pelaksanaan</div>
                    <div>: <span id="modalEventTanggal" style="color: var(--text-primary);">-</span></div>
                    
                    <div style="font-weight: 500;">Metode</div>
                    <div>: <span id="modalEventMetode" style="color: var(--text-primary);">-</span></div>
                    
                    <div style="font-weight: 500;">Jam Pelajaran</div>
                    <div>: <span id="modalEventJp" style="color: var(--text-primary);">-</span></div>
                </div>
                
                <div id="modalExtraFields" style="display: none; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.05);">
                    <div style="display: grid; grid-template-columns: 140px 1fr; gap: 12px;">
                        <div style="font-weight: 500;">Kuota Tersedia</div>
                        <div>: <span id="modalEventKuota" style="color: var(--text-primary);">-</span></div>
                        
                        <div style="font-weight: 500;">Syarat Jabatan</div>
                        <div>: <span id="modalEventSyarat" style="color: var(--text-primary);">-</span></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: none; padding: 0 24px 24px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background: rgba(255,255,255,0.1); border: none;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* FullCalendar styling overrides to fit the dark theme of ATLAS */
    .fc {
        font-family: inherit;
        color: var(--text-primary) !important;
    }
    .fc-theme-standard td, .fc-theme-standard th {
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
    .fc-theme-standard .fc-scrollgrid {
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 8px;
        overflow: hidden;
    }
    .fc .fc-toolbar-title {
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        color: var(--text-primary);
    }
    .fc .fc-button {
        background-color: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: var(--text-primary) !important;
        font-weight: 500 !important;
        font-size: 13px !important;
        text-transform: capitalize !important;
        transition: all 0.2s;
    }
    .fc .fc-button:hover {
        background-color: rgba(255, 255, 255, 0.15) !important;
        color: var(--text-primary) !important;
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active, 
    .fc .fc-button-primary:not(:disabled):active {
        background-color: var(--primary) !important;
        border-color: var(--primary) !important;
        color: #ffffff !important;
    }
    .fc .fc-daygrid-day.fc-day-today {
        background-color: rgba(45, 140, 240, 0.05) !important;
    }
    .fc .fc-daygrid-day-number {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-primary);
        padding: 6px 10px !important;
    }
    .fc .fc-col-header-cell-cushion {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary);
        padding: 8px 0 !important;
        text-decoration: none !important;
    }
    .fc-event {
        border-radius: 4px !important;
        padding: 2px 4px !important;
        font-size: 11px !important;
        border: none !important;
        cursor: pointer;
        transition: transform 0.1s;
    }
    .fc-event:hover {
        transform: scale(1.02);
    }
    .fc .fc-list-event:hover td {
        background-color: rgba(255, 255, 255, 0.03) !important;
    }
    
    /* Agenda / List View Improvements */
    .fc-theme-standard .fc-list {
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 8px;
        overflow: hidden;
    }
    .fc .fc-list-empty {
        background-color: transparent !important;
        color: var(--text-secondary);
        padding: 3rem !important;
        font-size: 14px;
    }
    .fc .fc-list-day-cushion {
        background-color: rgba(255, 255, 255, 0.02) !important;
        padding: 10px 16px !important;
        color: var(--text-primary);
        font-weight: 600;
    }
    .fc .fc-list-event td {
        padding: 12px 16px !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
        color: var(--text-secondary);
    }
    .fc .fc-list-event-title {
        color: var(--text-primary);
        font-weight: 500;
    }
    .fc .fc-list-event-title a {
        color: inherit !important;
        text-decoration: none !important;
    }
    .fc .fc-list-event-dot {
        border-color: currentColor !important;
        border-width: 4px !important;
    }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    if (typeof FullCalendar !== 'undefined') {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                list: 'Agenda'
            },
            events: @json($events),
            eventDidMount: function(info) {
                if (info.event.extendedProps.description) {
                    info.el.setAttribute('title', info.event.extendedProps.description);
                }
            },
            eventClick: function(info) {
                var props = info.event.extendedProps;
                
                // Populate Modal Data
                document.getElementById('modalEventTitle').textContent = info.event.title;
                document.getElementById('modalEventJenis').textContent = props.jenis || '-';
                document.getElementById('modalEventTanggal').textContent = props.tanggal || '-';
                document.getElementById('modalEventMetode').textContent = props.metode || '-';
                document.getElementById('modalEventJp').textContent = props.jp ? (props.jp + ' JP') : '-';
                document.getElementById('modalEventStatus').textContent = props.status || '-';
                
                // Extra fields for Diklat SIMPEL
                var extraFields = document.getElementById('modalExtraFields');
                if (props.jenis === 'Diklat SIMPEL') {
                    extraFields.style.display = 'block';
                    document.getElementById('modalEventKuota').textContent = props.kuota || '-';
                    document.getElementById('modalEventSyarat').textContent = props.syarat || '-';
                } else {
                    extraFields.style.display = 'none';
                }
                
                // Show Modal
                var eventModal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
                eventModal.show();
            }
        });
        calendar.render();
    } else {
        console.error('FullCalendar library is not loaded.');
        calendarEl.innerHTML = '<p class="text-danger">Gagal memuat library Kalender. Hubungi Administrator.</p>';
    }
});
</script>
@endpush
