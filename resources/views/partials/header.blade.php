{{-- Header partial --}}
<div class="header-title">@yield('header_title', 'ATLAS v4.0')</div>
<div class="header-meta">
    {{ now()->isoFormat('dddd, D MMMM YYYY') }}
    &nbsp;&bull;&nbsp;
    <strong>{{ session('active_role', auth()->user()->role ?? '') }}</strong>
</div>
