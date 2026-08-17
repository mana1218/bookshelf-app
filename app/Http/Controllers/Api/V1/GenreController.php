<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Http\Resources\Api\V1\BookResource;
use App\Http\Resources\Api\V1\GenreResource;
use App\Http\Requests\Api\V1\StoreGenreRequest;
use App\Http\Requests\Api\V1\UpdateGenreRequest;


class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::withCount('books')
            ->orderBy('name')
            ->get();

        return GenreResource::collection($genres);
    }

    public function store(StoreGenreRequest $request)
    {
        $genre = Genre::create($request->validated());

        return (new GenreResource($genre))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Genre $genre)
    {
        $books = $genre->books()->paginate(10);

        return response()->json([
            'genre' => new GenreResource($genre),
            'books' => BookResource::collection($books),
        ]);
    }

    public function update(UpdateGenreRequest $request, Genre $genre)
    {
        $validated = $request->validated();

        $genre->update($validated);

        return new GenreResource($genre);
    }

    public function destroy(Genre $genre)
    {
        $genre->delete();

        return response()->json(null, 204);
    }
}
