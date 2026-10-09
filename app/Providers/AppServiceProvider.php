<?php

namespace App\Providers;

use App\Contracts\CareerAdviserClient;
use App\Contracts\CareerAiClient;
use App\Contracts\ChatTransport;
use App\Models\AdvertisementSlot;
use App\Services\AI\GroqCareerAdviserClient;
use App\Services\AI\GroqCareerAiClient;
use App\Services\AI\GroqClient;
use App\Services\AI\HttpCareerAiClient;
use App\Services\AI\MockCareerAdviserClient;
use App\Services\AI\MockCareerAiClient;
use App\Services\AI\OpenAiCompatibleClient;
use App\Services\AI\OpenRouterClient;
use App\Services\Business\AdvertisementService;
use App\Services\Student\StudentAttention;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
         * One per request, so the navigation and the page share
         * the same "new" badge checks instead of repeating them.
         */
        $this->app->scoped(StudentAttention::class);

        /*
         * Which service runs the model. Bound separately from the
         * career clients so switching provider leaves the prompts
         * and schemas, and therefore the advice, untouched.
         */
        $this->app->bind(
            ChatTransport::class,
            function ($app) {
                return match (config('career-ai.transport')) {
                    'groq' => $app->make(
                        GroqClient::class
                    ),

                    'openrouter' => $app->make(
                        OpenRouterClient::class
                    ),

                    /*
                     * Any other name is read as a provider speaking
                     * OpenAI's API, served by its own config block.
                     * Adding one is configuration, not code. A name
                     * with no block still fails loudly, naming the
                     * setting it could not find.
                     */
                    default => new OpenAiCompatibleClient(
                        (string) config('career-ai.transport')
                    ),
                };
            }
        );

        $this->app->bind(
            CareerAiClient::class,
            function ($app) {
                return match (config('career-ai.driver')) {
                    'mock' => $app->make(
                        MockCareerAiClient::class
                    ),

                    'http' => $app->make(
                        HttpCareerAiClient::class
                    ),

                    'groq' => $app->make(
                        GroqCareerAiClient::class
                    ),

                    default => throw new \RuntimeException(
                        'Unsupported Career AI driver: '
                        .config('career-ai.driver')
                    ),
                };
            }
        );

        $this->app->bind(
            CareerAdviserClient::class,
            function ($app) {
                return match (config('career-ai.adviser_driver')) {
                    'mock' => $app->make(
                        MockCareerAdviserClient::class
                    ),

                    'groq' => $app->make(
                        GroqCareerAdviserClient::class
                    ),

                    default => throw new \RuntimeException(
                        'Unsupported Career Adviser driver: '
                        .config('career-ai.adviser_driver')
                    ),
                };
            }
        );
    }

    public function boot(): void
    {
        /*
         * The student layout needs to know whether it is drawing
         * advertising columns before it draws anything, so the
         * resolved advertisements are shared with the layout
         * rather than being passed by every controller.
         */
        View::composer(
            'layouts.app',
            function ($view) {
                $user = Auth::user();

                $view->with(
                    'pageAdvertisements',
                    $user
                        ? app(AdvertisementService::class)
                            ->forStudent($user)
                        : collect()
                );

                /*
                 * How each placement behaves is configuration
                 * rather than markup, so the layout reads it
                 * alongside what it is showing.
                 */
                $view->with(
                    'pageAdvertisementSlots',
                    $user
                        ? AdvertisementSlot::all()->keyBy('position')
                        : collect()
                );
            }
        );
    }
}
