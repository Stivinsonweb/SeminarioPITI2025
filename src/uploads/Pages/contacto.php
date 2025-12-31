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
      <h1 class="display-4 fw-bold text-white">Contacto</h1>
      <p class="lead text-white">
        Escríbenos tus dudas, sugerencias o comentarios.
      </p>
    </div>
  </div>
</section>

<!-- Sección de contacto -->
<section class="contacto-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="contacto-form shadow rounded">
          <h3 class="fw-bold mb-4 text-center">Envíanos un mensaje</h3>
          <form>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control" placeholder="Tu nombre" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Apellido</label>
                <input type="text" class="form-control" placeholder="Tu apellido" required>
              </div>
            </div>
            <div class="mt-3">
              <label class="form-label">Correo</label>
              <input type="email" class="form-control" placeholder="ejemplo@correo.com" required>
            </div>
            <div class="mt-3">
              <label class="form-label">Asunto</label>
              <input type="text" class="form-control" placeholder="Motivo del mensaje" required>
            </div>
            <div class="mt-3">
              <label class="form-label">Mensaje</label>
              <textarea class="form-control" rows="4" placeholder="Escribe tu mensaje aquí..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-4">Enviar mensaje</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include '../inc/footer.php'; ?>
