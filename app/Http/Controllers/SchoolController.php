<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schools = School::all();
        return ResponseHelper::success($schools,["message" => "Listagem de todas as escolas"],200);
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
        ];

        $messages = [
           'name.required'  => 'Nome é obrigatorio',
           'email.required' => 'E-mail é obrigatorio',
           'classrooms'     => 'Nº Sala é obrigatorio',
       ];
        
        $validator = Validator::make($request->all(), $rules,$messages);

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
            return ResponseHelper::error("Escola não encontrada.",[],404);
       } else {
            return ResponseHelper::success($school,["message" => "Escola encotrada com sucesso"],200);
       }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $school = School::find($id);
        
        $rules = [
          'name'       => 'required|string|max:255',
          'email'      => 'required|email|unique:schools',
          'classrooms' => 'required|integer',
        ];
 
       $messages = [
         'name.required'  => 'Nome é obrigatorio',
         'email.required' => 'E-mail é obrigatorio',
         'classrooms'     => 'Nº Sala é obrigatorio',
       ];
      
       $validator = Validator::make($request->all(), $rules,$messages);

       if ($validator->fails()) {
          return ResponseHelper::error("Erro no preechimento do formulário", $validator->errors()->messages(), 422);
       }
       
       if ($school == null) {
            return ResponseHelper::error("Escola não encontrada.",[],404);
       } else {
            $school->update($request->all());
            return ResponseHelper::success($school,["message" => "Escola atualizada com sucesso"],200);
       }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       $school = School::find($id);

       if ($school == null) {
            return ResponseHelper::error("Escola não encontrada.",[],404);
       } else {
            $school_removed = $school;
            $school->delete();
            return ResponseHelper::success($school_removed,["message" => "Escola excluída com sucesso"],200);
       }
    }

}
