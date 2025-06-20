<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Permitir acesso de qualquer origem
require_once 'db_connection.php';

// Função para sanitizar parâmetros de entrada
function sanitize($param) {
    return htmlspecialchars(strip_tags(trim($param)));
}

// Função para registrar logs
function logError($message, $data = null) {
    $logFile = 'api_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    
    if ($data !== null) {
        $logMessage .= " | Data: " . json_encode($data);
    }
    
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
}

// Obter parâmetros da requisição
$action = isset($_GET['action']) ? sanitize($_GET['action']) : '';
$ano = isset($_GET['ano']) ? sanitize($_GET['ano']) : '2024';
$regiao = isset($_GET['regiao']) ? sanitize($_GET['regiao']) : '';

// Resposta padrão
$response = [
    'success' => false,
    'data' => null,
    'message' => 'Ação não especificada'
];

try {
    // Processar a ação solicitada
    switch ($action) {
        case 'dashboard_data':
            // Obter dados para o dashboard principal
            $response = getDashboardData($conn, $ano, $regiao);
            break;
            
        case 'kpis':
            // Obter dados para os KPIs
            $response = getKPIsData($conn, $ano, $regiao);
            break;
            
        case 'obitos_regiao':
            // Obter dados de óbitos por região
            $response = getObitosPorRegiao($conn, $ano, $regiao);
            break;
            
        case 'obitos_faixa_etaria':
            // Obter dados de óbitos por faixa etária
            $response = getObitosPorFaixaEtaria($conn, $ano, $regiao);
            break;
            
        case 'tendencia_anos':
            // Obter dados de tendência ao longo dos anos
            $response = getTendenciaAnos($conn, $regiao);
            break;
            
        case 'custos_faixa_etaria':
            // Obter dados de custos por faixa etária
            $response = getCustosPorFaixaEtaria($conn, $ano, $regiao);
            break;
            
        default:
            $response['message'] = 'Ação inválida';
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'data' => null,
        'message' => 'Erro na API: ' . $e->getMessage()
    ];
    
    logError('Exceção na API: ' . $e->getMessage());
}

// Retornar resposta em formato JSON
echo json_encode($response);

// Funções para obter dados do banco de dados

function getDashboardData($conn, $ano, $regiao) {
    try {
        // Obter KPIs
        $kpis = getKPIsData($conn, $ano, $regiao);
        
        // Obter dados de óbitos por região
        $dadosPorRegiao = getObitosPorRegiao($conn, $ano, $regiao);
        
        // Obter dados de óbitos por faixa etária
        $dadosPorFaixaEtaria = getObitosPorFaixaEtaria($conn, $ano, $regiao);
        
        // Obter dados de tendência ao longo dos anos
        $tendenciaAnos = getTendenciaAnos($conn, $regiao);
        
        // Obter dados de custos por faixa etária
        $custosPorFaixaEtaria = getCustosPorFaixaEtaria($conn, $ano, $regiao);
        
        // Verificar se todos os dados foram obtidos com sucesso
        if (!$kpis['success'] || !$dadosPorRegiao['success'] || !$dadosPorFaixaEtaria['success'] || 
            !$tendenciaAnos['success'] || !$custosPorFaixaEtaria['success']) {
            
            $mensagensErro = [];
            if (!$kpis['success']) $mensagensErro[] = $kpis['message'];
            if (!$dadosPorRegiao['success']) $mensagensErro[] = $dadosPorRegiao['message'];
            if (!$dadosPorFaixaEtaria['success']) $mensagensErro[] = $dadosPorFaixaEtaria['message'];
            if (!$tendenciaAnos['success']) $mensagensErro[] = $tendenciaAnos['message'];
            if (!$custosPorFaixaEtaria['success']) $mensagensErro[] = $custosPorFaixaEtaria['message'];
            
            return [
                'success' => false,
                'data' => null,
                'message' => 'Erro ao obter dados: ' . implode('; ', $mensagensErro)
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'kpis' => $kpis['data'],
                'graficos' => [
                    'dadosPorRegiao' => $dadosPorRegiao['data'],
                    'dadosPorFaixaEtaria' => $dadosPorFaixaEtaria['data'],
                    'tendenciaAnos' => $tendenciaAnos['data'],
                    'custosPorFaixaEtaria' => $custosPorFaixaEtaria['data']
                ]
            ],
            'message' => 'Dados obtidos com sucesso'
        ];
    } catch (Exception $e) {
        logError('Erro em getDashboardData: ' . $e->getMessage());
        return [
            'success' => false,
            'data' => null,
            'message' => 'Erro ao obter dados: ' . $e->getMessage()
        ];
    }
}

