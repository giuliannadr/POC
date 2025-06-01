<?php
require_once("core/Session.php");
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

            Session::set('usuario', $usuario);
            Session::set('tipo', $tipo);


            if
            ($tipo === 'admin') {
                $lobby = new LobbyADMController($this->view);
                $lobby->show();
            } elseif ($tipo === 'editor') {
                $lobby = new LobbyEDITORController($this->view);
                $lobby->show();
            } elseif ($tipo === 'jugador') {
                $lobby = new LobbyJugController($this->view);
                $lobby->show();
            }
        }
    }


    public function cerrarSesion()
    {
        session_unset();  // limpiar variables de sesión
        Session::destroy();
        $this->view->render('headerChico', 'homeLogin');
        exit;
    }
}

