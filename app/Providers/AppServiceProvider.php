<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer(
            ['layouts.main', 'dashboard.partials.*', 'modules.*'],
            function ($view) {
                if (Auth::check()) {
                    $user = Auth::user();
                    $family = $user->family;
                    if ($family) {
                        $subscription = $family->activeSubscription()->with('plan')->first();
                        $isPro = $subscription && $subscription->plan && $subscription->plan->isPro() && $subscription->expires_at->isFuture();
                        $members = $family->users()->orderBy('points', 'desc')->get();
                        $categories = $family->categories()->get();
                        $chores = $family->chores;

                        $view->with([
                            'user' => $user,
                            'family' => $family,
                            'members' => $members,
                            'subscription' => $subscription,
                            'isPro' => $isPro,
                            'categories' => $categories,
                            'chores' => $chores,
                        ]);
                    }
                }
            }
        );
    }
}
