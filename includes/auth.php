<?php
session_start();
require_once 'db.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $query = "SELECT * FROM users WHERE id = ?";
    return getRow($query, [$_SESSION['user_id']]);
}

// Check if user has specific role
function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
}

// Redirect if user doesn't have required role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: ../index.php');
        exit;
    }
}

// Login function
function login($email, $password) {
    $query = "SELECT * FROM users WHERE email = ?";
    $user = getRow($query, [$email]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        return true;
    }
    return false;
}

// Logout function
function logout() {
    session_destroy();
    header('Location: ../login.php');
    exit;
}

// Register function
function register($name, $email, $phone, $password, $role, $school_id = null) {
    try {
        // Check if email already exists
        $query = "SELECT id FROM users WHERE email = ?";
        $existing = getRow($query, [$email]);
        
        if ($existing) {
            throw new Exception("Email already exists");
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $query = "INSERT INTO users (name, email, phone, password, role, school_id) VALUES (?, ?, ?, ?, ?, ?)";
        executeQuery($query, [$name, $email, $phone, $hashedPassword, $role, $school_id]);
        
        return true;
    } catch (Exception $e) {
        throw $e;
    }
}

// Add notification
function addNotification($userId, $message, $type = 'info') {
    $query = "INSERT INTO notifications (to_user_id, message, type) VALUES (?, ?, ?)";
    executeQuery($query, [$userId, $message, $type]);
}

// Get unread notifications count
function getUnreadNotificationsCount($userId) {
    $query = "SELECT COUNT(*) as count FROM notifications WHERE to_user_id = ? AND read_status = 0";
    $result = getRow($query, [$userId]);
    return $result['count'];
}

// Mark notification as read
function markNotificationAsRead($notificationId) {
    $query = "UPDATE notifications SET read_status = 1 WHERE id = ?";
    executeQuery($query, [$notificationId]);
}
?>