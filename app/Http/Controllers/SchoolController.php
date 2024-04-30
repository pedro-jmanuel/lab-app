<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Imports\SchoolImport;
use App\Models\ExcelFile;
use App\Models\Province;
use Illuminate\Support\Facades\Http;
use Excel;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="LAB APP - API Documentation",
 *      description="API para gestão de escolas.",
 *      @OA\Contact(
 *          email="pedro.manuel.dev@gmail.com"
 *      ),
 *      @OA\License(
 *          name="License Name",
 *          url="http://url-to-license.com"
 *      )
 * )
 */

class SchoolController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/school",
     *     summary="Obter uma lista com todas as escolas.",
     *     operationId="getAllSchools",
     *     tags={"Schools"},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Uma mensagem de sucesso", example="Listagem de todas as escolas"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 description="Um array com todas as escolas encontradas",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", description="ID da escola"),
     *                     @OA\Property(property="name", type="string", description="Nome da escola"),
     *                     @OA\Property(property="email", type="string", description="E-mail da escola"),
     *                     @OA\Property(property="classrooms", type="integer", description="Número de salas da escola"),
     *                     @OA\Property(property="province_id", type="integer", description="ID da provincia"),
     *                     @OA\Property(
        *                      property="province", 
        *                      type="object", 
        *                      description="Provincia onde esta localizada a escola.",
        *                      @OA\Property(property="id", type="integer", description="ID da provincia"),
        *                      @OA\Property(property="name", type="string", description="Nome da provincia"),
        *                      @OA\Property(property="description", type="string", description="Descrição da provincia"),
     *                      ),
     *                 ),
     *             ),
     *         ),
     *     ),
     * )
     */
    public function index()
    {
        $schools = School::with('province')->get();
        return ResponseHelper::success($schools, ["message" => "Listagem de todas as escolas"], 200);
    }


   /**
     * @OA\Post(
     *     path="/api/school",
     *     summary="Criar uma nova escola",
     *     operationId="storeSchool",
     *     tags={"Schools"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "classrooms", "province"},
     *             @OA\Property(property="name", type="string", description="Nome da escola (Max. 255 characteres)"),
     *             @OA\Property(property="email", type="string", description="E-mail da escola"),
     *             @OA\Property(property="classrooms", type="integer", description="Numero de salas da escola"),
     *             @OA\Property(
     *                 property="province_id",
     *                 type="integer",
     *                 description="ID da provincia onde a escola esta localizada ",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro no formulário",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Mensagem de erros", example="Erro no preechimento do formulário"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Uma lista detalhada dos erros encontrado no formulário.",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Uma mensagem de sucesso", example="Escola criada com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Retorna a escola criada",
     *                 @OA\Property(property="id", type="integer", description="ID da escola"),
     *                 @OA\Property(property="name", type="string", description="Nome da escola"),
     *                 @OA\Property(property="email", type="string", description="O E-mail da escola"),
     *                 @OA\Property(property="classrooms", type="integer", description="Número de salas da escola"),
     *                
     *             ),
     *         ),
     *     ),
     * )
     */
    public function store(Request $request)
    {

        $rules = [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:schools',
            'classrooms'    => 'required|integer',
            'province_id'   => 'required|integer',
        ];

        $messages = [
            'name.required'       => 'Nome é obrigatorio',
            'email.required'      => 'E-mail é obrigatorio',
            'email.unique'        => 'E-mail já foi usado',
            'classrooms.required' => 'Nº Sala é obrigatorio',
            'province.required'   => 'Provincia é obrigatorio',
            'province.integer'    => 'Provincia deve ser um inteiro',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return ResponseHelper::error("Erro no preechimento do formulário", $validator->errors()->messages(), 422);
        }

        $school = School::create($request->all());


        return ResponseHelper::success($school, ["message" => "Escola criada com sucesso"], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/school/{id}",
     *     summary="Obter uma escola pelo seu ID.",
     *     operationId="getSchoolById",
     *     tags={"Schools"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="ID da escola que se pretente obter."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Não encontrada.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Uma mensagem de erro", example="Escola não encontrada."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Uma mensagem de sucesso.", example="Escola encontrada com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Retorna a escola encotrada.",
     *                 @OA\Property(property="id", type="integer", description="ID da escola"),
     *                 @OA\Property(property="name", type="string", description="Nome da escola"),
     *                 @OA\Property(property="email", type="string", description="E-mail da escola"),
     *                 @OA\Property(property="classrooms", type="integer", description="Número da sala da escola"),
     *                 @OA\Property(property="province_id", type="object", description="ID da provincia"),
     *                 @OA\Property(
        *                      property="province", 
        *                      type="object", 
        *                      description="Provincia onde esta localizada a escola.",
        *                      @OA\Property(property="id", type="integer", description="ID da provincia"),
        *                      @OA\Property(property="name", type="string", description="Nome da provincia"),
        *                      @OA\Property(property="description", type="string", description="Descrição da provincia"),
     *                 ),
     *             ),
     *         ),
     *     ),
     * )
     */
    public function show(string $id)
    {
        $school = School::with('province')->find($id);

        if ($school == null) {
            return ResponseHelper::error("Escola não encontrada.", [], 404);
        } else {
            return ResponseHelper::success($school, ["message" => "Escola encontrada com sucesso"], 200);
        }
    }


     /**
     * @OA\Patch(
     *     path="/api/school/{id}",
     *     summary="Atualiza os dados de uma escola usando o seu ID",
     *     operationId="updateSchoolById",
     *     tags={"Schools"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="ID da escola a ser atualizada"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Nome da escola"),
     *             @OA\Property(property="email", type="string", description="E-mail da escola"),
     *             @OA\Property(property="classrooms", type="integer", description="Número de salas da escola"),
     *             @OA\Property(
     *                 property="province_id",
     *                 type="integer",
     *                 description="ID da Provincia onde esta localizada.",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Uma mensagem de sucesso", example="Escola não encontrada."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Uma mensagem de erro", example="Erro no preechimento do formulário"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Uma lista detalhadas dos erros encontrado no formúlario.",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Uma mensagem de sucesso", example="Escola atualizada com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Retorna a escola atualizada",
     *                 @OA\Property(property="id", type="integer", description="ID da escola"),
     *                 @OA\Property(property="name", type="string", description="Nome da escola"),
     *                 @OA\Property(property="email", type="string", description="E-mail da escola"),
     *                 @OA\Property(property="classrooms", type="integer", description="Número de salas da escolas"),
     *                 @OA\Property(property="province", type="integer", description="ID da Provincia onde esta localizada. (Deve ser um JSON)"),
     *             ),
     *         ),
     *     ),
     * )
     */
    public function update(Request $request, string $id)
    {
        $school = School::find($id);

        $rules = [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email',
            'classrooms'    => 'required|integer',
            'province_id'   => 'required|integer',
        ];

        $messages = [
            'name.required'       => 'Nome é obrigatorio',
            'email.required'      => 'E-mail é obrigatorio',
            'email.unique'        => 'E-mail já foi usado',
            'classrooms.required' => 'Nº Sala é obrigatorio',
            'province.required'   => 'Provincia é obrigatorio',
            'province.integer'    => 'Provincia deve ser um inteiro',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return ResponseHelper::error("Erro no preechimento do formulário", $validator->errors()->messages(), 422);
        }

        if ($school == null) {
            return ResponseHelper::error("Escola não encontrada.", [], 404);
        } else {
            $school->update($request->all());
            return ResponseHelper::success($school, ["message" => "Escola atualizada com sucesso"], 200);
        }
    }

   /**
     * @OA\Delete(
     *     path="/api/school/{id}",
     *     summary="Remover uma escola pelo seu ID",
     *     operationId="deleteSchoolById",
     *     tags={"Schools"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="ID da escola que se pretende remover"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Uma mensagem de erro", example="Escola não encontrada."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Uma mensagem de sucesso", example="Escola excluída com sucesso"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Retorna os dados da escola removida",
     *                 @OA\Property(property="id", type="integer", description="ID da escola removida"),
     *                 @OA\Property(property="name", type="string", description="Nome da escola removida"),
     *                 @OA\Property(property="email", type="string", description="E-mail da escola removida"),
     *                 @OA\Property(property="classrooms", type="integer", description="Número de salas da escola removida"),
     *                 @OA\Property(property="province_id", type="integer", description="ID Provincia da escola removida"),
     *             ),
     *         ),
     *     ),
     * )
     */
    public function destroy(string $id)
    {
        $school = School::find($id);

        if ($school == null) {
            return ResponseHelper::error("Escola não encontrada.", [], 404);
        } else {
            $school_removed = $school;
            $school->delete();
            return ResponseHelper::success($school_removed, ["message" => "Escola excluída com sucesso"], 200);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/school-excel",
     *     summary="Importar dados das escolas apartir de um arquivo excel",
     *     operationId="importSchoolsFromExcel",
     *     tags={"Schools"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"excel"},
     *                 @OA\Property(
     *                     property="excel",
     *                     type="file",
     *                     description="Um arquivo excel com dados das ecolas  (Deve ser um arquivo .xlsx)",
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro no formúlario",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Uma mensagem de erro"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Uma lista detalhada dos erros encontrados no formulário",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Uma mensagem de sucesso", example="Escolas importadas com sucesso"),
     *         ),
     *     ),
     * )
     */
    public function store_excel(Request $request)
    {
        if ($request->file('excel')) {
            if (!$request->hasFile('excel') || !$request->file('excel')->isValid()) {
                $errorCode = $request->file('excel')->getError();

                switch ($errorCode) {
                    case UPLOAD_ERR_INI_SIZE:
                        $message = "O tamanho do excel excedeu o limite do servidor";
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $message = "O tamanho do excel excede o limite definido no formulário HTML";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $message = "O upload do excel foi interrompido";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $message = "Nenhum excel foi enviado";
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $message = "Pasta temporária ausente";
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $message = "Falha ao escrever o excel no disco";
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $message = "Extensão de excel não permitida";
                        break;
                    default:
                        $message = "Falha ao enviar o excel";
                }

                return ResponseHelper::error($message, [], 422);
            }
        } else {
            return ResponseHelper::error("Não foi submetido nenhum arquivo excel, Por Favor selecione um arquivo excel", [], 422);
        }




        // Validação do tipo de arquivo (deve ser .xlsx)
        $mimeType = $request->file('excel')->getMimeType();
        if ($mimeType !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            return ResponseHelper::error("O arquivo enviado não é um Excel válido (extensão .xlsx)", [], 422);
        }

        $fileName = uniqid('school_') . '.xlsx';
        $request->file('excel')->move(public_path('excel'), $fileName);


        try {

            Excel::import(new SchoolImport, public_path('excel/' . $fileName));

            ExcelFile::create(['path' => 'excel/' . $fileName]);

            return ResponseHelper::success([], ["message" => "Escolas importadas com sucesso"], 200);
        } catch (Exception $e) {
            // Limpar arquivo caso haja erro (opcional)
            \Storage::delete('excel/' . $fileName);

            return ResponseHelper::error("Falha ao processar o arquivo Excel: " . $e->getMessage(), [], 422);
        }
    }

   
}

