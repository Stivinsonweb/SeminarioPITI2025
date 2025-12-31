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
    $stmt = $pdo->query("
        SELECT p.ID, p.Nombre, p.Apellido, p.Numero_de_documento, 
               td.Tipo_documento, p.Fecha_de_inscripcion 
        FROM participante p 
        INNER JOIN tipo_documento td ON p.Id_Tipo_documento = td.ID 
        ORDER BY p.Fecha_de_inscripcion DESC
    ");
    $participantes = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $participantes = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Participantes - Seminario PITI 2025</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
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
        
        .btn-export-pdf {
            background: #dc2626;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .btn-export-pdf:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }
        
        .data-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .data-header {
            background: #f8fafc;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .data-title {
            font-weight: 600;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .count-badge {
            background: #10b981;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .search-box {
            position: relative;
            max-width: 300px;
        }
        
        .search-box input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .search-box i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
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
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #475569;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e2e8f0;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }
        
        tr:hover {
            background: #f8fafc;
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
            font-size: 0.8rem;
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
            
            .page-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            table {
                font-size: 0.8rem;
            }
            
            th, td {
                padding: 0.5rem;
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
            
            <a href="listado_participantes.php" class="menu-item active">
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
                <h1><i class="bi bi-people-fill"></i> Listado de Participantes</h1>
                <p>Gestión de participantes inscritos en el seminario</p>
            </div>
            <button onclick="exportToPDF()" class="btn-export-pdf" id="btnExportPDF">
                <i class="bi bi-file-earmark-pdf"></i>
                Descargar PDF
            </button>
        </div>

        <div class="data-container">
            <div class="data-header">
                <div class="data-title">
                    <span>Total de Participantes</span>
                    <span class="count-badge"><?php echo count($participantes); ?></span>
                </div>
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar por documento..." onkeyup="filterTable()">
                </div>
            </div>
            
            <div class="table-container">
                <?php if (!empty($participantes)): ?>
                <table id="participantesTable">
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
                    <i class="bi bi-inbox"></i>
                    <p><strong>No hay participantes registrados</strong></p>
                    <p style="font-size: 0.9rem; margin-top: 0.5rem;">Los participantes aparecerán aquí una vez se inscriban.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        function filterTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('participantesTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td')[3];
                if (td) {
                    const txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = '';
                    } else {
                        tr[i].style.display = 'none';
                    }
                }
            }
        }

        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            doc.setFontSize(18);
            doc.text('Listado de Participantes', 14, 22);
            doc.setFontSize(11);
            doc.text('Seminario PITI 2025', 14, 30);
            
            const tableData = [];
            const table = document.getElementById('participantesTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                if (rows[i].style.display !== 'none') {
                    const cols = rows[i].getElementsByTagName('td');
                    const rowData = [];
                    for (let j = 0; j < cols.length; j++) {
                        rowData.push(cols[j].innerText);
                    }
                    tableData.push(rowData);
                }
            }
            
            doc.autoTable({
                startY: 35,
                head: [['ID', 'Nombre Completo', 'Tipo Doc.', 'Número Doc.', 'Fecha Inscripción']],
                body: tableData,
                theme: 'grid',
                headStyles: {
                    fillColor: [16, 185, 129],
                    textColor: 255,
                    fontStyle: 'bold'
                },
                styles: {
                    fontSize: 9,
                    cellPadding: 3
                }
            });
            
            doc.save('participantes_seminario_piti_2025.pdf');
        }
    </script>
    <script src="../assets/js/hamburger_menu.js"></script>
</body>
</html>