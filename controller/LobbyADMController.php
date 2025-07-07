<?php
require_once("core/Session.php");

use Dompdf\Dompdf;

require_once("vendor/autoload.php"); // Si usás Composer y Dompdf
class LobbyADMController
{
    private $view;
    private $db;

    public function __construct($view, $db)
    {
        $this->view = $view;
        $this->db = $db;
    }

    public function show()
    {
        $usuario = Session::get('usuario');
        $periodo = $_GET['periodo'] ?? 'mes';

        $botones = [
            ['texto' => 'Día', 'link' => '/POC/lobbyAdm/show?periodo=dia', 'activo' => $periodo === 'dia'],
            ['texto' => 'Semana', 'link' => '/POC/lobbyAdm/show?periodo=semana', 'activo' => $periodo === 'semana'],
            ['texto' => 'Mes', 'link' => '/POC/lobbyAdm/show?periodo=mes', 'activo' => $periodo === 'mes'],
            ['texto' => 'Año', 'link' => '/POC/lobbyAdm/show?periodo=anio', 'activo' => $periodo === 'anio']
        ];



        $datos = $this->db->obtenerEstadisticas($periodo);
        $estadisticasPorEdad = $this->db->obtenerEstadisticasPorEdad($periodo);
        $usuariosPorPais = $this->db->obtenerEstadisticasPorPais($periodo);
        $usuariosNuevos = $this->db->obtenerUsuariosNuevosPorPeriodo($periodo);

        $totalEdad = $estadisticasPorEdad['menores'] + $estadisticasPorEdad['adultos'] + $estadisticasPorEdad['jubilados'];
        $porcentajesEdad = [
            'porcentajeMenores' => $totalEdad > 0 ? round(($estadisticasPorEdad['menores'] / $totalEdad) * 100, 1) : 0,
            'porcentajeAdultos' => $totalEdad > 0 ? round(($estadisticasPorEdad['adultos'] / $totalEdad) * 100, 1) : 0,
            'porcentajeJubilados' => $totalEdad > 0 ? round(($estadisticasPorEdad['jubilados'] / $totalEdad) * 100, 1) : 0
        ];

        $totalPaises = 0;
        foreach ($usuariosPorPais as $paisDato) {
            $totalPaises += $paisDato['cantidad'];
        }

        $paisesConPorcentaje = [];
        foreach ($usuariosPorPais as $paisDato) {
            $porcentaje = $totalPaises > 0 ? round(($paisDato['cantidad'] / $totalPaises) * 100, 1) : 0;
            $paisesConPorcentaje[] = [
                'pais' => $paisDato['pais'],
                'cantidad' => $paisDato['cantidad'],
                'porcentaje' => $porcentaje
            ];
        }

        $paisesParaGrafico = [];
        foreach ($paisesConPorcentaje as $paisDato) {
            $paisesParaGrafico[] = [$paisDato['pais'], $paisDato['porcentaje']];
        }
        $dataPaisesJson = json_encode($paisesParaGrafico);


        $datosGenero = $this->db->obtenerEstadisticasPorGenero($periodo);
        $totalGenero = $datosGenero['hombres'] + $datosGenero['mujeres'] + $datosGenero['otros'];
        $porcentajeHombres = $totalGenero > 0 ? round(($datosGenero['hombres'] / $totalGenero) * 100, 1) : 0;
        $porcentajeMujeres = $totalGenero > 0 ? round(($datosGenero['mujeres'] / $totalGenero) * 100, 1) : 0;
        $porcentajeOtros = $totalGenero > 0 ? round(($datosGenero['otros'] / $totalGenero) * 100, 1) : 0;

        $data = [
            'usuario' => $usuario,
            'botones' => $botones,
            'totalJugadores' => $datos['totalJugadores'],
            'totalPartidas' => $datos['totalPartidas'],
            'menores' => $estadisticasPorEdad['menores'],
            'adultos' => $estadisticasPorEdad['adultos'],
            'jubilados' => $estadisticasPorEdad['jubilados'],
            'paisesConPorcentaje' => $paisesConPorcentaje,
            'dataPaisesJson' => $dataPaisesJson,
            'usuariosNuevos' => $usuariosNuevos['usuariosNuevos'],
            'porcentajeMenores' => $porcentajesEdad['porcentajeMenores'],
            'porcentajeAdultos' => $porcentajesEdad['porcentajeAdultos'],
            'porcentajeJubilados' => $porcentajesEdad['porcentajeJubilados'],
            'hombres' => $datosGenero['hombres'],
            'mujeres' => $datosGenero['mujeres'],
            'otros' => $datosGenero['otros'],
            'porcentajeHombres' => $porcentajeHombres,
            'porcentajeMujeres' => $porcentajeMujeres,
            'porcentajeOtros' => $porcentajeOtros
        ];

        $this->view->render('headerAdminEditor', 'lobbyADM', $data);
    }

    public function exportarPDF()
    {
        $imgEdad = $_POST['imgEdad'] ?? '';
        $imgGenero = $_POST['imgGenero'] ?? '';
        $imgPais = $_POST['imgPais'] ?? '';
        $htmlPaises = $_POST['htmlPaises'] ?? '';
        $htmlEstadisticas = $_POST['htmlEstadisticas'] ?? '';
        $htmlEdad = $_POST['htmlEdad'] ?? '';
        $htmlGenero = $_POST['htmlGenero'] ?? '';

        $html = "
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 40px;
            background-color: #7e00d9;
            color: white;
            font-family: Arial, sans-serif;
        }

        .fila-estadistica {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 10px;
            margin: 5px 0;
            font-size: 14px;
        }
        .fila-estadistica p:first-child {
            margin: 0;
            font-weight: 600;
            white-space: nowrap;
        }
        .fila-estadistica p:last-child {
            margin: 0;
            background-color: #4c2681;
            padding: 4px 10px;
            border-radius: 8px;
            font-weight: 700;
            min-width: 40px;
            text-align: center;
        }
        .container-datos {
            width: 100%;
            text-align: center; 
        }
        .container-datos img {
            display: inline-block;
            width: 50%;
        }
        
        .container-genero {
            width: 100%;
            text-align: center; 
            page-break-inside: avoid;
        }
        .container-genero img {
            display: inline-block;
            width: 50%;
        }
    </style>

    <h1 style='text-align:center;'>Estadísticas del Sistema</h1>

    <h2>Estadísticas Generales</h2>
    <div>$htmlEstadisticas</div>

    <div class='container-datos'>
        <h2 style='text-align: left'>Distribución por Edad</h2>
        <div style='text-align: left'>$htmlEdad</div>
        <img src='$imgEdad' alt='Gráfico por Edad' style='width: 45%;'>
    </div>
    <div class='container-genero'>
        <h2 style='text-align: left'>Distribución por Género</h2>
        <div style='text-align: left'>$htmlGenero</div>
        <img src='$imgGenero' alt='Gráfico por Género' style='width: 45%;'>
    </div>
    
    <div class='container-datos'>
        <h2 style='text-align: left'>Usuarios por País</h2>
        <div style='text-align: left'>$htmlPaises</div>
        <img src='$imgPais' alt='Gráfico por País' style='width: 45%;'>
    </div>
    ";

        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();
        $pdf->stream('estadisticas.pdf', ['Attachment' => false]);
    }
}