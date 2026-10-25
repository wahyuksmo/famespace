<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Throwable;

class AdminPlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('subscriptions')->get();

        return view('modules.admin.plans.index', compact('plans'));
    }

    public function update(Request $request, int $id)
    {
        $plan = Plan::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            $plan->update([
                'name' => $data['name'],
                'price' => $data['price'],
                'duration_days' => $data['duration_days'],
                'is_active' => $request->boolean('is_active'),
            ]);

            return back()->with('success', "Paket {$plan->name} berhasil diperbarui!");
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
