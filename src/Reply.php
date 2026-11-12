<?php

declare(strict_types=1);

namespace Slacker;

/**
 * What a plugin wants posted back to Slack.
 */
final class Reply
{
    private function __construct(
        public readonly string $text,
        /** true = visible to the whole channel, false = only the invoking user. */
        public readonly bool $public,
        public readonly ?string $imageUrl = null,
    ) {
    }

    public static function channel(string $text, ?string $imageUrl = null): self
    {
        return new self($text, true, $imageUrl);
    }

    public static function ephemeral(string $text): self
    {
        return new self($text, false);
    }

    /**
     * Escapes text for Slack mrkdwn so user input can't inject
     * mentions like <!channel> or fake links.
     */
    public static function escape(string $text): string
    {
        return str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $text);
    }

    /** @return array<string, mixed> Slack slash-command response payload. */
    public function toPayload(string $prefix = ''): array
    {
        $text = $prefix . $this->text;
        $payload = [
            'response_type' => $this->public ? 'in_channel' : 'ephemeral',
            'text' => $text,
        ];

        if ($this->imageUrl !== null) {
            $payload['blocks'] = [
                ['type' => 'section', 'text' => ['type' => 'mrkdwn', 'text' => $text]],
                ['type' => 'image', 'image_url' => $this->imageUrl, 'alt_text' => 'image'],
            ];
        }

        return $payload;
    }
}
