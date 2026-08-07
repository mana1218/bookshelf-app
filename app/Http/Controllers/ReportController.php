<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $totalReviews = auth()->user()
            ->reviews()
            ->count();

        $booksRead = auth()->user()
            ->readingPlans()
            ->where('status', 'completed')
            ->distinct('book_id')
            ->count('book_id');

        $averageRating = auth()->user()
            ->reviews()
            ->avg('rating');

        $ratingDistribution = auth()->user()
            ->reviews()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating');

        $topRatedBooks = auth()->user()
            ->reviews()
            ->with('book')
            ->where('rating', '>=', 4)
            ->orderByDesc('rating')
            ->take(5)
            ->get();
        
        $genreRatings = Genre::select('genres.id','genres.name')
            ->join('book_genre', 'genres.id', '=', 'book_genre.genre_id')
            ->join('books', 'books.id', '=', 'book_genre.book_id')
            ->join('reviews', 'reviews.book_id', '=', 'books.id')
            ->where('reviews.user_id', auth()->id())
            ->selectRaw('AVG(reviews.rating) as average_rating')
            ->selectRaw('COUNT(reviews.id) as review_count')
            ->groupBy('genres.id', 'genres.name')
            ->orderByDesc('average_rating')
            ->take(5)
            ->get();

        $stats = [
            'summary' => [
                'total_reviews' => $totalReviews,
                'books_read' => $booksRead,
                'average_rating' => $averageRating,
            ],
        
            'rating_distribution' => collect(range(1, 5))->map(function ($rating) use ($ratingDistribution) {
                return $ratingDistribution[$rating] ?? 0;
            }),
        
            'top_rated_books' => $topRatedBooks->map(function ($review) {
                return [
                    'id' => $review->book->id,
                    'title' => $review->book->title,
                    'author' => $review->book->author,
                    'rating' => $review->rating,
                ];
            }),
        
            'genre_ratings' => $genreRatings->map(function ($genre) {
                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'average_rating' => $genre->average_rating,
                    'count' => $genre->review_count,
                ];
            }),
        ];
        
        return view('reports.index', compact('stats'));
    }
}
