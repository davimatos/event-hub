<?php

namespace App\Modules\User\Domain\Enums;

enum UserType: int
{
    case ORGANIZER = 1;
    case PARTICIPANT = 2;
}
