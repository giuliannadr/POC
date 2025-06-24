<?php
require_once("core/Session.php");
class LobbyEDITORController
{
    private $view;
    private $db;

    public function __construct($view,$db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    public function show()
    {


        // Redirige si no hay usuario o si el tipo no es 'editor'


        $dataLobby = new DataLobbys();
        $data = $dataLobby->getLobbyEditorData($this->db);

        $this->view->render('headerAdminEditor', 'lobbyEDITOR', $data);
    }
}
