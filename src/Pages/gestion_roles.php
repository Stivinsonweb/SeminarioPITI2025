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

if ($current_user['rol_id'] != 2) {
    header('Location: dashboard.php');
    exit();
}

try {
    $stmt_usuarios = $pdo->query("
        SELECT u.ID, u.Usuario, r.Rol as Rol_Nombre, u.Id_rol
        FROM usuario u
        INNER JOIN rol r ON u.Id_rol = r.ID
        WHERE u.Id_rol != 2
        ORDER BY u.Usuario ASC
    ");
    $usuarios = $stmt_usuarios->fetchAll();
    
    $stmt_roles = $pdo->query("SELECT ID, Rol FROM rol WHERE ID != 2 ORDER BY ID");
    $roles = $stmt_roles->fetchAll();
    
} catch (PDOException $e) {
    $usuarios = [];
    $roles = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Roles - Seminario PITI 2025</title>
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
        
        .btn-nuevo {
            background: #8b5cf6;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-nuevo:hover {
            background: #7c3aed;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }
        
        .data-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
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
        
        .role-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .role-admin {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .role-estudiante {
            background: #fef3c7;
            color: #92400e;
        }
        
        .btn-action {
            padding: 0.4rem 0.8rem;
            border: none;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            margin: 0 0.25rem;
        }
        
        .btn-editar {
            background: #3b82f6;
            color: white;
        }
        
        .btn-editar:hover {
            background: #2563eb;
        }
        
        .btn-eliminar {
            background: #ef4444;
            color: white;
        }
        
        .btn-eliminar:hover {
            background: #dc2626;
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
            
            <div class="menu-divider"></div>
            
            <a href="gestion_roles.php" class="menu-item active">
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
            <div class="page-title">
                <h1><i class="bi bi-person-gear"></i> Gestión de Roles</h1>
                <p>Administra los usuarios del sistema</p>
            </div>
            <button class="btn-nuevo" onclick="nuevoUsuario()">
                <i class="bi bi-plus-circle"></i>
                Nuevo Usuario
            </button>
        </div>

        <div class="data-container">
            <div class="table-container">
                <?php if (!empty($usuarios)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['ID']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['Usuario']); ?></td>
                            <td>
                                <span class="role-badge <?php echo $usuario['Id_rol'] == 1 ? 'role-admin' : 'role-estudiante'; ?>">
                                    <?php echo htmlspecialchars($usuario['Rol_Nombre']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-action btn-editar" onclick="editarUsuario(<?php echo $usuario['ID']; ?>, '<?php echo htmlspecialchars($usuario['Usuario']); ?>', <?php echo $usuario['Id_rol']; ?>)">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <button class="btn-action btn-eliminar" onclick="eliminarUsuario(<?php echo $usuario['ID']; ?>, '<?php echo htmlspecialchars($usuario['Usuario']); ?>')">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-data">
                    <i class="bi bi-person-x"></i>
                    <p><strong>No hay usuarios registrados</strong></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        async function nuevoUsuario() {
            const { value: formValues } = await Swal.fire({
                title: 'Nuevo Usuario',
                html:
                    '<input id="swal-input1" class="swal2-input" placeholder="Usuario">' +
                    '<input id="swal-input2" type="password" class="swal2-input" placeholder="Contraseña">' +
                    '<select id="swal-input3" class="swal2-input">' +
                    '<option value="">Selecciona un rol</option>' +
                    <?php foreach ($roles as $rol): ?>
                    '<option value="<?php echo $rol['ID']; ?>"><?php echo htmlspecialchars($rol['Rol']); ?></option>' +
                    <?php endforeach; ?>
                    '</select>',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Crear',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const usuario = document.getElementById('swal-input1').value;
                    const password = document.getElementById('swal-input2').value;
                    const rol = document.getElementById('swal-input3').value;
                    
                    if (!usuario || !password || !rol) {
                        Swal.showValidationMessage('Todos los campos son obligatorios');
                        return false;
                    }
                    
                    return { usuario, password, rol };
                }
            });

            if (formValues) {
                try {
                    const response = await fetch('../../SQL/gestionar_usuario.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            accion: 'crear',
                            ...formValues
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Usuario creado!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo crear el usuario'
                    });
                }
            }
        }

        async function editarUsuario(id, usuarioActual, rolActual) {
            const { value: formValues } = await Swal.fire({
                title: 'Editar Usuario',
                html:
                    '<input id="swal-input1" class="swal2-input" placeholder="Usuario" value="' + usuarioActual + '">' +
                    '<input id="swal-input2" type="password" class="swal2-input" placeholder="Nueva Contraseña (opcional)">' +
                    '<select id="swal-input3" class="swal2-input">' +
                    <?php foreach ($roles as $rol): ?>
                    '<option value="<?php echo $rol['ID']; ?>" ' + (<?php echo $rol['ID']; ?> == rolActual ? 'selected' : '') + '><?php echo htmlspecialchars($rol['Rol']); ?></option>' +
                    <?php endforeach; ?>
                    '</select>',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Actualizar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const usuario = document.getElementById('swal-input1').value;
                    const password = document.getElementById('swal-input2').value;
                    const rol = document.getElementById('swal-input3').value;
                    
                    if (!usuario || !rol) {
                        Swal.showValidationMessage('Usuario y rol son obligatorios');
                        return false;
                    }
                    
                    return { usuario, password, rol };
                }
            });

            if (formValues) {
                try {
                    const response = await fetch('../../SQL/gestionar_usuario.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            accion: 'editar',
                            id: id,
                            ...formValues
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Usuario actualizado!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo actualizar el usuario'
                    });
                }
            }
        }

        async function eliminarUsuario(id, usuario) {
            const result = await Swal.fire({
                title: '¿Eliminar usuario?',
                text: `Se eliminará el usuario "${usuario}"`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ef4444'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('../../SQL/gestionar_usuario.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            accion: 'eliminar',
                            id: id
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Usuario eliminado!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo eliminar el usuario'
                    });
                }
            }
        }
    </script>
    <script src="../assets/js/hamburger_menu.js"></script>
</body>
</html>