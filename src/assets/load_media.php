<?php
header('Content-Type: application/json');

$day = isset($_GET['day']) ? intval($_GET['day']) : 1;
$folderPath = __DIR__ . '/img/Noticias/dia' . $day;

$mediaFiles = [];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'avi'];

if (is_dir($folderPath)) {
    $files = scandir($folderPath);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $filePath = $folderPath . '/' . $file;
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        if (in_array($extension, $allowedExtensions) && is_file($filePath)) {
            $type = in_array($extension, ['mp4', 'mov', 'avi']) ? 'video' : 'image';
            
            $mediaFiles[] = [
                'name' => pathinfo($file, PATHINFO_FILENAME),
                'path' => '/src/assets/img/Noticias/dia' . $day . '/' . $file,
                'type' => $type
            ];
        }
    }
}

echo json_encode($mediaFiles);
?>
