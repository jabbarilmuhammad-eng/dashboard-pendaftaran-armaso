@extends('layout.admin')

@section('title', 'Daftar Pembayaran | Admin')

@section('content')

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif


{{-- ===================================================== --}}
{{-- PEMBAYARAN PENDING --}}
{{-- ===================================================== --}}

<div class="card shadow mb-4">

    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            Pembayaran Belum Divalidasi
        </h6>
    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered" width="100%" cellspacing="0">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Sekolah</th>
                        <th>Jenis Pendaftaran</th>
                        <th>Jumlah</th>
                        <th>Bukti Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($belumvalid as $payment)

                    <tr>

                        <td>
                            {{ $loop->iteration }}
                        </td>

                        <td>
                            {{ $payment['name'] ?? '-' }}
                        </td>

                        <td>
                            {{ $payment['school'] ?? '-' }}
                        </td>

                        <td>
                            {{ $payment['type'] ?? '-' }}
                        </td>

                        <td>
                            Rp {{ number_format($payment['amount'] ?? 0, 0, ',', '.') }}
                        </td>

                        <td>

                            @if(!empty($payment['proofUrl']))

                                <a
                                    href="{{ $payment['proofUrl'] }}"
                                    target="_blank"
                                    class="btn btn-sm btn-info"
                                >
                                    Lihat Bukti
                                </a>

                            @else

                                <span class="text-muted">
                                    Tidak ada bukti
                                </span>

                            @endif

                        </td>

                        <td>

                            {{-- VALIDASI --}}

                            <form
                                action="{{ route('pembayaran.validbukti', $payment['id']) }}"
                                method="POST"
                                style="display:inline-block;"
                            >

                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="btn btn-sm btn-success"
                                    onclick="return confirm('Yakin ingin memverifikasi pembayaran ini?')"
                                >
                                    Validasi
                                </button>

                            </form>


                            {{-- TOLAK --}}

                            <form
                                action="{{ url('admin/pembayaran/tolak/' . $payment['id']) }}"
                                method="POST"
                                style="display:inline-block;"
                            >

                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Yakin ingin menolak pembayaran ini?')"
                                >
                                    Tolak
                                </button>

                            </form>

                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="7" class="text-center">
                            Tidak ada pembayaran yang menunggu validasi.
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>



{{-- ===================================================== --}}
{{-- PEMBAYARAN VERIFIED --}}
{{-- ===================================================== --}}

<div class="card shadow mb-4">

    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-success">
            Pembayaran Terverifikasi
        </h6>
    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered" width="100%" cellspacing="0">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Sekolah</th>
                        <th>Jenis Pendaftaran</th>
                        <th>Jumlah</th>
                        <th>Bukti Pembayaran</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($valid as $payment)

                    <tr>

                        <td>
                            {{ $loop->iteration }}
                        </td>

                        <td>
                            {{ $payment['name'] ?? '-' }}
                        </td>

                        <td>
                            {{ $payment['school'] ?? '-' }}
                        </td>

                        <td>
                            {{ $payment['type'] ?? '-' }}
                        </td>

                        <td>
                            Rp {{ number_format($payment['amount'] ?? 0, 0, ',', '.') }}
                        </td>

                        <td>

                            @if(!empty($payment['proofUrl']))

                                <a
                                    href="{{ $payment['proofUrl'] }}"
                                    target="_blank"
                                    class="btn btn-sm btn-info"
                                >
                                    Lihat Bukti
                                </a>

                            @else

                                <span class="text-muted">
                                    Tidak ada bukti
                                </span>

                            @endif

                        </td>

                        <td>

                            <span class="badge badge-success">
                                TERVERIFIKASI
                            </span>

                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="7" class="text-center">
                            Belum ada pembayaran terverifikasi.
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>



{{-- ===================================================== --}}
{{-- PEMBAYARAN DITOLAK --}}
{{-- ===================================================== --}}

<div class="card shadow mb-4">

    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-danger">
            Pembayaran Ditolak
        </h6>
    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered" width="100%" cellspacing="0">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Sekolah</th>
                        <th>Jenis Pendaftaran</th>
                        <th>Jumlah</th>
                        <th>Bukti Pembayaran</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($ditolak as $payment)

                    <tr>

                        <td>
                            {{ $loop->iteration }}
                        </td>

                        <td>
                            {{ $payment['name'] ?? '-' }}
                        </td>

                        <td>
                            {{ $payment['school'] ?? '-' }}
                        </td>

                        <td>
                            {{ $payment['type'] ?? '-' }}
                        </td>

                        <td>
                            Rp {{ number_format($payment['amount'] ?? 0, 0, ',', '.') }}
                        </td>

                        <td>

                            @if(!empty($payment['proofUrl']))

                                <a
                                    href="{{ $payment['proofUrl'] }}"
                                    target="_blank"
                                    class="btn btn-sm btn-info"
                                >
                                    Lihat Bukti
                                </a>

                            @else

                                <span class="text-muted">
                                    Tidak ada bukti
                                </span>

                            @endif

                        </td>

                        <td>

                            <span class="badge badge-danger">
                                DITOLAK
                            </span>

                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="7" class="text-center">
                            Belum ada pembayaran yang ditolak.
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection