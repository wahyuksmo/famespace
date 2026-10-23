<?php

use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Auth\Controllers\RegisterController;
use App\Modules\Calendar\Controllers\EventController;
use App\Modules\Chore\Controllers\ChoreController;
use App\Modules\Dashboard\Controllers\DashboardController;
use App\Modules\Family\Controllers\MemberController;
use App\Modules\Finance\Controllers\FinanceController;
use App\Modules\Subscription\Controllers\SubscriptionController;
use App\Modules\Timeline\Controllers\TimelineController;
use Illuminate\Support\Facades\Route;

// Public Landing Page & PWA Offline Fallback
Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/offline', function () {
    return view('errors.offline');
})->name('offline');

// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/demo-login', [AuthController::class, 'demoLogin'])->name('demo.login');

    Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/join/{code}', [RegisterController::class, 'showJoin'])->name('join.show');
    Route::post('/join/{code}', [RegisterController::class, 'processJoin'])->name('join.process');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Members & Roles
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::patch('/members/{id}/toggle-finance', [MemberController::class, 'toggleFinanceAccess'])->name('members.toggle_finance');

    // Chores (Tugas)
    Route::get('/chores', [ChoreController::class, 'index'])->name('chores.index');
    Route::get('/chores/paginate/{page?}', [ChoreController::class, 'paginate'])->name('chores.paginate');
    Route::post('/chores', [ChoreController::class, 'store'])->name('chores.store');
    Route::put('/chores/{id}', [ChoreController::class, 'update'])->name('chores.update');
    Route::delete('/chores/{id}', [ChoreController::class, 'destroy'])->name('chores.destroy');
    Route::patch('/chores/{id}/toggle', [ChoreController::class, 'toggle'])->name('chores.toggle');

    // Finance (Kas)
    Route::get('/finances', [FinanceController::class, 'index'])->name('finances.index');
    Route::get('/finances/paginate/{page?}', [FinanceController::class, 'paginate'])->name('finances.paginate');
    Route::post('/finances', [FinanceController::class, 'store'])->name('finances.store');
    Route::put('/finances/{id}', [FinanceController::class, 'update'])->name('finances.update');
    Route::delete('/finances/{id}', [FinanceController::class, 'destroy'])->name('finances.destroy');

    // Finance Categories & Budgets
    Route::get('/finances/budgets/paginate', [FinanceController::class, 'paginateBudgets'])->name('finances.budgets.paginate');
    Route::get('/finances/categories/paginate', [FinanceController::class, 'paginateCategories'])->name('finances.categories.paginate');
    Route::post('/finances/categories', [FinanceController::class, 'storeCategory'])->name('finances.categories.store');
    Route::put('/finances/categories/{id}', [FinanceController::class, 'updateCategory'])->name('finances.categories.update');
    Route::delete('/finances/categories/{id}', [FinanceController::class, 'destroyCategory'])->name('finances.categories.destroy');
    Route::post('/finances/period-budgets', [FinanceController::class, 'storePeriodBudget'])->name('finances.period_budgets.store');

    // Finance Goals (Celengan)
    Route::post('/finances/goals', [FinanceController::class, 'storeGoal'])->name('finances.goals.store');
    Route::put('/finances/goals/{id}', [FinanceController::class, 'updateGoal'])->name('finances.goals.update');
    Route::delete('/finances/goals/{id}', [FinanceController::class, 'destroyGoal'])->name('finances.goals.destroy');
    Route::post('/finances/goals/{id}/deposit', [FinanceController::class, 'depositGoal'])->name('finances.goals.deposit');

    // Subscription & Pro Tiering
    Route::post('/subscriptions/invoice', [SubscriptionController::class, 'createInvoice'])->name('subscription.invoice');
    Route::post('/subscriptions/simulate-pay/{invoice}', [SubscriptionController::class, 'simulatePay'])->name('subscription.simulate_pay');
    Route::post('/subscriptions/dev-toggle', [SubscriptionController::class, 'toggleDev'])->name('subscription.dev_toggle');

    // Pro Feature Action Handlers (Protected by 'pro' middleware)
    Route::middleware('pro')->group(function () {
        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        Route::get('/events/paginate/{page?}', [EventController::class, 'paginate'])->name('events.paginate');
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
        Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('events.destroy');

        Route::get('/timeline', [TimelineController::class, 'index'])->name('timeline.index');
        Route::get('/timeline/map-points', [TimelineController::class, 'mapPoints'])->name('timeline.map-points');
        Route::get('/timeline/paginate/{page?}', [TimelineController::class, 'paginate'])->name('timeline.paginate');
        Route::post('/timeline', [TimelineController::class, 'store'])->name('timeline.store');
        Route::put('/timeline/{id}', [TimelineController::class, 'update'])->name('timeline.update');
        Route::delete('/timeline/{id}', [TimelineController::class, 'destroy'])->name('timeline.destroy');
    });
});
