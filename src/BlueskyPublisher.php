<?php
namespace Jordi\BlueskyPhp;

use GuzzleHttp\Client;
use Exception;

class BlueskyPublisher
{
    protected Client $client;
    protected VideoHistory $history;
    protected YouTubeService $youtube;
    protected ChannelSelector $channelSelector;
    protected Logger $logger;
    protected ?string $accessJwt = null;
    protected int $minDuration = 150;

    

    public function __construct()
    {
        $this->logger = new Logger();
        $this->logger->info("--- Ejecución con Rotación Estricta ---");

        try {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../config');
            $dotenv->load();
            
            $this->client = new Client([
                'base_uri' => 'https://bsky.social',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'BlueskyPHP/1.0'
                ]
            ]);

            $this->history = new VideoHistory();
            $this->youtube = new YouTubeService();

            $this->channelSelector = new ChannelSelector(
                [
                    $_ENV['BEATLES_CHANNEL_ID'],
                    $_ENV['KING_CRIMSON_CHANNEL_ID'], 
                    $_ENV['LED_ZEPPELIN_CHANNEL_ID'],
                    $_ENV['DAVID_BOWIE_CHANNEL_ID'],
                    $_ENV['PRIMAL_SCREAM_CHANNEL_ID'],
                    $_ENV['ROBERT_FRIPP_CHANNEL_ID']
                ],
                $this->history
            );
        } catch (Exception $e) {
            $this->logger->error("Error en el constructor: " . $e->getMessage());
        }
    }

    public function pickVideoForToday(): array
{
    $totalCanales = $this->channelSelector->getChannelsCount();

    $beatlesPlaylists = [
        'PL0jp-uZ7a4g-_PwkKZviEPJLCLnaGPQcH', // Abbey Road
        'PL0jp-uZ7a4g-xSJLr2b8qnqvGSBClg10O', // Yellow Submarine
        'PL0jp-uZ7a4g-dHzo0qKlwmgHbl8-cht0J', // White Album
        'PL0jp-uZ7a4g-MSkNtQYhrxUuRVs-R5EeS', // Magical Mystery Tour
        'PL0jp-uZ7a4g_gLKXDWcJ9bJsj6ABDU0oN', // Sgt. Pepper's
        'PL0jp-uZ7a4g_yNQp6iYEtf_9cInGbrT5d', // Revolver
        'PL0jp-uZ7a4g-i-6UoyDVhPcbCF_QKVuCu', // Rubber Soul
        'PL0jp-uZ7a4g9Bb2FNZ-IIvJZBya-rNTkb', // Help!
        'PL0jp-uZ7a4g_OecOBQjsqXBo7g5MXBAEh', // Beatles For Sale
        'PL0jp-uZ7a4g_4iMT5wmgU7YLqeNQiS1G_', // A Hard Day's Night
        'PL0jp-uZ7a4g87DFKo2l_3VTk8NUzIdAzg', // With The Beatles
        'PL0jp-uZ7a4g-U72wajDxSDU-QXD8EFtAh', // Please Please Me
    ];

    $kingCrimsonPlaylists = [
        'PLXhfRoiJBIisdpSKItIKblZiAWnuOsyAu', // VROOOM VROOOM
        'PLXhfRoiJBIivyJfsjWvAqCaHEPsE986uE', // The Power To Believe
        'PLXhfRoiJBIitKBA6uZ504snD8zZNicfQi', // The ConstruKction Of Light
        'PLXhfRoiJBIivY-ttuoIM-eQAHUYXYkDY1', // THRAK
        'PLXhfRoiJBIitF14G-8gMVpu5eGzdfYrKo', // Three Of A Perfect Pair
        'PLXhfRoiJBIiuLcoYgyuSW2btuN7_zaRPM', // Beat
        'PLXhfRoiJBIitwly9g1nmx6mWt7yqrG4A8', // Discipline
        'PLXhfRoiJBIislZ9MHBBNYK0h3N30Gv7Ja', // Red
        'PLXhfRoiJBIiutFt5GN8_tsxHJVKRdE2qm', // Starless And Bible Black
        'PLXhfRoiJBIitfeySfg3M2JpsHofCulKv9', // Larks' Tongues In Aspic
        'PLXhfRoiJBIissE6cOh0bqouyvin6IflJs', // Islands
        'PLXhfRoiJBIivF7WWLowP0hkPDypPtqInQ', // Lizard
        'PLXhfRoiJBIisZQ8vMbNUApfin2R8Rbcav', // In The Wake Of Poseidon
        'PLXhfRoiJBIiuXOUv_7EJ1i7UKj0aGfy0U', // In The Court Of The Crimson King
    ];

    $ledZeppelinPlaylists = [
        'PLMmd10177iHu7ox5d_5DREh13MB4457my', // Coda
        'PLMmd10177iHszS0zy1LK8_9mrDs9W6aRx', // In Through the Out Door
        'PLMmd10177iHs9yszOnhoFEinWi0TQ0-tH', // Presence
        'PLMmd10177iHsNgILFlhqXmqoNP3QTZO8s', // Physical Graffiti
        'PLMmd10177iHtpLWh5UVuIoqP_U-fD2Mrt', // Houses of the Holy
        'PLMmd10177iHuglVcy9blANFopTddKtSV7', // Led Zeppelin IV
        'PLMmd10177iHvxz_xRVdDqJfIHtX01mNwN', // Led Zeppelin III
        'PLMmd10177iHvfOoVrmqroN6fvGYZFc8Kr', // Led Zeppelin II
        'PLMmd10177iHtzX_a90SYx1ILFdA-2OZ_6', // Led Zeppelin I
    ];

    $robertFrippPlaylists = [
        'PLbnpw-XH5DOHL4PM01_A1bEpCr00OUyJJ', // Andy Summers & Robert Fripp - Skyline
        'PLbnpw-XH5DOGigeYKr4PuBZJKvj_c071Y', // Andy Summers & Robert Fripp - Entropy Pulse
        'PLbnpw-XH5DOGneDHolBPmD-UUpNkySozT', // Fripp & Eno - Beyond Even
        'PLbnpw-XH5DOGBSIzOb4ei7AFpZeNh43oX', // Fripp & Eno - Equatorial Stars
        'PLbnpw-XH5DOEBwHa21IbRwHh1uO0JARzb', // David Sylvian & Robert Fripp - Damage
        'PLbnpw-XH5DOEE1FL9eHH2TkfFKmgF8nxc', // David Sylvian & Robert Fripp - The First Day
        'PLbnpw-XH5DOFMv-Gx3vNF3Rdsf-4CuCAs', // Robert Fripp - Exposure
        'PLbnpw-XH5DOH38Q_SlnqaRLI79iKQK1Wv', // Fripp & Eno - Evening Star
        'PLbnpw-XH5DOGTS7hnEZTb4BxOje4iR4d_', // Fripp & Eno - No Pussyfooting
        'PLbnpw-XH5DOHexzPb74T63LTDPnOL1xCL', // Andy Summers & Robert Fripp - Bewitched
        'PLbnpw-XH5DOGMckLJQkySc9p7IPrkjYW3', // Andy Summers & Robert Fripp - I Advance Masked
        'PLbnpw-XH5DOEvi1Mn4i7VYzBNe7x65TZX', // Soundscapes
        'PLbnpw-XH5DOHh1Co24szN4ZHH6i1vAA8M', // The Grid / Fripp - Leviathan
    ];

    $playlistChannels = [
        $_ENV['BEATLES_CHANNEL_ID']      => $beatlesPlaylists,
        $_ENV['KING_CRIMSON_CHANNEL_ID'] => $kingCrimsonPlaylists,
        $_ENV['LED_ZEPPELIN_CHANNEL_ID'] => $ledZeppelinPlaylists,
        $_ENV['ROBERT_FRIPP_CHANNEL_ID'] => $robertFrippPlaylists,
    ];

    for ($i = 0; $i < $totalCanales; $i++) {
        $channelId = $this->channelSelector->getChannelCandidate($i);
        $this->logger->info("Probando canal (" . ($i + 1) . "/$totalCanales): $channelId");
        $maxDuration = ($channelId === $_ENV['ROBERT_FRIPP_CHANNEL_ID']) ? 2100 : 600;

        try {
            if (isset($playlistChannels[$channelId])) {
                $playlists = $playlistChannels[$channelId];
                shuffle($playlists);
                $videoIds = [];
                foreach ($playlists as $playlistId) {
                    $candidates = array_filter(
                        $this->youtube->getVideosFromPlaylist($playlistId),
                        fn($id) => !$this->history->wasPublished($id)
                    );
                    if (!empty($candidates)) {
                        $videoIds = array_values($candidates);
                        $this->logger->info("Playlist $playlistId con " . count($videoIds) . " vídeos disponibles");
                        break;
                    }
                }
            } else {
                $videoIds = $this->youtube->getVideosFromChannel($channelId, 50);
            }

            if (empty($videoIds)) {
                $this->logger->info("Canal $channelId vacío. Saltando...");
                continue;
            }

            shuffle($videoIds);

            foreach ($videoIds as $videoId) {
                if ($this->history->wasPublished($videoId)) {
                    continue;
                }

                $duration = $this->youtube->getVideoDuration($videoId);

                if ($duration >= $this->minDuration && $duration <= $maxDuration) {
                    $this->logger->info("¡Vídeo elegido!: $videoId (Duración: {$duration}s)");
                    return ['videoId' => $videoId, 'channelId' => $channelId];
                }
            }

            $this->logger->info("Sin vídeos válidos en $channelId. Probando el siguiente...");

        } catch (Exception $e) {
            $this->logger->error("Error al procesar canal $channelId: " . $e->getMessage());
        }
    }

    throw new Exception("Se han revisado todos los canales y no hay material nuevo.");
}

    public function login(): bool
    {
        try {
            $response = $this->client->post('/xrpc/com.atproto.server.createSession', [
                'json' => [
                    'identifier' => $_ENV['BSKY_HANDLE'],
                    'password' => $_ENV['BSKY_APP_PASSWORD']
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            $this->accessJwt = $data['accessJwt'];
            $this->logger->info("Login exitoso.");
            return true;
        } catch (Exception $e) {
            $this->logger->error("Fallo login: " . $e->getMessage());
            return false;
        }
    }

    public function publish(): bool
{
    try {
        if (!$this->accessJwt && !$this->login()) {
            return false;
        }

        // Guardamos el canal que "toca" ANTES de buscar vídeos
        $intendedChannelId = $this->channelSelector->getChannelCandidate(0);

        $video = $this->pickVideoForToday();
        $videoId = $video['videoId'];
        $channelId = $video['channelId'];

        $title = $this->youtube->getVideoTitle($videoId) ?? "Video musical";
        $hashtag = $this->channelSelector->getHashtagForChannel($channelId);
        $cleanHashtag = preg_replace('/[^a-zA-Z0-9]/', '', $hashtag);
        
        $postText = "Buenos días!\n\n#music #$cleanHashtag";

        $external = [
            'uri' => "https://www.youtube.com/watch?v=$videoId",
            'title' => substr($title, 0, 300),
            'description' => ''
        ];

        $thumbBlob = $this->uploadBlob("https://i.ytimg.com/vi/$videoId/hqdefault.jpg");
        
        if ($thumbBlob !== null) {
            $external['thumb'] = $thumbBlob;
        }

        $record = [
            '$type' => 'app.bsky.feed.post',
            'text' => $postText,
            'createdAt' => date('Y-m-d\TH:i:s.v\Z'),
            'facets' => $this->createFacets($postText),
            'embed' => [
                '$type' => 'app.bsky.embed.external',
                'external' => $external
            ]
        ];

        $this->client->post('/xrpc/com.atproto.repo.createRecord', [
            'headers' => ['Authorization' => 'Bearer ' . $this->accessJwt],
            'json' => [
                'repo' => $_ENV['BSKY_HANDLE'],
                'collection' => 'app.bsky.feed.post',
                'record' => $record
            ]
        ]);

        $this->history->addVideo($videoId);
        // Confirmamos el canal intended, no el fallback que haya publicado finalmente
        $this->channelSelector->confirmChannel($intendedChannelId);
        
        $this->logger->info("✅ PUBLICACIÓN COMPLETADA: $title (intended: $intendedChannelId, actual: $channelId)");
        return true;

    } catch (Exception $e) {
        $this->logger->error("Fallo crítico: " . $e->getMessage());
        return false;
    }
}

    protected function uploadBlob($imageUrl): ?object
    {
        try {
            $response = $this->client->get($imageUrl);
            $imageData = $response->getBody()->getContents();
            
            if (empty($imageData)) {
                return null;
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'bsky');
            file_put_contents($tempFile, $imageData);

            $res = $this->client->post('/xrpc/com.atproto.repo.uploadBlob', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessJwt,
                    'Content-Type' => 'image/jpeg'
                ],
                'body' => fopen($tempFile, 'r')
            ]);
            
            unlink($tempFile);
            $result = json_decode($res->getBody());
            return $result->blob ?? null;
        } catch (Exception $e) {
            $this->logger->error("Error al subir miniatura (se omitirá): " . $e->getMessage());
            return null; 
        }
    }

    private function createFacets(string $text): array
    {
        $facets = [];
        if (!preg_match_all('/#\w+/u', $text, $matches, PREG_OFFSET_CAPTURE)) return $facets;
        foreach ($matches[0] as $match) {
            $facets[] = [
                '$type' => 'app.bsky.richtext.facet',
                'index' => ['byteStart' => $match[1], 'byteEnd' => $match[1] + strlen($match[0])],
                'features' => [['$type' => 'app.bsky.richtext.facet#tag', 'tag' => ltrim($match[0], '#')]]
            ];
        }
        return $facets;
    }
}