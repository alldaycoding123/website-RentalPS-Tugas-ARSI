<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';

    protected $primaryKey = 'id_pelanggan';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'nama',
        'no_hp',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'id_pelanggan');
    }
}
