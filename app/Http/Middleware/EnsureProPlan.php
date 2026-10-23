<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProPlan
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->family) {
            return redirect()->route('login');
        }

        $subscription = $user->family->activeSubscription;
        $isPro = $subscription && $subscription->plan && $subscription->plan->isPro() && $subscription->expires_at->isFuture();

        if (! $isPro) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Fitur ini membutuhkan paket Famespace Pro.',
                    'upgrade_url' => route('subscription.invoice'),
                ], 403);
            }

            return redirect()->route('dashboard', ['tab' => 'overview', 'paywall' => '1'])
                ->with('warning', 'Fitur ini membutuhkan paket Famespace Pro (Rp 20.000/bln).');
        }

        return $next($request);
    }
}
