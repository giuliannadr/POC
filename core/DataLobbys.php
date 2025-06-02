<?php
require_once("Session.php");
class DataLobbys{

    public function getLobbyJugData($usuario) {
        $usuario = Session::get('usuario');

        $usuario['tiene_foto'] = !empty($usuario['foto_perfil']);

        $puntaje = 0;
        if (isset($usuario['puntaje']) && !is_array($usuario['puntaje']) && $usuario['puntaje'] !== null) {
            $puntaje = $usuario['puntaje'];
        }
        $usuario['puntaje_mostrar'] = $puntaje . "pts";


        $botones = [
            ['texto' => 'Ver ranking', 'link' => '#'],
            ['texto' => 'Historial de partidas', 'link' => '#'],
            ['texto' => 'Crear preguntas', 'link' => '#']
        ];

        return [
            'usuario' => $usuario,
            'botones' => $botones
        ];
    }

    public function getLobbyEditorData()
    {
        $usuario = Session::get('usuario');

        // Botones en el nav segun el rol
        $botones = [
            ['texto' => 'Gestionar Preguntas', 'link' => '/editor/gestionarPreguntas'],
            ['texto' => 'Preguntas Reportadas', 'link' => '/editor/preguntasReportadas'],
            ['texto' => 'Preguntas Sugeridas', 'link' => '/editor/preguntasSugeridas'],
        ];
        return [
            'usuario' => $usuario,
            'botones' => $botones
        ];
    }

    public function getLobbyAdminData(){
        $usuario = Session::get('usuario');

        // Botones para el nav rol admin
        // Hay que ver como se haria lo de los filtros
        $botones = [
            ['texto' => 'Dia', 'link' => '#'],
            ['texto' => 'Semana', 'link' => '#'],
            ['texto' => 'Mes', 'link' => '#']
        ];
        return [
            'usuario' => $usuario,
            'botones' => $botones
        ];
    }
}
