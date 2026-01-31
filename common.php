<?php
// Common.php - Database connection helper & common function
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct($config) {
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $this->pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance($config = null) {
        if (self::$instance === null) {
            if ($config === null) {
                // Load config automatically
                $config = require __DIR__ . '/config.php';
                $config = $config['database'];
            }
            self::$instance = new self($config);
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    // Helper methods for common operations
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        
        return $this->pdo->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge($data, $whereParams));
        
        return $stmt->rowCount();
    }
}

 function wkSign($to_sign, $api_secret_key) {
        
	$error = "";
	
	try {
		if (!($privateKeyResource = openssl_pkey_get_private($api_secret_key))) {
			throw new Exception("Could not load private key");
		}
		
		if (!openssl_sign($to_sign, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256)) {
			throw new Exception("Signature generation failed");
		}
		
	} catch(Exception $e) {
		$error = $e->getMessage();
	}
	
	if ($error) {
		return ["error" => $error];
	}
	
	return ["signature" => bin2hex($signature)];
}