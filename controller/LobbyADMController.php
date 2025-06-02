<?php
require_once("core/Session.php");
class LobbyADMController
{
    private $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function show()
    {


        if (!Session::exists('usuario') || Session::get('tipo') !== 'admin')  {
            $this->view->render('headerChico', 'homeLogin');
            exit;
        }

        $dataLobby = new DataLobbys();
        $data = $dataLobby->getLobbyAdminData();

        $this->view->render('headerAdminEditor', 'lobbyADM', $data);
    }
}