function getKPIsData($conn, $ano, $regiao) {
    try {
        // Condição para filtrar por região, se especificada
        $regiaoCondition = $regiao ? "AND regiao = '$regiao'" : "";
        
        // Obter total de óbitos
        $sqlObitos = "SELECT SUM(total) as total_obitos FROM obitos_regiao_uf_faixa_etaria 
                      WHERE ano = $ano $regiaoCondition";
        $resultObitos = $conn->query($sqlObitos);
        
        if ($resultObitos === false) {
            throw new Exception("Erro na consulta de óbitos: " . $conn->error);
        }
        
        $totalObitos = $resultObitos->fetch_assoc()['total_obitos'] ?? 0;
        
        // Obter taxa de mortalidade média
        $sqlTaxa = "SELECT AVG(total) as taxa_media FROM taxa_mortalidade_regiao_uf_faixa_etaria 
                    WHERE ano = $ano $regiaoCondition";
        $resultTaxa = $conn->query($sqlTaxa);
        
        if ($resultTaxa === false) {
            throw new Exception("Erro na consulta de taxa de mortalidade: " . $conn->error);
        }
        
        $taxaMortalidade = $resultTaxa->fetch_assoc()['taxa_media'] ?? 0;
        
        // Obter custo total
        $sqlCusto = "SELECT SUM(total) as custo_total FROM custos_regiao_uf_faixa_etaria 
                     WHERE ano = $ano $regiaoCondition";
        $resultCusto = $conn->query($sqlCusto);
        
        if ($resultCusto === false) {
            throw new Exception("Erro na consulta de custos: " . $conn->error);
        }
        
        $custoTotal = $resultCusto->fetch_assoc()['custo_total'] ?? 0;
        
        // Calcular custo médio por paciente
        $custoMedio = $totalObitos > 0 ? $custoTotal / $totalObitos : 0;
        
        // Calcular variação da taxa de mortalidade em relação ao ano anterior
        $anoAnterior = $ano - 1;
        $sqlTaxaAnterior = "SELECT AVG(total) as taxa_media FROM taxa_mortalidade_regiao_uf_faixa_etaria 
                            WHERE ano = $anoAnterior $regiaoCondition";
        $resultTaxaAnterior = $conn->query($sqlTaxaAnterior);
        
        if ($resultTaxaAnterior === false) {
            throw new Exception("Erro na consulta de taxa de mortalidade anterior: " . $conn->error);
        }
        
        $taxaAnterior = $resultTaxaAnterior->fetch_assoc()['taxa_media'] ?? 0;
        $taxaVariacao = $taxaAnterior > 0 ? (($taxaMortalidade - $taxaAnterior) / $taxaAnterior) * 100 : 0;
        
        // Calcular variação do custo total em relação ao ano anterior
        $sqlCustoAnterior = "SELECT SUM(total) as custo_total FROM custos_regiao_uf_faixa_etaria 
                             WHERE ano = $anoAnterior $regiaoCondition";
        $resultCustoAnterior = $conn->query($sqlCustoAnterior);
        
        if ($resultCustoAnterior === false) {
            throw new Exception("Erro na consulta de custos anteriores: " . $conn->error);
        }
        
        $custoAnterior = $resultCustoAnterior->fetch_assoc()['custo_total'] ?? 0;
        $custoVariacao = $custoAnterior > 0 ? (($custoTotal - $custoAnterior) / $custoAnterior) * 100 : 0;
        
        return [
            'success' => true,
            'data' => [
                'totalObitos' => (int)$totalObitos,
                'taxaMortalidade' => (float)$taxaMortalidade,
                'custoTotal' => (float)$custoTotal,
                'custoMedio' => (float)$custoMedio,
                'taxaVariacao' => (float)$taxaVariacao,
                'custoVariacao' => (float)$custoVariacao
            ],
            'message' => 'KPIs obtidos com sucesso'
        ];
    } catch (Exception $e) {
        logError('Erro em getKPIsData: ' . $e->getMessage());
        return [
            'success' => false,
            'data' => null,
            'message' => 'Erro ao obter KPIs: ' . $e->getMessage()
        ];
    }
}

