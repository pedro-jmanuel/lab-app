<?php

namespace App\Http\Controllers;

use App\Models\Province;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Http;

class ProvinceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/province",
     *     summary="Obter uma lista das provincias disponiveis.",
     *     operationId="getAllProvinces",
     *     tags={"Provinces"},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="A success message", example="Listagem de todas as provincias"),
     *              @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 description="Um array com as provincias",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", description="ID da provincia"),
     *                     @OA\Property(property="name", type="string", description="Nome da provincia"),
     *                     @OA\Property(property="description", type="string", description="Descrição da provincias"),
     *                 ),
     *             ),
     *         ),
     *     ),
     * )
     */
    public function index()
    {
        $provinces = Province::all();
        return ResponseHelper::success($provinces, ["message" => "Listagem de todas as provincias"], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/receive-provinces",
     *     summary="Faz a requisição e salva as provincias usando uma API externa.",
     *     operationId="fetchAndSaveProvinces",
     *     tags={"Provinces"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="endpoint", type="string", description="URL do endpoint que retorna os dados das provincias."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro na requisição do endpoint",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Uma mensagem de erro"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Uma mensagem de sucesso", example="Provincias importadas com sucesso"),
     *         ),
     *     ),
     * )
     */
    public function fetchAndSaveProvinces(Request $request)
    {
        $endpoint= $request->endpoint;

        $response = Http::get($endpoint);

        if ($response->ok()) {

            $provincesData = json_decode($response->getBody()->getContents(), true);

            foreach ($provincesData as $provinceData) {
                if(!Province::where("id",$provinceData['id'])->exists()){
                     $province = Province::create([
                        'id'          => $provinceData['id'],
                        'name'        => $provinceData['descricao'],
                        'description' => $provinceData['descricao'],
                    ]);
                }
            }

            return ResponseHelper::success([], ["message" => "Provincias importadas com sucesso"], 200);
        } else {
            return ResponseHelper::error("Falha ao consumir provincias .", [], 422);
        }
    }

  

}
