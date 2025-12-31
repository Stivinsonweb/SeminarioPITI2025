<?php
session_start();
include '../../ruta.php';
include '../../SQL/db_connect.php';
include '../inc/head.php';

// Manejar descarga de presentación
if (isset($_GET['descargar']) && !empty($_GET['descargar'])) {
    $id = (int)$_GET['descargar'];
    
    try {
        $stmt = $pdo->prepare("SELECT Nombre, Apellido, Ruta_presentacion FROM ponente WHERE ID = ?");
        $stmt->execute([$id]);
        $ponente = $stmt->fetch();
        
        if ($ponente && !empty($ponente['Ruta_presentacion'])) {
            $archivo = '../' . $ponente['Ruta_presentacion'];
            
            if (file_exists($archivo)) {
                $extension = pathinfo($archivo, PATHINFO_EXTENSION);
                $nombre_descarga = 'Presentacion_' . $ponente['Nombre'] . '_' . $ponente['Apellido'] . '.' . $extension;
                
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $nombre_descarga . '"');
                header('Content-Length: ' . filesize($archivo));
                header('Cache-Control: no-cache, must-revalidate');
                
                readfile($archivo);
                exit;
            } else {
                $error = "El archivo de presentación no existe en el servidor.";
            }
        } else {
            $error = "No se encontró la presentación solicitada.";
        }
    } catch (Exception $e) {
        $error = "Error al descargar: " . $e->getMessage();
    }
}

