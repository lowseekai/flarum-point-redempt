<?php

declare(strict_types=1);

namespace Lowseekai\PointRedempt\Support;

use Lowseekai\PointRedempt\Model\Redemption;
use Lowseekai\PointRedempt\Model\RedemptionBatch;

trait RedemptionResources
{
    protected function batchResource(RedemptionBatch $batch): array
    {
        $now = now();
        $status = ! $batch->is_enabled
            ? 'disabled'
            : ($now->lt($batch->starts_at)
                ? 'pending'
                : ($now->gte($batch->expires_at)
                    ? 'expired'
                    : ((int) $batch->redeemed_count >= (int) $batch->quantity ? 'exhausted' : 'active')));

        return $this->resource('point-redemption-batches', $batch->id, [
            'name' => $batch->name,
            'pointsAmount' => (int) $batch->points_amount,
            'quantity' => (int) $batch->quantity,
            'redeemedCount' => (int) $batch->redeemed_count,
            'startsAt' => $batch->starts_at?->toISOString(),
            'expiresAt' => $batch->expires_at?->toISOString(),
            'isEnabled' => (bool) $batch->is_enabled,
            'status' => $status,
            'note' => $batch->note,
            'createdBy' => (int) $batch->created_by,
            'createdAt' => $batch->created_at?->toISOString(),
        ]);
    }

    protected function redemptionResource(Redemption $redemption, bool $admin = false): array
    {
        $attributes = [
            'batchId' => (int) $redemption->batch_id,
            'batchName' => $redemption->batch?->name,
            'pointsAmount' => (int) $redemption->points_amount,
            'codeSuffix' => $redemption->code_suffix,
            'redeemedAt' => $redemption->redeemed_at?->toISOString(),
        ];

        if ($admin) {
            $attributes['userId'] = (int) $redemption->user_id;
            $attributes['username'] = $redemption->user?->username;
            $attributes['pointTransactionId'] = $redemption->point_transaction_id ? (int) $redemption->point_transaction_id : null;
        }

        return $this->resource('point-redemptions', $redemption->id, $attributes);
    }
}
