<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Imports\KolektifImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\DataDiri;
use App\Models\Infolomba;

class ImportdatakolektifController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | IMPORT EXCEL KOLEKTIF
    |--------------------------------------------------------------------------
    */

    public function importExcelkolektif(Request $request)
    {
        $import = new KolektifImport($request);

        Excel::import(
            $import,
            $request->file('file')
        );

        return back()->with('hasil_import', [
            'total' => $import->total,
            'berhasil' => $import->berhasil,
            'gagal' => count($import->gagal),
            'gagal_nama' => $import->gagal,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | DATA PESERTA KOLEKTIF
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | AMBIL PENDAFTARAN KOLEKTIF DARI NEON
        |--------------------------------------------------------------------------
        */

        $registrations = DB::connection('neon')
            ->table('registration as r')
            ->join('user as u', 'u.id', '=', 'r.userId')
            ->leftJoin('payment as p', 'p.registrationId', '=', 'r.id')
            ->select(
                'r.id as registration_id',
                'r.type',
                'r.userId',
                'r.createdAt',

                'u.name as user_name',
                'u.email as user_email',

                'p.id as payment_id',
                'p.status as payment_status',
                'p.proofUrl as proof_url',
                'p.amount as payment_amount'
            )
            ->where('r.type', 'COLLECTIVE')
            ->orderByDesc('r.createdAt')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | SIAPKAN DATA UNTUK BLADE LAMA
        |--------------------------------------------------------------------------
        */

        $pesertaKolektif = collect();


        foreach ($registrations as $registration) {

            /*
            |--------------------------------------------------------------------------
            | AMBIL SEMUA PESERTA DALAM REGISTRASI INI
            |--------------------------------------------------------------------------
            */

            $participants = DB::connection('neon')
                ->table('participant')
                ->where(
                    'registrationId',
                    $registration->registration_id
                )
                ->orderBy('id')
                ->get();


            /*
            |--------------------------------------------------------------------------
            | SATU REGISTRASI KOLEKTIF BISA BERISI BANYAK PESERTA
            |--------------------------------------------------------------------------
            */

            foreach ($participants as $participant) {

                $data = (object) [

                    /*
                    | ID peserta Neon
                    */
                    'id' =>
                        $participant->id,
                    'nomor_peserta' => null,

                    /*
                    | ID registrasi Neon
                    */
                    'registration_id' =>
                        $registration->registration_id,

                    /*
                    | Data peserta
                    */
                    'nama_lengkap' =>
                        $participant->fullName,

                    'sekolah' =>
                        $participant->school,

                    'bidang' =>
                        $participant->field,

                    /*
                    | Data akun pendaftar
                    */
                    'user_id' =>
                        $registration->userId,

                    'email' =>
                        $registration->user_email,

                    'user_name' =>
                        $registration->user_name,

                    /*
                    | Jenis pendaftaran
                    */
                    'jenis_daftar' =>
                        'kolektif',

                    'type' =>
                        $registration->type,

                    /*
                    | Pembayaran
                    */
                    'payment_id' =>
                        $registration->payment_id,

                    'payment_status' =>
                        $registration->payment_status ?? 'PENDING',

                    'payment_amount' =>
                        $registration->payment_amount,

                    'proof_url' =>
                        $registration->proof_url,

                    /*
                    | Tanggal
                    */
                    'created_at' =>
                        $registration->createdAt,

                    'createdAt' =>
                        $registration->createdAt,
                ];


                $pesertaKolektif->push($data);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | DATA INFO LOMBA
        |--------------------------------------------------------------------------
        */

        $infolomba = Infolomba::all();


        /*
        |--------------------------------------------------------------------------
        | KIRIM KE VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.datadiripeserta.kolektif.index',
            compact(
                'pesertaKolektif',
                'infolomba'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HAPUS DATA
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $peserta = DataDiri::findOrFail($id);

        // Hapus semua tagihan terkait
        $peserta->tagihan()->delete();

        // Hapus peserta
        $peserta->delete();

        return redirect()
            ->route('kolektif.index')
            ->with(
                'success',
                'Data berhasil dihapus.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DETAIL DATA
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $peserta = DataDiri::findOrFail($id);

        return view(
            'admin.datadiripeserta.kolektif.show',
            compact('peserta')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT DATA
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $peserta = DataDiri::findOrFail($id);

        return view(
            'admin.datadiripeserta.kolektif.edit',
            compact('peserta')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE DATA
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $peserta = DataDiri::findOrFail($id);

        $request->validate([
            'nisn' =>
                'required|numeric|unique:datadiri,nisn,' . $peserta->id,

            'nama_lengkap' =>
                'required|string|max:255',

            'sekolah' =>
                'required|string|max:255',

            'tempat_lahir' =>
                'required|string|max:255',

            'tanggal_lahir' =>
                'required|date',

            'jenis_kelamin' =>
                'required|string',

            'alamat' =>
                'required|string',

            'nomor_hp' =>
                'required|string|max:15',
        ]);

        $peserta->update(
            $request->all()
        );

        return redirect()
            ->route(
                'kolektif.show',
                $id
            )
            ->with(
                'success',
                'Data berhasil diperbarui.'
            );
    }
}