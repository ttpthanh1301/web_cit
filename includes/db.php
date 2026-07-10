<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dbHost = getenv('DB_HOST') ?: '127.0.0.1';
    $dbPort = getenv('DB_PORT') ?: '3306';
    $dbName = getenv('DB_NAME') ?: 'club_management';
    $dbUser = getenv('DB_USER') ?: 'root';
    $dbPass = getenv('DB_PASS') ?: 'Cit@2026';

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5,
    ];
    if (defined('Pdo\Mysql::ATTR_INIT_COMMAND')) {
        $options[Pdo\Mysql::ATTR_INIT_COMMAND] = "SET SESSION sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'";
    } elseif (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
        $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET SESSION sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'";
    }

    try {
        $pdo = new PDO(
            "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4;connect_timeout=5",
            $dbUser,
            $dbPass,
            $options
        );
    } catch (PDOException $exception) {
        error_log('Database connection failed: ' . $exception->getMessage());
        http_response_code(500);
        exit('Không thể kết nối cơ sở dữ liệu. Vui lòng kiểm tra cấu hình.');
    }

    return $pdo;
}

class LazyPDO
{
    private ?PDO $instance = null;

    private function getPDO(): PDO
    {
        if ($this->instance === null) {
            $this->instance = db();
        }
        return $this->instance;
    }

    public function __call(string $name, array $args): mixed
    {
        return call_user_func_array([$this->getPDO(), $name], $args);
    }

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        return $this->getPDO()->query($query, $fetchMode, ...$fetchModeArgs);
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return $this->getPDO()->prepare($query, $options);
    }

    public function exec(string $statement): int|false
    {
        return $this->getPDO()->exec($statement);
    }

    public function beginTransaction(): bool
    {
        return $this->getPDO()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->getPDO()->commit();
    }

    public function rollBack(): bool
    {
        return $this->getPDO()->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->getPDO()->inTransaction();
    }

    public function lastInsertId(?string $name = null): string|false
    {
        return $this->getPDO()->lastInsertId($name);
    }
}

$pdo = new LazyPDO();
