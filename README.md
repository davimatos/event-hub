# Event Hub

Sistema de gerenciamento de eventos com venda de ingressos, processamento de pagamentos e notificações.

## Requisitos

![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat&logo=php&logoColor=white) ![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat&logo=laravel&logoColor=white) ![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white) ![PHPUnit](https://img.shields.io/badge/PHPUnit-11.5.3-3C9CD7?style=flat&logo=php&logoColor=white) ![Docker](https://img.shields.io/badge/Docker-Required-2496ED?style=flat&logo=docker&logoColor=white)

## Configuração Inicial

### Configuração e inicialização do projeto

```bash
# Copiar arquivo de ambiente
cp .env.example .env

# Inicialize os containers Docker
docker-compose up -d

# Instalar dependências
docker-compose exec eventhub.api composer install

# Executar comandos de inicialização
docker-compose exec eventhub.api bash -c "php artisan key:generate && php artisan migrate && php artisan db:seed && php artisan migrate --env=testing"
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

## Arquitetura e Design

A aplicação segue um design modular com DDD e princípios de Clean Architecture. Abaixo, alguns gráficos para orientar a navegação pelo código e entender dependências e fluxos.

### 1) Visão DDD (Bounded Contexts e dependências)

```mermaid
graph LR
  subgraph User[User Context]
    UEnt[Entities/VOs]
    UApp[UseCases]
  end

  subgraph Auth[Auth Context]
    AUse[UseCases]
  end

  subgraph Event[Event Context]
    EEnt[Entities/VOs]
    EApp[UseCases]
  end

  subgraph Order[Order Context]
    OEnt[Entities/VOs]
    OApp[UseCases/Services]
  end

  subgraph PaymentProcessor[Payment Context]
    PP[Processor Service]
  end

  subgraph Shared[Shared Context]
    SVO[Value Objects]
    SPorts[Ports/Adapters]
  end

  Auth --> User
  Event --> User
  Order --> Event
  Order --> User
  Order --> PaymentProcessor
  User --> Shared
  Auth --> Shared
  Event --> Shared
  Order --> Shared
  PaymentProcessor --> Shared
```

Legenda:
- Cada módulo encapsula suas entidades, casos de uso e portas/repos.
- Shared concentra VOs, exceções e contratos (adapters/ports) usados por vários módulos.

### 2) Camadas (Clean Architecture)

```mermaid
graph TD
  subgraph Interface[Interface Layer]
    Ctr[Controllers]
    Req[Requests]
  end

  subgraph Application[Application Layer]
    UC[UseCases]
    Svc[App Services]
    DTO[DTOs]
    Ports[Repository Interfaces]
  end

  subgraph Domain[Domain Layer]
    Ent[Entities]
    VO[Value Objects]
    DExc[Domain Exceptions]
  end

  subgraph Infrastructure[Infrastructure Layer]
    RepoImpl[Repositories Impl]
    Adapters[External Adapters]
    Gateways[External Services]
  end

  Ctr --> UC
  Req --> UC
  UC --> Ports
  UC --> Ent
  Ent --> VO
  RepoImpl -.-> Ports
  Adapters -.-> Ports
  Gateways -.-> Adapters
```

Regras:
- Interface depende de Application; Application depende de Domain.
- Infrastructure implementa as portas definidas em Application/Domain e é injetada de fora.

### 3) Fluxo: Compra de Ingressos (buy-ticket)

```mermaid
sequenceDiagram
  participant C as Client
  participant API as HTTP Controller
  participant UC as CreateOrderUseCase
  participant ER as EventRepository
  participant OR as OrderRepository
  participant PP as PaymentProcessor
  participant TM as TransactionManager
  participant NS as NewOrderNotification

  C->>API: POST /api/v1/buy-ticket
  API->>UC: execute(dto)
  UC->>ER: getById(eventId)
  UC->>ER: getRemainingTickets(eventId)
  UC->>PP: process(order, creditCard)
  alt Autorizado
    UC->>TM: run(transaction)
    TM->>OR: create(order)
    TM->>ER: decrementRemainingTickets(eventId, qty)
    UC->>NS: execute(newOrder)
    UC-->>API: OrderOutputDto
  else Falha pagamento
    UC-->>API: OrderPaymentFailException
  end
```

### 4) Agregados e Relacionamentos (alto nível)

```mermaid
classDiagram
  class User {
    +id
    +name
    +email
    +type (PARTICIPANT|ORGANIZER)
  }

  class Event {
    +id
    +organizer_id (User)
    +title
    +description
    +date
    +ticket_price
    +capacity
    +remaining_tickets
  }

  class Order {
    +id
    +event_id (Event)
    +participant_id (User)
    +quantity
    +ticket_price
    +discount
    +total_amount
    +status
  }

  class Ticket {
    +id
    +order_id (Order)
    +event_id (Event)
    +participant_id (User)
    +used_at
  }

  Event <.. User : organizer_id
  Order <.. Event : reference (event_id)
  Order <.. User : participant_id
  Order "1" o-- "many" Ticket
```

Notas:
- Event é um agregado próprio e não carrega Order/Ticket.
- Order é agregado raiz dos Tickets (composição); relaciona-se a Event e User por ID.
- User é agregado independente (usado por Auth, Event e Order).
