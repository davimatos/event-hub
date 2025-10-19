<?php

namespace App\Framework\Http\Middleware;

use App\Modules\Shared\Application\Exceptions\UnauthorizedException;
use App\Modules\User\Domain\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizerUserMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $authUser = Auth::user();

        if ($authUser->type != UserType::ORGANIZER->value) {
            throw new UnauthorizedException;
        }

        return $next($request);
    }
}
