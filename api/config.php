<?php
/**
 * ============================================================================
 * PATRÓN SINGLETON EN PHP: DatabaseConnection
 * Conexión única a la base de datos 'ingles' en MySQL (XAMPP)
 * Cumple con SOLID (SRP: Gestión única de la conexión de persistencia)
 * ============================================================================
 */

class DatabaseConnection {
    private static ?DatabaseConnection $instance = null;
    private ?PDO $connection = null;

    private string $host;
    private string $db;
    private string $user;
    private string $pass;
    private string $port;
    private string $charset = 'utf8mb4';

    private function __construct() {
        $this->host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
        $this->db   = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: ($_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: 'ingles');
        $this->user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: ($_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'root');
        $this->pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: ($_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '');
        $this->port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';

        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];
        try {
            $this->connection = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // Log de error interno
            error_log("Error de conexión a la base de datos 'ingles': " . $e->getMessage());
            $this->connection = null;
        }
    }

    public static function getInstance(): DatabaseConnection {
        if (self::$instance === null) {
            self::$instance = new DatabaseConnection();
        }
        return self::$instance;
    }

    public function getConnection(): ?PDO {
        return $this->connection;
    }

    // Prevenir clonación y deserialización
    private function __clone() {}
    public function __wakeup() {
        throw new \Exception("No se puede deserializar una instancia de DatabaseConnection (Singleton).");
    }
}
