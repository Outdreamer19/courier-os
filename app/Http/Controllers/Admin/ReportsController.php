<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Support\Tenancy\TenantConfig;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReportsController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
        private readonly TenantConfig $tenantConfig,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/reports/Index', [
            'currency' => $this->tenantConfig->currency(),
            'revenueByMonth' => $this->reports->revenueByMonth(),
            'packageVolumeByMonth' => $this->reports->packageVolumeByMonth(),
            'unpaidTotal' => $this->reports->unpaidTotal(),
            'unpaidCount' => $this->reports->unpaidCount(),
            'topCustomers' => $this->reports->topCustomers(),
        ]);
    }

    public function export(): HttpResponse
    {
        $rows = $this->reports->monthlyCsvRows();
        $currency = $this->tenantConfig->currency();

        $csv = "Month,Revenue ({$currency}),Packages\n";
        foreach ($rows as $row) {
            $csv .= "\"{$row['month']}\",{$row['revenue']},{$row['packages']}\n";
        }

        $filename = 'courieros-report-'.now()->format('Y-m').'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
