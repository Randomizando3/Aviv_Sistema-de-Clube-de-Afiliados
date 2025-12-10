<?php
final class Auth {
  public static function start(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      // Evita warning se alguém chamar tarde demais
      if (!headers_sent()) {
        session_start();
      }
    }
  }

  public static function user(): ?array {
    self::start();
    return $_SESSION['user'] ?? null;
  }

  public static function login(array $u): void {
    self::start();
    $_SESSION['user'] = $u;
  }

  public static function logout(): void {
    self::start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
      $p = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
  }

  public static function requireRole(array $roles): void {
    $u = self::user();
    if (!$u || !in_array($u['role'], $roles, true)) {
      http_response_code(403);
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(['error' => 'forbidden'], JSON_UNESCAPED_UNICODE);
      exit;
    }
  }

  public static function csrfToken(): string {
    self::start();
    if (empty($_SESSION['csrf'])) {
      $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
  }

  public static function checkCsrf(): void {
    self::start();
    $t = $_SERVER['HTTP_X_CSRF'] ?? ($_POST['_csrf'] ?? '');
    if (!hash_equals($_SESSION['csrf'] ?? '', $t)) {
      http_response_code(419);
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(['error' => 'csrf'], JSON_UNESCAPED_UNICODE);
      exit;
    }
  }
}
