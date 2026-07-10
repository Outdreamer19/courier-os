<?php

namespace Tests\Unit;

use App\Channels\WhatsApp\WhatsAppMessage;
use PHPUnit\Framework\TestCase;

class WhatsAppMessageTest extends TestCase
{
    public function test_builds_minimal_payload_without_body_params(): void
    {
        $msg = WhatsAppMessage::template('my_template');

        $payload = $msg->toPayload('+18765551234');

        $this->assertSame('whatsapp', $payload['messaging_product']);
        $this->assertSame('18765551234', $payload['to']); // digits stripped
        $this->assertSame('template', $payload['type']);
        $this->assertSame('my_template', $payload['template']['name']);
        $this->assertSame('en_US', $payload['template']['language']['code']);
        $this->assertArrayNotHasKey('components', $payload['template']);
    }

    public function test_builds_payload_with_body_params(): void
    {
        $msg = WhatsAppMessage::template('pkg_update')
            ->language('en_GB')
            ->bodyParams(['Alice', 'PKG-001', 'Ready for Pickup']);

        $payload = $msg->toPayload('18005551234');

        $this->assertSame('en_GB', $payload['template']['language']['code']);
        $components = $payload['template']['components'];
        $this->assertCount(1, $components);
        $this->assertSame('body', $components[0]['type']);
        $this->assertCount(3, $components[0]['parameters']);
        $this->assertSame(['type' => 'text', 'text' => 'Alice'], $components[0]['parameters'][0]);
    }

    public function test_strips_non_digit_characters_from_phone(): void
    {
        $msg = WhatsAppMessage::template('t');

        $payload = $msg->toPayload('+1 (876) 555-9999');

        $this->assertSame('18765559999', $payload['to']);
    }
}
