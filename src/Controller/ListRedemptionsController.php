<?php

declare(strict_types=1);

namespace Lowseekai\PointRedempt\Controller;

use Lowseekai\PointRedempt\Model\Redemption;
use Lowseekai\PointRedempt\Support\JsonController;
use Lowseekai\PointRedempt\Support\RedemptionResources;
use Psr\Http\Message\ServerRequestInterface;

class ListRedemptionsController extends JsonController
{
    use RedemptionResources;

    public function handle(ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        $this->actor($request)->assertCan('pointRedemption.manage');
        $page = max(1, (int) ($request->getQueryParams()['page'] ?? 1));
        $query = Redemption::with(['batch', 'user'])->orderByDesc('id');
        if ($batchId = (int) ($request->getQueryParams()['batchId'] ?? 0)) {
            $query->where('batch_id', $batchId);
        }
        $total = $query->count();
        $items = $query->forPage($page, 50)->get()->map(fn ($redemption) => $this->redemptionResource($redemption, true))->all();

        return $this->response($items, ['total' => $total, 'page' => $page]);
    }
}
