<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Durasi & total dihitung server-side dari session draft slot.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'string', 'regex:/^(08|628|\+628)[0-9]{8,11}$/'],
            'metode' => ['required', 'in:TRANSFER_BANK,E_WALLET,BAYAR_DI_TEMPAT'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama' => 'nama lengkap',
            'no_hp' => 'No. HP',
            'metode' => 'metode pembayaran',
        ];
    }

    public function prepareForValidation(): void
    {
        if ($this->filled('no_hp')) {
            $noHp = preg_replace('/[\s\-]/', '', (string) $this->input('no_hp'));

            if (str_starts_with($noHp, '+628')) {
                $noHp = '0'.substr($noHp, 4);
            } elseif (str_starts_with($noHp, '628')) {
                $noHp = '0'.substr($noHp, 3);
            }

            $this->merge(['no_hp' => $noHp]);
        }
    }
}
