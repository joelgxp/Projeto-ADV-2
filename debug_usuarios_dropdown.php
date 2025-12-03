<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'application/vendor/autoload.php';

$envFile = __DIR__ . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/application');
    $dotenv->load();
}

require_once __DIR__ . '/application/config/database.php';
$db_config = $db['default'];

echo "<h1>Debug - Usuários no Dropdown</h1>";
echo "<hr>";

$conn = @mysqli_connect(
    $db_config['hostname'],
    $db_config['username'],
    $db_config['password'],
    $db_config['database']
);

if (!$conn) {
    die("<p>❌ Erro de conexão: " . mysqli_connect_error() . "</p>");
}

echo "<h3>1. Usuários no Banco de Dados:</h3>";
$query = mysqli_query($conn, "SELECT idUsuarios, nome, nivel, email FROM usuarios");
if ($query) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Nível</th><th>Email</th></tr>";
    while ($row = mysqli_fetch_assoc($query)) {
        echo "<tr>";
        echo "<td>{$row['idUsuarios']}</td>";
        echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nivel']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ Erro na query: " . mysqli_error($conn) . "</p>";
}

echo "<hr>";
echo "<h3>2. Teste do Método Sistema_model::get():</h3>";

// Simular o método get do Sistema_model
class Mock_DB {
    private $conn;
    public $last_query = '';
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function select($fields) { return $this; }
    public function from($table) { return $this; }
    public function limit($perpage, $start) {
        $this->limit_perpage = $perpage;
        $this->limit_start = $start;
        return $this;
    }
    public function where($where) { return $this; }
    public function get($table = null) {
        $sql = "SELECT * FROM usuarios";
        if ($this->limit_perpage > 0) {
            $sql .= " LIMIT " . intval($this->limit_start) . ", " . intval($this->limit_perpage);
        } elseif ($this->limit_perpage == 0) {
            $sql .= " LIMIT 0"; // Isso limita a 0 registros!
        }
        $this->last_query = $sql;
        return mysqli_query($this->conn, $sql);
    }
}

$mock_db = new Mock_DB($conn);
$result = $mock_db->select('*')->from('usuarios')->limit(0, 0)->get();
echo "<p><strong>Query executada:</strong> <code>" . $mock_db->last_query . "</code></p>";
echo "<p><strong>Registros retornados:</strong> " . ($result ? mysqli_num_rows($result) : 0) . "</p>";

if ($result && mysqli_num_rows($result) > 0) {
    echo "<p>✅ Registros encontrados!</p>";
} else {
    echo "<p>❌ NENHUM registro retornado quando perpage=0!</p>";
    echo "<p><strong>Problema identificado:</strong> Quando <code>perpage = 0</code>, o método <code>limit(0, 0)</code> limita a 0 registros!</p>";
}

echo "<hr>";
echo "<h3>3. Solução:</h3>";
echo "<p>O método <code>Sistema_model::get()</code> precisa ser corrigido para não aplicar LIMIT quando <code>perpage = 0</code>.</p>";

mysqli_close($conn);
?>

