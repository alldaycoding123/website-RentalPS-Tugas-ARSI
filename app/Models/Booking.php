<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'booking';

    protected $primaryKey = 'id_booking';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'kode',
        'id_pelanggan',
        'id_unit',
        'id_staff',
        'tgl_main',
        'mulai',
        'selesai',
        'durasi_jam',
        'total',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tgl_main' => 'date:Y-m-d',
            'total' => 'decimal:2',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitPs::class, 'id_unit');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'id_staff');
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'id_booking');
    }

    /**
     * Status yang membuat slot unit dianggap terpakai.
     */
    public const STATUS_PAKAI = ['PENDING', 'CONFIRMED', 'CHECKIN', 'SELESAI'];
}
