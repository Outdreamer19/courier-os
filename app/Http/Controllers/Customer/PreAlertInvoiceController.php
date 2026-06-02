<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PreAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PreAlertInvoiceController extends Controller
{
    public function __invoke(Request $request, PreAlert $preAlert): StreamedResponse
    {
        $this->authorize('downloadInvoice', $preAlert);

        $disk = Storage::disk(config('shipdjm.invoice_uploads.disk', 'local'));

        abort_unless($disk->exists($preAlert->invoice_path), 404);

        return $disk->download($preAlert->invoice_path);
    }
}
