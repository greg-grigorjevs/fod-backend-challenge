<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Http;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $movies = Movie::search($request->all())
            ->with('genres')
            ->orderBy('name')
            ->paginate(10);

        return MovieResource::collection($movies);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    /* public function create() */
    /* { */
    /*     // */
    /* } */

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreMovieRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMovieRequest $request)
    {
        $movie = new Movie($request->all());
        $movie->save();

        if (isset($request->genre_ids)) {
            $movie->genres()->attach($request->genre_ids);
        }

        return new MovieResource($movie->load('genres'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Movie  $movie
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Movie $movie)
    {
        if ($request['fetchImdbRating'] == true) {
            $movie->fetchImdbRating();
        }

        return new MovieResource($movie->load('genres'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Movie  $movie
     * @return \Illuminate\Http\Response
     */
    /* public function edit(Movie $movie) */
    /* { */
    /*     // */
    /* } */

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMovieRequest  $request
     * @param  \App\Models\Movie  $movie
     * @return \Illuminate\Http\Response
     */
    /* public function update(UpdateMovieRequest $request, Movie $movie) */
    /* { */
    /*     // */
    /* } */

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Movie  $movie
     * @return \Illuminate\Http\Response
     */
    public function destroy(Movie $movie)
    {
        $movie->genres()->detach();
        $movie->delete();
        return new MovieResource($movie);
    }
}
