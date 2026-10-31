@extends('layout.admin')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Data Peserta Futsal
        </h1>
    </div>


    {{-- ========================= --}}
    {{-- DATA PENDING --}}
    {{-- ========================= --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-warning">
                Peserta Futsal - Menunggu Verifikasi
            </h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">

                @if ($dataPending->count() > 0)

                    <table class="table table-bordered" id="dataTableFutsalPending"
                        width="100%" cellspacing="0">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Tim</th>
                                <th>Email</th>
                                <th>Sekolah</th>
                                <th>Jumlah Anggota</th>
                                <th>Status</th>
                                <th>Nominal</th>
                                <th>Bukti</th>
                                <th>Aksi</th>
                                <th>Tanggal Daftar</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($dataPending as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        <strong>{{ $data->team_name }}</strong>
                                    </td>

                                    <td>{{ $data->email }}</td>

                                    <td>{{ $data->school }}</td>

                                    <td>
                                        {{ $data->total_members }} orang
                                    </td>

                                    <td>
                                        <span class="badge badge-warning">
                                            PENDING
                                        </span>
                                    </td>

                                    <td>
                                        @if ($data->payment_amount)
                                            Rp {{ number_format($data->payment_amount, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if ($data->proof_url)
                                            <a href="{{ $data->proof_url }}"
                                                target="_blank"
                                                class="btn btn-sm btn-info">
                                                <i class="fas fa-image"></i>
                                                Lihat
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="text-center">

                                        @if ($data->payment_id)

                                            {{-- VERIFIKASI --}}
                                            <form
                                                action="{{ route('pembayaran.validbukti', $data->payment_id) }}"
                                                method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('Yakin ingin memverifikasi pembayaran tim {{ $data->team_name }}?')"
                                            >
                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-success"
                                                    title="Verifikasi"
                                                >
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>


                                            {{-- REJECT --}}
                                            <form
                                                action="{{ route('pembayaran.rejectbukti', $data->payment_id) }}"
                                                method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('Yakin ingin menolak pembayaran tim {{ $data->team_name }}?')"
                                            >
                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    title="Reject"
                                                >
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>

                                        @else
                                            -
                                        @endif

                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($data->created_at)->format('d-m-Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>

                @else

                    <div class="alert alert-info">
                        Tidak ada peserta futsal yang menunggu verifikasi.
                    </div>

                @endif

            </div>
        </div>
    </div>


    {{-- ========================= --}}
    {{-- DATA VERIFIED --}}
    {{-- ========================= --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">
                Peserta Futsal - Terverifikasi
            </h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">

                @if ($dataValid->count() > 0)

                    <table class="table table-bordered" id="dataTableFutsalValid"
                        width="100%" cellspacing="0">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Tim</th>
                                <th>Email</th>
                                <th>Sekolah</th>
                                <th>Jumlah Anggota</th>
                                <th>Status</th>
                                <th>Nominal</th>
                                <th>Bukti</th>
                                <th>Tanggal Daftar</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($dataValid as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        <strong>{{ $data->team_name }}</strong>
                                    </td>

                                    <td>{{ $data->email }}</td>

                                    <td>{{ $data->school }}</td>

                                    <td>
                                        {{ $data->total_members }} orang
                                    </td>

                                    <td>
                                        <span class="badge badge-success">
                                            VERIFIED
                                        </span>
                                    </td>

                                    <td>
                                        @if ($data->payment_amount)
                                            Rp {{ number_format($data->payment_amount, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if ($data->proof_url)
                                            <a href="{{ $data->proof_url }}"
                                                target="_blank"
                                                class="btn btn-sm btn-info">
                                                <i class="fas fa-image"></i>
                                                Lihat
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($data->created_at)->format('d-m-Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>

                @else

                    <div class="alert alert-success">
                        Belum ada peserta futsal yang terverifikasi.
                    </div>

                @endif

            </div>
        </div>
    </div>


    {{-- ========================= --}}
    {{-- DATA REJECTED --}}
    {{-- ========================= --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-danger">
                Peserta Futsal - Ditolak
            </h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">

                @if ($dataRejected->count() > 0)

                    <table class="table table-bordered" id="dataTableFutsalRejected"
                        width="100%" cellspacing="0">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Tim</th>
                                <th>Email</th>
                                <th>Sekolah</th>
                                <th>Jumlah Anggota</th>
                                <th>Status</th>
                                <th>Nominal</th>
                                <th>Bukti</th>
                                <th>Tanggal Daftar</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($dataRejected as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        <strong>{{ $data->team_name }}</strong>
                                    </td>

                                    <td>{{ $data->email }}</td>

                                    <td>{{ $data->school }}</td>

                                    <td>
                                        {{ $data->total_members }} orang
                                    </td>

                                    <td>
                                        <span class="badge badge-danger">
                                            REJECTED
                                        </span>
                                    </td>

                                    <td>
                                        @if ($data->payment_amount)
                                            Rp {{ number_format($data->payment_amount, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if ($data->proof_url)
                                            <a href="{{ $data->proof_url }}"
                                                target="_blank"
                                                class="btn btn-sm btn-info">
                                                <i class="fas fa-image"></i>
                                                Lihat
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($data->created_at)->format('d-m-Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>

                @else

                    <div class="alert alert-danger">
                        Belum ada peserta futsal yang ditolak.
                    </div>

                @endif

            </div>
        </div>
    </div>

</div>
@endsection


@push('scripts')
<script>
    $(document).ready(function () {

        const tableOptions = {
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                zeroRecords: "Data tidak ditemukan",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            }
        };

        $('#dataTableFutsalPending').DataTable(tableOptions);
        $('#dataTableFutsalValid').DataTable(tableOptions);
        $('#dataTableFutsalRejected').DataTable(tableOptions);

    });
</script>
@endpush