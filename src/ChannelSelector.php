<?php
namespace Jordi\BlueskyPhp;

class ChannelSelector
{
    private array $channels;
    private VideoHistory $history;

    public function __construct(array $channels, VideoHistory $history)
    {
        $this->channels = $channels;
        $this->history = $history;
    }

    /**
     * Obtiene el canal que toca según el historial, aplicando un desplazamiento (offset).
     * No guarda nada todavía, solo nos dice qué canal es el candidato.
     */
    public function getChannelCandidate(int $offset = 0): string
    {
        $lastChannel = $this->history->getLastUsedChannel();

        if ($lastChannel === null) {
            $baseIndex = -1; // Empezará en 0 al sumar 1 abajo
        } else {
            $baseIndex = array_search($lastChannel, $this->channels, true);
            if ($baseIndex === false) $baseIndex = -1;
        }

        // Calculamos el índice usando el offset sin modificar el estado real
        $targetIndex = ($baseIndex + 1 + $offset) % count($this->channels);
        return $this->channels[$targetIndex];
    }

    /**
     * Este método es el que confirma el cambio en la rotación.
     * Solo debe llamarse DESPUÉS de una publicación exitosa.
     */
    public function confirmChannel(string $channelId): void
    {
        $this->history->setLastUsedChannel($channelId);
    }

    public function getChannelsCount(): int
    {
        return count($this->channels);
    }

    public function getHashtagForChannel(string $identifier): string
    {
        $hashtags = [
            'UCc4K7bAqpdBP8jh1j9XZAww' => 'TheBeatles',
            'UCBxEf1UWDjbIEoh2MAQR7zQ' => 'KingCrimson',
            'UCaKZA66vM_TUpetUNohmR0A' => 'LedZeppelin',
            'UC8YgWcDKi1rLbQ1OtrOHeDw' => 'DavidBowie',
            'UC1-XKRM1fzbGKciUj2TjM_w' => 'PrimalScream',
            'UCQxX-MDtlFo3wAYdwwvk-YA' => 'RobertFripp'
        ];

        return $hashtags[$identifier] ?? 'Music';
    }
}