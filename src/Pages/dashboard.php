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
    $stmt_participantes = $pdo->query("SELECT COUNT(*) as total FROM participante");
    $total_participantes = $stmt_participantes->fetch()['total'];
    
    $stmt_ponentes_egresados = $pdo->query("
        SELECT COUNT(*) as total FROM ponentes WHERE Id_Tipo_Ponente = 2
    ");
    $total_egresados = $stmt_ponentes_egresados->fetch()['total'];
    
    $stmt_ponentes_nacionales = $pdo->query("
        SELECT COUNT(*) as total FROM ponentes WHERE Id_Tipo_Ponente = 1
    ");
    $total_nacionales = $stmt_ponentes_nacionales->fetch()['total'];
    
    $total_ponentes = $total_egresados + $total_nacionales;
    
} catch (PDOException $e) {
    $total_participantes = 0;
    $total_egresados = 0;
    $total_nacionales = 0;
    $total_ponentes = 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Seminario PITI 2025</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
            padding: 0;
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
        
        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .page-header p {
            color: #64748b;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-left: 4px solid;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .stat-card.participantes {
            border-left-color: #10b981;
        }
        
        .stat-card.egresados {
            border-left-color: #f59e0b;
        }
        
        .stat-card.nacionales {
            border-left-color: #3b82f6;
        }
        
        .stat-card.total {
            border-left-color: #8b5cf6;
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-card.participantes .stat-icon {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        
        .stat-card.egresados .stat-icon {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        
        .stat-card.nacionales .stat-icon {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
        
        .stat-card.total .stat-icon {
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        
        .stat-card.participantes .stat-number {
            color: #10b981;
        }
        
        .stat-card.egresados .stat-number {
            color: #f59e0b;
        }
        
        .stat-card.nacionales .stat-number {
            color: #3b82f6;
        }
        
        .stat-card.total .stat-number {
            color: #8b5cf6;
        }
        
        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .chart-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .chart-header {
            margin-bottom: 1.5rem;
        }
        
        .chart-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
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
            <a href="dashboard.php" class="menu-item active">
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
            
            <?php if ($current_user['rol_id'] != 3): ?>
            <a href="confirmacion_participantes.php" class="menu-item">
                <i class="bi bi-check-circle"></i>
                <span>Confirmación</span>
            </a>
            <?php endif; ?>
            
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
            <h1><i class="bi bi-graph-up"></i> Panel de Estadísticas</h1>
            <p>Vista general del seminario PITI 2025</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card participantes">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo $total_participantes; ?></div>
                        <div class="stat-label">Participantes</div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card egresados">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo $total_egresados; ?></div>
                        <div class="stat-label">Ponentes Egresados</div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card nacionales">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo $total_nacionales; ?></div>
                        <div class="stat-label">Ponentes Nacionales</div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-flag-fill"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card total">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo $total_ponentes; ?></div>
                        <div class="stat-label">Total Ponentes</div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-mic-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-container">
            <div class="chart-header">
                <h2><i class="bi bi-bar-chart-fill"></i> Distribución de Inscripciones</h2>
            </div>
            <canvas id="statsChart" height="80"></canvas>
        </div>
    </main>

    <script>
        const ctx = document.getElementById('statsChart').getContext('2d');
        const statsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Participantes', 'Ponentes Egresados', 'Ponentes Nacionales'],
                datasets: [{
                    label: 'Cantidad de Inscritos',
                    data: [
                        <?php echo $total_participantes; ?>,
                        <?php echo $total_egresados; ?>,
                        <?php echo $total_nacionales; ?>
                    ],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(59, 130, 246, 0.8)'
                    ],
                    borderColor: [
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(59, 130, 246)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#fff',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
    <script src="../assets/js/hamburger_menu.js"></script>
</body>
</html>