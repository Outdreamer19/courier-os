<script setup lang="ts">
import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';
import { Download, TrendingUp } from 'lucide-vue-next';
import { Line } from 'vue-chartjs';
import BarChart from '@/components/charts/BarChart.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { chartBarColors, chartGridColor } from '@/lib/chartColors';
import { formatMoney } from '@/lib/money';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as reportsIndex, exportMethod as reportsExport } from '@/routes/admin/reports';
import { computed } from 'vue';

ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, Tooltip, Legend);

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Reports', href: reportsIndex() },
        ],
    },
});

const props = defineProps<{
    currency: string;
    revenueByMonth: { labels: string[]; data: number[] };
    packageVolumeByMonth: { labels: string[]; data: number[] };
    unpaidTotal: number;
    unpaidCount: number;
    topCustomers: Array<{
        customer_id: number;
        name: string;
        email: string;
        total_paid: number;
        package_count: number;
    }>;
}>();

const fmt = (amount: number) => formatMoney(amount, props.currency);

const volumeChartData = computed(() => ({
    labels: props.packageVolumeByMonth.labels,
    datasets: [
        {
            label: 'Packages',
            data: props.packageVolumeByMonth.data,
            borderColor: chartBarColors[1],
            backgroundColor: chartBarColors[1] + '33',
            tension: 0.3,
            fill: true,
            pointRadius: 4,
        },
    ],
}));

const volumeChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: {
            beginAtZero: true,
            ticks: { precision: 0 },
            grid: { color: chartGridColor },
        },
        x: { grid: { display: false } },
    },
};
</script>

<template>
    <Head title="Reports" />

    <div class="space-y-6 p-6">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Reports</h1>
                <p class="text-muted-foreground text-sm">Last 12 months of activity for this tenant.</p>
            </div>
            <Button variant="outline" as-child>
                <a :href="reportsExport().url" download>
                    <Download class="mr-2 h-4 w-4" />
                    Export CSV
                </a>
            </Button>
        </div>

        <!-- KPI cards -->
        <div class="grid gap-4 sm:grid-cols-3">
            <Card>
                <CardHeader class="pb-2">
                    <CardDescription>Total revenue (12 mo)</CardDescription>
                    <CardTitle class="text-3xl">
                        {{ fmt(revenueByMonth.data.reduce((a, b) => a + b, 0)) }}
                    </CardTitle>
                </CardHeader>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardDescription>Total packages (12 mo)</CardDescription>
                    <CardTitle class="text-3xl">
                        {{ packageVolumeByMonth.data.reduce((a, b) => a + b, 0).toLocaleString() }}
                    </CardTitle>
                </CardHeader>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardDescription>Outstanding balance</CardDescription>
                    <CardTitle class="text-3xl text-amber-600 dark:text-amber-400">
                        {{ fmt(unpaidTotal) }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-muted-foreground text-sm">{{ unpaidCount }} unpaid package{{ unpaidCount !== 1 ? 's' : '' }}</p>
                </CardContent>
            </Card>
        </div>

        <!-- Charts -->
        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Revenue by month</CardTitle>
                    <CardDescription>Paid packages · {{ currency }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <BarChart
                        :labels="revenueByMonth.labels"
                        :data="revenueByMonth.data"
                        :label="`Revenue (${currency})`"
                    />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        Package volume
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardTitle>
                    <CardDescription>Packages created per month</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="h-64">
                        <Line :data="volumeChartData" :options="volumeChartOptions" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Top customers table -->
        <Card>
            <CardHeader>
                <CardTitle>Top customers</CardTitle>
                <CardDescription>Ranked by total revenue paid</CardDescription>
            </CardHeader>
            <CardContent class="p-0">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="pl-6">#</TableHead>
                            <TableHead>Customer</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead class="text-right">Packages</TableHead>
                            <TableHead class="pr-6 text-right">Revenue ({{ currency }})</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="topCustomers.length === 0">
                            <TableCell colspan="5" class="text-muted-foreground py-8 text-center">
                                No paid packages yet.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="(customer, i) in topCustomers" :key="customer.customer_id">
                            <TableCell class="pl-6 text-muted-foreground">{{ i + 1 }}</TableCell>
                            <TableCell class="font-medium">{{ customer.name }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ customer.email }}</TableCell>
                            <TableCell class="text-right">{{ customer.package_count }}</TableCell>
                            <TableCell class="pr-6 text-right font-mono">
                                {{ fmt(customer.total_paid) }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    </div>
</template>
