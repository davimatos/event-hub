<?php

return [
    'rate_limit_per_minute' => env('API_RATE_LIMIT_PER_MINUTE', 60),
    'auth_token_lifetime_minutes' => env('AUTH_TOKEN_LIFETIME_MINUTES', 525600),
    'max_tickets_per_order' => env('MAX_TICKETS_PER_ORDER', 5),
    'max_tickets_per_event' => env('MAX_TICKETS_PER_EVENT', 15),
];
