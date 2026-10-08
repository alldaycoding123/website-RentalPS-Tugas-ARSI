<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginPelangganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Format HP Indonesia: 08xx / +628xx, 10–14 digit (atau token QR sesi "PSR:...")
            'no_hp' => ['required', 'string', 'regex:/^((08|628|\+628)[0-9]{8,11}|PSR:[A-Za-z0-9\-]+)$/'],
        ];
    }

    public function attributes(): array
    {
        return [
            'no_hp' => 'No. HP',
        ];
    }

    public function messages(): array
    {
        return [
            'no_hp.regex' => 'Format No. HP tidak valid. Gunakan format 08xxxxxxxxxx atau +628xxxxxxxxxx (10–14 digit).',
        ];
    }

    /**
     * Normalisasi No. HP sebelum divalidasi: 628xx -> 08xx.
     */
    public function prepareForValidation(): void
    {
        if ($this->filled('no_hp')) {
            $noHp = preg_replace('/[\s\-]/', '', (string) $this->input('no_hp'));

            // Token QR sesi tidak dinormalisasi
            if (str_starts_with($noHp, 'PSR:')) {
                $this->merge(['no_hp' => $noHp]);

                return;
            }

            if (str_starts_with($noHp, '+628')) {
                $noHp = '0'.substr($noHp, 4);
            } elseif (str_starts_with($noHp, '628')) {
                $noHp = '0'.substr($noHp, 3);
            }

            $this->merge(['no_hp' => $noHp]);
        }
    }
}
