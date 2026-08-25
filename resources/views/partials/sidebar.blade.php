{{-- Sidebar partial --}}
<div class="sidebar-logo">
    <div class="logo-text">ATLAS</div>
    <div class="logo-sub">Talent & Learning Analytic</div>
</div>

@php
    $user = auth()->user();
    $roleLabels = [
        'staff'       => 'staff',
        'pengampuSDM' => 'perencana',
        'eselon3'     => 'verifikator level 1',
        'eselon2'     => 'verifikator level 2',
        'sesma'       => 'executive sesma',
        'deputi'      => 'executive deputi',
        'bangkom'     => 'perencana bangkom',
        'kombinasi'   => 'verifikator bangkom level 1',
        'karoSDM'     => 'verifikator bangkom level 2',
        'admin'       => 'admin',
    ];

    $eselon2Unit = '-';
    if ($user) {
        if ($user->role === 'sesma' || $user->role === 'deputi') { 
            $eselon2Unit = $user->unit_eselon1;
        } elseif ($user->role === 'pengampuSDM' && $user->jabatan === 'Kepala Subbagian Tata Usaha Deputi' ) {
            $eselon2Unit = $user->unit_eselon1;
        } elseif ($user->role === 'admin') {
            $eselon2Unit = '-';
        } else {
            $eselon2Unit = $user->unit_eselon2;
        }
    }
@endphp

