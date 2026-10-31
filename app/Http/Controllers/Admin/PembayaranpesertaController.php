<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class PembayaranpesertaController extends Controller
{
    private function backendUrl()
    {
        return config(
            'app.backend_url',
            env('BACKEND_URL', 'http://localhost:3001')
        );
    }

    private function adminHeaders()
    {
        return [
            'X-Admin-Api-Key' => env('ADMIN_API_KEY'),
        ];
    }

    public function index()
    {
        try {
            $response = Http::withHeaders(
                $this->adminHeaders()
            )->get(
                $this->backendUrl() . '/payments'
            );

            if (!$response->successful()) {
                return back()->with(
                    'error',
                    'Gagal mengambil data pembayaran dari backend'
                );
            }

            $payments = $response->json();

            $valid = collect($payments)
                ->where('status', 'VERIFIED');

            $belumvalid = collect($payments)
                ->where('status', 'PENDING');

            $ditolak = collect($payments)
                ->where('status', 'REJECTED');

            return view(
                'admin.pembayaran.index',
                compact(
                    'valid',
                    'belumvalid',
                    'ditolak'
                )
            );

        } catch (\Exception $e) {

            return view(
                'admin.pembayaran.index',
                [
                    'valid' => collect(),
                    'belumvalid' => collect(),
                    'ditolak' => collect(),
                ]
            )->with(
                'error',
                'Backend tidak dapat dihubungi'
            );
        }
    }

    public function validbukti(Request $request, $id)
    {
        try {
            $response = Http::withHeaders(
                $this->adminHeaders()
            )->patch(
                $this->backendUrl()
                . "/payments/{$id}/verify"
            );

            if (!$response->successful()) {
                return redirect(
                    $request->input(
                        'redirect_to',
                        route('pembayaran.index')
                    )
                )->with(
                    'error',
                    'Gagal memverifikasi pembayaran'
                );
            }

            return redirect(
                $request->input(
                    'redirect_to',
                    route('pembayaran.index')
                )
            )->with(
                'success',
                'Pembayaran berhasil diverifikasi'
            );

        } catch (\Exception $e) {

            return redirect(
                $request->input(
                    'redirect_to',
                    route('pembayaran.index')
                )
            )->with(
                'error',
                'Backend tidak dapat dihubungi'
            );
        }
    }

    public function rejectbukti(Request $request, $id)
    {
        try {
            $response = Http::withHeaders(
                $this->adminHeaders()
            )->patch(
                $this->backendUrl()
                . "/payments/{$id}/reject"
            );

            if (!$response->successful()) {
                return redirect(
                    $request->input(
                        'redirect_to',
                        route('pembayaran.index')
                    )
                )->with(
                    'error',
                    'Gagal menolak pembayaran'
                );
            }

            return redirect(
                $request->input(
                    'redirect_to',
                    route('pembayaran.index')
                )
            )->with(
                'success',
                'Pembayaran berhasil ditolak'
            );

        } catch (\Exception $e) {

            return redirect(
                $request->input(
                    'redirect_to',
                    route('pembayaran.index')
                )
            )->with(
                'error',
                'Backend tidak dapat dihubungi'
            );
        }
    }
}