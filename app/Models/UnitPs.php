<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitPs extends Model
{
    use HasFactory;

    protected $table = 'unit_ps';

    protected $primaryKey = 'id_unit';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'kode_unit',
        'nama',
        'tipe',
        'ruang',
        'tv',
        'harga_jam',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'harga_jam' => 'decimal:2',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'id_unit');
    }
}
