<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Movie;
use App\Models\Genre;
use Illuminate\Support\Facades\Schema;

class MovieTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function model_has_correct_columns()
    {
        $this->assertTrue(Schema::hasColumns('movies', ['id', 'year', 'description', 'created_at', 'updated_at']));
    }

    /** @test */
    public function movie_genres_relationship_test()
    {
        $movie = Movie::factory()->create();
        $movie->genres()->attach(Genre::factory()->create()->id);

        $this->assertInstanceOf(Genre::class, $movie->genres()->first());
        $this->assertEquals(1, $movie->genres()->count());
    }
}
