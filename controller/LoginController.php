<?php

class LoginController
{
    private $view;
    private $model;
    private $session;

    public function __construct($model, $view, $session)
    {
        $this->view = $view;
        $this->model = $model;
        $this->session = $session;

    }

    public function validar()
    {


        $email = $_POST['email'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';

        $resultado = $this->model->validarLogin($email, $contrasena);

        if (isset($resultado['error'])) {
            $this->view->render('headerChico', 'homeLogin', [
                'error' => $resultado['error'],  // mensaje de error aquí
                'success' => ''
            ]);
        } else {
            $tipo = $resultado['tipo'];
            $usuario = $resultado['datos'];

           /* $_SESSION['usuario'] = $usuario;
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
            }*/

            // Usar SessionController en lugar de acceder directamente a $_SESSION
            $this->session->iniciarSesion($usuario, $tipo);

            // Redirigir según el tipo de usuario
            switch ($tipo) {
                case 'admin':
                    header('Location: /POC/index.php?controller=LobbyADM&method=show');
                    break;
                case 'editor':
                    header('Location: /POC/index.php?controller=LobbyEDITOR&method=show');
                    break;
                case 'jugador':
                    header('Location: /POC/index.php?controller=LobbyJug&method=show');
                    break;
            }
            exit();

        }
    }


    public function cerrarSesion()
    {
        $this->session->cerrarSesion();
        header('Location: index.php?controller=Home&method=show');
        exit;
    }
}

