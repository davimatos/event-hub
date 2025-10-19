<?php

namespace App\Modules\Order\Infra\Persistence\Eloquent\Models;

use App\Modules\Event\Infra\Persistence\Eloquent\Models\EventModel;
use App\Modules\User\Infra\Persistence\Eloquent\Models\UserModel;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketModel extends Model
{
    use HasUlids;

    protected $table = 'tickets';

    protected $fillable = [
        'order_id',
        'event_id',
        'participant_id',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(EventModel::class, 'event_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'participant_id');
    }
}
