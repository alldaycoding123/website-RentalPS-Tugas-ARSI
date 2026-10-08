<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWalkinBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Booking walk-in dibuat admin; total tetap dihitung server-side.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_unit' => ['required', 'integer', 'exists:unit_ps,id_unit'],
            'id_pelanggan' => ['required', 'integer', 'exists:pelanggan,id_pelanggan'],
            'tgl_main' => ['required', 'date', 'after_or_equal:today'],
            'mulai' => ['required', 'date_format:H:i', 'regex:/^(09|1[0-9]|2[0-1]):00$/'],
            'durasi_jam' => ['required', 'integer', 'min:1', 'max:6'],
            'metode' => ['required', 'in:TRANSFER_BANK,E_WALLET,BAYAR_DI_TEMPAT'],
        ];
    }

    public function attributes(): array
    {
        return [
            'id_unit' => 'unit PS',
            'id_pelanggan' => 'pelanggan',
            'tgl_main' => 'tanggal main',
            'mulai' => 'jam mulai',
            'durasi_jam' => 'durasi',
            'metode' => 'metode pembayaran',
        ];
    }
}
