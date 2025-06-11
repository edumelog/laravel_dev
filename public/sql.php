<?php

// Configurações do banco de dados
$config = [
    'host' => '192.168.4.73',
    'port' => '1433',
    'database' => 'dcmdigital',
    'username' => 'dti-user',
    'password' => '12345',
    'encrypt' => true,
    'trust_server_certificate' => true
];

// Função para testar conexão
function testConnection($driver, $dsn, $username, $password) {
    echo "\n=== Testando conexão com $driver ===\n";
    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        echo "[SUCESSO] Conexão estabelecida com $driver\n";
        echo "Versão do servidor: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
        return true;
    } catch (PDOException $e) {
        echo "[ERRO] Falha na conexão com $driver: " . $e->getMessage() . "\n";
        return false;
    }
}

// Função para testar sqlsrv nativo
function testSqlsrvNative($config) {
    echo "\n=== Testando conexão com sqlsrv nativo ===\n";
    try {
        $connectionInfo = [
            "Database" => $config['database'],
            "UID" => $config['username'],
            "PWD" => $config['password'],
            "Encrypt" => $config['encrypt'],
            "TrustServerCertificate" => $config['trust_server_certificate']
        ];
        
        $conn = sqlsrv_connect($config['host'], $connectionInfo);
        
        if ($conn) {
            echo "[SUCESSO] Conexão estabelecida com sqlsrv nativo\n";
            $serverInfo = sqlsrv_server_info($conn);
            echo "Versão do servidor: " . $serverInfo['SQLServerVersion'] . "\n";
            sqlsrv_close($conn);
            return true;
        } else {
            echo "[ERRO] Falha na conexão com sqlsrv nativo: " . print_r(sqlsrv_errors(), true) . "\n";
            return false;
        }
    } catch (Exception $e) {
        echo "[ERRO] Falha na conexão com sqlsrv nativo: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "\n=== INICIANDO TESTES DE CONEXÃO SQL SERVER ===\n";

// Teste SQLSRV nativo
if (extension_loaded('sqlsrv')) {
    testSqlsrvNative($config);
} else {
    echo "[AVISO] Extensão sqlsrv não está carregada\n";
}

// Teste ODBC 17
$dsn_odbc17 = "odbc:Driver={ODBC Driver 17 for SQL Server};Server={$config['host']},{$config['port']};Database={$config['database']};Encrypt={$config['encrypt']};TrustServerCertificate={$config['trust_server_certificate']}";
testConnection('ODBC 17', $dsn_odbc17, $config['username'], $config['password']);

// Teste ODBC 18
$dsn_odbc18 = "odbc:Driver={ODBC Driver 18 for SQL Server};Server={$config['host']},{$config['port']};Database={$config['database']};Encrypt={$config['encrypt']};TrustServerCertificate={$config['trust_server_certificate']}";
testConnection('ODBC 18', $dsn_odbc18, $config['username'], $config['password']);

// Teste FreeTDS
$dsn_freetds = "dblib:host={$config['host']}:{$config['port']};dbname={$config['database']}";
testConnection('FreeTDS', $dsn_freetds, $config['username'], $config['password']);

// Informações do ambiente
echo "\n=== INFORMAÇÕES DO AMBIENTE PHP ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Extensões carregadas:\n";
echo "- sqlsrv: " . (extension_loaded('sqlsrv') ? 'Sim' : 'Não') . "\n";
echo "- pdo_sqlsrv: " . (extension_loaded('pdo_sqlsrv') ? 'Sim' : 'Não') . "\n";
echo "- pdo_odbc: " . (extension_loaded('pdo_odbc') ? 'Sim' : 'Não') . "\n";
echo "- pdo_dblib: " . (extension_loaded('pdo_dblib') ? 'Sim' : 'Não') . "\n";