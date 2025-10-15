document.addEventListener("DOMContentLoaded", function () {
  const chartEl = document.querySelector("#chart");
  const filterEl = document.querySelector("#chartFilter");

  if (!chartEl || !filterEl) return;

  const options = {
    series: [{
      name: "This month",
      data: window.dashboardData ? window.dashboardData.monthlyData : []
    }],
    chart: {
      height: 264,
      type: 'line',
      toolbar: { show: false },
      zoom: { enabled: false },
      dropShadow: {
        enabled: true,
        top: 6,
        left: 0,
        blur: 4,
        color: "#000",
        opacity: 0.1,
      },

    },
    dataLabels: { enabled: false },
    stroke: {
      curve: 'smooth',
      colors: ['#487FFF'],
      width: 3
    },
    markers: {
      size: 0,
      strokeWidth: 3,
      hover: { size: 8 }
    },
    tooltip: {
      enabled: true,
      x: { show: true },
      y: { show: false },
      z: { show: false }
    },
    grid: {
      row: { colors: ['transparent', 'transparent'], opacity: 0.5 },
      borderColor: '#D1D5DB',
      strokeDashArray: 3,
    },
    yaxis: {
      labels: {
        formatter: function(value) { return "Rp " + value.toLocaleString(); },
        style: { fontSize: "14px" },
        // Geser sedikit ke kanan supaya muat
        offsetX: 100,
        align: 'right',
      }
    },
    xaxis: {
      categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
      tooltip: { enabled: false },
      labels: { style: { fontSize: "14px" } },
      axisBorder: { show: false },
      crosshairs: {
        show: true,
        width: 30,
        stroke: { width: 0 },
        fill: { type: 'solid', color: '#487FFF40' }
      }
    }
  };

  // 🔥 Pastikan hanya ada satu chart instance
  if (window.chartInstance) {
    window.chartInstance.destroy();
  }

  window.chartInstance = new ApexCharts(chartEl, options);
  window.chartInstance.render();

  // 🟣 Event dropdown filter chart
  filterEl.addEventListener("change", function (e) {
    const value = e.target.value.toLowerCase();

    if (!window.chartInstance) return;

    if (value === "monthly") {
      window.chartInstance.updateSeries([{ name: "Revenue", data: window.dashboardData.monthlyData }]);
      window.chartInstance.updateOptions({
        xaxis: { categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] }
      });
    } else if (value === "weekly") {
      window.chartInstance.updateSeries([{ name: "Revenue", data: window.dashboardData.weeklyData }]);
      window.chartInstance.updateOptions({
        xaxis: { categories: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] }
      });
    }
  });


});
