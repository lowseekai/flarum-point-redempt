<?php

declare(strict_types=1);

namespace Lowseekai\PointRedempt\Controller;

use Flarum\Foundation\ValidationException;
use Lowseekai\PointRedempt\Model\RedemptionBatch;
use Lowseekai\PointRedempt\Support\JsonController;
use Lowseekai\PointRedempt\Support\RedemptionResources;
use Psr\Http\Message\ServerRequestInterface;

class UpdateBatchController extends JsonController
{
    use RedemptionResources;

    public function handle(ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        $this->actor($request)->assertCan('pointRedemption.manage');
        $id = (int) ($request->getAttribute('routeParameters')['id'] ?? 0);
        $batch = RedemptionBatch::find($id);
        if (! $batch) {
            throw new ValidationException(['batch' => '兑换码批次不存在。']);
        }

        $enabled = (bool) ($this->attributes($request)['isEnabled'] ?? false);
        if ($enabled && (now()->gte($batch->expires_at) || (int) $batch->redeemed_count >= (int) $batch->quantity)) {
            throw new ValidationException(['batch' => '已过期或已用完的批次不能启用。']);
        }

        $batch->is_enabled = $enabled;
        $batch->save();

        return $this->response($this->batchResource($batch));
    }
}
