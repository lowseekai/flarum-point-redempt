<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema): void {
        if (! $schema->hasTable('point_redemption_batches')) {
            return;
        }

        $connection = $schema->getConnection();
        $connection->statement(
            'ALTER TABLE '.$connection->getTablePrefix().'point_redemption_batches MODIFY expires_at DATETIME NULL'
        );
    },
    'down' => function (Builder $schema): void {
        if (! $schema->hasTable('point_redemption_batches')) {
            return;
        }

        $connection = $schema->getConnection();
        $connection->statement(
            'ALTER TABLE '.$connection->getTablePrefix().'point_redemption_batches MODIFY expires_at DATETIME NOT NULL'
        );
    },
];
