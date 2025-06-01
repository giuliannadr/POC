<?php

class LobbyADMController
{
    private $view;
    private $session;

    public function __construct($view,$session)
    {
        $this->view = $view;
        $this->session = $session;
    }

    public function show()
    {
        $this->session->verificarSesion();

        // Redirige al home si no hay usuario o si el tipo de usuario no es 'admin'
        if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'admin') {
            $this->view->render('headerChico', 'homeLogin');
            exit;
        }

        $usuario = $this->session->obtenerUsuario();

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