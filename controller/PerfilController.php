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
            'pais' => $datos['pais']
                ?? ''
        ]);

    }

    public function editar() {
        session_start();

        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?controller=Login&method=mostrarLogin');
            exit;
        }

        $id_usuario = $_SESSION['usuario']['id_usuario'];
        $datos = $this->model->obtenerDatosPerfil($id_usuario);

        // Renderizar la vista de edición
        $this->view->render('headerPerfil', 'perfilEditar', [
            'nombre' => $datos['nombre'],
            'apellido' => $datos['apellido'],
            'genero' => $datos['genero'],
            'fecha_nacimiento' => $datos['fecha_nacimiento'],
            'email' => $datos['email'],
            'usuario' => $datos['usuario'],
            'foto_perfil' => $datos['foto_perfil'],
            'ciudad' => $datos['ciudad'],
            'pais' => $datos['pais'] ?? ''
        ]);
    }

    public function actualizar() {
        session_start();

        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?controller=Login&method=mostrarLogin');
            exit;
        }

        $id_usuario = $_SESSION['usuario']['id_usuario'];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $nombre_usuario = $_POST['nombre_usuario'];
            $email = $_POST['email'];
            $foto_perfil = $_POST['foto_perfil'];

            $this->model->actualizarPerfil($id_usuario, $nombre_usuario, $email, $foto_perfil);

            // Redirigir al perfil actualizado
            header("Location: index.php?controller=Perfil&method=mostrar");
            exit;
        } else {
            echo "❌ Error: Método no permitido.";
        }
    }

}
