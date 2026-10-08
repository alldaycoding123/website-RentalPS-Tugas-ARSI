<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar saat rentang jam booking baru bentrok dengan booking lain
 * pada unit & tanggal yang sama.
 */
class SlotBentrokException extends RuntimeException
{
}
