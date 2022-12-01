<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'year', 'description'];

    protected $table = 'movies';

    /**
     *  The genres that belongs to movie
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function fetchImdbRating(): void
    {
        $apiKey = env('OMDB_API_KEY');

        $response = $apiKey ? Http::get('http://www.omdbapi.com', [
            'apikey' => $apiKey,
            't' => $this->name,
        ]) : null;

        if ($response?->successful() && isset($response['imdbRating'])) {
            $this->imdbRating = $response['imdbRating'];
        } else {
            $this->imdbRating = "N/A";
        }
    }

    public function scopeSearch(Builder $query, array $params = []): Builder
    {
        return $query
            ->when($params['name'] ?? null, function ($query, $name) {
                $query->where('name', 'like', "%${name}%");
            })
            ->when($params['year'] ?? null, function ($query, $year) {
                $query->where('year', $year);
            })
            ->when($params['genre_ids'] ?? null, function ($query, $genre_ids) {
                $query->whereHas('genres', function ($query) use ($genre_ids) {
                    $query->whereIn('genres.id', $genre_ids);
                });
            })
            ->when($params['genre'] ?? null, function ($query, $genre) {
                $query->whereHas('genres', function ($query) use ($genre) {
                    $query->where('genres.name', 'like', "%${genre}%");
                });
            });
    }
}
