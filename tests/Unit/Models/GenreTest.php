<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Movie;
use App\Models\Genre;
use Illuminate\Support\Facades\Schema;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function model_has_correct_columns()
    {
        $this->assertTrue(Schema::hasColumns('genres', ['id', 'name', 'created_at', 'updated_at']));
    }

    /** @test */
    public function genre_movies_relationship_test()
    {
        $genre = Genre::factory()->create();
        $genre->movies()->attach(Movie::factory()->create()->id);

        $this->assertInstanceOf(Movie::class, $genre->movies()->first());
        $this->assertEquals(1, $genre->movies()->count());
    }
}
