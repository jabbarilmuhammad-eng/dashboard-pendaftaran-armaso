@extends('layout.admin')

@section('title', 'Data Peserta Kolektif | Admin')

@section('content')

<div class="card shadow mb-4">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">
            Data Peserta Kolektif
        </h5>

        <!-- Tombol Import Data -->
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary" data-toggle="modal" data-target="#importModal">
                <i class="fa-solid fa-upload"></i>
                Import Data
            </button>
        </div>
    </div>

    <div class="card-body">

        <div class="table-responsive">

            @if ($pesertaKolektif->count() > 0)

            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nomor Peserta</th>
                        <th>Nama</th>
                        <th>Nomor HP</th>
                        <th>Sekolah</th>
                        <th>Mapel</th>
                        <th>Status Data</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($pesertaKolektif as $item)

                    <tr>

                        {{-- Nomor --}}
                        <td class="text-center">
                            {{ $loop->iteration }}
                        </td>

                        {{-- Nomor Peserta --}}
                        <td>
                            {{ $item->registration_id ?? '-' }}
                        </td>

                        {{-- Nama --}}
                        <td>
                            {{ $item->nama_lengkap ?? 'Tanpa Nama' }}
                        </td>

                        {{-- Nomor HP --}}
                        <td>

                            @if (!empty($item->nomor_hp))

                                @php
                                    $nomorHp = preg_replace('/[^0-9+]/', '', $item->nomor_hp);

                                    if (str_starts_with($nomorHp, '+62')) {
                                        $nomorWa = $nomorHp;
                                    } elseif (str_starts_with($nomorHp, '62')) {
                                        $nomorWa = '+' . $nomorHp;
                                    } elseif (str_starts_with($nomorHp, '0')) {
                                        $nomorWa = '+62' . substr($nomorHp, 1);
                                    } else {
                                        $nomorWa = $nomorHp;
                                    }
                                @endphp

                                <a href="https://wa.me/{{ ltrim($nomorWa, '+') }}"
                                   target="_blank"
                                   rel="noopener noreferrer">

                                    {{ $item->nomor_hp }}

                                </a>

                            @else

                                <span class="text-muted">
                                    -
                                </span>

                            @endif

                        </td>

                        {{-- Sekolah --}}
                        <td>
                            {{ $item->sekolah ?? '-' }}
                        </td>

                        {{-- Mapel --}}
                        <td>

                            @if (!empty($item->bidang))

                                {{ $item->bidang }}

                            @else

                                <span class="text-muted">
                                    Tidak ada mapel
                                </span>

                            @endif

                        </td>

                        {{-- Status Data --}}
                        <td>

                            @if (($item->payment_status ?? '') === 'VERIFIED')

                                <span class="badge badge-success">
                                    Data Valid
                                </span>

                            @elseif (($item->payment_status ?? '') === 'REJECTED')

                                <span class="badge badge-danger">
                                    Pembayaran Ditolak
                                </span>

                            @else

                                <span class="badge badge-warning">
                                    Belum Valid
                                </span>

                            @endif

                        </td>

                        {{-- Aksi --}}
                        <td class="text-center">

                            {{-- Detail --}}
                            <a href="{{ url('/admin/peserta/kolektif/show/' . $item->registration_id) }}"
                               class="btn btn-sm btn-info text-white d-inline-block"
                               title="Lihat Detail">

                                <i class="fa-solid fa-eye"></i>

                            </a>

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

            @else

            <div class="alert alert-warning">
                Belum ada data peserta kolektif yang tersedia.
            </div>

            @endif

        </div>

    </div>

</div>


{{-- ====================================================== --}}
{{-- MODAL IMPORT --}}
{{-- ====================================================== --}}

