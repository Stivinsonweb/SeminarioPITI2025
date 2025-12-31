<?php
session_start();


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../src/Pages/login.php');
    exit();
}

include '../../ruta.php';
include '../../SQL/db_connect.php';

try {
    
    $stmt_participantes = $pdo->query("
        SELECT p.ID, p.Nombre, p.Apellido, p.Numero_de_documento, 
               td.Tipo_documento, p.Fecha_de_inscripcion 
        FROM participante p 
        INNER JOIN tipo_documento td ON p.Id_Tipo_documento = td.ID 
        ORDER BY p.Fecha_de_inscripcion DESC
    ");
    $participantes = $stmt_participantes->fetchAll();
    
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="listado_participantes_' . date('Y-m-d_H-i-s') . '.xls"');
    header('Cache-Control: max-age=0');
    
    
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<style>';
    echo 'table { border-collapse: collapse; width: 100%; }';
    echo 'th, td { border: 1px solid #000; padding: 8px; text-align: left; }';
    echo 'th { background-color: #f2f2f2; font-weight: bold; }';
    echo '</style>';
    echo '</head>';
    echo '<body>';
    
    echo '<h2>Listado de Participantes - Seminario PITI 2025</h2>';
    echo '<p>Fecha de generación: ' . date('d/m/Y H:i:s') . '</p>';
    
    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>Tipo de Documento</th>';
    echo '<th>Número de Documento</th>';
    echo '<th>Nombre</th>';
    echo '<th>Apellido</th>';
    echo '<th>Fecha de Inscripción</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    if (!empty($participantes)) {
        foreach ($participantes as $participante) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($participante['ID']) . '</td>';
            echo '<td>' . htmlspecialchars($participante['Tipo_documento']) . '</td>';
            echo '<td>' . htmlspecialchars($participante['Numero_de_documento']) . '</td>';
            echo '<td>' . htmlspecialchars($participante['Nombre']) . '</td>';
            echo '<td>' . htmlspecialchars($participante['Apellido']) . '</td>';
            echo '<td>' . date('d/m/Y H:i:s', strtotime($participante['Fecha_de_inscripcion'])) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="6">No hay participantes registrados.</td></tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    
    echo '<p><strong>Total de participantes:</strong> ' . count($participantes) . '</p>';
    
    echo '</body>';
    echo '</html>';
    
} catch (PDOException $e) {
    
    header('Content-Type: text/plain');
    echo 'Error al generar el archivo: ' . $e->getMessage();
}

exit();
?>