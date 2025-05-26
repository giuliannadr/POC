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
            $nombre = $_POST['nombreRegistro'] ?? '';
            $apellido = $_POST['apellidoRegistro'] ?? '';
            $usuario = $_POST['usuarioRegistro'] ?? '';
            $fechaNacimiento = $_POST['fechaNacimiento'] ?? '';
            $sexo = $_POST['sexo'] ?? '';
            $email = $_POST['emailRegistro'] ?? '';
            $contrasena = $_POST['passRegistro'] ?? '';
            $pais = $_POST['paisSeleccionado'] ?? '';
            $ciudad = $_POST['ciudadSeleccionada'] ?? '';

            // Procesar la imagen subida
            if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
                $archivoTmp = $_FILES['profilePic']['tmp_name'];
                $nombreArchivo = basename($_FILES['profilePic']['name']);
                $carpetaDestino = __DIR__ . '/uploads/';

                // Crear carpeta si no existe
                if (!is_dir($carpetaDestino)) {
                    mkdir($carpetaDestino, 0755, true);
                }

                $rutaFinal = $carpetaDestino . $nombreArchivo;

                if (move_uploaded_file($archivoTmp, $rutaFinal)) {
                    // Guardamos la ruta relativa para la base de datos o para mostrar
                    $fotoPerfil = '/POC/public/uploads/' . $nombreArchivo;
                } else {
                    $fotoPerfil = ''; // Error al mover el archivo
                }
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
        $token = $_GET['token'] ?? '';

        if ($this->model->activarCuenta($token)) {
            // Activación exitosa
            $this->view->render('headerChico', 'homeLogin', ['success' => 'Cuenta activada correctamente. ¡Bienvenido!']);
        } else {
            // Activación fallida
            $this->view->render('headerChico', 'homeLogin', ['error' => 'Token inválido o cuenta ya activada.']);
        }
    }
}
