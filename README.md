# Aviv Sistema de Clube de Afiliados

Aplicacao web em PHP voltada para a operacao de um clube de afiliados, com modulos para administracao, parceiros, afiliados e membros.

## Tecnologias observadas

- PHP 8.2+
- Composer
- MySQL ou MariaDB
- PHPMailer
- Dompdf
- Dockerfile para ambiente local

## Estrutura principal

- `app/controllers`: controladores da aplicacao e endpoints de API
- `app/views`: telas separadas por area, incluindo `admin`, `affiliate`, `partner`, `member` e `site`
- `app/services`: servicos de negocio, autenticacao e afiliacao
- `migrations`: scripts de banco para schema, afiliados, memberships, payouts e integracoes
- `public`: ponto de entrada HTTP da aplicacao
- `storage`: arquivos temporarios, logs e documentos gerados

## Funcionalidades identificadas

- Autenticacao e sessao
- Dashboards por perfil de usuario
- Rotas publicas e API
- Fluxos de afiliacao e captura de referencia
- Geracao de PDF e envio de email

## Como iniciar

1. Instale as dependencias com Composer.
2. Configure banco de dados e credenciais localmente.
3. Execute as migrations necessarias.
4. Aponte o servidor web para a pasta `public`.

## Status

README inicial criado a partir da estrutura atual do repositorio.