<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Genre;
use App\Http\Requests\IndexBookRequest;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;

class BookController extends Controller
{
    public function index(IndexBookRequest $request): View
    {
        $query = Book::with('genres')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if($request->filled('genre')) {
            $genre = $request->input('genre');
            $query->whereHas('genres', function ($query) use ($genre) {
                $query->where('genres.id', $genre);
            });
        }

        $sort = $request->input('sort', 'newest');

        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;

            case 'title':
                $query->orderBy('title');
                break;

            case 'rating':
                $query->orderByRaw('reviews_avg_rating IS NULL')
                    ->orderByDesc('reviews_avg_rating');
                break;

            default:
                $query->latest();
                break;
        }

        $books = $query->paginate(10)->withQueryString();

        $genres = Genre::orderBy('name')->get();

        return view('books.index', compact('books', 'genres'));
    }

    public function create(): View
    {
        $genres = Genre::orderBy('name')->get();

        return view('books.create', compact('genres'));
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $book = $request->user()->books()->create([
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'published_date' => $validated['published_date'],
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
        ]);

        $book->genres()->sync($validated['genres'] ?? []);

        return redirect()->route('books.index');
    }

    public function show(Book $book): View
    {
        $book->load(['genres', 'reviews', 'favoriteBooks']);

        return view('books.show', compact('book'));
    }

    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $genres = Genre::orderBy('name')->get();

        return view('books.edit', compact('genres', 'book'));
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        $book->update([
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'published_date' => $validated['published_date'],
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
        ]);

        $book->genres()->sync($validated['genres'] ?? []);

        return redirect()->route('books.index');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);
        
        $book->delete();

        return redirect()->route('books.index');
    }

    public function isbn($isbn)
    {
        if (!preg_match('/^\d{13}$/', $isbn)) {
            return response()->json([
                'message' => 'ISBNは13桁で入力してください。'
            ], 422);
        }

        $response = Http::get(
            'https://www.googleapis.com/books/v1/volumes',
            [
                'q' => 'isbn:' . $isbn,
                'key' => env('GOOGLE_BOOKS_API_KEY'),
            ]
        );

        if (!$response->successful()) {
            return response()->json([
                'message' => '書籍情報の取得に失敗しました。'
            ], 500);
        }

        $data = $response->json();

        if (empty($data['items'])) {
            return response()->json([
                'message' => '書籍情報が見つかりませんでした。'
            ], 404);
        }

        $book = $data['items'][0]['volumeInfo'];

        return response()->json([
            'isbn' => $isbn,
            'title' => $book['title'] ?? null,
            'author' => $book['authors'][0] ?? null,
            'published_date' => $book['publishedDate'] ?? null,
            'description' => $book['description'] ?? null,
            'image_url' => $book['imageLinks']['thumbnail'] ?? null,
        ]);
    }
}
