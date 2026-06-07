<?php

namespace App\Http\Controllers\Admin;

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

        abort_unless($preAlert->invoice_path && $disk->exists($preAlert->invoice_path), 404);

        $filename = basename($preAlert->invoice_path);

        return $disk->response($preAlert->invoice_path, $filename, [
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
