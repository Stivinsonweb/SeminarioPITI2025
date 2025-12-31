<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../src/Pages/login.php');
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

if ($current_user['rol_id'] == 3) {
    header('Location: confirmacion_participantes.php');
    exit();
}

try {
    $stmt_egresados = $pdo->query("
        SELECT p.ID, p.Nombre, p.Apellido, p.Numero_de_documento, p.Correo_electronico, p.Telefono,
               td.Tipo_documento, p.Fecha_de_inscripcion,
               pe.Titulo_de_la_presentacion, pe.Presentacion, pe.Hoja_de_vida,
               t.Tematicas
        FROM ponentes p
        INNER JOIN tipo_documento td ON p.Id_Tipo_documento = td.ID
        INNER JOIN ponente_egresado pe ON p.ID = pe.Id_Ponentes
        INNER JOIN tematica t ON pe.Id_Tematica = t.ID
        WHERE p.Id_Tipo_Ponente = 2
        ORDER BY p.Fecha_de_inscripcion DESC
    ");
    $ponentes_egresados = $stmt_egresados->fetchAll();
    
    $stmt_nacionales = $pdo->query("
        SELECT p.ID, p.Nombre, p.Apellido, p.Numero_de_documento, p.Correo_electronico, p.Telefono,
               td.Tipo_documento, p.Fecha_de_inscripcion,
               pn.Titulo_de_la_presentacion, pn.Presentacion,
               t.Tematicas
        FROM ponentes p
        INNER JOIN tipo_documento td ON p.Id_Tipo_documento = td.ID
        INNER JOIN ponente_nacional pn ON p.ID = pn.Id_Ponentes
        INNER JOIN tematica t ON pn.Id_Tematica = t.ID
        WHERE p.Id_Tipo_Ponente = 1
        ORDER BY p.Fecha_de_inscripcion DESC
    ");
    $ponentes_nacionales = $stmt_nacionales->fetchAll();
    
} catch (PDOException $e) {
    $ponentes_egresados = [];
    $ponentes_nacionales = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Ponentes - Seminario PITI 2025</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
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
            background: rgba(139, 92, 246, 0.3);
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
            border-left-color: #8b5cf6;
        }
        
        .menu-item.active {
            background: rgba(139, 92, 246, 0.2);
            color: white;
            border-left-color: #8b5cf6;
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
            margin-bottom: 2rem;
        }
        
        .page-title h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }
        
        .page-title p {
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .tabs-container {
            background: white;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .tabs {
            display: flex;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .tab {
            flex: 1;
            padding: 1rem 1.5rem;
            background: white;
            border: none;
            font-size: 1rem;
            font-weight: 500;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }
        
        .tab:hover {
            background: #f8fafc;
            color: #475569;
        }
        
        .tab.active {
            color: #8b5cf6;
            border-bottom-color: #8b5cf6;
            background: #faf5ff;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .data-header {
            background: #f8fafc;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .count-badge {
            background: #f59e0b;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .btn-export-pdf {
            background: #dc2626;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-export-pdf:hover {
            background: #b91c1c;
            transform: translateY(-1px);
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
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e2e8f0;
        }
        
        td {
            padding: 0.75rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
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
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            transition: background 0.3s;
            margin: 0.2rem;
        }
        
        .btn-download:hover {
            background: #2563eb;
        }
        
        .btn-download.secondary {
            background: #10b981;
        }
        
        .btn-download.secondary:hover {
            background: #059669;
        }
        
        .no-data {
            text-align: center;
            padding: 3rem 1rem;
            color: #64748b;
        }
        
        .no-data i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }
        
        .date-badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
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
            
            table {
                font-size: 0.75rem;
            }
            
            th, td {
                padding: 0.5rem 0.25rem;
            }
        }
    </style>
</head>
<body>

    <?php include 'hamburger_menu.php'; ?>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
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
            
            <a href="listado_ponentes.php" class="menu-item active">
                <i class="bi bi-mic"></i>
                <span>Ponentes</span>
            </a>
            
            <a href="confirmacion_participantes.php" class="menu-item">
                <i class="bi bi-check-circle"></i>
                <span>Confirmación</span>
            </a>
            
            <?php if ($current_user['rol_id'] == 2): ?>
            <div class="menu-divider"></div>
            
            <a href="gestion_roles.php" class="menu-item">
                <i class="bi bi-person-gear"></i>
                <span>Gestión de Roles</span>
            </a>
            <?php endif; ?>
            
            <?php if ($current_user['rol_id'] == 1 || $current_user['rol_id'] == 2): ?>
            <a href="configuracion.php" class="menu-item">
                <i class="bi bi-gear"></i>
                <span>Configuración</span>
            </a>
            <?php endif; ?>
        </nav>
        
        <a href="../../SQL/logout.php" class="btn-logout-sidebar">
            <i class="bi bi-box-arrow-right"></i>
            <span style="margin-left: 0.5rem;">Cerrar Sesión</span>
        </a>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="bi bi-mic-fill"></i> Listado de Ponentes</h1>
                <p>Gestión de ponentes egresados y nacionales</p>
            </div>
        </div>

        <div class="tabs-container">
            <div class="tabs">
                <button class="tab active" onclick="openTab(event, 'egresados')">
                    <i class="bi bi-mortarboard-fill"></i> Ponentes Egresados
                    <span class="count-badge"><?php echo count($ponentes_egresados); ?></span>
                </button>
                <button class="tab" onclick="openTab(event, 'nacionales')">
                    <i class="bi bi-flag-fill"></i> Ponentes Nacionales
                    <span class="count-badge" style="background: #3b82f6;"><?php echo count($ponentes_nacionales); ?></span>
                </button>
            </div>

            <div id="egresados" class="tab-content active">
                <div class="data-header">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-weight: 600; color: #475569;">Ponentes Egresados</span>
                    </div>
                    <button onclick="exportEgresadosPDF()" class="btn-export-pdf">
                        <i class="bi bi-file-earmark-pdf"></i>
                        Descargar PDF
                    </button>
                </div>
                
                <div class="table-container">
                    <?php if (!empty($ponentes_egresados)): ?>
                    <table id="tableEgresados">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Documento</th>
                                <th>Contacto</th>
                                <th>Temática</th>
                                <th>Título</th>
                                <th>Archivos</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ponentes_egresados as $ponente): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ponente['Nombre'] . ' ' . $ponente['Apellido']); ?></td>
                                <td>
                                    <small><?php echo htmlspecialchars($ponente['Tipo_documento']); ?></small><br>
                                    <?php echo htmlspecialchars($ponente['Numero_de_documento']); ?>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($ponente['Correo_electronico']); ?></small><br>
                                    <small><?php echo htmlspecialchars($ponente['Telefono']); ?></small>
                                </td>
                                <td><small><?php echo htmlspecialchars($ponente['Tematicas']); ?></small></td>
                                <td><small><?php echo htmlspecialchars($ponente['Titulo_de_la_presentacion']); ?></small></td>
                                <td>
                                    <?php if (!empty($ponente['Presentacion'])): ?>
                                    <a href="<?php echo RUTA . $ponente['Presentacion']; ?>" 
                                    class="btn-download" 
                                    target="_blank" 
                                    download>
                                        <i class="bi bi-file-earmark-slides"></i>
                                        Diapositivas
                                    </a>
                                    <?php endif; ?>
                                    <?php if (!empty($ponente['Hoja_de_vida'])): ?>
                                    <a href="<?php echo RUTA . $ponente['Hoja_de_vida']; ?>" 
                                    class="btn-download secondary" 
                                    target="_blank" 
                                    download>
                                        <i class="bi bi-file-earmark-person"></i>
                                        Hoja de Vida
                                    </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="date-badge">
                                        <?php echo date('d/m/Y', strtotime($ponente['Fecha_de_inscripcion'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-data">
                        <i class="bi bi-inbox"></i>
                        <p><strong>No hay ponentes egresados registrados</strong></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div id="nacionales" class="tab-content">
                <div class="data-header">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-weight: 600; color: #475569;">Ponentes Nacionales</span>
                    </div>
                    <button onclick="exportNacionalesPDF()" class="btn-export-pdf">
                        <i class="bi bi-file-earmark-pdf"></i>
                        Descargar PDF
                    </button>
                </div>
                
                <div class="table-container">
                    <?php if (!empty($ponentes_nacionales)): ?>
                    <table id="tableNacionales">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Documento</th>
                                <th>Contacto</th>
                                <th>Temática</th>
                                <th>Título</th>
                                <th>Diapositivas</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ponentes_nacionales as $ponente): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ponente['Nombre'] . ' ' . $ponente['Apellido']); ?></td>
                                <td>
                                    <small><?php echo htmlspecialchars($ponente['Tipo_documento']); ?></small><br>
                                    <?php echo htmlspecialchars($ponente['Numero_de_documento']); ?>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($ponente['Correo_electronico']); ?></small><br>
                                    <small><?php echo htmlspecialchars($ponente['Telefono']); ?></small>
                                </td>
                                <td><small><?php echo htmlspecialchars($ponente['Tematicas']); ?></small></td>
                                <td><small><?php echo htmlspecialchars($ponente['Titulo_de_la_presentacion']); ?></small></td>
                                <td>
                                    <?php if (!empty($ponente['Presentacion'])): ?>
                                        <a href="<?php echo RUTA . $ponente['Presentacion']; ?>" 
                                           class="btn-download" 
                                           target="_blank" 
                                           download>
                                            <i class="bi bi-file-earmark-slides"></i>
                                            Descargar
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="date-badge">
                                        <?php echo date('d/m/Y', strtotime($ponente['Fecha_de_inscripcion'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-data">
                        <i class="bi bi-inbox"></i>
                        <p><strong>No hay ponentes nacionales registrados</strong></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        function openTab(evt, tabName) {
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }
            
            const tabs = document.getElementsByClassName('tab');
            for (let i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove('active');
            }
            
            document.getElementById(tabName).classList.add('active');
            evt.currentTarget.classList.add('active');
        }

        function exportEgresadosPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('landscape');
            
            doc.setFontSize(16);
            doc.text('Listado de Ponentes Egresados', 14, 15);
            doc.setFontSize(10);
            doc.text('Seminario PITI 2025', 14, 22);
            
            const tableData = [];
            const table = document.getElementById('tableEgresados');
            if (table) {
                const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                
                for (let i = 0; i < rows.length; i++) {
                    const cols = rows[i].getElementsByTagName('td');
                    tableData.push([
                        cols[0].innerText,
                        cols[1].innerText.replace(/\n/g, ' '),
                        cols[2].innerText.replace(/\n/g, ' '),
                        cols[3].innerText,
                        cols[4].innerText,
                        cols[6].innerText
                    ]);
                }
            }
            
            doc.autoTable({
                startY: 28,
                head: [['Nombre', 'Documento', 'Contacto', 'Temática', 'Título', 'Fecha']],
                body: tableData,
                theme: 'grid',
                headStyles: {
                    fillColor: [245, 158, 11],
                    textColor: 255,
                    fontStyle: 'bold'
                },
                styles: {
                    fontSize: 8,
                    cellPadding: 2
                }
            });
            
            doc.save('ponentes_egresados_piti_2025.pdf');
        }

        function exportNacionalesPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('landscape');
            
            doc.setFontSize(16);
            doc.text('Listado de Ponentes Nacionales', 14, 15);
            doc.setFontSize(10);
            doc.text('Seminario PITI 2025', 14, 22);
            
            const tableData = [];
            const table = document.getElementById('tableNacionales');
            if (table) {
                const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                
                for (let i = 0; i < rows.length; i++) {
                    const cols = rows[i].getElementsByTagName('td');
                    tableData.push([
                        cols[0].innerText,
                        cols[1].innerText.replace(/\n/g, ' '),
                        cols[2].innerText.replace(/\n/g, ' '),
                        cols[3].innerText,
                        cols[4].innerText,
                        cols[6].innerText
                    ]);
                }
            }
            
            doc.autoTable({
                startY: 28,
                head: [['Nombre', 'Documento', 'Contacto', 'Temática', 'Título', 'Fecha']],
                body: tableData,
                theme: 'grid',
                headStyles: {
                    fillColor: [59, 130, 246],
                    textColor: 255,
                    fontStyle: 'bold'
                },
                styles: {
                    fontSize: 8,
                    cellPadding: 2
                }
            });
            
            doc.save('ponentes_nacionales_piti_2025.pdf');
        }
    </script>
    <script src="../assets/js/hamburger_menu.js"></script>
</body>
</html>