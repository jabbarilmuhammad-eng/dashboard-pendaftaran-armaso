
@extends('layout.admin')

@section('title', 'Pendaftaran Offline | Admin')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">
            Pendaftaran Peserta Offline ARMASO
        </h5>
    </div>

    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Periksa kembali data berikut:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('ofline.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="type" class="form-label">Kategori Pendaftaran</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="">-- Pilih kategori --</option>
                    <option value="SELF" {{ old('type') == 'SELF' ? 'selected' : '' }}>
                        Sains Individu
                    </option>
                    <option value="COLLECTIVE" {{ old('type') == 'COLLECTIVE' ? 'selected' : '' }}>
                        Sains Kolektif
                    </option>
                    <option value="FUTSAL" {{ old('type') == 'FUTSAL' ? 'selected' : '' }}>
                        Futsal
                    </option>
                </select>
            </div>

            <div class="mb-3">
                <label for="school" class="form-label">Asal Sekolah</label>
                <input
                    type="text"
                    name="school"
                    id="school"
                    class="form-control"
                    value="{{ old('school') }}"
                    maxlength="255"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="whatsapp" class="form-label">
                    Nomor WhatsApp Peserta / Kontak Official
                </label>
                <input
                    type="text"
                    name="whatsapp"
                    id="whatsapp"
                    class="form-control"
                    value="{{ old('whatsapp') }}"
                    maxlength="20"
                    placeholder="Contoh: 081234567890"
                    required
                >
                <small class="text-muted">
                    Untuk futsal, masukkan nomor WhatsApp official tim.
                </small>
            </div>

            {{-- SAINS INDIVIDU --}}
            <div id="self-fields" class="border rounded p-3 mb-3" hidden>
                <h6 class="font-weight-bold mb-3">Data Peserta Individu</h6>

                <div class="mb-3">
                    <label for="fullName" class="form-label">Nama Lengkap</label>
                    <input
                        type="text"
                        name="fullName"
                        id="fullName"
                        class="form-control"
                        value="{{ old('fullName') }}"
                        maxlength="255"
                    >
                </div>

                <div class="mb-3">
                    <label for="field" class="form-label">Bidang Sains</label>
                    <select name="field" id="field" class="form-control">
                        <option value="">-- Pilih bidang --</option>
                        <option value="MATEMATIKA" {{ old('field') == 'MATEMATIKA' ? 'selected' : '' }}>
                            Matematika
                        </option>
                        <option value="IPA" {{ old('field') == 'IPA' ? 'selected' : '' }}>
                            IPA
                        </option>
                        <option value="IPS" {{ old('field') == 'IPS' ? 'selected' : '' }}>
                            IPS
                        </option>
                    </select>
                </div>

                <p class="text-muted mb-0">
                    Biaya pendaftaran: Rp35.000.
                </p>
            </div>

            {{-- SAINS KOLEKTIF --}}
            <div id="collective-fields" class="border rounded p-3 mb-3" hidden>
                <h6 class="font-weight-bold">Data Peserta Kolektif</h6>
                <p class="text-muted">
                    Masukkan 1–11 peserta untuk satu pendaftaran.
                    Setiap 11 peserta, 1 peserta gratis.
                </p>

                <div id="participants-container">
                    @php
                        $oldParticipants = old('participants', [
                            ['fullName' => '', 'field' => '']
                        ]);
                    @endphp

                    @foreach ($oldParticipants as $i => $participant)
                        <div class="participant-row border rounded p-3 mb-3">
                            <h6 class="participant-number">
                                Peserta {{ $i + 1 }}
                            </h6>

                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input
                                    type="text"
                                    name="participants[{{ $i }}][fullName]"
                                    class="form-control participant-name"
                                    value="{{ $participant['fullName'] ?? '' }}"
                                    maxlength="255"
                                >
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Bidang Sains</label>
                                <select
                                    name="participants[{{ $i }}][field]"
                                    class="form-control participant-field"
                                >
                                    <option value="">-- Pilih bidang --</option>
                                    <option value="MATEMATIKA" {{ ($participant['field'] ?? '') == 'MATEMATIKA' ? 'selected' : '' }}>
                                        Matematika
                                    </option>
                                    <option value="IPA" {{ ($participant['field'] ?? '') == 'IPA' ? 'selected' : '' }}>
                                        IPA
                                    </option>
                                    <option value="IPS" {{ ($participant['field'] ?? '') == 'IPS' ? 'selected' : '' }}>
                                        IPS
                                    </option>
                                </select>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-danger remove-participant">
                                Hapus peserta
                            </button>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="add-participant" class="btn btn-outline-primary">
                    + Tambah Peserta
                </button>

                <div class="alert alert-info mt-3 mb-0">
                    <strong>Estimasi biaya:</strong>
                    <span id="collective-price">Rp35.000</span>
                    <br>
                    <small>Biaya dihitung otomatis berdasarkan jumlah peserta.</small>
                </div>
            </div>

            {{-- FUTSAL --}}
            <div id="futsal-fields" class="border rounded p-3 mb-3" hidden>
                <h6 class="font-weight-bold mb-3">Data Tim Futsal</h6>

                <div class="mb-3">
                    <label for="teamName" class="form-label">Nama Tim</label>
                    <input
                        type="text"
                        name="teamName"
                        id="teamName"
                        class="form-control"
                        value="{{ old('teamName') }}"
                        maxlength="255"
                    >
                </div>

                <p class="text-muted mb-0">
                    Biaya pendaftaran: Rp150.000 per tim.
                    Tidak perlu mengisi nama anggota tim.
                </p>
            </div>

            <hr>

            <div class="alert alert-warning">
                <strong>Pembayaran offline:</strong>
                status pembayaran akan langsung menjadi VERIFIED karena
                pembayaran telah diterima oleh panitia.
            </div>

            <button type="submit" class="btn btn-primary">
                Simpan Pendaftaran
            </button>

            <a href="{{ route('ofline.index') }}" class="btn btn-secondary">
                Kembali
            </a>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('type');
    const selfFields = document.getElementById('self-fields');
    const collectiveFields = document.getElementById('collective-fields');
    const futsalFields = document.getElementById('futsal-fields');

    const fullName = document.getElementById('fullName');
    const field = document.getElementById('field');
    const teamName = document.getElementById('teamName');

    const container = document.getElementById('participants-container');
    const addButton = document.getElementById('add-participant');
    const priceDisplay = document.getElementById('collective-price');

    function setGroupRequired(required) {
        container.querySelectorAll('.participant-name, .participant-field')
            .forEach(function (input) {
                input.required = required;
            });
    }

    function updateType() {
        const type = typeSelect.value;

        selfFields.hidden = type !== 'SELF';
        collectiveFields.hidden = type !== 'COLLECTIVE';
        futsalFields.hidden = type !== 'FUTSAL';

        fullName.required = type === 'SELF';
        field.required = type === 'SELF';
        teamName.required = type === 'FUTSAL';

        setGroupRequired(type === 'COLLECTIVE');
    }

    function updateParticipants() {
        const rows = container.querySelectorAll('.participant-row');

        rows.forEach(function (row, index) {
            row.querySelector('.participant-number').textContent =
                'Peserta ' + (index + 1);

            row.querySelectorAll('input, select').forEach(function (input) {
                const key = input.classList.contains('participant-name')
                    ? 'fullName'
                    : 'field';

                input.name = 'participants[' + index + '][' + key + ']';
            });

            row.querySelector('.remove-participant').disabled = rows.length <= 1;
        });

        const total = rows.length;
        const free = Math.floor(total / 11);
        const paid = total - free;
        const amount = paid * 35000;

        priceDisplay.textContent = 'Rp' + amount.toLocaleString('id-ID');
        addButton.disabled = total >= 11;
    }

    addButton.addEventListener('click', function () {
        const rows = container.querySelectorAll('.participant-row');

        if (rows.length >= 11) return;

        const index = rows.length;
        const row = document.createElement('div');

        row.className = 'participant-row border rounded p-3 mb-3';

        row.innerHTML = `
            <h6 class="participant-number">Peserta ${index + 1}</h6>

            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input
                    type="text"
                    class="form-control participant-name"
                    maxlength="255"
                    required
                >
            </div>

            <div class="mb-2">
                <label class="form-label">Bidang Sains</label>
                <select class="form-control participant-field" required>
                    <option value="">-- Pilih bidang --</option>
                    <option value="MATEMATIKA">Matematika</option>
                    <option value="IPA">IPA</option>
                    <option value="IPS">IPS</option>
                </select>
            </div>

            <button type="button" class="btn btn-sm btn-outline-danger remove-participant">
                Hapus peserta
            </button>
        `;

        container.appendChild(row);
        updateType();
        updateParticipants();
    });

    container.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-participant');

        if (!button) return;

        const rows = container.querySelectorAll('.participant-row');

        if (rows.length <= 1) return;

        button.closest('.participant-row').remove();
        updateParticipants();
    });

    typeSelect.addEventListener('change', updateType);

    updateType();
    updateParticipants();
});
</script>
@endsection
