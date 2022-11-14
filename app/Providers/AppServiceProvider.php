<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\User;
use App\Observers\CourseObserver;
use App\Observers\UserObserver;
use App\Services\AdminNotifier;
use App\Services\CustomUserProvider;
use App\Services\Settings\ApplicationSettings;
use App\Services\Settings\SettingsProvider;
use App\Services\Settings\SettingsProviderImpl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Blade::directive('dateTime', function ($expr) {
            return "<?= {$expr}->isoFormat('ddd D MMMM Y HH:mm') ?>";
        });

        Blade::directive('date', function ($expr) {
            return "<?= {$expr}->isoFormat('ddd D MMMM Y') ?>";
        });

        Blade::directive('time', function ($expr) {
            return "<?= substr({$expr}, -9, -3) ?>";
        });

        Blade::directive('description', function ($expr) {
            return <<<EOF
                <?php
                foreach(explode("\n\n", {$expr}) as \$paragraph) {
                    echo '<p class="card-text">' . nl2br(e(\$paragraph), false) . '</p>';
                }
                ?>
                EOF;
        });

        setlocale(LC_TIME, config('app.locale'));

        Course::observe(CourseObserver::class);
        User::observe(UserObserver::class);

        Paginator::useBootstrapFive();

        if (! $this->app->isProduction()) {
            Model::preventAccessingMissingAttributes();
            Model::preventSilentlyDiscardingAttributes();
        }

        // Disable query events to save performance
        Model::unsetEventDispatcher();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(SettingsProvider::class, SettingsProviderImpl::class);
        $this->app->singleton(ApplicationSettings::class);
        $this->app->singleton(AdminNotifier::class);
        $this->app['auth']->provider('custom', function ($config) {
            return new CustomUserProvider($this->app['hash'], User::class);
        });
    }
}
