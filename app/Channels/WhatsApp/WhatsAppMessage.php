<?php

namespace App\Channels\WhatsApp;

/**
 * A WhatsApp template message to be sent via the Meta Cloud API.
 *
 * Usage:
 *   WhatsAppMessage::template('package_status_update')
 *       ->language('en_US')
 *       ->bodyParams(['PKG-001', 'Ready for Pickup']);
 */
class WhatsAppMessage
{
    public string $language = 'en_US';

    /** @var list<string> */
    public array $bodyParams = [];

    private function __construct(public readonly string $templateName) {}

    public static function template(string $name): self
    {
        return new self($name);
    }

    public function language(string $code): self
    {
        $this->language = $code;

        return $this;
    }

    /**
     * Positional text parameters that fill {{1}}, {{2}}, … in the template body.
     *
     * @param  list<string>  $params
     */
    public function bodyParams(array $params): self
    {
        $this->bodyParams = $params;

        return $this;
    }

    /**
     * Build the Meta Cloud API request payload for this message.
     *
     * @return array<string, mixed>
     */
    public function toPayload(string $recipientPhone): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => preg_replace('/\D+/', '', $recipientPhone),
            'type' => 'template',
            'template' => [
                'name' => $this->templateName,
                'language' => ['code' => $this->language],
            ],
        ];

        if (! empty($this->bodyParams)) {
            $payload['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => array_map(
                        fn (string $text) => ['type' => 'text', 'text' => $text],
                        $this->bodyParams,
                    ),
                ],
            ];
        }

        return $payload;
    }
}
