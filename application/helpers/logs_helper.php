<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('formatarTamanho')) {
    /**
     * Formata tamanho de arquivo em bytes para formato legível
     * 
     * @param int $bytes Tamanho em bytes
     * @return string Tamanho formatado (ex: "1.5 MB")
     */
    function formatarTamanho($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

