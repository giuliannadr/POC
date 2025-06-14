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

        $dataLobby = new DataLobbys();
        $data = $dataLobby->getLobbyAdminData();

        $this->view->render('headerAdminEditor', 'lobbyADM', $data);
    }
}