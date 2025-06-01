<?php

class SessionController
{

    public function iniciarSesion($usuario,$tipo) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }/*si no hay sesion la instancia*/


        $_SESSION['usuario'] = $usuario;
        $_SESSION['tipo'] = $tipo;

    }

    public function obtenerUsuario() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['usuario'] ?? null;
    }

    public function cerrarSesion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
    }

    public function verificarSesion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header("Location: login.php"); // Redirigir si no hay sesión activa
            exit();
        }
    }


}