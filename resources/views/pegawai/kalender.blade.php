@extends('layouts.app')
@section('title', 'Kalender Saya')
@section('header_title', 'Kalender Kegiatan Bangkom')

@section('content')
<div class="page-header">
    <h1 style="font-size:30px">📅 Kalender Kegiatan Bangkom</h1>
</div>

<div class="card">
    <div class="card-title" style="font-size:18px;">Jadwal Kegiatan Bangkom Saya</div>
    
    {{-- Filter Buttons --}}
    <div style="display:flex;gap:10px;margin-bottom:20px;">
        <button class="btn btn-primary filter-btn active" data-filter="all" style="border-radius:20px; padding:6px 16px; font-size:14px; font-weight:500;">Semua</button>
        <button class="btn btn-outline filter-btn" data-filter="diklat" style="border-radius:20px; padding:6px 16px; font-size:14px; font-weight:500;">Diklat</button>
        <button class="btn btn-outline filter-btn" data-filter="bangkom" style="border-radius:20px; padding:6px 16px; font-size:14px; font-weight:500;">Bangkom</button>
    </div>

    {{-- Calendar Container --}}
    <div id="calendar-wrapper" style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:12px; padding:20px; min-height:500px;">
        <div id="calendar"></div>
    </div>
</div>

{{-- Event Detail Modal (Custom Vanilla) --}}
<div id="eventDetailModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.8); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center; opacity:0; transition:opacity 0.2s ease-in-out;">
    <div style="background:var(--card-bg); border:1px solid #E2E8F0; border-radius:12px; width:90%; max-width:500px; transform:scale(0.95); transition:transform 0.2s ease-in-out;">
        {{-- Header --}}
        <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 24px; border-bottom:1px solid #E2E8F0;">
            <h5 style="margin:0; color:var(--text-primary); font-weight:600; font-size:18px;">Detail Kegiatan</h5>
            <button type="button" onclick="closeEventModal()" style="background:transparent; border:none; color:var(--text-secondary); font-size:28px; cursor:pointer; line-height:1; padding:0;">&times;</button>
        </div>
        
        {{-- Body --}}
        <div style="color:var(--text-secondary); padding:24px;">
            <h4 id="modalEventTitle" style="color:var(--text-primary); margin:0 0 20px 0; font-weight:700;">-</h4>
            
            <div style="display:grid; grid-template-columns:140px 1fr; gap:12px; margin-bottom:16px; font-size:14px;">
                <div style="font-weight:500;">Jenis Kegiatan</div>
                <div>: <span id="modalEventJenis" class="badge" style="background:#F1F5F9; color:var(--text-primary); font-weight:normal; padding:4px 8px; border-radius:4px;">-</span></div>
                
                <div style="font-weight:500;">Status</div>
                <div>: <span id="modalEventStatus" style="color:var(--text-primary);">-</span></div>
                
                <div style="font-weight:500;">Pelaksanaan</div>
                <div>: <span id="modalEventTanggal" style="color:var(--text-primary);">-</span></div>
                
                <div style="font-weight:500;">Metode</div>
                <div>: <span id="modalEventMetode" style="color:var(--text-primary);">-</span></div>
                
                <div style="font-weight:500;">Jam Pelajaran</div>
                <div>: <span id="modalEventJp" style="color:var(--text-primary);">-</span></div>
            </div>
            
            <div id="modalExtraFields" style="display:none; padding-top:16px; border-top:1px solid #E2E8F0; font-size:14px;">
                <div style="display:grid; grid-template-columns:140px 1fr; gap:12px;">
                    <div style="font-weight:500;">Kuota Tersedia</div>
                    <div>: <span id="modalEventKuota" style="color:var(--text-primary);">-</span></div>
                    
                    <div style="font-weight:500;">Syarat Jabatan</div>
                    <div>: <span id="modalEventSyarat" style="color:var(--text-primary);">-</span></div>
                </div>
            </div>
        </div>
        
        {{-- Footer --}}
        <div style="padding:16px 24px; text-align:right; border-top:1px solid #E2E8F0;">
            <button type="button" onclick="closeEventModal()" class="btn" style="background:#F1F5F9; color:var(--text-primary); border:none; padding:8px 16px; border-radius:6px; cursor:pointer; font-weight:500;">Tutup</button>
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
        border: 1px solid #E2E8F0 !important;
    }
    .fc-theme-standard .fc-scrollgrid {
        border: 1px solid #E2E8F0 !important;
        border-radius: 8px;
        overflow: hidden;
    }
    .fc .fc-toolbar-title {
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        color: var(--text-primary);
    }
    .fc .fc-button {
        background-color: #F8FAFC !important;
        border: 1px solid #E2E8F0 !important;
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
        background-color: rgba(14, 165, 233, 0.15) !important;
        border: 1px solid rgba(14, 165, 233, 0.50) !important;
    }
    .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        background-color: var(--primary, #2d8cf0);
        color: #ffffff !important;
        border-radius: 4px;
        padding: 2px 8px !important;
        margin: 4px;
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
        background-color: #F8FAFC !important;
    }
    
    /* Agenda / List View Improvements */
    .fc-theme-standard .fc-list {
        border: 1px solid #E2E8F0 !important;
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
        background-color: #F8FAFC !important;
        padding: 10px 16px !important;
        color: var(--text-primary);
        font-weight: 600;
    }
    .fc .fc-list-event td {
        padding: 12px 16px !important;
        border-bottom:1px solid #E2E8F0 !important;
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
        var allEvents = @json($events);
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
            events: allEvents,
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
                
                // Show Modal using vanilla JS
                var modal = document.getElementById('eventDetailModal');
                modal.style.display = 'flex';
                // Trigger reflow for animation
                void modal.offsetWidth;
                modal.style.opacity = '1';
                modal.children[0].style.transform = 'scale(1)';
            }
        });
        calendar.render();

        // Custom Modal Close Function
        window.closeEventModal = function() {
            var modal = document.getElementById('eventDetailModal');
            modal.style.opacity = '0';
            modal.children[0].style.transform = 'scale(0.95)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 200);
        };

        // Filter functionality
        var filterBtns = document.querySelectorAll('.filter-btn');
        filterBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                // Update active state
                filterBtns.forEach(b => {
                    b.classList.remove('btn-primary', 'active');
                    b.classList.add('btn-outline-primary');
                    b.style.borderColor = 'rgba(255,255,255,0.2)';
                });
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary', 'active');
                this.style.borderColor = '';

                var filter = this.getAttribute('data-filter');
                var filteredEvents = [];

                if (filter === 'all') {
                    filteredEvents = allEvents;
                } else if (filter === 'diklat') {
                    filteredEvents = allEvents.filter(e => e.extendedProps.jenis === 'Diklat SIMPEL');
                } else if (filter === 'bangkom') {
                    filteredEvents = allEvents.filter(e => e.extendedProps.jenis !== 'Diklat SIMPEL');
                }

                // Remove and re-add events
                calendar.removeAllEvents();
                calendar.addEventSource(filteredEvents);
            });
        });
    } else {
        console.error('FullCalendar library is not loaded.');
        calendarEl.innerHTML = '<p class="text-danger">Gagal memuat library Kalender. Hubungi Administrator.</p>';
    }
});
</script>
@endpush
