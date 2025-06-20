// Constantes
const CHART_COLORS = [
  "#2563eb", // blue-600
  "#16a34a", // green-600
  "#ea580c", // orange-600
  "#dc2626", // red-600
  "#9333ea", // purple-600
  "#0891b2", // cyan-600
  "#4f46e5", // indigo-600
  "#c026d3", // fuchsia-600
  "#65a30d", // lime-600
  "#0d9488", // teal-600
];

const FAIXAS_ETARIAS = [
  { value: "de_20_a_29_anos", label: "20 a 29 anos" },
  { value: "de_30_a_39_anos", label: "30 a 39 anos" },
  { value: "de_40_a_49_anos", label: "40 a 49 anos" },
  { value: "de_50_a_59_anos", label: "50 a 59 anos" },
  { value: "de_60_a_69_anos", label: "60 a 69 anos" },
  { value: "de_70_a_79_anos", label: "70 a 79 anos" },
  { value: "de_80_anos_e_mais", label: "80 anos e mais" },
];

const REGIOES = [
  { value: "Região Norte", label: "Região Norte" },
  { value: "Região Nordeste", label: "Região Nordeste" },
  { value: "Região Sudeste", label: "Região Sudeste" },
  { value: "Região Sul", label: "Região Sul" },
  { value: "Região Centro-Oeste", label: "Região Centro-Oeste" },
];

// Estado global
let dashboardData = {
  kpis: {
    totalObitos: 0,
    taxaMortalidade: 0,
    custoTotal: 0,
    custoMedio: 0,
    taxaVariacao: 0,
    custoVariacao: 0,
  },
  graficos: {
    dadosPorRegiao: [],
    dadosPorFaixaEtaria: [],
    tendenciaAnos: [],
    custosPorFaixaEtaria: [],
  },
};

let currentFilters = {
  ano: "2024",
  regiao: "",
};

// Funções utilitárias
function formatCurrency(value) {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(value);
}

function calculatePercentageChange(current, previous) {
  if (previous === 0) return 0;
  return Number((((current - previous) / previous) * 100).toFixed(2));
}

// Funções de manipulação de DOM
function updateKPIs() {
  document.getElementById("total-obitos").textContent = dashboardData.kpis.totalObitos.toLocaleString("pt-BR");
  document.getElementById("taxa-mortalidade").textContent = `${dashboardData.kpis.taxaMortalidade.toFixed(2)}%`;
  document.getElementById("custo-total").textContent = formatCurrency(dashboardData.kpis.custoTotal);
  document.getElementById("custo-medio").textContent = formatCurrency(dashboardData.kpis.custoMedio);

  const taxaVariacao = document.getElementById("taxa-variacao");
  taxaVariacao.textContent = `${dashboardData.kpis.taxaVariacao > 0 ? "↑" : "↓"} ${Math.abs(dashboardData.kpis.taxaVariacao).toFixed(2)}%`;
  taxaVariacao.className = `trend-value ${dashboardData.kpis.taxaVariacao > 0 ? "positive" : "negative"}`;

  const custoVariacao = document.getElementById("custo-variacao");
  custoVariacao.textContent = `${dashboardData.kpis.custoVariacao > 0 ? "↑" : "↓"} ${Math.abs(dashboardData.kpis.custoVariacao).toFixed(2)}%`;
  custoVariacao.className = `trend-value ${dashboardData.kpis.custoVariacao > 0 ? "positive" : "negative"}`;
}

