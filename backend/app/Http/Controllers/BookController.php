<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Requests\Book\StoreBookRequest;
use App\Http\Requests\Book\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Services\BookService;
use App\Services\SimilarityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
    public function __construct(
        private readonly BookService $books,
        private readonly SimilarityService $similarity,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['titulo', 'titulo_do_indice']);

        return BookResource::collection($this->books->list($filters));
    }

    /** Livros semanticamente similares a um titulo livre. */
    public function similarByText(Request $request): AnonymousResourceCollection
    {
        $titulo = trim((string) $request->query('titulo', ''));

        if ($titulo === '') {
            throw new ApiException(422, 'Informe o parametro titulo');
        }

        return BookResource::collection($this->similarity->similarToText($titulo));
    }

    /** Livros semanticamente similares a um livro existente. */
    public function similar(int $id): AnonymousResourceCollection
    {
        $book = $this->books->getById($id);

        return BookResource::collection($this->similarity->similarTo($book));
    }

    public function show(int $id): BookResource
    {
        return new BookResource($this->books->getById($id));
    }

    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = $this->books->create($request->user()->id, $request->validated());

        return (new BookResource($book))->response()->setStatusCode(201);
    }

    public function update(UpdateBookRequest $request, Book $book): BookResource
    {
        $this->assertOwner($request, $book);

        return new BookResource($this->books->update($book, $request->validated()));
    }

    public function destroy(Request $request, Book $book): JsonResponse
    {
        $this->assertOwner($request, $book);
        $this->books->delete($book);

        return response()->json(null, 204);
    }

    /** So o usuario publicador pode alterar ou remover o livro. */
    private function assertOwner(Request $request, Book $book): void
    {
        if ($book->user_id !== $request->user()->id) {
            throw new ApiException(403, 'Voce nao tem permissao sobre este livro');
        }
    }
}
