<footer>
  <div class="footer-container">
    <!-- Logos e Información Principal -->
    <div class="footer-logos">
      <img src="<?php echo RUTA; ?>src/assets/img/logo.png" alt="Logo PITI" class="footer-logo">
      <img src="<?php echo RUTA; ?>src/assets/img/Logo_Universidad.png" alt="Logo Universidad" class="footer-logo">
    </div>

    <!-- Información de la Universidad -->
    <div class="footer-info">
      <h4>Universidad Tecnológica del Chocó</h4>
      <p>Institución de educación superior comprometida con la formación integral y el desarrollo tecnológico de la región.</p>
      
      <div class="contact-info">
        <div class="contact-item">
          <i class="bi bi-geo-alt-fill"></i>
          <span>Quibdó, Chocó, Colombia</span>
        </div>
        <div class="contact-item">
          <i class="bi bi-telephone-fill"></i>
          <span>+574 672 65 65</span>
        </div>
        <div class="contact-item">
          <i class="bi bi-envelope-fill"></i>
          <span>contactenos@utch.edu.co</span>
        </div>
      </div>
    </div>

    <!-- Enlaces Rápidos -->
    <div class="footer-links">
      <h4>Enlaces Rápidos</h4>
      <ul>
        <li>
          <a href="<?php echo RUTA; ?>src/Pages/preinscripcion.php">
            <i class="bi bi-arrow-right"></i>
            Preinscripción
          </a>
        </li>
        <li>
          <a href="<?php echo RUTA; ?>src/Pages/certificado.php">
            <i class="bi bi-arrow-right"></i>
            Certificado
          </a>
        </li>
        <li>
          <a href="<?php echo RUTA; ?>src/Pages/horario.php">
            <i class="bi bi-arrow-right"></i>
            Horario
          </a>
        </li>
            <!--<li>
      <a href="<?php echo RUTA; ?>src/Pages/contacto.php"> 
            <i class="bi bi-arrow-right"></i>
            Contacto
          </a>
        </li>-->
      </ul>
    </div>

    <!-- Desarrolladores -->
    <div class="footer-developers">
      <h4>Desarrolladores</h4>
      <ul>
        <li>
          <a href="https://www.instagram.com/stivinson_fullstack/" target="_blank" rel="noopener">
            <i class="bi bi-instagram"></i>
            Stivinson Maturana
          </a>
        </li>
        <li>
          <a href="https://github.com/Zblue98" target="_blank" rel="noopener">
            <i class="bi bi-github"></i>
            Albis Fox
          </a>
        </li>
      
        <li>
          <a href="https://utch.edu.co" target="_blank" rel="noopener">
            <i class="bi bi-globe"></i>
            Sitio Web UTCH
          </a>
        </li>
      </ul>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom">
      <p>&copy; <?php echo date('Y'); ?> PITI - Universidad Tecnológica del Chocó. Todos los derechos reservados.</p>
      
      <div class="social-links">
        <a href="https://web.facebook.com/prensautch/?locale=es_LA&_rdc=1&_rdr#" class="social-link" target="_blank" rel="noopener">
          <i class="bi bi-facebook"></i>
        </a>
        <a href="https://x.com/UTCH_" class="social-link" target="_blank" rel="noopener">
          <i class="bi bi-x"></i>
        </a>
        <a href="https://www.instagram.com/utch_edu/" class="social-link" target="_blank" rel="noopener">
          <i class="bi bi-instagram"></i>
        </a>
        <a href="https://www.youtube.com/user/centroafro" class="social-link" target="_blank" rel="noopener">
          <i class="bi bi-youtube"></i>
        </a>
      </div>
    </div>
  </div>
</footer>

<!-- Scripts Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<!-- JS personalizado -->
<script src="<?php echo RUTA; ?>src/assets/js/Preinscripcion.js"></script>
<script src="<?php echo RUTA; ?>src/assets/js/certificado.js"></script>
</body>
</html>