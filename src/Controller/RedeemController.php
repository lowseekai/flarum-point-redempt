<?php

declare(strict_types=1);

namespace Lowseekai\PointRedempt\Controller;

use Lowseekai\PointRedempt\Service\RedeemService;
use Lowseekai\PointRedempt\Support\JsonController;
use Lowseekai\PointRedempt\Support\RedemptionResources;
use Psr\Http\Message\ServerRequestInterface;
use Ramon\PointSystem\Repository\PointsRepository;

class RedeemController extends JsonController
{
    use RedemptionResources;

    public function __construct(
        private RedeemService $service,
        private PointsRepository $points,
    ) {}

    public function handle(ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        $actor = $this->actor($request);
        $actor->assertRegistered();
        $actor->assertCan('pointRedemption.redeem');
        $data = $this->attributes($request);
        $redemption = $this->service->redeem(
            $actor,
            (string) ($data['code'] ?? ''),
            isset($data['requestId']) ? (string) $data['requestId'] : null,
        );
        $balance = (int) $this->points->getOrCreate($actor->fresh())->balance;

        return $this->response($this->redemptionResource($redemption), ['balance' => $balance], 201);
    }
}
