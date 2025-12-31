<?php
include '../../ruta.php';
include '../inc/head.php';
include '../inc/header.php';
include '../../SQL/db_connect.php';

try {
    $stmt = $pdo->prepare("SELECT Id_Habilitar_preinscripcion FROM usuario WHERE Usuario = 'Admin' LIMIT 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $preinscripcionHabilitada = false;
    if ($config && isset($config['Id_Habilitar_preinscripcion'])) {
        $preinscripcionHabilitada = ($config['Id_Habilitar_preinscripcion'] == 1 || $config['Id_Habilitar_preinscripcion'] == '1');
    }
} catch (PDOException $e) {
    $preinscripcionHabilitada = false;
    error_log("Error consultando estado de preinscripción: " . $e->getMessage());
}

try {
    $stmt = $pdo->prepare("SELECT ID, Tipo_documento FROM tipo_documento ORDER BY ID");
    $stmt->execute();
    $tiposDocumento = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tiposDocumento = [];
}

try {
    $stmt = $pdo->prepare("SELECT ID, Tipo_Ponente FROM tipo_ponente ORDER BY ID");
    $stmt->execute();
    $tiposPonente = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tiposPonente = [];
}

try {
    $stmt = $pdo->prepare("SELECT ID, Estudios FROM estudios_realizados ORDER BY ID");
    $stmt->execute();
    $estudiosRealizados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $estudiosRealizados = [];
}

try {
    $stmt = $pdo->prepare("SELECT ID, Tematicas FROM tematica ORDER BY ID");
    $stmt->execute();
    $tematicas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tematicas = [];
}
?>

<section class="video-hero">
  <video autoplay muted loop class="video-background">
    <source src="<?php echo RUTA; ?>/src/assets/video/tecnologia.mp4" type="video/mp4">
    Tu navegador no soporta videos en HTML5.
  </video>
  <div class="video-overlay"></div>
  <div class="hero-content">
    <div class="container text-center">
      <h1 class="display-4 fw-bold text-white hero-main-title">Preinscripción</h1>
      <p class="lead text-white">Lo invitamos a realizar la preinscripción en la modalidad de <b>asistente</b> o <b>ponente</b>. Recuerde ingresar sus datos correctamente.</p>
    </div>
  </div>
</section>

<section class="preinscripcion-section py-5">
  <div class="container">
    
    <div class="alert <?php echo $preinscripcionHabilitada ? 'alert-success' : 'alert-danger'; ?> text-center" role="alert">
        <h4 class="mb-0">
            <?php if ($preinscripcionHabilitada): ?>
                <i class="bi bi-check-circle me-2"></i>Inscripciones Abiertas
            <?php else: ?>
                <i class="bi bi-x-circle me-2"></i>Inscripciones Cerradas
            <?php endif; ?>
        </h4>
    </div>

    <?php if (!$preinscripcionHabilitada): ?>
        <style>
            .formulario-deshabilitado {
                opacity: 0.6;
                pointer-events: none;
                user-select: none;
            }
        </style>
    <?php endif; ?>

    <h2 class="text-center mb-5 fw-bold">Formulario de Preinscripción</h2>

    <div class="form-selector text-center mb-4 <?php echo !$preinscripcionHabilitada ? 'formulario-deshabilitado' : ''; ?>">
      <div class="btn-group w-100" role="group">
        <button type="button" class="btn btn-outline-primary btn-lg" onclick="mostrarFormulario('participante')" <?php echo !$preinscripcionHabilitada ? 'disabled' : ''; ?>>
          <i class="bi bi-person-check me-2"></i>
          Inscripción como Participante
        </button>
        <button type="button" class="btn btn-outline-secondary btn-lg" onclick="mostrarFormulario('ponente')" <?php echo !$preinscripcionHabilitada ? 'disabled' : ''; ?>>
          <i class="bi bi-mic me-2"></i>
          Inscripción como ponente
        </button>
      </div>
    </div>

    <div id="form-participante" class="formulario-container <?php echo !$preinscripcionHabilitada ? 'formulario-deshabilitado' : ''; ?>">
      <div class="form-card">
        <div class="form-header mb-4">
          <h3 class="text-primary">
            <i class="bi bi-person-check me-2"></i>
            Inscripción como Participante
          </h3>
        </div>

        <form id="formParticipante" class="needs-validation" method="POST" novalidate>
          <input type="hidden" name="rol" value="participante">
          <fieldset <?php echo !$preinscripcionHabilitada ? 'disabled' : ''; ?>>

          <div class="row">
            <div class="col-md-3 mb-3">
              <label for="tipoDocP" class="form-label">Documento *</label>
              <select id="tipoDocP" name="tipoDoc" class="form-select" required>
                <option value="">Selecciona...</option>
                <?php foreach ($tiposDocumento as $tipo): ?>
                    <option value="<?php echo $tipo['ID']; ?>"><?php echo htmlspecialchars($tipo['Tipo_documento']); ?></option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Por favor selecciona un tipo de documento.</div>
            </div>

            <div class="col-md-3 mb-3">
              <label for="numDocP" class="form-label">N°. documento *</label>
              <input type="text" id="numDocP" name="numDoc" class="form-control"
                required pattern="[0-9]{6,10}" maxlength="10"
                placeholder="Numero de documento">
              <div class="invalid-feedback">Ingresa un número válido (6-10 dígitos).</div>
            </div>

            <div class="col-md-3 mb-3">
              <label for="nombreP" class="form-label">Nombre *</label>
              <input type="text" id="nombreP" name="nombre" class="form-control" required placeholder="Tu nombre (sin tildes)">
              <div class="invalid-feedback">Por favor ingresa tu nombre.</div>
            </div>

            <div class="col-md-3 mb-3">
              <label for="apellidoP" class="form-label">Apellido *</label>
              <input type="text" id="apellidoP" name="apellido" class="form-control" required placeholder="Tu apellido (sin tildes)">
              <div class="invalid-feedback">Por favor ingresa tu apellido.</div>
            </div>
          </div>

          <div class="form-check mb-4">
            <input type="checkbox" id="aceptaP" class="form-check-input" required>
            <label class="form-check-label" for="aceptaP">
              Acepto el <a href="#" data-bs-toggle="modal" data-bs-target="#modalTerminos">tratamiento de datos</a> *
            </label>
            <div class="invalid-feedback">Debes aceptar el tratamiento de datos.</div>
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-success btn-lg" id="btnSubmitP" <?php echo !$preinscripcionHabilitada ? 'disabled' : ''; ?>>
              <i class="bi bi-send me-2"></i>
              Enviar Inscripción
            </button>
          </div>

          </fieldset>
        </form>
      </div>
    </div>

    <div id="form-ponente" class="formulario-container <?php echo !$preinscripcionHabilitada ? 'formulario-deshabilitado' : ''; ?>" style="display: none;">
      <div class="form-card">
        <div class="form-header mb-4">
          <h3 class="text-secondary">
            <i class="bi bi-mic me-2"></i>
            Inscripción como ponente
          </h3>
        </div>

        <form id="formPonente" class="needs-validation" method="POST" novalidate enctype="multipart/form-data">
          <input type="hidden" name="rol" value="ponente">
          <fieldset <?php echo !$preinscripcionHabilitada ? 'disabled' : ''; ?>>

          <div class="row">
            <div class="col-md-3 mb-3">
              <label for="tipoDocE" class="form-label">Documento *</label>
              <select id="tipoDocE" name="tipoDoc" class="form-select" required>
                <option value="">Selecciona...</option>
                <?php foreach ($tiposDocumento as $tipo): ?>
                    <option value="<?php echo $tipo['ID']; ?>"><?php echo htmlspecialchars($tipo['Tipo_documento']); ?></option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Por favor selecciona un tipo de documento.</div>
            </div>

            <div class="col-md-3 mb-3">
              <label for="numDocE" class="form-label">N°. documento *</label>
              <input type="text" id="numDocE" name="numDoc" class="form-control"
                required pattern="[0-9]{6,10}" maxlength="10"
                placeholder="Numero de documento">
              <div class="invalid-feedback">Ingresa un número válido (6-10 dígitos).</div>
            </div>

            <div class="col-md-3 mb-3">
              <label for="nombreE" class="form-label">Nombre *</label>
              <input type="text" id="nombreE" name="nombre" class="form-control" required placeholder="Tu nombre (sin tildes)">
              <div class="invalid-feedback">Por favor ingresa tu nombre.</div>
            </div>

            <div class="col-md-3 mb-3">
              <label for="apellidoE" class="form-label">Apellido *</label>
              <input type="text" id="apellidoE" name="apellido" class="form-control" required placeholder="Tu apellido (sin tildes)">
              <div class="invalid-feedback">Por favor ingresa tu apellido.</div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="correoE" class="form-label">Correo Electrónico *</label>
              <input type="email" id="correoE" name="correo" class="form-control" required placeholder="correo@ejemplo.com">
              <div class="invalid-feedback">Ingresa un correo válido.</div>
            </div>

            <div class="col-md-6 mb-3">
              <label for="telefonoE" class="form-label">Teléfono de contacto *</label>
              <input type="tel" id="telefonoE" name="telefono" class="form-control" 
                required pattern="[0-9]{7,10}" maxlength="10" placeholder="3001234567">
              <div class="invalid-feedback">Ingresa un teléfono válido (7-10 dígitos).</div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="tipoPonente" class="form-label">Tipo de ponente *</label>
              <select id="tipoPonente" name="tipoPonente" class="form-select" required>
                <option value="">Selecciona el tipo de ponente...</option>
                <?php foreach ($tiposPonente as $tipo): ?>
                    <option value="<?php echo $tipo['ID']; ?>"><?php echo htmlspecialchars($tipo['Tipo_Ponente']); ?></option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Por favor selecciona un tipo de ponente.</div>
            </div>
          </div>

          <div id="camposEgresado" style="display: none;">
            <hr class="my-4">
            <h5 class="mb-3 text-secondary">Información Adicional para Egresados</h5>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="fechaGraduacion" class="form-label">Fecha de graduación *</label>
                <input type="date" id="fechaGraduacion" name="fechaGraduacion" class="form-control">
                <div class="invalid-feedback">Ingresa una fecha válida.</div>
              </div>

              <div class="col-md-6 mb-3">
                <label for="ultimoEstudio" class="form-label">Último estudios realizados *</label>
                <select id="ultimoEstudio" name="ultimoEstudio" class="form-select">
                  <option value="">Selecciona...</option>
                  <?php foreach ($estudiosRealizados as $estudio): ?>
                      <option value="<?php echo $estudio['ID']; ?>"><?php echo htmlspecialchars($estudio['Estudios']); ?></option>
                  <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Selecciona tu último nivel de estudios.</div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="ciudadResidencia" class="form-label">¿En qué ciudad resides? *</label>
                <input type="text" id="ciudadResidencia" name="ciudadResidencia" class="form-control" placeholder="Bogotá">
                <div class="invalid-feedback">Ingresa la ciudad donde resides.</div>
              </div>

              <div class="col-md-6 mb-3">
                <label for="cargo" class="form-label">Ocupación actual / cargo *</label>
                <input type="text" id="cargo" name="cargo" class="form-control" placeholder="Ingeniero de Software">
                <div class="invalid-feedback">Ingresa tu ocupación o cargo actual.</div>
              </div>
            </div>

            <div class="mb-3">
              <label for="empresa" class="form-label">Empresa o institución donde labora *</label>
              <input type="text" id="empresa" name="empresa" class="form-control" placeholder="Nombre de la empresa">
              <div class="invalid-feedback">Ingresa el nombre de tu empresa o institución.</div>
            </div>

            <div class="mb-3">
              <label for="experiencia" class="form-label">Breve descripción de su experiencia profesional *</label>
              <textarea id="experiencia" name="experiencia" class="form-control" rows="4" 
                maxlength="1800" placeholder="Describe tu experiencia profesional (máx. 300 palabras)"></textarea>
              <div class="form-text">
                <span id="contadorPalabras">0</span> / 300 palabras
              </div>
              <div class="invalid-feedback">La experiencia es obligatoria y no puede superar 300 palabras.</div>
            </div>

            <div class="mb-3">
              <label for="tematicaEgresado" class="form-label">Temáticas de interés para participar *</label>
              <select id="tematicaEgresado" name="tematicaEgresado" class="form-select">
                <option value="">Selecciona una temática...</option>
                <?php foreach ($tematicas as $tem): ?>
                    <option value="<?php echo $tem['ID']; ?>"><?php echo htmlspecialchars($tem['Tematicas']); ?></option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Selecciona una temática de interés.</div>
            </div>

            <div class="mb-3">
              <label for="tituloPresentacionEgresado" class="form-label">Título de la presentación *</label>
              <input type="text" id="tituloPresentacionEgresado" name="tituloPresentacionEgresado" class="form-control" 
                maxlength="200" placeholder="Título de tu presentación">
              <div class="invalid-feedback">El título de la presentación es obligatorio.</div>
            </div>

            <div class="mb-3">
              <label for="hojaVida" class="form-label">Hoja de vida resumida (máx. 2 páginas) *</label>
              <input type="file" id="hojaVida" name="hojaVida" class="form-control" accept=".pdf">
              <div class="form-text">Formato aceptado: PDF. Máx 5MB</div>
              <div class="invalid-feedback">Por favor selecciona tu hoja de vida en PDF.</div>
            </div>

            <div class="mb-3">
              <label for="diapositivasEgresado" class="form-label">Diapositivas de la presentación *</label>
              <input type="file" id="diapositivasEgresado" name="diapositivasEgresado" class="form-control" accept=".pdf,.ppt,.pptx">
              <div class="form-text">Formatos aceptados: PDF, PPT, PPTX. Máx 10MB</div>
              <div class="invalid-feedback">Por favor selecciona el archivo de diapositivas.</div>
            </div>

            <div class="mb-3">
              <label for="motivacion" class="form-label">¿Por qué desea participar como panelista en este seminario? *</label>
              <textarea id="motivacion" name="motivacion" class="form-control" rows="4" 
                maxlength="1500" placeholder="Explica tu motivación para participar"></textarea>
              <div class="form-text">Máximo 250 palabras</div>
              <div class="invalid-feedback">La motivación es obligatoria.</div>
            </div>
          </div>

          <div id="camposNacional" style="display: none;">
            <hr class="my-4">
            <h5 class="mb-3 text-secondary">Información Adicional para Ponente Nacional</h5>

            <div class="mb-3">
              <label for="tematicaNacional" class="form-label">Temática *</label>
              <select id="tematicaNacional" name="tematicaNacional" class="form-select">
                <option value="">Selecciona una temática...</option>
                <?php foreach ($tematicas as $tem): ?>
                    <option value="<?php echo $tem['ID']; ?>"><?php echo htmlspecialchars($tem['Tematicas']); ?></option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Selecciona una temática.</div>
            </div>

            <div class="mb-3">
              <label for="tituloPresentacionNacional" class="form-label">Título de la presentación *</label>
              <input type="text" id="tituloPresentacionNacional" name="tituloPresentacionNacional" class="form-control" 
                maxlength="200" placeholder="Título de tu presentación">
              <div class="invalid-feedback">El título de la presentación es obligatorio.</div>
            </div>

            <div class="mb-3">
              <label for="diapositivasNacional" class="form-label">Diapositivas de la presentación *</label>
              <input type="file" id="diapositivasNacional" name="diapositivasNacional" class="form-control" accept=".pdf,.ppt,.pptx">
              <div class="form-text">Formatos aceptados: PDF, PPT, PPTX. Máx 10MB</div>
              <div class="invalid-feedback">Por favor selecciona el archivo de diapositivas.</div>
            </div>
          </div>

          <div class="form-check mb-4">
            <input type="checkbox" id="aceptaE" class="form-check-input" required>
            <label class="form-check-label" for="aceptaE">
              Acepto el <a href="#" data-bs-toggle="modal" data-bs-target="#modalTerminos">tratamiento de datos</a> *
            </label>
            <div class="invalid-feedback">Debes aceptar el tratamiento de datos.</div>
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-success btn-lg" id="btnSubmitE" <?php echo !$preinscripcionHabilitada ? 'disabled' : ''; ?>>
              <i class="bi bi-send me-2"></i>
              Enviar Inscripción
            </button>
          </div>

          </fieldset>
        </form>
      </div>
    </div>

  </div>
</section>

<div class="modal fade" id="modalTerminos" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-black">Autorización de uso de información e imagen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-black text-justify">Al enviar este formulario, autorizo a la Universidad Tecnológica del Chocó a utilizar mi información y fotografía con fines académicos, logísticos y de divulgación del evento, conforme a la Ley 1581 de 2012 sobre protección de datos personales.</p>
      </div>
    </div>
  </div>
</div>

<script>
const preinscripcionHabilitada = <?php echo $preinscripcionHabilitada ? 'true' : 'false'; ?>;
</script>

<?php
include '../inc/footer.php';
?>