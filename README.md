# Aviv Sistema de Clube de Afiliados

## English
PHP web application for managing an affiliate club platform with dedicated areas for admins, partners, affiliates, and members.

### Stack observed
- PHP 8.2+
- Composer
- MySQL or MariaDB
- PHPMailer
- Dompdf
- Docker support for local environments

### Main structure
- app/controllers: controllers and API endpoints
- app/views: admin, affiliate, partner, member, and public views
- app/services: business logic and support services
- migrations: database and feature migrations
- public: web entry point

### Local setup
1. Install dependencies with Composer.
2. Configure database access locally.
3. Run the required migrations.
4. Point the web server to public.

## Português
Aplicação web em PHP para gerenciar uma plataforma de clube de afiliados com áreas dedicadas para administradores, parceiros, afiliados e membros.

### Stack observada
- PHP 8.2+
- Composer
- MySQL ou MariaDB
- PHPMailer
- Dompdf
- Suporte a Docker para ambiente local

### Estrutura principal
- app/controllers: controladores e endpoints de API
- app/views: views de admin, affiliate, partner, member e páginas públicas
- app/services: lógica de negócio e serviços de apoio
- migrations: migrações de banco e funcionalidades
- public: ponto de entrada web

### Configuração local
1. Instale as dependências com Composer.
2. Configure o acesso ao banco localmente.
3. Execute as migrações necessárias.
4. Aponte o servidor web para public.
