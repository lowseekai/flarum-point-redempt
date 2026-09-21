<?php

declare(strict_types=1);

namespace Lowseekai\PointRedempt\Controller;

use Lowseekai\PointRedempt\Model\RedemptionBatch;
use Lowseekai\PointRedempt\Support\JsonController;
use Lowseekai\PointRedempt\Support\RedemptionResources;
use Psr\Http\Message\ServerRequestInterface;

class ListBatchesController extends JsonController
{
    use RedemptionResources;

    public function handle(ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        $this->actor($request)->assertCan('pointRedemption.manage');
        $page = max(1, (int) ($request->getQueryParams()['page'] ?? 1));
        $query = RedemptionBatch::query()->orderByDesc('id');
        $total = $query->count();
        $items = $query->forPage($page, 30)->get()->map(fn ($batch) => $this->batchResource($batch))->all();

        return $this->response($items, ['total' => $total, 'page' => $page]);
    }
}
