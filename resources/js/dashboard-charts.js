/**
 * Dashboard Charts Module
 * Lazy-loaded Chart.js integration for admin dashboard.
 */
import {
  Chart,
  LineController,
  DoughnutController,
  LineElement,
  PointElement,
  ArcElement,
  CategoryScale,
  LinearScale,
  Filler,
  Tooltip,
  Legend,
} from 'chart.js';

Chart.register(
  LineController,
  DoughnutController,
  LineElement,
  PointElement,
  ArcElement,
  CategoryScale,
  LinearScale,
  Filler,
  Tooltip,
  Legend
);

/**
 * Create the trend line chart.
 */
export function createTrendChart(canvasId, labels, values) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return null;

  const ctx = canvas.getContext('2d');

  // Create gradient fill
  const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height || 300);
  gradient.addColorStop(0, 'rgba(124, 58, 237, 0.28)');
  gradient.addColorStop(1, 'rgba(124, 58, 237, 0.02)');

  return new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Jumlah Permohonan',
        data: values,
        borderColor: 'rgba(124, 58, 237, 1)',
        backgroundColor: gradient,
        borderWidth: 2.5,
        pointBackgroundColor: '#fff',
        pointBorderColor: 'rgba(124, 58, 237, 1)',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointHoverBackgroundColor: 'rgba(124, 58, 237, 1)',
        fill: true,
        tension: 0.4,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        intersect: false,
        mode: 'index',
      },
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          backgroundColor: 'rgba(15, 23, 42, 0.92)',
          titleFont: { family: 'Poppins', size: 12, weight: '600' },
          bodyFont: { family: 'Poppins', size: 12 },
          padding: { top: 10, bottom: 10, left: 14, right: 14 },
          cornerRadius: 10,
          displayColors: false,
          callbacks: {
            title: (items) => items[0]?.label || '',
            label: (item) => `${item.formattedValue} permohonan`,
          },
        },
      },
      scales: {
        x: {
          grid: {
            display: false,
          },
          ticks: {
            font: { family: 'Poppins', size: 11 },
            color: 'rgb(100, 116, 139)',
            maxRotation: 45,
          },
          border: {
            display: false,
          },
        },
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(226, 232, 240, 0.5)',
          },
          ticks: {
            font: { family: 'Poppins', size: 11 },
            color: 'rgb(100, 116, 139)',
            precision: 0,
          },
          border: {
            display: false,
          },
        },
      },
    },
  });
}

/**
 * Create the status distribution doughnut chart.
 */
export function createStatusChart(canvasId, labels, values, colors) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return null;

  const ctx = canvas.getContext('2d');
  const total = values.reduce((s, v) => s + v, 0);

  return new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data: values,
        backgroundColor: colors,
        borderColor: '#fff',
        borderWidth: 3,
        hoverBorderColor: '#fff',
        hoverBorderWidth: 4,
        hoverOffset: 8,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '68%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            usePointStyle: true,
            pointStyle: 'circle',
            padding: 18,
            font: { family: 'Poppins', size: 12, weight: '500' },
            color: 'rgb(100, 116, 139)',
          },
        },
        tooltip: {
          backgroundColor: 'rgba(15, 23, 42, 0.92)',
          titleFont: { family: 'Poppins', size: 12, weight: '600' },
          bodyFont: { family: 'Poppins', size: 12 },
          padding: { top: 10, bottom: 10, left: 14, right: 14 },
          cornerRadius: 10,
          callbacks: {
            label: (item) => {
              const pct = total > 0 ? ((item.raw / total) * 100).toFixed(1) : 0;
              return ` ${item.label}: ${item.formattedValue} (${pct}%)`;
            },
          },
        },
      },
    },
    plugins: [{
      id: 'centerText',
      afterDraw(chart) {
        const { ctx: c, chartArea } = chart;
        const cx = (chartArea.left + chartArea.right) / 2;
        const cy = (chartArea.top + chartArea.bottom) / 2;

        c.save();
        c.textAlign = 'center';
        c.textBaseline = 'middle';

        c.font = '700 1.6rem Poppins, sans-serif';
        c.fillStyle = getComputedStyle(document.documentElement)
          .getPropertyValue('--c-fg')
          .trim()
          .split(' ')
          .length === 3
          ? `rgb(${getComputedStyle(document.documentElement).getPropertyValue('--c-fg').trim()})`
          : '#111827';
        c.fillText(total.toLocaleString('id-ID'), cx, cy - 8);

        c.font = '500 0.72rem Poppins, sans-serif';
        c.fillStyle = 'rgb(100, 116, 139)';
        c.fillText('Total', cx, cy + 14);

        c.restore();
      },
    }],
  });
}

/**
 * Initialize all dashboard charts from DOM data attributes.
 */
export function initDashboardCharts() {
  const container = document.querySelector('[data-dashboard-charts]');
  if (!container) return;

  try {
    const trendData = JSON.parse(container.dataset.trend || '{}');
    const statusData = JSON.parse(container.dataset.status || '{}');

    if (trendData.labels && trendData.values) {
      createTrendChart('dashboardTrendChart', trendData.labels, trendData.values);
    }

    if (statusData.labels && statusData.values && statusData.colors) {
      createStatusChart('dashboardStatusChart', statusData.labels, statusData.values, statusData.colors);
    }
  } catch (e) {
    console.error('Failed to init dashboard charts:', e);
  }
}

// Expose globally for Blade template fallback
window.initDashboardCharts = initDashboardCharts;

// Auto-init when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initDashboardCharts);
} else {
  initDashboardCharts();
}
