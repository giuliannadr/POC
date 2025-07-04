<?php

class RegistroController
{
    private $view;
    private $model;
    public function __construct($model, $view)
    {
        $this->model = $model;
        $this->view = $view;
    }

    public function show()
    {
        
        $this->view->render("headerChico", "registro");
    }

    public function registro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Obtener los datos del form
            $nombre = isset($_POST['nombreRegistro']) ? $_POST['nombreRegistro'] : '';
            $apellido = isset($_POST['apellidoRegistro']) ? $_POST['apellidoRegistro'] : '';
            $usuario = isset($_POST['usuarioRegistro']) ? $_POST['usuarioRegistro'] : '';
            $fechaNacimiento = isset($_POST['fechaNacimiento']) ? $_POST['fechaNacimiento'] : '';
            $sexo = isset($_POST['sexo']) ? $_POST['sexo'] : '';
            $email = isset($_POST['emailRegistro']) ? $_POST['emailRegistro'] : '';
            $contrasena = isset($_POST['passRegistro']) ? $_POST['passRegistro'] : '';
            $pais = isset($_POST['paisSeleccionado']) ? $_POST['paisSeleccionado'] : '';
            $ciudad = isset($_POST['ciudadSeleccionada']) ? $_POST['ciudadSeleccionada'] : '';

            // Procesar la imagen subida
            if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
                $archivoTmp = $_FILES['profilePic']['tmp_name'];
                $tipoMime = mime_content_type($archivoTmp); // Obtener mime type (ej: image/jpeg)
                $contenidoImagen = file_get_contents($archivoTmp);
                $base64 = base64_encode($contenidoImagen);
                // Guardamos la imagen con el prefijo para mostrar en <img src="data:...">
                $fotoPerfil = "data:$tipoMime;base64,$base64";
            } else {
                $fotoPerfil = ''; // No se subió imagen o hubo error
            }


            // Llamás a la función del modelo
            $exito = $this->model->registrarJugador(
                $nombre, $apellido, $usuario, $fechaNacimiento, $sexo,
                $email, $contrasena, $fotoPerfil, $pais, $ciudad
            );

            if (!$exito) {
                $this->view->render('headerChico', 'registro', ['error' => 'El email o usuario ya están en uso']);
            } else {
                $this->view->render('headerChico', 'registro', ['mostrarPopupActivacion' => true]);
            }
        } else {
            // Si no es POST, sólo mostrar el formulario
            $this->view->render('headerChico', 'registro');
        }
    }

    public function activar()
    {
        $token = isset($_GET['token']) ? $_GET['token'] : '';

        if ($this->model->activarCuenta($token)) {
            // Activación exitosa
            $this->view->render('headerChico', 'homeLogin', ['success' => 'Cuenta activada correctamente. ¡Bienvenido!']);
        } else {
            // Activación fallida
            $this->view->render('headerChico', 'homeLogin', ['error' => 'Token inválido o cuenta ya activada.']);
        }
    }

    public function verificarEmail()
    {
        if (isset($_GET['email'])) {
            $email = $_GET['email'];
            $yaExiste = $this->model->existeEmail($email);
            header('Content-Type: application/json');
            echo json_encode(['existe' => $yaExiste]);
        }
    }

    public function verificarUsuario()
    {
        if (isset($_GET['usuario'])) {
            $usuario = $_GET['email'];
            $yaExiste = $this->model->existeUsuario($usuario);
            header('Content-Type: application/json');
            echo json_encode(['existe' => $yaExiste]);
        }
    }

}
