<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class JugadorModel
{
    private $database;
    private $email;


    public function __construct($database, $email)
    {
        $this->database = $database;
        $this->email = $email;

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
    public function registrarJugador($nombre, $apellido, $usuario, $fechaNacimiento, $sexo, $email, $contrasena, $fotoPerfil, $pais, $ciudad)
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
        $sql = "INSERT INTO Jugador (id_usuario, nombre, apellido, fecha_nac, sexo, foto_perfil, activado, token_activacion, pais, ciudad) 
                VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, ?)";
        $stmt = $this->database->prepare($sql);
        if ($stmt === false) {
            die("Error al preparar consulta Jugador: " . $this->database->error);
        }
        $stmt->bind_param("issssssss", $idUsuario, $nombre, $apellido, $fechaNacimiento, $sexo, $fotoPerfil, $tokenActivacion, $pais, $ciudad);
        $stmt->execute();
        $stmt->close();

        // Enviar correo activación
        $asunto = "Activación de cuenta";
        $mensajeHTML = "<h1>¡Gracias por registrarte!</h1>
            <p>Activá tu cuenta y accedé al lobby haciendo clic en el enlace:</p>
            <p>
        <a href='http://localhost/POC/index.php?controller=registro&method=activar&token=$tokenActivacion'
        style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
            Activar cuenta
            </a>
            </p>";

        $this->enviarCorreoActivacion($email, $asunto, $mensajeHTML);

        return true;
    }

    // Envía mail con link de activación
    public function enviarCorreoActivacion($destinatario, $asunto, $mensajeHTML)
    {
        $mail = new PHPMailer(true);

        try {
            // Configuración SMTP (Gmail)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->email->getMail();
            $mail->Password   = $this->email->getPassword(); // No subir a repos públicos
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom($this->email->getMail(), 'Preguntados App');
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



    public function actualizarPerfil($id_usuario, $datos)
    {
        if (!empty($datos['foto_perfil'])) {
            // Si se proporciona una imagen, incluimos la columna foto_perfil
            $sql = "UPDATE jugador SET 
            nombre = ?, 
            apellido = ?, 
            sexo = ?, 
            fecha_nac = ?, 
            ciudad = ?, 
            pais = ?, 
            foto_perfil = ?
            WHERE id_usuario = ?";

            $stmt = $this->database->prepare($sql);
            $stmt->bind_param("sssssssi",
                $datos['nombre'],
                $datos['apellido'],
                $datos['sexo'],
                $datos['fecha_nacimiento'],
                $datos['ciudad'],
                $datos['pais'],
                $datos['foto_perfil'], // esta es la imagen base64
                $id_usuario
            );
        } else {
            // Si no se proporciona imagen, no se modifica la columna foto_perfil
            $sql = "UPDATE jugador SET 
            nombre = ?, 
            apellido = ?, 
            sexo = ?, 
            fecha_nac = ?, 
            ciudad = ?, 
            pais = ?
            WHERE id_usuario = ?";

            $stmt = $this->database->prepare($sql);
            $stmt->bind_param("ssssssi",
                $datos['nombre'],
                $datos['apellido'],
                $datos['sexo'],
                $datos['fecha_nacimiento'],
                $datos['ciudad'],
                $datos['pais'],
                $id_usuario
            );
        }

        $stmt->execute();
        $stmt->close();
    }

    /* -------------------------------- OBTENER DATOS -------------------------------- */
    public function obtenerDatosPerfil($id_usuario) {
        $sql = "SELECT 
                u.nombre_usuario,
                u.mail AS email,
                j.nombre,
                j.apellido,
                j.sexo,
                j.fecha_nac AS fecha_nacimiento,
                j.foto_perfil,
                j.ciudad AS ciudad,
                j.pais AS pais
            FROM usuario u
            JOIN jugador j ON u.id_usuario = j.id_usuario
            WHERE u.id_usuario = ?";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $datos = $result->fetch_assoc();
        $stmt->close();

        return $datos;
    }

    public function obtenerPuntaje($id_usuario) {
        $sql = "SELECT 
                j.puntaje
            FROM usuario u
            JOIN jugador j ON u.id_usuario = j.id_usuario
            WHERE u.id_usuario = ?";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $datos = $result->fetch_assoc();
        $stmt->close();

        if ($datos && isset($datos['puntaje'])) {
            return (int)$datos['puntaje']; // devolvés solo el puntaje como int
        }

        return 0;
    }

    public function obtenerDatosPerfilPorUsuario($nombre_usuario) {
        $sql = "SELECT 
                u.nombre_usuario,
                u.mail AS email,
                j.nombre,
                j.apellido,
                j.sexo,
                j.fecha_nac AS fecha_nacimiento,
                j.foto_perfil,
                j.ciudad AS ciudad,
                j.pais AS pais,
                j.puntaje
            FROM usuario u
            JOIN jugador j ON u.id_usuario = j.id_usuario
            WHERE u.nombre_usuario = ?";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $nombre_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $datos = $result->fetch_assoc();
        $stmt->close();

        return $datos;
    }

    public function obtenerPartidasJugadas($nombre_usuario) {
        $sql = "SELECT COUNT(*) as partidas_jugadas
                FROM partida p
                JOIN usuario u ON p.id_jugador = u.id_usuario
                WHERE u.nombre_usuario = ?";

        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("s", $nombre_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $datos = $result->fetch_assoc();
        $stmt->close();

        if ($datos && isset($datos['partidas_jugadas'])) {
            return (int)$datos['partidas_jugadas'];
        }

        return 0;
    }

    public function getDatabase() {
        return $this->database;
    }

}
?>
