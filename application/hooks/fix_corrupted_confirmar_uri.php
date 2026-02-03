<?php
/**
 * Hook: Corrige URLs corrompidas por clientes de e-mail
 * Ex: definir-senha=%20?t=TOKEN, usuarios/conf=%20irmar_email/TOKEN → redireciona para definir-senha?t=TOKEN
 * Executado em pre_system ANTES do URI parse para evitar 400 Bad Request (chars não permitidos)
 */
defined('BASEPATH') || exit('No direct script access allowed');

function fix_corrupted_confirmar_uri()
{
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    if (empty($uri)) {
        return;
    }

    $path = parse_url($uri, PHP_URL_PATH);
    $query = parse_url($uri, PHP_URL_QUERY);
    if (!$path) {
        return;
    }

    // 1) Corrupção em definir-senha: path = ".../definir-senha=%20" ou "definir-senha=" com ?t=TOKEN na query
    if (preg_match('#/definir-senha[=%\s]#', $path) && $query) {
        parse_str($query, $params);
        $token = isset($params['t']) ? trim(preg_replace('/[^a-f0-9]/i', '', (string) $params['t'])) : '';
        if (strlen($token) === 64) {
            $proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? '';
            $idx = strpos($path, '/definir-senha');
            $basePath = ($idx !== false) ? substr($path, 0, $idx) : rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
            header('Location: ' . $proto . '://' . $host . $basePath . '/definir-senha?t=' . $token, true, 302);
            exit;
        }
    }

    // 2) Padrão: .../usuarios/conf...irmar_email/TOKEN (token pode ter = ou %20 por corrupção)
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
