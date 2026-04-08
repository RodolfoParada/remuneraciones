<?php
// src/Auth.php

class Auth
{
    private static function startIfNeeded(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private static function basePath(): string
    {
        $path = dirname($_SERVER['SCRIPT_NAME']);
        if ($path === '/' || $path === '\\' || $path === '.') {
            return '/';
        }
        return rtrim($path, '/') . '/';
    }

    public static function check(): bool
    {
        self::startIfNeeded();
        return !empty($_SESSION['usuario_id']);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . self::basePath() . 'login.php');
            exit;
        }
    }

    public static function user(): array
    {
        self::startIfNeeded();
        return [
            'id'     => $_SESSION['usuario_id']    ?? null,
            'nombre' => $_SESSION['usuario_nombre'] ?? '',
            'email'  => $_SESSION['usuario_email']  ?? '',
            'rol'    => $_SESSION['usuario_rol']    ?? '',
        ];
    }

    public static function login(array $usuario): void
    {
        self::startIfNeeded();
        session_regenerate_id(true);
        $_SESSION['usuario_id']     = $usuario['id_usuario'];
        $_SESSION['usuario_nombre'] = $usuario['nombre_usuario']; // ← fix
        $_SESSION['usuario_email']  = $usuario['email'];
        $_SESSION['usuario_rol']    = $usuario['rol'];
    }

    public static function logout(): void
    {
        self::startIfNeeded();
        $_SESSION = [];
        session_destroy();
    }
}