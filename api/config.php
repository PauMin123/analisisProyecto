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

    private string $host = '127.0.0.1';
    private string $db   = 'ingles';
    private string $user = 'root';
    private string $pass = '';
    private string $charset = 'utf8mb4';

    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
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
