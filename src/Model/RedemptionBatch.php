<?php

declare(strict_types=1);

namespace Lowseekai\PointRedempt\Model;

use Flarum\Database\AbstractModel;
use Flarum\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RedemptionBatch extends AbstractModel
{
    protected $table = 'point_redemption_batches';

    protected $fillable = ['name', 'points_amount', 'quantity', 'redeemed_count', 'starts_at', 'expires_at', 'is_enabled', 'note', 'created_by'];

    protected $casts = [
        'points_amount' => 'integer',
        'quantity' => 'integer',
        'redeemed_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_enabled' => 'boolean',
    ];

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function codes(): HasMany { return $this->hasMany(RedemptionCode::class, 'batch_id'); }
    public function redemptions(): HasMany { return $this->hasMany(Redemption::class, 'batch_id'); }
}
