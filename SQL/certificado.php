<?php
ini_set('max_execution_time', 60);
if (ob_get_level()) {
    ob_end_clean();
}
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once __DIR__ . '/../src/uploads/libs/fpdf/fpdf.php';
require_once __DIR__ . '/db_connect.php';
session_start();
$current_time = time();
$doc_param = $_REQUEST['doc'] ?? '';
$rate_limit_key = 'cert_' . md5(($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '_' . date('Y-m-d'));
if (!isset($_SESSION[$rate_limit_key])) {
    $_SESSION[$rate_limit_key] = [];
}
$_SESSION[$rate_limit_key] = array_filter($_SESSION[$rate_limit_key], function($timestamp) use ($current_time) {
    return ($current_time - $timestamp) < 600;
});
if (count($_SESSION[$rate_limit_key]) >= 30) {
    header('HTTP/1.1 429 Too Many Requests');
    header('Content-Type: application/json');
    header('Retry-After: 600');
    echo json_encode([
        'success' => false, 
        'message' => 'Ha realizado demasiadas solicitudes. Por favor, espere 10 minutos antes de intentar nuevamente.',
        'retry_after' => 600
    ]);
    exit;
}
$_SESSION[$rate_limit_key][] = $current_time;
class PDF extends FPDF {
    private $customFontLoaded = false;
    function AddCustomFont() {
        $fontPath = __DIR__ . '/../src/assets/fonts/';
        $phpFile = $fontPath . 'GreatVibes-Regular.php';
        $zFile = $fontPath . 'GreatVibes-Regular.z';
        if (file_exists($phpFile) && file_exists($zFile)) {
            try {
                $this->AddFont('GreatVibes', '', $phpFile);
                $this->customFontLoaded = true;
            } catch (Exception $e) {
                error_log("Error al cargar fuente personalizada: " . $e->getMessage());
                $this->customFontLoaded = false;
            }
        } else {
            error_log("Archivos de fuente no encontrados: $phpFile o $zFile");
            $this->customFontLoaded = false;
        }
    }
    function IsCustomFontLoaded() {
        return $this->customFontLoaded;
    }
}
function AddText($pdf, $text, $x, $y, $a, $f, $t, $s, $r, $g, $b) {
    $pdf->SetFont($f, $t, $s);
    $pdf->SetXY($x, $y);
    $pdf->SetTextColor($r, $g, $b);
    if ($s >= 20) {
        $pdf->Cell(0, $s * 0.5, $text, 0, 0, $a);
    } else {
        $pdf->Cell(0, 10, $text, 0, 0, $a);
    }
}
function buscarPersona($pdo, $doc) {
    try {
        $stmt_participante = $pdo->prepare("
            SELECT 
                Numero_de_documento,
                Nombre,
                Apellido,
                Id_Tipo_documento,
                'participante' AS tipo_persona
            FROM participante 
            WHERE Numero_de_documento = ? 
            LIMIT 1
        ");
        $stmt_participante->execute([$doc]);
        $resultado = $stmt_participante->fetch(PDO::FETCH_ASSOC);
        if ($resultado) {
            try {
                $stmt_tipo = $pdo->prepare("SELECT Tipo_documento FROM tipo_documento WHERE Id_Tipo_documento = ?");
                $stmt_tipo->execute([$resultado['Id_Tipo_documento']]);
                $tipo = $stmt_tipo->fetch(PDO::FETCH_ASSOC);
                $resultado['Tipo_documento'] = $tipo ? $tipo['Tipo_documento'] : 'C.C.';
            } catch (Exception $e) {
                $resultado['Tipo_documento'] = 'C.C.';
            }
            return $resultado;
        }
        $stmt_ponente = $pdo->prepare("
            SELECT 
                Numero_de_documento,
                Nombre,
                Apellido,
                Id_Tipo_documento,
                'ponente' AS tipo_persona
            FROM ponentes 
            WHERE Numero_de_documento = ? 
            LIMIT 1
        ");
        $stmt_ponente->execute([$doc]);
        $resultado = $stmt_ponente->fetch(PDO::FETCH_ASSOC);
        if ($resultado) {
            try {
                $stmt_tipo = $pdo->prepare("SELECT Tipo_documento FROM tipo_documento WHERE Id_Tipo_documento = ?");
                $stmt_tipo->execute([$resultado['Id_Tipo_documento']]);
                $tipo = $stmt_tipo->fetch(PDO::FETCH_ASSOC);
                $resultado['Tipo_documento'] = $tipo ? $tipo['Tipo_documento'] : 'C.C.';
            } catch (Exception $e) {
                $resultado['Tipo_documento'] = 'C.C.';
            }
        }
        return $resultado;
    } catch (Exception $e) {
        error_log("Error en buscarPersona: " . $e->getMessage());
        return false;
    }
}
$action = $_REQUEST['action'] ?? '';
$doc = $_REQUEST['doc'] ?? '';
$confirmed = $_REQUEST['confirmed'] ?? '';
if (empty($doc) || !preg_match('/^[0-9]{6,10}$/', $doc)) {
    if ($action === 'generar') {
        die("El número de documento ingresado no es válido. Por favor, verifique e intente nuevamente.");
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'El número de documento debe contener entre 6 y 10 dígitos numéricos.'
        ]);
        exit;
    }
}
try {
    $pdo->setAttribute(PDO::ATTR_TIMEOUT, 5);
    $persona = buscarPersona($pdo, $doc);
    if (!$persona) {
        if ($action === 'generar') {
            die("Lo sentimos, no encontramos su registro en el evento. Verifique su número de documento o contacte con el organizador.");
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'No se encontró ningún registro asociado a este documento en nuestro sistema.'
            ]);
            exit;
        }
    }
    $nombreCompleto = trim($persona['Nombre'] . ' ' . $persona['Apellido']);
    $tipoDocumento = $persona['Tipo_documento'] ?? 'C.C.';
    $numeroDocumento = $persona['Numero_de_documento'];
    $tipoPersona = $persona['tipo_persona'];
    if ($action === 'validar') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'nombre' => $persona['Nombre'],
            'apellido' => $persona['Apellido'],
            'tipo_documento' => $tipoDocumento,
            'numero_documento' => $numeroDocumento,
            'tipo_persona' => $tipoPersona
        ]);
        exit;
    }
    if ($action === 'generar') {
        if ($confirmed !== 'yes') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'confirm' => true,
                'nombre' => $nombreCompleto,
                'tipo_persona' => $tipoPersona
            ]);
            exit;
        }
        while (ob_get_level()) {
            ob_end_clean();
        }
        static $imagePath = null;
        if ($imagePath === null) {
            $imagePaths = [
                __DIR__ . '/../src/uploads/certificado.png',
                __DIR__ . '/../src/uploads/certificado.jpg',
                __DIR__ . '/../src/uploads/Certificado.jpg',
                __DIR__ . '/../uploads/certificado.JPG',
                __DIR__ . '/../uploads/certificado.jpg'
            ];
            foreach ($imagePaths as $path) {
                if (file_exists($path)) {
                    $imagePath = $path;
                    break;
                }
            }
            if ($imagePath === null) {
                $imagePath = false;
            }
        }
        if (empty($nombreCompleto)) {
            die("Error: No se pudo obtener el nombre completo. Por favor, contacte con el administrador.");
        }
        $pdf = new PDF('L', 'mm', 'A4');
        $pdf->AddCustomFont();
        $pdf->AddPage();
        $pdf->SetCreator('Sistema de Certificados');
        $pdf->SetAutoPageBreak(false);
        $pdf->SetCompression(true);
        if ($imagePath) {
            $pdf->Image($imagePath, 0, 0, 297, 210);
        } else {
            $pdf->SetFillColor(240, 248, 255);
            $pdf->Rect(0, 0, 297, 210, 'F');
            $titulo = ($tipoPersona === 'ponente') ? 'CERTIFICADO DE PONENCIA' : 'CERTIFICADO DE PARTICIPACION';
            AddText($pdf, $titulo, 0, 40, 'C', 'Arial', 'B', 24, 0, 50, 100);
        }
        if ($pdf->IsCustomFontLoaded()) {
            $pdf->SetFont('GreatVibes', '', 48);
        } else {
            $pdf->SetFont('Times', 'BI', 38);
        }
        $pdf->SetXY(0, 90);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 15, $nombreCompleto, 0, 0, 'C');
        $pdf->SetFont('Arial', '', 14);
        $pdf->SetTextColor(0, 0, 0);
        $textoCompleto = 'Identificado con ' . $tipoDocumento . ', ' . $numeroDocumento;
        $anchoTexto = $pdf->GetStringWidth($textoCompleto);
        $xInicio = (297 - $anchoTexto) / 2;
        $pdf->SetXY($xInicio, 110);
        $pdf->Cell($anchoTexto, 5, $textoCompleto, 0, 0, 'L');
        $tipoPersonaUpper = ucfirst($tipoPersona);
        $filename = 'Certificado_' . $tipoPersonaUpper . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', strval($nombreCompleto)) . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Content-Transfer-Encoding: binary');
        $pdf->Output('D', $filename);
        unset($pdf);
        exit;
    }
} catch (PDOException $e) {
    error_log("Error DB certificado: " . $e->getMessage());
    if ($action === 'generar') {
        die("Error de conexión con la base de datos. Por favor, intente nuevamente en unos momentos.");
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Error de conexión con el servidor. Por favor, intente más tarde.'
        ]);
    }
} catch (Exception $e) {
    error_log("Error certificado: " . $e->getMessage());
    if ($action === 'generar') {
        die("Error al procesar su solicitud. Por favor, intente nuevamente o contacte con soporte técnico.");
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Ocurrió un error inesperado. Por favor, intente nuevamente.'
        ]);
    }
}
?>
