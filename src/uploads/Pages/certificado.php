<?php
include '../../ruta.php';
include '../inc/head.php';
include '../inc/header.php';
include '../../SQL/db_connect.php';

try {
    $stmt = $pdo->prepare("SELECT Id_Habilitar_Certificado FROM usuario WHERE Usuario = 'Admin' LIMIT 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $certificadosHabilitados = false;
    if ($config && isset($config['Id_Habilitar_Certificado'])) {
        $certificadosHabilitados = ($config['Id_Habilitar_Certificado'] == 1 || $config['Id_Habilitar_Certificado'] == '1');
    }
} catch (PDOException $e) {
    $certificadosHabilitados = false;
    error_log("Error consultando estado de certificados: " . $e->getMessage());
}

$puede_descargar = $certificadosHabilitados;
?>

<section class="video-hero">
  <video autoplay muted loop class="video-background">
    <source src="<?php echo RUTA; ?>/src/assets/video/tecnologia.mp4" type="video/mp4">
    Tu navegador no soporta videos en HTML5.
  </video>

  <div class="video-overlay"></div>

  <div class="hero-content">
    <div class="container text-center">
      <h1 class="display-4 fw-bold text-white hero-main-title">Certificado de Seminario</h1>
      <p class="lead text-white">
        <?php if (!$puede_descargar): ?>
          Los certificados estarán disponibles cuando el administrador los habilite.
        <?php else: ?>
          Gracias por participar en nuestro seminario.
          Ingresa tu número de documento para generar tu certificado.
        <?php endif; ?>
      </p>
    </div>
  </div>
</section>

<section class="certificado-section">
  <div class="container">
    <h1 class="title">Certificado</h1>

    <div class="icono-certificado">
      <i class="fas fa-certificate"></i>
    </div>

    <div class="alert <?php echo $puede_descargar ? 'alert-success' : 'alert-warning'; ?> text-center mb-4" role="alert">
        <h4 class="mb-0">
            <?php if ($puede_descargar): ?>
                <i class="bi bi-check-circle me-2"></i>Certificados Disponibles
            <?php else: ?>
                <i class="bi bi-clock-history me-2"></i>Certificados No Disponibles
            <?php endif; ?>
        </h4>
    </div>

    <?php if (!$puede_descargar): ?>
        <div class="formulario-deshabilitado-certificado"></div>
    <?php endif; ?>

    <?php if (!$puede_descargar): ?>
    <div class="countdown-container">
      <div class="countdown-header">
        <h3>Los certificados aún no están disponibles</h3>
        <div class="event-info">
          <p><i class="fas fa-info-circle"></i> <strong>Información:</strong></p>
          <p>Los certificados serán habilitados por el administrador una vez finalice el evento.</p>
          <p>Por favor, vuelve a consultar más tarde.</p>
        </div>
      </div>
      
      <div class="status-message">
        <i class="fas fa-lock"></i>
        <span>Descarga bloqueada hasta que el administrador habilite los certificados</span>
      </div>
    </div>
    <?php endif; ?>

    <div class="certificado-content <?php echo !$puede_descargar ? 'disabled' : ''; ?>">
      <div class="imagen">
        <img src="<?php echo RUTA; ?>src/assets/img/certificado-1.jpg" alt="Certificado">
        <?php if (!$puede_descargar): ?>
        <div class="overlay-disabled">
          <i class="fas fa-lock"></i>
          <span>Disponible cuando el administrador lo habilite</span>
        </div>
        <?php endif; ?>
      </div>

      <div class="certificado-form">
        <form id="formCertificado" class="<?php echo !$puede_descargar ? 'form-deshabilitado' : ''; ?>">
          <fieldset <?php echo !$puede_descargar ? 'disabled' : ''; ?>>
            <input
              type="text"
              id="numeroDocumento"
              name="numeroDocumento"
              placeholder="Número de documento"
              <?php echo !$puede_descargar ? 'disabled' : 'required'; ?>>
            <button type="submit" <?php echo !$puede_descargar ? 'disabled' : ''; ?>>
              <?php if (!$puede_descargar): ?>
                <i class="fas fa-lock"></i> No Disponible
              <?php else: ?>
                Generar certificado
              <?php endif; ?>
            </button>
          </fieldset>
        </form>
        
        <?php if (!$puede_descargar): ?>
        <div class="info-box">
          <i class="fas fa-info-circle"></i>
          <p>El formulario se habilitará automáticamente cuando el administrador active los certificados.</p>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php include '../inc/footer.php'; ?>