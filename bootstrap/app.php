<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\TrackLastActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        /*
         * PHP rejects uploads above post_max_size before Laravel's
         * validation runs, so show a friendly page instead of the
         * error screen. The session has not started at this point,
         * so a redirect with a flash message would not survive.
         */
        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            $message = 'The file you uploaded is too large. Please choose a file of 10 MB or less and try again.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return response()->view('errors.413', [
                'message' => $message,
                'backUrl' => $request->headers->get('referer', url('/')),
            ], 413);
        });
    })->create();