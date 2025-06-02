<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class UsuarioModel
{

    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function validarLogin($email, $contrasena)
    {
        $sql = "SELECT id_usuario, mail, contraseña, nombre_usuario 
                FROM Usuario 
                WHERE (mail = ?)";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $stmt->close();
            return ['error' => 'Usuario no encontrado o no activado.'];
        }

        $usuarioDatos = $result->fetch_assoc();
        $stmt->close();
        if (!password_verify($contrasena, $usuarioDatos['contraseña'])) {
            return ['error' => 'Contraseña incorrecta.'];
        }

        $id_usuario = $usuarioDatos['id_usuario'];

        if ($this->esJugador($id_usuario)) {
            if ($this->esJugadorActivo($id_usuario)) {
                $datosJugador = $this->obtenerDatosJugador($id_usuario);
                // Merge entre Usuario y Jugador
                $usuarioDatos = array_merge($usuarioDatos, $datosJugador);
                return ['tipo' => 'jugador', 'datos' => $usuarioDatos];
            } else {
                return ['error' => 'Usuario no activado. Revise su casilla de email para activar su cuenta.'];
            }
        }
        elseif ($this->esEditor($id_usuario)) {
            return ['tipo' => 'editor', 'datos' => $usuarioDatos];
        } elseif ($this->esAdmin($id_usuario)) {
            return ['tipo' => 'admin', 'datos' => $usuarioDatos];
        } else {
            return ['error' => 'El usuario no tiene un rol asignado.'];
        }

    }


    function esEditor($id_usuario)
    {
        $sql = "SELECT id_usuario FROM Editor WHERE id_usuario = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $stmt->close();
            return false;
        }
        $stmt->close();
        return true;
    }

    function esAdmin($id_usuario)
    {
        $sql = "SELECT id_usuario FROM Administrador WHERE id_usuario = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $stmt->close();
            return false;
        }
        $stmt->close();
        return true;
    }

    function esJugador($id_usuario)
    {
        $sql = "SELECT id_usuario FROM Jugador WHERE id_usuario = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $stmt->close();
            return false;
        }
        $stmt->close();
        return true;
    }

    function esJugadorActivo($id)
    {
        $sql = "SELECT activado FROM Jugador WHERE id_usuario = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $jugadorActivo = $result->fetch_assoc();

        if ($result->num_rows == 0 or $jugadorActivo['activado'] == 0) {
            $stmt->close();
            return false;
        }
        $stmt->close();
        return true;
    }

    public function obtenerDatosJugador($id_usuario)
    {
        $sql = "SELECT * FROM Jugador WHERE id_usuario = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $datosJugador = $result->fetch_assoc();
        $stmt->close();
        return $datosJugador;
    }

}


?>