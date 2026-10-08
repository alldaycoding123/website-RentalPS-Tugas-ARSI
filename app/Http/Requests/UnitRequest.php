<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
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
        $unitId = $this->route('unit')?->id_unit ?? $this->route('unit');

        return [
            'kode_unit' => [
                'required', 'string', 'max:20',
                Rule::unique('unit_ps', 'kode_unit')->ignore($unitId, 'id_unit'),
            ],
            'nama' => ['required', 'string', 'max:100'],
            'tipe' => ['required', 'in:PS4,PS5'],
            'ruang' => ['required', 'string', 'max:50'],
            'tv' => ['required', 'string', 'max:150'],
            'harga_jam' => ['required', 'numeric', 'min:1000', 'max:1000000'],
            'status' => ['required', 'in:TERSEDIA,MAINTENANCE,DIGUNAKAN'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kode_unit' => 'kode unit',
            'nama' => 'nama unit',
            'tipe' => 'tipe',
            'ruang' => 'ruang',
            'tv' => 'TV / kelengkapan',
            'harga_jam' => 'harga per jam',
            'status' => 'status',
        ];
    }
}
