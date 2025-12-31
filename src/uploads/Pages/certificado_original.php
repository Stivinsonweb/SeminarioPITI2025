<?php
include '../../ruta.php';
include '../inc/head.php';
include '../inc/header.php';
?>

<!-- Sección del video de fondo -->
<section class="video-hero">
  <video autoplay muted loop class="video-background">
    <source src="<?php echo RUTA; ?>/src/assets/video/tecnologia.mp4" type="video/mp4">
    Tu navegador no soporta videos en HTML5.
  </video>

  <div class="video-overlay"></div>

  <div class="hero-content">
    <div class="container text-center">
      <h1 class="display-4 fw-bold text-white">Certificado de Seminario</h1>
      <p class="lead text-white">
        Gracias por participar en nuestro seminario.
        Ingresa tu número de documento para generar tu certificado.
      </p>
    </div>
  </div>
</section>

<section class="certificado-section">
  <div class="container">
    <h1 class="title">Certificado</h1>

    <!-- Icono -->
    <div class="icono-certificado">
      <i class="fas fa-certificate"></i>
    </div>

    <!-- Layout mejorado -->
    <div class="certificado-content">
      <div class="imagen">
        <img src="<?php echo RUTA; ?>src/assets/img/Certificado.png" alt="Certificado">
      </div>

      <div class="certificado-form">
        <form id="formCertificado">
          <input
            type="text"
            id="numeroDocumento"
            name="numeroDocumento"
            placeholder="Número de documento"
            required>
          <button type="submit">Generar certificado</button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include '../inc/footer.php'; ?>