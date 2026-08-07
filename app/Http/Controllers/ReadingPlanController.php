<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    public function index(Request $request): View
    {
        $readingPlans = auth()->user()->readingPlans();

        $currentStatus = $request->status;
    
        if ($request->filled('status')) {
            $readingPlans->where('status', $currentStatus);
        }
    
        $readingPlans = $readingPlans
            ->with('book')
            ->orderBy('due_date')
            ->get();
    
        return view('reading-plans.index', compact(
            'readingPlans',
            'currentStatus'
        ));
    }

    public function create(): View
    {
        $books = Book::orderBy('title')->get();

        return view('reading-plans.create', compact('books'));
    }

    public function store(StoreReadingPlanRequest $request): RedirectResponse
    {
        ReadingPlan::create([
            'user_id' => auth()->id(),
            'book_id' => $request->book_id,
            'due_date' => $request->due_date,
            'status' => ReadingPlanStatus::Pending
        ]);

        return redirect()->route('reading-plans.index');
    }

    public function edit(ReadingPlan $readingPlan): View
    {
        $this->authorize('update', $readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    public function update(UpdateReadingPlanRequest $request, ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'due_date' => $request->due_date,
        ]);

        return redirect()->route('reading-plans.index', $readingPlan);
    }

    public function destroy(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('delete', $readingPlan);
        
        $readingPlan->delete();

        return redirect()->route('reading-plans.index', $readingPlan);
    }

    public function complete(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
        ]);

        return redirect()->route('reading-plans.index');
    }
}
