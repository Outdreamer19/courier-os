<?php

use Valet\Drivers\LaravelValetDriver;

class LocalValetDriver extends LaravelValetDriver
{
    public function serves(string $sitePath, string $siteName, string $uri): bool
    {
        return in_array($siteName, ['shipdjm', 'shipdjmapp'], true);
    }
}
