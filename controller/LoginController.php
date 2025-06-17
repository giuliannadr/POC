<?php
require_once("core/Session.php");
require_once("core/DataLobbys.php");
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

            $dataLobby = new DataLobbys();
            if
            ($tipo === 'admin') {
                $data = $dataLobby->getLobbyAdminData();
                $this->view->render('headerAdminEditor', 'lobbyADM', $data);
            } elseif ($tipo === 'editor') {
                $data = $dataLobby->getLobbyEditorData();
                $this->view->render('headerAdminEditor', 'lobbyEDITOR', $data);
            } elseif ($tipo === 'jugador') {

                $usuario = Session::get('usuario');
                $data = $dataLobby->getLobbyJugData();
                $this->view->render('headerGrande', 'lobbyJug', $data);
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

