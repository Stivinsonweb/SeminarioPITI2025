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
$es_superadmin = ($current_user['rol_id'] == 1); // Asumiendo que rol_id 1 es SuperAdmin

try {
    // Obtener participantes que NO han sido confirmados por NINGÚN representante
    $stmt_participantes = $pdo->query("
        SELECT p.ID, p.Nombre, p.Apellido, p.Numero_de_documento, 
               td.Tipo_documento, p.Fecha_de_inscripcion 
        FROM participante p 
        INNER JOIN tipo_documento td ON p.Id_Tipo_documento = td.ID 
        WHERE p.ID NOT IN (
            SELECT Id_Participante 
            FROM confirmacion_de_participacion
        )
        ORDER BY p.Nombre ASC
    ");
    $participantes = $stmt_participantes->fetchAll();
    
    // Query mejorada para SuperAdmin - con detalles completos
    if ($es_superadmin) {
        $stmt_confirmados = $pdo->query("
            SELECT c.ID, c.Id_Participante, c.Asistion, c.Fecha_de_confirmacion,
                   p.Nombre, p.Apellido, p.Numero_de_documento,
                   s.Semestre, u.Usuario, u.ID as Id_usuario, td.Tipo_documento
            FROM confirmacion_de_participacion c
            INNER JOIN participante p ON c.Id_Participante = p.ID
            INNER JOIN semestre s ON c.Id_Semestre = s.ID
            INNER JOIN usuario u ON c.Id_usuario = u.ID
            INNER JOIN tipo_documento td ON p.Id_Tipo_documento = td.ID
            ORDER BY u.Usuario ASC, c.Fecha_de_confirmacion DESC
        ");
    } else {
        $stmt_confirmados = $pdo->query("
            SELECT c.ID, c.Id_Participante, c.Asistion, c.Fecha_de_confirmacion,
                   p.Nombre, p.Apellido, p.Numero_de_documento,
                   s.Semestre, u.Usuario
            FROM confirmacion_de_participacion c
            INNER JOIN participante p ON c.Id_Participante = p.ID
            INNER JOIN semestre s ON c.Id_Semestre = s.ID
            INNER JOIN usuario u ON c.Id_usuario = u.ID
            ORDER BY c.Fecha_de_confirmacion DESC
        ");
    }
    $confirmados = $stmt_confirmados->fetchAll();
    
    $stmt_semestres = $pdo->query("SELECT ID, Semestre FROM semestre ORDER BY ID");
    $semestres = $stmt_semestres->fetchAll();
    
    $stmt_verificar = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM confirmacion_de_participacion 
        WHERE Id_usuario = ?
    ");
    $stmt_verificar->execute([$current_user['id']]);
    $ya_confirmo = $stmt_verificar->fetch()['total'] > 0;
    
    // Obtener MIS confirmaciones (del usuario actual)
    if ($ya_confirmo && !$es_superadmin) {
        $stmt_mis_confirmaciones = $pdo->prepare("
            SELECT c.ID as confirmacion_id, c.Id_Participante, c.Asistion, c.Fecha_de_confirmacion,
                   p.Nombre, p.Apellido, p.Numero_de_documento,
                   s.Semestre, td.Tipo_documento
            FROM confirmacion_de_participacion c
            INNER JOIN participante p ON c.Id_Participante = p.ID
            INNER JOIN semestre s ON c.Id_Semestre = s.ID
            INNER JOIN tipo_documento td ON p.Id_Tipo_documento = td.ID
            WHERE c.Id_usuario = ?
            ORDER BY p.Nombre ASC, p.Apellido ASC
        ");
        $stmt_mis_confirmaciones->execute([$current_user['id']]);
        $mis_confirmaciones = $stmt_mis_confirmaciones->fetchAll();
    } else {
        $mis_confirmaciones = [];
    }
    
    // Estadísticas para SuperAdmin
    if ($es_superadmin) {
        $stmt_stats = $pdo->query("
            SELECT 
                u.Usuario as representante,
                COUNT(c.ID) as total_confirmados,
                s.Semestre,
                MAX(c.Fecha_de_confirmacion) as ultima_confirmacion
            FROM confirmacion_de_participacion c
            INNER JOIN usuario u ON c.Id_usuario = u.ID
            INNER JOIN semestre s ON c.Id_Semestre = s.ID
            GROUP BY u.ID, u.Usuario, s.Semestre
            ORDER BY u.Usuario ASC, s.Semestre ASC
        ");
        $estadisticas = $stmt_stats->fetchAll();
    }
    
} catch (PDOException $e) {
    $participantes = [];
    $confirmados = [];
    $semestres = [];
    $ya_confirmo = false;
    $estadisticas = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Participantes - Seminario PITI 2025</title>
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
        
        .alert-info {
            background: #dbeafe;
            border: 1px solid #3b82f6;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            color: #1e40af;
        }
        
        .alert-success {
            background: #d1fae5;
            border: 1px solid #10b981;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            color: #065f46;
        }

        /* Sección de Mis Confirmaciones */
        .mis-confirmaciones-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #10b981;
        }

        .section-header-mis {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .section-header-mis h2 {
            font-size: 1.5rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
        }

        .section-header-mis h2 i {
            color: #10b981;
            font-size: 1.75rem;
        }

        .badge-count-mis {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }

        .confirmaciones-tabla-container {
            overflow-x: auto;
            border-radius: 8px;
        }

        .mis-confirmaciones-table {
            width: 100%;
            border-collapse: collapse;
        }

        .mis-confirmaciones-table thead {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .mis-confirmaciones-table th {
            padding: 1rem;
            text-align: left;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
        }

        .mis-confirmaciones-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: all 0.3s;
        }

        .mis-confirmaciones-table tbody tr:hover {
            background: #f0fdf4;
            transform: scale(1.005);
        }

        .mis-confirmaciones-table td {
            padding: 1rem;
            color: #1e293b;
            font-size: 0.95rem;
        }

        .mis-confirmaciones-table td:first-child {
            font-weight: 700;
            color: #10b981;
            font-size: 1.1rem;
        }

        .badge-semestre-small {
            background: #dbeafe;
            color: #1e40af;
            padding: 0.35rem 0.85rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .btn-eliminar-conf {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
        }

        .btn-eliminar-conf:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .btn-eliminar-conf i {
            margin-right: 0.35rem;
        }

        /* SuperAdmin Panel */
        .superadmin-panel {
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            color: white;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
        }

        .superadmin-panel h3 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: rgba(255,255,255,0.2);
            padding: 1rem;
            border-radius: 8px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .stat-card h4 {
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            opacity: 0.95;
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
        }

        .representantes-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .representantes-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .representantes-table thead {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        }

        .representantes-table th {
            background: transparent;
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
        }

        .representantes-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: all 0.3s;
        }

        .representantes-table tbody tr:hover {
            background: #f8fafc;
            transform: scale(1.01);
        }

        .representantes-table td {
            padding: 1rem;
            color: #1e293b;
            border: none;
            font-size: 0.95rem;
        }

        .representantes-table td:first-child {
            font-weight: 600;
            color: #7c3aed;
        }

        .btn-ver-detalle {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }

        .btn-ver-detalle:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .summary-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            display: none;
        }

        .summary-box.active {
            display: block;
        }

        .summary-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .summary-header h3 {
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .summary-total {
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .summary-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
        }

        .summary-list {
            max-height: 200px;
            overflow-y: auto;
            background: rgba(0,0,0,0.2);
            border-radius: 8px;
            padding: 1rem;
        }

        .summary-list::-webkit-scrollbar {
            width: 8px;
        }

        .summary-list::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
        }

        .summary-list::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 4px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            background: rgba(255,255,255,0.1);
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .summary-item:last-child {
            margin-bottom: 0;
        }

        .summary-empty {
            text-align: center;
            padding: 2rem 1rem;
            color: rgba(255,255,255,0.7);
            font-style: italic;
        }
        
        .section-confirmados {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
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
        
        .confirmados-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.25rem;
            margin-top: 1rem;
        }
        
        .confirmado-card {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #10b981;
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .confirmado-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #10b981 0%, #059669 100%);
        }

        .confirmado-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.2);
            border-color: #059669;
        }
        
        .confirmado-card h4 {
            color: #065f46;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
        }

        .confirmado-card h4 i {
            font-size: 1.3rem;
        }
        
        .confirmado-card p {
            font-size: 0.9rem;
            color: #047857;
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .confirmado-card p i {
            color: #10b981;
        }

        .confirmado-card .card-footer {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(16, 185, 129, 0.2);
            font-size: 0.8rem;
            color: #059669;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .confirmado-card .card-footer i {
            font-size: 0.9rem;
        }
        
        .search-container {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .search-box {
            position: relative;
            max-width: 400px;
        }
        
        .search-box input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 1.2rem;
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
        
        .btn-action {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-confirmar {
            background: #10b981;
            color: white;
        }
        
        .btn-confirmar:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-confirmado {
            background: #10b981 !important;
            cursor: default !important;
            opacity: 0.9;
        }

        .btn-confirmado:hover {
            background: #10b981 !important;
            transform: none !important;
        }

        .btn-deshacer {
            background: #f59e0b;
            color: white;
            margin-left: 0.5rem;
        }

        .btn-deshacer:hover {
            background: #d97706;
        }
        
        .btn-rechazar {
            background: #ef4444;
            color: white;
        }
        
        .btn-rechazar:hover {
            background: #dc2626;
        }
        
        .btn-enviar {
            background: #8b5cf6;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 2rem auto 0;
        }
        
        .btn-enviar:hover {
            background: #7c3aed;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }
        
        .temp-confirmado {
            background: #fef3c7 !important;
        }

        .row-hidden {
            display: none !important;
        }
        
        .no-data {
            text-align: center;
            padding: 3rem 1rem;
            color: #64748b;
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
            
            .confirmados-grid, .stats-grid {
                grid-template-columns: 1fr;
            }

            .summary-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }

        /* Modal styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.75);
            backdrop-filter: blur(4px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            max-width: 900px;
            width: 95%;
            max-height: 85vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            animation: slideUp 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        @keyframes slideUp {
            from { 
                transform: translateY(50px);
                opacity: 0;
            }
            to { 
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            padding: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-close {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.3s;
            line-height: 1;
        }

        .modal-close:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 0;
            overflow-y: auto;
            flex: 1;
        }

        .modal-body::-webkit-scrollbar {
            width: 10px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 5px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .modal-stats {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 1.5rem;
            display: flex;
            gap: 1rem;
            border-bottom: 2px solid #3b82f6;
        }

        .modal-stat {
            flex: 1;
            text-align: center;
            padding: 1rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .modal-stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e40af;
            display: block;
        }

        .modal-stat-label {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .participantes-list {
            padding: 1.5rem;
        }

        .participante-detail {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem;
            margin-bottom: 0.75rem;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .participante-detail:hover {
            background: #f8fafc;
            border-color: #3b82f6;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }

        .participante-detail:last-child {
            margin-bottom: 0;
        }

        .participante-number {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .participante-info {
            flex: 1;
        }

        .participante-info strong {
            color: #1e293b;
            display: block;
            margin-bottom: 0.5rem;
            font-size: 1.05rem;
        }

        .participante-meta {
            display: flex;
            gap: 1.5rem;
            font-size: 0.9rem;
            color: #64748b;
            flex-wrap: wrap;
        }

        .participante-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .participante-meta-item i {
            color: #3b82f6;
        }

        .badge-semestre {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
        }

        .participante-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            align-items: flex-end;
        }

        .modal-empty {
            text-align: center;
            padding: 3rem;
            color: #94a3b8;
        }

        .modal-empty i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
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
            <?php if ($current_user['rol_id'] != 3): ?>
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
            <?php endif; ?>
            
            <a href="confirmacion_participantes.php" class="menu-item active">
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
                <h1><i class="bi bi-check-circle-fill"></i> Confirmación de Asistencia</h1>
                <p>Confirma la asistencia de los participantes a tu cargo</p>
            </div>
        </div>

        <?php if ($es_superadmin): ?>
        <!-- Panel SuperAdmin -->
        <div class="superadmin-panel">
            <h3><i class="bi bi-shield-fill-check"></i> Panel de Supervisión - SuperAdmin</h3>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Total Confirmados</h4>
                    <div class="stat-value"><?php echo count($confirmados); ?></div>
                </div>
                <div class="stat-card">
                    <h4>Representantes Activos</h4>
                    <div class="stat-value"><?php echo count(array_unique(array_column($estadisticas, 'representante'))); ?></div>
                </div>
                <div class="stat-card">
                    <h4>Total Participantes</h4>
                    <div class="stat-value"><?php echo count($participantes); ?></div>
                </div>
            </div>

            <h4 style="margin-bottom: 1rem; font-size: 1.1rem;">
                <i class="bi bi-people-fill"></i> Confirmaciones por Representante
            </h4>
            <div class="representantes-table">
                <table>
                    <thead>
                        <tr>
                            <th>Representante</th>
                            <th>Semestre</th>
                            <th>Confirmados</th>
                            <th>Última Confirmación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($estadisticas)): ?>
                            <?php foreach ($estadisticas as $stat): ?>
                            <tr>
                                <td><i class="bi bi-person-badge"></i> <?php echo htmlspecialchars($stat['representante']); ?></td>
                                <td><?php echo htmlspecialchars($stat['Semestre']); ?></td>
                                <td><strong><?php echo $stat['total_confirmados']; ?></strong> participantes</td>
                                <td><?php echo date('d/m/Y H:i', strtotime($stat['ultima_confirmacion'])); ?></td>
                                <td>
                                    <button class="btn-ver-detalle" onclick="verDetalleRepresentante('<?php echo htmlspecialchars($stat['representante']); ?>', '<?php echo $stat['Semestre']; ?>')">
                                        <i class="bi bi-eye"></i> Ver Detalle
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem; color: #64748b;">
                                    No hay confirmaciones registradas aún
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($ya_confirmo && !$es_superadmin): ?>
        <div class="mis-confirmaciones-section">
            <div class="section-header-mis">
                <h2><i class="bi bi-check-circle-fill"></i> Mis Confirmaciones</h2>
                <span class="badge-count-mis"><?php echo count($mis_confirmaciones); ?> participantes</span>
            </div>
            
            <div class="confirmaciones-tabla-container">
                <?php if (!empty($mis_confirmaciones)): ?>
                <table class="mis-confirmaciones-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre Completo</th>
                            <th>Tipo Doc.</th>
                            <th>Número</th>
                            <th>Semestre</th>
                            <th>Fecha Confirmación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mis_confirmaciones as $index => $conf): ?>
                        <tr id="conf-row-<?php echo $conf['confirmacion_id']; ?>">
                            <td><?php echo $index + 1; ?></td>
                            <td><strong><?php echo htmlspecialchars($conf['Nombre'] . ' ' . $conf['Apellido']); ?></strong></td>
                            <td><?php echo htmlspecialchars($conf['Tipo_documento']); ?></td>
                            <td><?php echo htmlspecialchars($conf['Numero_de_documento']); ?></td>
                            <td><span class="badge-semestre-small"><?php echo htmlspecialchars($conf['Semestre']); ?></span></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($conf['Fecha_de_confirmacion'])); ?></td>
                            <td>
                                <button class="btn-action btn-eliminar-conf" onclick="eliminarConfirmacion(<?php echo $conf['confirmacion_id']; ?>, '<?php echo htmlspecialchars(addslashes($conf['Nombre'] . ' ' . $conf['Apellido'])); ?>')">
                                    <i class="bi bi-trash-fill"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="text-align: center; padding: 2rem; color: #64748b;">No tienes confirmaciones registradas</p>
                <?php endif; ?>
            </div>
        </div>
        <?php elseif (!$es_superadmin): ?>
        <div class="alert-info">
            <i class="bi bi-info-circle-fill"></i>
            <strong>Instrucciones:</strong> Busca y confirma la asistencia de los participantes de tu grupo. Al finalizar, haz clic en "Enviar Confirmaciones" para guardar.
        </div>

        <!-- Caja de resumen en tiempo real -->
        <div class="summary-box" id="summaryBox">
            <div class="summary-header">
                <h3><i class="bi bi-clipboard-check"></i> Resumen de Confirmaciones</h3>
                <div class="summary-total">
                    Total: <span id="summaryTotal">0</span>
                </div>
            </div>
            
            <div class="summary-user">
                <i class="bi bi-person-circle"></i>
                <div>
                    <strong>Confirmando:</strong> <?php echo htmlspecialchars($current_user['usuario']); ?> 
                    <span style="opacity: 0.8; font-size: 0.85rem;">(<?php echo htmlspecialchars($current_user['rol_nombre']); ?>)</span>
                </div>
            </div>

            <div class="summary-list" id="summaryList">
                <div class="summary-empty">
                    <i class="bi bi-inbox"></i><br>
                    Aún no has agregado participantes
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="section-confirmados">
            <div class="section-header">
                <div class="section-title">
                    <i class="bi bi-check-all"></i>
                    Participantes Confirmados
                    <span class="count-badge" id="countConfirmados"><?php echo count($confirmados); ?></span>
                </div>
            </div>
            
            <?php if (!empty($confirmados)): ?>
            <div class="confirmados-grid">
                <?php 
                $grouped = [];
                foreach ($confirmados as $conf) {
                    $grouped[$conf['Semestre']][] = $conf;
                }
                foreach ($grouped as $semestre => $participantes_sem): 
                ?>
                <div class="confirmado-card">
                    <h4><i class="bi bi-folder-fill"></i> Semestre <?php echo $semestre; ?></h4>
                    <p>
                        <i class="bi bi-people-fill"></i>
                        <strong><?php echo count($participantes_sem); ?></strong> participantes confirmados
                    </p>
                    <div class="card-footer">
                        <i class="bi bi-person-check-fill"></i>
                        Confirmado por: <strong><?php echo htmlspecialchars($participantes_sem[0]['Usuario']); ?></strong>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="text-align: center; color: #64748b; padding: 2rem;">Aún no hay confirmaciones registradas</p>
            <?php endif; ?>
        </div>

        <?php if (!$ya_confirmo && !$es_superadmin): ?>
        <div class="search-container">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #475569;">
                <i class="bi bi-search"></i> Buscar Participante
            </label>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar por número de documento..." onkeyup="filterTable()">
            </div>
        </div>

        <div class="data-container">
            <div class="table-container">
                <?php if (!empty($participantes)): ?>
                <table id="participantesTable">
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Documento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php foreach ($participantes as $participante): ?>
                        <tr data-id="<?php echo $participante['ID']; ?>" data-nombre="<?php echo htmlspecialchars($participante['Nombre'] . ' ' . $participante['Apellido']); ?>" data-documento="<?php echo htmlspecialchars($participante['Numero_de_documento']); ?>">
                            <td><?php echo htmlspecialchars($participante['Nombre'] . ' ' . $participante['Apellido']); ?></td>
                            <td><?php echo htmlspecialchars($participante['Numero_de_documento']); ?></td>
                            <td>
                                <button class="btn-action btn-confirmar" onclick="confirmarParticipante(<?php echo $participante['ID']; ?>, this)">
                                    <i class="bi bi-check-lg"></i> Confirmar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <button class="btn-enviar" id="btnEnviar" style="display: none;" onclick="enviarConfirmaciones()">
                    <i class="bi bi-send-fill"></i>
                    Enviar Confirmaciones a BD
                </button>
                <?php else: ?>
                <div class="no-data">
                    <i class="bi bi-inbox"></i>
                    <p><strong>No hay participantes registrados</strong></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Modal para detalle de representante -->
    <div class="modal-overlay" id="modalDetalle">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle"><i class="bi bi-list-check"></i> Detalle de Confirmaciones</h3>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Se llenará dinámicamente -->
            </div>
        </div>
    </div>

    <script>
        let confirmacionesTemp = [];
        let semestreSeleccionado = null;
        let participantesData = {};

        // Datos de confirmaciones para SuperAdmin
        const confirmacionesData = <?php echo json_encode($confirmados); ?>;

        function actualizarResumen() {
            const summaryBox = document.getElementById('summaryBox');
            const summaryTotal = document.getElementById('summaryTotal');
            const summaryList = document.getElementById('summaryList');

            if (!summaryBox || !summaryTotal || !summaryList) return;

            summaryTotal.textContent = confirmacionesTemp.length;

            if (confirmacionesTemp.length > 0) {
                summaryBox.classList.add('active');
                
                let html = '';
                confirmacionesTemp.forEach((conf, index) => {
                    const participante = participantesData[conf.id_participante];
                    html += `
                        <div class="summary-item">
                            <span>
                                <i class="bi bi-person-check-fill"></i>
                                ${participante.nombre} - ${participante.documento}
                            </span>
                        </div>
                    `;
                });
                
                summaryList.innerHTML = html;
            } else {
                summaryBox.classList.remove('active');
                summaryList.innerHTML = `
                    <div class="summary-empty">
                        <i class="bi bi-inbox"></i><br>
                        Aún no has agregado participantes
                    </div>
                `;
            }
        }

        function filterTable() {
            const input = document.getElementById('searchInput');
            if (!input) return;
            
            const filter = input.value.toUpperCase();
            const table = document.getElementById('participantesTable');
            if (!table) return;
            
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                // No filtrar filas que ya están ocultas por confirmación
                if (tr[i].classList.contains('row-hidden')) {
                    continue;
                }

                const td = tr[i].getElementsByTagName('td')[1];
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

        async function confirmarParticipante(idParticipante, btn) {
            console.log('Confirmar participante:', idParticipante);
            
            // Verificar si ya está confirmado
            const yaConfirmado = confirmacionesTemp.some(c => c.id_participante === idParticipante);
            if (yaConfirmado) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Ya confirmado',
                    text: 'Este participante ya está en tu lista de confirmaciones',
                    timer: 2000
                });
                return;
            }

            // Pedir semestre si no se ha seleccionado
            if (!semestreSeleccionado) {
                const { value: semestre } = await Swal.fire({
                    title: 'Selecciona el rol del participante',
                    input: 'select',
                    inputOptions: {
                        <?php foreach ($semestres as $sem): ?>
                        '<?php echo $sem['ID']; ?>': '<?php echo $sem['Semestre']; ?>',
                        <?php endforeach; ?>
                    },
                    inputPlaceholder: 'Selecciona el rol',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Debes seleccionar un semestre';
                        }
                    }
                });

                if (semestre) {
                    semestreSeleccionado = semestre;
                    console.log('Semestre seleccionado:', semestre);
                } else {
                    return;
                }
            }

            // Obtener datos de la fila
            const row = btn.closest('tr');
            const nombre = row.getAttribute('data-nombre');
            const documento = row.getAttribute('data-documento');

            console.log('Datos:', {nombre, documento});

            // Guardar datos del participante
            participantesData[idParticipante] = {
                nombre: nombre,
                documento: documento
            };

            // Agregar a confirmaciones temporales
            confirmacionesTemp.push({
                id_participante: idParticipante,
                id_semestre: semestreSeleccionado
            });

            console.log('Confirmaciones temp:', confirmacionesTemp);

            // Cambiar el botón a "Confirmado"
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Confirmado';
            btn.disabled = true;
            btn.classList.remove('btn-confirmar');
            btn.classList.add('btn-confirmado');
            btn.style.background = '#10b981';
            btn.style.cursor = 'default';

            // Agregar botón deshacer
            const btnDeshacer = document.createElement('button');
            btnDeshacer.className = 'btn-action btn-deshacer';
            btnDeshacer.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i> Deshacer';
            btnDeshacer.type = 'button';
            btnDeshacer.onclick = function(e) { 
                e.preventDefault();
                deshacerConfirmacion(idParticipante, row); 
            };
            btn.parentElement.appendChild(btnDeshacer);

            // Marcar fila como confirmada temporalmente
            row.classList.add('temp-confirmado');

            // Ocultar la fila
            setTimeout(() => {
                row.classList.add('row-hidden');
            }, 500);

            // Mostrar botón enviar
            const btnEnviar = document.getElementById('btnEnviar');
            if (btnEnviar) {
                btnEnviar.style.display = 'flex';
            }

            // Actualizar resumen
            actualizarResumen();

            // Notificación
            Swal.fire({
                icon: 'success',
                title: 'Participante Agregado',
                html: `
                    <div style="text-align: left; margin-top: 1rem;">
                        <p style="margin-bottom: 0.5rem;"><strong>Total confirmados:</strong> ${confirmacionesTemp.length}</p>
                        <p style="margin-bottom: 0.5rem; color: #10b981;">✓ ${nombre}</p>
                    </div>
                `,
                timer: 2000,
                showConfirmButton: false
            });
        }

        function deshacerConfirmacion(idParticipante, row) {
            Swal.fire({
                title: '¿Deshacer confirmación?',
                text: 'Este participante volverá a la lista disponible',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, deshacer',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#f59e0b'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Eliminar de confirmaciones temporales
                    confirmacionesTemp = confirmacionesTemp.filter(c => c.id_participante !== idParticipante);

                    // Quitar clases de confirmado
                    row.classList.remove('temp-confirmado');
                    row.classList.remove('row-hidden');

                    // Restaurar botón original
                    const td = row.querySelector('td:last-child');
                    td.innerHTML = `
                        <button class="btn-action btn-confirmar" onclick="confirmarParticipante(${idParticipante}, this)">
                            <i class="bi bi-check-lg"></i> Confirmar
                        </button>
                    `;

                    // Actualizar resumen
                    actualizarResumen();

                    // Ocultar botón enviar si no hay confirmaciones
                    if (confirmacionesTemp.length === 0) {
                        const btnEnviar = document.getElementById('btnEnviar');
                        if (btnEnviar) {
                            btnEnviar.style.display = 'none';
                        }
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Deshecho',
                        text: 'Participante eliminado de la lista',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }

        async function enviarConfirmaciones() {
            if (confirmacionesTemp.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin confirmaciones',
                    text: 'No has confirmado ningún participante'
                });
                return;
            }

            let listaHTML = '<div style="text-align: left; max-height: 300px; overflow-y: auto; margin: 1rem 0;">';
            listaHTML += '<p style="margin-bottom: 1rem; font-weight: 600;">Participantes a confirmar:</p>';
            confirmacionesTemp.forEach((conf, index) => {
                const participante = participantesData[conf.id_participante];
                listaHTML += `<p style="margin: 0.5rem 0; padding: 0.5rem; background: #f0fdf4; border-radius: 4px;">
                    ${index + 1}. ${participante.nombre} - ${participante.documento}
                </p>`;
            });
            listaHTML += '</div>';

            const result = await Swal.fire({
                title: `¿Enviar ${confirmacionesTemp.length} confirmaciones?`,
                html: listaHTML,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar',
                width: '600px'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('../../SQL/guardar_confirmaciones.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            confirmaciones: confirmacionesTemp,
                            id_usuario: <?php echo $current_user['id']; ?>
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Confirmaciones guardadas!',
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
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo guardar las confirmaciones'
                    });
                }
            }
        }

        // Funciones para SuperAdmin
        function verDetalleRepresentante(representante, semestre) {
            const participantes = confirmacionesData.filter(c => 
                c.Usuario === representante && c.Semestre === semestre
            );

            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');

            if (!modalTitle || !modalBody) return;

            modalTitle.innerHTML = `<i class="bi bi-person-badge-fill"></i> ${representante} - Semestre ${semestre}`;

            // Crear estadísticas
            let statsHTML = `
                <div class="modal-stats">
                    <div class="modal-stat">
                        <span class="modal-stat-value">${participantes.length}</span>
                        <span class="modal-stat-label">Total Participantes</span>
                    </div>
                    <div class="modal-stat">
                        <span class="modal-stat-value">${semestre}</span>
                        <span class="modal-stat-label">Semestre</span>
                    </div>
                    <div class="modal-stat">
                        <span class="modal-stat-value">${representante}</span>
                        <span class="modal-stat-label">Representante</span>
                    </div>
                </div>
            `;

            // Crear lista de participantes
            let listHTML = '<div class="participantes-list">';
            
            if (participantes.length > 0) {
                participantes.forEach((p, index) => {
                    const fechaObj = new Date(p.Fecha_de_confirmacion);
                    const fechaFormateada = fechaObj.toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                    const horaFormateada = fechaObj.toLocaleTimeString('es-ES', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    listHTML += `
                        <div class="participante-detail">
                            <div class="participante-number">${index + 1}</div>
                            <div class="participante-info">
                                <strong>${p.Nombre} ${p.Apellido}</strong>
                                <div class="participante-meta">
                                    <div class="participante-meta-item">
                                        <i class="bi bi-credit-card-fill"></i>
                                        <span>${p.Tipo_documento || 'N/A'}: ${p.Numero_de_documento}</span>
                                    </div>
                                    <div class="participante-meta-item">
                                        <i class="bi bi-calendar-check-fill"></i>
                                        <span>${fechaFormateada} a las ${horaFormateada}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="participante-actions">
                                <span class="badge-semestre">Semestre ${p.Semestre}</span>
                            </div>
                        </div>
                    `;
                });
            } else {
                listHTML += `
                    <div class="modal-empty">
                        <i class="bi bi-inbox"></i>
                        <p><strong>No hay participantes</strong></p>
                        <p>No se encontraron participantes para este representante y semestre</p>
                    </div>
                `;
            }
            
            listHTML += '</div>';

            modalBody.innerHTML = statsHTML + listHTML;
            
            const modal = document.getElementById('modalDetalle');
            if (modal) {
                modal.classList.add('active');
                // Bloquear scroll del body
                document.body.style.overflow = 'hidden';
            }
        }

        function cerrarModal() {
            const modal = document.getElementById('modalDetalle');
            if (modal) {
                modal.classList.remove('active');
                // Restaurar scroll del body
                document.body.style.overflow = '';
            }
        }

        // Cerrar modal con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModal();
            }
        });

        // Cerrar modal al hacer clic fuera
        document.addEventListener('DOMContentLoaded', function() {
            const modalDetalle = document.getElementById('modalDetalle');
            if (modalDetalle) {
                modalDetalle.addEventListener('click', function(e) {
                    if (e.target === this) {
                        cerrarModal();
                    }
                });
            }
        });

        // Función para eliminar confirmación ya enviada
        async function eliminarConfirmacion(confirmacionId, nombreParticipante) {
            const result = await Swal.fire({
                title: '¿Eliminar confirmación?',
                html: `<p>¿Estás seguro de eliminar la confirmación de:</p><p style="font-weight: 600; color: #dc2626;">${nombreParticipante}</p><p style="font-size: 0.9rem; color: #64748b; margin-top: 0.5rem;">Esta acción devolverá el participante a la lista disponible.</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('../../SQL/eliminar_confirmacion.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            confirmacion_id: confirmacionId
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Eliminar fila de la tabla con animación
                        const row = document.getElementById('conf-row-' + confirmacionId);
                        if (row) {
                            row.style.transition = 'all 0.3s';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(-20px)';
                            
                            setTimeout(() => {
                                row.remove();
                                
                                // Actualizar contador
                                const badge = document.querySelector('.badge-count-mis');
                                if (badge) {
                                    const currentCount = parseInt(badge.textContent);
                                    badge.textContent = (currentCount - 1) + ' participantes';
                                }

                                // Recargar si no quedan más
                                const remainingRows = document.querySelectorAll('.mis-confirmaciones-table tbody tr').length;
                                if (remainingRows === 0) {
                                    location.reload();
                                }
                            }, 300);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: 'La confirmación ha sido eliminada correctamente',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'No se pudo eliminar la confirmación'
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo eliminar la confirmación. Verifica tu conexión.'
                    });
                }
            }
        }
    </script>
    <script src="../assets/js/hamburger_menu.js"></script>
</body>
</html>
