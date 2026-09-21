<?php

declare(strict_types=1);

namespace Lowseekai\PointRedempt\Service;

use Flarum\Foundation\ValidationException;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Lowseekai\PointRedempt\Model\RedemptionBatch;
use Lowseekai\PointRedempt\Model\RedemptionCode;
use Lowseekai\PointRedempt\Support\CodeCodec;

class BatchService
{
    public function __construct(
        private ConnectionInterface $db,
        private SettingsRepositoryInterface $settings,
    ) {
    }

    public function create(User $actor, array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $points = filter_var($data['pointsAmount'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($data['quantity'] ?? null, FILTER_VALIDATE_INT);
        $note = trim((string) ($data['note'] ?? ''));

        $startsAtInput = trim((string) ($data['startsAt'] ?? ''));
        $expiresAtInput = trim((string) ($data['expiresAt'] ?? ''));
        $permanent = (bool) ($data['permanent'] ?? false);

        try {
            if ($startsAtInput === '' || (! $permanent && $expiresAtInput === '')) {
                throw new \InvalidArgumentException('Missing batch dates');
            }

            $startsAt = Carbon::parse($startsAtInput);
            $expiresAt = $permanent ? null : Carbon::parse($expiresAtInput);
        } catch (\Throwable) {
            throw new ValidationException(['time' => '请输入有效的生效和失效时间。']);
        }

        if ($name === '' || mb_strlen($name) > 100) {
            throw new ValidationException(['name' => '批次名称需要为 1 到 100 个字符。']);
        }
        if ($points === false || $points < 1 || $points > 1000000) {
            throw new ValidationException(['pointsAmount' => '每码积分必须为 1 到 1000000。']);
        }
        if ($quantity === false || $quantity < 1 || $quantity > 1000) {
            throw new ValidationException(['quantity' => '生成数量必须为 1 到 1000。']);
        }
        if ($expiresAt && $expiresAt->lte($startsAt)) {
            throw new ValidationException(['expiresAt' => '失效时间必须晚于生效时间。']);
        }
        if (mb_strlen($note) > 500) {
            throw new ValidationException(['note' => '备注不能超过 500 个字符。']);
        }

        return $this->db->transaction(function () use ($actor, $name, $points, $quantity, $startsAt, $expiresAt, $note): array {
            $batch = RedemptionBatch::create([
                'name' => $name,
                'points_amount' => $points,
                'quantity' => $quantity,
                'redeemed_count' => 0,
                'starts_at' => $startsAt->utc(),
                'expires_at' => $expiresAt?->utc(),
                'is_enabled' => true,
                'note' => $note ?: null,
                'created_by' => $actor->id,
            ]);

            $secret = $this->secret();
            $plainCodes = [];
            for ($i = 0; $i < $quantity; $i++) {
                do {
                    $code = CodeCodec::generate();
                    $hash = CodeCodec::hash($code, $secret);
                } while (RedemptionCode::where('code_hash', $hash)->exists());

                RedemptionCode::create([
                    'batch_id' => $batch->id,
                    'code_hash' => $hash,
                    'code_suffix' => CodeCodec::suffix($code),
                ]);
                $plainCodes[] = $code;
            }

            return [$batch, $plainCodes];
        });
    }

    public function secret(): string
    {
        $key = (string) $this->settings->get('point-redempt.hmac_secret', '');
        if ($key === '') {
            $key = bin2hex(random_bytes(32));
            $this->settings->set('point-redempt.hmac_secret', $key);
        }

        return $key;
    }
}
