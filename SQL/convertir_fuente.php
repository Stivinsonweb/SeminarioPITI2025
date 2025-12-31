<?php
$fpdfPath = __DIR__ . '/../src/uploads/libs/fpdf/';
$fontSourcePath = __DIR__ . '/../src/assets/fonts/GreatVibes-Regular.ttf';
$fontOutputPath = __DIR__ . '/../src/assets/fonts/';

if (!file_exists($fpdfPath . 'fpdf.php')) {
    die("ERROR: No se encuentra FPDF en: $fpdfPath\n");
}

if (!file_exists($fontSourcePath)) {
    die("ERROR: No se encuentra la fuente en: $fontSourcePath\n");
}

$makefontPath = $fpdfPath . 'makefont/makefont.php';
if (!file_exists($makefontPath)) {
    die("ERROR: No se encuentra makefont.php en: $makefontPath\nDescárgalo de: http://www.fpdf.org/\n");
}

require($makefontPath);

echo "Convirtiendo fuente GreatVibes...\n";

MakeFont($fontSourcePath, 'cp1252');

echo "¡Fuente convertida exitosamente!\n";
echo "Archivos generados en: $fontOutputPath\n";
echo "- GreatVibes-Regular.php\n";
echo "- GreatVibes-Regular.z\n";
?>
