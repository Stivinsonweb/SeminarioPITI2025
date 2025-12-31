<header class="navbar navbar-expand-lg custom-navbar">
  <div class="container">
    <!-- Logo con imagen -->
    <a class="navbar-brand d-flex align-items-center brand-container" href="<?php echo RUTA; ?>index.php">
      <img src="<?php echo RUTA; ?>/src/assets/img/logo.png" alt="Logo EIEI 2024" class="logo-navbar me-2">
      <div class="brand-text">
        <span class="fw-bold text-gradient d-block">SEMINARIO PITI 2025</span>
      </div>
    </a>

    <!-- Botón de menú responsive -->
    <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Links de navegación -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto text-uppercase fw-semibold">
        <li class="nav-item">
          <a class="nav-link nav-link-gradient" href="<?php echo RUTA; ?>index.php">Inicio</a>
        </li>

        <!-- Información Académica como links normales -->
        <li class="nav-item">
          <a class="nav-link nav-link-gradient" href="<?php echo RUTA; ?>src/Pages/horario.php">Horario del evento</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-link-gradient" href="<?php echo RUTA; ?>src/Pages/certificado.php">Descarga certificado</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-link-gradient" href="<?php echo RUTA; ?>src/Pages/preinscripcion.php">Preinscripción</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-link-gradient" href="<?php echo RUTA; ?>src/Pages/noticias.php">Noticias</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-link-gradient" href="<?php echo RUTA; ?>src/Pages/login.php">Login</a>
      </ul>
    </div>
  </div>
</header>
