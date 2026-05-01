<?php
class AutoBackupUI {
    private $backupDir;
    private $statusFile;
    private $projectRoot;

    // Para backup: Ejecuta: php autobackup_ui.php run
    
    public function __construct() {
        $this->projectRoot = __DIR__;
        $this->backupDir = $this->projectRoot . '/project_backups/';
        $this->statusFile = $this->backupDir . 'status.json';
        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    public function run() {
        $this->updateStatus('running');
        
        // SCAN REAL del proyecto - BACKUP DE VERDAD
        $content = $this->generateRealBackup();
        
        $filename = date('Y-m-d_His') . '_autobackup.txt';
        file_put_contents($this->backupDir . $filename, $content);
        
        $this->cleanOldBackups();
        $this->updateStatus('idle');
        
        echo "✅ Backup REAL creado: " . $filename . " (" . round(strlen($content)/1024, 2) . " KB)\n";
    }
    
    private function generateRealBackup() {
        $content = "============================================\n";
        $content .= "BACKUP REAL DEL PROYECTO MENTAT-AUTOMATION\n";
        $content .= "FECHA: " . date('Y-m-d H:i:s') . "\n";
        $content .= "============================================\n\n";
        
        // ESCANEAR estructura REAL
        $content .= "ESTRUCTURA DE CARPETAS:\n";
        $content .= "=======================\n";
        $content .= $this->scanDirectoryStructure($this->projectRoot);
        
        // CONTENIDO de archivos IMPORTANTES
        $content .= "\nCONTENIDO DE ARCHIVOS CLAVE:\n";
        $content .= "=============================\n";
        $content .= $this->getImportantFilesContent();
        
        return $content;
    }
    
    private function scanDirectoryStructure($dir, $depth = 0) {
        $content = "";
        $items = scandir($dir);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $path = $dir . '/' . $item;
            $relativePath = str_replace($this->projectRoot . '/', '', $path);
            
            if (is_dir($path)) {
                $content .= str_repeat('  ', $depth) . "📁 " . $relativePath . "/\n";
                $content .= $this->scanDirectoryStructure($path, $depth + 1);
            } else {
                $content .= str_repeat('  ', $depth) . "📄 " . $relativePath . " (" . round(filesize($path)/1024, 2) . " KB)\n";
            }
        }
        
        return $content;
    }
    
    private function getImportantFilesContent() {
        $content = "";
        $importantFiles = [
            'projects/HirisePublisher/composer.json',
            'composer.json',
            'config/.env.example',
            'backup_chat.php',
            'autobackup_ui.php'
        ];
        
        foreach ($importantFiles as $file) {
            $fullPath = $this->projectRoot . '/' . $file;
            if (file_exists($fullPath)) {
                $content .= "\n--- " . $file . " ---\n";
                $content .= file_get_contents($fullPath) . "\n";
            }
        }
        
        return $content;
    }
    
    public function getStatus() {
        if (file_exists($this->statusFile)) {
            return json_decode(file_get_contents($this->statusFile), true);
        }
        return ['status' => 'never_run'];
    }
    
    private function updateStatus($status) {
        $statusData = [
            'status' => $status,
            'last_run' => date('Y-m-d H:i:s'),
            'next_run' => date('Y-m-d H:i:s', time() + 600)
        ];
        file_put_contents($this->statusFile, json_encode($statusData, JSON_PRETTY_PRINT));
    }
    
    private function cleanOldBackups() {
        $backups = glob($this->backupDir . '*_autobackup.txt');
        usort($backups, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        if (count($backups) > 6) { // Mantener solo 6 backups reales
            $toDelete = array_slice($backups, 6);
            foreach ($toDelete as $file) {
                unlink($file);
            }
        }
    }
}

// INTERFAZ DE MONITOREO (el mismo código de antes)
if (php_sapi_name() === 'cli') {
    $backup = new AutoBackupUI();
    
    if (isset($argv[1]) && $argv[1] === 'run') {
        $backup->run();
        
    } elseif (isset($argv[1]) && $argv[1] === 'status') {
        $status = $backup->getStatus();
        
        echo "🔍 MONITOR DE AUTOBACKUP\n";
        echo "=======================\n";
        echo "Estado: " . $status['status'] . "\n";
        echo "Última ejecución: " . ($status['last_run'] ?? 'Nunca') . "\n";
        echo "Próxima ejecución: " . ($status['next_run'] ?? 'N/A') . "\n";
        
        $backups = glob(__DIR__ . '/project_backups/*_autobackup.txt');
        echo "Backups existentes: " . count($backups) . "\n";
        
    } else {
        echo "🔍 MONITOR DE AUTOBACKUP - COMANDOS\n";
        echo "===================================\n";
        echo "php autobackup_ui.php run    - Ejecutar backup ahora\n";
        echo "php autobackup_ui.php status - Ver estado actual\n";
        echo "php autobackup_ui.php        - Mostrar esta ayuda\n";
    }
}
?>