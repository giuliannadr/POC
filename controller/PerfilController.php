<?php
require_once("core/Session.php");
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



        // Si no hay sesión activa, redirigir al login
        if (!Session::exists('usuario') ) {
            header('Location: index.php?controller=Login&method=mostrarLogin');
            exit;
        }

        $usuario = Session::get('usuario'); // Esto devuelve el array completo (o null si no existe)

        if ($usuario && isset($usuario['id_usuario'])) {
            $id_usuario = $usuario['id_usuario'];
        } else {
            $id_usuario = null; // o manejar error si no existe
        }

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

}
