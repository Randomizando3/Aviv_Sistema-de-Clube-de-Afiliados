<?php
namespace App\Services;

final class Asaas
{
  private string $base;
  private string $key;

  public function __construct(?string $key = null, ?string $base = null)
  {
    $base0 = $base
      ?: (\defined('ASAAS_BASE') ? \ASAAS_BASE : (getenv('ASAAS_BASE') ?: 'https://api-sandbox.asaas.com/v3'));
    $key0  = $key
      ?: (\defined('ASAAS_API_KEY') ? \ASAAS_API_KEY : (getenv('ASAAS_API_KEY') ?: ''));

    // Normaliza .env (remove aspas e barras finais)
    $base0 = rtrim(trim($base0, " \t\n\r\0\x0B\"'"), '/');
    $key0  = trim($key0, " \t\n\r\0\x0B\"'");

    $this->base = $base0;
    $this->key  = $key0;

    if (!$this->key) {
      throw new \RuntimeException('Asaas API key ausente (defina ASAAS_API_KEY).');
    }
  }

  /** HTTP base */
  private function req(string $method, string $path, ?array $body = null, array $qs = []): array
  {
    $url = rtrim($this->base, '/') . '/' . ltrim($path, '/');
    if ($qs) $url .= '?' . http_build_query($qs);

    $ch = curl_init($url);
    $headers = [
      'Accept: application/json',
      'Content-Type: application/json',
      'access_token: ' . $this->key,
      'User-Agent: AvivPlusSandbox/1.0',
    ];
    curl_setopt_array($ch, [
      CURLOPT_CUSTOMREQUEST  => strtoupper($method),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER     => $headers,
      CURLOPT_TIMEOUT        => 30,
    ]);
    curl_setopt($ch, CURLOPT_USERAGENT, 'AvivPlusSandbox/1.0');

    if ($body !== null) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }

    $out  = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    if (!$code) $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($out === false) {
      throw new \RuntimeException('Erro cURL: ' . $err);
    }

    $json = json_decode((string)$out, true);
    if ($code >= 400) {
      $msg = is_array($json) ? (json_encode($json, JSON_UNESCAPED_UNICODE) ?: (string)$out) : (string)$out;
      throw new \RuntimeException("Asaas HTTP $code: $msg");
    }
    return is_array($json) ? $json : [];
  }

  /* ===== Customers ===== */

  public function findCustomerByEmail(string $email): ?array
  {
    $res = $this->req('GET', 'customers', null, ['email' => $email, 'limit' => 1]);
    return ($res['totalCount'] ?? 0) ? ($res['data'][0] ?? null) : null;
  }

  public function createCustomer(array $payload): array
  {
    return $this->req('POST', 'customers', $payload);
  }

  public function updateCustomer(string $customerId, array $data): array
  {
    return $this->req('PUT', 'customers/' . urlencode($customerId), $data);
  }

  /** Garante um customer no Asaas e retorna o ID */
  public function ensureCustomer(array $data): string
  {
    $email = (string)($data['email'] ?? '');
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new \InvalidArgumentException('E-mail inválido para criar/obter cliente.');
    }

    $cli = $this->findCustomerByEmail($email);
    if ($cli && !empty($cli['id'])) {
      return (string)$cli['id'];
    }

    $payload = [
      'name'        => (string)($data['name'] ?? 'Cliente'),
      'email'       => $email,
      'cpfCnpj'     => isset($data['cpfCnpj']) ? preg_replace('/\D+/', '', (string)$data['cpfCnpj']) : null,
      'mobilePhone' => isset($data['mobilePhone']) ? preg_replace('/\D+/', '', (string)$data['mobilePhone']) : null,
    ];
    $created = $this->createCustomer($payload);
    if (empty($created['id'])) {
      throw new \RuntimeException('Falha ao criar cliente no Asaas.');
    }
    return (string)$created['id'];
  }

  /* ===== Subscriptions (mantém para seus planos) ===== */

  public function createSubscription(array $payload): array
  {
    return $this->req('POST', 'subscriptions', $payload);
  }

  public function getSubscription(string $subscriptionId): array
  {
    return $this->req('GET', 'subscriptions/' . urlencode($subscriptionId));
  }

  /* ===== Payments ===== */

  public function getPayments(array $qs): array
  {
    return $this->req('GET', 'payments', null, $qs);
  }

  public function getPaymentById(string $paymentId): array
  {
    return $this->req('GET', 'payments/' . urlencode($paymentId));
  }

  /**
   * Cria um pagamento avulso (ONE-TIME) e retorna o objeto do Asaas.
   * Requer: ['customerData'=>['name','email','cpfCnpj?','mobilePhone?'], 'value', 'description', 'billingType', 'externalReference?']
   * billingType: 'BOLETO' | 'PIX' | 'CREDIT_CARD' (para cartão, seria preciso token de cartão; não tratamos aqui)
   */
  public function createOneTimePayment(array $payload): array
  {
    $customerData = $payload['customerData'] ?? [];
    $customerId   = $this->ensureCustomer($customerData);

    $value       = (float)($payload['value'] ?? 0);
    $description = (string)($payload['description'] ?? '');
    $billingType = strtoupper((string)($payload['billingType'] ?? 'BOLETO'));
    $externalRef = isset($payload['externalReference']) ? (string)$payload['externalReference'] : null;

    if ($value <= 0) throw new \InvalidArgumentException('Valor inválido.');
    if ($description === '') $description = 'Pagamento';

    $pay = [
      'customer'          => $customerId,
      'billingType'       => in_array($billingType, ['PIX','BOLETO','CREDIT_CARD'], true) ? $billingType : 'BOLETO',
      'value'             => $value,
      'description'       => $description,
      'externalReference' => $externalRef,
    ];

    // opcional: dueDate para boleto
    if (!empty($payload['dueDate'])) {
      $pay['dueDate'] = (string)$payload['dueDate']; // 'YYYY-MM-DD'
    }

    // Observação: para cartão seria necessário tokenizar e enviar creditCard/creditCardHolderInfo.

    return $this->req('POST', 'payments', $pay);
  }

  /* ====== (mantemos o método de Checkout caso use em outra parte do site) ====== */

  public function createCheckout(array $payload): array
  {
    // MANTIDO apenas para compatibilidade (assinaturas). Para anúncios usamos payments.
    // Se quiser continuar usando checkout em outra parte, o método segue válido.
    // Aqui só garantimos que não quebre se invocado.
    return $this->req('POST', 'checkoutSession', $payload);
  }
}
