<?php

use Illuminate\Support\Carbon;

if (! function_exists('rupiah')) {
    /**
     * Format nilai menjadi Rupiah: 30000 -> "Rp 30.000".
     */
    function rupiah($nilai): string
    {
        return 'Rp '.number_format((float) $nilai, 0, ',', '.');
    }
}

if (! function_exists('jam')) {
    /**
     * Format jam database (H:i:s / H:i) menjadi H:i: 14:00:00 -> "14:00".
     */
    function jam($waktu): string
    {
        return substr((string) $waktu, 0, 5);
    }
}

if (! function_exists('tanggal_id')) {
    /**
     * Format tanggal Indonesia: 2026-10-08 -> "08 Okt 2026".
     */
    function tanggal_id($tanggal): string
    {
        return Carbon::parse($tanggal)->translatedFormat('d M Y');
    }
}
