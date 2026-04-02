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

    /** Devuelve true si hay una sesión activa */
    public static function check(): bool
    {
        self::startIfNeeded();
        return !empty($_SESSION['usuario_id']);
    }

    /** Redirige al login si no hay sesión */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /remuneraciones/public/login.php');
            exit;
        }
    }

    /** Datos del usuario en sesión */
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

    /** Iniciar sesión */
    public static function login(array $usuario): void
    {
        self::startIfNeeded();
        session_regenerate_id(true);
        $_SESSION['usuario_id']     = $usuario['id_usuario'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_email']  = $usuario['email'];
        $_SESSION['usuario_rol']    = $usuario['rol'];
    }

    /** Cerrar sesión */
    public static function logout(): void
    {
        self::startIfNeeded();
        $_SESSION = [];
        session_destroy();
    }
}