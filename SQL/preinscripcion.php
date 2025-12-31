<?php
include 'db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT Id_Habilitar_preinscripcion FROM usuario WHERE Usuario = 'Admin' LIMIT 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config || !isset($config['Id_Habilitar_preinscripcion']) || $config['Id_Habilitar_preinscripcion'] != 1) {
        throw new Exception('Las inscripciones no están habilitadas en este momento');
    }
    
    $rol = $_POST['rol'] ?? '';
    $idTipoDoc = $_POST['tipoDoc'] ?? '';
    $numDoc = trim($_POST['numDoc'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    
    if (empty($rol) || empty($idTipoDoc) || empty($numDoc) || empty($nombre) || empty($apellido)) {
        throw new Exception('Todos los campos obligatorios deben ser completados');
    }
    
    if (!in_array($rol, ['participante', 'ponente'])) {
        throw new Exception('Rol no válido');
    }
    
    if (!preg_match('/^[0-9]{6,10}$/', $numDoc)) {
        throw new Exception('El número de documento debe tener entre 6 y 10 dígitos');
    }
    
    $stmt = $pdo->prepare("SELECT ID FROM tipo_documento WHERE ID = ?");
    $stmt->execute([$idTipoDoc]);
    if (!$stmt->fetch()) {
        throw new Exception('Tipo de documento no válido');
    }
    
    $stmt = $pdo->prepare("SELECT ID FROM participante WHERE Numero_de_documento = ?");
    $stmt->execute([$numDoc]);
    $existeParticipante = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT ID FROM ponentes WHERE Numero_de_documento = ?");
    $stmt->execute([$numDoc]);
    $existePonente = $stmt->fetch();
    
    if ($existeParticipante && $existePonente) {
        throw new Exception('Ya tienes inscripciones en ambas modalidades. Una persona solo puede estar inscrita en una modalidad.');
    }
    
    if ($rol === 'participante' && $existeParticipante) {
        throw new Exception('Ya estás inscrito como participante. No puedes inscribirte nuevamente.');
    }
    
    if ($rol === 'ponente' && $existePonente) {
        throw new Exception('Ya estás inscrito como ponente. No puedes inscribirte nuevamente.');
    }
    
    if ($rol === 'participante' && $existePonente) {
        throw new Exception('Ya estás inscrito como ponente. No puedes inscribirte como participante.');
    }
    
    if ($rol === 'ponente' && $existeParticipante) {
        throw new Exception('Ya estás inscrito como participante. No puedes inscribirte como ponente.');
    }
    
    if ($rol === 'participante') {
        
        $stmt = $pdo->prepare("
            INSERT INTO participante (
                Id_Tipo_documento, 
                Nombre, 
                Apellido, 
                Numero_de_documento, 
                Acepto_terminoycondicciones, 
                Fecha_de_inscripcion
            ) 
            VALUES (?, ?, ?, ?, 'SI', NOW())
        ");
        $stmt->execute([$idTipoDoc, $nombre, $apellido, $numDoc]);
        
        echo json_encode([
            'success' => true,
            'message' => '¡Inscripción como participante realizada exitosamente! Bienvenido(a) ' . $nombre . ' ' . $apellido,
            'tipo' => 'participante'
        ]);
        
    } 
    elseif ($rol === 'ponente') {
        
        $tipoPonente = $_POST['tipoPonente'] ?? '';
        $correo = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        
        if (empty($tipoPonente)) {
            throw new Exception('Debe seleccionar un tipo de ponente');
        }
        
        if (empty($correo) || empty($telefono)) {
            throw new Exception('El correo y el teléfono son obligatorios');
        }
        
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El correo electrónico no es válido');
        }
        
        if (!preg_match('/^[0-9]{7,10}$/', $telefono)) {
            throw new Exception('El teléfono debe tener entre 7 y 10 dígitos');
        }
        
        $stmt = $pdo->prepare("SELECT ID FROM tipo_ponente WHERE ID = ?");
        $stmt->execute([$tipoPonente]);
        if (!$stmt->fetch()) {
            throw new Exception('Tipo de ponente no válido');
        }
        
        $pdo->beginTransaction();
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO ponentes (
                    Id_Tipo_documento, 
                    Numero_de_documento,
                    Correo_electronico,
                    Telefono,
                    Nombre, 
                    Apellido, 
                    Id_Tipo_Ponente,
                    Acepto_termino_y_Condicciones, 
                    Fecha_de_inscripcion
                ) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'SI', NOW())
            ");
            $stmt->execute([$idTipoDoc, $numDoc, $correo, $telefono, $nombre, $apellido, $tipoPonente]);
            
            $idPonente = $pdo->lastInsertId();
            
            if ($tipoPonente == '1') {
                
                $tematica = $_POST['tematicaNacional'] ?? '';
                $tituloPresentacion = trim($_POST['tituloPresentacionNacional'] ?? '');
                
                if (empty($tematica) || empty($tituloPresentacion)) {
                    throw new Exception('Todos los campos de ponente nacional son obligatorios');
                }
                
                if (!isset($_FILES['diapositivasNacional']) || $_FILES['diapositivasNacional']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Debe subir el archivo de diapositivas');
                }
                
                $archivo = $_FILES['diapositivasNacional'];
                
                if ($archivo['size'] > 10 * 1024 * 1024) {
                    throw new Exception('Las diapositivas no pueden superar los 10MB');
                }
                
                $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                
                if (!in_array($extension, ['pdf', 'ppt', 'pptx'])) {
                    throw new Exception('Solo se permiten archivos PDF, PPT o PPTX para las diapositivas');
                }
                
                $uploadDir = __DIR__ . '/../src/uploads/Nacional/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $safeName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $numDoc . '_' . basename($archivo['name']));
                $uniqueName = date('Ymd_His') . '_' . $safeName;
                $rutaFinal = $uploadDir . $uniqueName;
                
                if (!move_uploaded_file($archivo['tmp_name'], $rutaFinal)) {
                    throw new Exception('Error al guardar las diapositivas en el servidor');
                }
                
                $urlArchivo = '/src/uploads/Nacional/' . $uniqueName;
                
                $stmt = $pdo->prepare("
                    INSERT INTO ponente_nacional (
                        Id_Ponentes,
                        Id_Tematica,
                        Titulo_de_la_presentacion,
                        Presentacion
                    ) VALUES (?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $idPonente,
                    $tematica,
                    $tituloPresentacion,
                    $urlArchivo
                ]);
                
                $mensaje = '¡Inscripción como ponente nacional realizada exitosamente! Bienvenido(a) ' . $nombre . ' ' . $apellido;
                
            } 
            elseif ($tipoPonente == '2') {
                
                $fechaGraduacion = $_POST['fechaGraduacion'] ?? '';
                $ultimoEstudio = $_POST['ultimoEstudio'] ?? '';
                $ciudadResidencia = trim($_POST['ciudadResidencia'] ?? '');
                $cargo = trim($_POST['cargo'] ?? '');
                $empresa = trim($_POST['empresa'] ?? '');
                $experiencia = trim($_POST['experiencia'] ?? '');
                $tematica = $_POST['tematicaEgresado'] ?? '';
                $tituloPresentacion = trim($_POST['tituloPresentacionEgresado'] ?? '');
                $motivacion = trim($_POST['motivacion'] ?? '');
                
                if ($fechaGraduacion === '' || $ultimoEstudio === '' || $ciudadResidencia === '' || 
                    $cargo === '' || $empresa === '' || $experiencia === '' || $tematica === '' || 
                    $tituloPresentacion === '' || $motivacion === '') {
                    
                    $camposFaltantes = [];
                    if ($fechaGraduacion === '') $camposFaltantes[] = 'Fecha de graduación';
                    if ($ultimoEstudio === '') $camposFaltantes[] = 'Último estudio';
                    if ($ciudadResidencia === '') $camposFaltantes[] = 'Ciudad de residencia';
                    if ($cargo === '') $camposFaltantes[] = 'Cargo';
                    if ($empresa === '') $camposFaltantes[] = 'Empresa';
                    if ($experiencia === '') $camposFaltantes[] = 'Experiencia';
                    if ($tematica === '') $camposFaltantes[] = 'Temática';
                    if ($tituloPresentacion === '') $camposFaltantes[] = 'Título de presentación';
                    if ($motivacion === '') $camposFaltantes[] = 'Motivación';
                    
                    throw new Exception('Faltan los siguientes campos: ' . implode(', ', $camposFaltantes));
                }
                
                $palabrasExperiencia = str_word_count($experiencia);
                if ($palabrasExperiencia > 300) {
                    throw new Exception('La experiencia profesional no puede superar 300 palabras');
                }
                
                if (!isset($_FILES['hojaVida']) || $_FILES['hojaVida']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Debe subir la hoja de vida en formato PDF');
                }
                
                $archivoHV = $_FILES['hojaVida'];
                
                if ($archivoHV['size'] > 5 * 1024 * 1024) {
                    throw new Exception('La hoja de vida no puede superar los 5MB');
                }
                
                $extensionHV = strtolower(pathinfo($archivoHV['name'], PATHINFO_EXTENSION));
                
                if ($extensionHV !== 'pdf') {
                    throw new Exception('Solo se permiten archivos PDF para la hoja de vida');
                }
                
                $uploadDirHV = __DIR__ . '/../src/uploads/Egresados/';
                if (!is_dir($uploadDirHV)) {
                    mkdir($uploadDirHV, 0755, true);
                }
                
                $safeNameHV = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $numDoc . '_HV_' . basename($archivoHV['name']));
                $uniqueNameHV = date('Ymd_His') . '_' . $safeNameHV;
                $rutaFinalHV = $uploadDirHV . $uniqueNameHV;
                
                if (!move_uploaded_file($archivoHV['tmp_name'], $rutaFinalHV)) {
                    throw new Exception('Error al guardar la hoja de vida en el servidor');
                }
                
                $urlArchivoHV = '/src/uploads/Egresados/' . $uniqueNameHV;
                
                if (!isset($_FILES['diapositivasEgresado']) || $_FILES['diapositivasEgresado']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Debe subir el archivo de diapositivas');
                }
                
                $archivoDiap = $_FILES['diapositivasEgresado'];
                
                if ($archivoDiap['size'] > 10 * 1024 * 1024) {
                    throw new Exception('Las diapositivas no pueden superar los 10MB');
                }
                
                $extensionDiap = strtolower(pathinfo($archivoDiap['name'], PATHINFO_EXTENSION));
                
                if (!in_array($extensionDiap, ['pdf', 'ppt', 'pptx'])) {
                    throw new Exception('Solo se permiten archivos PDF, PPT o PPTX para las diapositivas');
                }
                
                $uploadDirDiap = __DIR__ . '/../src/uploads/Egresados/';
                $safeNameDiap = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $numDoc . '_DIAP_' . basename($archivoDiap['name']));
                $uniqueNameDiap = date('Ymd_His') . '_' . $safeNameDiap;
                $rutaFinalDiap = $uploadDirDiap . $uniqueNameDiap;
                
                if (!move_uploaded_file($archivoDiap['tmp_name'], $rutaFinalDiap)) {
                    throw new Exception('Error al guardar las diapositivas en el servidor');
                }
                
                $urlArchivoDiap = '/src/uploads/Egresados/' . $uniqueNameDiap;
                
                $stmt = $pdo->prepare("
                    INSERT INTO ponente_egresado (
                        Id_Ponentes,
                        Fecha_de_graduacion,
                        Id_Ultimos_estudios,
                        Ciudad_resides,
                        Cargo,
                        Empresa_o_institucion_donde_labora,
                        Descripcion_de_la_experiencia_profesional,
                        Id_Tematica,
                        Titulo_de_la_presentacion,
                        Hoja_de_vida,
                        Presentacion,
                        Motivacion_de_participacion
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $idPonente,
                    $fechaGraduacion,
                    $ultimoEstudio,
                    $ciudadResidencia,
                    $cargo,
                    $empresa,
                    $experiencia,
                    $tematica,
                    $tituloPresentacion,
                    $urlArchivoHV,
                    $urlArchivoDiap,
                    $motivacion
                ]);
                
                $mensaje = '¡Inscripción como ponente egresado realizada exitosamente! Bienvenido(a) ' . $nombre . ' ' . $apellido;
                
            } else {
                $mensaje = '¡Inscripción como ponente realizada exitosamente! Bienvenido(a) ' . $nombre . ' ' . $apellido;
            }
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => $mensaje,
                'tipo' => 'ponente'
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
} catch (PDOException $e) {
    error_log("Error PDO en preinscripcion_ponentes.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error en el sistema. Por favor inténtalo de nuevo más tarde.'
    ]);
}
?>