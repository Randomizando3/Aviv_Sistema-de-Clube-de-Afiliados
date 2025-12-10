<?php
final class Json {
  public static function ok($data = []): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE); exit;
  }
  public static function fail(string $msg, int $code = 400): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error'=>$msg], JSON_UNESCAPED_UNICODE); exit;
  }
}
