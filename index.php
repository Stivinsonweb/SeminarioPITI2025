<?php
include './ruta.php';
include './src/inc/head.php';
include './src/inc/header.php';
?>


<main class="video-section" style="overflow: hidden;">
  <video autoplay muted loop class="video-background">
    <source src="<?php echo RUTA; ?>/src/assets/video/tecnologia.mp4" type="video/mp4">
    Tu navegador no soporta videos en HTML5.
  </video>

  <div class="overlay"></div>

  <section class="content-box text-center">
    <!-- HERO LIMPIO SOLO CON TEXTO DINÁMICO -->
    <div class="clean-hero">
      <div class="hero-content-wrapper">
        <!-- Texto dinámico mejorado -->
        <div class="dynamic-hero-content">
          
          
          <h1 class="hero-main-title">SEMANA DE LA CIENCIA</h1>
          <div class="typewriter-wrapper">
            <div class="typewriter-container">
              <span class="typewriter-text" id="typewriter"></span>
              <span class="typewriter-cursor">|</span>
            </div>
          </div>
          
          <p class="hero-description">
            Descubre las tendencias que están transformando 
            <span class="highlight-text">Colombia y el mundo</span>
          </p>
          
          <!-- Indicadores minimalistas -->
          <div class="text-indicators">
            <div class="indicator-dot active" data-index="0"></div>
            <div class="indicator-dot" data-index="1"></div>
            <div class="indicator-dot" data-index="2"></div>
            <div class="indicator-dot" data-index="3"></div>
          </div>
        </div>
      </div>
      
      <div class="hero-cta">
        <a href="<?php echo RUTA; ?>src/Pages/preinscripcion.php" class="cta-button">
          <i class="bi bi-pencil-square"></i>
          Preinscripción Gratuita
        </a>
        <div class="cta-subtext">
          <span>Del 30 al 31 de octubre 2025</span>
        </div>
      </div>
    </div>

      <!-- SECCIÓN DE TODAS LAS CARDS MEJORADA -->
    <section class="all-cards-section">
      <h2 class="section-title">Temas del Evento</h2>
      <p class="section-subtitle">Descubre todos los temas que cubrirá nuestro seminario</p>
      
      <div class="temas-grid">
        <!-- Inteligencia artificial -->
        <div class="tema-card">
          <div class="card-image" style="background-image: url('<?php echo RUTA; ?>/src/assets/img/Inteligencia artificial.png');"></div>
          <div class="card-overlay"></div>
          <div class="card-particles">
            <div class="card-particle" style="left: 20%; animation-delay: 0s;"></div>
            <div class="card-particle" style="left: 50%; animation-delay: 0.5s;"></div>
            <div class="card-particle" style="left: 80%; animation-delay: 1s;"></div>
          </div>
          <div class="card-content">
            <h3>Inteligencia artificial y ética en telecomunicaciones</h3>
          </div>
        </div>

        <!-- Redes 5G -->
        <div class="tema-card">
          <div class="card-image" style="background-image: url('<?php echo RUTA; ?>/src/assets/img/Redes 5G.jpg');"></div>
          <div class="card-overlay"></div>
          <div class="card-particles">
            <div class="card-particle" style="left: 20%; animation-delay: 0s;"></div>
            <div class="card-particle" style="left: 50%; animation-delay: 0.5s;"></div>
            <div class="card-particle" style="left: 80%; animation-delay: 1s;"></div>
          </div>
          <div class="card-content">
            <h3>Redes 5G y su impacto en Colombia</h3>
          </div>
        </div>

        <!-- Ciberseguridad -->
        <div class="tema-card">
          <div class="card-image" style="background-image: url('<?php echo RUTA; ?>/src/assets/img/Ciberseguridad.jpg');"></div>
          <div class="card-overlay"></div>
          <div class="card-particles">
            <div class="card-particle" style="left: 20%; animation-delay: 0s;"></div>
            <div class="card-particle" style="left: 50%; animation-delay: 0.5s;"></div>
            <div class="card-particle" style="left: 80%; animation-delay: 1s;"></div>
          </div>
          <div class="card-content">
            <h3>Ciberseguridad para sectores públicos y privados</h3>
          </div>
        </div>

        <!-- Computación en la nube -->
        <div class="tema-card">
          <div class="card-image" style="background-image: url('<?php echo RUTA; ?>/src/assets/img/computacion_nube.jpg');"></div>
          <div class="card-overlay"></div>
          <div class="card-particles">
            <div class="card-particle" style="left: 20%; animation-delay: 0s;"></div>
            <div class="card-particle" style="left: 50%; animation-delay: 0.5s;"></div>
            <div class="card-particle" style="left: 80%; animation-delay: 1s;"></div>
          </div>
          <div class="card-content">
            <h3>Computación en la nube y arquitectura de redes modernas</h3>
          </div>
        </div>

        <!-- Internet de las cosas -->
        <div class="tema-card">
          <div class="card-image" style="background-image: url('<?php echo RUTA; ?>/src/assets/img/Internet de las cosas.png');"></div>
          <div class="card-overlay"></div>
          <div class="card-particles">
            <div class="card-particle" style="left: 20%; animation-delay: 0s;"></div>
            <div class="card-particle" style="left: 50%; animation-delay: 0.5s;"></div>
            <div class="card-particle" style="left: 80%; animation-delay: 1s;"></div>
          </div>
          <div class="card-content">
            <h3>Internet de las cosas (IoT) y ciudades inteligentes</h3>
          </div>
        </div>

        <!-- Tecnologías emergentes -->
        <div class="tema-card">
          <div class="card-image" style="background-image: url('<?php echo RUTA; ?>/src/assets/img/Empredimiento en tecnologia.jpg');"></div>
          <div class="card-overlay"></div>
          <div class="card-particles">
            <div class="card-particle" style="left: 20%; animation-delay: 0s;"></div>
            <div class="card-particle" style="left: 50%; animation-delay: 0.5s;"></div>
            <div class="card-particle" style="left: 80%; animation-delay: 1s;"></div>
          </div>
          <div class="card-content">
            <h3>Tecnologías emergentes y transformación digital</h3>
          </div>
        </div>

        <!-- Emprendimiento digital -->
        <div class="tema-card">
          <div class="card-image" style="background-image: url('<?php echo RUTA; ?>/src/assets/img/Empredimiento digital.jpg');"></div>
          <div class="card-overlay"></div>
          <div class="card-particles">
            <div class="card-particle" style="left: 20%; animation-delay: 0s;"></div>
            <div class="card-particle" style="left: 50%; animation-delay: 0.5s;"></div>
            <div class="card-particle" style="left: 80%; animation-delay: 1s;"></div>
          </div>
          <div class="card-content">
            <h3>Emprendimiento digital y casos de éxito en tecnología</h3>
          </div>
        </div>
      </div>
    </section>
    <!-- EVENTO IMPERDIBLE -->
    <section class="evento-imperdible">
      <h2>Una Experiencia Única</h2>
      <p class="lead">Vive momentos extraordinarios que transformarán tu visión profesional</p>

      <div class="evento-items">
        <div class="evento-item">
          <div class="icon-container">
            <i class="bi bi-star-fill"></i>
          </div>
          <h4>Expertos Nacionales</h4>
          <p class="evento-descripcion">Líderes de la industria compartiendo conocimiento de vanguardia</p>
          <div class="particulas">
            <div class="particula"></div>
            <div class="particula"></div>
            <div class="particula"></div>
            <div class="particula"></div>
            <div class="particula"></div>
          </div>
        </div>

        <div class="evento-item">
          <div class="icon-container">
            <i class="bi bi-rocket-fill"></i>
          </div>
          <h4>Egresados Destacados</h4>
          <p class="evento-descripcion">Casos de éxito y experiencias de transformación profesional</p>
          <div class="particulas">
            <div class="particula"></div>
            <div class="particula"></div>
            <div class="particula"></div>
            <div class="particula"></div>
            <div class="particula"></div>
          </div>
        </div>

        <div class="evento-item">
          <div class="icon-container">
            <i class="bi bi-people-fill"></i>
          </div>
          <h4>Comunidad Activa</h4>
          <p class="evento-descripcion">Networking con los futuros líderes del sector tecnológico</p>
          <div class="particulas">
            <div class="particula"></div>
            <div class="particula"></div>
            <div class="particula"></div>
            <div class="particula"></div>
            <div class="particula"></div>
          </div>
        </div>

        <div class="evento-item">
          <div class="icon-container">
            <i class="bi bi-lightbulb-fill"></i>
          </div>
          <h4>Futuros Talentos</h4>
          <p class="evento-descripcion">Jóvenes promesas mostrando su potencial innovador</p>
          <div class="particulas">
            <div class="particula"></div>
            <div class="particula"></div>
            <div class="particula"></div>
            <div class="particula"></div>
            <div class="particula"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- CON EL APOYO DE -->
    <section class="hero-main-title">
      <h2>Con el apoyo de</h2>
      <div class="apoyo-logo-grid">
        <div class="apoyo-logo-item">
          <img src="<?php echo RUTA; ?>src/assets/img/Logo_Universidad.png" alt="Logo Universidad" class="apoyo-logo">
        </div>
      </div>
       <div class="apoyo-logo-grid">
        <div class="apoyo-logo-item">
          <img src="<?php echo RUTA; ?>src/assets/img/logo1.png" alt="Logo Universidad" class="apoyo-logo">
        </div>
      </div>
       <div class="apoyo-logo-grid">
        <div class="apoyo-logo-item">
          <img src="<?php echo RUTA; ?>src/assets/img/logo2.png" alt="Logo Universidad" class="apoyo-logo">
        </div>
      </div>
       <div class="apoyo-logo-grid">
        <div class="apoyo-logo-item">
          <img src="<?php echo RUTA; ?>src/assets/img/logo3.png" alt="Logo Universidad" class="apoyo-logo">
        </div>
      </div>
       <div class="apoyo-logo-grid">
        <div class="apoyo-logo-item">
          <img src="<?php echo RUTA; ?>src/assets/img/logo4.png" alt="Logo Universidad" class="apoyo-logo">
        </div>
      </div>
       <div class="apoyo-logo-grid">
        <div class="apoyo-logo-item">
          <img src="<?php echo RUTA; ?>src/assets/img/logo5.png" alt="Logo Universidad" class="apoyo-logo">
        </div>
      </div>
      <p class="apoyo-descripcion">Instituciones comprometidas con la innovación tecnológica</p>
    </section>
  </section>
