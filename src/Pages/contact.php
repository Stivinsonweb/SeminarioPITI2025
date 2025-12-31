<?php include 'includes/head.php'; ?>
<?php include 'includes/header.php'; ?>

<main class="container my-5">
  <h2 class="fw-bold">Contáctanos</h2>
  <form class="mt-3">
    <div class="mb-3">
      <label for="name" class="form-label">Nombre</label>
      <input type="text" class="form-control" id="name" placeholder="Escribe tu nombre">
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Correo</label>
      <input type="email" class="form-control" id="email" placeholder="ejemplo@correo.com">
    </div>
    <div class="mb-3">
      <label for="message" class="form-label">Mensaje</label>
      <textarea class="form-control" id="message" rows="4"></textarea>
    </div>
    <button type="submit" class="btn btn-success">Enviar</button>
  </form>
</main>

<?php include 'includes/footer.php'; ?>
