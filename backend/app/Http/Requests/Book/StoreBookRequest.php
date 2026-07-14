<?php

namespace App\Http\Requests\Book;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'numero_paginas' => ['required', 'integer', 'min:1'],
            'indices' => ['present', 'array'],
        ];
    }

    /**
     * Valida a arvore de indices recursivamente (profundidade ilimitada),
     * o que nao e expressavel com wildcards estaticos do Laravel.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $this->validateNodes($v, $this->input('indices', []), 'indices');
        });
    }

    /**
     * @param  array<int, mixed>  $nodes
     */
    protected function validateNodes(Validator $v, mixed $nodes, string $path): void
    {
        if (! is_array($nodes)) {
            $v->errors()->add($path, 'Deve ser uma lista de indices.');

            return;
        }

        foreach ($nodes as $i => $node) {
            $base = "{$path}.{$i}";

            if (! is_array($node)) {
                $v->errors()->add($base, 'Indice invalido.');

                continue;
            }

            if (! isset($node['titulo']) || ! is_string($node['titulo']) || trim($node['titulo']) === '') {
                $v->errors()->add("{$base}.titulo", 'Titulo do indice e obrigatorio.');
            }

            if (! isset($node['pagina']) || ! is_int($node['pagina']) || $node['pagina'] < 1) {
                $v->errors()->add("{$base}.pagina", 'Pagina do indice deve ser inteiro positivo.');
            }

            $sub = $node['subindices'] ?? [];
            if ($sub !== []) {
                $this->validateNodes($v, $sub, "{$base}.subindices");
            }
        }
    }
}
