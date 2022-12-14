<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreMovieRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $thisYear = Carbon::now()->year;
        return [
            'name' => 'required|string|max:255',
            'year' => "required|integer|between:1900,{$thisYear}",
            'description' => 'required|string',
            'genre_ids' => 'sometimes|required|array|exists:App\Models\Genre,id',
        ];
    }
}
