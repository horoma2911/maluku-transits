const Charts = {
  revenueChart(canvasId) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
          label: 'Revenue (TZS)',
          data: [850000, 920000, 780000, 1100000, 1250000, 1180000, 1350000, 1420000, 1380000, 1500000, 1620000, 1750000],
          borderColor: '#99CC33',
          backgroundColor: 'rgba(153,204,51,0.1)',
          fill: true,
          tension: 0.4,
          borderWidth: 2,
          pointRadius: 0,
          pointHoverRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: { color: '#6B7280' } },
          y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#6B7280' } }
        }
      }
    });
  },

  tripsChart(canvasId) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
          label: 'Trips',
          data: [45, 52, 38, 60, 55, 68, 72, 65, 58, 70, 75, 80],
          backgroundColor: '#99CC33',
          borderRadius: 4,
          barThickness: 24
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: { color: '#6B7280' } },
          y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#6B7280' } }
        }
      }
    });
  },

  doughnutChart(canvasId, labels, data, colors) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{ data, backgroundColor: colors, borderWidth: 0, hoverOffset: 4 }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
        },
        cutout: '65%'
      }
    });
  }
};
