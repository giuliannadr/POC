<?php

class LoginController
{
    private $view;
    private $model;

    public function __construct($model, $view)
    {
        $this->view = $view;
        $this->model = $model;

    }

    public function validar()
    {
        session_start();

        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';

        $resultado = $this->model->validarLogin($email, $contrasena);

        if (isset($resultado['error'])) {
            $this->view->render('headerChico', 'homeLogin', [
                'error' => $resultado['error'],  // mensaje de error aquí
                'success' => ''
            ]);
        } else {
            $tipo = $resultado['tipo'];
            $usuario = $resultado['datos'];

            $_SESSION['usuario'] = $usuario;
            $_SESSION['tipo'] = $tipo;

            if
            ($tipo === 'admin') {
                header('Location: /POC/index.php?controller=LobbyADM&method=show');
                exit;
            } elseif ($tipo === 'editor') {
                header('Location: /POC/index.php?controller=LobbyEDITOR&method=show');
                exit;
            } elseif ($tipo === 'jugador') {
                header('Location: /POC/index.php?controller=LobbyJug&method=show');
                exit;
            }
        }
    }


    public function cerrarSesion()
    {
        session_start();
        session_unset();
        session_destroy();
        header('Location: index.php?controller=Home&method=show');
        exit;
    }
}

