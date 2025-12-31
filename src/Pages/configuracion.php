<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../src/Pages/login.php');
    exit();
}

include '../../ruta.php';
$pageTitle = "Semana de la Ciencia 2025";
$pageDescription = "Ya puede generar el certificado de la Semana de la Ciencia 2025. Descarga tu certificado de participación del evento.";
$pageImage = RUTA . "src/assets/img/Noticias/preview-noticias.png";
$pageUrl = RUTA . "src/Pages/noticias.php";
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
    $stmt = $pdo->prepare("SELECT Id_Habilitar_preinscripcion, Id_Habilitar_Certificado FROM usuario WHERE Usuario = 'Admin' LIMIT 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $preinscripcionHabilitada = $config['Id_Habilitar_preinscripcion'] == 1;
    $certificadosHabilitados = $config['Id_Habilitar_Certificado'] == 1;
    
} catch (PDOException $e) {
    $preinscripcionHabilitada = false;
    $certificadosHabilitados = false;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Seminario PITI 2025</title>
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
        
        .config-grid {
            display: grid;
            gap: 1.5rem;
            max-width: 800px;
        }
        
        .config-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .config-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .config-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .config-description {
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #10b981;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .status-enabled {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-disabled {
            background: #fee2e2;
            color: #991b1b;
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
            
            <a href="configuracion.php" class="menu-item active">
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
            <div class="page-title">
                <h1><i class="bi bi-gear-fill"></i> Configuración del Sistema</h1>
                <p>Administra las opciones del seminario</p>
            </div>
        </div>

        <div class="config-grid">
            <div class="config-card">
                <div class="config-header">
                    <div class="config-title">
                        <i class="bi bi-pencil-square"></i>
                        Preinscripciones
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="togglePreinscripcion" <?php echo $preinscripcionHabilitada ? 'checked' : ''; ?> onchange="togglePreinscripcion(this)">
                        <span class="slider"></span>
                    </label>
                </div>
                <p class="config-description">
                    Permite a los usuarios inscribirse como participantes o ponentes en el seminario.
                </p>
                <div style="margin-top: 1rem;">
                    <span class="status-badge <?php echo $preinscripcionHabilitada ? 'status-enabled' : 'status-disabled'; ?>" id="statusPreinscripcion">
                        <i class="bi <?php echo $preinscripcionHabilitada ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?>"></i>
                        <?php echo $preinscripcionHabilitada ? 'Habilitado' : 'Deshabilitado'; ?>
                    </span>
                </div>
            </div>

            <div class="config-card">
                <div class="config-header">
                    <div class="config-title">
                        <i class="bi bi-award"></i>
                        Certificados
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="toggleCertificados" <?php echo $certificadosHabilitados ? 'checked' : ''; ?> onchange="toggleCertificados(this)">
                        <span class="slider"></span>
                    </label>
                </div>
                <p class="config-description">
                    Permite a los participantes descargar sus certificados de asistencia al seminario.
                </p>
                <div style="margin-top: 1rem;">
                    <span class="status-badge <?php echo $certificadosHabilitados ? 'status-enabled' : 'status-disabled'; ?>" id="statusCertificados">
                        <i class="bi <?php echo $certificadosHabilitados ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?>"></i>
                        <?php echo $certificadosHabilitados ? 'Habilitado' : 'Deshabilitado'; ?>
                    </span>
                </div>
            </div>
        </div>
    </main>

    <script>
        async function togglePreinscripcion(checkbox) {
            const nuevoEstado = checkbox.checked ? 1 : 2;
            
            try {
                const response = await fetch('../../SQL/actualizar_configuracion.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        tipo: 'preinscripcion',
                        estado: nuevoEstado
                    })
                });

                const data = await response.json();

                if (data.success) {
                    const statusElement = document.getElementById('statusPreinscripcion');
                    if (checkbox.checked) {
                        statusElement.className = 'status-badge status-enabled';
                        statusElement.innerHTML = '<i class="bi bi-check-circle-fill"></i> Habilitado';
                    } else {
                        statusElement.className = 'status-badge status-disabled';
                        statusElement.innerHTML = '<i class="bi bi-x-circle-fill"></i> Deshabilitado';
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    checkbox.checked = !checkbox.checked;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            } catch (error) {
                checkbox.checked = !checkbox.checked;
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo actualizar la configuración'
                });
            }
        }

        async function toggleCertificados(checkbox) {
            const nuevoEstado = checkbox.checked ? 1 : 2;
            
            try {
                const response = await fetch('../../SQL/actualizar_configuracion.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        tipo: 'certificados',
                        estado: nuevoEstado
                    })
                });

                const data = await response.json();

                if (data.success) {
                    const statusElement = document.getElementById('statusCertificados');
                    if (checkbox.checked) {
                        statusElement.className = 'status-badge status-enabled';
                        statusElement.innerHTML = '<i class="bi bi-check-circle-fill"></i> Habilitado';
                    } else {
                        statusElement.className = 'status-badge status-disabled';
                        statusElement.innerHTML = '<i class="bi bi-x-circle-fill"></i> Deshabilitado';
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    checkbox.checked = !checkbox.checked;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            } catch (error) {
                checkbox.checked = !checkbox.checked;
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo actualizar la configuración'
                });
            }
        }
    </script>
    <script src="../../src/assets/js/hamburger_menu.js"></script>
</body>
</html>