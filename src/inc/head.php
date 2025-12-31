<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <!-- Título dinámico -->
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Seminario PITI 2025 - Universidad Tecnológica del Chocó'; ?></title>
    
    <!-- Meta Description para SEO -->
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Seminario PITI 2025 de la Universidad Tecnológica del Chocó. Formación integral y desarrollo tecnológico para la región.'; ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo isset($pageTitle) ? $pageTitle : 'Seminario PITI 2025 - UTCH'; ?>">
    <meta property="og:description" content="<?php echo isset($pageDescription) ? $pageDescription : '📚 Universidad Tecnológica del Chocó - Programa de formación PITI 2025. Preinscripciones abiertas.'; ?>">
    <meta property="og:url" content="<?php echo isset($pageUrl) ? $pageUrl : RUTA; ?>">
    <meta property="og:image" content="<?php echo isset($pageImage) ? $pageImage : RUTA . '/src/assets/img/og-image.jpg'; ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="UTCH PITI">
    <meta property="og:locale" content="es_CO">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo isset($pageTitle) ? $pageTitle : 'Seminario PITI 2025 - UTCH'; ?>">
    <meta name="twitter:description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Programa de formación PITI 2025 - Universidad Tecnológica del Chocó'; ?>">
    <meta name="twitter:image" content="<?php echo isset($pageImage) ? $pageImage : RUTA . '/src/assets/img/og-image.jpg'; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo RUTA; ?>src/assets/img/favicon.ico">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo isset($pageUrl) ? $pageUrl : RUTA; ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?php echo RUTA; ?>src/assets/css/main.css">
</head>
<body>