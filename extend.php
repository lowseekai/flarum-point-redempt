<?php

declare(strict_types=1);

use Flarum\Api\Context;
use Flarum\Api\Resource\ForumResource;
use Flarum\Api\Schema;
use Flarum\Extend;
use Lowseekai\PointRedempt\Controller;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less')
        ->route('/point-redemption', 'pointRedemption.index'),
    new Extend\Locales(__DIR__.'/locale'),
    (new Extend\Routes('api'))
        ->get('/point-redemption/batches', 'pointRedemption.batches.index', Controller\ListBatchesController::class)
        ->post('/point-redemption/batches', 'pointRedemption.batches.create', Controller\CreateBatchController::class)
        ->patch('/point-redemption/batches/{id:[0-9]+}', 'pointRedemption.batches.update', Controller\UpdateBatchController::class)
        ->get('/point-redemption/redemptions', 'pointRedemption.redemptions.index', Controller\ListRedemptionsController::class)
        ->get('/point-redemption/me/redemptions', 'pointRedemption.me.index', Controller\ListMyRedemptionsController::class)
        ->post('/point-redemption/redeem', 'pointRedemption.redeem', Controller\RedeemController::class),
    (new Extend\ApiResource(ForumResource::class))->fields(fn () => [
        Schema\Boolean::make('canRedeemPoints')
            ->get(fn ($forum, Context $context) => $context->getActor()->hasPermission('pointRedemption.redeem')),
    ]),
];
