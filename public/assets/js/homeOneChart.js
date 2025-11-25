document.addEventListener("DOMContentLoaded", function () {
  const chartEl = document.querySelector("#chart");
  const filterEl = document.querySelector("#chartFilter");
  const yearFilterEl = document.querySelector("#chartYearFilter");
  const monthFilterEl = document.querySelector("#chartMonthFilter");

  if (!chartEl || !filterEl || !yearFilterEl || !monthFilterEl) return;

  let currentYear = window.dashboardData.currentYear;
  let currentPeriod = 'monthly';
  let currentMonth = window.dashboardData.currentMonth;

  const options = {
    series: [{
      name: "Revenue",
      data: window.dashboardData.membershipByYear[currentYear]?.monthly || []
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

  if (window.chartInstance) {
    window.chartInstance.destroy();
  }

  window.chartInstance = new ApexCharts(chartEl, options);
  window.chartInstance.render();

  // Filter Periode (Monthly/Weekly)
  filterEl.addEventListener("change", function (e) {
    currentPeriod = e.target.value.toLowerCase();
    
    // Tampilkan/sembunyikan dropdown bulan
    if (currentPeriod === 'weekly') {
      monthFilterEl.style.display = 'block';
    } else {
      monthFilterEl.style.display = 'none';
    }
    
    updateChart();
  });

  // Filter Tahun
  yearFilterEl.addEventListener("change", function (e) {
    currentYear = e.target.value;
    updateChart();
  });

  // Filter Bulan (untuk weekly)
  monthFilterEl.addEventListener("change", function (e) {
    currentMonth = parseInt(e.target.value);
    updateChart();
  });

  function updateChart() {
    if (!window.chartInstance) return;

    const yearData = window.dashboardData.membershipByYear[currentYear];
    if (!yearData) return;

    if (currentPeriod === "monthly") {
      window.chartInstance.updateSeries([{ 
        name: "Revenue", 
        data: yearData.monthly 
      }]);
      window.chartInstance.updateOptions({
        xaxis: { categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] }
      });
    } else if (currentPeriod === "weekly") {
      const weeklyData = yearData.weekly[currentMonth] || [];
      const weekLabels = weeklyData.map((_, index) => `Week ${index + 1}`);
      
      window.chartInstance.updateSeries([{ 
        name: "Revenue", 
        data: weeklyData 
      }]);
      window.chartInstance.updateOptions({
        xaxis: { categories: weekLabels }
      });
    }
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const chartProductEl = document.querySelector("#chartProduct");
  const filterProductEl = document.querySelector("#chartFilterProduct");
  const yearFilterProductEl = document.querySelector("#chartYearFilterProduct");
  const monthFilterProductEl = document.querySelector("#chartMonthFilterProduct");

  if (!chartProductEl || !filterProductEl || !yearFilterProductEl || !monthFilterProductEl) return;

  let currentYearProduct = window.dashboardData.currentYear;
  let currentPeriodProduct = 'monthly';
  let currentMonthProduct = window.dashboardData.currentMonth;

  const optionsProduct = {
    series: [{
      name: "Penjualan Produk",
      data: window.dashboardData.productByYear[currentYearProduct]?.monthly || []
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
      colors: ['#10B981'],
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
        fill: { type: 'solid', color: '#10B98140' }
      }
    }
  };

  if (window.chartProductInstance) {
    window.chartProductInstance.destroy();
  }

  window.chartProductInstance = new ApexCharts(chartProductEl, optionsProduct);
  window.chartProductInstance.render();

  // Filter Periode (Monthly/Weekly)
  filterProductEl.addEventListener("change", function (e) {
    currentPeriodProduct = e.target.value.toLowerCase();
    
    // Tampilkan/sembunyikan dropdown bulan
    if (currentPeriodProduct === 'weekly') {
      monthFilterProductEl.style.display = 'block';
    } else {
      monthFilterProductEl.style.display = 'none';
    }
    
    updateProductChart();
  });

  // Filter Tahun
  yearFilterProductEl.addEventListener("change", function (e) {
    currentYearProduct = e.target.value;
    updateProductChart();
  });

  // Filter Bulan (untuk weekly)
  monthFilterProductEl.addEventListener("change", function (e) {
    currentMonthProduct = parseInt(e.target.value);
    updateProductChart();
  });

  function updateProductChart() {
    if (!window.chartProductInstance) return;

    const yearData = window.dashboardData.productByYear[currentYearProduct];
    if (!yearData) return;

    if (currentPeriodProduct === "monthly") {
      window.chartProductInstance.updateSeries([{ 
        name: "Penjualan Produk", 
        data: yearData.monthly 
      }]);
      window.chartProductInstance.updateOptions({
        xaxis: { categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] }
      });
    } else if (currentPeriodProduct === "weekly") {
      const weeklyData = yearData.weekly[currentMonthProduct] || [];
      const weekLabels = weeklyData.map((_, index) => `Week ${index + 1}`);
      
      window.chartProductInstance.updateSeries([{ 
        name: "Penjualan Produk", 
        data: weeklyData 
      }]);
      window.chartProductInstance.updateOptions({
        xaxis: { categories: weekLabels }
      });
    }
  }
});