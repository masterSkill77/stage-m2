<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FormController extends Controller
{
    public function store(Request $request)
    {
        $form = new Form([
            'form_content' => $request->input('form_content')
        ]);
        $form->save();
        return response()->json($form, Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id)
    {
        $form = Form::findOrFail($id);
        $form->form_content = $request->input('form_content');
        $form->save();
        return response()->json($form, Response::HTTP_OK);
    }
}
