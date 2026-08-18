<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Http\Resources\Api\V1\BookResource;
use App\Http\Requests\Api\V1\IndexBookRequest;
use App\Http\Requests\Api\V1\StoreBookRequest;
use App\Http\Requests\Api\V1\UpdateBookRequest;
use Illuminate\Support\Facades\Http;

class BookController extends Controller
{
    public function index(IndexBookRequest $request)
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

        return BookResource::collection($books);
    }

    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();

        $book = Book::create([
            'user_id' => 1,
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'published_date' => $validated['published_date'],
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
        ]);
        
        $book->genres()->sync($validated['genres'] ?? []);

        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Book $book)
    {
        $book->load(['genres', 'reviews.user']);

        return new BookResource($book);
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
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

        return new BookResource($book);
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json(null, 204);
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