// Funções de criação de gráficos
function createBarChart(elementId, data, xAxisKey, dataKey, label, color) {
  const ctx = document.getElementById(elementId);

  if (!ctx) {
    console.error(`Elemento com ID ${elementId} não encontrado`);
    return null;
  }

  if (!data || !Array.isArray(data) || data.length === 0) {
    console.error(`Dados inválidos para o gráfico ${elementId}`, data);
    return null;
  }

  return new Chart(ctx, {
    type: 'bar',
    data: {
      labels: data.map(item => item[xAxisKey]),
      datasets: [{
        label: label,
        data: data.map(item => item[dataKey]),
        backgroundColor: color,
        borderColor: color,
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
}

function createLineChart(elementId, data, xAxisKey, lines) {
  const ctx = document.getElementById(elementId);

  if (!ctx) {
    console.error(`Elemento com ID ${elementId} não encontrado`);
    return null;
  }

  if (!data || !Array.isArray(data) || data.length === 0) {
    console.error(`Dados inválidos para o gráfico ${elementId}`, data);
    return null;
  }

  const datasets = lines.map((line, index) => ({
    label: line.name,
    data: data.map(item => item[line.dataKey]),
    borderColor: line.color || CHART_COLORS[index],
    backgroundColor: line.color || CHART_COLORS[index],
    tension: 0.1
  }));

  return new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.map(item => item[xAxisKey]),
      datasets: datasets
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
}

function createPieChart(elementId, data, dataKey, nameKey, colors) {
  const ctx = document.getElementById(elementId);

  if (!ctx) {
    console.error(`Elemento com ID ${elementId} não encontrado`);
    return null;
  }

  if (!data || !Array.isArray(data) || data.length === 0) {
    console.error(`Dados inválidos para o gráfico ${elementId}`, data);
    return null;
  }

  return new Chart(ctx, {
    type: 'pie',
    data: {
      labels: data.map(item => item[nameKey]),
      datasets: [{
        data: data.map(item => item[dataKey]),
        backgroundColor: colors.slice(0, data.length),
        hoverOffset: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right',
        }
      }
    }
  });
}

// Inicializar gráficos
let charts = {};

function initializeCharts() {
  console.log("Inicializando gráficos...");

  const chartElements = [
    { id: 'chart-obitos-regiao', type: 'canvas' },
    { id: 'chart-obitos-faixa-etaria', type: 'canvas' },
    { id: 'chart-tendencia-anos', type: 'canvas' },
    { id: 'chart-custos-faixa-etaria', type: 'canvas' },
    { id: 'chart-tendencia-taxa', type: 'canvas' },
    { id: 'chart-taxa-regiao', type: 'canvas' },
    { id: 'chart-tendencia-obitos', type: 'canvas' },
    { id: 'chart-obitos-faixa-etaria-analise', type: 'canvas' },
    { id: 'chart-tendencia-custos', type: 'canvas' },
    { id: 'chart-custos-faixa-etaria-analise', type: 'canvas' }
  ];

  chartElements.forEach(element => {
    const container = document.getElementById(element.id);
    if (container) {
      container.innerHTML = '';
      const canvas = document.createElement('canvas');
      canvas.id = `${element.id}-canvas`;
      container.appendChild(canvas);
    } else {
      console.error(`Container ${element.id} não encontrado`);
    }
  });

  if (!dashboardData.graficos.dadosPorRegiao || dashboardData.graficos.dadosPorRegiao.length === 0) {
    console.error("Dados por região não disponíveis");
    return;
  }

  if (!dashboardData.graficos.dadosPorFaixaEtaria || dashboardData.graficos.dadosPorFaixaEtaria.length === 0) {
    console.error("Dados por faixa etária não disponíveis");
    return;
  }

  if (!dashboardData.graficos.tendenciaAnos || dashboardData.graficos.tendenciaAnos.length === 0) {
    console.error("Dados de tendência por anos não disponíveis");
    return;
  }

  if (!dashboardData.graficos.custosPorFaixaEtaria || dashboardData.graficos.custosPorFaixaEtaria.length === 0) {
    console.error("Dados de custos por faixa etária não disponíveis");
    return;
  }

  try {
    charts.obitosPorRegiao = createBarChart(
      'chart-obitos-regiao-canvas',
      dashboardData.graficos.dadosPorRegiao,
      'regiao',
      'total_obitos',
      'Total de Óbitos',
      CHART_COLORS[0]
    );

    charts.obitosPorFaixaEtaria = createPieChart(
      'chart-obitos-faixa-etaria-canvas',
      dashboardData.graficos.dadosPorFaixaEtaria,
      'total_obitos',
      'label',
      CHART_COLORS
    );

    charts.tendenciaAnos = createLineChart(
      'chart-tendencia-anos-canvas',
      dashboardData.graficos.tendenciaAnos,
      'ano',
      [
        { dataKey: 'total_obitos', name: 'Total de Óbitos', color: CHART_COLORS[0] },
        { dataKey: 'taxa_media', name: 'Taxa de Mortalidade (%)', color: CHART_COLORS[1] }
      ]
    );

    charts.custosPorFaixaEtaria = createBarChart(
      'chart-custos-faixa-etaria-canvas',
      dashboardData.graficos.custosPorFaixaEtaria,
      'label',
      'custo_total',
      'Custo Total (R$)',
      CHART_COLORS[2]
    );

    charts.tendenciaTaxa = createLineChart(
      'chart-tendencia-taxa-canvas',
      dashboardData.graficos.tendenciaAnos,
      'ano',
      [
        { dataKey: 'taxa_media', name: 'Taxa de Mortalidade (%)', color: CHART_COLORS[1] }
      ]
    );

    charts.taxaPorRegiao = createBarChart(
      'chart-taxa-regiao-canvas',
      dashboardData.graficos.dadosPorRegiao.map(item => ({
        ...item,
        taxa_media: Number.parseFloat((Math.random() * 10).toFixed(2)),
      })),
      'regiao',
      'taxa_media',
      'Taxa de Mortalidade (%)',
      CHART_COLORS[1]
    );

    charts.tendenciaObitos = createLineChart(
      'chart-tendencia-obitos-canvas',
      dashboardData.graficos.tendenciaAnos,
      'ano',
      [
        { dataKey: 'total_obitos', name: 'Total de Óbitos', color: CHART_COLORS[0] }
      ]
    );

    charts.obitosPorFaixaEtariaAnalise = createBarChart(
      'chart-obitos-faixa-etaria-analise-canvas',
      dashboardData.graficos.dadosPorFaixaEtaria,
      'label',
      'total_obitos',
      'Total de Óbitos',
      CHART_COLORS[0]
    );

    charts.tendenciaCustos = createLineChart(
      'chart-tendencia-custos-canvas',
      dashboardData.graficos.tendenciaAnos.map(item => ({
        ...item,
        custo_total: item.total_obitos * 10000,
      })),
      'ano',
      [
        { dataKey: 'custo_total', name: 'Custo Total (R$)', color: CHART_COLORS[2] }
      ]
    );

    charts.custosPorFaixaEtariaAnalise = createBarChart(
      'chart-custos-faixa-etaria-analise-canvas',
      dashboardData.graficos.custosPorFaixaEtaria,
      'label',
      'custo_total',
      'Custo Total (R$)',
      CHART_COLORS[2]
    );

    console.log("Gráficos inicializados com sucesso!");
  } catch (error) {
    console.error("Erro ao inicializar gráficos:", error);
  }
}

function initializeKPIPage() {
  console.log("Inicializando página de KPIs...");

  const taxaDiagnosticoPrecoce = 65 + Math.random() * 15;
  const tempoMedioInicioTratamento = 15 + Math.random() * 10;
  const efetividadeTratamento = 75 + Math.random() * 15;
  const taxaReincidencia = 10 + Math.random() * 8;

  document.getElementById('taxa-diagnostico').textContent = `${taxaDiagnosticoPrecoce.toFixed(1)}%`;
  document.getElementById('tempo-tratamento').textContent = `${tempoMedioInicioTratamento.toFixed(1)} dias`;
  document.getElementById('efetividade').textContent = `${efetividadeTratamento.toFixed(1)}%`;
  document.getElementById('reincidencia').textContent = `${taxaReincidencia.toFixed(1)}%`;

  document.getElementById('progress-diagnostico').style.width = `${taxaDiagnosticoPrecoce}%`;
  document.getElementById('progress-tempo').style.width = `${100 - (tempoMedioInicioTratamento / 30) * 100}%`;
  document.getElementById('progress-efetividade').style.width = `${efetividadeTratamento}%`;
  document.getElementById('progress-reincidencia').style.width = `${100 - taxaReincidencia}%`;

  document.getElementById('meta-diagnostico').textContent = taxaDiagnosticoPrecoce >= 80 ? 'Atingida' : 'Não atingida';
  document.getElementById('meta-tempo').textContent = tempoMedioInicioTratamento <= 15 ? 'Atingida' : 'Não atingida';
  document.getElementById('meta-efetividade').textContent = efetividadeTratamento >= 85 ? 'Atingida' : 'Não atingida';
  document.getElementById('meta-reincidencia').textContent = taxaReincidencia <= 10 ? 'Atingida' : 'Não atingida';

  if (!dashboardData.graficos.dadosPorRegiao || dashboardData.graficos.dadosPorRegiao.length === 0) {
    console.error("Dados por região não disponíveis para KPIs");
    return;
  }

  if (!dashboardData.graficos.dadosPorFaixaEtaria || dashboardData.graficos.dadosPorFaixaEtaria.length === 0) {
    console.error("Dados por faixa etária não disponíveis para KPIs");
    return;
  }

  const efetividadeRegiaoContainer = document.getElementById('efetividade-regiao-container');
  if (efetividadeRegiaoContainer) {
    efetividadeRegiaoContainer.innerHTML = '';

    dashboardData.graficos.dadosPorRegiao.forEach((regiao, index) => {
      const efetividade = 65 + Math.random() * 25;
      const progressItem = document.createElement('div');
      progressItem.className = 'progress-item';

      const progressHeader = document.createElement('div');
      progressHeader.className = 'progress-header';

      const regionName = document.createElement('span');
      regionName.textContent = regiao.regiao;

      const efetividadeValue = document.createElement('span');
      efetividadeValue.textContent = `${efetividade.toFixed(1)}%`;

      progressHeader.appendChild(regionName);
      progressHeader.appendChild(efetividadeValue);

      const progressContainer = document.createElement('div');
      progressContainer.className = 'progress-container';

      const progressBar = document.createElement('div');
      progressBar.className = 'progress-bar';
      progressBar.style.width = `${efetividade}%`;

      progressContainer.appendChild(progressBar);

      progressItem.appendChild(progressHeader);
      progressItem.appendChild(progressContainer);

      efetividadeRegiaoContainer.appendChild(progressItem);
    });
  } else {
    console.error("Container efetividade-regiao-container não encontrado");
  }

  const tempoFaixaEtariaContainer = document.getElementById('tempo-faixa-etaria-container');
  if (tempoFaixaEtariaContainer) {
    tempoFaixaEtariaContainer.innerHTML = '';

    dashboardData.graficos.dadosPorFaixaEtaria.forEach((faixa, index) => {
      const tempoTratamento = 30 + Math.random() * 60;
      const progressItem = document.createElement('div');
      progressItem.className = 'progress-item';

      const progressHeader = document.createElement('div');
      progressHeader.className = 'progress-header';

      const faixaName = document.createElement('span');
      faixaName.textContent = faixa.label;

      const tempoValue = document.createElement('span');
      tempoValue.textContent = `${tempoTratamento.toFixed(0)} dias`;

      progressHeader.appendChild(faixaName);
      progressHeader.appendChild(tempoValue);

      const progressContainer = document.createElement('div');
      progressContainer.className = 'progress-container';

      const progressBar = document.createElement('div');
      progressBar.className = 'progress-bar';
      progressBar.style.width = `${(tempoTratamento / 120) * 100}%`;

      progressContainer.appendChild(progressBar);

      progressItem.appendChild(progressHeader);
      progressItem.appendChild(progressContainer);

      tempoFaixaEtariaContainer.appendChild(progressItem);
    });
  } else {
    console.error("Container tempo-faixa-etaria-container não encontrado");
  }

  console.log("Página de KPIs inicializada com sucesso!");
}

function setupNavigation() {
  const navItems = document.querySelectorAll('.navbar-item');
  const pages = document.querySelectorAll('.page');

  navItems.forEach(item => {
    item.addEventListener('click', (e) => {
      e.preventDefault();

      const targetPage = item.getAttribute('data-page');

      navItems.forEach(navItem => {
        navItem.classList.remove('active');
      });
      item.classList.add('active');

      pages.forEach(page => {
        if (page.id === `${targetPage}-page`) {
          page.classList.remove('hidden');
        } else {
          page.classList.add('hidden');
        }
      });
    });
  });
}

function setupTabs() {
  const tabTriggers = document.querySelectorAll('.tab-trigger');
  const tabContents = document.querySelectorAll('.tab-content');

  tabTriggers.forEach(trigger => {
    trigger.addEventListener('click', () => {
      const targetTab = trigger.getAttribute('data-tab');

      tabTriggers.forEach(tabTrigger => {
        tabTrigger.classList.remove('active');
      });
      trigger.classList.add('active');

      tabContents.forEach(content => {
        if (content.id === `tab-${targetTab}`) {
          content.classList.add('active');
        } else {
          content.classList.remove('active');
        }
      });
    });
  });
}

function setupFilters() {
  const anoSelect = document.getElementById('ano-select');
  const regiaoSelect = document.getElementById('regiao-select');
  const resetButton = document.getElementById('reset-filters');

  const anoSelectKpi = document.getElementById('ano-select-kpi');
  const regiaoSelectKpi = document.getElementById('regiao-select-kpi');
  const resetButtonKpi = document.getElementById('reset-filters-kpi');

  const anoSelectDashboard = document.getElementById('ano-select-dashboard');
  const regiaoSelectDashboard = document.getElementById('regiao-select-dashboard');
  const resetButtonDashboard = document.getElementById('reset-filters-dashboard');

  function updateFilters(ano, regiao) {
    currentFilters.ano = ano;
    currentFilters.regiao = regiao;

    [anoSelect, anoSelectKpi, anoSelectDashboard].forEach(select => {
      if (select) select.value = ano;
    });

    [regiaoSelect, regiaoSelectKpi, regiaoSelectDashboard].forEach(select => {
      if (select) select.value = regiao;
    });

    if (regiao) {
      document.getElementById('filtro-atual').textContent = ` Filtro atual: ${regiao}`;
      document.getElementById('filtro-atual-kpi').textContent = ` Filtro atual: ${regiao}`;
    } else {
      document.getElementById('filtro-atual').textContent = '';
      document.getElementById('filtro-atual-kpi').textContent = '';
    }

    applyFilters();
  }

  if (anoSelect) {
    anoSelect.addEventListener('change', () => {
      updateFilters(anoSelect.value, regiaoSelect.value);
    });
  }

  if (regiaoSelect) {
    regiaoSelect.addEventListener('change', () => {
      updateFilters(anoSelect.value, regiaoSelect.value);
    });
  }

  if (resetButton) {
    resetButton.addEventListener('click', () => {
      updateFilters("2024", "");
    });
  }

  if (anoSelectKpi) {
    anoSelectKpi.addEventListener('change', () => {
      updateFilters(anoSelectKpi.value, regiaoSelectKpi.value);
    });
  }

  if (regiaoSelectKpi) {
    regiaoSelectKpi.addEventListener('change', () => {
      updateFilters(anoSelectKpi.value, regiaoSelectKpi.value);
    });
  }

  if (resetButtonKpi) {
    resetButtonKpi.addEventListener('click', () => {
      updateFilters("2024", "");
    });
  }

  if (anoSelectDashboard) {
    anoSelectDashboard.addEventListener('change', () => {
      updateFilters(anoSelectDashboard.value, regiaoSelectDashboard.value);
    });
  }

  if (regiaoSelectDashboard) {
    regiaoSelectDashboard.addEventListener('change', () => {
      updateFilters(anoSelectDashboard.value, regiaoSelectDashboard.value);
    });
  }

  if (resetButtonDashboard) {
    resetButtonDashboard.addEventListener('click', () => {
      updateFilters("2024", "");
    });
  }
}

function applyFilters() {
  console.log("Aplicando filtros:", currentFilters);

  document.body.style.cursor = 'wait';

  fetch(`api.php?action=dashboard_data&ano=${currentFilters.ano}&regiao=${encodeURIComponent(currentFilters.regiao)}`)
    .then(response => {
      if (!response.ok) {
        throw new Error(`Erro na requisição: ${response.status} ${response.statusText}`);
      }
      return response.json();
    })
    .then(result => {
      if (result.success) {
        console.log("Dados recebidos com sucesso:", result.data);
        dashboardData = result.data;

        updateKPIs();

        Object.values(charts).forEach(chart => {
          if (chart && typeof chart.destroy === 'function') {
            chart.destroy();
          }
        });

        initializeCharts();
        initializeKPIPage();
      } else {
        console.error('Erro ao buscar dados:', result.message);
        alert(`Erro ao buscar dados: ${result.message}`);
      }
    })
    .catch(error => {
      console.error('Erro na requisição:', error);
      alert(`Erro na requisição: ${error.message}`);
    })
    .finally(() => {
      document.body.style.cursor = 'default';
    });
}

function updateCharts() {
  console.log("Atualizando gráficos...");

  if (charts.obitosPorRegiao) {
    charts.obitosPorRegiao.data.labels = dashboardData.graficos.dadosPorRegiao.map(item => item.regiao);
    charts.obitosPorRegiao.data.datasets[0].data = dashboardData.graficos.dadosPorRegiao.map(item => item.total_obitos);
    charts.obitosPorRegiao.update();
  }

  if (charts.obitosPorFaixaEtaria) {
    charts.obitosPorFaixaEtaria.data.labels = dashboardData.graficos.dadosPorFaixaEtaria.map(item => item.label);
    charts.obitosPorFaixaEtaria.data.datasets[0].data = dashboardData.graficos.dadosPorFaixaEtaria.map(item => item.total_obitos);
    charts.obitosPorFaixaEtaria.update();
  }

  if (charts.tendenciaAnos) {
    charts.tendenciaAnos.data.labels = dashboardData.graficos.tendenciaAnos.map(item => item.ano);
    charts.tendenciaAnos.data.datasets[0].data = dashboardData.graficos.tendenciaAnos.map(item => item.total_obitos);
    charts.tendenciaAnos.data.datasets[1].data = dashboardData.graficos.tendenciaAnos.map(item => item.taxa_media);
    charts.tendenciaAnos.update();
  }

  if (charts.custosPorFaixaEtaria) {
    charts.custosPorFaixaEtaria.data.labels = dashboardData.graficos.custosPorFaixaEtaria.map(item => item.label);
    charts.custosPorFaixaEtaria.data.datasets[0].data = dashboardData.graficos.custosPorFaixaEtaria.map(item => item.custo_total);
    charts.custosPorFaixaEtaria.update();
  }

  if (charts.tendenciaTaxa) {
    charts.tendenciaTaxa.data.labels = dashboardData.graficos.tendenciaAnos.map(item => item.ano);
    charts.tendenciaTaxa.data.datasets[0].data = dashboardData.graficos.tendenciaAnos.map(item => item.taxa_media);
    charts.tendenciaTaxa.update();
  }

  if (charts.taxaPorRegiao) {
    const taxaData = dashboardData.graficos.dadosPorRegiao.map(item => ({
      ...item,
      taxa_media: Number.parseFloat((Math.random() * 10).toFixed(2)),
    }));
    charts.taxaPorRegiao.data.labels = taxaData.map(item => item.regiao);
    charts.taxaPorRegiao.data.datasets[0].data = taxaData.map(item => item.taxa_media);
    charts.taxaPorRegiao.update();
  }

  if (charts.tendenciaObitos) {
    charts.tendenciaObitos.data.labels = dashboardData.graficos.tendenciaAnos.map(item => item.ano);
    charts.tendenciaObitos.data.datasets[0].data = dashboardData.graficos.tendenciaAnos.map(item => item.total_obitos);
    charts.tendenciaObitos.update();
  }

  if (charts.obitosPorFaixaEtariaAnalise) {
    charts.obitosPorFaixaEtariaAnalise.data.labels = dashboardData.graficos.dadosPorFaixaEtaria.map(item => item.label);
    charts.obitosPorFaixaEtariaAnalise.data.datasets[0].data = dashboardData.graficos.dadosPorFaixaEtaria.map(item => item.total_obitos);
    charts.obitosPorFaixaEtariaAnalise.update();
  }

  if (charts.tendenciaCustos) {
    const custosData = dashboardData.graficos.tendenciaAnos.map(item => ({
      ...item,
      custo_total: item.total_obitos * 10000,
    }));
    charts.tendenciaCustos.data.labels = custosData.map(item => item.ano);
    charts.tendenciaCustos.data.datasets[0].data = custosData.map(item => item.custo_total);
    charts.tendenciaCustos.update();
  }

  if (charts.custosPorFaixaEtariaAnalise) {
    charts.custosPorFaixaEtariaAnalise.data.labels = dashboardData.graficos.custosPorFaixaEtaria.map(item => item.label);
    charts.custosPorFaixaEtariaAnalise.data.datasets[0].data = dashboardData.graficos.custosPorFaixaEtaria.map(item => item.custo_total);
    charts.custosPorFaixaEtariaAnalise.update();
  }

  console.log("Gráficos atualizados com sucesso!");
}

// Inicialize o aplicativo
document.addEventListener('DOMContentLoaded', () => {
  console.log("Inicializando aplicação...");

  setupNavigation();
  setupTabs();
  setupFilters();
  applyFilters();
});