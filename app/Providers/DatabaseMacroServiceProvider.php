<?php

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class DatabaseMacroServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Blueprint::macro('_timestamps', function () {
            $this->timestampTz('_createdAt')->useCurrent();
            $this->timestampTz('_updatedAt')->useCurrent()->useCurrentOnUpdate();
            $this->timestampTz('_deletedAt')->nullable();
        });

        Blueprint::macro('_id', function () {
            $this->uuid('_id')->primary();
        });
    }
}
