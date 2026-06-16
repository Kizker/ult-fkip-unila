@extends('layouts.app')

@section('title', 'Pelaporan & Statistik')

@section('content')
<div class="page-admin-reports">
    <header class="admin-page-header">
        <div class="admin-page-heading">
            <div class="admin-page-kicker">Analitik</div>
            <h1 class="admin-page-title">Pelaporan Transaksi</h1>
            <p class="admin-page-subtitle">Visualisasi data transaksi permohonan layanan ULT.</p>
        </div>
        <div class="admin-page-actions">
            <form action="{{ route('admin.reports.index') }}" method="GET" class="flex items-center gap-2">
                <select name="year" class="form-select" onchange="this.form.submit()">
                    @for($y = date('Y'); $y >= 2023; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <x-card>
            <div class="p-4 border-b border-slate-200 dark:border-slate-800">
                <h2 class="text-lg font-bold">Grafik Permohonan Bulanan</h2>
            </div>
            <div class="p-4">
                <canvas id="monthlyChart" height="250"></canvas>
            </div>
        </x-card>

        <x-card>
            <div class="p-4 border-b border-slate-200 dark:border-slate-800">
                <h2 class="text-lg font-bold">Distribusi Status Permohonan</h2>
            </div>
            <div class="p-4">
                <canvas id="statusChart" height="250"></canvas>
            </div>
        </x-card>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data Bulanan
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Jumlah Permohonan',
                    data: @json($monthlyChartData),
                    backgroundColor: 'rgba(124, 58, 237, 0.6)',
                    borderColor: 'rgba(124, 58, 237, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });

        // Data Status
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusDataRaw = @json($statusData);
        const statusLabels = Object.keys(statusDataRaw).map(s => s.replace(/_/g, ' ').toUpperCase());
        const statusValues = Object.values(statusDataRaw);

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#64748b'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    });
</script>
@endsection
