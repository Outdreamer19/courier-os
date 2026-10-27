<?php

namespace Tests\Feature\Ops;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class DatabaseBackupScheduleTest extends TestCase
{
    public function test_database_backup_runs_nightly_with_cleanup_and_monitoring(): void
    {
        $commands = collect(app(Schedule::class)->events())
            ->map(fn (Event $event): string => (string) $event->command);

        $this->assertTrue($commands->contains(fn (string $c): bool => str_contains($c, 'backup:run --only-db')));
        $this->assertTrue($commands->contains(fn (string $c): bool => str_contains($c, 'backup:clean')));
        $this->assertTrue($commands->contains(fn (string $c): bool => str_contains($c, 'backup:monitor')));
    }

    public function test_r2_disk_is_configured_for_backups(): void
    {
        $this->assertSame('s3', config('filesystems.disks.r2.driver'));
        $this->assertSame('auto', config('filesystems.disks.r2.region'));
    }
}
