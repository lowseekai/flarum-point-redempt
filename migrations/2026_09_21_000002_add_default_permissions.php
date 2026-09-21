<?php

declare(strict_types=1);

use Illuminate\Database\ConnectionInterface;

return [
    'up' => function (ConnectionInterface $db): void {
        $db->table('group_permission')->updateOrInsert([
            'group_id' => 3,
            'permission' => 'pointRedemption.redeem',
        ]);
    },
    'down' => function (ConnectionInterface $db): void {
        $db->table('group_permission')
            ->where('group_id', 3)
            ->where('permission', 'pointRedemption.redeem')
            ->delete();
    },
];
