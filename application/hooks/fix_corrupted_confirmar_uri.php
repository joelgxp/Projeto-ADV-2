<?php
/**
 * Hook: Corrige URLs corrompidas por clientes de e-mail
 * Ex: usuarios/conf=%20irmar_email/TOKEN → redireciona para definir-senha?t=TOKEN
 * Executado em pre_system ANTES do URI parse para evitar 400 Bad Request (chars não permitidos)
 */
defined('BASEPATH') || exit('No direct script access allowed');

function fix_corrupted_confirmar_uri()
{
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    if (empty($uri)) {
        return;
    }

    // Extrair path (sem query string)
    $path = parse_url($uri, PHP_URL_PATH);
    if (!$path) {
        return;
    }

    // Padrão: .../usuarios/conf...irmar_email/TOKEN (token pode ter = ou %20 por corrupção)
    if (!preg_match('#/usuarios/([^/]+)/([a-fA-F0-9=_\-\s%]+)#', $path, $m)) {
        return;
    }

    $method_part = strtolower(preg_replace('/[^a-z]/', '', $m[1]));
    $token_raw = $m[2];

    $is_confirmar = (
        strpos($method_part, 'conf') !== false &&
        strpos($method_part, 'irmar') !== false &&
        (strpos($method_part, 'mail') !== false || strpos($method_part, 'email') !== false)
    );

    if (!$is_confirmar) {
        return;
    }

    $token = trim(preg_replace('/[^a-f0-9]/i', '', $token_raw));
    $proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $base = $proto . '://' . $host;
    $idx = strpos($path, '/usuarios/');
    $basePath = ($idx !== false) ? substr($path, 0, $idx) : ($_SERVER['SCRIPT_NAME'] ? dirname($_SERVER['SCRIPT_NAME']) : '');
    $basePath = rtrim($basePath, '/');

    if (strlen($token) === 64) {
        header('Location: ' . $base . $basePath . '/definir-senha?t=' . $token, true, 302);
        exit;
    }

    header('Location: ' . $base . $basePath . '/login', true, 302);
    exit;
}
