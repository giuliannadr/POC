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

        //  Agregá esto para ver qué trae:


        // Renderizar la vista con header
        $this->view->render('headerPerfil', 'perfil', [
            'nombre' => $datos['nombre'],
            'apellido' => $datos['apellido'],
            'genero' => $datos['genero'],
            'fecha_nacimiento' => $datos['fecha_nacimiento'],
            'email' => $datos['email'],
            'usuario' => $datos['usuario'],
            'foto_perfil' => $datos['foto_perfil'],
            'ciudad' => $datos['ciudad'],
            'pais' => isset($datos['pais']) ? $datos['pais'] : ''
        ]);

    }

    public function editar()
    {
        session_start();
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?controller=Login&method=mostrarLogin');
            exit;
        }

        $id_usuario = $_SESSION['usuario']['id_usuario'];
        $datos = $this->model->obtenerDatosPerfil($id_usuario);

        $this->view->render('headerPerfil', 'perfil', array_merge($datos, ['modo_edicion' => true]));
    }

    public function guardar()
    {
        session_start();
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?controller=Login&method=mostrarLogin');
            exit;
        }

        $id_usuario = $_SESSION['usuario']['id_usuario'];

        // agarro los datos del formulario
        $datos = [
            'nombre' => $_POST['nombre'],
            'apellido' => $_POST['apellido'],
            'genero' => $_POST['genero'],
            'fecha_nacimiento' => $_POST['fecha_nacimiento'],
            'ciudad' => $_POST['ciudad'],
            'pais' => $_POST['pais'],
        ];

        // los guardo en la base de datos
        $this->model->actualizarPerfil($id_usuario, $datos);

        // redirige al perfil con los datos actualizados
        header('Location: index.php?controller=Perfil&method=mostrar');
    }


}
