<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Erro de dominio da API com status HTTP explicito.
 * Espelha o HttpError do projeto de referencia: o handler global o
 * converte em JSON { "error": <mensagem> } com o status informado.
 */
class ApiException extends RuntimeException
{
    public function __construct(public int $status, string $message)
    {
        parent::__construct($message);
    }
}
