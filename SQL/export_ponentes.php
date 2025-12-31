<?php
session_start();


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../src/Pages/login.php');
    exit();
}

include '../../ruta.php';
include 'db_connect.php';

try {
    
    $stmt_ponentes = $pdo->query("
        SELECT p.ID, p.Nombre, p.Apellido, p.Numero_de_documento, 
               p.Titulo_de_la_presentacion, td.Tipo_documento, 
               p.Fecha_de_inscripcion 
        FROM ponente p 
        INNER JOIN tipo_documento td ON p.Id_Tipo_documento = td.ID 
        ORDER BY p.Fecha_de_inscripcion DESC
    ");
    $ponentes = $stmt_ponentes->fetchAll();
    
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="listado_ponentes_' . date('Y-m-d_H-i-s') . '.xls"');
    header('Cache-Control: max-age=0');
    
    
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<style>';
    echo 'table { border-collapse: collapse; width: 100%; }';
    echo 'th, td { border: 1px solid #000000ff; padding: 8px; text-align: left; }';
    echo 'th { background-color: #f2f2f2; font-weight: bold; }';
    echo '</style>';
    echo '</head>';
    echo '<body>';
    
    echo '<h2>Listado de Ponentes - Seminario PITI 2025</h2>';
    echo '<p>Fecha de generación: ' . date('d/m/Y H:i:s') . '</p>';
    
    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>Tipo de Documento</th>';
    echo '<th>Número de Documento</th>';
    echo '<th>Nombre</th>';
    echo '<th>Apellido</th>';
    echo '<th>Título de la Presentación</th>';
    echo '<th>Fecha de Inscripción</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    if (!empty($ponentes)) {
        foreach ($ponentes as $ponente) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($ponente['ID']) . '</td>';
            echo '<td>' . htmlspecialchars($ponente['Tipo_documento']) . '</td>';
            echo '<td>' . htmlspecialchars($ponente['Numero_de_documento']) . '</td>';
            echo '<td>' . htmlspecialchars($ponente['Nombre']) . '</td>';
            echo '<td>' . htmlspecialchars($ponente['Apellido']) . '</td>';
            echo '<td>' . htmlspecialchars($ponente['Titulo_de_la_presentacion']) . '</td>';
            echo '<td>' . date('d/m/Y H:i:s', strtotime($ponente['Fecha_de_inscripcion'])) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="7">No hay ponentes registrados.</td></tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    
    echo '<p><strong>Total de ponentes:</strong> ' . count($ponentes) . '</p>';
    
    echo '</body>';
    echo '</html>';
    
} catch (PDOException $e) {
    
    header('Content-Type: text/plain');
    echo 'Error al generar el archivo: ' . $e->getMessage();
}

exit();
?>