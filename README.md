# Event Hub

Sistema de gerenciamento de eventos com venda de ingressos, processamento de pagamentos e notificações.

## Requisitos

![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat&logo=php&logoColor=white) ![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat&logo=laravel&logoColor=white) ![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white) ![PHPUnit](https://img.shields.io/badge/PHPUnit-11.5.3-3C9CD7?style=flat&logo=php&logoColor=white) ![Docker](https://img.shields.io/badge/Docker-Required-2496ED?style=flat&logo=docker&logoColor=white)

## Configuração Inicial

### 1. Inicialize os containers Docker

```bash
docker-compose up -d
```

### 2. Configuração e inicialização do projeto

```bash
# Copiar arquivo de ambiente
cp .env.example .env

# Instalar dependências
docker-compose exec laravel.test composer install

# Executar comandos de inicialização
docker-compose exec laravel.test bash -c "php artisan key:generate && php artisan migrate && php artisan db:seed && php artisan migrate --env=testing"
```

Após a inicialização e execução dos containers, os seguintes recursos estarão disponíveis:

| Serviço | Porta | Descrição                          | Como acessar |
|---------|-------|------------------------------------|--------------|
| **Laravel (API)** | `80` | Aplicação principal                | http://localhost/api/v1/ |
| **MySQL** | `3306` | Banco de dados                     | `localhost:3306` |
| **Workers** | - | Processamento de filas | `php artisan queue:work --queue=notifications` |
| **Documentação** | - | Swagger/OpenAPI | http://localhost/docs* |

<small>* Para gerar a documentação Swagger, execute: <code>php artisan l5-swagger:generate</code></small>


## Parâmetros

O projeto possui parâmetros customizáveis no arquivo `.env`:

```env
API_RATE_LIMIT_PER_MINUTE=60      # Limite de requisições por minuto
AUTH_TOKEN_LIFETIME_SECONDS=3600  # Tempo de vida do token (1 hora)
MAX_TICKETS_PER_ORDER=5           # Máximo de ingressos por pedido
MAX_TICKETS_PER_EVENT=15          # Máximo de ingressos por evento por usuário
```

Os cupons de desconto são hardcoded no sistema:

| Código | Desconto |
|--------|----------|
| `BLACKFRIDAY` | 50% |
| `PROMO30` | 30% |
| `10OFF` | 20% |

## Testes

Para rodar a suíte de testes com PHPUnit (Unit, Feature e E2E):

```bash
php artisan test
```
