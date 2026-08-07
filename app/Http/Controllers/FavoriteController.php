<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(): View
    {
        $books = auth()->user()->favoriteBooks()->oldest()->paginate(10);

        return view('favorites.index', compact('books'));
    }

    public function toggle(Book $book)
    {
        auth()->user()->favoriteBooks()->toggle($book->id);

        return back();
    }
}
