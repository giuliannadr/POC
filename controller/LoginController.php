<?php

class LoginController
{
    private $view;
    private $model;

    public function __construct($model,$view)
    {
        $this->view = $view;
        $this->model = $model;

    }

    public function validar()
    {
        session_start();

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

            $_SESSION['usuario'] = $usuario;
            $_SESSION['tipo'] = $tipo;

            if ($tipo === 'jugador') {
                $this->view->render('headerChico', 'lobbyJug', ['usuario' => $usuario]);
            } elseif ($tipo === 'editor') {
                $this->view->render('headerChico', 'lobbyEDITOR', ['usuario' => $usuario]);
            } elseif ($tipo === 'admin') {
                $this->view->render('headerChico', 'lobbyADM', ['usuario' => $usuario]);
            }
            exit;
        }
    }

}

