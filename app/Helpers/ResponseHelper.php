<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    /**
     * Gera uma resposta JSON com sucesso.
     *
     * @param mixed $data Dados a serem retornados.
     * @param array $meta Metadados opcionais.
     * @param int $statusCode Código de status HTTP (padrão 200).
     * @return JsonResponse
     */
    
    public static function success($data = null, $meta = [], $statusCode = 200)
    {
        $response = [
            'data' => $data,
            'meta' => $meta,
        ];

        return response()->json($response, $statusCode);
    }

    /**
     * Gera uma resposta JSON com erro.
     *
     * @param string $message Mensagem de erro.
     * @param array $errors Erros específicos (opcional).
     * @param int $statusCode Código de status HTTP (padrão 400).
     * @return JsonResponse
     */
    public static function error($message, $errors = [], $statusCode = 400)
    {
        $response = [
            'errors' => [
                'message' => $message,
                'errors' => $errors,
            ],
        ];

        return response()->json($response, $statusCode);
    }

}
