<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\TypeDocument;
use Illuminate\Http\Request;
use Spatie\FlareClient\Http\Exceptions\BadResponse;

class TypeDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $typedocument = TypeDocument::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->with('documents')
            ->orderBy('name','asc')
            ->paginate();

            return $typedocument;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->all();
        $typedocument = TypeDocument::create($data);
        return [
            'message'=>'success'
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $typedocument = TypeDocument::find($id);


        return ['typedocument'=>$typedocument];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $typedocument = TypeDocument::find($id);
        


        return ['typedocument'=>$typedocument];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();


        $typedocument = TypeDocument::find($id);

        $typedocument->update($data);

        return $typedocument;

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $typedocument = TypeDocument::find($id);

        $documents = Document::where('type_document_id',$id)->get();

        if($documents->count()>0){
            return response()->json(['message'=>'Erro'],404);
        }

        $typedocument->delete();

        return true;
    }
}
