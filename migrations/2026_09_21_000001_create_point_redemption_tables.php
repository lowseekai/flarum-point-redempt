<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema): void {
        if (! $schema->hasTable('point_redemption_batches')) {
            $schema->create('point_redemption_batches', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('name', 100);
                $table->unsignedInteger('points_amount');
                $table->unsignedInteger('quantity');
                $table->unsignedInteger('redeemed_count')->default(0);
                $table->dateTime('starts_at');
                $table->dateTime('expires_at')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->string('note', 500)->nullable();
                $table->unsignedInteger('created_by');
                $table->timestamps();
                $table->index(['is_enabled', 'starts_at', 'expires_at'], 'point_redemption_batch_status');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            });
        }

        if (! $schema->hasTable('point_redemption_codes')) {
            $schema->create('point_redemption_codes', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('batch_id');
                $table->char('code_hash', 64)->unique();
                $table->string('code_suffix', 4);
                $table->unsignedInteger('redeemed_by')->nullable();
                $table->unsignedInteger('redemption_id')->nullable()->unique();
                $table->dateTime('redeemed_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['batch_id', 'redeemed_at']);
                $table->foreign('batch_id')->references('id')->on('point_redemption_batches')->onDelete('cascade');
                $table->foreign('redeemed_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (! $schema->hasTable('point_redemptions')) {
            $schema->create('point_redemptions', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('code_id')->unique();
                $table->unsignedInteger('batch_id');
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('points_amount');
                $table->unsignedInteger('point_transaction_id')->nullable()->unique();
                $table->string('code_suffix', 4);
                $table->dateTime('redeemed_at');
                $table->string('request_id', 64)->nullable()->unique();
                $table->index(['user_id', 'redeemed_at']);
                $table->index(['batch_id', 'redeemed_at']);
                $table->foreign('code_id')->references('id')->on('point_redemption_codes')->onDelete('restrict');
                $table->foreign('batch_id')->references('id')->on('point_redemption_batches')->onDelete('restrict');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    },
    'down' => function (Builder $schema): void {
        $schema->dropIfExists('point_redemptions');
        $schema->dropIfExists('point_redemption_codes');
        $schema->dropIfExists('point_redemption_batches');
    },
];
