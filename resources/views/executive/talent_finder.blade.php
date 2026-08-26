@extends('layouts.app')
@section('title', 'Talent Finder - Executive')
@section('header_title', 'Talent Finder Nasional')

@section('content')
<div class="page-header">
    <h1>🔍 Talent Finder Nasional</h1>
    <p class="text-muted" style="margin-top: 8px;">Cari pegawai unggulan berdasarkan kriteria kompetensi atau sertifikasi lintas unit kerja nasional.</p>
</div>

<!-- Primary Tabs -->
<div style="display: flex; gap: 12px; margin-bottom: 24px;">
    <button id="btn-kompetensi" class="btn {{ request('type', 'Kompetensi') === 'Kompetensi' ? 'btn-primary' : 'btn-neutral' }}" onclick="selectType('Kompetensi')">
        <span style="display: flex; align-items: center; gap: 8px;">
            <span>🧠</span>
            Kompetensi
        </span>
    </button>
    <button id="btn-sertifikasi" class="btn {{ request('type') === 'Sertifikasi' ? 'btn-primary' : 'btn-neutral' }}" onclick="selectType('Sertifikasi')">
        <span style="display: flex; align-items: center; gap: 8px;">
            <span>✅</span>
            Sertifikasi
        </span>
    </button>
</div>

