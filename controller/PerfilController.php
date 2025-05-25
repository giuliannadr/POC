<?php

class PerfilController
{
    private $model;
    private $view;

    public function __construct($model, $view)
    {
        $this->model = $model;
        $this->view = $view;
    }

    public function mostrar()
    {
        session_start();

        // Si no hay sesión activa, redirigir al login
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?controller=Login&method=mostrarLogin');
            exit;
        }

        $id_usuario = $_SESSION['usuario']['id_usuario'];

        // Obtener datos del perfil
        $datos = $this->model->obtenerDatosPerfil($id_usuario);

        // Renderizar la vista con header
        $this->view->render('headerChico', 'perfilView', [
            'nombre' => $datos['nombre'],
            'apellido' => $datos['apellido'],
            'genero' => $datos['sexo'],
            'fecha_nacimiento' => $datos['fecha_nac'],
            'email' => $datos['mail'],
            'usuario' => $datos['nombre_usuario'],
            'foto_perfil' => $datos['foto_perfil'] ?? ''
        ]);
    }
}
