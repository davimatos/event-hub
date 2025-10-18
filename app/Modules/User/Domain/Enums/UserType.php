<?php

namespace App\Modules\User\Domain\Enums;

enum UserType: string
{
    case ORGANIZER = 'organizer';
    case PARTICIPANT = 'participant';
}
