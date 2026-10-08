<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $primaryKey = 'id_bayar';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_booking',
        'id_staff',
        'metode',
        'nominal',
        'status',
        'waktu',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'waktu' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'id_booking');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'id_staff');
    }
}
