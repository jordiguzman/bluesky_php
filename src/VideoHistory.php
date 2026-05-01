<?php
namespace Jordi\BlueskyPhp;

class VideoHistory
{
    private $filePath;

    public function __construct()
    {
        $this->filePath = __DIR__ . '/../data/published_videos.json';
        $this->ensureFileExists();
    }

    private function ensureFileExists()
    {
        if (!file_exists($this->filePath)) {
            $dir = dirname($this->filePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            // Estructura inicial coherente
            $data = [
                'videos' => [],
                'channels' => []
            ];
            file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT));
        }
    }

    private function loadData(): array
    {
        return json_decode(file_get_contents($this->filePath), true);
    }

    private function saveData(array $data): void
    {
        file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    // ==========================
    // CONTROL DE VÍDEOS
    // ==========================
    public function wasPublished(string $videoId): bool
{
    $data = $this->loadData();
    $allVideos = [];

    // Incluir índices numéricos
    foreach ($data as $key => $value) {
        if (is_numeric($key)) $allVideos[] = $value;
    }

    // Incluir array moderno 'videos'
    if (isset($data['videos'])) {
        $allVideos = array_merge($allVideos, $data['videos']);
    }

    return in_array($videoId, $allVideos, true);
}

public function addVideo(string $videoId): void
{
    $data = $this->loadData();

    // Evitar duplicados
    if (!in_array($videoId, $data['videos'] ?? [])) {
        // Agregar al array moderno
        $data['videos'][] = $videoId;

        // Agregar al siguiente índice numérico disponible
        $numericKeys = array_filter(array_keys($data), 'is_numeric');
        $nextIndex = empty($numericKeys) ? 0 : max($numericKeys) + 1;
        $data[$nextIndex] = $videoId;

        $this->saveData($data);
    }
}


    

    // ==========================
    // CONTROL DE CANALES
    // ==========================
    public function getLastChannelDate(string $channelId): ?string
    {
        $data = $this->loadData();
        return $data['channels'][$channelId] ?? null;
    }

    public function addChannel(string $channelId): void
    {
        $data = $this->loadData();
        $data['channels'][$channelId] = date('Y-m-d');
        $this->saveData($data);
    }
    // ==========================
// CONTROL DEL ÚLTIMO CANAL PUBLICADO
// ==========================
public function getLastUsedChannel(): ?string
{
    $data = $this->loadData();
    return $data['last_channel'] ?? null;
}

public function setLastUsedChannel(string $channelId): void
{
    $data = $this->loadData();
    $data['last_channel'] = $channelId;
    $this->saveData($data);
}

}

