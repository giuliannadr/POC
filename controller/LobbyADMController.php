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
            ['texto' => 'Dia', 'link' => '/POC/admin/dashboard?periodo=dia'],
            ['texto' => 'Semana', 'link' => '/POC/admin/dashboard?periodo=semana'],
            ['texto' => 'Mes', 'link' => '/POC/admin/dashboard?periodo=mes']
        ];

        $adminModel = new AdminModel($this->db);

        $datos = $adminModel->obtenerEstadisticas($periodo);
        $estadisticasPorEdad = $adminModel->obtenerEstadisticasPorEdad();
        $usuariosPorPais = $adminModel->obtenerEstadisticasPorPais();

        $totalEdad = $estadisticasPorEdad['menores'] + $estadisticasPorEdad['adultos'] + $estadisticasPorEdad['jubilados'];
        $porcentajesEdad = [
            'porcentajeMenores' => $totalEdad > 0 ? round(($estadisticasPorEdad['menores'] / $totalEdad) * 100, 1) : 0,
            'porcentajeAdultos' => $totalEdad > 0 ? round(($estadisticasPorEdad['adultos'] / $totalEdad) * 100, 1) : 0,
            'porcentajeJubilados' => $totalEdad > 0 ? round(($estadisticasPorEdad['jubilados'] / $totalEdad) * 100, 1) : 0
        ];

        $datosGenero = $adminModel->obtenerEstadisticasPorGenero();
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
            'usuariosPorPais' => $usuariosPorPais,
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
        $htmlPaises = $_POST['htmlPaises'] ?? '';
        $htmlEstadisticas = $_POST['htmlEstadisticas'] ?? '';

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

        /* Contenedor flex para gráficos */
        .graficos-container {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-bottom: 30px;
        }
        .graficos-container img {
            width: 45%; /* Ajusta tamaño para que quepan los dos */
            height: auto;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            padding: 10px;
        }
    </style>

    <h1 style='text-align:center;'>Estadísticas del Sistema</h1>

    <h2>Estadísticas Generales</h2>
    <div>$htmlEstadisticas</div>

    <h2>Distribución por Edad y Género</h2>
    <div class='graficos-container'>
        <img src='$imgEdad' alt='Gráfico por Edad'>
        <img src='$imgGenero' alt='Gráfico por Género'>
    </div>

    <h2>Usuarios por País</h2>
    <div>$htmlPaises</div>
    ";

        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();
        $pdf->stream('estadisticas.pdf', ['Attachment' => false]);
    }
}