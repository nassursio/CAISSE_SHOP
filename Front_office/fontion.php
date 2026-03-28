<?php

/**
 * Establishes a secure connection to the database using PDO.
 */
function db_connect() {
    $host = '127.0.0.1';
    $db   = 'caisse_shop';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

/**
 * Securely registers a new user by hashing their password.
 */
function register_user($nom, $prenom, $email, $password) {
    $pdo = db_connect();
    
    // Hash the password using the current standard algorithm (BCRYPT)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO utilisateurs (Nom, Prenom, Email, Motdepasse) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$nom, $prenom, $email, $hashedPassword]);
}

/**
 * Verifies user credentials and starts a session.
 */
function login_user($email, $password) {
    $pdo = db_connect();
    
    $sql = "SELECT * FROM utilisateurs WHERE Email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify if user exists and the password matches the stored hash
    if ($user && password_verify($password, $user['Motdepasse'])) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user['Id'];
        $_SESSION['user_nom'] = $user['Nom'];
        return true;
    }
    
    return false;
}
