
@extends('layout.admin')

@section('title', 'Data Peserta Offline | Admin')

@section('content')
<style>
    .offline-page {
        --primary: #3157d5;
        --text: #202b3c;
        --muted: #758195;
        --border: #e8edf4;
        color: var(--text);
        font-size: 14px;
    }

    .offline-page .page-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .offline-page .page-title {
        margin: 0 0 6px;
        font-size: 25px;
        font-weight: 750;
    }

    .offline-page .page-subtitle {
        margin: 0;
        color: var(--muted);
        font-size: 13px;
    }

    .offline-page .btn-add {
        padding: 11px 17px;
        border-radius: 10px;
        background: var(--primary);
        color: white;
        font-weight: 600;
        text-decoration: none;
    }

    .offline-page .stat-card,
    .offline-page .main-panel {
        border: 1px solid var(--border);
        border-radius: 15px;
        background: white;
        box-shadow: 0 3px 12px rgba(29, 42, 68, .025);
    }

    .offline-page .stat-card {
        height: 100%;
        padding: 20px;
    }

    .offline-page .stat-label {
        color: var(--muted);
        font-size: 12px;
        font-weight: 600;
    }

    .offline-page .stat-number {
        margin: 12px 0 5px;
        font-size: 29px;
        font-weight: 750;
    }

    .offline-page .stat-description {
        margin: 0;
        color: #929cad;
        font-size: 11px;
    }

    .offline-page .panel-header {
        padding: 21px 23px;
        border-bottom: 1px solid var(--border);
    }

    .offline-page .panel-title {
        margin: 0 0 5px;
        font-size: 16px;
        font-weight: 700;
    }

    .offline-page .toolbar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        padding: 18px 23px;
    }

    .offline-page .search-input,
    .offline-page .filter-select {
        height: 40px;
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: white;
        font-size: 12px;
    }

    .offline-page .search-input {
        flex: 1 1 230px;
        min-width: 0;
    }

    .offline-page .filter-select {
        min-width: 145px;
        max-width: 100%;
    }

    .offline-page .btn-reset {
        height: 40px;
        padding: 0 12px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: white;
        font-size: 12px;
    }

    .offline-page .table-container {
        padding: 0 23px 22px;
    }

    .offline-page table.dataTable {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0;
    }

    .offline-page #dataTableOffline thead th {
        padding: 14px 12px;
        border-top: 1px solid #edf0f5;
        border-bottom: 1px solid #edf0f5;
        background: #f8fafc;
        color: #758195;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .offline-page #dataTableOffline tbody td {
        padding: 14px 12px;
        border-bottom: 1px solid #edf0f5;
        color: #526075;
        font-size: 12px;
        vertical-align: middle;
    }

    .offline-page .primary-text {
        color: #273449;
        font-weight: 650;
    }

    .offline-page .muted-text {
        color: #929cad;
    }

    .offline-page .status-badge,
    .offline-page .type-badge {
        display: inline-block;
        padding: 6px 9px;
        border-radius: 7px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .offline-page .status-verified {
        background: #e8f8ef;
        color: #168451;
    }

    .offline-page .status-pending {
        background: #fff4e5;
        color: #b97513;
    }

    .offline-page .status-rejected {
        background: #feecec;
        color: #bd3434;
    }

    .offline-page .type-science {
        background: #eef2ff;
        color: #3157d5;
    }

    .offline-page .type-futsal {
        background: #fff4e5;
        color: #b97513;
    }

    .offline-page .phone-link {
        color: #168451;
        text-decoration: none;
        white-space: nowrap;
    }

    .offline-page .phone-link:hover {
        text-decoration: underline;
    }

    .offline-page .amount {
        color: #273449;
        font-weight: 700;
        white-space: nowrap;
    }

    .offline-page .btn-delete {
        padding: 6px 9px;
        border: 1px solid #f1cccc;
        border-radius: 7px;
        background: #fff5f5;
        color: #bd3434;
        font-size: 11px;
    }

    .offline-page .collective-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 290px), 1fr));
        gap: 14px;
        padding: 20px 23px 23px;
    }

    .offline-page .collective-card {
        min-width: 0;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        background: #fff;
    }

    .offline-page .collective-card-heading {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .offline-page .collective-icon {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #eef2ff;
        color: #3157d5;
    }

    .offline-page .collective-school {
        margin: 0 0 4px;
        color: #273449;
        font-size: 14px;
        font-weight: 750;
        overflow-wrap: anywhere;
    }

    .offline-page .collective-meta {
        color: var(--muted);
        font-size: 11px;
    }

    .offline-page .collective-info {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin: 14px 0;
    }

    .offline-page .collective-chip {
        display: inline-block;
        padding: 5px 8px;
        border-radius: 6px;
        background: #f3f6fb;
        color: #526075;
        font-size: 10px;
        font-weight: 650;
    }

    .offline-page .collective-details {
        margin-top: 8px;
        font-size: 11px;
    }

    .offline-page .collective-details summary {
        display: inline-block;
        padding: 6px 9px;
        border: 1px solid #dbe4f0;
        border-radius: 7px;
        background: #f8fafc;
        color: #3157d5;
        cursor: pointer;
        font-weight: 650;
        list-style-position: inside;
    }

    .offline-page .collective-member-list {
        min-width: 190px;
        margin-top: 8px;
        padding: 9px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: #fff;
    }

    .offline-page .collective-member {
        display: grid;
        grid-template-columns: 18px minmax(90px, 1fr) auto;
        gap: 7px;
        padding: 7px 0;
        border-bottom: 1px solid #edf0f5;
        align-items: start;
    }

    .offline-page .collective-member:last-child {
        border-bottom: 0;
    }

    .offline-page .collective-member-number,
    .offline-page .collective-member-field {
        color: var(--muted);
    }

    .offline-page .collective-member-name {
        color: var(--text);
        overflow-wrap: anywhere;
    }

    .offline-page .empty-state {
        padding: 42px 15px;
        color: #8994a5;
        text-align: center;
    }

    @media (max-width: 767.98px) {
        .offline-page .page-title {
            font-size: 22px;
        }

        .offline-page .toolbar {
            padding: 15px;
        }

        .offline-page .table-container {
            padding: 0 12px 16px;
        }

        .offline-page .search-input {
            flex-basis: 100%;
        }

        .offline-page .filter-select {
            flex: 1 1 120px;
            min-width: 0;
        }
    }
</style>

<div class="offline-page">
    <div class="page-heading">
        <div>
            <h1 class="page-title">Data Peserta Offline</h1>
            <p class="page-subtitle">
                Data pendaftaran yang dicatat langsung oleh panitia dan tersimpan di Neon PostgreSQL.
            </p>
        </div>

        <a href="{{ route('ofline.create') }}" class="btn btn-add">
            <i class="fa-solid fa-plus mr-1"></i>
            Tambah Pendaftaran
        </a>
    </div>

    @php
        $totalPendaftaran = $registrations->count();

        $totalPeserta = $registrations
            ->whereIn('type', ['SELF', 'COLLECTIVE'])
            ->sum(function ($item) {
                return $item->participant_name !== null ? 1 : 0;
            });

        $totalTim = $registrations
            ->where('type', 'FUTSAL')
            ->count();

        $totalVerified = $registrations
            ->where('payment_status', 'VERIFIED')
            ->count();

        $totalPendapatan = $registrations
            ->where('payment_status', 'VERIFIED')
            ->sum('amount');

        $collectiveRegistrations = $registrations->where('type', 'COLLECTIVE');
        $otherRegistrations = $registrations->where('type', '!=', 'COLLECTIVE');
    @endphp

    <div class="row mb-4">
        <div class="col-12 col-md-4 mb-3">
            <div class="stat-card">
                <div class="stat-label">Total Pendaftaran Offline</div>
                <div class="stat-number">{{ $totalPendaftaran }}</div>
                <p class="stat-description">Seluruh transaksi pendaftaran offline</p>
            </div>
        </div>

        <div class="col-12 col-md-4 mb-3">
            <div class="stat-card">
                <div class="stat-label">Pembayaran Terverifikasi</div>
                <div class="stat-number">{{ $totalVerified }}</div>
                <p class="stat-description">Pendaftaran dengan status VERIFIED</p>
            </div>
        </div>

        <div class="col-12 col-md-4 mb-3">
            <div class="stat-card">
                <div class="stat-label">Total Pembayaran Terverifikasi</div>
                <div class="stat-number" style="font-size:24px">
                    Rp{{ number_format($totalPendapatan, 0, ',', '.') }}
                </div>
                <p class="stat-description">Akumulasi nominal pembayaran VERIFIED</p>
            </div>
        </div>
    </div>

    <div class="main-panel mb-4">
        <div class="panel-header">
            <h2 class="panel-title">Science Kolektif Offline</h2>
            <p class="page-subtitle">
                Klik nama sekolah untuk membuka daftar anggota pada pendaftaran kolektif tersebut.
            </p>
        </div>

        @if ($collectiveRegistrations->isEmpty())
            <div class="empty-state">
                <i class="fa-solid fa-users fa-2x mb-3"></i>
                <p class="mb-1"><strong>Belum ada pendaftaran Science Kolektif offline</strong></p>
                <p class="mb-0">Pendaftaran kolektif yang dicatat panitia akan muncul di sini.</p>
            </div>
        @else
            <div class="collective-grid">
                @foreach ($collectiveRegistrations as $item)
                    @php
                        $schoolTitle = $item->participant_school ?: 'Sekolah belum tersedia';
                        $collectiveStatus = strtoupper($item->payment_status ?? 'PENDING');
                        $fields = collect($item->participants)->pluck('field')->filter()->unique()->values();
                    @endphp

                    <article class="collective-card">
                        <div class="collective-card-heading">
                            <div class="collective-icon">
                                <i class="fa-solid fa-school"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <h3 class="collective-school">{{ $schoolTitle }}</h3>
                                <div class="collective-meta">
                                    {{ $item->participants->count() }} peserta
                                    · Pendaftaran #{{ $item->registration_id }}
                                </div>
                            </div>
                        </div>

                        <div class="collective-info">
                            @foreach ($fields as $fieldName)
                                <span class="collective-chip">{{ $fieldName }}</span>
                            @endforeach

                            <span class="collective-chip">
                                Rp{{ number_format((int) ($item->amount ?? 0), 0, ',', '.') }}
                            </span>

                            <span class="status-badge {{ $collectiveStatus === 'VERIFIED' ? 'status-verified' : ($collectiveStatus === 'REJECTED' ? 'status-rejected' : 'status-pending') }}">
                                {{ $collectiveStatus }}
                            </span>
                        </div>

                        <details class="collective-details">
                            <summary>Lihat nama anggota</summary>
                            <div class="collective-member-list">
                                @forelse ($item->participants as $participantIndex => $participant)
                                    <div class="collective-member">
                                        <span class="collective-member-number">{{ $participantIndex + 1 }}.</span>
                                        <span class="collective-member-name">
                                            {{ $participant->fullName ?: 'Nama belum tersedia' }}
                                        </span>
                                        <span class="collective-member-field">
                                            {{ $participant->field ?: '-' }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="muted-text">Data anggota tidak ditemukan.</div>
                                @endforelse
                            </div>
                        </details>

                        <div class="collective-meta mt-3">
                            WhatsApp:
                            @if ($item->whatsapp)
                                @php
                                    $collectiveWa = preg_replace('/[^0-9]/', '', $item->whatsapp);
                                    if (substr($collectiveWa, 0, 1) === '0') {
                                        $collectiveWa = '62' . substr($collectiveWa, 1);
                                    }
                                @endphp
                                <a class="phone-link" href="https://wa.me/{{ $collectiveWa }}" target="_blank" rel="noopener noreferrer">
                                    {{ $item->whatsapp }}
                                </a>
                            @else
                                -
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    <div class="main-panel">
        <div class="panel-header">
            <h2 class="panel-title">Sains Individu dan Futsal Offline</h2>
            <p class="page-subtitle">
                Data peserta individu dan tim futsal ditampilkan terpisah dari Science Kolektif.
            </p>
        </div>

        @if (session('success'))
            <div class="alert alert-success m-3">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger m-3">
                {{ session('error') }}
            </div>
        @endif

        <div class="toolbar">
            <input
                type="search"
                id="offlineSearch"
                class="search-input"
                placeholder="Cari nama, tim, sekolah, atau WhatsApp..."
            >

            <select id="offlineTypeFilter" class="filter-select">
                <option value="">Semua Kategori</option>
                <option value="SELF">Sains Individu</option>
                <option value="FUTSAL">Futsal</option>
            </select>

            <select id="offlineStatusFilter" class="filter-select">
                <option value="">Semua Status</option>
                <option value="VERIFIED">VERIFIED</option>
                <option value="PENDING">PENDING</option>
                <option value="REJECTED">REJECTED</option>
            </select>

            <button type="button" id="offlineResetFilter" class="btn-reset">
                Reset
            </button>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table" id="dataTableOffline">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Kategori</th>
                            <th>Nama Peserta / Tim</th>
                            <th>Sekolah</th>
                            <th>Bidang</th>
                            <th>WhatsApp</th>
                            <th>Nominal</th>
                            <th>Status Pembayaran</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($otherRegistrations as $item)
                            @php
                                $isFutsal = $item->type === 'FUTSAL';
                                $name = $isFutsal
                                    ? $item->team_name
                                    : ($item->type === 'COLLECTIVE'
                                        ? $item->participant_school
                                        : $item->participant_name);

                                $school = $isFutsal
                                    ? $item->team_school
                                    : $item->participant_school;

                                $status = strtoupper($item->payment_status ?? 'PENDING');

                                $typeLabel = [
                                    'SELF' => 'Sains Individu',
                                    'COLLECTIVE' => 'Sains Kolektif',
                                    'FUTSAL' => 'Futsal',
                                ][$item->type] ?? $item->type;
                            @endphp

                            <tr
                                data-search="{{ strtolower(($name ?? '') . ' ' . ($school ?? '') . ' ' . ($item->whatsapp ?? '') . ' ' . $typeLabel) }}"
                                data-type="{{ $item->type }}"
                                data-status="{{ $status }}"
                            >
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <span class="type-badge {{ $isFutsal ? 'type-futsal' : 'type-science' }}">
                                        {{ $typeLabel }}
                                    </span>
                                </td>

                                <td>
                                    <span class="primary-text">
                                        {{ $name ?: 'Data peserta tidak ditemukan' }}
                                    </span>

                                </td>

                                <td>{{ $school ?: '-' }}</td>

                                <td>
                                    @if ($isFutsal)
                                        <span class="muted-text">Tidak berlaku</span>
                                    @else
                                        {{ $item->field ?: '-' }}
                                    @endif
                                </td>

                                <td>
                                    @if ($item->whatsapp)
                                        @php
                                            $wa = preg_replace('/[^0-9]/', '', $item->whatsapp);

                                            if (substr($wa, 0, 1) === '0') {
                                                $wa = '62' . substr($wa, 1);
                                            }
                                        @endphp

                                        <a
                                            class="phone-link"
                                            href="https://wa.me/{{ $wa }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <i class="fa-brands fa-whatsapp"></i>
                                            {{ $item->whatsapp }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="amount">
                                    Rp{{ number_format((int) $item->amount, 0, ',', '.') }}
                                </td>

                                <td>
                                    @if ($status === 'VERIFIED')
                                        <span class="status-badge status-verified">VERIFIED</span>
                                    @elseif ($status === 'REJECTED')
                                        <span class="status-badge status-rejected">REJECTED</span>
                                    @else
                                        <span class="status-badge status-pending">PENDING</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $item->createdAt
                                        ? \Carbon\Carbon::parse($item->createdAt)->format('d/m/Y H:i')
                                        : '-' }}
                                </td>

                                <td>
                                    <form
                                        action="{{ route('ofline.destroy', $item->registration_id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus pendaftaran offline ini? Semua data terkait juga akan dihapus.');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn-delete">
                                            <i class="fa-solid fa-trash"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($otherRegistrations->isEmpty())
                    <div class="empty-state">
                        <i class="fa-solid fa-inbox fa-2x mb-3"></i>
                        <p class="mb-1"><strong>Belum ada pendaftaran offline</strong></p>
                        <p class="mb-0">
                            Gunakan tombol Tambah Pendaftaran untuk memasukkan data.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('offlineSearch');
    const typeFilter = document.getElementById('offlineTypeFilter');
    const statusFilter = document.getElementById('offlineStatusFilter');
    const reset = document.getElementById('offlineResetFilter');
    const table = document.getElementById('dataTableOffline');

    if (!table) return;

    function filterRows() {
        const keyword = search.value.trim().toLowerCase();
        const selectedType = typeFilter.value;
        const selectedStatus = statusFilter.value;

        table.querySelectorAll('tbody tr').forEach(function (row) {
            const rowSearch = row.dataset.search || '';
            const rowType = row.dataset.type || '';
            const rowStatus = row.dataset.status || '';

            const matchesSearch = rowSearch.includes(keyword);
            const matchesType = !selectedType || rowType === selectedType;
            const matchesStatus = !selectedStatus || rowStatus === selectedStatus;

            row.hidden = !(matchesSearch && matchesType && matchesStatus);
        });
    }

    search.addEventListener('input', filterRows);
    typeFilter.addEventListener('change', filterRows);
    statusFilter.addEventListener('change', filterRows);

    reset.addEventListener('click', function () {
        search.value = '';
        typeFilter.value = '';
        statusFilter.value = '';
        filterRows();
    });
});
</script>
@endsection