try {
    // Obtener ponentes con presentaciones
    $stmt = $pdo->query("
        SELECT ID, Nombre, Apellido, Email, Titulo_ponencia, Ruta_presentacion, Fecha_registro
        FROM ponente 
        WHERE Ruta_presentacion IS NOT NULL AND Ruta_presentacion != ''
        ORDER BY Fecha_registro DESC
    ");
    $ponentes_con_presentaciones = $stmt->fetchAll();
    
    // Obtener estadísticas
    $stmt_total = $pdo->query("SELECT COUNT(*) as total FROM ponente");
    $total_ponentes = $stmt_total->fetch()['total'];
    
    $stmt_con_archivo = $pdo->query("SELECT COUNT(*) as total FROM ponente WHERE Ruta_presentacion IS NOT NULL AND Ruta_presentacion != ''");
    $ponentes_con_archivo = $stmt_con_archivo->fetch()['total'];
    
} catch (Exception $e) {
    $error = "Error al cargar presentaciones: " . $e->getMessage();
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<title>Presentaciones - Dashboard</title>
<style>
/* Dashboard CSS */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f8f9fa;
}

.dashboard-container {
    display: flex;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 280px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    z-index: 1000;
}

.sidebar-header {
    padding: 2rem 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-header h4 {
    font-weight: 600;
    margin: 0;
}

.sidebar-nav {
    padding: 1rem 0;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.nav-link:hover {
    background: rgba(255,255,255,0.1);
    color: white;
    border-left-color: #ffc107;
}

.nav-link.active {
    background: rgba(255,255,255,0.15);
    color: white;
    border-left-color: #ffc107;
}

.nav-link i {
    margin-right: 0.75rem;
    width: 20px;
    text-align: center;
}

.sidebar-divider {
    height: 1px;
    background: rgba(255,255,255,0.1);
    margin: 1rem 0;
}

/* Main Content */
.main-content {
    flex: 1;
    margin-left: 280px;
    padding: 2rem;
    background-color: #f8f9fa;
    min-height: 100vh;
}

.header {
    margin-bottom: 2rem;
}

.header h1 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

/* Cards */
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}

.card-header {
    background: white;
    border-bottom: 1px solid #eee;
    border-radius: 15px 15px 0 0 !important;
    padding: 1.5rem;
}

.card-header h5 {
    margin: 0;
    color: #2c3e50;
    font-weight: 600;
}

.card-body {
    padding: 1.5rem;
}

/* Presentación Card */
.presentacion-card {
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.presentacion-card:hover {
    border-color: #667eea;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.1);
    transform: translateY(-2px);
}

.presentacion-title {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.presentacion-author {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.file-info {
    background: #f8f9fa;
    padding: 0.75rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

/* Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.6rem 1.2rem;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.btn-primary {
    background: linear-gradient(135deg, #3498db, #2980b9);
    border: none;
}

.btn-success {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    border: none;
}

.btn-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    border: none;
}

.btn-warning {
    background: linear-gradient(135deg, #f39c12, #e67e22);
    border: none;
}

/* Stat Cards */
.stat-mini {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.stat-mini h3 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    color: #2c3e50;
}

.stat-mini p {
    color: #6c757d;
    margin: 0;
    font-size: 0.9rem;
}

/* Alert */
.alert {
    border-radius: 10px;
    border: none;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .main-content {
        margin-left: 0;
        padding: 1rem;
    }
    
    .presentacion-card {
        padding: 1rem;
    }
}
</style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h4><i class="bi bi-graph-up"></i> Dashboard</h4>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-link">
                    <i class="bi bi-house"></i> Inicio
                </a>
                <a href="participantes.php" class="nav-link">
                    <i class="bi bi-people"></i> Participantes
                </a>
                <a href="ponentes.php" class="nav-link">
                    <i class="bi bi-person-badge"></i> Ponentes
                </a>
                <a href="presentaciones.php" class="nav-link active">
                    <i class="bi bi-file-earmark-slides"></i> Presentaciones
                </a>
                <a href="exportar.php" class="nav-link">
                    <i class="bi bi-download"></i> Exportar Datos
                </a>
                <div class="sidebar-divider"></div>
                <a href="../pages/index.php" class="nav-link">
                    <i class="bi bi-arrow-left"></i> Volver al Sitio
                </a>
            </nav>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Gestión de Presentaciones</h1>
                <p class="text-muted">Administrar archivos de presentaciones de ponentes</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stat-mini">
                        <h3><?php echo $total_ponentes; ?></h3>
                        <p>Total Ponentes</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-mini">
                        <h3><?php echo $ponentes_con_archivo; ?></h3>
                        <p>Con Presentación</p>
                    </div>
                </div>
            </div>
            
            <!-- Lista de presentaciones -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-file-earmark-slides"></i> Presentaciones Disponibles</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($ponentes_con_presentaciones)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-file-earmark-slides" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="mt-2 text-muted">No hay presentaciones subidas aún.</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($ponentes_con_presentaciones as $ponente): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="presentacion-card">
                                        <div class="presentacion-title">
                                            <?php echo htmlspecialchars($ponente['Titulo_ponencia']); ?>
                                        </div>
                                        <div class="presentacion-author">
                                            <i class="bi bi-person"></i>
                                            <?php echo htmlspecialchars($ponente['Nombre'] . ' ' . $ponente['Apellido']); ?>
                                        </div>
                                        
                                        <?php if (!empty($ponente['Ruta_presentacion'])): ?>
                                            <div class="file-info">
                                                <i class="bi bi-file-earmark"></i>
                                                Archivo: <?php echo basename($ponente['Ruta_presentacion']); ?>
                                                <br>
                                                <small class="text-muted">
                                                    Subido: <?php echo date('d/m/Y', strtotime($ponente['Fecha_registro'])); ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex gap-2">
                                            <a href="?descargar=<?php echo $ponente['ID']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="bi bi-download"></i> Descargar
                                            </a>
                                            <button class="btn btn-outline-info btn-sm" 
                                                    onclick="verInfo(<?php echo $ponente['ID']; ?>)">
                                                <i class="bi bi-info-circle"></i> Info
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Acciones adicionales -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="bi bi-tools"></i> Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="ponentes.php" class="btn btn-info">
                            <i class="bi bi-person-badge"></i> Ver Todos los Ponentes
                        </a>
                        <button onclick="descargarTodas()" class="btn btn-warning">
                            <i class="bi bi-download"></i> Descargar Todas (ZIP)
                        </button>
                        <a href="exportar.php?tipo=ponentes" class="btn btn-success">
                            <i class="bi bi-file-earmark-excel"></i> Exportar Lista Ponentes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verInfo(id) {
            // Mostrar información adicional del ponente
            alert('Información del ponente ID: ' + id);
        }
        
        function descargarTodas() {
            // Función para descargar todas las presentaciones
            alert('Funcionalidad para descargar todas las presentaciones en ZIP');
        }
    </script>
</body>
</html>