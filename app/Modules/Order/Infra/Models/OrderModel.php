<?php

namespace App\Modules\Order\Infra\Models;

use App\Modules\Event\Infra\Models\EventModel;
use App\Modules\Ticket\Infra\Models\TicketModel;
use App\Modules\User\Infra\Models\UserModel;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderModel extends Model
{
    use HasUlids;

    protected $table = 'orders';

    protected $fillable = [
        'event_id',
        'participant_id',
        'quantity',
        'ticket_price',
        'discount',
        'total_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'ticket_price' => 'float',
            'discount' => 'float',
            'total_amount' => 'float',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(EventModel::class, 'event_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'participant_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(TicketModel::class, 'order_id');
    }
}