</main>


<!-- JavaScript CORREGIDO para el efecto typewriter -->
<script>

class CleanDynamicHero {
  constructor() {
    this.texts = [
      "Innovación", 
      "Conectividad",
      "Futuro Digital",
      "By PITI",
    ];
    this.currentIndex = 0;
    this.typewriterElement = document.getElementById('typewriter');
    this.indicators = document.querySelectorAll('.indicator-dot');
    this.isTyping = false;
    this.typeTimeout = null;
    
    this.init();
    this.setupIndicatorInteractions();
  }
  
  init() {
    // Mostrar el primer texto inmediatamente
    this.typewriterElement.textContent = this.texts[this.currentIndex];
    this.startAutoRotation();
  }
  
  startAutoRotation() {
    setInterval(() => {
      if (!this.isTyping) {
        this.nextText();
      }
    }, 3000);
  }
  
  nextText() {
    if (this.isTyping) return;
    
    this.isTyping = true;
    const currentText = this.texts[this.currentIndex];
    const nextIndex = (this.currentIndex + 1) % this.texts.length;
    const nextText = this.texts[nextIndex];
    
    // Efecto de borrado
    this.deleteText(currentText, () => {
      // Cambiar índice y actualizar indicadores
      this.currentIndex = nextIndex;
      this.updateIndicators();
      
      // Escribir nuevo texto
      this.typeText(nextText, () => {
        this.isTyping = false;
      });
    });
  }
  
