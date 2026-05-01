<?php
namespace Jordi\BlueskyPhp;

class Logger
{
    private string $logFile;

    public function __construct()
    {
        // Guardaremos el log en la carpeta data
        $this->logFile = __DIR__ . '/../data/app.log';
    }

    /**
     * Escribe un mensaje en el archivo de log con timestamp.
     */
    public function info(string $message): void
    {
        $date = date('Y-m-d H:i:s');
        $formattedMessage = "[$date] [INFO] $message" . PHP_EOL;
        file_put_contents($this->logFile, $formattedMessage, FILE_APPEND);
    }

    /**
     * Escribe errores importantes.
     */
    public function error(string $message): void
    {
        $date = date('Y-m-d H:i:s');
        $formattedMessage = "[$date] [ERROR] $message" . PHP_EOL;
        file_put_contents($this->logFile, $formattedMessage, FILE_APPEND);
    }
}