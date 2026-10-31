<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DataDiri;

class DatadiripesertaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR ONLINE
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $registrations = DB::connection('neon')
            ->table('registration as r')
            ->leftjoin('user as u', 'u.id', '=', 'r.userId')
            ->leftJoin('payment as p', 'p.registrationId', '=', 'r.id')
            ->leftJoin('participant as pt', 'pt.registrationId', '=', 'r.id')
            ->select(
                'r.id as registration_id',
                'r.type',
                'r.createdAt',
                'u.name as user_name',
                'u.email as user_email',
                'pt.fullName as participant_name',
                'pt.school as school',
                'pt.field as field',
                'p.status as payment_status',
                'p.proofUrl as proof_url',
                'p.amount as payment_amount'
            )
            ->whereIn('r.type', [
                'SELF',
                'COLLECTIVE',
            ])
            ->orderByDesc('r.createdAt')
            ->get();

        $dataValid = collect();
        $dataPending = collect();
        $dataRejected = collect();

        foreach ($registrations as $registration) {
            $data = (object) [
                'registration_id' =>
                    $registration->registration_id,

                'name' =>
                    $registration->participant_name
                    ?? $registration->user_name,

                'email' =>
                    $registration->user_email,

                'school' =>
                    $registration->school ?? '-',

                'field' =>
                    $registration->field ?? '-',

                'type' =>
                    $registration->type,

                'payment_status' =>
                    $registration->payment_status ?? 'PENDING',

                'proof_url' =>
                    $registration->proof_url,

                'payment_amount' =>
                    $registration->payment_amount,

                'created_at' =>
                    $registration->createdAt,
            ];

            if ($data->payment_status === 'VERIFIED') {
                $dataValid->push($data);
            } elseif ($data->payment_status === 'REJECTED') {
                $dataRejected->push($data);
            } else {
                $dataPending->push($data);
            }
        }

        return view(
            'admin.datadiripeserta.online.index',
            compact(
                'dataValid',
                'dataPending',
                'dataRejected'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SEMUA DATA PESERTA
    |--------------------------------------------------------------------------
    */

    public function semuaData()
    {
        $registrations = DB::connection('neon')
            ->table('registration as r')
            ->leftjoin('user as u', 'u.id', '=', 'r.userId')
            ->leftJoin('payment as p', 'p.registrationId', '=', 'r.id')
            ->select(
                'r.id as registration_id',
                'r.type',
                'r.createdAt',
                'u.name as user_name',
                'u.email as user_email',
                'p.status as payment_status',
                'p.proofUrl as proof_url',
                'p.amount as payment_amount'
            )
            ->orderByDesc('r.createdAt')
            ->get();

        $dataValid = collect();
        $dataPending = collect();
        $dataRejected = collect();

        foreach ($registrations as $registration) {
            $participants = DB::connection('neon')
                ->table('participant')
                ->where(
                    'registrationId',
                    $registration->registration_id
                )
                ->get();

            foreach ($participants as $participant) {
                $data = (object) [
                    'registration_id' =>
                        $registration->registration_id,

                    'name' =>
                        $participant->fullName,

                    'email' =>
                        $registration->user_email,

                    'school' =>
                        $participant->school,

                    'field' =>
                        $participant->field,

                    'type' =>
                        $registration->type,

                    'payment_status' =>
                        $registration->payment_status ?? 'PENDING',

                    'proof_url' =>
                        $registration->proof_url,

                    'payment_amount' =>
                        $registration->payment_amount,

                    'created_at' =>
                        $registration->createdAt,
                ];

                if ($data->payment_status === 'VERIFIED') {
                    $dataValid->push($data);
                } elseif ($data->payment_status === 'REJECTED') {
                    $dataRejected->push($data);
                } else {
                    $dataPending->push($data);
                }
            }

            $team = DB::connection('neon')
                ->table('team')
                ->where(
                    'registrationId',
                    $registration->registration_id
                )
                ->first();

            if ($team) {
                $teamData = (object) [
                    'registration_id' =>
                        $registration->registration_id,

                    'name' =>
                        $team->name,

                    'email' =>
                        $registration->user_email,

                    'school' =>
                        $team->school,

                    'field' =>
                        'FUTSAL',

                    'type' =>
                        $registration->type,

                    'payment_status' =>
                        $registration->payment_status ?? 'PENDING',

                    'proof_url' =>
                        $registration->proof_url,

                    'payment_amount' =>
                        $registration->payment_amount,

                    'created_at' =>
                        $registration->createdAt,
                ];

                if ($teamData->payment_status === 'VERIFIED') {
                    $dataValid->push($teamData);
                } elseif ($teamData->payment_status === 'REJECTED') {
                    $dataRejected->push($teamData);
                } else {
                    $dataPending->push($teamData);
                }
            }
        }

        return view(
            'admin.datadiripeserta.index',
            compact(
                'dataValid',
                'dataPending',
                'dataRejected'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DATA PESERTA FUTSAL
    |--------------------------------------------------------------------------
    */

    public function futsal()
    {
        $registrations = DB::connection('neon')
            ->table('registration as r')
            ->leftjoin('user as u', 'u.id', '=', 'r.userId')
            ->leftJoin('payment as p', 'p.registrationId', '=', 'r.id')
            ->join('team as t', 't.registrationId', '=', 'r.id')
            ->select(
                'r.id as registration_id',
                'r.type',
                'r.createdAt',
                'u.email as user_email',
                't.id as team_id',
                't.name as team_name',
                't.school as school',
                'p.id as payment_id',
                'p.status as payment_status',
                'p.proofUrl as proof_url',
                'p.amount as payment_amount'
            )
            ->where('r.type', 'FUTSAL')
            ->orderByDesc('r.createdAt')
            ->get();

        $dataPending = collect();
        $dataValid = collect();
        $dataRejected = collect();

        foreach ($registrations as $registration) {

            $totalMembers = DB::connection('neon')
                ->table('teamMember')
                ->where(
                    'teamId',
                    $registration->team_id
                )
                ->count();

            $data = (object) [
                'registration_id' =>
                    $registration->registration_id,

                'team_id' =>
                    $registration->team_id,
                
                'payment_id' =>
                    $registration->payment_id,

                'team_name' =>
                    $registration->team_name,

                'email' =>
                    $registration->user_email,

                'school' =>
                    $registration->school,

                'total_members' =>
                    $totalMembers,

                'payment_status' =>
                    $registration->payment_status ?? 'PENDING',

                'proof_url' =>
                    $registration->proof_url,

                'payment_amount' =>
                    $registration->payment_amount,

                'created_at' =>
                    $registration->createdAt,
            ];

            if ($data->payment_status === 'VERIFIED') {

                $dataValid->push($data);

            } elseif ($data->payment_status === 'REJECTED') {

                $dataRejected->push($data);

            } else {

                $dataPending->push($data);
            }
        }

        return view(
            'admin.datadiripeserta.futsal.index',
            compact(
                'dataPending',
                'dataValid',
                'dataRejected'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | KONFIRMASI PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    public function confirmPayment($registrationId)
    {
        DB::connection('neon')
            ->table('payment')
            ->where(
                'registrationId',
                $registrationId
            )
            ->update([
                'status' => 'VERIFIED',
            ]);

        return redirect()
            ->back()
            ->with(
                'success',
                'Pembayaran berhasil dikonfirmasi!'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | TOLAK PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    public function rejectPayment($registrationId)
    {
        DB::connection('neon')
            ->table('payment')
            ->where(
                'registrationId',
                $registrationId
            )
            ->update([
                'status' => 'REJECTED',
            ]);

        return redirect()
            ->back()
            ->with(
                'success',
                'Pembayaran berhasil ditolak!'
            );
    }
}