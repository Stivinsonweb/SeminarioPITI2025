<?php
// include '../inc/session_check.php'; // No es necesario si ya validaste la sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../src/Pages/login.php');
    exit();
}

include '../../ruta.php';
include '../../SQL/db_connect.php';
include '../inc/head.php';

// Función para obtener información del usuario actual
function getCurrentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'usuario' => $_SESSION['usuario'] ?? null,
        'rol_id' => $_SESSION['rol_id'] ?? null,
        'rol_nombre' => $_SESSION['rol_nombre'] ?? null
    ];
}

$current_user = getCurrentUser();

// Obtener estadísticas
try {
    // Contar participantes
    $stmt_participantes = $pdo->query("SELECT COUNT(*) as total FROM participante");
    $total_participantes = $stmt_participantes->fetch()['total'];
    
    // Contar ponentes
    $stmt_ponentes = $pdo->query("SELECT COUNT(*) as total FROM ponente");
    $total_ponentes = $stmt_ponentes->fetch()['total'];
    
    // Obtener participantes con detalles
    $stmt_lista_participantes = $pdo->query("
        SELECT p.ID, p.Nombre, p.Apellido, p.Numero_de_documento, 
               td.Tipo_documento, p.Fecha_de_inscripcion 
        FROM participante p 
        INNER JOIN tipo_documento td ON p.Id_Tipo_documento = td.ID 
        ORDER BY p.Fecha_de_inscripcion DESC
    ");
    $participantes = $stmt_lista_participantes->fetchAll();
    
    // Obtener ponentes con presentaciones
    $stmt_lista_ponentes = $pdo->query("
        SELECT p.ID, p.Nombre, p.Apellido, p.Numero_de_documento, 
               p.Titulo_de_la_presentacion, p.Presentacion, 
               td.Tipo_documento, p.Fecha_de_inscripcion 
        FROM ponente p 
        INNER JOIN tipo_documento td ON p.Id_Tipo_documento = td.ID 
        ORDER BY p.Fecha_de_inscripcion DESC
    ");
    $ponentes = $stmt_lista_ponentes->fetchAll();
    
} catch (PDOException $e) {
    $total_participantes = 0;
    $total_ponentes = 0;
    $participantes = [];
    $ponentes = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Seminario PITI 2025</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8fafc;
            color: #334155;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .role-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .btn-logout {
            background: rgba(239, 68, 68, 0.9);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background 0.3s;
        }
        
        .btn-logout:hover {
            background: #dc2626;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid;
        }
        
        .stat-card.participantes {
            border-left-color: #10b981;
        }
        
        .stat-card.ponentes {
            border-left-color: #f59e0b;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-card.participantes .stat-number {
            color: #10b981;
        }
        
        .stat-card.ponentes .stat-number {
            color: #f59e0b;
        }
        
        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .data-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .section-header {
            background: #f8fafc;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            color: #475569;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-export {
            background: #059669;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }
        
        .btn-export:hover {
            background: #047857;
            transform: translateY(-1px);
        }
        
        .btn-export:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f1f5f9;
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            color: #475569;
            font-size: 0.9rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        td {
            padding: 0.75rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }
        
        tr:hover {
            background: #f8fafc;
        }
        
        .btn-download {
            background: #3b82f6;
            color: white;
            padding: 0.4rem 0.8rem;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            transition: background 0.3s;
        }
        
        .btn-download:hover {
            background: #2563eb;
        }
        
        .btn-download:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        
        .no-data {
            text-align: center;
            padding: 2rem;
            color: #64748b;
        }
        
        .date-badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .container {
                padding: 0 0.5rem;
            }
            
            .section-header {
                flex-direction: column;
                gap: 0.5rem;
                align-items: stretch;
            }
            
            .table-container {
                font-size: 0.8rem;
            }
            
            th, td {
                padding: 0.5rem 0.25rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <h1><i class="bi bi-speedometer2"></i> Dashboard - Seminario PITI 2025</h1>
        <div class="user-info">
            <span>Bienvenido, <?php echo htmlspecialchars($current_user['usuario']); ?></span>
            <span class="role-badge"><?php echo htmlspecialchars($current_user['rol_nombre']); ?></span>
            <a href="../../SQL/logout.php" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
            </a>
        </div>
    </header>

    <div class="container">
        <!-- Estadísticas -->
        <div class="stats-cards">
            <div class="stat-card participantes">
                <div class="stat-number"><?php echo $total_participantes; ?></div>
                <div class="stat-label">Participantes Registrados</div>
            </div>
            <div class="stat-card ponentes">
                <div class="stat-number"><?php echo $total_ponentes; ?></div>
                <div class="stat-label">Ponentes Confirmados</div>
            </div>
        </div>

        <!-- Lista de Participantes -->
        <div class="data-section">
            <div class="section-header">
                <div class="section-title">
                    <i class="bi bi-people-fill"></i> 
                    Lista de Participantes (<?php echo count($participantes); ?>)
                </div>
            </div>
            <div class="table-container">
                <?php if (!empty($participantes)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Tipo Documento</th>
                            <th>Número Documento</th>
                            <th>Fecha Inscripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participantes as $participante): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($participante['ID']); ?></td>
                            <td><?php echo htmlspecialchars($participante['Nombre'] . ' ' . $participante['Apellido']); ?></td>
                            <td><?php echo htmlspecialchars($participante['Tipo_documento']); ?></td>
                            <td><?php echo htmlspecialchars($participante['Numero_de_documento']); ?></td>
                            <td>
                                <span class="date-badge">
                                    <?php echo date('d/m/Y H:i', strtotime($participante['Fecha_de_inscripcion'])); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-data">
                    <i class="bi bi-inbox" style="font-size: 2rem; color: #9ca3af;"></i>
                    <p>No hay participantes registrados aún.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Lista de Ponentes -->
        <div class="data-section">
            <div class="section-header">
                <div class="section-title">
                    <i class="bi bi-mic-fill"></i> 
                    Lista de Ponentes (<?php echo count($ponentes); ?>)
                </div>
                <a href="../../SQL/export_ponentes.php" class="btn-export" <?php echo count($ponentes) == 0 ? 'style="pointer-events: none; opacity: 0.5;"' : ''; ?>>
                    <i class="bi bi-file-earmark-excel"></i>
                    Exportar a Excel
                </a>
            </div>
            <div class="table-container">
                <?php if (!empty($ponentes)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Documento</th>
                            <th>Título Presentación</th>
                            <th>Presentación</th>
                            <th>Fecha Inscripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ponentes as $ponente): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ponente['ID']); ?></td>
                            <td><?php echo htmlspecialchars($ponente['Nombre'] . ' ' . $ponente['Apellido']); ?></td>
                            <td>
                                <small><?php echo htmlspecialchars($ponente['Tipo_documento']); ?></small><br>
                                <?php echo htmlspecialchars($ponente['Numero_de_documento']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($ponente['Titulo_de_la_presentacion']); ?></td>
                            <td>
                                <?php if (!empty($ponente['Presentacion'])): ?>
                                    <?php 
                                    // Obtener el nombre del archivo de la ruta
                                    $filename = basename($ponente['Presentacion']);
                                    $file_path = RUTA . $ponente['Presentacion'];
                                    ?>
                                    <a href="<?php echo $file_path; ?>" 
                                       target="_blank" 
                                       class="btn-download"
                                       download="<?php echo $filename; ?>">
                                        <i class="bi bi-download"></i>
                                        Descargar
                                    </a>
                                    <br>
                                    <small style="color: #64748b;"><?php echo htmlspecialchars($filename); ?></small>
                                <?php else: ?>
                                    <span style="color: #9ca3af;">Sin archivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="date-badge">
                                    <?php echo date('d/m/Y H:i', strtotime($ponente['Fecha_de_inscripcion'])); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-data">
                    <i class="bi bi-inbox" style="font-size: 2rem; color: #9ca3af;"></i>
                    <p>No hay ponentes registrados aún.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>