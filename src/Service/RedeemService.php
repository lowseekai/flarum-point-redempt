<?php

declare(strict_types=1);

namespace Lowseekai\PointRedempt\Service;

use Flarum\Foundation\ValidationException;
use Flarum\User\User;
use Illuminate\Database\ConnectionInterface;
use Lowseekai\PointRedempt\Model\Redemption;
use Lowseekai\PointRedempt\Model\RedemptionBatch;
use Lowseekai\PointRedempt\Model\RedemptionCode;
use Lowseekai\PointRedempt\Support\CodeCodec;
use Ramon\PointSystem\Repository\PointsRepository;

class RedeemService
{
    public function __construct(
        private ConnectionInterface $db,
        private PointsRepository $points,
        private BatchService $batches,
    ) {
    }

    public function redeem(User $user, string $plainCode, ?string $requestId = null): Redemption
    {
        $normalized = CodeCodec::normalize($plainCode);
        if (strlen($normalized) !== 18 || ! str_starts_with($normalized, 'LS')) {
            throw new ValidationException(['code' => '请输入有效的兑换码。']);
        }

        $hash = CodeCodec::hash($normalized, $this->batches->secret());

        return $this->db->transaction(function () use ($user, $hash, $requestId): Redemption {
            if ($requestId) {
                $existing = Redemption::where('request_id', $requestId)->where('user_id', $user->id)->first();
                if ($existing) {
                    return $existing->load('batch');
                }
            }

            $code = RedemptionCode::where('code_hash', $hash)->lockForUpdate()->first();
            if (! $code) {
                throw new ValidationException(['code' => '兑换码无效。']);
            }
            if ($code->redeemed_at || $code->redemption_id) {
                throw new ValidationException(['code' => '兑换码已被使用。']);
            }

            $batch = RedemptionBatch::whereKey($code->batch_id)->lockForUpdate()->first();
            if (! $batch || ! $batch->is_enabled) {
                throw new ValidationException(['code' => '兑换码当前不可用。']);
            }

            $now = now();
            if ($now->lt($batch->starts_at)) {
                throw new ValidationException(['code' => '兑换码尚未生效。']);
            }
            if ($now->gte($batch->expires_at)) {
                throw new ValidationException(['code' => '兑换码已过期。']);
            }

            $redemption = Redemption::create([
                'code_id' => $code->id,
                'batch_id' => $batch->id,
                'user_id' => $user->id,
                'points_amount' => $batch->points_amount,
                'code_suffix' => $code->code_suffix,
                'redeemed_at' => $now,
                'request_id' => $requestId ?: null,
            ]);

            $transaction = $this->points->award(
                $user,
                (int) $batch->points_amount,
                'redemption_code.redeemed',
                'point_redemption',
                (int) $redemption->id,
                ['batch_id' => (int) $batch->id, 'code_suffix' => $code->code_suffix],
            );

            if (! $transaction) {
                throw new ValidationException(['points' => '积分服务暂不可用，请稍后重试。']);
            }

            $redemption->point_transaction_id = $transaction->id;
            $redemption->save();
            $code->redeemed_by = $user->id;
            $code->redemption_id = $redemption->id;
            $code->redeemed_at = $now;
            $code->save();
            $batch->redeemed_count = (int) $batch->redeemed_count + 1;
            $batch->save();

            return $redemption->load('batch');
        });
    }
}
