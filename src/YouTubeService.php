<?php
namespace Jordi\BlueskyPhp;

use GuzzleHttp\Client;
use Exception;

class YouTubeService
{
    private Client $client;
    private string $apiKey;
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->apiKey = $_ENV['YOUTUBE_API_KEY'] ?? '';
        
        $this->client = new Client([
            'base_uri' => 'https://www.googleapis.com/youtube/v3/',
            'timeout'  => 20.0, 
            'connect_timeout' => 10.0
        ]);
    }
    public function getVideosDurations(array $videoIds): array
{
    $durations = [];
    // La API acepta máximo 50 IDs por llamada
    foreach (array_chunk($videoIds, 50) as $chunk) {
        try {
            $response = $this->client->get('videos', [
                'query' => [
                    'part' => 'contentDetails',
                    'id'   => implode(',', $chunk),
                    'key'  => $this->apiKey
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            foreach ($data['items'] ?? [] as $item) {
                $interval = new \DateInterval($item['contentDetails']['duration']);
                $durations[$item['id']] = ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
            }
        } catch (Exception $e) {
            $this->logger->error("Error batch durations: " . $e->getMessage());
        }
    }
    return $durations; // ['videoId' => segundos, ...]
}

public function getVideosFromSearch(string $query, int $limit = 50, string $channelId = ''): array
{
    try {
        $this->logger->info("YouTubeService: Buscando por query: \"$query\"");
        $params = [
            'part'       => 'snippet',
            'q'          => $query,
            'maxResults' => min($limit, 50),
            'order'      => 'relevance',
            'type'       => 'video',
            'key'        => $this->apiKey
        ];

        if (!empty($channelId)) {
            $params['channelId'] = $channelId;
        }

        $response = $this->client->get('search', ['query' => $params]);
        $data = json_decode($response->getBody(), true);
        $videoIds = [];
        foreach ($data['items'] ?? [] as $item) {
            if (isset($item['id']['videoId'])) {
                $videoIds[] = $item['id']['videoId'];
            }
        }
        return $videoIds;
    } catch (Exception $e) {
        $this->logger->error("YouTubeService ERROR (search): " . $e->getMessage());
        return [];
    }
}
    /**
     * Obtiene una lista de vídeos. Ahora permite buscar hasta 100 vídeos usando paginación.
     */
    public function getVideosFromChannel(string $channelId, int $limit = 100): array
    {
        try {
            $sortOrders = ['date', 'rating', 'relevance', 'viewCount'];
            $randomOrder = $sortOrders[array_rand($sortOrders)];

            $this->logger->info("YouTubeService: Buscando hasta $limit vídeos en canal $channelId (Orden: $randomOrder)");

            $videoIds = [];
            $nextPageToken = null;
            $peticiones = 0;
            $maxPeticiones = ($limit > 50) ? 2 : 1;

            while ($peticiones < $maxPeticiones) {
                $params = [
                    'part'       => 'snippet',
                    'channelId'  => $channelId,
                    'maxResults' => 50,
                    'order'      => $randomOrder,
                    'type'       => 'video',
                    'key'        => $this->apiKey
                ];

                if ($nextPageToken) {
                    $params['pageToken'] = $nextPageToken;
                }

                $response = $this->client->get('search', ['query' => $params]);
                $data = json_decode($response->getBody(), true);
                
                if (!isset($data['items'])) break;

                foreach ($data['items'] as $item) {
                    if (isset($item['id']['videoId'])) {
                        $videoIds[] = $item['id']['videoId'];
                    }
                }

                $nextPageToken = $data['nextPageToken'] ?? null;
                $peticiones++;
                
                if (!$nextPageToken) break;
            }

            return $videoIds;

        } catch (Exception $e) {
            $this->logger->error("YouTubeService ERROR: " . $e->getMessage());
            return [];
        }
    }

    public function getVideoDuration(string $videoId): int
    {
        try {
            $response = $this->client->get('videos', [
                'query' => [
                    'part' => 'contentDetails',
                    'id'   => $videoId,
                    'key'  => $this->apiKey
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            if (empty($data['items'])) return 0;

            $duration = $data['items'][0]['contentDetails']['duration'];
            $interval = new \DateInterval($duration);
            return ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
        } catch (Exception $e) {
            return 0;
        }
    }
    public function getVideosFromPlaylist(string $playlistId): array
{
    try {
        $this->logger->info("YouTubeService: Obteniendo vídeos de playlist $playlistId");
        $videoIds = [];
        $nextPageToken = null;

        do {
            $params = [
                'part'       => 'contentDetails',
                'playlistId' => $playlistId,
                'maxResults' => 50,
                'key'        => $this->apiKey
            ];

            if ($nextPageToken) {
                $params['pageToken'] = $nextPageToken;
            }

            $response = $this->client->get('playlistItems', ['query' => $params]);
            $data = json_decode($response->getBody(), true);

            foreach ($data['items'] ?? [] as $item) {
                $videoId = $item['contentDetails']['videoId'] ?? null;
                if ($videoId) $videoIds[] = $videoId;
            }

            $nextPageToken = $data['nextPageToken'] ?? null;

        } while ($nextPageToken);

        return $videoIds;
    } catch (Exception $e) {
        $this->logger->error("YouTubeService ERROR (playlist): " . $e->getMessage());
        return [];
    }
}

    public function getVideoTitle(string $videoId): ?string
    {
        try {
            $response = $this->client->get('videos', [
                'query' => [
                    'part' => 'snippet',
                    'id'   => $videoId,
                    'key'  => $this->apiKey
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            return $data['items'][0]['snippet']['title'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }
}