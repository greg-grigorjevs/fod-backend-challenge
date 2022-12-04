<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Genre;
use App\Models\Movie;
use Tests\TestCase;

class MovieAPITest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function store_test()
    {
        $genre = Genre::create(['name' => 'Action']);
        $params = [
            'name' => 'Star Wars',
            'year' => 1987,
            'description' => "In a far away galaxy.."
        ];

        $response = $this->post('api/movies', array_merge($params, ['genre_ids' => array($genre->id)]));
        $response->assertCreated();

        $this->assertDatabaseHas('movies', $params);
    }

    /** @test */
    public function validation_fails_on_incorrect_inputs()
    {
        // fails on non-existing genre_id
        $this->post('api/movies', [
            'name' => 'Star Wars',
            'year' => 1987,
            'description' => "In a far away galaxy..",
            'genre_ids' => [1],
        ])->assertInvalid();

        // fails on incorrect year input
        $this->post('api/movies', [
            'name' => 'Star Wars',
            'year' => 2069,
            'description' => "In a far away galaxy..",
        ])->assertInvalid();

        // fails when name is missing
        $this->post('api/movies', [
            'year' => 1987,
            'description' => "In a far away galaxy..",
        ])->assertInvalid();
        
        // fails when year is missing
        $this->post('api/movies', [
            'name' => 'Star Wars',
            'description' => "In a far away galaxy..",
        ])->assertInvalid();
        
        // fails when description is missing
        $this->post('api/movies', [
            'name' => 'Star Wars',
            'year' => 2069,
        ])->assertInvalid();
    }

    /** @test */
    public function index_test()
    {
        $genres = Genre::factory()->count(2)->create();
        $movies = Movie::factory()->count(2)->create();

        $movies->get(0)->genres()->attach($genres->get(0)->id);
        $movies->get(1)->genres()->attach($genres->get(1)->id);

        $response = $this->get('api/movies');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'year',
                        'description',
                        'genres' => [
                            '*' => [
                                'id',
                                'name',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function show_test()
    {
        $movie = Movie::factory()->create(['name' => 'Star Wars']);
        $genre = Genre::factory()->create();

        $movie->genres()->attach($genre->id);

        $this->get("api/movies/$movie->id")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'year',
                    'description',
                    'genres' => [
                        '*' => [
                            'id',
                            'name'
                        ]
                    ],
                ],
            ])
            ->assertJsonFragment([
                'id' => $movie->id,
                'year' => $movie->year,
                'description' => $movie->description,
                'genres' => [
                    [
                        'id' => $genre->id,
                        'name' => $genre->name,
                    ]
                ],
            ]);

        // test fetching imdbRatring from OMDB api
        $this->get("api/movies/$movie->id?fetchImdbRating=1")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'year',
                    'description',
                    'genres' => [
                        '*' => [
                            'id',
                            'name'
                        ]
                    ],
                    'imdbRating'
                ],
            ]);
    }

    /** @test */
    public function search_test()
    {
        $starWarsMovie = Movie::factory()->create(['name' => 'Star Wars', 'year' => 1997]);
        $theShiningMovie = Movie::factory()->create(['name' => 'The Shining', 'year' => 1986]);

        $actionGenre = Genre::create(['name' => 'Action']);
        $adventureGenre = Genre::create(['name' => 'Adventure']);
        $horrorGenre = Genre::create(['name' => 'Horror']);

        $starWarsMovie->genres()->attach([$actionGenre->id, $adventureGenre->id]);
        $theShiningMovie->genres()->attach([$horrorGenre->id]);

        //search by name
        $this->json('GET', 'api/movies', ['name' => 'star'])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => $starWarsMovie->name])
            ->assertJsonMissing(['name' => $theShiningMovie->name]);

        //search by year
        $this->json('GET', 'api/movies', ['year' => $starWarsMovie->year])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['year' => $starWarsMovie->year])
            ->assertJsonMissing(['year' => $theShiningMovie->year]);

        //search by genre
        $this->json('GET', 'api/movies', ['genre' => $actionGenre->name])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => $starWarsMovie->name])
            ->assertJsonMissing(['name' => $theShiningMovie->name]);

        //search by a single genre_id
        $this->json('GET', 'api/movies', ['genre_ids' => [$actionGenre->id]])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => $starWarsMovie->name])
            ->assertJsonMissing(['name' => $theShiningMovie->name]);

        //search by multiple genre_ids
        $this->json('GET', 'api/movies', ['genre_ids' => [$actionGenre->id, $adventureGenre->id]])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => $starWarsMovie->name])
            ->assertJsonMissing(['name' => $theShiningMovie->name]);
    }

    /** @test */
    public function delete_test()
    {
        $movie = Movie::factory()->create();

        $this->assertDatabaseHas('movies', $movie->getAttributes());

        $this->delete("api/movies/$movie->id")->assertOk();

        $this->assertDatabaseMissing('movies', $movie->getAttributes());
    }
}
