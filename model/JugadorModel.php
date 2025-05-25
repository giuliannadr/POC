<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class JugadorModel
{
    private $database;


    public function __construct($database)
    {
        $this->database = $database;

    }

    // Verifica si existe un usuario con ese email o nombre_usuario
    public function existeUsuarioOEmail($email, $usuario)
    {
        $sql = "SELECT id_usuario FROM Usuario WHERE mail = ? OR nombre_usuario = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("ss", $email, $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $existe = ($result->num_rows > 0);
        $stmt->close();
        return $existe;
    }

    // Registra un nuevo jugador (inserta en Usuario y luego en Jugador)
    public function registrarJugador($nombreCompleto, $usuario, $fechaNacimiento, $sexo, $email, $contrasena, $fotoPerfil)
    {
        if ($this->existeUsuarioOEmail($email, $usuario)) {
            return false; // Ya existe email o usuario
        }

        $tokenActivacion = bin2hex(openssl_random_pseudo_bytes(16));

        if (empty($fotoPerfil)) {
            $fotoPerfil = 'img/sinperfil.png';
        }

        $hashContrasena = password_hash($contrasena, PASSWORD_DEFAULT);

        // Insertar en Usuario
        $sql = "INSERT INTO Usuario (mail, contraseña, nombre_usuario) VALUES (?, ?, ?)";
        $stmt = $this->database->prepare($sql);
        if ($stmt === false) {
            die("Error al preparar consulta Usuario: " . $this->database->error);
        }
        $stmt->bind_param("sss", $email, $hashContrasena, $usuario);
        $stmt->execute();
        $idUsuario = $this->database->getInsertId();
        $stmt->close();

        // Insertar en Jugador
        $sql = "INSERT INTO Jugador (id_usuario, nombre_completo, fecha_nac, sexo, foto_perfil, activado, token_activacion) 
                VALUES (?, ?, ?, ?, ?, 0, ?)";
        $stmt = $this->database->prepare($sql);
        if ($stmt === false) {
            die("Error al preparar consulta Jugador: " . $this->database->error);
        }
        $stmt->bind_param("isssss", $idUsuario, $nombreCompleto, $fechaNacimiento, $sexo, $fotoPerfil, $tokenActivacion);
        $stmt->execute();
        $stmt->close();

        // Enviar correo activación
        $asunto = "Activación de cuenta";
        $mensajeHTML = "<h1>¡Gracias por registrarte!</h1>
            <p>Activá tu cuenta y accedé al lobby haciendo clic en el enlace:</p>
            <p>
        <a href='http://localhost/POC/index.php?controller=registro&method=activar&token=$tokenActivacion'
        style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
            Activar cuenta y entrar al lobby
            </a>
            </p>";

        self::enviarCorreoActivacion($email, $asunto, $mensajeHTML);

        return true;
    }

    // Envía mail con link de activación
    public static function enviarCorreoActivacion($destinatario, $asunto, $mensajeHTML)
    {
        $mail = new PHPMailer(true);

        try {
            // Configuración SMTP (Gmail)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'preguntadosprogweb2@gmail.com';
            $mail->Password   = 'fiqx yujx cgmm ykhc'; // No subir a repos públicos
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('preguntadosprogweb2@gmail.com', 'Preguntados App');
            $mail->addAddress($destinatario);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $asunto;
            $mail->Body    = $mensajeHTML;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $mail->ErrorInfo);
            return false;
        }
    }

    // Activa cuenta si el token es válido
    public function activarCuenta($token)
    {
        $sql = "SELECT id_usuario FROM Jugador WHERE token_activacion = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $sqlUpdate = "UPDATE Jugador SET activado = 1, token_activacion = NULL WHERE token_activacion = ?";
            $stmtUpdate = $this->database->prepare($sqlUpdate);
            $stmtUpdate->bind_param("s", $token);
            $stmtUpdate->execute();
            $stmtUpdate->close();
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false; // Token inválido
    }

    // Puedes agregar otros métodos como login, obtener datos, etc.
}
?>
