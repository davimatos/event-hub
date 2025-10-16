<?php

return [
    'rate_limit_per_minute' => env('API_RATE_LIMIT_PER_MINUTE', 60),
    'auth_token_lifetime_minutes' => env('AUTH_TOKEN_LIFETIME_MINUTES', 525600),
];
