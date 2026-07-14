<?php

namespace App\Http\Requests\Book;

/**
 * PUT com semantica de substituicao total: o corpo representa o estado
 * final do livro (titulo, numero_paginas e a arvore completa de indices).
 * Reaproveita as mesmas regras e validacao recursiva do cadastro.
 */
class UpdateBookRequest extends StoreBookRequest
{
}
