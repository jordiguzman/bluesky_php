<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/config');
$dotenv->load();

$apiKey    = $_ENV['YOUTUBE_API_KEY'];
$channelId = $_ENV['ROBERT_FRIPP_CHANNEL_ID'];

$client = new \GuzzleHttp\Client([
    'base_uri' => 'https://www.googleapis.com/youtube/v3/',
    'timeout'  => 20.0
]);

$nextPageToken = null;

do {
    $params = [
        'part'       => 'snippet',
        'channelId'  => $channelId,
        'maxResults' => 50,
        'key'        => $apiKey
    ];

    if ($nextPageToken) {
        $params['pageToken'] = $nextPageToken;
    }

    $response = $client->get('playlists', ['query' => $params]);
    $data     = json_decode($response->getBody(), true);

    foreach ($data['items'] ?? [] as $item) {
        echo "Robert Fripp". "-" .$item['id'] . " | " . $item['snippet']['title'] . "\n";
    }

    $nextPageToken = $data['nextPageToken'] ?? null;

} while ($nextPageToken);