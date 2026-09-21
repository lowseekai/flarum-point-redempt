<?php

declare(strict_types=1);

namespace Lowseekai\PointRedempt\Controller;

use Lowseekai\PointRedempt\Service\BatchService;
use Lowseekai\PointRedempt\Support\JsonController;
use Lowseekai\PointRedempt\Support\RedemptionResources;
use Psr\Http\Message\ServerRequestInterface;

class CreateBatchController extends JsonController
{
    use RedemptionResources;

    public function __construct(private BatchService $service) {}

    public function handle(ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        $actor = $this->actor($request);
        $actor->assertCan('pointRedemption.manage');
        [$batch, $codes] = $this->service->create($actor, $this->attributes($request));

        return $this->response($this->batchResource($batch), ['codes' => $codes], 201);
    }
}