<div class="sidebar-user">
    <div class="user-avatar">{{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}</div>
    <div class="user-name">{{ $user->name ?? 'Pengguna' }}</div>
    <span class="user-role-badge">{{ $roleLabels[session('active_role', $user->role ?? '-')] ?? session('active_role', $user->role ?? '-') }}</span>
    
    <div style="font-size: 11px; color: var(--text-secondary); margin-top: 8px; text-align: left; line-height: 1.4; padding: 0 12px; width: 100%; box-sizing: border-box;">
        <div style="font-weight: 600; color: #8899aa; word-break: break-word;">{{ $user->jabatan ?? '-' }}</div>
        <div style="margin-top: 4px; font-style: italic; color: #718096; word-break: break-word;">{{ $eselon2Unit }}</div>
    </div>
</div>

{{-- Switch Role --}}
@php
    $user = auth()->user();
    $activeRole = session('active_role', $user->role ?? '');
    $availableRoles = [$user->role];
    if ($user->mode_individu && $user->role !== 'staff') $availableRoles[] = 'staff';
    if ($user->role === 'admin') {
        $availableRoles = ['admin','staff','pengampuSDM','eselon2','eselon3','karoSDM','kombinasi','bangkom','deputi','sesma'];
    }

    // Label mapping for readable display
    $roleLabels = [
        'staff'       => 'staff',
        'pengampuSDM' => 'perencana',
        'eselon3'     => 'verifikator level 1',
        'eselon2'     => 'verifikator level 2',
        'sesma'       => 'executive sesma',
        'deputi'      => 'executive deputi',
        'bangkom'     => 'perencana bangkom',
        'kombinasi'   => 'verifikator bangkom level 1',
        'karoSDM'     => 'verifikator bangkom level 2',
        'admin'       => 'admin',
    ];

    // Korwas flag: eselon3 with jabatan = 'Koordinator Pengawasan' is view-only on unit approval pages
    $isKorwas = ($activeRole === 'eselon3' && ($user->jabatan ?? '') === 'Koordinator Pengawasan');
@endphp
@if(count($availableRoles) > 1)
<div class="switch-role-form">
    <form method="POST" action="{{ route('switch.role') }}">
        @csrf
        <select name="role" onchange="this.form.submit()" class="form-control" style="font-size:11px;">
            @foreach($availableRoles as $r)
                <option value="{{ $r }}" {{ $activeRole === $r ? 'selected' : '' }}>{{ $roleLabels[$r] ?? ucfirst($r) }}</option>
            @endforeach
        </select>
    </form>
</div>
@endif

{{-- Navigation by role --}}
<nav class="sidebar-nav">
@php $activeRole = session('active_role', auth()->user()->role ?? ''); @endphp

{{-- ══════════════════════════════════════════
     STAFF (Pegawai Fungsional / Pelaksana)
══════════════════════════════════════════ --}}
@if($activeRole === 'staff')
    <div class="nav-section-label">Menu</div>
    <a href="{{ route('pegawai.dashboard') }}" class="nav-item {{ request()->routeIs('pegawai.dashboard') ? 'active' : '' }}">
        <span class="nav-icon">🏠</span> Dashboard Saya
    </a>
    <a href="{{ route('pegawai.profil360') }}" class="nav-item {{ request()->routeIs('pegawai.profil360') ? 'active' : '' }}">
        <span class="nav-icon">👤</span> Profil 360 & Kompetensi
    </a>
    <a href="{{ route('pegawai.susunIdp') }}" class="nav-item {{ request()->routeIs('pegawai.susunIdp') ? 'active' : '' }}">
        <span class="nav-icon">📋</span> Susun IDP Saya
    </a>
    <a href="{{ route('pegawai.kalender') }}" class="nav-item {{ request()->routeIs('pegawai.kalender') ? 'active' : '' }}">
        <span class="nav-icon">📅</span> Kalender Saya
    </a>


{{-- ══════════════════════════════════════════
     PENGAMPU SDM UNIT (Eselon IV - Subkoor Kepegawaian / Kasubbag)
══════════════════════════════════════════ --}}
@elseif($activeRole === 'pengampuSDM')
    <div class="nav-section-label">Menu</div>
    <a href="{{ route('pengampu.demandPool') }}" class="nav-item {{ request()->routeIs('pengampu.demandPool') ? 'active' : '' }}">
        <span class="nav-icon">📥</span> IDP Demand Pool
    </a>
    <a href="{{ route('pengampu.strategicDirection') }}" class="nav-item {{ request()->routeIs('pengampu.strategicDirection') ? 'active' : '' }}">
        <span class="nav-icon">🎯</span> Strategic Direction
    </a>
    <a href="{{ route('pengampu.rencanaBangkom') }}" class="nav-item {{ request()->routeIs('pengampu.rencanaBangkom') ? 'active' : '' }}">
        <span class="nav-icon">📅</span> Rencana Bangkom Unit
    </a>
    <a href="{{ route('pengampu.realisasiBangkom') }}" class="nav-item {{ request()->routeIs('pengampu.realisasiBangkom') ? 'active' : '' }}">
        <span class="nav-icon">✅</span> Realisasi Bangkom Unit
    </a>
    <a href="{{ route('pengampu.certification') }}" class="nav-item {{ request()->routeIs('pengampu.certification') ? 'active' : '' }}">
        <span class="nav-icon">🏅</span> Certification Control
    </a>
    <a href="{{ route('pengampu.profil360') }}" class="nav-item {{ request()->routeIs('pengampu.profil360') ? 'active' : '' }}">
        <span class="nav-icon">👤</span> Profil 360 Unit
    </a>

{{-- ══════════════════════════════════════════
     ESELON II (Kepala Biro / Pusat / Inspektur / Direktur / Kaperwakilan)
══════════════════════════════════════════ --}}
@elseif($activeRole === 'eselon2')
    <div class="nav-section-label">Menu</div>
    <a href="{{ route('eselon2.dashboard') }}" class="nav-item {{ request()->routeIs('eselon2.dashboard') ? 'active' : '' }}">
        <span class="nav-icon">🏠</span> Dashboard Unit
    </a>
    <a href="{{ route('eselon2.arahanStrategis') }}" class="nav-item {{ request()->routeIs('eselon2.arahanStrategis') ? 'active' : '' }}">
        <span class="nav-icon">🎯</span> Arahan Strategis
    </a>
    <a href="{{ route('eselon2.reviewIdp') }}" class="nav-item {{ request()->routeIs('eselon2.reviewIdp') ? 'active' : '' }}">
        <span class="nav-icon">🔍</span> Review IDP Pegawai
    </a>
    <a href="{{ route('eselon2.penetapanBangkom') }}" class="nav-item {{ request()->routeIs('eselon2.penetapanBangkom') ? 'active' : '' }}">
        <span class="nav-icon">📅</span> Bangkom Unit
    </a>
    <a href="{{ route('eselon2.pembatalanBangkom') }}" class="nav-item {{ request()->routeIs('eselon2.pembatalanBangkom') ? 'active' : '' }}">
        <span class="nav-icon">❌</span> Pembatalan Bangkom
    </a>
    <a href="{{ route('eselon2.talentFinder') }}" class="nav-item {{ request()->routeIs('eselon2.talentFinder') ? 'active' : '' }}">
        <span class="nav-icon">💡</span> Talent Finder
    </a>
    <a href="{{ route('eselon2.profil360') }}" class="nav-item {{ request()->routeIs('eselon2.profil360') ? 'active' : '' }}">
        <span class="nav-icon">👤</span> Profil 360 Unit
    </a>

{{-- ══════════════════════════════════════════
     ESELON III (Koordinator / Kabagum)
══════════════════════════════════════════ --}}
@elseif($activeRole === 'eselon3')
    <div class="nav-section-label">Menu</div>
    <a href="{{ route('eselon3.dashboard') }}" class="nav-item {{ request()->routeIs('eselon3.dashboard') ? 'active' : '' }}">
        <span class="nav-icon">🏠</span> Dashboard Unit
    </a>
    <a href="{{ route('eselon3.arahanStrategis') }}" class="nav-item {{ request()->routeIs('eselon3.arahanStrategis') ? 'active' : '' }}">
        <span class="nav-icon">🎯</span> Arahan Strategis
    </a>
    <a href="{{ route('eselon3.reviewIdp') }}" class="nav-item {{ request()->routeIs('eselon3.reviewIdp') ? 'active' : '' }}">
        <span class="nav-icon">🔍</span> Review IDP Pegawai
    </a>
    <a href="{{ route('eselon3.penetapanBangkom') }}" class="nav-item {{ request()->routeIs('eselon3.penetapanBangkom') ? 'active' : '' }}">
        <span class="nav-icon">📅</span> Penetapan Bangkom
    </a>
    <a href="{{ route('eselon3.pembatalanBangkom') }}" class="nav-item {{ request()->routeIs('eselon3.pembatalanBangkom') ? 'active' : '' }}">
        <span class="nav-icon">❌</span> Pembatalan Bangkom
    </a>
    <a href="{{ route('eselon3.talentFinder') }}" class="nav-item {{ request()->routeIs('eselon3.talentFinder') ? 'active' : '' }}">
        <span class="nav-icon">💡</span> Talent Finder
    </a>
    <a href="{{ route('eselon3.profil360') }}" class="nav-item {{ request()->routeIs('eselon3.profil360') ? 'active' : '' }}">
        <span class="nav-icon">👤</span> Profil 360 Unit
    </a>

{{-- ══════════════════════════════════════════
     BANGKOM — Subkoor Pengembangan Kompetensi Biro SDM
══════════════════════════════════════════ --}}
@elseif($activeRole === 'bangkom')
    <div class="nav-section-label">Bangkom Nasional</div>
    <a href="{{ route('executive.insights') }}" class="nav-item {{ request()->routeIs('executive.insights') ? 'active' : '' }}">
        <span class="nav-icon">📊</span> Executive Insights
    </a>
    <a href="{{ route('executive.ncc') }}" class="nav-item {{ request()->routeIs('executive.ncc') ? 'active' : '' }}">
        <span class="nav-icon">🌐</span> National Control Centre
    </a>
    <a href="{{ route('executive.bangkomNasional') }}" class="nav-item {{ request()->routeIs('executive.bangkomNasional') ? 'active' : '' }}">
        <span class="nav-icon">📅</span> Bangkom Nasional
    </a>
    <a href="{{ route('executive.talentFinder') }}" class="nav-item {{ request()->routeIs('executive.talentFinder') ? 'active' : '' }}">
        <span class="nav-icon">🔍</span> Talent Finder Nasional
    </a>
    <a href="{{ route('executive.reportGenerator') }}" class="nav-item {{ request()->routeIs('executive.reportGenerator') ? 'active' : '' }}">
        <span class="nav-icon">📄</span> Report & Brief
    </a>
    <a href="{{ route('executive.profil360') }}" class="nav-item {{ request()->routeIs('executive.profil360') ? 'active' : '' }}">
        <span class="nav-icon">👤</span> Profil 360 Nasional
    </a>

    <div class="nav-section-label">Unit Biro SDM</div>
    <a href="{{ route('pengampu.demandPool') }}" class="nav-item {{ request()->routeIs('pengampu.demandPool') ? 'active' : '' }}">
        <span class="nav-icon">📥</span> IDP Demand Pool
    </a>
    <a href="{{ route('pengampu.strategicDirection') }}" class="nav-item {{ request()->routeIs('pengampu.strategicDirection') ? 'active' : '' }}">
        <span class="nav-icon">🎯</span> Strategic Direction
    </a>
    <a href="{{ route('pengampu.rencanaBangkom') }}" class="nav-item {{ request()->routeIs('pengampu.rencanaBangkom') ? 'active' : '' }}">
        <span class="nav-icon">📅</span> Rencana Bangkom
    </a>
    <a href="{{ route('pengampu.realisasiBangkom') }}" class="nav-item {{ request()->routeIs('pengampu.realisasiBangkom') ? 'active' : '' }}">
        <span class="nav-icon">✅</span> Realisasi Bangkom
    </a>
    <a href="{{ route('pengampu.certification') }}" class="nav-item {{ request()->routeIs('pengampu.certification') ? 'active' : '' }}">
        <span class="nav-icon">🏅</span> Certification Control
    </a>

{{-- ══════════════════════════════════════════
     KOMBINASI — Koordinator Pengembangan Kompetensi, Penilaian Kompetensi, dan Pembinaan SDM
══════════════════════════════════════════ --}}
@elseif($activeRole === 'kombinasi')
    <div class="nav-section-label">Bangkom Nasional</div>
    <a href="{{ route('executive.insights') }}" class="nav-item {{ request()->routeIs('executive.insights') ? 'active' : '' }}">
        <span class="nav-icon">📊</span> Executive Insights
    </a>
    <a href="{{ route('executive.talentFinder') }}" class="nav-item {{ request()->routeIs('executive.talentFinder') ? 'active' : '' }}">
        <span class="nav-icon">🔍</span> Talent Finder Nasional
    </a>
    <a href="{{ route('executive.ncc') }}" class="nav-item {{ request()->routeIs('executive.ncc') ? 'active' : '' }}">
        <span class="nav-icon">🌐</span> National Control Centre
    </a>
    <a href="{{ route('executive.profil360') }}" class="nav-item {{ request()->routeIs('executive.profil360') ? 'active' : '' }}">
        <span class="nav-icon">👤</span> Profil 360 Nasional
    </a>

    <div class="nav-section-label">Unit Biro SDM</div>
    <a href="{{ route('eselon2.arahanStrategis') }}" class="nav-item {{ request()->routeIs('eselon2.arahanStrategis') ? 'active' : '' }}">
        <span class="nav-icon">🎯</span> Arahan Strategis
    </a>
    <a href="{{ route('eselon2.reviewIdp') }}" class="nav-item {{ request()->routeIs('eselon2.reviewIdp') ? 'active' : '' }}">
        <span class="nav-icon">🔍</span> Review IDP Pegawai
    </a>
    <a href="{{ route('eselon2.penetapanBangkom') }}" class="nav-item {{ request()->routeIs('eselon2.penetapanBangkom') ? 'active' : '' }}">
        <span class="nav-icon">📅</span> Penetapan Bangkom
    </a>
    <a href="{{ route('eselon2.pembatalanBangkom') }}" class="nav-item {{ request()->routeIs('eselon2.pembatalanBangkom') ? 'active' : '' }}">
        <span class="nav-icon">❌</span> Pembatalan Bangkom
    </a>

{{-- ══════════════════════════════════════════
     KARO SDM — Kepala Biro SDM (akses nasional)
══════════════════════════════════════════ --}}
@elseif($activeRole === 'karoSDM')
    <div class="nav-section-label">Bangkom Nasional</div>
    <a href="{{ route('executive.insights') }}" class="nav-item {{ request()->routeIs('executive.insights') ? 'active' : '' }}">
        <span class="nav-icon">📊</span> Executive Insights
    </a>
    <a href="{{ route('executive.talentFinder') }}" class="nav-item {{ request()->routeIs('executive.talentFinder') ? 'active' : '' }}">
        <span class="nav-icon">🔍</span> Talent Finder Nasional
    </a>
    <a href="{{ route('executive.ncc') }}" class="nav-item {{ request()->routeIs('executive.ncc') ? 'active' : '' }}">
        <span class="nav-icon">🌐</span> National Control Centre
    </a>
    <a href="{{ route('executive.profil360') }}" class="nav-item {{ request()->routeIs('executive.profil360') ? 'active' : '' }}">
        <span class="nav-icon">👤</span> Profil 360 Nasional
    </a>

    <div class="nav-section-label">Unit Biro SDM</div>
    <a href="{{ route('eselon2.arahanStrategis') }}" class="nav-item {{ request()->routeIs('eselon2.arahanStrategis') ? 'active' : '' }}">
        <span class="nav-icon">🎯</span> Arahan Strategis
    </a>
    <a href="{{ route('eselon2.reviewIdp') }}" class="nav-item {{ request()->routeIs('eselon2.reviewIdp') ? 'active' : '' }}">
        <span class="nav-icon">🔍</span> Review IDP
    </a>
    <a href="{{ route('eselon2.penetapanBangkom') }}" class="nav-item {{ request()->routeIs('eselon2.penetapanBangkom') ? 'active' : '' }}">
        <span class="nav-icon">📅</span> Bangkom Unit
    </a>

{{-- ══════════════════════════════════════════
     DEPUTI — Deputi Kepala Eselon I
══════════════════════════════════════════ --}}
@elseif($activeRole === 'deputi')
    <div class="nav-section-label">Menu</div>
    <a href="{{ route('executive.insights') }}" class="nav-item {{ request()->routeIs('executive.insights') ? 'active' : '' }}">
        <span class="nav-icon">📊</span> Executive Insights
    </a>
    <a href="{{ route('executive.talentFinder') }}" class="nav-item {{ request()->routeIs('executive.talentFinder') ? 'active' : '' }}">
        <span class="nav-icon">🔍</span> Talent Finder Eselon I
    </a>
    <a href="{{ route('executive.ncc') }}" class="nav-item {{ request()->routeIs('executive.ncc') ? 'active' : '' }}">
        <span class="nav-icon">🌐</span> National Control Centre
    </a>
    <a href="{{ route('executive.reviewEselon2') }}" class="nav-item {{ request()->routeIs('executive.reviewEselon2') ? 'active' : '' }}">
        <span class="nav-icon">✅</span> Review IDP Eselon II
    </a>
    <a href="{{ route('executive.profil360') }}" class="nav-item {{ request()->routeIs('executive.profil360') ? 'active' : '' }}">
        <span class="nav-icon">👤</span> Profil 360 Pegawai
    </a>

{{-- ══════════════════════════════════════════
     SESMA — Sekretaris Utama
══════════════════════════════════════════ --}}
@elseif($activeRole === 'sesma')
    <div class="nav-section-label">Menu</div>
    <a href="{{ route('executive.insights') }}" class="nav-item {{ request()->routeIs('executive.insights') ? 'active' : '' }}">
        <span class="nav-icon">📊</span> Executive Insights
    </a>
    <a href="{{ route('executive.sestamaBrief') }}" class="nav-item {{ request()->routeIs('executive.sestamaBrief') ? 'active' : '' }}">
        <span class="nav-icon">📝</span> Sestama Strategic Brief
    </a>
    <a href="{{ route('executive.talentFinder') }}" class="nav-item {{ request()->routeIs('executive.talentFinder') ? 'active' : '' }}">
        <span class="nav-icon">🔍</span> Talent Finder
    </a>
    <a href="{{ route('executive.ncc') }}" class="nav-item {{ request()->routeIs('executive.ncc') ? 'active' : '' }}">
        <span class="nav-icon">🌐</span> National Control Centre
    </a>
    <a href="{{ route('executive.reviewEselon2') }}" class="nav-item {{ request()->routeIs('executive.reviewEselon2') ? 'active' : '' }}">
        <span class="nav-icon">✅</span> Review IDP Eselon II
    </a>
    <a href="{{ route('executive.profil360') }}" class="nav-item {{ request()->routeIs('executive.profil360') ? 'active' : '' }}">
        <span class="nav-icon">👤</span> Profil 360 Nasional
    </a>

{{-- ══════════════════════════════════════════
     ADMIN — Admin Sistem
══════════════════════════════════════════ --}}
@elseif($activeRole === 'admin')
    <div class="nav-section-label">Menu</div>
    <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
        <span class="nav-icon">👥</span> User & Role CRUD
    </a>
    <a href="{{ route('admin.setara') }}" class="nav-item {{ request()->routeIs('admin.setara') ? 'active' : '' }}">
        <span class="nav-icon">🔗</span> SETARA Monitor
    </a>
    <a href="{{ route('admin.auditTrail') }}" class="nav-item {{ request()->routeIs('admin.auditTrail') ? 'active' : '' }}">
        <span class="nav-icon">📋</span> System Logs
    </a>
    <a href="{{ route('admin.reference') }}" class="nav-item {{ request()->routeIs('admin.reference') ? 'active' : '' }}">
        <span class="nav-icon">⚙️</span> Reference & Parameter
    </a>
    <a href="{{ route('admin.testApiSmile') }}" class="nav-item {{ request()->routeIs('admin.testApiSmile') ? 'active' : '' }}">
        <span class="nav-icon">🔌</span> Uji Coba API SMILE
    </a>
@endif

</nav>

<div class="sidebar-footer">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout-btn">
            <span>🚪</span> Keluar
        </button>
    </form>
</div>
