<?php
include '../../ruta.php';

$pageTitle = "Noticias - Semana de la Ciencia 2025";
$pageDescription = "Comparte tu experiencia en la Semana de la Ciencia. Cuéntanos tu opinión y compártenos tu mejor foto del evento. Galería de fotos, videos y testimonios de los participantes.";
$pageImage = RUTA . "src/assets/img/Noticias/preview-noticias.png";
$pageUrl = RUTA . "src/Pages/noticias.php";

include '../inc/head.php';
include '../inc/header.php';

include '../../SQL/db_connect.php';

$avatares = [];
try {
    $sql = "SELECT Id_Avatar FROM Avatar ORDER BY Id_Avatar";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($result as $row) {
        $avatares[] = [
            'Id_Avatar' => $row['Id_Avatar'],
            'Avatar' => 'SQL/get_avatar.php?id=' . $row['Id_Avatar']
        ];
    }
    
} catch(PDOException $e) {
    error_log("Error al cargar avatares: " . $e->getMessage());
    $avatares = [];
}

$avatares_json = json_encode($avatares);
if ($avatares_json === false || $avatares_json === 'null') {
    $avatares_json = '[]';
}

?>

<style>
  .event-info {
    text-align: center;
    color: #f8f9fa;
    font-size: 1.1rem;
    margin-bottom: 30px;
    font-weight: 500;
  }

  .highlight-stat {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 5px 15px;
    border-radius: 25px;
    font-weight: bold;
    font-size: 1.3rem;
    display: inline-block;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    animation: pulse 2s ease-in-out infinite;
  }

  @keyframes pulse {
    0%, 100% {
      transform: scale(1);
    }
    50% {
      transform: scale(1.05);
    }
  }

  .day-section {
    margin-bottom: 60px;
  }

  .day-section.hidden {
    display: none;
  }

  .day-title {
    color: white;
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 30px;
    text-align: center;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  }

  .gallery-container {
    padding: 40px 0 80px;
    background: linear-gradient(180deg, #000000 0%, #1a1a1a 100%);
  }

  .gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
    padding: 0 15px;
  }

  .gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 12px;
    cursor: pointer;
    aspect-ratio: 16/9;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .gallery-item:hover {
    transform: translateY(-10px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
  }

  .gallery-item img,
  .gallery-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
  }

  .gallery-item:hover img,
  .gallery-item:hover video {
    transform: scale(1.1);
  }

  .gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.8) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    display: none;
    align-items: flex-end;
    padding: 20px;
  }

  .gallery-item:hover .gallery-overlay {
    opacity: 1;
  }

  .gallery-caption {
    color: white;
    font-size: 1rem;
    font-weight: 500;
  }

  .video-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(255, 255, 255, 0.9);
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: bold;
    color: #667eea;
    z-index: 2;
  }

  .lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.95);
    z-index: 9999;
    justify-content: center;
    align-items: center;
  }

  .lightbox.active {
    display: flex;
  }

  .lightbox-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
    animation: zoomIn 0.3s ease;
  }

  @keyframes zoomIn {
    from {
      transform: scale(0.8);
      opacity: 0;
    }
    to {
      transform: scale(1);
      opacity: 1;
    }
  }

  .lightbox-content img,
  .lightbox-content video {
    max-width: 100%;
    max-height: 90vh;
    border-radius: 8px;
    box-shadow: 0 0 50px rgba(255, 255, 255, 0.2);
  }

  .lightbox-close {
    position: absolute;
    top: -50px;
    right: 0;
    color: white;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    background: rgba(255, 0, 0, 0.8);
    border: none;
    padding: 10px 20px;
    border-radius: 50%;
    transition: all 0.3s ease;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .lightbox-close:hover {
    background: rgba(255, 0, 0, 1);
    transform: scale(1.1);
  }

  .lightbox-download {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    color: white;
    font-size: 13px;
    cursor: pointer;
    background: rgba(0, 150, 0, 0.95);
    border: 2px solid rgba(255, 255, 255, 0.4);
    padding: 6px 18px;
    border-radius: 20px;
    transition: all 0.3s ease;
    font-weight: 600;
    z-index: 10;
  }

  .lightbox-download:hover {
    background: rgba(0, 180, 0, 1);
    transform: translateX(-50%) scale(1.08);
    border-color: rgba(255, 255, 255, 0.7);
    box-shadow: 0 4px 15px rgba(0, 150, 0, 0.5);
  }

  .lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    color: white;
    font-size: 50px;
    cursor: pointer;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    padding: 20px 15px;
    transition: background 0.3s ease;
    border-radius: 5px;
  }

  .lightbox-nav:hover {
    background: rgba(255, 255, 255, 0.2);
  }

  .lightbox-prev {
    left: 20px;
  }

  .lightbox-next {
    right: 20px;
  }

  .lightbox-counter {
    position: absolute;
    bottom: -50px;
    left: 50%;
    transform: translateX(-50%);
    color: white;
    font-size: 1.2rem;
    font-weight: 500;
  }

  @media (max-width: 768px) {
    .gallery-grid {
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 15px;
    }

    .lightbox-nav {
      font-size: 35px;
      padding: 15px 10px;
    }
  }
  
  /* Sistema de Testimonios */
  .testimonios-section {
    background: linear-gradient(180deg, #1a1a1a 0%, #000000 100%);
    padding: 60px 0;
    position: relative;
  }

  .testimonios-section .experiencias-title {
    color: white;
    font-size: 2.5rem;
    font-weight: bold;
    text-align: center;
    margin-bottom: 15px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  }

  .testimonios-section .experiencias-subtitle {
    color: #b8b8b8;
    text-align: center;
    font-size: 1.1rem;
    margin-bottom: 50px;
  }

  .testimonios-section .experience-form {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 40px;
    max-width: 800px;
    margin: 0 auto 60px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  }

  .testimonios-section .form-group {
    margin-bottom: 25px;
  }

  .testimonios-section .form-group label {
    color: white;
    font-weight: 600;
    margin-bottom: 10px;
    display: block;
    font-size: 1rem;
  }

  .testimonios-section .form-control {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    color: white;
    padding: 12px 15px;
    width: 100%;
    font-size: 1rem;
    transition: all 0.3s ease;
  }

  .testimonios-section .form-control:focus {
    background: rgba(255, 255, 255, 0.15);
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
  }

  .testimonios-section .form-control::placeholder {
    color: rgba(255, 255, 255, 0.5);
  }

  .testimonios-section textarea.form-control {
    resize: vertical;
    min-height: 100px;
  }

  .testimonios-section .avatar-selector {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 15px;
    margin-top: 10px;
  }

  .testimonios-section .avatar-option {
    cursor: pointer;
    border: 3px solid transparent;
    border-radius: 15px;
    transition: all 0.3s ease;
    padding: 15px;
    display: flex;
    flex-direction: column;
    align-items: center;
    background: rgba(255, 255, 255, 0.05);
    min-height: 140px;
  }

  .testimonios-section .avatar-option img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 10px;
    transition: transform 0.3s ease;
  }

  .testimonios-section .avatar-option:hover {
    transform: translateY(-5px);
    border-color: #667eea;
    background: rgba(255, 255, 255, 0.1);
  }

  .testimonios-section .avatar-option:hover img {
    transform: scale(1.1);
  }

  .testimonios-section .avatar-option.selected {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.2);
    box-shadow: 0 0 30px rgba(102, 126, 234, 0.6);
  }

  .testimonios-section .avatar-label {
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    text-align: center;
  }

  .testimonios-section .rating-container {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 10px;
      justify-content: space-between;
    }

  .testimonios-section .rating-option {
    flex: 1;
    min-width: 120px;
    padding: 12px 10px;
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    color: white;
    cursor: pointer;
    text-align: center;
    font-weight: 600;
    transition: all 0.3s ease;
    font-size: 1.2rem;
    white-space:nowrap;
  }

  .testimonios-section .rating-option:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
  }

  .testimonios-section .rating-option.selected {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
  }

  .testimonios-section .file-upload-button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 25px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    width: 100%;
    transition: all 0.3s ease;
  }

  .testimonios-section .file-upload-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
  }

  .testimonios-section .file-name {
    color: #667eea;
    margin-top: 10px;
    font-size: 0.9rem;
  }

  .testimonios-section .submit-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 40px;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    width: 100%;
    transition: all 0.3s ease;
    margin-top: 10px;
  }

  .testimonios-section .submit-btn:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
  }

  .testimonios-section .submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  /* Slider con HOVER ESPECTACULAR */
  .testimonios-section .testimonials-slider {
    position: relative;
    overflow: hidden;
    max-width: 100%;
    padding: 20px 0;
  }

  .testimonios-section .testimonials-track {
    display: flex;
    gap: 25px;
    transition: transform 0.5s ease;
    padding: 20px 0;
  }

  .testimonios-section .testimonial-card {
    flex: 0 0 380px;
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 30px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    cursor: pointer;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }

  .testimonios-section .testimonial-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.3), transparent);
    transition: left 0.7s ease;
  }

  .testimonios-section .testimonial-card:hover::before {
    left: 100%;
  }

  .testimonios-section .testimonial-card::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(102, 126, 234, 0.4) 0%, transparent 70%);
    transform: translate(-50%, -50%);
    transition: width 0.6s ease, height 0.6s ease;
  }

  .testimonios-section .testimonial-card:hover::after {
    width: 500px;
    height: 500px;
  }

  .testimonios-section .testimonial-card:hover {
    transform: translateY(-15px) scale(1.02);
    box-shadow: 
      0 20px 60px rgba(102, 126, 234, 0.4),
      0 0 0 1px rgba(102, 126, 234, 0.5),
      inset 0 0 50px rgba(102, 126, 234, 0.1);
    border-color: #667eea;
    background: rgba(255, 255, 255, 0.08);
  }

  .testimonios-section .testimonial-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    position: relative;
    z-index: 2;
  }

  .testimonios-section .testimonial-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 5px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid rgba(255, 255, 255, 0.2);
  }

  .testimonios-section .testimonial-card:hover .testimonial-avatar {
    transform: scale(1.15) rotate(5deg);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.6);
  }

  .testimonios-section .testimonial-avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
  }

  .testimonios-section .testimonial-name {
    color: white;
    font-weight: bold;
    font-size: 1.1rem;
    transition: all 0.3s ease;
  }

  .testimonios-section .testimonial-card:hover .testimonial-name {
    color: #667eea;
    text-shadow: 0 0 10px rgba(102, 126, 234, 0.5);
  }

  .testimonios-section .testimonial-rating {
    color: #ffd700;
    font-size: 1rem;
    margin-top: 3px;
    transition: all 0.3s ease;
  }

  .testimonios-section .testimonial-card:hover .testimonial-rating {
    transform: scale(1.1);
    filter: drop-shadow(0 0 5px rgba(255, 215, 0, 0.8));
  }

  .testimonios-section .testimonial-text {
    color: #d4d4d4;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 15px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    position: relative;
    z-index: 2;
  }

  .testimonios-section .testimonial-photo {
    width: 100%;
    height: 200px;
    border-radius: 15px;
    object-fit: cover;
    margin-top: 15px;
    position: relative;
    z-index: 2;
    transition: all 0.4s ease;
  }

  .testimonios-section .testimonial-card:hover .testimonial-photo {
    transform: scale(1.05);
    filter: brightness(1.1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
  }

  .testimonios-section .slider-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: none;
    color: white;
    font-size: 30px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 10;
  }

  .testimonios-section .slider-nav:hover {
    background: rgba(102, 126, 234, 0.8);
    transform: translateY(-50%) scale(1.1);
  }

  .testimonios-section .slider-nav.prev {
    left: 10px;
  }

  .testimonios-section .slider-nav.next {
    right: 10px;
  }

  @media (max-width: 768px) {
    .testimonios-section .avatar-selector {
      grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
    
    .testimonios-section .testimonial-card {
      flex: 0 0 300px;
    }
  }
</style>

<section class="video-hero">
  <video autoplay muted loop class="video-background">
    <source src="<?php echo RUTA; ?>/src/assets/video/tecnologia.mp4" type="video/mp4">
    Tu navegador no soporta videos en HTML5.
  </video>

  <div class="video-overlay"></div>
  <div class="hero-content">
    <div class="container text-center">
      <h1 class="display-4 fw-bold text-white hero-main-title">Noticias</h1>
      <p class="lead text-white">
        Bienvenido a las noticias de la Semana de la Ciencia
      </p>
    </div>
  </div>
</section>

<!-- Sección de Testimonios -->
<section class="testimonios-section">
  <div class="container">
    <h2 class="experiencias-title">Cuéntanos tu Experiencia</h2>
    <p class="experiencias-subtitle">Comparte tu opinión sobre la Semana de la Ciencia</p>

    <!-- Formulario -->
    <div class="experience-form">
      <form id="experienceForm" enctype="multipart/form-data">
        
        <!-- Nombre -->
        <div class="form-group">
          <label for="userName">Tu Nombre *</label>
          <input type="text" id="userName" name="nombre" class="form-control" placeholder="Escribe tu nombre" required>
        </div>

        <!-- Avatar -->
        <div class="form-group">
          <label>Elige tu Avatar *</label>
          <div class="avatar-selector" id="avatarSelector"></div>
          <input type="hidden" id="selectedAvatar" name="id_avatar" required>
        </div>

        <!-- Opinión -->
        <div class="form-group">
          <label for="presentationOpinion">¿Cómo te pareció la presentación y la temática? *</label>
          <textarea id="presentationOpinion" name="opinion" class="form-control" placeholder="Comparte tu opinión..." required></textarea>
        </div>

        <!-- Foto -->
        <div class="form-group">
          <label>Compártenos tu mejor foto de la Semana de la Ciencia</label>
          <button type="button" class="file-upload-button" onclick="document.getElementById('photoUpload').click()">
            📷 Seleccionar Foto
          </button>
          <input type="file" id="photoUpload" name="foto" accept="image/*" style="display: none;">
          <div class="file-name" id="fileName"></div>
        </div>

        <!-- Valoración -->
        <div class="form-group">
          <label>¿Cómo te pareció el evento? *</label>
          <div class="rating-container">
            <div class="rating-option" data-rating="5">⭐⭐⭐⭐⭐</div>
            <div class="rating-option" data-rating="4">⭐⭐⭐⭐</div>
            <div class="rating-option" data-rating="3">⭐⭐⭐</div>
            <div class="rating-option" data-rating="2">⭐⭐</div>
            <div class="rating-option" data-rating="1">⭐</div>
          </div>
          <input type="hidden" id="selectedRating" name="valoracion" required>
        </div>

        <!-- Botón Enviar -->
        <button type="submit" class="submit-btn" id="submitBtn">Enviar mi Experiencia</button>
      </form>
    </div>

    <!-- Slider de Testimonios -->
    <div id="testimonialsContainer" style="display: none;">
      <h3 class="experiencias-title" style="font-size: 2rem; margin-bottom: 40px;">Lo que dicen nuestros participantes</h3>
      <div class="testimonials-slider">
        <button class="slider-nav prev" onclick="slideTestimonials(-1)">‹</button>
        <div class="testimonials-track" id="testimonialsTrack"></div>
        <button class="slider-nav next" onclick="slideTestimonials(1)">›</button>
      </div>
    </div>

  </div>
</section>

<section class="gallery-container">
  <div class="container">
    
    <div class="day-section" id="day1">
      <h2 class="day-title">Día 1 - 30/10/2025</h2>
      <p class="event-info">Tuvo una participación de más de <span class="highlight-stat">150+</span> personas</p>
      <div class="gallery-grid" id="gallery-day1"></div>
    </div>

    <div class="day-section hidden" id="day2">
      <h2 class="day-title">Día 2 - 31/10/2025</h2>
      <p class="event-info">Tuvo una participación de más de <span class="highlight-stat">200+</span> personas</p>
      <div class="gallery-grid" id="gallery-day2"></div>
    </div>

  </div>
</section>

<div class="lightbox" id="lightbox">
  <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
  <button class="lightbox-nav lightbox-prev" onclick="changeImage(-1)">&#10094;</button>
  <div class="lightbox-content">
    <img id="lightbox-img" src="" alt="">
    <video id="lightbox-video" controls style="display: none;">
      <source src="" type="video/mp4">
    </video>
    <button class="lightbox-download" onclick="downloadMedia()">⬇ Descargar</button>
  </div>
  <button class="lightbox-nav lightbox-next" onclick="changeImage(1)">&#10095;</button>
  <div class="lightbox-counter" id="lightbox-counter"></div>
</div>

<script>
  let currentIndex = 0;
  let currentDay = 'day1';
  let mediaFiles = {
    day1: [],
    day2: []
  };

  async function loadMedia() {
    try {
      const response1 = await fetch('<?php echo RUTA; ?>/src/assets/load_media.php?day=1');
      const data1 = await response1.json();
      mediaFiles.day1 = data1;
      displayGallery(data1, 'gallery-day1', 'day1');

      const response2 = await fetch('<?php echo RUTA; ?>/src/assets/load_media.php?day=2');
      const data2 = await response2.json();
      if (data2.length > 0) {
        mediaFiles.day2 = data2;
        document.getElementById('day2').classList.remove('hidden');
        displayGallery(data2, 'gallery-day2', 'day2');
      }
    } catch (error) {
      console.error('Error al cargar los archivos:', error);
    }
  }

  function displayGallery(files, containerId, day) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';

    files.forEach((file, index) => {
      const isVideo = file.type === 'video';
      const item = document.createElement('div');
      item.className = 'gallery-item';
      item.onclick = () => openLightbox(index, day);

      if (isVideo) {
        item.innerHTML = `
          <video muted>
            <source src="${file.path}" type="video/mp4">
          </video>
          <div class="video-badge">📹 VIDEO</div>
        `;
      } else {
        item.innerHTML = `
          <img src="${file.path}" alt="${file.name}">
        `;
      }

      container.appendChild(item);
    });
  }

  function openLightbox(index, day) {
    currentIndex = index;
    currentDay = day;
    const lightbox = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    const video = document.getElementById('lightbox-video');
    const file = mediaFiles[day][index];

    if (file.type === 'video') {
      img.style.display = 'none';
      video.style.display = 'block';
      video.querySelector('source').src = file.path;
      video.load();
    } else {
      video.style.display = 'none';
      img.style.display = 'block';
      img.src = file.path;
    }

    updateCounter();
    lightbox.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    const video = document.getElementById('lightbox-video');
    lightbox.classList.remove('active');
    video.pause();
    document.body.style.overflow = 'auto';
  }

  function changeImage(direction) {
    const files = mediaFiles[currentDay];
    currentIndex += direction;

    if (currentIndex < 0) {
      currentIndex = files.length - 1;
    } else if (currentIndex >= files.length) {
      currentIndex = 0;
    }

    const img = document.getElementById('lightbox-img');
    const video = document.getElementById('lightbox-video');
    const file = files[currentIndex];

    if (file.type === 'video') {
      img.style.display = 'none';
      video.style.display = 'block';
      video.querySelector('source').src = file.path;
      video.load();
    } else {
      video.style.display = 'none';
      img.style.display = 'block';
      img.src = file.path;
    }

    updateCounter();
  }

  function updateCounter() {
    const counter = document.getElementById('lightbox-counter');
    const total = mediaFiles[currentDay].length;
    counter.textContent = `${currentIndex + 1} / ${total}`;
  }

  function downloadMedia() {
    const file = mediaFiles[currentDay][currentIndex];
    const link = document.createElement('a');
    link.href = file.path;
    link.download = file.name;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') changeImage(-1);
    if (e.key === 'ArrowRight') changeImage(1);
  });

  document.getElementById('lightbox').addEventListener('click', (e) => {
    if (e.target.id === 'lightbox') closeLightbox();
  });

  loadMedia();
