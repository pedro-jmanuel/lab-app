<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Imports\SchoolImport;
use App\Models\ExcelFile;
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
     *     path="/api/schools",
     *     tags={"Schools"},
     *     summary="Get list of schools",
     *     description="Returns a list of schools",
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index()
    {
        $schools = School::all();
        return ResponseHelper::success($schools, ["message" => "Listagem de todas as escolas"], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $rules = [
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:schools',
            'classrooms' => 'required|integer',
            'province'   => 'required|json',
        ];

        $messages = [
            'name.required'       => 'Nome é obrigatorio',
            'email.required'      => 'E-mail é obrigatorio',
            'email.unique'        => 'E-mail já foi usado',
            'classrooms.required' => 'Nº Sala é obrigatorio',
            'province.required'   => 'Provincia é obrigatorio',
            'province.json'       => 'Provincia deve ser um JSON',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return ResponseHelper::error("Erro no preechimento do formulário", $validator->errors()->messages(), 422);
        }

        $school = School::create($request->all());


        return ResponseHelper::success($school, ["message" => "Escola criada com sucesso"], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $school = School::find($id);

        if ($school == null) {
            return ResponseHelper::error("Escola não encontrada.", [], 404);
        } else {
            return ResponseHelper::success($school, ["message" => "Escola encotrada com sucesso"], 200);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $school = School::find($id);

        $rules = [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:schools',
            'classrooms'=> 'required|integer',
            'province'  => 'required|json',
        ];

        $messages = [
            'name.required'       => 'Nome é obrigatorio',
            'email.required'      => 'E-mail é obrigatorio',
            'email.unique'        => 'E-mail já foi usado',
            'classrooms.required' => 'Nº Sala é obrigatorio',
            'province.required'   => 'Provincia é obrigatorio',
            'province.json'       => 'Provincia deve ser um JSON',
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
     * Remove the specified resource from storage.
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
