<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\ContractType;
use App\Models\Department;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TechnicianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $searchQuery = request('query');

            $technicians = Technician::query()
            ->when(request('query'),function($query,$searchQuery){
                $query->where('name','like',"%{$searchQuery}%");
            })
            ->with('department')
            ->with('area')
            ->with('contract_type')
            ->orderBy('name','asc')
            ->paginate();

            return $technicians;
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
        $data = $this->prepareContractData($data);
        if($request->has('image')){
                $files = $request->file('image');
                // foreach($files as $file){
                    $filename = $files->getClientOriginalName();
                    $extension = $files->getClientOriginalExtension();
                    $imagePath = $files->store('technician-images','s3');
                    $data['image'] =  $imagePath;
                // }
            
        }else{
            $data['image'] = null;
        }

        $technician = Technician::create($data);
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
        $technician = Technician::with('department')->with('area')->with('contract_type')->find($id);

        if($technician->image){

            $technician->image = Storage::disk('s3')->temporaryUrl(
                $technician->image,
                now()->addMinutes(10),
                ['ResponseContentDisposition' => 'attachment']
            );
        }
        

        return [
            'technician'=>$technician,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //

        $technician = Technician::find($id);
        $departments = Department::orderBy('name','asc')->get();
        $areas = Area::orderBy('name','asc')->get();
        $contractTypes = ContractType::active()->orderBy('name','asc')->get();
        
        return [
            'departments'=>$departments,
            'areas'=>$areas,
            'contract_types'=>$contractTypes,
            'technician'=>$technician
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = $request->all();
        $data = $this->prepareContractData($data);


        $technician = Technician::find($id);

        if($request->has('image')){
            $files = $request->file('image');
            // foreach($files as $file){
                $filename = $files->getClientOriginalName();
                $extension = $files->getClientOriginalExtension();
                $imagePath = $files->store('technician-images','s3');
                $data['image'] =  $imagePath;
            // }

        }else{
            $data['image'] = $technician->image;
        }

        $technician->update($data);

        return $technician;
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $technician = Technician::find($id);

        $technician->delete();

        return true;
    }

    private function prepareContractData(array $data): array
    {
        if (isset($data['contract_extra_data']) && is_string($data['contract_extra_data'])) {
            $decoded = json_decode($data['contract_extra_data'], true);
            $data['contract_extra_data'] = is_array($decoded) ? $decoded : null;
        }

        if (empty($data['contract_type_id'])) {
            $data['contract_type_id'] = null;
            $data['contract_extra_data'] = null;
        }

        return $data;
    }
}