<form method="GET" action="{{ route('executive.talentFinder') }}" id="filter-form">
    <input type="hidden" name="type" id="type-input" value="{{ request('type', 'Kompetensi') }}">

    <div class="card mb-4" style="position: relative; z-index: 100;">
        <div class="card-title" style="margin-bottom: 20px;">Kriteria Pencarian</div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
            <!-- Dynamic Filter (Kompetensi OR Sertifikasi) -->
            <div id="filter-kompetensi-container" style="display: {{ request('type', 'Kompetensi') === 'Kompetensi' ? 'block' : 'none' }};">
                <label style="font-weight: 600; font-size: 13px; color: var(--text-secondary); margin-bottom: 8px; display: block;">Jenis Kompetensi</label>
                <div class="dropdown-container">
                    <button type="button" class="form-control dropdown-btn" onclick="toggleDropdown('kompetensi-dropdown')">
                        <span>Pilih Kompetensi...</span>
                        <span style="font-size: 12px; opacity: 0.6;">▼</span>
                    </button>
                    <div class="dropdown-menu" id="kompetensi-dropdown">
                        <label class="dropdown-item">
                            <input type="checkbox" class="custom-checkbox" name="kompetensi[]" value="All Kompetensi" onchange="toggleAllDropdown('kompetensi')" {{ in_array('All Kompetensi', request('kompetensi', [])) ? 'checked' : '' }}>
                            <span>Semua Kompetensi</span>
                        </label>
                        @foreach($allCompetencies as $komp)
                        <label class="dropdown-item kompetensi-item">
                            <input type="checkbox" class="custom-checkbox" name="kompetensi[]" value="{{ $komp }}" onchange="updateChips('kompetensi-chips', this)" {{ in_array($komp, request('kompetensi', [])) ? 'checked' : '' }}>
                            <span>{{ $komp }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="chip-group chips-container" id="kompetensi-chips"></div>
            </div>

            <div id="filter-sertifikasi-container" style="display: {{ request('type') === 'Sertifikasi' ? 'block' : 'none' }};">
                <label style="font-weight: 600; font-size: 13px; color: var(--text-secondary); margin-bottom: 8px; display: block;">Jenis Sertifikasi</label>
                @if(count($certifications) > 0)
                <div class="dropdown-container">
                    <button type="button" class="form-control dropdown-btn" onclick="toggleDropdown('sertifikasi-dropdown')">
                        <span>Pilih Sertifikasi...</span>
                        <span style="font-size: 12px; opacity: 0.6;">▼</span>
                    </button>
                    <div class="dropdown-menu" id="sertifikasi-dropdown">
                        @foreach($certifications as $cert)
                        <label class="dropdown-item">
                            <input type="checkbox" class="custom-checkbox" name="sertifikasi[]" value="{{ $cert }}" onchange="updateChips('sertifikasi-chips', this)" {{ in_array($cert, request('sertifikasi', [])) ? 'checked' : '' }}>
                            <span>{{ $cert }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="chip-group chips-container" id="sertifikasi-chips"></div>
                @else
                <p class="text-muted" style="margin:0; font-size: 13px;">Belum ada data pemetaan sertifikasi.</p>
                @endif
            </div>

            <!-- Filter Jabatan -->
            <div>
                <label style="font-weight: 600; font-size: 13px; color: var(--text-secondary); margin-bottom: 8px; display: block;">Jabatan</label>
                <div class="dropdown-container">
                    <button type="button" class="form-control dropdown-btn" onclick="toggleDropdown('jabatan-dropdown')">
                        <span>Pilih Jabatan...</span>
                        <span style="font-size: 12px; opacity: 0.6;">▼</span>
                    </button>
                    <div class="dropdown-menu" id="jabatan-dropdown">
                        <label class="dropdown-item">
                            <input type="checkbox" class="custom-checkbox" name="jabatan[]" value="All Jabatan" onchange="toggleAllDropdown('jabatan')" {{ in_array('All Jabatan', request('jabatan', [])) ? 'checked' : '' }}>
                            <span>Semua Jabatan</span>
                        </label>
                        @php
                            $jabatans = ['Auditor Terampil', 'Auditor Mahir', 'Auditor Ahli Pertama', 'Auditor Ahli Muda', 'Auditor Ahli Madya', 'Auditor Ahli Utama'];
                        @endphp
                        @foreach($jabatans as $jab)
                        <label class="dropdown-item jabatan-item">
                            <input type="checkbox" class="custom-checkbox" name="jabatan[]" value="{{ $jab }}" onchange="updateChips('jabatan-chips', this)" {{ in_array($jab, request('jabatan', [])) ? 'checked' : '' }}>
                            <span>{{ $jab }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="chip-group chips-container" id="jabatan-chips"></div>
            </div>

            <!-- Filter Unit Kerja Eselon 1 -->
            @if($showEselon1)
            <div>
                <label style="font-weight: 600; font-size: 13px; color: var(--text-secondary); margin-bottom: 8px; display: block;">Unit Kerja Eselon 1</label>
                <div class="dropdown-container">
                    <button type="button" class="form-control dropdown-btn" onclick="toggleDropdown('eselon1-dropdown')">
                        <span>Pilih Unit Kerja Eselon 1...</span>
                        <span style="font-size: 12px; opacity: 0.6;">▼</span>
                    </button>
                    <div class="dropdown-menu" id="eselon1-dropdown">
                        <label class="dropdown-item">
                            <input type="checkbox" class="custom-checkbox" name="eselon1[]" value="All Eselon 1" onchange="toggleAllDropdown('eselon1', 'All Eselon 1')" {{ in_array('All Eselon 1', request('eselon1', [])) ? 'checked' : '' }}>
                            <span>Semua Unit Kerja Eselon 1</span>
                        </label>
                        @foreach($eselon1Options as $e1)
                        <label class="dropdown-item eselon1-item">
                            <input type="checkbox" class="custom-checkbox" name="eselon1[]" value="{{ $e1 }}" onchange="updateChips('eselon1-chips', this)" {{ in_array($e1, request('eselon1', [])) ? 'checked' : '' }}>
                            <span>{{ $e1 }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="chip-group chips-container" id="eselon1-chips"></div>
            </div>
            @endif

            <!-- Filter Unit Kerja Eselon 2 -->
            @if($showEselon2)
            <div>
                <label style="font-weight: 600; font-size: 13px; color: var(--text-secondary); margin-bottom: 8px; display: block;">Unit Kerja Eselon 2</label>
                <div class="dropdown-container">
                    <button type="button" class="form-control dropdown-btn" onclick="toggleDropdown('eselon2-dropdown')">
                        <span>Pilih Unit Kerja Eselon 2...</span>
                        <span style="font-size: 12px; opacity: 0.6;">▼</span>
                    </button>
                    <div class="dropdown-menu" id="eselon2-dropdown">
                        <label class="dropdown-item">
                            <input type="checkbox" class="custom-checkbox" name="eselon2[]" value="All Eselon 2" onchange="toggleAllDropdown('eselon2', 'All Eselon 2')" {{ in_array('All Eselon 2', request('eselon2', [])) ? 'checked' : '' }}>
                            <span>Semua Unit Kerja Eselon 2</span>
                        </label>
                        @foreach($eselon2Options as $e2)
                        <label class="dropdown-item eselon2-item">
                            <input type="checkbox" class="custom-checkbox" name="eselon2[]" value="{{ $e2 }}" onchange="updateChips('eselon2-chips', this)" {{ in_array($e2, request('eselon2', [])) ? 'checked' : '' }}>
                            <span>{{ $e2 }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="chip-group chips-container" id="eselon2-chips"></div>
            </div>
            @endif
        </div>
        
        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 16px;">
            <a href="{{ route('executive.talentFinder') }}" class="btn btn-neutral">Reset</a>
            <button type="submit" class="btn btn-primary">🔍 Cari Talenta</button>
        </div>
    </div>
</form>

@if(request('type'))
<div class="card">
    <div class="card-title mb-4" style="display: flex; align-items: center; justify-content: space-between;">
        <span>Hasil Pencarian: {{ request('type') }}</span>
        <span class="badge badge-primary" style="font-size: 12px; padding: 4px 8px; border-radius: 4px;">{{ count($employees) }} Pegawai Ditemukan</span>
    </div>

    @if(count($employees) > 0)
    <div style="overflow-x: auto;">
        <table class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th style="padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.1);">Pegawai</th>
                    <th style="padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.1);">Jabatan</th>
                    <th style="padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.1);">Unit Kerja</th>
                    @if(request('type') === 'Kompetensi')
                    <th style="padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center;">Rata-rata Nilai</th>
                    @endif
                    <th style="padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.1); width: 100px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $emp)
                <tr>
                    <td style="padding: 16px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(59,130,246,0.15); color: #60a5fa; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px;">
                                {{ $emp->initial ?? substr($emp->name, 0, 2) }}
                            </div>
                            <div>
                                <div style="font-weight: 600; color: #fff; font-size: 14px;">{{ $emp->name }}</div>
                                <div style="font-size: 12px; color: var(--text-secondary);">NIP: {{ $emp->id }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 16px; border-bottom: 1px solid rgba(255,255,255,0.05);"><span style="font-size: 13px; color: #ddd;">{{ $emp->role }}</span></td>
                    <td style="padding: 16px; border-bottom: 1px solid rgba(255,255,255,0.05);"><span style="font-size: 13px; color: #ddd;">{{ $emp->unit }}</span></td>
                    @if(request('type') === 'Kompetensi')
                    <td style="padding: 16px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: center;">
                        <span style="font-weight: 700; color: #4ade80; font-size: 15px;">{{ $emp->rata_rata_nilai ? number_format($emp->rata_rata_nilai, 2, ',', '.') : '-' }}</span>
                    </td>
                    @endif
                    <td style="padding: 16px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: center;">
                        <a href="{{ route('executive.profil360', ['q' => $emp->id]) }}" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-weight: 600; padding: 6px 14px; border-radius: 6px; box-shadow: 0 2px 4px rgba(59,130,246,0.2);">
                            <span style="font-size: 13px;">👤</span> Lihat Profil
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="margin-top: 16px; display: flex; justify-content: center;">
        {{ $employees->links('pagination::bootstrap-4') }}
    </div>
    @else
    <div style="text-align:center; padding: 48px 24px;">
        <div style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;">🔍</div>
        <h3 style="color: #fff; font-size: 16px; margin-bottom: 8px;">Tidak Ada Pegawai Ditemukan</h3>
        <p style="color: var(--text-secondary); max-width: 420px; margin: 0 auto; font-size: 13px;">Coba sesuaikan kembali filter kompetensi, sertifikasi, atau jabatan Anda.</p>
    </div>
    @endif
</div>
@endif
@endsection

@push('styles')
<style>
    /* DROPDOWN */
    .dropdown-container {
        position: relative;
        width: 100%;
    }
    .dropdown-btn {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        background: rgba(255,255,255,0.02);
        padding: 8px 12px;
        color: #e2e8f0;
    }
    .dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        max-height: 250px;
        overflow-y: auto;
        z-index: 50;
        margin-top: 4px;
        background: #1e293b;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 6px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
    }
    .dropdown-menu.active {
        display: block;
    }
    .dropdown-item {
        display: flex;
        align-items: center;
        padding: 10px 16px;
        cursor: pointer;
        transition: background-color 0.2s;
        color: #e2e8f0;
        font-size: 13px;
        margin: 0;
    }
    .dropdown-item:hover {
        background: rgba(255,255,255,0.05);
        color: #fff;
    }

    /* CUSTOM CHECKBOX */
    .custom-checkbox {
        appearance: none;
        background-color: transparent;
        margin: 0;
        margin-right: 12px;
        font: inherit;
        color: currentColor;
        width: 1.15em;
        height: 1.15em;
        border: 2px solid #64748b;
        border-radius: 0.15em;
        display: grid;
        place-content: center;
        cursor: pointer;
    }
    .custom-checkbox::before {
        content: "";
        width: 0.65em;
        height: 0.65em;
        transform: scale(0);
        transition: 120ms transform ease-in-out;
        box-shadow: inset 1em 1em #3b82f6;
        background-color: #3b82f6;
        transform-origin: center;
        clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%);
    }
    .custom-checkbox:checked::before {
        transform: scale(1);
    }
    .custom-checkbox:checked {
        border-color: #3b82f6;
    }

    /* CHIPS */
    .chip-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }
    .chip-item {
        display: flex;
        align-items: center;
        gap: 6px;
        background: rgba(59,130,246,0.15);
        border: 1px solid rgba(59,130,246,0.3);
        color: #93c5fd;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .chip-item button {
        background: transparent;
        border: none;
        color: #60a5fa;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        margin: 0;
    }
    .chip-item button:hover {
        color: #f87171;
    }
</style>
@endpush

@push('scripts')
<script>
    function selectType(type) {
        document.getElementById('type-input').value = type;
        
        const btnK = document.getElementById('btn-kompetensi');
        const btnS = document.getElementById('btn-sertifikasi');
        
        if (type === 'Kompetensi') {
            btnK.classList.remove('btn-neutral');
            btnK.classList.add('btn-primary');
            btnS.classList.remove('btn-primary');
            btnS.classList.add('btn-neutral');
            
            document.getElementById('filter-kompetensi-container').style.display = 'block';
            document.getElementById('filter-sertifikasi-container').style.display = 'none';
        } else {
            btnS.classList.remove('btn-neutral');
            btnS.classList.add('btn-primary');
            btnK.classList.remove('btn-primary');
            btnK.classList.add('btn-neutral');
            
            document.getElementById('filter-kompetensi-container').style.display = 'none';
            document.getElementById('filter-sertifikasi-container').style.display = 'block';
        }
    }

    function toggleDropdown(dropdownId) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu.id !== dropdownId) {
                menu.classList.remove('active');
            }
        });
        document.getElementById(dropdownId).classList.toggle('active');
    }

    document.addEventListener('click', function(event) {
        if (!event.target.closest('.dropdown-container')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('active');
            });
        }
    });

    function refreshGroupChips(group) {
        const containerId = group + '-chips';
        const container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = '';
        
        const allCheckbox = document.querySelector(`input[name="${group}[]"][value^="All "]`);
        if (allCheckbox && allCheckbox.checked) {
            renderChip(containerId, allCheckbox.value);
        } else {
            const itemCheckboxes = document.querySelectorAll(`.${group}-item input:checked`);
            itemCheckboxes.forEach(cb => {
                renderChip(containerId, cb.value);
            });
        }
    }

    function toggleAllDropdown(group, allValueOverride) {
        const allValue = allValueOverride || "All " + group.charAt(0).toUpperCase() + group.slice(1);
        const allCheckbox = document.querySelector(`input[name="${group}[]"][value="${allValue}"]`);
        const itemCheckboxes = document.querySelectorAll(`.${group}-item input`);
        
        if (allCheckbox && allCheckbox.checked) {
            itemCheckboxes.forEach(cb => cb.checked = true);
        } else {
            itemCheckboxes.forEach(cb => cb.checked = false);
        }
        refreshGroupChips(group);
    }

    function updateChips(containerId, checkbox) {
        const group = containerId.replace('-chips', '');
        const allCheckbox = document.querySelector(`input[name="${group}[]"][value^="All "]`);
        
        if (allCheckbox && allCheckbox.checked && !checkbox.value.startsWith('All ')) {
            allCheckbox.checked = false;
        }
        refreshGroupChips(group);
    }

    function updateRadioChips(containerId, radio, label) {
        const container = document.getElementById(containerId);
        if (container) container.innerHTML = '';
        if (radio.checked) {
            renderChip(containerId, label, radio.value);
        }
    }

    function renderChip(containerId, value, actualValue = null) {
        const container = document.getElementById(containerId);
        if(!container || container.querySelector(`[data-value="${value}"]`)) return;

        const chip = document.createElement('div');
        chip.className = 'chip-item';
        chip.setAttribute('data-value', value);
        if (actualValue !== null) {
            chip.setAttribute('data-actual-value', actualValue);
        }
        
        chip.innerHTML = `
            <span>${value}</span>
            <button type="button" onclick="removeChipAndUncheck('${containerId}', '${value}')">
                <span style="font-size: 14px; font-weight: bold;">✕</span>
            </button>
        `;
        
        container.appendChild(chip);
    }

    function removeChipAndUncheck(containerId, value) {
        const card = document.getElementById(containerId).closest('div').querySelector('.dropdown-menu');
        if (card) {
            const checkbox = Array.from(card.querySelectorAll('input[type="checkbox"]')).find(cb => cb.value === value);
            if (checkbox) {
                checkbox.checked = false;
                const group = containerId.replace('-chips', '');
                refreshGroupChips(group);
            }

            const radio = Array.from(card.querySelectorAll('input[type="radio"]')).find(r => r.value === value || r.nextElementSibling.innerText.trim() === value);
            if (radio) {
                radio.checked = false;
                const defaultRadio = card.querySelector('input[type="radio"][value=""]');
                if (defaultRadio) {
                    defaultRadio.checked = true;
                    renderChip(containerId, 'Semua Unit Kerja', '');
                }
                const container = document.getElementById(containerId);
                const chip = container.querySelector(`[data-value="${value}"]`);
                if (chip) container.removeChild(chip);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        ['kompetensi', 'sertifikasi', 'jabatan', 'eselon1', 'eselon2'].forEach(prefix => {
            const dropdown = document.getElementById(prefix + '-dropdown');
            const chipsContainer = prefix + '-chips';
            if (dropdown) {
                refreshGroupChips(prefix);
                
                dropdown.querySelectorAll('input[type="radio"]').forEach(r => {
                    if(r.checked) {
                        const label = r.nextElementSibling.innerText.trim();
                        renderChip(chipsContainer, label, r.value);
                    }
                });
            }
        });
    });
</script>
@endpush
