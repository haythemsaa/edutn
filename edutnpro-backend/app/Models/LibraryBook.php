<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class LibraryBook extends Model
{
    protected $fillable = [
    'school_id',
    'isbn',
    'title',
    'title_ar',
    'author',
    'author_ar',
    'publisher',
    'publication_year',
    'category',
    'language',
    'total_copies',
    'available_copies',
    'qr_code',
    'cover_image',
    'description',
    'status'
];

    protected $casts = [
    'publication_year' => 'integer',
    'total_copies' => 'integer',
    'available_copies' => 'integer'
];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function loans()
    {
        return $this->hasMany(LibraryLoan::class, 'book_id');
    }
}
