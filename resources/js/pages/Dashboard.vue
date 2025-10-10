<script lang="ts" setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { AreaChart } from '@/components/ui/chart-area';
import { BarChart } from '@/components/ui/chart-bar';
import { ArrowUpIcon, ArrowDownIcon, TrendingUpIcon, UsersIcon, PackageIcon, ShoppingCartIcon } from 'lucide-vue-next';
import { computed } from 'vue';

interface Stats {
    revenue: {
        total: number;
        totalFormatted: string;
        count: number;
    };
    expenses: {
        total: number;
        totalFormatted: string;
        count: number;
    };
    profit: {
        total: number;
        totalFormatted: string;
        margin: number;
    };
    entities: {
        clients: number;
        suppliers: number;
        products: number;
    };
}

interface MonthlyData {
    month: string;
    revenue: number;
    revenueFormatted: string;
    expenses: number;
    expensesFormatted: string;
    clientOrders: number;
    supplierOrders: number;
}

const props = defineProps<{
    stats: Stats;
    monthlyData: MonthlyData[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const formatNumber = (num: number): string => {
    return new Intl.NumberFormat('sq-AL').format(num);
};

const revenueTrend = computed(() => {
    if (props.monthlyData.length < 2) return 0;
    const lastMonth = props.monthlyData[props.monthlyData.length - 1].revenue;
    const previousMonth = props.monthlyData[props.monthlyData.length - 2].revenue;
    if (previousMonth === 0) return lastMonth > 0 ? 100 : 0;
    return ((lastMonth - previousMonth) / previousMonth) * 100;
});

const expensesTrend = computed(() => {
    if (props.monthlyData.length < 2) return 0;
    const lastMonth = props.monthlyData[props.monthlyData.length - 1].expenses;
    const previousMonth = props.monthlyData[props.monthlyData.length - 2].expenses;
    if (previousMonth === 0) return lastMonth > 0 ? 100 : 0;
    return ((lastMonth - previousMonth) / previousMonth) * 100;
});

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Top Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Revenue Card -->
                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardDescription>Total Revenue</CardDescription>
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/20">
                                <ArrowUpIcon class="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                        </div>
                        <CardTitle class="text-2xl">{{ props.stats.revenue.totalFormatted }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-2 text-sm">
                            <div class="flex items-center gap-1" :class="[revenueTrend >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400']">
                                <component :is="revenueTrend >= 0 ? ArrowUpIcon : ArrowDownIcon" class="h-3 w-3" />
                                <span class="font-medium">{{ Math.abs(revenueTrend).toFixed(1) }}%</span>
                            </div>
                            <span class="text-muted-foreground">from last month</span>
                        </div>
                        <p class="mt-2 text-xs text-muted-foreground">
                            {{ props.stats.revenue.count }} orders from clients
                        </p>
                    </CardContent>
                </Card>

                <!-- Expenses Card -->
                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardDescription>Total Expenses</CardDescription>
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/20">
                                <ArrowDownIcon class="h-5 w-5 text-red-600 dark:text-red-400" />
                            </div>
                        </div>
                        <CardTitle class="text-2xl">{{ props.stats.expenses.totalFormatted }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-2 text-sm">
                            <div class="flex items-center gap-1" :class="[expensesTrend >= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400']">
                                <component :is="expensesTrend >= 0 ? ArrowUpIcon : ArrowDownIcon" class="h-3 w-3" />
                                <span class="font-medium">{{ Math.abs(expensesTrend).toFixed(1) }}%</span>
                            </div>
                            <span class="text-muted-foreground">from last month</span>
                        </div>
                        <p class="mt-2 text-xs text-muted-foreground">
                            {{ props.stats.expenses.count }} orders to suppliers
                        </p>
                    </CardContent>
                </Card>

                <!-- Profit Card -->
                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardDescription>Net Profit</CardDescription>
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/20">
                                <TrendingUpIcon class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                        <CardTitle class="text-2xl" :class="[props.stats.profit.total >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400']">
                            {{ props.stats.profit.totalFormatted }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="font-medium">{{ props.stats.profit.margin.toFixed(1) }}%</span>
                            <span class="text-muted-foreground">profit margin</span>
                        </div>
                        <p class="mt-2 text-xs text-muted-foreground">
                            {{ props.stats.profit.total >= 0 ? 'Positive' : 'Negative' }} cash flow
                        </p>
                    </CardContent>
                </Card>

                <!-- Orders Summary Card -->
                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardDescription>Total Orders</CardDescription>
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/20">
                                <ShoppingCartIcon class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                            </div>
                        </div>
                        <CardTitle class="text-2xl">
                            {{ formatNumber(props.stats.revenue.count + props.stats.expenses.count) }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-1 text-xs text-muted-foreground">
                            <div class="flex justify-between">
                                <span>Client orders:</span>
                                <span class="font-medium">{{ formatNumber(props.stats.revenue.count) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Supplier orders:</span>
                                <span class="font-medium">{{ formatNumber(props.stats.expenses.count) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts Section -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Revenue vs Expenses Chart -->
                <Card>
                    <CardHeader class="border-b">
                        <CardTitle>Revenue vs Expenses</CardTitle>
                        <CardDescription>
                            Financial overview for the last 6 months
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pt-6">
                        <AreaChart
                            :data="props.monthlyData"
                            index="month"
                            :categories="['revenue', 'expenses']"
                            :colors="['#10b981', '#ef4444']"
                            :formatted-fields="{ revenue: 'revenueFormatted', expenses: 'expensesFormatted' }"
                            class="h-[300px]"
                        />
                    </CardContent>
                </Card>

                <!-- Orders Count Chart -->
                <Card>
                    <CardHeader class="border-b">
                        <CardTitle>Orders Overview</CardTitle>
                        <CardDescription>
                            Number of orders by type for the last 6 months
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pt-6">
                        <BarChart
                            :data="props.monthlyData"
                            index="month"
                            :categories="['clientOrders', 'supplierOrders']"
                            :colors="['#3b82f6', '#f59e0b']"
                            type="grouped"
                            :rounded-corners="4"
                            class="h-[300px]"
                        />
                    </CardContent>
                </Card>
            </div>

            <!-- Bottom Stats Grid -->
            <div class="grid gap-4 md:grid-cols-3">
                <!-- Clients Card -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardDescription>Active Clients</CardDescription>
                                <CardTitle class="mt-2 text-3xl">{{ formatNumber(props.stats.entities.clients) }}</CardTitle>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/20">
                                <UsersIcon class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                            </div>
                        </div>
                    </CardHeader>
                </Card>

                <!-- Suppliers Card -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardDescription>Active Suppliers</CardDescription>
                                <CardTitle class="mt-2 text-3xl">{{ formatNumber(props.stats.entities.suppliers) }}</CardTitle>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900/20">
                                <UsersIcon class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                            </div>
                        </div>
                    </CardHeader>
                </Card>

                <!-- Products Card -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardDescription>Total Products</CardDescription>
                                <CardTitle class="mt-2 text-3xl">{{ formatNumber(props.stats.entities.products) }}</CardTitle>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-teal-100 dark:bg-teal-900/20">
                                <PackageIcon class="h-6 w-6 text-teal-600 dark:text-teal-400" />
                            </div>
                        </div>
                    </CardHeader>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