function getObitosPorRegiao($conn, $ano, $regiao) {
    try {
        // Condição para filtrar por região, se especificada
        $regiaoCondition = $regiao ? "AND regiao = '$regiao'" : "";
        
        // Consulta SQL para obter óbitos por região
        $sql = "SELECT regiao, SUM(total) as total_obitos 
                FROM obitos_regiao_uf_faixa_etaria 
                WHERE ano = $ano $regiaoCondition 
                GROUP BY regiao";
        
        $result = $conn->query($sql);
        
        if ($result === false) {
            throw new Exception("Erro na consulta de óbitos por região: " . $conn->error);
        }
        
        $dados = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $dados[] = [
                    'regiao' => $row['regiao'],
                    'total_obitos' => (int)$row['total_obitos']
                ];
            }
        }
        
        // Se não houver dados e uma região específica foi solicitada, retornar dados simulados
        if (empty($dados) && $regiao) {
            $dados[] = [
                'regiao' => $regiao,
                'total_obitos' => 0
            ];
        }
        
        return [
            'success' => true,
            'data' => $dados,
            'message' => 'Dados de óbitos por região obtidos com sucesso'
        ];
    } catch (Exception $e) {
        logError('Erro em getObitosPorRegiao: ' . $e->getMessage());
        return [
            'success' => false,
            'data' => [],
            'message' => 'Erro ao obter dados de óbitos por região: ' . $e->getMessage()
        ];
    }
}

function getObitosPorFaixaEtaria($conn, $ano, $regiao) {
    try {
        // Condição para filtrar por região, se especificada
        $regiaoCondition = $regiao ? "AND regiao = '$regiao'" : "";
        
        // Consulta SQL para obter óbitos por faixa etária
        $sql = "SELECT 
                SUM(de_20_a_29_anos) as faixa_20_29,
                SUM(de_30_a_39_anos) as faixa_30_39,
                SUM(de_40_a_49_anos) as faixa_40_49,
                SUM(de_50_a_59_anos) as faixa_50_59,
                SUM(de_60_a_69_anos) as faixa_60_69,
                SUM(de_70_a_79_anos) as faixa_70_79,
                SUM(de_80_anos_e_mais) as faixa_80_mais
                FROM obitos_regiao_uf_faixa_etaria 
                WHERE ano = $ano $regiaoCondition";
        
        $result = $conn->query($sql);
        
        if ($result === false) {
            throw new Exception("Erro na consulta de óbitos por faixa etária: " . $conn->error);
        }
        
        $row = $result->fetch_assoc();
        
        // Verificar se há dados
        if (!$row) {
            throw new Exception("Nenhum dado encontrado para óbitos por faixa etária");
        }
        
        $dados = [
            ['faixa_etaria' => 'de_20_a_29_anos', 'total_obitos' => (int)$row['faixa_20_29'], 'label' => '20 a 29 anos'],
            ['faixa_etaria' => 'de_30_a_39_anos', 'total_obitos' => (int)$row['faixa_30_39'], 'label' => '30 a 39 anos'],
            ['faixa_etaria' => 'de_40_a_49_anos', 'total_obitos' => (int)$row['faixa_40_49'], 'label' => '40 a 49 anos'],
            ['faixa_etaria' => 'de_50_a_59_anos', 'total_obitos' => (int)$row['faixa_50_59'], 'label' => '50 a 59 anos'],
            ['faixa_etaria' => 'de_60_a_69_anos', 'total_obitos' => (int)$row['faixa_60_69'], 'label' => '60 a 69 anos'],
            ['faixa_etaria' => 'de_70_a_79_anos', 'total_obitos' => (int)$row['faixa_70_79'], 'label' => '70 a 79 anos'],
            ['faixa_etaria' => 'de_80_anos_e_mais', 'total_obitos' => (int)$row['faixa_80_mais'], 'label' => '80 anos e mais']
        ];
        
        return [
            'success' => true,
            'data' => $dados,
            'message' => 'Dados de óbitos por faixa etária obtidos com sucesso'
        ];
    } catch (Exception $e) {
        logError('Erro em getObitosPorFaixaEtaria: ' . $e->getMessage());
        return [
            'success' => false,
            'data' => [],
            'message' => 'Erro ao obter dados de óbitos por faixa etária: ' . $e->getMessage()
        ];
    }
}

