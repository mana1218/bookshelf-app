<?php

namespace App\Http\Controllers;

use App\Enums\PlanStatus;
use App\Models\Book;
use App\Models\Plan;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(Request $request): View
    {
        $plans = auth()->user()->plans();

        $currentStatus = $request->status;
    
        if ($currentStatus === 'overdue') {
            $plans
                ->whereDate('target_date', '<', today())
                ->where('status', '!=', PlanStatus::Completed);
        } elseif ($request->filled('status')) {
            $plans->where('status', $currentStatus);
        }

        $plans = $plans
            ->with('book')
            ->orderBy('target_date')
            ->get();
    
        return view('reading-plans.index', compact('plans','currentStatus'));
    }

    public function create(): View
    {
        $books = Book::orderBy('title')->get();

        return view('reading-plans.create', compact('books'));
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

            $exists = Plan::where('user_id', auth()->id())
                ->where('book_id', $validated['book_id'])
                ->where('status', '!=', PlanStatus::Completed)
                ->whereDate('target_date', '>=', today())
                ->exists();
        
            if ($exists) {
                return back()->withErrors([
                    'book_id' => 'この本は現在、読書計画が有効です。',
                ])->withInput();
            }


        Plan::create([
            'user_id' => auth()->id(),
            'book_id' => $validated['book_id'],
            'target_date' => $validated['target_date'],
            'status' => PlanStatus::Reading,
        ]);

        return redirect()->route('reading-plans.index');
    }

    public function edit(Plan $plan): View
    {
        $this->authorize('update', $plan);

        return view('reading-plans.edit', compact('plan'));
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        $validated = $request->validated();

        $plan->update([
            'target_date' => $validated['target_date']
        ]);

        return redirect()->route('reading-plans.index');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $this->authorize('delete', $plan);
        
        $plan->delete();

        return redirect()->route('reading-plans.index');
    }

    public function start(Plan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        $plan->update([
            'status' => PlanStatus::Reading,
        ]);

        return redirect()->route('reading-plans.index');
    }

    public function complete(Plan $plan): RedirectResponse
    {
        $this->authorize('complete', $plan);

        $plan->update([
            'status' => PlanStatus::Completed,
        ]);

        return redirect()->route('reading-plans.index');
    }
}
