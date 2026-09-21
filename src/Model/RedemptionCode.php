<?php

declare(strict_types=1);

namespace Lowseekai\PointRedempt\Model;

use Flarum\Database\AbstractModel;
use Flarum\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedemptionCode extends AbstractModel
{
    protected $table = 'point_redemption_codes';
    public $timestamps = false;
    protected $fillable = ['batch_id', 'code_hash', 'code_suffix', 'redeemed_by', 'redemption_id', 'redeemed_at'];
    protected $casts = ['redeemed_at' => 'datetime'];

    public function batch(): BelongsTo { return $this->belongsTo(RedemptionBatch::class, 'batch_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'redeemed_by'); }
    public function redemption(): BelongsTo { return $this->belongsTo(Redemption::class, 'redemption_id'); }
}
