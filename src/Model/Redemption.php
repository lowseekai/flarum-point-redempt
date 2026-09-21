<?php

declare(strict_types=1);

namespace Lowseekai\PointRedempt\Model;

use Flarum\Database\AbstractModel;
use Flarum\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Redemption extends AbstractModel
{
    protected $table = 'point_redemptions';
    public $timestamps = false;
    protected $fillable = ['code_id', 'batch_id', 'user_id', 'points_amount', 'point_transaction_id', 'code_suffix', 'redeemed_at', 'request_id'];
    protected $casts = ['points_amount' => 'integer', 'point_transaction_id' => 'integer', 'redeemed_at' => 'datetime'];

    public function batch(): BelongsTo { return $this->belongsTo(RedemptionBatch::class, 'batch_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function code(): BelongsTo { return $this->belongsTo(RedemptionCode::class, 'code_id'); }
}
