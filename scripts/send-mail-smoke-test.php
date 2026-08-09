<?php

use App\Enums\PackageStatus;
use App\Enums\PreAlertStatus;
use App\Models\Package;
use App\Models\PlatformSupportMessage;
use App\Models\PlatformSupportThread;
use App\Models\PreAlert;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\PackageReadyForPickupNotification;
use App\Notifications\PackageStatusChangedNotification;
use App\Notifications\PlatformSupportMessageFromPlatform;
use App\Notifications\PlatformSupportMessageFromTenant;
use App\Notifications\PreAlertStatusChangedNotification;
use Illuminate\Auth\Notifications\ResetPassword;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$to = $argv[1] ?? 'info@courieros.co';

$recipient = User::query()->firstOrFail();
$originalEmail = $recipient->email;
$recipient->email = $to;
$recipient->name = $recipient->name ?: 'CourierOS';

$results = [];

try {
    $pkg = Package::withoutGlobalScopes()->whereNotNull('tenant_id')->with(['tenant', 'user'])->firstOrFail();
    $recipient->notifyNow(new PackageStatusChangedNotification(
        $pkg,
        PackageStatus::ReceivedAtFloridaWarehouse,
        PackageStatus::InTransitToJamaica,
    ));
    $results[] = 'PackageStatusChangedNotification: OK';
} catch (Throwable $e) {
    $results[] = 'PackageStatusChangedNotification: FAIL '.$e->getMessage();
}

try {
    $pkg = Package::withoutGlobalScopes()->whereNotNull('tenant_id')->with(['tenant', 'user'])->firstOrFail();
    $recipient->notifyNow(new PackageReadyForPickupNotification($pkg));
    $results[] = 'PackageReadyForPickupNotification: OK';
} catch (Throwable $e) {
    $results[] = 'PackageReadyForPickupNotification: FAIL '.$e->getMessage();
}

try {
    $pre = PreAlert::withoutGlobalScopes()->whereNotNull('tenant_id')->with(['tenant', 'user'])->firstOrFail();
    $recipient->notifyNow(new PreAlertStatusChangedNotification(
        $pre,
        PreAlertStatus::Submitted,
        PreAlertStatus::MatchedToPackage,
    ));
    $results[] = 'PreAlertStatusChangedNotification: OK';
} catch (Throwable $e) {
    $results[] = 'PreAlertStatusChangedNotification: FAIL '.$e->getMessage();
}

$thread = null;

try {
    $tenant = Tenant::firstOrFail();
    $thread = PlatformSupportThread::create([
        'tenant_id' => $tenant->id,
        'subject' => 'Resend mail test thread',
        'status' => PlatformSupportThread::STATUS_OPEN,
        'created_by_user_id' => $recipient->id,
        'last_message_at' => now(),
    ]);
    $message = PlatformSupportMessage::create([
        'thread_id' => $thread->id,
        'user_id' => $recipient->id,
        'body' => 'This is a test support message from the tenant side to verify Resend delivery.',
        'author_side' => PlatformSupportMessage::SIDE_TENANT,
    ]);
    $recipient->notifyNow(new PlatformSupportMessageFromTenant($thread, $message));
    $results[] = 'PlatformSupportMessageFromTenant: OK';

    $platformMessage = PlatformSupportMessage::create([
        'thread_id' => $thread->id,
        'user_id' => $recipient->id,
        'body' => 'This is a test support reply from platform to verify Resend delivery.',
        'author_side' => PlatformSupportMessage::SIDE_PLATFORM,
    ]);
    $recipient->notifyNow(new PlatformSupportMessageFromPlatform($thread, $platformMessage));
    $results[] = 'PlatformSupportMessageFromPlatform: OK';
} catch (Throwable $e) {
    $results[] = 'PlatformSupport: FAIL '.$e->getMessage();
} finally {
    if ($thread) {
        $thread->messages()->delete();
        $thread->delete();
    }
}

try {
    $recipient->notifyNow(new ResetPassword('test-reset-token-not-for-use'));
    $results[] = 'ResetPassword: OK';
} catch (Throwable $e) {
    $results[] = 'ResetPassword: FAIL '.$e->getMessage();
}

// Restore in-memory only (email was never persisted).
$recipient->email = $originalEmail;

echo "Sent to: {$to}".PHP_EOL;
echo implode(PHP_EOL, $results).PHP_EOL;