</script>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
(function() {
  'use strict';
  
  const RUTA = '<?php echo RUTA; ?>';
  const API_URL = RUTA + 'SQL/testimonios.php';
  const avatares = <?php echo $avatares_json; ?>;
  
  let testimonials = [];
  let currentSlideIndex = 0;
  let selectedAvatar = '';
  let selectedRating = '';

  function displayAvatares(avatares) {
    const container = document.getElementById('avatarSelector');
    
    if (!container) {
      console.error('No se encontró el contenedor de avatares');
      return;
    }
    
    if (!Array.isArray(avatares) || avatares.length === 0) {
      console.error('No hay avatares disponibles');
      return;
    }
    
    container.innerHTML = '';

    avatares.forEach(avatar => {
      const div = document.createElement('div');
      div.className = 'avatar-option';
      div.dataset.idAvatar = avatar.Id_Avatar;
      div.innerHTML = `
        <img src="${RUTA}${avatar.Avatar}" 
             alt="Avatar ${avatar.Id_Avatar}" 
             onerror="this.src='${RUTA}src/assets/img/placeholder.jpg'">
        <span class="avatar-label">Avatar ${avatar.Id_Avatar}</span>
      `;
      div.addEventListener('click', () => selectAvatar(avatar.Id_Avatar));
      container.appendChild(div);
    });
  }

  function selectAvatar(idAvatar) {
    document.querySelectorAll('.testimonios-section .avatar-option').forEach(opt => {
      opt.classList.remove('selected');
    });
    
    const selectedElement = document.querySelector(`.testimonios-section [data-id-avatar="${idAvatar}"]`);
    if (selectedElement) {
      selectedElement.classList.add('selected');
      selectedAvatar = idAvatar;
      
      const hiddenInput = document.getElementById('selectedAvatar');
      if (hiddenInput) {
        hiddenInput.value = idAvatar;
      }
    }
  }

  async function loadTestimonials() {
    try {
      const response = await fetch(API_URL + '?action=get_testimonios');
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const data = await response.json();
      
      if (!Array.isArray(data)) {
        console.error('Los testimonios no son un array:', data);
        return;
      }
      
      testimonials = data;
      
      if (testimonials.length > 0) {
        const container = document.getElementById('testimonialsContainer');
        if (container) {
          container.style.display = 'block';
          renderTestimonials();
        }
      }
    } catch (error) {
      console.error('Error al cargar testimonios:', error);
    }
  }

  function renderTestimonials() {
    const track = document.getElementById('testimonialsTrack');
    
    if (!track) {
      console.error('No se encontró el contenedor de testimonios');
      return;
    }
    
    track.innerHTML = '';
    currentSlideIndex = 0;
    track.style.transform = 'translateX(0px)';

    testimonials.forEach((testimonial, index) => {
      const card = document.createElement('div');
      card.className = 'testimonial-card';
      card.onclick = () => openTestimonialModal(index);

      const stars = '⭐'.repeat(testimonial.rating);
      
      let photoHtml = '';
      if (testimonial.photo) {
        const photoSrc = testimonial.photo.startsWith('src/') 
          ? RUTA + testimonial.photo 
          : testimonial.photo;
        photoHtml = `<img src="${photoSrc}" class="testimonial-photo" alt="Foto" onerror="this.style.display='none'">`;
      }

      card.innerHTML = `
        <div class="testimonial-header">
          <div class="testimonial-avatar">
            <img src="${RUTA}${testimonial.avatar_url}" alt="Avatar" onerror="this.src='${RUTA}src/assets/img/placeholder.jpg'">
          </div>
          <div>
            <div class="testimonial-name">${testimonial.name}</div>
            <div class="testimonial-rating">${stars}</div>
          </div>
        </div>
        <div class="testimonial-text">${testimonial.opinion}</div>
        ${photoHtml}
      `;

      track.appendChild(card);
    });
  }

  window.slideTestimonials = function(direction) {
    const track = document.getElementById('testimonialsTrack');
    if (!track || testimonials.length === 0) return;
    
    const cardWidth = 405;
    const visibleCards = window.innerWidth < 768 ? 1 : 3;
    const totalCards = testimonials.length;
    const maxIndex = Math.max(0, totalCards - visibleCards);
    
    currentSlideIndex += direction;
    
    if (currentSlideIndex < 0) {
      currentSlideIndex = 0;
    } else if (currentSlideIndex > maxIndex) {
      currentSlideIndex = maxIndex;
    }
    
    const translateX = -(currentSlideIndex * cardWidth);
    track.style.transform = `translateX(${translateX}px)`;
    
    console.log('Slide:', currentSlideIndex, 'de', maxIndex, 'translateX:', translateX);
  };

  function openTestimonialModal(index) {
    const t = testimonials[index];
    if (!t) return;
    
    const stars = '⭐'.repeat(t.rating);
    
    let photoHtml = '';
    if (t.photo) {
      const photoSrc = t.photo.startsWith('src/') 
        ? RUTA + t.photo 
        : t.photo;
      photoHtml = `<img src="${photoSrc}" style="width: 100%; border-radius: 10px; margin: 15px 0;" alt="Foto" onerror="this.style.display='none'">`;
    }

    Swal.fire({
      title: t.name,
      html: `
        <div style="text-align: left;">
          <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
            <img src="${RUTA}${t.avatar_url}" 
                 style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;" 
                 alt="Avatar"
                 onerror="this.src='${RUTA}src/assets/img/placeholder.jpg'">
            <div>
              <div style="font-size: 1.2rem; font-weight: bold;">${t.name}</div>
              <div style="color: #ffd700; font-size: 1.1rem;">${stars}</div>
              <div style="color: #999; font-size: 0.9rem;">${t.date}</div>
            </div>
          </div>
          ${photoHtml}
          <h4 style="color: #667eea; margin: 15px 0 10px 0;">Opinión:</h4>
          <p style="line-height: 1.6; color: #333;">${t.opinion}</p>
        </div>
      `,
      width: 600,
      showCloseButton: true,
      showConfirmButton: false,
      background: '#fff',
      customClass: {
        popup: 'testimonial-modal-swal'
      }
    });
  }

  const ratingOptions = document.querySelectorAll('.testimonios-section .rating-option');
  ratingOptions.forEach(option => {
    option.addEventListener('click', function() {
      ratingOptions.forEach(opt => opt.classList.remove('selected'));
      this.classList.add('selected');
      selectedRating = this.dataset.rating;
      
      const hiddenInput = document.getElementById('selectedRating');
      if (hiddenInput) {
        hiddenInput.value = selectedRating;
      }
    });
  });

  const photoUpload = document.getElementById('photoUpload');
  if (photoUpload) {
    photoUpload.addEventListener('change', function(e) {
      const file = e.target.files[0];
      const fileName = document.getElementById('fileName');
      
      if (file && fileName) {
        fileName.textContent = '✅ ' + file.name;
      }
    });
  }

  const experienceForm = document.getElementById('experienceForm');
  
  if (experienceForm) {
    experienceForm.addEventListener('submit', async function(e) {
      e.preventDefault();

      if (!selectedAvatar) {
        Swal.fire({
          icon: 'warning',
          title: 'Avatar requerido',
          text: 'Por favor selecciona un avatar',
          confirmButtonColor: '#667eea'
        });
        return;
      }

      if (!selectedRating) {
        Swal.fire({
          icon: 'warning',
          title: 'Valoración requerida',
          text: 'Por favor selecciona una valoración',
          confirmButtonColor: '#667eea'
        });
        return;
      }

      const submitBtn = document.getElementById('submitBtn');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Enviando...';
      }

      const formData = new FormData(this);

      try {
        const response = await fetch(API_URL + '?action=save_testimonio', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          await Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: result.message,
            confirmButtonColor: '#667eea'
          });
          
          await loadTestimonials();
          
          this.reset();
          document.querySelectorAll('.testimonios-section .avatar-option').forEach(opt => {
            opt.classList.remove('selected');
          });
          document.querySelectorAll('.testimonios-section .rating-option').forEach(opt => {
            opt.classList.remove('selected');
          });
          
          const fileNameElement = document.getElementById('fileName');
          if (fileNameElement) {
            fileNameElement.textContent = '';
          }
          
          selectedAvatar = '';
          selectedRating = '';
          
          const testimonialsContainer = document.getElementById('testimonialsContainer');
          if (testimonialsContainer) {
            testimonialsContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: result.message,
            confirmButtonColor: '#667eea'
          });
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Hubo un error al enviar tu experiencia',
          confirmButtonColor: '#667eea'
        });
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Enviar mi Experiencia';
        }
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      displayAvatares(avatares);
      loadTestimonials();
    });
  } else {
    displayAvatares(avatares);
    loadTestimonials();
  }
  
})();
</script>

<?php include '../inc/footer.php'; ?>
