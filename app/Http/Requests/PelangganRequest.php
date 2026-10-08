<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PelangganRequest extends FormRequest
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
        $pelangganId = $this->route('pelanggan')?->id_pelanggan ?? $this->route('pelanggan');

        return [
            'nama' => ['required', 'string', 'max:100'],
            'no_hp' => [
                'required', 'string', 'regex:/^(08|628|\+628)[0-9]{8,11}$/',
                Rule::unique('pelanggan', 'no_hp')->ignore($pelangganId, 'id_pelanggan'),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama' => 'nama pelanggan',
            'no_hp' => 'No. HP',
        ];
    }

    public function messages(): array
    {
        return [
            'no_hp.regex' => 'Format No. HP tidak valid. Gunakan 08xxxxxxxxxx atau +628xxxxxxxxxx (10–14 digit).',
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
