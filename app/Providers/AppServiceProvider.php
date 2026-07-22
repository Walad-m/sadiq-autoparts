<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureMySql();
    }

    /**
     * Configure default behaviours for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Apply MySQL-specific optimisations when the active connection is mysql.
     *
     * These settings are silently skipped on SQLite (dev), so the same
     * codebase works across both environments without any conditional branches
     * in migrations or queries.
     */
    protected function configureMySql(): void
    {
        // Only applies when using MySQL / MariaDB — no-op for sqlite
        Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event): void {
            $connection = $event->connection;

            if (! $connection instanceof \Illuminate\Database\MySqlConnection) {
                return;
            }

            try {
                // Ensure the session timezone is UTC regardless of server default.
                // This prevents silent date-offset bugs when the DB server is in
                // a different timezone than PHP.
                $connection->statement("SET time_zone = '+00:00'");

                // Enforce strict SQL mode so truncation/type-coercion errors
                // fail loudly instead of silently corrupting data.
                $connection->statement(
                    "SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,"
                    . "NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'"
                );
            } catch (\Throwable $e) {
                // Log but don't crash — a session-timezone failure should not
                // take down the whole request.
                Log::warning('MySQL session config failed', [
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }
}

