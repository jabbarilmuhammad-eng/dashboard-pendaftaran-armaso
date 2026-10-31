<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class DaftarofflineController extends Controller
{
    private const SCIENCE_PRICE = 35000;
    private const FUTSAL_PRICE = 150000;

    /**
     * Menampilkan daftar pendaftaran offline dari Neon.
     */
    public function index()
    {
        $db = DB::connection('neon');

        $participantsQuery = $db->table('participant')
            ->select('registrationId')
            ->selectRaw('COUNT(*) as participant_count')
            ->selectRaw('STRING_AGG("fullName", \', \') as participant_name')
            ->selectRaw('STRING_AGG(DISTINCT "school", \', \') as participant_school')
            ->selectRaw('STRING_AGG(DISTINCT "field", \', \') as field')
            ->groupBy('registrationId');

        $registrations = $db->table('registration as r')
            ->leftJoin('payment as p', 'p.registrationId', '=', 'r.id')
            ->leftJoinSub(
                $participantsQuery,
                'pt',
                'pt.registrationId',
                '=',
                'r.id'
            )
            ->leftJoin('team as t', 't.registrationId', '=', 'r.id')
            ->where('r.source', 'OFFLINE')
            ->select(
                'r.id as registration_id',
                'r.type',
                'r.whatsapp',
                'r.createdAt',
                'p.amount',
                'p.status as payment_status',
                'pt.participant_name',
                'pt.participant_school',
                'pt.field',
                'pt.participant_count',
                't.name as team_name',
                't.school as team_school'
            )
            ->orderByDesc('r.createdAt')
            ->get();

        // Ambil nama dan bidang setiap anggota untuk kartu Science Kolektif.
        $collectiveIds = $registrations
            ->where('type', 'COLLECTIVE')
            ->pluck('registration_id')
            ->all();

        $collectiveParticipants = empty($collectiveIds)
            ? collect()
            : $db->table('participant')
                ->select('registrationId', 'fullName', 'field', 'school')
                ->whereIn('registrationId', $collectiveIds)
                ->orderBy('createdAt')
                ->get()
                ->groupBy('registrationId');

        foreach ($registrations as $registration) {
            $registration->participants = $registration->type === 'COLLECTIVE'
                ? ($collectiveParticipants->get($registration->registration_id) ?? collect())
                : collect();
        }

        $totalPendaftaran = $registrations->count();

        $totalPeserta = $registrations->sum(function ($item) {
            return (int) ($item->participant_count ?? 0);
        });

        $totalTim = $registrations->where('type', 'FUTSAL')->count();

        $totalVerified = $registrations
            ->where('payment_status', 'VERIFIED')
            ->count();

        $totalPendapatan = $registrations
            ->where('payment_status', 'VERIFIED')
            ->sum(function ($item) {
                return (int) ($item->amount ?? 0);
            });

        return view(
            'admin.datadiripeserta.ofline.index',
            compact(
                'registrations',
                'totalPendaftaran',
                'totalPeserta',
                'totalTim',
                'totalVerified',
                'totalPendapatan'
            )
        );
    }

    /**
     * Form pendaftaran offline.
     */
    public function create()
    {
        return view('admin.datadiripeserta.ofline.create');
    }

    /**
     * Menyimpan pendaftaran offline beserta pembayaran ke Neon.
     */
    public function store(Request $request)
    {
        $type = strtoupper((string) $request->input('type'));

        if (!in_array($type, ['SELF', 'COLLECTIVE', 'FUTSAL'], true)) {
            throw ValidationException::withMessages([
                'type' => 'Pilih kategori pendaftaran yang valid.',
            ]);
        }

        $rules = [
            'type' => 'required|in:SELF,COLLECTIVE,FUTSAL',
            'whatsapp' => ['required', 'string', 'max:20'],
        ];

        if ($type === 'SELF') {
            $rules['fullName'] = 'required|string|max:255';
            $rules['school'] = 'required|string|max:255';
            $rules['field'] = 'required|in:MATEMATIKA,IPA,IPS';
        }

        if ($type === 'COLLECTIVE') {
            $rules['school'] = 'required|string|max:255';
            $rules['participants'] = 'required|array|min:1|max:11';
            $rules['participants.*.fullName'] = 'required|string|max:255';
            $rules['participants.*.field'] = 'required|in:MATEMATIKA,IPA,IPS';
        }

        if ($type === 'FUTSAL') {
            $rules['teamName'] = 'required|string|max:255';
            $rules['school'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        $db = DB::connection('neon');
        $diagnosticId = uniqid('offline_', true);

        Log::info('OFFLINE_DIAG: store dimulai', [
            'diagnostic_id' => $diagnosticId,
            'type' => $type,
            'connection' => 'neon',
        ]);

        try {
            $registrationId = $db->transaction(function () use (
                $db,
                $validated,
                $type,
                $diagnosticId
            ) {
                $now = now();

                /*
                 * STEP 1: INSERT REGISTRATION
                 */
                Log::info('OFFLINE_DIAG: sebelum insert registration', [
                    'diagnostic_id' => $diagnosticId,
                    'type' => $type,
                ]);

                try {
                    $registrationId = $db->table('registration')
                        ->insertGetId([
                            'type' => $type,
                            'source' => 'OFFLINE',
                            'userId' => null,
                            'whatsapp' => $validated['whatsapp'],
                            'createdAt' => $now,
                            'updatedAt' => $now,
                        ]);
                } catch (Throwable $e) {
                    Log::error('OFFLINE_DIAG: INSERT REGISTRATION GAGAL', [
                        'diagnostic_id' => $diagnosticId,
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'sql_state' => $e instanceof \Illuminate\Database\QueryException
                            ? ($e->errorInfo[0] ?? null)
                            : null,
                        'error_info' => $e instanceof \Illuminate\Database\QueryException
                            ? ($e->errorInfo ?? null)
                            : null,
                    ]);

                    throw $e;
                }

                Log::info('OFFLINE_DIAG: registration berhasil', [
                    'diagnostic_id' => $diagnosticId,
                    'registration_id' => $registrationId,
                ]);

                $amount = 0;

                /*
                 * STEP 2: INSERT PESERTA INDIVIDU
                 */
                if ($type === 'SELF') {
                    Log::info('OFFLINE_DIAG: sebelum insert participant SELF', [
                        'diagnostic_id' => $diagnosticId,
                        'registration_id' => $registrationId,
                    ]);

                    try {
                        $db->table('participant')->insert([
                            'registrationId' => $registrationId,
                            'fullName' => $validated['fullName'],
                            'school' => $validated['school'],
                            'field' => $validated['field'],
                            'createdAt' => $now,
                            'updatedAt' => $now,
                        ]);
                    } catch (Throwable $e) {
                        Log::error('OFFLINE_DIAG: INSERT PARTICIPANT SELF GAGAL', [
                            'diagnostic_id' => $diagnosticId,
                            'registration_id' => $registrationId,
                            'exception' => get_class($e),
                            'message' => $e->getMessage(),
                            'code' => $e->getCode(),
                            'sql_state' => $e instanceof \Illuminate\Database\QueryException
                                ? ($e->errorInfo[0] ?? null)
                                : null,
                            'error_info' => $e instanceof \Illuminate\Database\QueryException
                                ? ($e->errorInfo ?? null)
                                : null,
                        ]);

                        throw $e;
                    }

                    Log::info('OFFLINE_DIAG: participant SELF berhasil', [
                        'diagnostic_id' => $diagnosticId,
                        'registration_id' => $registrationId,
                    ]);

                    $amount = self::SCIENCE_PRICE;
                }

                /*
                 * STEP 3: INSERT PESERTA KOLEKTIF
                 */
                elseif ($type === 'COLLECTIVE') {
                    $participants = $validated['participants'];
                    $totalParticipants = count($participants);

                    foreach ($participants as $index => $participant) {
                        Log::info('OFFLINE_DIAG: sebelum insert participant COLLECTIVE', [
                            'diagnostic_id' => $diagnosticId,
                            'registration_id' => $registrationId,
                            'participant_index' => $index,
                        ]);

                        try {
                            $db->table('participant')->insert([
                                'registrationId' => $registrationId,
                                'fullName' => $participant['fullName'],
                                'school' => $validated['school'],
                                'field' => $participant['field'],
                                'createdAt' => $now,
                                'updatedAt' => $now,
                            ]);
                        } catch (Throwable $e) {
                            Log::error('OFFLINE_DIAG: INSERT PARTICIPANT COLLECTIVE GAGAL', [
                                'diagnostic_id' => $diagnosticId,
                                'registration_id' => $registrationId,
                                'participant_index' => $index,
                                'exception' => get_class($e),
                                'message' => $e->getMessage(),
                                'code' => $e->getCode(),
                                'sql_state' => $e instanceof \Illuminate\Database\QueryException
                                    ? ($e->errorInfo[0] ?? null)
                                    : null,
                                'error_info' => $e instanceof \Illuminate\Database\QueryException
                                    ? ($e->errorInfo ?? null)
                                    : null,
                            ]);

                            throw $e;
                        }

                        Log::info('OFFLINE_DIAG: participant COLLECTIVE berhasil', [
                            'diagnostic_id' => $diagnosticId,
                            'registration_id' => $registrationId,
                            'participant_index' => $index,
                        ]);
                    }

                    // Setiap 11 peserta: 10 membayar dan 1 gratis.
                    $freeParticipants = intdiv($totalParticipants, 11);
                    $paidParticipants = $totalParticipants - $freeParticipants;

                    $amount = $paidParticipants * self::SCIENCE_PRICE;
                }

                /*
                 * STEP 4: INSERT TIM FUTSAL
                 */
                elseif ($type === 'FUTSAL') {
                    Log::info('OFFLINE_DIAG: sebelum insert team FUTSAL', [
                        'diagnostic_id' => $diagnosticId,
                        'registration_id' => $registrationId,
                    ]);

                    try {
                        $db->table('team')->insert([
                            'registrationId' => $registrationId,
                            'name' => $validated['teamName'],
                            'school' => $validated['school'],
                            'createdAt' => $now,
                            'updatedAt' => $now,
                        ]);
                    } catch (Throwable $e) {
                        Log::error('OFFLINE_DIAG: INSERT TEAM FUTSAL GAGAL', [
                            'diagnostic_id' => $diagnosticId,
                            'registration_id' => $registrationId,
                            'exception' => get_class($e),
                            'message' => $e->getMessage(),
                            'code' => $e->getCode(),
                            'sql_state' => $e instanceof \Illuminate\Database\QueryException
                                ? ($e->errorInfo[0] ?? null)
                                : null,
                            'error_info' => $e instanceof \Illuminate\Database\QueryException
                                ? ($e->errorInfo ?? null)
                                : null,
                        ]);

                        throw $e;
                    }

                    Log::info('OFFLINE_DIAG: team FUTSAL berhasil', [
                        'diagnostic_id' => $diagnosticId,
                        'registration_id' => $registrationId,
                    ]);

                    $amount = self::FUTSAL_PRICE;
                }

                /*
                 * STEP 5: INSERT PAYMENT
                 */
                Log::info('OFFLINE_DIAG: sebelum insert payment', [
                    'diagnostic_id' => $diagnosticId,
                    'registration_id' => $registrationId,
                    'amount' => $amount,
                ]);

                try {
                    $db->table('payment')->insert([
                        'registrationId' => $registrationId,
                        'userId' => null,
                        'amount' => $amount,
                        'status' => 'VERIFIED',
                        'paymentMethod' => 'CASH',
                        'proofUrl' => null,
                        'createdAt' => $now,
                        'updatedAt' => $now,
                    ]);
                } catch (Throwable $e) {
                    Log::error('OFFLINE_DIAG: INSERT PAYMENT GAGAL', [
                        'diagnostic_id' => $diagnosticId,
                        'registration_id' => $registrationId,
                        'amount' => $amount,
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'sql_state' => $e instanceof \Illuminate\Database\QueryException
                            ? ($e->errorInfo[0] ?? null)
                            : null,
                        'error_info' => $e instanceof \Illuminate\Database\QueryException
                            ? ($e->errorInfo ?? null)
                            : null,
                    ]);

                    throw $e;
                }

                Log::info('OFFLINE_DIAG: payment berhasil', [
                    'diagnostic_id' => $diagnosticId,
                    'registration_id' => $registrationId,
                    'amount' => $amount,
                ]);

                return $registrationId;
            });

            Log::info('OFFLINE_DIAG: TRANSAKSI BERHASIL', [
                'diagnostic_id' => $diagnosticId,
                'registration_id' => $registrationId,
            ]);

            // Sinkronisasi dijalankan setelah transaksi Neon selesai.
            $sheetsSynced = $this->syncOfflineRegistrationToGoogleSheets(
                $db,
                $registrationId,
                $diagnosticId
            );

            $successMessage = 'Pendaftaran offline berhasil disimpan ke Neon. Pembayaran otomatis VERIFIED. ID pendaftaran: '
                . $registrationId;

            if ($sheetsSynced) {
                $successMessage .= '. Data juga berhasil disinkronkan ke Google Sheets.';
            } else {
                $successMessage .= '. PERHATIAN: data aman di Neon, tetapi sinkronisasi Google Sheets gagal. Periksa storage/logs/laravel.log.';
            }

            return redirect()
                ->route('ofline.index')
                ->with('success', $successMessage);
        } catch (Throwable $e) {
            Log::error('OFFLINE_DIAG: TRANSAKSI GAGAL', [
                'diagnostic_id' => $diagnosticId,
                'type' => $type,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'sql_state' => $e instanceof \Illuminate\Database\QueryException
                    ? ($e->errorInfo[0] ?? null)
                    : null,
                'error_info' => $e instanceof \Illuminate\Database\QueryException
                    ? ($e->errorInfo ?? null)
                    : null,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    /**
     * Mengirim data pendaftaran offline ke Google Sheets melalui Apps Script.
     */
    private function syncOfflineRegistrationToGoogleSheets(
        $db,
        $registrationId,
        $diagnosticId
    ): bool {
        $webhookUrl = trim((string) config('google_sheets.webhook_url', ''));
        $webhookSecret = trim((string) config('google_sheets.webhook_secret', ''));

        if ($webhookUrl === '' || $webhookSecret === '') {
            Log::warning('GOOGLE_SHEETS_SYNC: konfigurasi belum lengkap', [
                'diagnostic_id' => $diagnosticId,
                'registration_id' => $registrationId,
                'has_url' => $webhookUrl !== '',
                'has_secret' => $webhookSecret !== '',
            ]);

            return false;
        }

        try {
            $registration = $db->table('registration')
                ->where('id', $registrationId)
                ->where('source', 'OFFLINE')
                ->first();

            if (!$registration) {
                throw new \RuntimeException(
                    'Pendaftaran offline tidak ditemukan saat sinkronisasi Sheets.'
                );
            }

            $payment = $db->table('payment')
                ->where('registrationId', $registrationId)
                ->first();

            if (!$payment) {
                throw new \RuntimeException(
                    'Pembayaran tidak ditemukan saat sinkronisasi Sheets.'
                );
            }

            $participants = $db->table('participant')
                ->where('registrationId', $registrationId)
                ->orderBy('id')
                ->get([
                    'id',
                    'registrationId',
                    'fullName',
                    'school',
                    'field',
                    'createdAt',
                ]);

            $team = $db->table('team')
                ->where('registrationId', $registrationId)
                ->first([
                    'id',
                    'registrationId',
                    'name',
                    'school',
                    'createdAt',
                ]);

            $participantPayload = $participants->values()->map(
                function ($participant, $index) use ($registration, $payment) {
                    $amount = (int) ($payment->amount ?? 0);

                    if ($registration->type === 'COLLECTIVE') {
                        $amount = (($index + 1) % 11 === 0)
                            ? 0
                            : self::SCIENCE_PRICE;
                    }

                    return [
                        'id' => (string) $participant->id,
                        'registrationId' => (string) $participant->registrationId,
                        'fullName' => (string) $participant->fullName,
                        'school' => (string) $participant->school,
                        'field' => strtoupper((string) $participant->field),
                        'whatsapp' => (string) $registration->whatsapp,
                        'amount' => $amount,
                        'paymentStatus' => (string) $payment->status,
                        'createdAt' => (string) (
                            $registration->createdAt
                            ?? $participant->createdAt
                            ?? now()->toDateTimeString()
                        ),
                        'type' => (string) $registration->type,
                        'source' => 'OFFLINE',
                    ];
                }
            )->all();

            $payload = [
                'action' => 'appendOfflineRegistration',
                'secret' => $webhookSecret,
                'registration' => [
                    'id' => (string) $registration->id,
                    'type' => (string) $registration->type,
                    'source' => 'OFFLINE',
                    'whatsapp' => (string) $registration->whatsapp,
                    'createdAt' => (string) (
                        $registration->createdAt
                        ?? now()->toDateTimeString()
                    ),
                    'amount' => (int) $payment->amount,
                    'paymentStatus' => (string) $payment->status,
                ],
                'participants' => $participantPayload,
                'team' => $team ? [
                    'id' => (string) $team->id,
                    'registrationId' => (string) $team->registrationId,
                    'name' => (string) $team->name,
                    'school' => (string) $team->school,
                ] : null,
            ];

            $response = Http::asJson()
                ->timeout(20)
                ->retry(2, 500)
                ->post($webhookUrl, $payload);

            $body = $response->json();

            $accepted = $response->successful()
                && is_array($body)
                && (($body['ok'] ?? false) === true);

            if (!$accepted) {
                Log::error('GOOGLE_SHEETS_SYNC: webhook menolak atau gagal', [
                    'diagnostic_id' => $diagnosticId,
                    'registration_id' => $registrationId,
                    'http_status' => $response->status(),
                    'response_body' => mb_substr(
                        (string) $response->body(),
                        0,
                        1200
                    ),
                ]);

                return false;
            }

            Log::info('GOOGLE_SHEETS_SYNC: berhasil', [
                'diagnostic_id' => $diagnosticId,
                'registration_id' => $registrationId,
                'added_rows' => $body['added'] ?? null,
                'skipped_rows' => $body['skipped'] ?? null,
            ]);

            return true;
        } catch (Throwable $e) {
            Log::warning('GOOGLE_SHEETS_SYNC: exception', [
                'diagnostic_id' => $diagnosticId,
                'registration_id' => $registrationId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Detail satu pendaftaran offline.
     */
    public function show($id)
    {
        $db = DB::connection('neon');

        $registration = $db->table('registration as r')
            ->leftJoin('payment as p', 'p.registrationId', '=', 'r.id')
            ->where('r.id', $id)
            ->where('r.source', 'OFFLINE')
            ->select(
                'r.*',
                'p.amount',
                'p.status as payment_status',
                'p.paymentMethod',
                'p.proofUrl'
            )
            ->first();

        abort_unless(
            $registration,
            404,
            'Data offline tidak ditemukan.'
        );

        $participants = $db->table('participant')
            ->where('registrationId', $id)
            ->orderBy('createdAt')
            ->get();

        $team = $db->table('team')
            ->where('registrationId', $id)
            ->first();

        return view(
            'admin.datadiripeserta.ofline.show',
            compact('registration', 'participants', 'team')
        );
    }

    /**
     * Edit belum diaktifkan untuk data Neon.
     */
    public function edit($id)
    {
        abort(501, 'Edit pendaftaran offline Neon belum diaktifkan.');
    }

    /**
     * Update belum diaktifkan untuk data Neon.
     */
    public function update(Request $request, $id)
    {
        abort(501, 'Update pendaftaran offline Neon belum diaktifkan.');
    }

    /**
     * Menghapus pendaftaran offline beserta data terkait.
     */
    public function destroy($id)
    {
        $db = DB::connection('neon');

        $db->transaction(function () use ($db, $id) {
            $registration = $db->table('registration')
                ->where('id', $id)
                ->where('source', 'OFFLINE')
                ->first();

            abort_unless(
                $registration,
                404,
                'Data offline tidak ditemukan.'
            );

            $db->table('payment')
                ->where('registrationId', $id)
                ->delete();

            $db->table('participant')
                ->where('registrationId', $id)
                ->delete();

            $teamIds = $db->table('team')
                ->where('registrationId', $id)
                ->pluck('id');

            if ($teamIds->isNotEmpty()) {
                $db->table('teamMember')
                    ->whereIn('teamId', $teamIds)
                    ->delete();
            }

            $db->table('team')
                ->where('registrationId', $id)
                ->delete();

            $db->table('registration')
                ->where('id', $id)
                ->delete();
        });

        return redirect()
            ->route('ofline.index')
            ->with(
                'success',
                'Pendaftaran offline berhasil dihapus dari Neon.'
            );
    }

    /**
     * Kompatibilitas dengan route lama.
     */
    public function pilihMapel(Request $request)
    {
        return redirect()
            ->route('ofline.index')
            ->with(
                'error',
                'Pemilihan lomba melalui form lama sudah tidak digunakan. Gunakan form pendaftaran offline.'
            );
    }
}
