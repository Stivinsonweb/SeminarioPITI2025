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

  <!-- Login Minimalista -->
  <div class="login-container">
    <div class="login-form">
      
      <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <span>
            <?php 
              switch($_GET['error']) {
                case 'invalid':
                  echo 'Usuario o contraseña incorrectos';
                  break;
                case 'empty':
                  echo 'Complete todos los campos';
                  break;
                case 'system':
                  echo 'Error del sistema';
                  break;
                default:
                  echo 'Error desconocido';
              }
            ?>
          </span>
        </div>
      <?php endif; ?>

      <?php if(isset($_GET['message']) && $_GET['message'] == 'logout_success'): ?>
        <div class="alert alert-success">
          <i class="bi bi-check-circle-fill"></i>
          <span>Sesión cerrada correctamente</span>
        </div>
      <?php endif; ?>

      <form action="../../SQL/auth.php" method="POST" id="loginForm">
        
        <div class="form-group">
          <label for="usuario">
            <i class="bi bi-person-fill"></i>
            Usuario
          </label>
          <input type="text" id="usuario" name="usuario" required placeholder="Ingrese su usuario" autocomplete="username">
        </div>
        
        <div class="form-group">
          <label for="password">
            <i class="bi bi-lock-fill"></i>
            Contraseña
          </label>
          <div class="password-container">
            <input type="password" id="password" name="password" required placeholder="Ingrese su contraseña" autocomplete="current-password">
            <button type="button" class="toggle-password" onclick="togglePassword()">
              <i id="toggle-icon" class="bi bi-eye"></i>
            </button>
          </div>
        </div>
        
        <button type="submit" class="btn-login">
          <span>Iniciar Sesión</span>
          <i class="bi bi-arrow-right"></i>
        </button>
        
      </form>
    </div>
  </div>
</section>

<style>
:root {
  --primary-color: #6366f1;
  --error-color: #ef4444;
  --success-color: #10b981;
  --text-dark: #1f2937;
  --text-medium: #6b7280;
  --text-light: #9ca3af;
  --bg-white: rgba(255, 255, 255, 0.95);
  --border-color: rgba(255, 255, 255, 0.2);
}

* {
  box-sizing: border-box;
}

.video-background {
  filter: brightness(0.4) contrast(1.2);
}

.video-overlay {
  background: linear-gradient(135deg, 
    rgba(99, 102, 241, 0.3) 0%, 
    rgba(139, 92, 246, 0.3) 50%, 
    rgba(16, 185, 129, 0.2) 100%);
}

.login-container {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 10;
  width: 100%;
  max-width: 350px;
  padding: 0 20px;
}

.login-form {
  background: var(--bg-white);
  backdrop-filter: blur(20px);
  border: 1px solid var(--border-color);
  padding: 1.25rem;
  border-radius: 12px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.alert {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border-radius: 8px;
  font-size: 0.85rem;
  margin-bottom: 1rem;
  animation: slideDown 0.4s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.alert-error {
  background: #fef2f2;
  color: var(--error-color);
  border: 1px solid #fecaca;
}

.alert-success {
  background: #ecfdf5;
  color: var(--success-color);
  border: 1px solid #a7f3d0;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group:last-of-type {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  color: var(--text-dark);
  font-weight: 600;
  font-size: 0.9rem;
}

.form-group label i {
  color: var(--primary-color);
  font-size: 1rem;
}

.form-group input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.9);
  color: var(--text-dark);
}

.form-group input::placeholder {
  color: var(--text-light);
}

.form-group input:focus {
  outline: none;
  border-color: var(--primary-color);
  background: white;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.password-container {
  position: relative;
}

.toggle-password {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: var(--text-medium);
  font-size: 1rem;
  padding: 0.25rem;
  border-radius: 4px;
  transition: color 0.3s ease;
}

.toggle-password:hover {
  color: var(--primary-color);
}

.btn-login {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.8rem 1.25rem;
  background: linear-gradient(135deg, var(--primary-color) 0%, #8b5cf6 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 0.25rem;
}

.btn-login:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
}

.btn-login:active {
  transform: translateY(0);
}

.btn-login i {
  transition: transform 0.3s ease;
}

.btn-login:hover i {
  transform: translateX(2px);
}

/* Responsive */
@media (max-width: 480px) {
  .login-container {
    max-width: 320px;
    padding: 0 15px;
  }
  
  .login-form {
    padding: 1rem;
  }
  
  .form-group input {
    padding: 0.7rem 0.9rem;
    font-size: 0.85rem;
  }
  
  .btn-login {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
  }
}
</style>

<script>
function togglePassword() {
  const passwordInput = document.getElementById('password');
  const toggleIcon = document.getElementById('toggle-icon');
  
  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    toggleIcon.className = 'bi bi-eye-slash';
  } else {
    passwordInput.type = 'password';
    toggleIcon.className = 'bi bi-eye';
  }
}

// Enfoque automático
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('usuario').focus();
});

// Validación del formulario
document.getElementById('loginForm').addEventListener('submit', function(e) {
  const usuario = document.getElementById('usuario').value.trim();
  const password = document.getElementById('password').value.trim();
  
  if (!usuario || !password) {
    e.preventDefault();
    alert('Por favor complete todos los campos');
  }
});
</script>

<?php include '../inc/footer.php'; ?>