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

        $usuario = $_SESSION['usuario'];

        // Botones para el nav rol admin
        // Hay que ver como se haria lo de los filtros
        $botones = [
            ['texto' => 'Dia', 'link' => '#'],
            ['texto' => 'Semana', 'link' => '#'],
            ['texto' => 'Mes', 'link' => '#']
        ];

        // Se pasa a mustachol los botones
        $this->view->render('headerAdminEditor', 'lobbyADM', [
            'usuario' => $usuario,
            'botones' => $botones
        ]);
    }
}