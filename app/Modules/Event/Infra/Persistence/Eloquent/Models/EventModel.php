<?php

namespace App\Modules\Event\Infra\Persistence\Eloquent\Models;

use App\Modules\User\Infra\Persistence\Eloquent\Models\UserModel;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventModel extends Model
{
    use HasUlids;

    protected $table = 'events';

    protected $fillable = [
        'organizer_id',
        'title',
        'description',
        'date',
        'ticket_price',
        'capacity',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'ticket_price' => 'float',
            'capacity' => 'integer',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'organizer_id');
    }
}
