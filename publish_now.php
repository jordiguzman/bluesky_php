<?php
// CARGAR .env MANUALMENTE
require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/config');
$dotenv->load();

use Jordi\BlueskyPhp\BlueskyPublisher;

echo "[" . date('Y-m-d H:i:s') . "] Iniciando publicación...\n";

try {
    $publisher = new BlueskyPublisher();
    $publisher->login();
    
    if ($publisher->publish()) {
        echo "[" . date('Y-m-d H:i:s') . "] ✅ Publicación EXITOSA\n";
    } else {
        echo "[" . date('Y-m-d H:i:s') . "] ❌ Publicación FALLIDA\n";
    }
    
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ❌ Error: " . $e->getMessage() . "\n";
}