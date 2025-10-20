<?php

namespace App\Modules\Order\Infra\Persistence\Eloquent\Models;

use App\Modules\Event\Infra\Persistence\Eloquent\Models\EventModel;
use App\Modules\User\Infra\Persistence\Eloquent\Models\UserModel;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderModel extends Model
{
    use HasFactory, HasUlids;

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

    protected static function newFactory()
    {
        return OrderFactory::new();
    }
}
