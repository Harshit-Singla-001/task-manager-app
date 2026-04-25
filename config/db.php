<?php
class Database {
    private $host = "localhost";
    private $db_name = "online_crime_reporting";
    private $username = "root";
    private $password = "";
    private static $conn = null;

    public static function getConnection() {
        if (self::$conn === null) {
            try {
                self::$conn = new PDO(
                    "mysql:host=localhost;dbname=online_crime_reporting;charset=utf8mb4",
                    "root",
                    "",
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch(PDOException $e) {
                error_log("Connection Error: " . $e->getMessage());
                die("Database connection failed");
            }
        }
        return self::$conn;
    }
}

// Optional helper
function getConnection() {
    return Database::getConnection();
}
?>