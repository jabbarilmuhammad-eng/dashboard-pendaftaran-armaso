@extends('layout.admin')

@section('title', 'Data Peserta Online | Admin')

@section('content')

<div class="card shadow mb-4">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">
            Data Peserta Online
        </h5>
    </div>

    <div class="card-body">

        {{-- ===================================================== --}}
        {{-- PENDING --}}
        {{-- ===================================================== --}}

        <div class="card mb-4 border-left-warning">

            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-warning">
                    Pembayaran Menunggu Verifikasi
                </h6>
            </div>

            <div class="card-body">

                @if ($dataPending->count() > 0)

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover"
                               id="dataTablePending"
                               width="100%">

                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Sekolah</th>
                                    <th>Bidang</th>
                                    <th>Jenis</th>
                                    <th>Nominal</th>
                                    <th>Bukti</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach ($dataPending as $item)

                                    <tr>

                                        <td>
                                            {{ $loop->iteration }}
                                        </td>

                                        <td>
                                            {{ $item->name }}
                                        </td>

                                        <td>
                                            {{ $item->email }}
                                        </td>

                                        <td>
                                            {{ $item->school }}
                                        </td>

                                        <td>
                                            {{ $item->field }}
                                        </td>

                                        <td>
                                            {{ $item->type }}
                                        </td>

                                        <td>
                                            @if ($item->payment_amount)
                                                Rp {{ number_format($item->payment_amount, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>
                                            @if ($item->proof_url)

                                                <a href="{{ $item->proof_url }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                    Lihat
                                                </a>

                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>
                                            <span class="badge badge-warning">
                                                PENDING
                                            </span>
                                        </td>

                                        <td>
                                            {{ $item->created_at }}
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @else

                    <div class="alert alert-success mb-0">
                        Tidak ada pembayaran yang menunggu verifikasi.
                    </div>

                @endif

            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- VERIFIED --}}
        {{-- ===================================================== --}}

        <div class="card mb-4 border-left-success">

            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-success">
                    Peserta Terverifikasi
                </h6>
            </div>

            <div class="card-body">

                @if ($dataValid->count() > 0)

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover"
                               id="dataTableValid"
                               width="100%">

                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Sekolah</th>
                                    <th>Bidang</th>
                                    <th>Jenis</th>
                                    <th>Nominal</th>
                                    <th>Bukti</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach ($dataValid as $item)

                                    <tr>

                                        <td>
                                            {{ $loop->iteration }}
                                        </td>

                                        <td>
                                            {{ $item->name }}
                                        </td>

                                        <td>
                                            {{ $item->email }}
                                        </td>

                                        <td>
                                            {{ $item->school }}
                                        </td>

                                        <td>
                                            {{ $item->field }}
                                        </td>

                                        <td>
                                            {{ $item->type }}
                                        </td>

                                        <td>
                                            @if ($item->payment_amount)
                                                Rp {{ number_format($item->payment_amount, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>
                                            @if ($item->proof_url)

                                                <a href="{{ $item->proof_url }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                    Lihat
                                                </a>

                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>
                                            <span class="badge badge-success">
                                                VERIFIED
                                            </span>
                                        </td>

                                        <td>
                                            {{ $item->created_at }}
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @else

                    <div class="alert alert-warning mb-0">
                        Belum ada peserta yang terverifikasi.
                    </div>

                @endif

            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- REJECTED --}}
        {{-- ===================================================== --}}

        <div class="card mb-4 border-left-danger">

            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-danger">
                    Pembayaran Ditolak
                </h6>
            </div>

            <div class="card-body">

                @if ($dataRejected->count() > 0)

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover"
                               id="dataTableRejected"
                               width="100%">

                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Sekolah</th>
                                    <th>Bidang</th>
                                    <th>Jenis</th>
                                    <th>Nominal</th>
                                    <th>Bukti</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach ($dataRejected as $item)

                                    <tr>

                                        <td>
                                            {{ $loop->iteration }}
                                        </td>

                                        <td>
                                            {{ $item->name }}
                                        </td>

                                        <td>
                                            {{ $item->email }}
                                        </td>

                                        <td>
                                            {{ $item->school }}
                                        </td>

                                        <td>
                                            {{ $item->field }}
                                        </td>

                                        <td>
                                            {{ $item->type }}
                                        </td>

                                        <td>
                                            @if ($item->payment_amount)
                                                Rp {{ number_format($item->payment_amount, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>
                                            @if ($item->proof_url)

                                                <a href="{{ $item->proof_url }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                    Lihat
                                                </a>

                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>
                                            <span class="badge badge-danger">
                                                REJECTED
                                            </span>
                                        </td>

                                        <td>
                                            {{ $item->created_at }}
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @else

                    <div class="alert alert-success mb-0">
                        Tidak ada pembayaran yang ditolak.
                    </div>

                @endif

            </div>

        </div>

    </div>

</div>


<script>

$(document).ready(function () {

    if ($('#dataTablePending').length) {

        $('#dataTablePending').DataTable({
            pageLength: 10,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                zeroRecords: "Tidak ditemukan data yang cocok",
                paginate: {
                    previous: "Sebelumnya",
                    next: "Berikutnya"
                }
            }
        });

    }


    if ($('#dataTableValid').length) {

        $('#dataTableValid').DataTable({
            pageLength: 10,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                zeroRecords: "Tidak ditemukan data yang cocok",
                paginate: {
                    previous: "Sebelumnya",
                    next: "Berikutnya"
                }
            }
        });

    }


    if ($('#dataTableRejected').length) {

        $('#dataTableRejected').DataTable({
            pageLength: 10,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                zeroRecords: "Tidak ditemukan data yang cocok",
                paginate: {
                    previous: "Sebelumnya",
                    next: "Berikutnya"
                }
            }
        });

    }

});

</script>

@endsection