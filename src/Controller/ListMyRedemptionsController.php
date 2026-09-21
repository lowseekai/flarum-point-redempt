<?php

declare(strict_types=1);

namespace Lowseekai\PointRedempt\Controller;

use Lowseekai\PointRedempt\Model\Redemption;
use Lowseekai\PointRedempt\Support\JsonController;
use Lowseekai\PointRedempt\Support\RedemptionResources;
use Psr\Http\Message\ServerRequestInterface;
use Ramon\PointSystem\Repository\PointsRepository;

class ListMyRedemptionsController extends JsonController
{
    use RedemptionResources;

    public function __construct(private PointsRepository $points) {}

    public function handle(ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        $actor = $this->actor($request);
        $actor->assertRegistered();
        $actor->assertCan('pointRedemption.redeem');
        $items = Redemption::with('batch')->where('user_id', $actor->id)->orderByDesc('id')->limit(50)->get()
            ->map(fn ($redemption) => $this->redemptionResource($redemption))->all();

        return $this->response($items, [
            'balance' => (int) $this->points->getOrCreate($actor)->balance,
        ]);
    }
}
