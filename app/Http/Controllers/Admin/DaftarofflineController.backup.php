
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DaftarofflineController extends Controller
{
    private const SCIENCE_PRICE = 35000;
    private const FUTSAL_PRICE = 150000;

    /**
     * Menampilkan daftar pendaftaran offline.
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

        $totalPendaftaran = $registrations->count();

        $totalPeserta = $registrations->sum(
            fn ($item) => (int) ($item->participant_count ?? 0)
        );

        $totalTim = $registrations
            ->where('type', 'FUTSAL')
            ->count();

        $totalVerified = $registrations
            ->where('payment_status', 'VERIFIED')
            ->count();

        $totalPendapatan = $registrations
            ->where('payment_status', 'VERIFIED')
            ->sum(fn ($item) => (int) ($item->amount ?? 0));

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
     * Menampilkan formulir pendaftaran offline.
     */
    public function create()
    {
        return view('admin.datadiripeserta.ofline.create');
    }

    /**
     * Menyimpan pendaftaran offline ke Neon.
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

        Log::info('OFFLINE_DIAG: permintaan tervalidasi', [
            'type' => $type,
        ]);

        try {
            $registrationId = $db->transaction(
                function () use ($db, $validated, $type) {
                    Log::info('OFFLINE_DIAG: transaksi dimulai', [
                        'type' => $type,
                    ]);

                    try {
                        $now = now();

                        // 1. Buat data pendaftaran.
                        Log::info('OFFLINE_DIAG: sebelum insert registration');

                        $registrationId = $db->table('registration')
                            ->insertGetId([
                                'type' => $type,
                                'source' => 'OFFLINE',
                                'userId' => null,
                                'whatsapp' => $validated['whatsapp'],
                                'createdAt' => $now,
                                'updatedAt' => $now,
                            ]);

                        Log::info('OFFLINE_DIAG: registration berhasil', [
                            'registration_id' => $registrationId,
                        ]);

                        $amount = 0;

                        // 2. Sains individu.
                        if ($type === 'SELF') {
                            Log::info('OFFLINE_DIAG: sebelum insert participant', [
                                'registration_id' => $registrationId,
                            ]);

                            $db->table('participant')->insert([
                                'registrationId' => $registrationId,
                                'fullName' => $validated['fullName'],
                                'school' => $validated['school'],
                                'field' => $validated['field'],
                                'createdAt' => $now,
                                'updatedAt' => $now,
                            ]);

                            Log::info('OFFLINE_DIAG: participant berhasil', [
                                'registration_id' => $registrationId,
                            ]);

                            $amount = self::SCIENCE_PRICE;
                        }

                        // 3. Sains kolektif: 10 berbayar + 1 gratis.
                        elseif ($type === 'COLLECTIVE') {
                            $participants = $validated['participants'];
                            $totalParticipants = count($participants);

                            foreach ($participants as $index => $participant) {
                                Log::info(
                                    'OFFLINE_DIAG: sebelum insert peserta kolektif',
                                    [
                                        'registration_id' => $registrationId,
                                        'participant_index' => $index,
                                    ]
                                );

                                $db->table('participant')->insert([
                                    'registrationId' => $registrationId,
                                    'fullName' => $participant['fullName'],
                                    'school' => $validated['school'],
                                    'field' => $participant['field'],
                                    'createdAt' => $now,
                                    'updatedAt' => $now,
                                ]);

                                Log::info(
                                    'OFFLINE_DIAG: peserta kolektif berhasil',
                                    [
                                        'registration_id' => $registrationId,
                                        'participant_index' => $index,
                                    ]
                                );
                            }

                            $freeParticipants = intdiv($totalParticipants, 11);
                            $paidParticipants = $totalParticipants - $freeParticipants;

                            $amount = $paidParticipants * self::SCIENCE_PRICE;
                        }

                        // 4. Futsal: data tim tanpa nama anggota.
                        elseif ($type === 'FUTSAL') {
                            Log::info('OFFLINE_DIAG: sebelum insert team', [
                                'registration_id' => $registrationId,
                            ]);

                            $db->table('team')->insert([
                                'registrationId' => $registrationId,
                                'name' => $validated['teamName'],
                                'school' => $validated['school'],
                                'createdAt' => $now,
                                'updatedAt' => $now,
                            ]);

                            Log::info('OFFLINE_DIAG: team berhasil', [
                                'registration_id' => $registrationId,
                            ]);

                            $amount = self::FUTSAL_PRICE;
                        }

                        // 5. Pembayaran offline langsung VERIFIED.
                        Log::info('OFFLINE_DIAG: sebelum insert payment', [
                            'registration_id' => $registrationId,
                            'amount' => $amount,
                        ]);

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

                        Log::info('OFFLINE_DIAG: payment berhasil', [
                            'registration_id' => $registrationId,
                        ]);

                        Log::info('OFFLINE_DIAG: semua insert selesai', [
                            'registration_id' => $registrationId,
                        ]);

                        return $registrationId;
                    } catch (\Throwable $e) {
                        Log::error('OFFLINE_DIAG: operasi dalam transaksi gagal', [
                            'type' => $type,
                            'message' => $e->getMessage(),
                            'exception' => get_class($e),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'previous' => $e->getPrevious()
                                ? $e->getPrevious()->getMessage()
                                : null,
                        ]);

                        throw $e;
                    }
                }
            );
        } catch (\Throwable $e) {
            Log::error('OFFLINE_DIAG: transaksi gagal atau dibatalkan', [
                'type' => $type,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'previous' => $e->getPrevious()
                    ? $e->getPrevious()->getMessage()
                    : null,
            ]);

            throw $e;
        }

        return redirect()
            ->route('ofline.index')
            ->with(
                'success',
                'Pendaftaran offline berhasil disimpan ke Neon. '
                . 'Pembayaran otomatis VERIFIED. ID pendaftaran: '
                . $registrationId
            );
    }

    /**
     * Menampilkan detail pendaftaran offline.
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
     * Fitur edit belum diaktifkan.
     */
    public function edit($id)
    {
        abort(501, 'Edit pendaftaran offline Neon belum diaktifkan.');
    }

    /**
     * Fitur update belum diaktifkan.
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

            $teamIds = $db->table('team')
                ->where('registrationId', $id)
                ->pluck('id');

            if ($teamIds->isNotEmpty()) {
                $db->table('teamMember')
                    ->whereIn('teamId', $teamIds)
                    ->delete();
            }

            $db->table('payment')
                ->where('registrationId', $id)
                ->delete();

            $db->table('participant')
                ->where('registrationId', $id)
                ->delete();

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
     * Pemilihan lomba melalui form lama tidak digunakan.
     */
    public function pilihMapel(Request $request)
    {
        return redirect()
            ->route('ofline.index')
            ->with(
                'error',
                'Pemilihan lomba melalui form lama sudah tidak digunakan. '
                . 'Gunakan form pendaftaran offline.'
            );
    }
}
