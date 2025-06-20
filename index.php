<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard de Saúde</title>
  <link rel="stylesheet" href="styles.css">
  <script src="https://cdn.jsdelivr.net/npm/recharts/umd/Recharts.min.js"></script>
</head>
<body>
  <nav class="navbar">
    <div class="container">
      <div class="navbar-brand">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-pie-chart"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
        <span class="navbar-title">Dashboard de Saúde</span>
      </div>

      <div class="navbar-menu">
        <a href="#" class="navbar-item active" data-page="dashboard">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
          Dashboard
        </a>
        <a href="#" class="navbar-item" data-page="analise">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><rect width="18" height="18" x="3" y="3" rx="2"></rect><line x1="3" x2="21" y1="9" y2="9"></line><line x1="3" x2="21" y1="15" y2="15"></line><line x1="9" x2="9" y1="3" y2="21"></line><line x1="15" x2="15" y1="3" y2="21"></line></svg>
          Análise Detalhada
        </a>
        <a href="#" class="navbar-item" data-page="kpis">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path></svg>
          KPIs
        </a>
      </div>
    </div>
  </nav>

  <main>
    <div class="container">
      <!-- Dashboard Page -->
      <div class="page" id="dashboard-page">
        <div class="dashboard-header">
          <h1>Dashboard de Saúde Câncer de Mama</h1>
          <p class="description">Monitoramento de indicadores de saúde, taxas de mortalidade, óbitos e custos com tratamentos</p>
        </div>

        <div class="alert info-alert">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
          <div>
            <h4>Período dos Dados</h4>
            <p>Os dados apresentados neste dashboard correspondem ao período de 2020 a 2024.</p>
          </div>
        </div>

        <!-- Filtros adicionados -->
        <div class="filters">
          <div class="filter-group">
            <label for="ano-select-dashboard">Ano</label>
            <select id="ano-select-dashboard" class="filter-select">
              <option value="2024">2024</option>
              <option value="2023">2023</option>
              <option value="2022">2022</option>
              <option value="2021">2021</option>
              <option value="2020">2020</option>
            </select>
          </div>
          <div class="filter-group">
            <label for="regiao-select-dashboard">Região</label>
            <select id="regiao-select-dashboard" class="filter-select">
              <option value="">Todas as Regiões</option>
              <option value="Região Norte">Região Norte</option>
              <option value="Região Nordeste">Região Nordeste</option>
              <option value="Região Sudeste">Região Sudeste</option>
              <option value="Região Sul">Região Sul</option>
              <option value="Região Centro-Oeste">Região Centro-Oeste</option>
            </select>
          </div>
          <button id="reset-filters-dashboard" class="btn-reset">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"></path><path d="M16 21h5v-5"></path></svg>
          </button>
        </div>

        <div class="kpi-cards">
          <div class="card kpi-card">
            <div class="card-header">
              <h3>Total de Óbitos</h3>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <div class="card-content">
              <div class="kpi-value" id="total-obitos">0</div>
              <p class="kpi-description">Número total de óbitos registrados</p>
            </div>
          </div>

          <div class="card kpi-card">
            <div class="card-header">
              <h3>Taxa de Mortalidade</h3>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg>
            </div>
            <div class="card-content">
              <div class="kpi-value" id="taxa-mortalidade">0%</div>
              <p class="kpi-description">Taxa média de mortalidade</p>
              <div class="kpi-trend">
                <span class="trend-value" id="taxa-variacao">0%</span>
                <span class="trend-label">vs. período anterior</span>
              </div>
            </div>
          </div>

          <div class="card kpi-card">
            <div class="card-header">
              <h3>Custo Total</h3>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><line x1="12" x2="12" y1="2" y2="22"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
            <div class="card-content">
              <div class="kpi-value" id="custo-total">R$ 0,00</div>
              <p class="kpi-description">Custo total com tratamentos</p>
              <div class="kpi-trend">
                <span class="trend-value" id="custo-variacao">0%</span>
                <span class="trend-label">vs. período anterior</span>
              </div>
            </div>
          </div>

          <div class="card kpi-card">
            <div class="card-header">
              <h3>Custo Médio por Paciente</h3>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
            </div>
            <div class="card-content">
              <div class="kpi-value" id="custo-medio">R$ 0,00</div>
              <p class="kpi-description">Custo médio por paciente</p>
            </div>
          </div>
        </div>

        <div class="charts-grid">
          <div class="card chart-card">
            <div class="card-header">
              <h3>Óbitos por Região</h3>
              <p class="description">Distribuição de óbitos por região</p>
            </div>
            <div class="card-content">
              <div class="chart-container" id="chart-obitos-regiao"></div>
            </div>
          </div>

          <div class="card chart-card">
            <div class="card-header">
              <h3>Óbitos por Faixa Etária</h3>
              <p class="description">Distribuição de óbitos por faixa etária</p>
            </div>
            <div class="card-content">
              <div class="chart-container" id="chart-obitos-faixa-etaria"></div>
            </div>
          </div>

          <div class="card chart-card">
            <div class="card-header">
              <h3>Tendência de Óbitos e Taxa de Mortalidade</h3>
              <p class="description">Evolução ao longo dos anos</p>
            </div>
            <div class="card-content">
              <div class="chart-container" id="chart-tendencia-anos"></div>
            </div>
          </div>

          <div class="card chart-card">
            <div class="card-header">
              <h3>Custos por Faixa Etária</h3>
              <p class="description">Distribuição de custos por faixa etária</p>
            </div>
            <div class="card-content">
              <div class="chart-container" id="chart-custos-faixa-etaria"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Análise Page -->
      <div class="page hidden" id="analise-page">
        <div class="dashboard-header">
          <h1>Análise Detalhada</h1>
          <p class="description">Análise detalhada de indicadores de saúde por região, faixa etária e período</p>
        </div>

        <div class="filters">
          <div class="filter-group">
            <label for="ano-select">Ano</label>
            <select id="ano-select" class="filter-select">
              <option value="2024">2024</option>
              <option value="2023">2023</option>
              <option value="2022">2022</option>
              <option value="2021">2021</option>
              <option value="2020">2020</option>
            </select>
          </div>
          <div class="filter-group">
            <label for="regiao-select">Região</label>
            <select id="regiao-select" class="filter-select">
              <option value="">Todas as Regiões</option>
              <option value="Região Norte">Região Norte</option>
              <option value="Região Nordeste">Região Nordeste</option>
              <option value="Região Sudeste">Região Sudeste</option>
              <option value="Região Sul">Região Sul</option>
              <option value="Região Centro-Oeste">Região Centro-Oeste</option>
            </select>
          </div>
          <button id="reset-filters" class="btn-reset">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"></path><path d="M16 21h5v-5"></path></svg>
          </button>
        </div>

        <div class="alert info-alert">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
          <div>
            <h4>Período dos Dados</h4>
            <p>Os dados apresentados nesta análise correspondem ao período de 2020 a 2024. <span id="filtro-atual"></span></p>
          </div>
        </div>

        <div class="tabs">
          <div class="tabs-list">
            <button class="tab-trigger active" data-tab="mortalidade">Taxa de Mortalidade</button>
            <button class="tab-trigger" data-tab="obitos">Óbitos</button>
            <button class="tab-trigger" data-tab="custos">Custos</button>
          </div>

          <div class="tab-content active" id="tab-mortalidade">
            <div class="card">
              <div class="card-header">
                <h3>Análise de Taxa de Mortalidade</h3>
                <p class="description">Análise detalhada da taxa de mortalidade por faixa etária e região</p>
              </div>
              <div class="card-content">
                <div class="charts-grid">
                  <div class="chart-container" id="chart-tendencia-taxa"></div>
                  <div class="chart-container" id="chart-taxa-regiao"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="tab-content" id="tab-obitos">
            <div class="card">
              <div class="card-header">
                <h3>Análise de Óbitos</h3>
                <p class="description">Análise detalhada de óbitos por faixa etária e região</p>
              </div>
              <div class="card-content">
                <div class="charts-grid">
                  <div class="chart-container" id="chart-tendencia-obitos"></div>
                  <div class="chart-container" id="chart-obitos-faixa-etaria-analise"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="tab-content" id="tab-custos">
            <div class="card">
              <div class="card-header">
                <h3>Análise de Custos</h3>
                <p class="description">Análise detalhada de custos por faixa etária e região</p>
              </div>
              <div class="card-content">
                <div class="charts-grid">
                  <div class="chart-container" id="chart-tendencia-custos"></div>
                  <div class="chart-container" id="chart-custos-faixa-etaria-analise"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- KPIs Page -->
      <div class="page hidden" id="kpis-page">
        <div class="dashboard-header">
          <h1>Indicadores de Desempenho (KPIs)</h1>
          <p class="description">Monitoramento de KPIs essenciais para avaliação de ações preventivas e efetividade do tratamento</p>
        </div>

        <div class="filters">
          <div class="filter-group">
            <label for="ano-select-kpi">Ano</label>
            <select id="ano-select-kpi" class="filter-select">
              <option value="2024">2024</option>
              <option value="2023">2023</option>
              <option value="2022">2022</option>
              <option value="2021">2021</option>
              <option value="2020">2020</option>
            </select>
          </div>
          <div class="filter-group">
            <label for="regiao-select-kpi">Região</label>
            <select id="regiao-select-kpi" class="filter-select">
              <option value="">Todas as Regiões</option>
              <option value="Região Norte">Região Norte</option>
              <option value="Região Nordeste">Região Nordeste</option>
              <option value="Região Sudeste">Região Sudeste</option>
              <option value="Região Sul">Região Sul</option>
              <option value="Região Centro-Oeste">Região Centro-Oeste</option>
            </select>
          </div>
          <button id="reset-filters-kpi" class="btn-reset">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"></path><path d="M16 21h5v-5"></path></svg>
          </button>
        </div>

        <div class="alert info-alert">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
          <div>
            <h4>Período dos Dados</h4>
            <p>Os dados apresentados nesta análise correspondem ao período de 2020 a 2024. <span id="filtro-atual-kpi"></span></p>
          </div>
        </div>

        <div class="kpi-cards">
          <div class="card kpi-card">
            <div class="card-header">
              <h3>Taxa de Diagnóstico Precoce</h3>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-green"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            <div class="card-content">
              <div class="kpi-value" id="taxa-diagnostico">0%</div>
              <p class="kpi-description">Percentual de casos diagnosticados em estágio inicial</p>
              <div class="progress-container">
                <div class="progress-bar" id="progress-diagnostico"></div>
              </div>
              <p class="meta-info">Meta: 80% • <span id="meta-diagnostico">Não atingida</span></p>
            </div>
          </div>

          <div class="card kpi-card">
            <div class="card-header">
              <h3>Tempo Médio para Início do Tratamento</h3>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-amber"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <div class="card-content">
              <div class="kpi-value" id="tempo-tratamento">0 dias</div>
              <p class="kpi-description">Tempo médio entre diagnóstico e início do tratamento</p>
              <div class="progress-container">
                <div class="progress-bar" id="progress-tempo"></div>
              </div>
              <p class="meta-info">Meta: 15 dias • <span id="meta-tempo">Não atingida</span></p>
            </div>
          </div>

          <div class="card kpi-card">
            <div class="card-header">
              <h3>Efetividade do Tratamento</h3>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-blue"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
            </div>
            <div class="card-content">
              <div class="kpi-value" id="efetividade">0%</div>
              <p class="kpi-description">Taxa de sucesso dos tratamentos realizados</p>
              <div class="progress-container">
                <div class="progress-bar" id="progress-efetividade"></div>
              </div>
              <p class="meta-info">Meta: 85% • <span id="meta-efetividade">Não atingida</span></p>
            </div>
          </div>

          <div class="card kpi-card">
            <div class="card-header">
              <h3>Taxa de Reincidência</h3>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-red"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" y2="13" x2="12"></line><line x1="12" y1="17" y2="17.01" x2="12"></line></svg>
            </div>
            <div class="card-content">
              <div class="kpi-value" id="reincidencia">0%</div>
              <p class="kpi-description">Percentual de pacientes com reincidência da doença</p>
              <div class="progress-container">
                <div class="progress-bar" id="progress-reincidencia"></div>
              </div>
              <p class="meta-info">Meta: &lt;10% • <span id="meta-reincidencia">Não atingida</span></p>
            </div>
          </div>
        </div>

        <div class="charts-grid">
          <div class="card chart-card">
            <div class="card-header">
              <h3>Análise de Efetividade por Região</h3>
              <p class="description">Comparação da efetividade do tratamento entre diferentes regiões</p>
            </div>
            <div class="card-content">
              <div id="efetividade-regiao-container"></div>
            </div>
          </div>

          <div class="card chart-card">
            <div class="card-header">
              <h3>Tempo Médio de Tratamento por Faixa Etária</h3>
              <p class="description">Duração média do tratamento por faixa etária em dias</p>
            </div>
            <div class="card-content">
              <div id="tempo-faixa-etaria-container"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="script.js"></script>
</body>
</html>