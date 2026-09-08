<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATLAS v4.0 – @yield('title', 'Dashboard')</title>
    <meta name="description" content="ATLAS – Aplikasi Talent & Learning Analytic System BPKP">
    <link rel="stylesheet" href="/css/atlas.css">
    <!-- FullCalendar (injected dynamically via JS bundle in v6) -->
    @stack('styles')
</head>
<body>
<div class="app-wrapper">
    {{-- Sidebar --}}
    <aside class="sidebar">
        @include('partials.sidebar')
    </aside>

    {{-- Main --}}
    <div class="main-content">
        {{-- Header --}}
        <header class="header">
            @include('partials.header')
        </header>

        {{-- Flash Messages --}}
        <div style="padding: 0 24px;">
            @if(session('success'))
                <div class="alert alert-success">✓ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">✗ {{ session('error') }}</div>
            @endif
        </div>

        {{-- Page Content --}}
        <section class="content">
            @yield('content')
        </section>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<!-- FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

{{-- Custom Confirmation Modal --}}
<div id="atlas-confirm-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.55);backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;opacity:0;transition:opacity 0.2s ease-in-out;">
    <div class="atlas-modal-content" style="background:var(--modal-bg);border:1px solid var(--card-border);border-radius:12px;width:90%;max-width:400px;padding:24px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.12), 0 10px 10px -5px rgba(0,0,0,0.06);transform:scale(0.95);transition:transform 0.2s ease-in-out;text-align:center;font-family:'Inter', sans-serif;">
        <div style="font-size:48px;margin-bottom:16px;color:var(--warning);">⚠️</div>
        <h3 id="atlas-confirm-title" style="margin:0 0 8px 0;font-size:18px;font-weight:700;color:var(--text-primary);">Konfirmasi</h3>
        <p id="atlas-confirm-message" style="margin:0 0 24px 0;font-size:14px;color:var(--text-secondary);line-height:1.5;">Apakah Anda yakin?</p>
        <div style="display:flex;gap:12px;justify-content:center;">
            <button id="atlas-confirm-btn-cancel" class="btn btn-neutral" style="padding:10px 20px;font-weight:600;min-width:100px;">Batal</button>
            <button id="atlas-confirm-btn-confirm" class="btn btn-primary" style="padding:10px 20px;font-weight:600;min-width:100px;">Ya</button>
        </div>
    </div>
</div>

<script>
window.atlasConfirm = function(options) {
    return new Promise((resolve) => {
        const modal = document.getElementById('atlas-confirm-modal');
        if (!modal) return resolve(true); // Fallback
        
        const modalContent = modal.querySelector('.atlas-modal-content');
        const titleEl = document.getElementById('atlas-confirm-title');
        const msgEl = document.getElementById('atlas-confirm-message');
        const btnConfirm = document.getElementById('atlas-confirm-btn-confirm');
        const btnCancel = document.getElementById('atlas-confirm-btn-cancel');

        titleEl.textContent = options.title || 'Konfirmasi';
        msgEl.textContent = options.message || 'Apakah Anda yakin?';
        btnConfirm.textContent = options.confirmText || 'Ya';
        
        if (options.confirmClass) {
            btnConfirm.className = options.confirmClass;
        }
        btnCancel.textContent = options.cancelText || 'Batal';

        // Show modal with animation
        modal.style.display = 'flex';
        modal.offsetHeight; // trigger reflow
        modal.style.opacity = '1';
        modalContent.style.transform = 'scale(1)';

        function hideModal() {
            modal.style.opacity = '0';
            modalContent.style.transform = 'scale(0.95)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 200);
        }

        const handleConfirm = () => {
            cleanup();
            hideModal();
            resolve(true);
        };

        const handleCancel = () => {
            cleanup();
            hideModal();
            resolve(false);
        };

        const cleanup = () => {
            btnConfirm.removeEventListener('click', handleConfirm);
            btnCancel.removeEventListener('click', handleCancel);
        };

        btnConfirm.addEventListener('click', handleConfirm);
        btnCancel.addEventListener('click', handleCancel);
    });
};
</script>

@stack('scripts')
</body>
</html>
