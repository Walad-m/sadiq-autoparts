<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\TrustProxies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->use([
            TrustProxies::class,
        ]);

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // ──────────────────────────────────────────────────────────────────
        // SQLSTATE codes that represent infrastructure/connectivity failures.
        // These are the ONLY cases we silently swallow — everything else
        // (constraint violations, bad SQL, etc.) must surface as a 500 so
        // developers and logs get the real error.
        //
        //  2002 → Connection refused (server not reachable)
        //  2006 → MySQL server has gone away (idle timeout)
        //  2013 → Lost connection to MySQL during query
        //  1044 → Access denied for user to database
        //  1045 → Access denied (wrong credentials)
        // ──────────────────────────────────────────────────────────────────
        $connectionErrorCodes = ['2002', '2006', '2013', '1044', '1045', 'HY000'];

        // ── 1. QueryException ────────────────────────────────────────────
        // Only intercept genuine connection failures; everything else
        // (duplicate key = 23000, FK = 23000, syntax = 42000, etc.)
        // returns null so Laravel's default 500 handler takes over.
        $exceptions->render(function (\Illuminate\Database\QueryException $e, Request $request) use ($connectionErrorCodes) {
            $code = (string) $e->getCode();

            if (! in_array($code, $connectionErrorCodes, true)) {
                // Not a connection error — let it bubble as a normal 500.
                // The stack trace is already written to the log by Laravel.
                return null;
            }

            \Illuminate\Support\Facades\Log::critical('Database connection lost', [
                'sqlstate'   => $code,
                'connection' => $e->getConnectionName(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Service temporarily unavailable. Please try again shortly.'], 503);
            }

            return Inertia::render('error', ['status' => 503])
                ->toResponse($request)
                ->setStatusCode(503);
        });

        // ── 2. PDOException ──────────────────────────────────────────────
        // Raw PDO connection errors (happen before Laravel wraps in QueryException,
        // e.g. very early in the boot cycle like session start).
        $exceptions->render(function (\PDOException $e, Request $request) use ($connectionErrorCodes) {
            $code = (string) $e->getCode();

            if (! in_array($code, $connectionErrorCodes, true)) {
                return null; // bubble as 500
            }

            \Illuminate\Support\Facades\Log::critical('Database connection refused (PDO)', [
                'sqlstate' => $code,
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Service temporarily unavailable. Please try again shortly.'], 503);
            }

            return Inertia::render('error', ['status' => 503])
                ->toResponse($request)
                ->setStatusCode(503);
        });

        // ── 3. HTTP response → Inertia error page ───────────────────────
        // Runs AFTER the exception has already been converted to an HTTP
        // response. We re-render it as an Inertia page so the React shell
        // stays intact and the user sees a branded error page.
        //
        // IMPORTANT — do NOT intercept:
        //   • 200  → normal responses
        //   • 302  → redirects (login redirects, back() calls, etc.)
        //   • 422  → ValidationException — Inertia handles these natively
        //             by flashing errors back to the previous form; if we
        //             intercept them the user's field-level errors disappear.
        //   • JSON → API clients need raw responses, not HTML error pages
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $exception, Request $request) {
            $status = $response->getStatusCode();

            // Never touch API/JSON responses
            if ($request->expectsJson()) {
                return $response;
            }

            // Never touch redirects — these are intentional (auth redirects,
            // after-form redirects, etc.)
            if ($status >= 301 && $status <= 308) {
                return $response;
            }

            // Never touch 422 — Inertia catches these and sends errors back
            // to the form fields automatically via the session flash.
            if ($status === 422) {
                return $response;
            }

            // 5xx: infrastructure / application errors — generic error page,
            // no detail shown to the user (real error is in the log).
            if ($status >= 500) {
                return Inertia::render('error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            // 4xx: user-facing errors with dedicated copy in error.tsx
            if (in_array($status, [403, 404, 419, 429], true)) {
                return Inertia::render('error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            return $response;
        });
    })->create();