function getTendenciaAnos($conn, $regiao) {
    try {
        // Condição para filtrar por região, se especificada
        $regiaoCondition = $regiao ? "AND regiao = '$regiao'" : "";
        
        // Consulta SQL para obter tendência de óbitos ao longo dos anos
        $sqlObitos = "SELECT ano, SUM(total) as total_obitos 
                      FROM obitos_regiao_uf_faixa_etaria 
                      WHERE 1=1 $regiaoCondition 
                      GROUP BY ano 
                      ORDER BY ano";
        
        $resultObitos = $conn->query($sqlObitos);
        
        if ($resultObitos === false) {
            throw new Exception("Erro na consulta de tendência de óbitos: " . $conn->error);
        }
        
        // Consulta SQL para obter tendência de taxa de mortalidade ao longo dos anos
        $sqlTaxa = "SELECT ano, AVG(total) as taxa_media 
                    FROM taxa_mortalidade_regiao_uf_faixa_etaria 
                    WHERE 1=1 $regiaoCondition 
                    GROUP BY ano 
                    ORDER BY ano";
        
        $resultTaxa = $conn->query($sqlTaxa);
        
        if ($resultTaxa === false) {
            throw new Exception("Erro na consulta de tendência de taxa de mortalidade: " . $conn->error);
        }
        
        $dadosObitos = [];
        while ($row = $resultObitos->fetch_assoc()) {
            $dadosObitos[$row['ano']] = (int)$row['total_obitos'];
        }
        
        $dadosTaxa = [];
        while ($row = $resultTaxa->fetch_assoc()) {
            $dadosTaxa[$row['ano']] = (float)$row['taxa_media'];
        }
        
        $dados = [];
        foreach ($dadosObitos as $ano => $totalObitos) {
            $dados[] = [
                'ano' => (int)$ano,
                'total_obitos' => $totalObitos,
                'taxa_media' => $dadosTaxa[$ano] ?? 0
            ];
        }
        
        // Se não houver dados, retornar dados simulados
        if (empty($dados)) {
            $anos = [2020, 2021, 2022, 2023, 2024];
            foreach ($anos as $ano) {
                $dados[] = [
                    'ano' => $ano,
                    'total_obitos' => 0,
                    'taxa_media' => 0
                ];
            }
        }
        
        return [
            'success' => true,
            'data' => $dados,
            'message' => 'Dados de tendência ao longo dos anos obtidos com sucesso'
        ];
    } catch (Exception $e) {
        logError('Erro em getTendenciaAnos: ' . $e->getMessage());
        return [
            'success' => false,
            'data' => [],
            'message' => 'Erro ao obter dados de tendência ao longo dos anos: ' . $e->getMessage()
        ];
    }
}

function getCustosPorFaixaEtaria($conn, $ano, $regiao) {
    try {
        // Condição para filtrar por região, se especificada
        $regiaoCondition = $regiao ? "AND regiao = '$regiao'" : "";
        
        // Consulta SQL para obter custos por faixa etária
        $sql = "SELECT 
                SUM(de_20_a_29_anos) as faixa_20_29,
                SUM(de_30_a_39_anos) as faixa_30_39,
                SUM(de_40_a_49_anos) as faixa_40_49,
                SUM(de_50_a_59_anos) as faixa_50_59,
                SUM(de_60_a_69_anos) as faixa_60_69,
                SUM(de_70_a_79_anos) as faixa_70_79,
                SUM(de_80_anos_e_mais) as faixa_80_mais
                FROM custos_regiao_uf_faixa_etaria 
                WHERE ano = $ano $regiaoCondition";
        
        $result = $conn->query($sql);
        
        if ($result === false) {
            throw new Exception("Erro na consulta de custos por faixa etária: " . $conn->error);
        }
        
        $row = $result->fetch_assoc();
        
        // Verificar se há dados
        if (!$row) {
            throw new Exception("Nenhum dado encontrado para custos por faixa etária");
        }
        
        $dados = [
            ['faixa_etaria' => 'de_20_a_29_anos', 'custo_total' => (float)$row['faixa_20_29'], 'label' => '20 a 29 anos'],
            ['faixa_etaria' => 'de_30_a_39_anos', 'custo_total' => (float)$row['faixa_30_39'], 'label' => '30 a 39 anos'],
            ['faixa_etaria' => 'de_40_a_49_anos', 'custo_total' => (float)$row['faixa_40_49'], 'label' => '40 a 49 anos'],
            ['faixa_etaria' => 'de_50_a_59_anos', 'custo_total' => (float)$row['faixa_50_59'], 'label' => '50 a 59 anos'],
            ['faixa_etaria' => 'de_60_a_69_anos', 'custo_total' => (float)$row['faixa_60_69'], 'label' => '60 a 69 anos'],
            ['faixa_etaria' => 'de_70_a_79_anos', 'custo_total' => (float)$row['faixa_70_79'], 'label' => '70 a 79 anos'],
            ['faixa_etaria' => 'de_80_anos_e_mais', 'custo_total' => (float)$row['faixa_80_mais'], 'label' => '80 anos e mais']
        ];
        
        return [
            'success' => true,
            'data' => $dados,
            'message' => 'Dados de custos por faixa etária obtidos com sucesso'
        ];
    } catch (Exception $e) {
        logError('Erro em getCustosPorFaixaEtaria: ' . $e->getMessage());
        return [
            'success' => false,
            'data' => [],
            'message' => 'Erro ao obter dados de custos por faixa etária: ' . $e->getMessage()
        ];
    }
}
?>