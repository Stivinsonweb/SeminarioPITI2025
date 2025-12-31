<?php
include '../../ruta.php';
include '../inc/head.php';
include '../inc/header.php';
?>

<!-- Sección principal con video de fondo Y contenido encima -->
<main class="video-section" style="overflow: hidden;">

  <video autoplay muted loop class="video-background">
    <source src="<?php echo RUTA; ?>/src/assets/video/tecnologia.mp4" type="video/mp4">
    Tu navegador no soporta videos en HTML5.
  </video>

  <div class="overlay"></div>

  <section class="content-box">
    <!-- Header Moderno -->
    <div class="horario-hero text-center">
      <h1 class="hero-main-title">Cronograma del Evento</h1>
      <p class="hero-subtitle">Del 30 al 31 de octubre, 2025 - Universidad Tecnológica del Chocó</p>
      
      <div class="event-stats">
        <div class="stat-item">
          <div class="stat-number">10</div>
          <div class="stat-label">Actividades</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">9</div>
          <div class="stat-label">Horas</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">3</div>
          <div class="stat-label">Conferencias</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">5+</div>
          <div class="stat-label">Ponentes</div>
        </div>
      </div>
    </div>

    <!-- Timeline Moderno Mejorado -->
    <div class="modern-timeline">
      <!-- Sesión Mañana -->
      <div class="session-section">
        <div class="session-header morning-session">
          <div class="session-icon">
            <i class="bi bi-sunrise"></i>
          </div>
          <div class="session-info">
            <h3>Sesión de la Mañana</h3>
            <p>8:00 AM - 12:15 PM</p>
          </div>
          <div class="session-badge">4 Actividades</div>
        </div>

        <div class="activities-grid">
          <!-- Actividad 1 -->
          <div class="activity-card">
            <div class="activity-time">
              <span class="time-main">8:00</span>
              <span class="time-end">8:30</span>
              <div class="time-duration">30min</div>
            </div>
            <div class="activity-content">
              <div class="activity-icon">
                <i class="bi bi-person-check"></i>
              </div>
              <div class="activity-details">
                <h4>Registro e Instalación</h4>
                <p>Recepción de participantes y acomodación en el auditorio principal</p>
                <div class="activity-tags">
                  <span class="activity-tag reception">Recepción</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Actividad 2 -->
          <div class="activity-card highlight">
            <div class="activity-time">
              <span class="time-main">8:30</span>
              <span class="time-end">9:00</span>
              <div class="time-duration">30min</div>
            </div>
            <div class="activity-content">
              <div class="activity-icon">
                <i class="bi bi-megaphone"></i>
              </div>
              <div class="activity-details">
                <h4>Acto de Apertura</h4>
                <p>Palabras de bienvenida de autoridades académicas e invitados especiales</p>
                <div class="activity-tags">
                  <span class="activity-tag opening">Inauguración</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Actividad 3 -->
          <div class="activity-card">
            <div class="activity-time">
              <span class="time-main">9:00</span>
              <span class="time-end">10:00</span>
              <div class="time-duration">60min</div>
            </div>
            <div class="activity-content">
              <div class="activity-icon">
                <i class="bi bi-mic"></i>
              </div>
              <div class="activity-details">
                <h4>Conferencia Magistral 1</h4>
                <p>Ponente nacional - Innovación en Telecomunicaciones</p>
                <div class="activity-tags">
                  <span class="activity-tag conference">Conferencia</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Actividad 4 -->
          <div class="activity-card break">
            <div class="activity-time">
              <span class="time-main">10:00</span>
              <span class="time-end">10:15</span>
              <div class="time-duration">15min</div>
            </div>
            <div class="activity-content">
              <div class="activity-icon">
                <i class="bi bi-cup-hot"></i>
              </div>
              <div class="activity-details">
                <h4>Refrigerio</h4>
                <p>Pausa para café y networking entre participantes</p>
                <div class="activity-tags">
                  <span class="activity-tag break">Break</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Actividad 5 -->
          <div class="activity-card">
            <div class="activity-time">
              <span class="time-main">10:15</span>
              <span class="time-end">11:15</span>
              <div class="time-duration">60min</div>
            </div>
            <div class="activity-content">
              <div class="activity-icon">
                <i class="bi bi-mic"></i>
              </div>
              <div class="activity-details">
                <h4>Conferencia Magistral 2</h4>
                <p>Ponente nacional - Inteligencia Artificial Aplicada</p>
                <div class="activity-tags">
                  <span class="activity-tag conference">Conferencia</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Actividad 6 -->
          <div class="activity-card highlight">
            <div class="activity-time">
              <span class="time-main">11:15</span>
              <span class="time-end">12:15</span>
              <div class="time-duration">60min</div>
            </div>
            <div class="activity-content">
              <div class="activity-icon">
                <i class="bi bi-people"></i>
              </div>
              <div class="activity-details">
                <h4>Panel de Egresados</h4>
                <p>Experiencias significativas y casos de éxito en el sector tecnológico</p>
                <div class="activity-tags">
                  <span class="activity-tag networking">Networking</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Almuerzo -->
      <div class="break-section">
        <div class="break-card">
          <div class="break-icon">
            <i class="bi bi-egg-fried"></i>
          </div>
          <div class="break-content">
            <h3>Almuerzo Libre</h3>
            <p>12:15 PM - 2:00 PM</p>
            <span class="break-tag">Tiempo para descanso y alimentación</span>
          </div>
        </div>
      </div>

      <!-- Sesión Tarde -->
      <div class="session-section">
        <div class="session-header afternoon-session">
          <div class="session-icon">
            <i class="bi bi-sunset"></i>
          </div>
          <div class="session-info">
            <h3>Sesión de la Tarde</h3>
            <p>2:00 PM - 5:30 PM</p>
          </div>
          <div class="session-badge">4 Actividades</div>
        </div>

        <div class="activities-grid">
          <!-- Actividad 7 -->
          <div class="activity-card">
            <div class="activity-time">
              <span class="time-main">2:00</span>
              <span class="time-end">3:00</span>
              <div class="time-duration">60min</div>
            </div>
            <div class="activity-content">
              <div class="activity-icon">
                <i class="bi bi-laptop"></i>
              </div>
              <div class="activity-details">
                <h4>Presentación de Proyectos</h4>
                <p>Exhibición de investigaciones desarrolladas en el aula por estudiantes</p>
                <div class="activity-tags">
                  <span class="activity-tag students">Estudiantes</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Actividad 8 -->
          <div class="activity-card">
            <div class="activity-time">
              <span class="time-main">3:00</span>
              <span class="time-end">4:00</span>
              <div class="time-duration">60min</div>
            </div>
            <div class="activity-content">
              <div class="activity-icon">
                <i class="bi bi-mic"></i>
              </div>
              <div class="activity-details">
                <h4>Conferencia Magistral 3</h4>
                <p>Ponente nacional - Futuro de la Conectividad 5G</p>
                <div class="activity-tags">
                  <span class="activity-tag conference">Conferencia</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Actividad 9 -->
          <div class="activity-card highlight">
            <div class="activity-time">
              <span class="time-main">4:00</span>
              <span class="time-end">5:00</span>
              <div class="time-duration">60min</div>
            </div>
            <div class="activity-content">
              <div class="activity-icon">
                <i class="bi bi-bank"></i>
              </div>
              <div class="activity-details">
                <h4>Feria Estudiantil</h4>
                <p>Espacio interactivo con colegios invitados y demostración de proyectos innovadores</p>
                <div class="activity-tags">
                  <span class="activity-tag interactive">Interactivo</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Actividad 10 -->
          <div class="activity-card">
            <div class="activity-time">
              <span class="time-main">5:00</span>
              <span class="time-end">5:30</span>
              <div class="time-duration">30min</div>
            </div>
            <div class="activity-content">
              <div class="activity-icon">
                <i class="bi bi-award"></i>
              </div>
              <div class="activity-details">
                <h4>Clausura y Reconocimientos</h4>
                <p>Ceremonia de cierre y premiación a participantes destacados del evento</p>
                <div class="activity-tags">
                  <span class="activity-tag closing">Clausura</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CTA Mejorado -->
    <div class="schedule-cta">
      <div class="cta-content">
        <h3>¿Listo para esta experiencia única?</h3>
        <p>Reserva tu lugar y sé parte del evento tecnológico más importante del año</p>
        <a href="<?php echo RUTA; ?>src/Pages/preinscripcion.php" class="cta-button">
          <i class="bi bi-calendar-plus"></i>
          <span>Reservar Mi Cupo</span>
        </a>
      </div>
    </div>
  </section>
</main>

<?php include '../inc/footer.php'; ?>