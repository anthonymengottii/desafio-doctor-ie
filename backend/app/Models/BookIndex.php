<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class BookIndex extends Model
{
    /** @use HasFactory<\Database\Factories\BookIndexFactory> */
    use HasFactory;

    // Fornece ancestors()/descendants() via recursive CTE (coluna pai = parent_id).
    use HasRecursiveRelationships;

    protected $table = 'book_indices';

    protected $fillable = [
        'book_id',
        'parent_id',
        'titulo',
        'pagina',
        'position',
    ];

    protected $casts = [
        'pagina' => 'integer',
        'position' => 'integer',
    ];

    /**
     * Livro dono deste indice.
     *
     * @return BelongsTo<Book, $this>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    // parent(), children(), ancestors() e descendants() vem do trait
    // HasRecursiveRelationships (coluna pai padrao = parent_id).
}
