<?php
require_once("core/Session.php");
class LobbyEDITORController
{
    private $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function show()
    {


        // Redirige si no hay usuario o si el tipo no es 'editor'


        $dataLobby = new DataLobbys();
        $data = $dataLobby->getLobbyEditorData();

        $this->view->render('headerAdminEditor', 'lobbyEDITOR', $data);
    }
}
