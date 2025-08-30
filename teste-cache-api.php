<?php

echo "🧪 TESTANDO CACHE API - ID ÚNICO A CADA 60 SEGUNDOS\n";
echo str_repeat("=", 60) . "\n\n";

// URL base da API
$baseUrl = 'http://localhost:8000/api';

function makeRequest($url, $method = 'GET', $data = null)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'http_code' => $httpCode,
        'response' => json_decode($response, true) ?? ['raw' => $response]
    ];
}

function testCacheEndpoint()
{
    global $baseUrl;

    echo "📡 Fazendo requisição para /test-cache...\n";
    $result = makeRequest("$baseUrl/test-cache", 'POST');

    if ($result['http_code'] === 200) {
        $data = $result['response'];
        echo "✅ Sucesso! Status: {$result['http_code']}\n";
        echo "📊 Status do cache: {$data['result']['status']}\n";
        echo "💬 Mensagem: {$data['result']['message']}\n";

        if (isset($data['result']['data'])) {
            $cacheData = $data['result']['data'];
            echo "🆔 ID único: {$cacheData['unique_id']}\n";
            echo "📅 Criado em: {$cacheData['created_at']}\n";
            echo "⏰ Expira em: {$cacheData['expires_at']}\n";
            echo "⏱️  Tempo restante: {$data['result']['cache_remaining_seconds']} segundos\n";
        }
    } else {
        echo "❌ Erro! Status: {$result['http_code']}\n";
        echo "📄 Resposta: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
    }

    return $result;
}

function getCacheStats()
{
    global $baseUrl;

    echo "\n📊 Obtendo estatísticas do cache...\n";
    $result = makeRequest("$baseUrl/cache-stats", 'GET');

    if ($result['http_code'] === 200) {
        $data = $result['response'];
        echo "✅ Estatísticas obtidas!\n";

        if ($data['stats']['cache_exists']) {
            $stats = $data['stats'];
            echo "🆔 ID no cache: {$stats['data']['unique_id']}\n";
            echo "⏰ Tempo decorrido: {$stats['elapsed_seconds']} segundos\n";
            echo "⏱️  Tempo restante: {$stats['remaining_seconds']} segundos\n";
            echo "📈 Progresso: {$stats['progress_percentage']}%\n";
        } else {
            echo "📭 Cache vazio: {$data['stats']['message']}\n";
        }
    } else {
        echo "❌ Erro ao obter estatísticas! Status: {$result['http_code']}\n";
    }

    return $result;
}

function clearCache()
{
    global $baseUrl;

    echo "\n🧹 Limpando cache...\n";
    $result = makeRequest("$baseUrl/clear-cache", 'DELETE');

    if ($result['http_code'] === 200) {
        $data = $result['response'];
        echo "✅ {$data['message']}\n";
    } else {
        echo "❌ Erro ao limpar cache! Status: {$result['http_code']}\n";
    }

    return $result;
}

// Função para aguardar com feedback
function waitWithProgress($seconds, $message)
{
    echo "\n⏳ $message\n";
    echo "Aguardando: ";
    for ($i = $seconds; $i > 0; $i--) {
        echo "$i ";
        sleep(1);
    }
    echo "✅\n";
}

echo "🚀 INICIANDO TESTES\n";
echo "===================\n\n";

echo "🔍 TESTE 1: Primeira chamada (deve criar novo ID)\n";
echo "------------------------------------------------\n";
testCacheEndpoint();

echo "\n🔍 TESTE 2: Segunda chamada imediata (deve retornar mesmo ID)\n";
echo "------------------------------------------------------------\n";
testCacheEndpoint();

echo "\n🔍 TESTE 3: Verificar estatísticas\n";
echo "-----------------------------------\n";
getCacheStats();

echo "\n🔍 TESTE 4: Aguardar alguns segundos e verificar novamente\n";
echo "---------------------------------------------------------\n";
waitWithProgress(5, "Aguardando 5 segundos...");
getCacheStats();

echo "\n🔍 TESTE 5: Terceira chamada (mesmo ID, tempo menor)\n";
echo "----------------------------------------------------\n";
testCacheEndpoint();

echo "\n🔍 TESTE 6: Limpar cache manualmente\n";
echo "------------------------------------\n";
clearCache();

echo "\n🔍 TESTE 7: Verificar cache após limpeza\n";
echo "----------------------------------------\n";
getCacheStats();

echo "\n🔍 TESTE 8: Nova chamada após limpeza (deve criar novo ID)\n";
echo "----------------------------------------------------------\n";
testCacheEndpoint();

echo "\n🎯 DEMONSTRAÇÃO: RENOVAÇÃO AUTOMÁTICA APÓS 60 SEGUNDOS\n";
echo "======================================================\n";
echo "⚠️  Para testar a renovação automática, você precisa:\n";
echo "1. Fazer uma chamada para /test-cache\n";
echo "2. Aguardar 60 segundos\n";
echo "3. Fazer outra chamada - um novo ID será gerado\n\n";

echo "📋 ROTAS DISPONÍVEIS:\n";
echo "====================\n";
echo "POST   $baseUrl/test-cache     - Testa cache (cria ou retorna ID)\n";
echo "GET    $baseUrl/cache-stats    - Mostra estatísticas do cache\n";
echo "DELETE $baseUrl/clear-cache    - Limpa o cache manualmente\n\n";

echo "🧪 COMANDOS CURL PARA TESTE MANUAL:\n";
echo "===================================\n";
echo "# Teste do cache\n";
echo "curl -X POST $baseUrl/test-cache\n\n";
echo "# Ver estatísticas\n";
echo "curl -X GET $baseUrl/cache-stats\n\n";
echo "# Limpar cache\n";
echo "curl -X DELETE $baseUrl/clear-cache\n\n";

echo "📝 FUNCIONAMENTO:\n";
echo "================\n";
echo "• O cache armazena um ID único com timestamp\n";
echo "• Se não existe cache OU passou 60s → cria novo ID\n";
echo "• Se existe cache E não passou 60s → retorna mesmo ID\n";
echo "• Mostra tempo restante até expiração\n";
echo "• Dados incluem: unique_id, created_at, expires_at\n\n";

echo "✅ DEMONSTRAÇÃO CONCLUÍDA!\n";
