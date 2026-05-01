<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Jordi\BlueskyPhp\BlueskyPublisher;

$publisher = new BlueskyPublisher();
$publisher->login();
$publisher->publishPost("¡Hola desde PHP! 🐘", "https://youtu.be/tu_video");