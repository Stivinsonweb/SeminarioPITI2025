<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../src/Pages/login.php');
    exit();
}

// Verificar que sea SuperAdmin
if ($_SESSION['rol_id'] != 1) {
    header('Location: confirmacion_participantes.php');
    exit();
}

include '../../ruta.php';
include '../../SQL/db_connect.php';
include '../inc/head.php';

function getCurrentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'usuario' => $_SESSION['usuario'] ?? null,
        'rol_id' => $_SESSION['rol_id'] ?? null,
        'rol_nombre' => $_SESSION['rol_nombre'] ?? null
    ];
}

$current_user = getCurrentUser();

// Obtener filtros
$filtro_representante = $_GET['representante'] ?? '';
$filtro_semestre = $_GET['semestre'] ?? '';

try {
    // Query base
    $sql = "SELECT c.ID, c.Id_Participante, c.Asistion, c.Fecha_de_confirmacion,
               p.Nombre, p.Apellido, p.Numero_de_documento,
               s.Semestre, u.Usuario, u.ID as Id_usuario, td.Tipo_documento,
               r.Rol_nombre
        FROM confirmacion_de_participacion c
        INNER JOIN participante p ON c.Id_Participante = p.ID
        INNER JOIN semestre s ON c.Id_Semestre = s.ID
        INNER JOIN usuario u ON c.Id_usuario = u.ID
        INNER JOIN tipo_documento td ON p.Id_Tipo_documento = td.ID
        INNER JOIN rol r ON u.Id_Rol = r.ID
        WHERE 1=1";
    
    $params = [];
    
    if ($filtro_representante) {
        $sql .= " AND u.Usuario = ?";
        $params[] = $filtro_representante;
    }
    
    if ($filtro_semestre) {
        $sql .= " AND s.Semestre = ?";
        $params[] = $filtro_semestre;
    }
    
    $sql .= " ORDER BY u.Usuario ASC, s.Semestre ASC, p.Apellido ASC, p.Nombre ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $confirmaciones = $stmt->fetchAll();
    
    // Obtener lista de representantes
    $stmt_rep = $pdo->query("
        SELECT DISTINCT u.Usuario 
        FROM confirmacion_de_participacion c
        INNER JOIN usuario u ON c.Id_usuario = u.ID
        ORDER BY u.Usuario ASC
    ");
    $representantes = $stmt_rep->fetchAll(PDO::FETCH_COLUMN);
    
    // Obtener lista de semestres
    $stmt_sem = $pdo->query("SELECT DISTINCT Semestre FROM semestre ORDER BY Semestre ASC");
    $semestres = $stmt_sem->fetchAll(PDO::FETCH_COLUMN);
    
    // Estadísticas generales
    $stmt_stats = $pdo->query("
        SELECT 
            COUNT(DISTINCT c.Id_Participante) as total_participantes,
            COUNT(DISTINCT c.Id_usuario) as total_representantes,
            COUNT(DISTINCT c.Id_Semestre) as total_semestres
        FROM confirmacion_de_participacion c
    ");
    $stats = $stmt_stats->fetch();
    
    // Agrupar por representante
    $agrupado = [];
    foreach ($confirmaciones as $conf) {
        $key = $conf['Usuario'];
        if (!isset($agrupado[$key])) {
            $agrupado[$key] = [
                'representante' => $conf['Usuario'],
                'rol' => $conf['Rol_nombre'],
                'participantes' => []
            ];
        }
        $agrupado[$key]['participantes'][] = $conf;
    }
    
} catch (PDOException $e) {
    $confirmaciones = [];
    $representantes = [];
    $semestres = [];
    $stats = ['total_participantes' => 0, 'total_representantes' => 0, 'total_semestres' => 0];
    $agrupado = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Confirmaciones - SuperAdmin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #334155;
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.2);
        }
        
        .sidebar-header h2 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .user-info-sidebar {
            font-size: 0.85rem;
            opacity: 0.8;
        }
        
        .role-badge-sidebar {
            display: inline-block;
            background: rgba(220, 38, 38, 0.4);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            margin-top: 0.5rem;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #dc2626;
        }
        
        .menu-item.active {
            background: rgba(220, 38, 38, 0.2);
            color: white;
            border-left-color: #dc2626;
        }
        
        .menu-item i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }
        
        .menu-divider {
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 0.5rem 1.5rem;
        }
        
        .btn-logout-sidebar {
            display: flex;
            align-items: center;
            width: calc(100% - 3rem);
            margin: 1rem 1.5rem;
            padding: 0.75rem;
            background: rgba(239, 68, 68, 0.2);
            color: white;
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 6px;
            text-decoration: none;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .btn-logout-sidebar:hover {
            background: rgba(239, 68, 68, 0.3);
        }
        
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 2rem;
            width: calc(100% - 260px);
        }
        
        .page-header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        }
        
        .page-header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .page-header p {
            opacity: 0.9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-left: 4px solid #dc2626;
        }
        
        .stat-card h3 {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .stat-card .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        .filters-container {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .filter-group select {
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            cursor: pointer;
        }
        
        .btn-filtrar {
            background: #dc2626;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-filtrar:hover {
            background: #991b1b;
        }
        
        .btn-limpiar {
            background: #64748b;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 0.5rem;
        }
        
        .btn-limpiar:hover {
            background: #475569;
        }
        
        .btn-exportar {
            background: #10b981;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 0.5rem;
        }
        
        .btn-exportar:hover {
            background: #059669;
        }
        
        .representante-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .representante-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 1rem;
        }
        
        .representante-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .badge-total {
            background: #dc2626;
            color: white;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .participantes-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .participantes-table thead {
            background: #f1f5f9;
        }
        
        .participantes-table th {
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            color: #475569;
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        
        .participantes-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .participantes-table tr:hover {
            background: #f8fafc;
        }
        
        .badge-semestre {
            background: #dbeafe;
            color: #1e40af;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .no-data {
            text-align: center;
            padding: 3rem 1rem;
            color: #64748b;
        }
        
        @media print {
            .sidebar, .filters-container, .btn-exportar {
                display: none;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
            }
            
            .stats-grid, .filters-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <?php include '../inc/header.php'; ?>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2><i class="bi bi-shield-fill-check"></i> SuperAdmin</h2>
            <div class="user-info-sidebar">
                <div><?php echo htmlspecialchars($current_user['usuario']); ?></div>
                <span class="role-badge-sidebar"><?php echo htmlspecialchars($current_user['rol_nombre']); ?></span>
            </div>
        </div>
        
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="bi bi-house-door"></i>
                <span>Inicio</span>
            </a>
            
            <a href="listado_participantes.php" class="menu-item">
                <i class="bi bi-people"></i>
                <span>Participantes</span>
            </a>
            
            <a href="listado_ponentes.php" class="menu-item">
                <i class="bi bi-mic"></i>
                <span>Ponentes</span>
            </a>
            
            <a href="confirmacion_participantes.php" class="menu-item">
                <i class="bi bi-check-circle"></i>
                <span>Confirmación</span>
            </a>
            
            <a href="reporte_confirmaciones_superadmin.php" class="menu-item active">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Reporte Detallado</span>
            </a>
            
            <div class="menu-divider"></div>
            
            <a href="gestion_roles.php" class="menu-item">
                <i class="bi bi-person-gear"></i>
                <span>Gestión de Roles</span>
            </a>
            
            <a href="configuracion.php" class="menu-item">
                <i class="bi bi-gear"></i>
                <span>Configuración</span>
            </a>
        </nav>
        
        <a href="../../SQL/logout.php" class="btn-logout-sidebar">
            <i class="bi bi-box-arrow-right"></i>
            <span style="margin-left: 0.5rem;">Cerrar Sesión</span>
        </a>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1><i class="bi bi-file-earmark-bar-graph-fill"></i> Reporte Detallado de Confirmaciones</h1>
            <p>Visualiza todas las confirmaciones de asistencia organizadas por representante</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><i class="bi bi-people-fill"></i> Total Participantes</h3>
                <div class="stat-value"><?php echo $stats['total_participantes']; ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="bi bi-person-badge"></i> Representantes</h3>
                <div class="stat-value"><?php echo $stats['total_representantes']; ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="bi bi-folder"></i> Semestres</h3>
                <div class="stat-value"><?php echo $stats['total_semestres']; ?></div>
            </div>
        </div>

        <div class="filters-container">
            <h3 style="margin-bottom: 1rem; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                <i class="bi bi-funnel"></i> Filtros de Búsqueda
            </h3>
            <form method="GET" action="">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="representante">Representante</label>
                        <select name="representante" id="representante">
                            <option value="">Todos los representantes</option>
                            <?php foreach ($representantes as $rep): ?>
                                <option value="<?php echo htmlspecialchars($rep); ?>" <?php echo ($filtro_representante === $rep) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($rep); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="semestre">Semestre</label>
                        <select name="semestre" id="semestre">
                            <option value="">Todos los semestres</option>
                            <?php foreach ($semestres as $sem): ?>
                                <option value="<?php echo htmlspecialchars($sem); ?>" <?php echo ($filtro_semestre === $sem) ? 'selected' : ''; ?>>
                                    Semestre <?php echo htmlspecialchars($sem); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn-filtrar">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="reporte_confirmaciones_superadmin.php" class="btn-limpiar">
                        <i class="bi bi-x-circle"></i> Limpiar Filtros
                    </a>
                    <button type="button" class="btn-exportar" onclick="window.print()">
                        <i class="bi bi-printer"></i> Imprimir
                    </button>
                    <button type="button" class="btn-exportar" onclick="exportarExcel()">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel
                    </button>
                </div>
            </form>
        </div>

        <?php if (!empty($agrupado)): ?>
            <?php foreach ($agrupado as $grupo): ?>
            <div class="representante-section">
                <div class="representante-header">
                    <div class="representante-title">
                        <i class="bi bi-person-circle"></i>
                        <?php echo htmlspecialchars($grupo['representante']); ?>
                        <span style="font-size: 0.8rem; color: #64748b; font-weight: normal;">
                            (<?php echo htmlspecialchars($grupo['rol']); ?>)
                        </span>
                    </div>
                    <span class="badge-total"><?php echo count($grupo['participantes']); ?> participantes</span>
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="participantes-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre Completo</th>
                                <th>Tipo Doc.</th>
                                <th>Número Documento</th>
                                <th>Semestre</th>
                                <th>Fecha Confirmación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grupo['participantes'] as $index => $p): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($p['Nombre'] . ' ' . $p['Apellido']); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['Tipo_documento']); ?></td>
                                <td><?php echo htmlspecialchars($p['Numero_de_documento']); ?></td>
                                <td><span class="badge-semestre"><?php echo htmlspecialchars($p['Semestre']); ?></span></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($p['Fecha_de_confirmacion'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-data">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e1;"></i>
                <h3>No hay confirmaciones</h3>
                <p>No se encontraron confirmaciones con los filtros seleccionados</p>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function exportarExcel() {
            Swal.fire({
                title: 'Exportando...',
                text: 'Preparando archivo Excel',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Preparar datos para Excel
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Representante,Rol,Nombre Completo,Tipo Documento,Numero Documento,Semestre,Fecha Confirmacion\n";
            
            <?php foreach ($agrupado as $grupo): ?>
                <?php foreach ($grupo['participantes'] as $p): ?>
                csvContent += "<?php echo addslashes($grupo['representante']); ?>,";
                csvContent += "<?php echo addslashes($grupo['rol']); ?>,";
                csvContent += "<?php echo addslashes($p['Nombre'] . ' ' . $p['Apellido']); ?>,";
                csvContent += "<?php echo addslashes($p['Tipo_documento']); ?>,";
                csvContent += "<?php echo $p['Numero_de_documento']; ?>,";
                csvContent += "<?php echo $p['Semestre']; ?>,";
                csvContent += "<?php echo date('d/m/Y H:i', strtotime($p['Fecha_de_confirmacion'])); ?>\n";
                <?php endforeach; ?>
            <?php endforeach; ?>

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "reporte_confirmaciones_" + new Date().getTime() + ".csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            Swal.fire({
                icon: 'success',
                title: '¡Exportado!',
                text: 'El archivo se ha descargado correctamente',
                timer: 2000,
                showConfirmButton: false
            });
        }
    </script>
    <script src="../assets/js/hamburger_menu.js"></script>
</body>
</html>
