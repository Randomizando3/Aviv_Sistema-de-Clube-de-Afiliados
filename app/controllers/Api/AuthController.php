<?php
namespace Api;

use PDO;

require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Json.php';

// [AFFILIATE]
require_once __DIR__ . '/../../services/Affiliate.php';
use App\services\Affiliate;

class AuthController
{
  /** Retorna um set com os nomes das colunas existentes na tabela users */
  private function usersColumns(): array
  {
    try {
      $pdo = \DB::pdo();
      $cols = [];
      $st = $pdo->query('SHOW COLUMNS FROM users');
      foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $cols[strtolower($c['Field'])] = true;
      }
      return $cols;
    } catch (\Throwable $e) { return []; }
  }

  /** Encontra o primeiro nome de coluna existente entre as alternativas */
  private function pickExisting(array $columnsSet, array $candidates): ?string
  {
    foreach ($candidates as $c) {
      if (!is_string($c)) continue;
      $k = strtolower($c);
      if (isset($columnsSet[$k])) return $c; // usa o nome real do BD
    }
    return null;
  }

  public function register(): void
  {
    \Auth::start();

    $name     = trim($_POST['name'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $pass     = (string)($_POST['password'] ?? '');
    $role     = $_POST['role'] ?? 'member';
    $role     = in_array($role, ['member','partner','affiliate','admin'], true) ? $role : 'member';
    $cpfCnpj  = trim($_POST['cpf_cnpj'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $ref      = $_POST['ref'] ?? ($_GET['ref'] ?? null);

    if ($name === '' || $email === '' || strlen($pass) < 6) {
      \Json::fail('invalid_payload', 422);
    }

    // 🔗 Seta/atualiza cookie de indicação a partir de GET/POST se veio
    if ($ref && empty($_COOKIE['aviv_ref'])) {
      $ref = preg_replace('~[^a-zA-Z0-9_-]~', '', (string)$ref);
      if ($ref !== '') {
        $days = Affiliate::cookieDays();
        setcookie('aviv_ref', $ref, time() + $days * 86400, '/', '', false, true);
        $_COOKIE['aviv_ref'] = $ref;
      }
    }

    $pdo = \DB::pdo();

    // já existe?
    $st = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $st->execute([$email]);
    if ($st->fetch()) {
      \Json::fail('email_in_use', 409);
    }

    $hash = password_hash($pass, PASSWORD_DEFAULT);

    // colunas dinâmicas
    $colsSet    = $this->usersColumns();
    $extraCols  = [];
    $extraVals  = [];

    // mapeia cpf/cnpj
    $cpfCol = $this->pickExisting($colsSet, ['cpf_cnpj','document','doc','cpf','cnpj']);
    if ($cpfCol && $cpfCnpj !== '') { $extraCols[] = $cpfCol; $extraVals[] = $cpfCnpj; }

    // mapeia telefone
    $phoneCol = $this->pickExisting($colsSet, ['phone','cellphone','mobile','tel','phone_number','telefone','celular']);
    if ($phoneCol && $phone !== '') { $extraCols[] = $phoneCol; $extraVals[] = $phone; }

    // monta INSERT
    $baseCols      = ['name','email','password_hash','role','created_at'];
    $placeholders  = ['?','?','?','?','NOW()'];
    $baseVals      = [$name, $email, $hash, $role];

    foreach ($extraCols as $_) { $baseCols[] = $_; $placeholders[] = '?'; }
    $baseVals = array_merge($baseVals, $extraVals);

    $sql = 'INSERT INTO users ('.implode(',', $baseCols).') VALUES ('.implode(',', $placeholders).')';

    $pdo->beginTransaction();
    try {
      $ins = $pdo->prepare($sql);
      $ins->execute($baseVals);
      $id = (int)$pdo->lastInsertId();

      // 🔗 vincula indicação (cria affiliate_conversions pending para o afiliado do cookie)
      Affiliate::attachReferralOnRegister($id);

      $pdo->commit();
    } catch (\Throwable $e) {
      $pdo->rollBack();
      \Json::fail('db_error', 500);
    }

    $user = ['id'=>$id,'name'=>$name,'email'=>$email,'role'=>$role];
    \Auth::login($user);
    \Json::ok(['user'=>$user]);
  }

  public function login(): void
  {
    \Auth::start();

    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');

    if ($email === '' || $pass === '') {
      \Json::fail('invalid_payload', 422);
    }

    $pdo = \DB::pdo();

    $st = $pdo->prepare('SELECT id,name,email,password_hash,role FROM users WHERE email = ? LIMIT 1');
    $st->execute([$email]);

    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row || empty($row['password_hash']) || !password_verify($pass, $row['password_hash'])) {
      \Json::fail('invalid_credentials', 401);
    }

    $dbRole = strtolower(trim((string)($row['role'] ?? 'member')));
    $role = in_array($dbRole, ['member','partner','affiliate','admin'], true) ? $dbRole : 'member';

    $user = [
      'id'    => (int)$row['id'],
      'name'  => $row['name'],
      'email' => $row['email'],
      'role'  => $role,
    ];

    \Auth::login($user);
    \Json::ok(['user' => $user]);
  }

  public function logout(): void
  {
    \Auth::logout();
    \Json::ok(['ok'=>true]);
  }
}