<div class="modal fade"
     id="importModal"
     tabindex="-1"
     role="dialog"
     aria-labelledby="importModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" role="document">

        <div class="modal-content">

            <form action="{{ route('kolektif.import') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="modal-header">

                    <h5 class="modal-title" id="importModalLabel">
                        Import Data Excel
                    </h5>

                    <button type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close">

                        <span aria-hidden="true">
                            &times;
                        </span>

                    </button>

                </div>


                <div class="modal-body">

                    <div class="input-group align-items-center">

                        <input type="file"
                               name="file"
                               class="d-none"
                               id="file"
                               required>

                        <label for="file"
                               class="btn btn-primary">

                            📂 Pilih File

                        </label>

                        <span id="file-name"
                              class="ms-3 text-muted">

                            Tidak ada file dipilih

                        </span>

                    </div>


                    <a href="{{ asset('format/formatKolektif.xlsx') }}"
                       class="btn btn-link mt-3"
                       download>

                        <i class="fa fa-download"></i>
                        Download Format Excel

                    </a>


                    <p class="mt-1 text-muted">

                        <strong>
                            Pastikan file yang diinput sesuai dengan format Excel di atas!
                        </strong>

                    </p>

                </div>


                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal">

                        Batal

                    </button>

                    <button type="submit"
                            class="btn btn-primary">

                        Import

                    </button>

                </div>


                <div class="alert alert-warning mt-3">

                    <h6 class="font-weight-bold">
                        📌 INFORMASI PENTING:
                    </h6>

                    <ol class="pl-3">

                        <li>
                            Jika ada data kosong, diisi terserah saja.
                            <strong>Jangan sampai ada data kosong</strong>
                            atau dikasih tanda <code>-</code>.
                        </li>

                        <li>
                            Harus mengikuti format yang ada,
                            <strong>jangan sampai ada data yang tertukar</strong>.
                        </li>

                        <li>
                            Kolom <strong>Jenis Kelamin</strong> dan
                            <strong>Lomba</strong> memiliki
                            <strong>dropdown</strong> untuk memilih nilai.
                        </li>

                        <li>
                            Nilai dalam dropdown berasal dari sheet bernama
                            <code>List</code>.
                        </li>

                        <li>
                            <strong>NISN dan Nomor HP</strong> gunakan format
                            <code>Text</code>,
                            <strong>Tanggal Lahir</strong> format
                            <code>Short Date</code>,
                            dan <strong>Total Tagihan</strong> format
                            <code>Accounting</code>
                            (harus ada dua nol di belakang titik).
                        </li>

                        <li>
                            Jika ketika import di bagian nama yang gagal muncul tulisan
                            <strong>"Tanpa Nama"</strong>,
                            <em>abaikan saja</em>.
                        </li>

                        <li>
                            <strong>Jangan lupa dicek kembali</strong>
                            setelah data berhasil diimport.
                        </li>

                        <li>
                            <strong>Nomor HP</strong> harus menggunakan +62.
                        </li>

                    </ol>

                </div>

            </form>

        </div>

    </div>

</div>


{{-- ====================================================== --}}
{{-- HASIL IMPORT --}}
{{-- ====================================================== --}}

@if(session('hasil_import'))

<div class="modal fade"
     id="hasilImportModal"
     tabindex="-1"
     aria-labelledby="hasilImportLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header bg-info text-white">

                <h5 class="modal-title"
                    id="hasilImportLabel">

                    Hasil Import Data

                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Tutup">

                </button>

            </div>


            <div class="modal-body">

                <p>
                    <strong>Total Data Diproses:</strong>
                    {{ session('hasil_import.total') }}
                </p>

                <p>
                    <strong>Berhasil Disimpan:</strong>
                    {{ session('hasil_import.berhasil') }}
                </p>

                <p>
                    <strong>Gagal Disimpan:</strong>
                    {{ session('hasil_import.gagal') }}
                </p>


                @if(session('hasil_import.gagal_nama'))

                    <p>
                        <strong>Nama yang Gagal:</strong>
                    </p>

                    <ul>

                        @foreach(session('hasil_import.gagal_nama') as $nama)

                            <li>
                                {{ $nama }}
                            </li>

                        @endforeach

                    </ul>

                @endif

            </div>

        </div>

    </div>

</div>

@endif


{{-- ====================================================== --}}
{{-- JAVASCRIPT --}}
{{-- ====================================================== --}}

<script>

window.addEventListener('load', function() {

    const hasilImportElement =
        document.getElementById('hasilImportModal');

    if (
        hasilImportElement &&
        typeof bootstrap !== 'undefined' &&
        bootstrap.Modal
    ) {

        const hasilImportModal =
            new bootstrap.Modal(hasilImportElement);

        hasilImportModal.show();

    }

});


document.addEventListener("DOMContentLoaded", function() {

    const buttons =
        document.querySelectorAll(".pilih-mapel-btn");

    buttons.forEach(button => {

        button.addEventListener("click", function() {

            let pesertaId =
                this.getAttribute("data-id");

            const pesertaInput =
                document.getElementById("peserta_id");

            if (pesertaInput) {

                pesertaInput.value =
                    pesertaId;

            }

        });

    });


    const fileInput =
        document.getElementById("file");

    const fileName =
        document.getElementById("file-name");


    if (fileInput && fileName) {

        fileInput.addEventListener("change", function() {

            let selectedFileName =
                this.files.length
                    ? this.files[0].name
                    : "Tidak ada file dipilih";

            fileName.textContent =
                selectedFileName;

        });

    }

});

</script>

@endsection