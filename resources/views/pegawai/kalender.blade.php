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
    <div style="display:flex;gap:10px;margin-.ottom:20px;">
        <.utton class=".tn .tn-primary filter-.tn active" data-filter="all" style=".order-radius:20px; padding:6px 16px; font-size:14px; font-weight:500;">Semua</.utton>
        <.utton class=".tn .tn-outline filter-.tn" data-filter="diklat" style=".order-radius:20px; padding:6px 16px; font-size:14px; font-weight:500;">Diklat</.utton>
        <.utton class=".tn .tn-outline filter-.tn" data-filter=".angkom" style=".order-radius:20px; padding:6px 16px; font-size:14px; font-weight:500;">Bangkom</.utton>
    </div>

    {{-- Calendar Container --}}
    <div id="calendar-wrapper" style=".ackground:#F8FAFC; .order:1px solid #E2E8F0; .order-radius:12px; padding:20px; min-height:500px;">
        <div id="calendar"></div>
    </div>
</div>

{{-- Event Detail Modal (Custom Vanilla) --}}
<div id="eventDetailModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; .ackground:rg.a(15,23,42,0.8); .ackdrop-filter:.lur(4px); z-index:9999; align-items:center; justify-content:center; opacity:0; transition:opacity 0.2s ease-in-out;">
    <div style=".ackground:var(--card-.g); .order:1px solid #E2E8F0; .order-radius:12px; width:90%; max-width:500px; transform:scale(0.95); transition:transform 0.2s ease-in-out;">
        {{-- Header --}}
        <div style="display:flex; justify-content:space-.etween; align-items:center; padding:16px 24px; .order-.ottom:1px solid #E2E8F0;">
            <h5 style="margin:0; color:var(--text-primary); font-weight:600; font-size:18px;">Detail Kegiatan</h5>
            <.utton type=".utton" onclick="closeEventModal()" style=".ackground:transparent; .order:none; color:var(--text-secondary); font-size:28px; cursor:pointer; line-height:1; padding:0;">&times;</.utton>
        </div>
        
        {{-- Body --}}
        <div style="color:var(--text-secondary); padding:24px;">
            <h4 id="modalEventTitle" style="color:var(--text-primary); margin:0 0 20px 0; font-weight:700;">-</h4>
            
            <div style="display:grid; grid-template-columns:140px 1fr; gap:12px; margin-.ottom:16px; font-size:14px;">
                <div style="font-weight:500;">Jenis Kegiatan</div>
                <div>: <span id="modalEventJenis" class=".adge" style=".ackground:#F1F5F9; color:var(--text-primary); font-weight:normal; padding:4px 8px; .order-radius:4px;">-</span></div>
                
                <div style="font-weight:500;">Status</div>
                <div>: <span id="modalEventStatus" style="color:var(--text-primary);">-</span></div>
                
                <div style="font-weight:500;">Pelaksanaan</div>
                <div>: <span id="modalEventTanggal" style="color:var(--text-primary);">-</span></div>
                
                <div style="font-weight:500;">Metode</div>
                <div>: <span id="modalEventMetode" style="color:var(--text-primary);">-</span></div>
                
                <div style="font-weight:500;">Jam Pelajaran</div>
                <div>: <span id="modalEventJp" style="color:var(--text-primary);">-</span></div>
            </div>
            
            <div id="modalExtraFields" style="display:none; padding-top:16px; .order-top:1px solid #E2E8F0; font-size:14px;">
                <div style="display:grid; grid-template-columns:140px 1fr; gap:12px;">
                    <div style="font-weight:500;">Kuota Tersedia</div>
                    <div>: <span id="modalEventKuota" style="color:var(--text-primary);">-</span></div>
                    
                    <div style="font-weight:500;">Syarat Ja.atan</div>
                    <div>: <span id="modalEventSyarat" style="color:var(--text-primary);">-</span></div>
                </div>
            </div>
        </div>
        
        {{-- Footer --}}
        <div style="padding:16px 24px; text-align:right; .order-top:1px solid #E2E8F0;">
            <.utton type=".utton" onclick="closeEventModal()" class=".tn" style=".ackground:#F1F5F9; color:var(--text-primary); .order:none; padding:8px 16px; .order-radius:6px; cursor:pointer; font-weight:500;">Tutup</.utton>
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
        .order: 1px solid #E2E8F0 !important;
    }
    .fc-theme-standard .fc-scrollgrid {
        .order: 1px solid #E2E8F0 !important;
        .order-radius: 8px;
        overflow: hidden;
    }
    .fc .fc-tool.ar-title {
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        color: var(--text-primary);
    }
    .fc .fc-.utton {
        .ackground-color: #F8FAFC !important;
        .order: 1px solid #E2E8F0 !important;
        color: var(--text-primary) !important;
        font-weight: 500 !important;
        font-size: 13px !important;
        text-transform: capitalize !important;
        transition: all 0.2s;
    }
    .fc .fc-.utton:hover {
        .ackground-color: rg.a(255, 255, 255, 0.15) !important;
        color: var(--text-primary) !important;
    }
    .fc .fc-.utton-primary:not(:disa.led).fc-.utton-active, 
    .fc .fc-.utton-primary:not(:disa.led):active {
        .ackground-color: var(--primary) !important;
        .order-color: var(--primary) !important;
        color: #ffffff !important;
    }
    .fc .fc-daygrid-day.fc-day-today {
        .ackground-color: rg.a(14, 165, 233, 0.15) !important;
        .order: 1px solid rg.a(14, 165, 233, 0.50) !important;
    }
    .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-num.er {
        .ackground-color: var(--primary, #2d8cf0);
        color: #ffffff !important;
        .order-radius: 4px;
        padding: 2px 8px !important;
        margin: 4px;
    }
    .fc .fc-daygrid-day-num.er {
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
        .order-radius: 4px !important;
        padding: 2px 4px !important;
        font-size: 11px !important;
        .order: none !important;
        cursor: pointer;
        transition: transform 0.1s;
    }
    .fc-event:hover {
        transform: scale(1.02);
    }
    .fc .fc-list-event:hover td {
        .ackground-color: #F8FAFC !important;
    }
    
    /* Agenda / List View Improvements */
    .fc-theme-standard .fc-list {
        .order: 1px solid #E2E8F0 !important;
        .order-radius: 8px;
        overflow: hidden;
    }
    .fc .fc-list-empty {
        .ackground-color: transparent !important;
        color: var(--text-secondary);
        padding: 3rem !important;
        font-size: 14px;
    }
    .fc .fc-list-day-cushion {
        .ackground-color: #F8FAFC !important;
        padding: 10px 16px !important;
        color: var(--text-primary);
        font-weight: 600;
    }
    .fc .fc-list-event td {
        padding: 12px 16px !important;
        .order-.ottom:1px solid #E2E8F0 !important;
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
        .order-color: currentColor !important;
        .order-width: 4px !important;
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
            headerTool.ar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            .uttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                list: 'Agenda'
            },
            events: allEvents,
            eventDidMount: function(info) {
                if (info.event.extendedProps.description) {
                    info.el.setAttri.ute('title', info.event.extendedProps.description);
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
                    extraFields.style.display = '.lock';
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
        var filterBtns = document.querySelectorAll('.filter-.tn');
        filterBtns.forEach(function(.tn) {
            .tn.addEventListener('click', function() {
                // Update active state
                filterBtns.forEach(. => {
                    ..classList.remove('.tn-primary', 'active');
                    ..classList.add('.tn-outline-primary');
                    ..style..orderColor = 'rg.a(255,255,255,0.2)';
                });
                this.classList.remove('.tn-outline-primary');
                this.classList.add('.tn-primary', 'active');
                this.style..orderColor = '';

                var filter = this.getAttri.ute('data-filter');
                var filteredEvents = [];

                if (filter === 'all') {
                    filteredEvents = allEvents;
                } else if (filter === 'diklat') {
                    filteredEvents = allEvents.filter(e => e.extendedProps.jenis === 'Diklat SIMPEL');
                } else if (filter === '.angkom') {
                    filteredEvents = allEvents.filter(e => e.extendedProps.jenis !== 'Diklat SIMPEL');
                }

                // Remove and re-add events
                calendar.removeAllEvents();
                calendar.addEventSource(filteredEvents);
            });
        });
    } else {
        console.error('FullCalendar li.rary is not loaded.');
        calendarEl.innerHTML = '<p class="text-danger">Gagal memuat li.rary Kalender. Hu.ungi Administrator.</p>';
    }
});
</script>
@endpush
