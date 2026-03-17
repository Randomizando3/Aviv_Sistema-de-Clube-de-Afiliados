# Aviv Sistema de Clube de Afiliados

## English
Aviv Sistema de Clube de Afiliados is a PHP web application for managing an affiliate club platform with separate areas for admins, partners, affiliates, and members.

### Stack observed in the repository
- PHP 8.2+
- Composer
- MySQL or MariaDB
- PHPMailer
- Dompdf
- Docker support for local environments

### Main structure
- `app/controllers`: application controllers and API endpoints
- `app/views`: views for `admin`, `affiliate`, `partner`, `member`, and public pages
- `app/services`: business logic and support services
- `migrations`: database schema and feature migrations
- `public`: web entry point
- `storage`: generated files, logs, and temporary data

### Local setup
1. Install dependencies with Composer.
2. Configure database access in your local environment.
3. Run the required migrations.
4. Point the web server to the `public` directory.

## PortuguÃªs
Aviv Sistema de Clube de Afiliados Ã© uma aplicaÃ§Ã£o web em PHP para gerenciar uma plataforma de clube de afiliados com Ã¡reas separadas para administradores, parceiros, afiliados e membros.

### Stack observada no repositÃ³rio
- PHP 8.2+
- Composer
- MySQL ou MariaDB
- PHPMailer
- Dompdf
- Suporte a Docker para ambiente local

### Estrutura principal
- `app/controllers`: controladores da aplicaÃ§Ã£o e endpoints de API
- `app/views`: views para `admin`, `affiliate`, `partner`, `member` e pÃ¡ginas pÃºblicas
- `app/services`: lÃ³gica de negÃ³cio e serviÃ§os de apoio
- `migrations`: schema do banco e migraÃ§Ãµes de funcionalidades
- `public`: ponto de entrada web
- `storage`: arquivos gerados, logs e dados temporÃ¡rios

### ConfiguraÃ§Ã£o local
1. Instale as dependÃªncias com Composer.
2. Configure o acesso ao banco no seu ambiente local.
3. Execute as migraÃ§Ãµes necessÃ¡rias.
4. Aponte o servidor web para a pasta `public`.