document.addEventListener("DOMContentLoaded", function () {
  const chartEl = document.querySelector("#chart");
  const filterEl = document.querySelector("#chartFilter");
  const yearFilterEl = document.querySelector("#chartYearFilter");
  const monthFilterEl = document.querySelector("#chartMonthFilter");
  const startDateEl = document.querySelector("#chartStartDate");
  const endDateEl = document.querySelector("#chartEndDate");

  if (!chartEl || !filterEl || !yearFilterEl || !monthFilterEl || !startDateEl || !endDateEl) return;

  let currentYear = window.dashboardData.currentYear;
  let currentPeriod = 'monthly';
  let currentMonth = window.dashboardData.currentMonth;
  let startDate = 1;
  let endDate = 31;

  // Fungsi format rupiah dengan singkatan
  function formatRupiah(value) {
    if (value >= 1000000000000) {
      return "Rp " + (value / 1000000000000).toFixed(1).replace('.0', '') + " T";
    } else if (value >= 1000000000) {
      return "Rp " + (value / 1000000000).toFixed(1).replace('.0', '') + " M";
    } else if (value >= 1000000) {
      return "Rp " + (value / 1000000).toFixed(1).replace('.0', '') + " jt";
    } else if (value >= 1000) {
      return "Rp " + (value / 1000).toFixed(0) + " rb";
    }
    return "Rp " + Math.round(value);
  }

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
      y: {
        show: true,
        formatter: function(value) {
          return "Rp " + value.toLocaleString('id-ID');
        }
      },
      z: { show: false }
    },
    grid: {
      row: { colors: ['transparent', 'transparent'], opacity: 0.5 },
      borderColor: '#D1D5DB',
      strokeDashArray: 3,
    },
    yaxis: {
      labels: {
        formatter: function(value) {
          return formatRupiah(value);
        },
        style: { 
          fontSize: "13px",
          colors: ['#64748b']
        },
        offsetX: 0,
      }
    },
    xaxis: {
      categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
      tooltip: { enabled: false },
      labels: { 
        style: { 
          fontSize: "14px",
          colors: ['#64748b']
        } 
      },
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

  // Filter Periode (Monthly/Weekly/Daily)
  filterEl.addEventListener("change", function (e) {
    currentPeriod = e.target.value.toLowerCase();
    
    if (currentPeriod === 'weekly') {
      monthFilterEl.style.display = 'block';
      startDateEl.style.display = 'none';
      endDateEl.style.display = 'none';
    } else if (currentPeriod === 'daily') {
      monthFilterEl.style.display = 'block';
      startDateEl.style.display = 'block';
      endDateEl.style.display = 'block';
      
      // Set max berdasarkan jumlah hari dalam bulan
      const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
      startDateEl.max = daysInMonth;
      endDateEl.max = daysInMonth;
      startDateEl.value = 1;
      endDateEl.value = daysInMonth;
      startDate = 1;
      endDate = daysInMonth;
    } else {
      monthFilterEl.style.display = 'none';
      startDateEl.style.display = 'none';
      endDateEl.style.display = 'none';
    }
    
    updateChart();
  });

  // Filter Tahun
  yearFilterEl.addEventListener("change", function (e) {
    currentYear = e.target.value;
    
    // Update max days jika sedang mode daily
    if (currentPeriod === 'daily') {
      const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
      startDateEl.max = daysInMonth;
      endDateEl.max = daysInMonth;
      if (endDate > daysInMonth) {
        endDate = daysInMonth;
        endDateEl.value = daysInMonth;
      }
    }
    
    updateChart();
  });

  // Filter Bulan
  monthFilterEl.addEventListener("change", function (e) {
    currentMonth = parseInt(e.target.value);
    
    // Update max days jika sedang mode daily
    if (currentPeriod === 'daily') {
      const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
      startDateEl.max = daysInMonth;
      endDateEl.max = daysInMonth;
      
      // Reset end date jika melebihi hari di bulan baru
      if (endDate > daysInMonth) {
        endDate = daysInMonth;
        endDateEl.value = daysInMonth;
      }
    }
    
    updateChart();
  });

  // Filter Start Date
  startDateEl.addEventListener("change", function (e) {
    startDate = parseInt(e.target.value) || 1;
    
    // Pastikan start tidak lebih besar dari end
    if (startDate > endDate) {
      startDate = endDate;
      startDateEl.value = endDate;
    }
    
    updateChart();
  });

  // Filter End Date
  endDateEl.addEventListener("change", function (e) {
    endDate = parseInt(e.target.value) || 31;
    
    // Pastikan end tidak lebih kecil dari start
    if (endDate < startDate) {
      endDate = startDate;
      endDateEl.value = startDate;
    }
    
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
    } else if (currentPeriod === "daily") {
      const dailyData = yearData.daily[currentMonth] || [];
      
      // Slice data sesuai range yang dipilih
      const rangeData = dailyData.slice(startDate - 1, endDate);
      
      // Generate labels untuk tanggal
      const dateLabels = [];
      for (let i = startDate; i <= endDate; i++) {
        dateLabels.push(i.toString());
      }
      
      window.chartInstance.updateSeries([{ 
        name: "Revenue", 
        data: rangeData 
      }]);
      window.chartInstance.updateOptions({
        xaxis: { categories: dateLabels }
      });
    }
  }
});

