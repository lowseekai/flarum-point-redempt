<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema): void {
        $db = $schema->getConnection();

        $db->table('group_permission')->updateOrInsert([
            'group_id' => 3,
            'permission' => 'pointRedemption.redeem',
        ]);
    },
    'down' => function (Builder $schema): void {
        $db = $schema->getConnection();

        $db->table('group_permission')
            ->where('group_id', 3)
            ->where('permission', 'pointRedemption.redeem')
            ->delete();
    },
];
