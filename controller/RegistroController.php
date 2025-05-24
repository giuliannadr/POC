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
            // Obtener los datos del form (sólo los que necesitás)
            $nombre = $_POST['nombreRegistro'] ?? '';
            $usuario = $_POST['usuarioRegistro'] ?? '';
            $fechaNacimiento = $_POST['fechaNacimiento'] ?? '';
            $sexo = $_POST['sexo'] ?? '';
            $email = $_POST['emailRegistro'] ?? '';
            $contrasena = $_POST['passRegistro'] ?? '';
            // Supongamos que la foto se procesa aparte y llega el path en $_POST['rutaFotoPerfil']
            $fotoPerfil = $_POST['rutaFotoPerfil'] ?? '';

            // Llamás a la función del modelo
            $exito = $this->model->registrarUsuario($nombre, $usuario, $fechaNacimiento, $sexo, $email, $contrasena, $fotoPerfil);

            if (!$exito) {
                $this->view->render('headerChico','registro', ['error' => 'El email o usuario ya están en uso']);
            } else {
                $this->view->render('headerChico','registro', ['mostrarPopupActivacion' => true]);
            }
        } else {
            // Si no es POST, sólo mostrar el formulario
            $this->view->render('headerChico','registro');
        }
    }

    public function activar() {
        $token = $_GET['token'] ?? '';

        if ($this->model->activarCuenta($token)) {
            // Podés setear sesión si querés que el usuario quede logueado automáticamente
            // $_SESSION['usuario'] = ...;

            $this->view->render('headerGrande','lobbyJug');
        } else {
            $this->view->render('headerChico','registro', ['error' => 'Token inválido o cuenta ya activada.']);
        }
    }

}