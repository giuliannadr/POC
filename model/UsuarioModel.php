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
    public function registrarJugador($nombreCompleto, $usuario, $fechaNacimiento, $sexo, $email, $contrasena, $fotoPerfil)
    {
        // Verificar si el email o usuario ya están registrados
        $sql = "SELECT id FROM jugador WHERE email = ? OR usuario = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("ss", $email, $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return false; // Email o usuario ya en uso
        }

        $tokenActivacion = bin2hex(openssl_random_pseudo_bytes(16));

        if (empty($fotoPerfil)) {
            $fotoPerfil = 'img/sinperfil.png';
        }

        $hashContrasena = password_hash($contrasena, PASSWORD_DEFAULT);

        $sql = "INSERT INTO jugador (nombre, usuario, fecha_nac, sexo, email, contraseña, foto_perfil, activado, token_activacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)";
        $stmt = $this->database->prepare($sql);

        if ($stmt === false) {
            die("Error al preparar la consulta: " . $this->database->error);
        }

        $stmt->bind_param("ssssssss", $nombreCompleto, $usuario, $fechaNacimiento, $sexo, $email, $hashContrasena, $fotoPerfil, $tokenActivacion);
        $stmt->execute();
        $stmt->close();

        // Enviar correo con link al lobby
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

    public static function enviarCorreoActivacion($destinatario, $asunto, $mensajeHTML)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'preguntadosprogweb2@gmail.com';
            $mail->Password   = 'fiqx yujx cgmm ykhc'; // Cuidado con subir esto a repositorios públicos
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('preguntadosprogweb2@gmail.com', 'Preguntados App');
            $mail->addAddress($destinatario);
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $mensajeHTML;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $mail->ErrorInfo);
            return false;
        }
    }

    public function activarCuenta($token)
    {
        $sql = "SELECT id FROM jugador WHERE token_activacion = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $sqlUpdate = "UPDATE jugador SET activado = 1, token_activacion = NULL WHERE token_activacion = ?";
            $stmtUpdate = $this->database->prepare($sqlUpdate);
            $stmtUpdate->bind_param("s", $token);
            $stmtUpdate->execute();
            $stmtUpdate->close();
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false; // Token no válido
    }

    public function validarLogin($usuario, $contrasena)
    {
        $sql = "SELECT id, nombre, usuario, fecha_nac, sexo, foto_perfil, email, contraseña, activado 
                FROM jugador 
                WHERE (usuario = ? OR email = ?) AND activado = 1";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("ss", $usuario, $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            return ['error' => 'Usuario no encontrado o no activado.'];
        }

        $jugadorDatos = $result->fetch_assoc();

        if (!password_verify($contrasena, $jugadorDatos['contraseña'])) {
            return ['error' => 'Contraseña incorrecta.'];
        }

        return ['jugador' => $jugadorDatos];
    }
}
?>