  deleteText(text, callback) {
    let charIndex = text.length;
    
    const deleteChar = () => {
      if (charIndex > 0) {
        charIndex--;
        this.typewriterElement.textContent = text.substring(0, charIndex);
        this.typeTimeout = setTimeout(deleteChar, 60);
      } else {
        callback();
      }
    };
    
    deleteChar();
  }
  
  typeText(text, callback) {
    let charIndex = 0;
    
    const typeChar = () => {
      if (charIndex < text.length) {
        charIndex++;
        this.typewriterElement.textContent = text.substring(0, charIndex);
        this.typeTimeout = setTimeout(typeChar, 120);
      } else {
        callback();
      }
    };
    
    typeChar();
  }
  
  updateIndicators() {
    this.indicators.forEach((indicator, index) => {
      indicator.classList.toggle('active', index === this.currentIndex);
    });
  }
  
  setupIndicatorInteractions() {
    this.indicators.forEach((indicator, index) => {
      indicator.addEventListener('click', () => {
        if (this.isTyping || index === this.currentIndex) return;
        
        // Limpiar timeout actual
        if (this.typeTimeout) {
          clearTimeout(this.typeTimeout);
        }
        
        this.isTyping = true;
        const currentText = this.texts[this.currentIndex];
        
        // Borrar texto actual
        this.deleteText(currentText, () => {
          // Cambiar al texto seleccionado
          this.currentIndex = index;
          this.updateIndicators();
          
          // Escribir nuevo texto
          this.typeText(this.texts[this.currentIndex], () => {
            this.isTyping = false;
          });
        });
      });
    });
  }
}

// Efectos de entrada suaves
document.addEventListener('DOMContentLoaded', () => {
  new CleanDynamicHero();
  
  // Animación de entrada para los elementos
  const heroElements = document.querySelectorAll('.dynamic-hero-content > *');
  heroElements.forEach((el, index) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    
    setTimeout(() => {
      el.style.transition = 'all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
      el.style.opacity = '1';
      el.style.transform = 'translateY(0)';
    }, index * 200);
  });
});
</script>

<?php include './src/inc/footer.php'; ?>