// ===================================================
// CHART PRODUCT
// ===================================================
document.addEventListener("DOMContentLoaded", function () {
  const chartProductEl = document.querySelector("#chartProduct");
  const filterProductEl = document.querySelector("#chartFilterProduct");
  const yearFilterProductEl = document.querySelector("#chartYearFilterProduct");
  const monthFilterProductEl = document.querySelector("#chartMonthFilterProduct");
  const startDateProductEl = document.querySelector("#chartStartDateProduct");
  const endDateProductEl = document.querySelector("#chartEndDateProduct");

  if (!chartProductEl || !filterProductEl || !yearFilterProductEl || !monthFilterProductEl || !startDateProductEl || !endDateProductEl) return;

  let currentYearProduct = window.dashboardData.currentYear;
  let currentPeriodProduct = 'monthly';
  let currentMonthProduct = window.dashboardData.currentMonth;
  let startDateProduct = 1;
  let endDateProduct = 31;

  // Fungsi format rupiah dengan singkatan
  function formatRupiahProduct(value) {
    if (value >= 1000000000000) {
      return "Rp " + (value / 1000000000000).toFixed(1).replace('.0', '') + " T";
    } else if (value >= 1000000000) {
      return "Rp " + (value / 1000000000).toFixed(1).replace('.0', '') + " M";
    } else if (value >= 1000000) {
      return "Rp " + (value / 1000000).toFixed(1).replace('.0', '') + " jt";
    } else if (value >= 1000) {
      return "Rp " + (value / 1000).toFixed(0) + " rb";
    }
    return "Rp " + Math.round(value);
  }

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
      y: {
        show: true,
        formatter: function(value) {
          return "Rp " + value.toLocaleString('id-ID');
        }
      },
      z: { show: false }
    },
    grid: {
      row: { colors: ['transparent', 'transparent'], opacity: 0.5 },
      borderColor: '#D1D5GB',
      strokeDashArray: 3,
    },
    yaxis: {
      labels: {
        formatter: function(value) {
          return formatRupiahProduct(value);
        },
        style: { 
          fontSize: "13px",
          colors: ['#64748b']
        },
        offsetX: 0,
      }
    },
    xaxis: {
      categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
      tooltip: { enabled: false },
      labels: { 
        style: { 
          fontSize: "14px",
          colors: ['#64748b']
        } 
      },
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

  // Filter Periode (Monthly/Weekly/Daily)
  filterProductEl.addEventListener("change", function (e) {
    currentPeriodProduct = e.target.value.toLowerCase();
    
    if (currentPeriodProduct === 'weekly') {
      monthFilterProductEl.style.display = 'block';
      startDateProductEl.style.display = 'none';
      endDateProductEl.style.display = 'none';
    } else if (currentPeriodProduct === 'daily') {
      monthFilterProductEl.style.display = 'block';
      startDateProductEl.style.display = 'block';
      endDateProductEl.style.display = 'block';
      
      const daysInMonth = new Date(currentYearProduct, currentMonthProduct, 0).getDate();
      startDateProductEl.max = daysInMonth;
      endDateProductEl.max = daysInMonth;
      startDateProductEl.value = 1;
      endDateProductEl.value = daysInMonth;
      startDateProduct = 1;
      endDateProduct = daysInMonth;
    } else {
      monthFilterProductEl.style.display = 'none';
      startDateProductEl.style.display = 'none';
      endDateProductEl.style.display = 'none';
    }
    
    updateProductChart();
  });

  // Filter Tahun
  yearFilterProductEl.addEventListener("change", function (e) {
    currentYearProduct = e.target.value;
    
    if (currentPeriodProduct === 'daily') {
      const daysInMonth = new Date(currentYearProduct, currentMonthProduct, 0).getDate();
      startDateProductEl.max = daysInMonth;
      endDateProductEl.max = daysInMonth;
      if (endDateProduct > daysInMonth) {
        endDateProduct = daysInMonth;
        endDateProductEl.value = daysInMonth;
      }
    }
    
    updateProductChart();
  });

  // Filter Bulan
  monthFilterProductEl.addEventListener("change", function (e) {
    currentMonthProduct = parseInt(e.target.value);
    
    if (currentPeriodProduct === 'daily') {
      const daysInMonth = new Date(currentYearProduct, currentMonthProduct, 0).getDate();
      startDateProductEl.max = daysInMonth;
      endDateProductEl.max = daysInMonth;
      
      if (endDateProduct > daysInMonth) {
        endDateProduct = daysInMonth;
        endDateProductEl.value = daysInMonth;
      }
    }
    
    updateProductChart();
  });

  // Filter Start Date
  startDateProductEl.addEventListener("change", function (e) {
    startDateProduct = parseInt(e.target.value) || 1;
    
    if (startDateProduct > endDateProduct) {
      startDateProduct = endDateProduct;
      startDateProductEl.value = endDateProduct;
    }
    
    updateProductChart();
  });

  // Filter End Date
  endDateProductEl.addEventListener("change", function (e) {
    endDateProduct = parseInt(e.target.value) || 31;
    
    if (endDateProduct < startDateProduct) {
      endDateProduct = startDateProduct;
      endDateProductEl.value = startDateProduct;
    }
    
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
    } else if (currentPeriodProduct === "daily") {
      const dailyData = yearData.daily[currentMonthProduct] || [];
      
      const rangeData = dailyData.slice(startDateProduct - 1, endDateProduct);
      
      const dateLabels = [];
      for (let i = startDateProduct; i <= endDateProduct; i++) {
        dateLabels.push(i.toString());
      }
      
      window.chartProductInstance.updateSeries([{ 
        name: "Penjualan Produk", 
        data: rangeData 
      }]);
      window.chartProductInstance.updateOptions({
        xaxis: { categories: dateLabels }
      });
    }
  }
});