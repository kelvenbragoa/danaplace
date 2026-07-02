<?php

namespace App\Http\Controllers;

use App\Models\AccountStatus;
use App\Models\Acquisition;
use App\Models\Area;
use App\Models\Cause;
use App\Models\CenterCost;
use App\Models\CenterCostAccount;
use App\Models\City;
use App\Models\Coin;
use App\Models\Consequence;
use App\Models\Criticaly;
use App\Models\ContractType;
use App\Models\Department;
use App\Models\Destination;
use App\Models\DistanceControl;
use App\Models\Driver;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentComponent;
use App\Models\EquipmentStatus;
use App\Models\EquipmentSubComponent;
use App\Models\Fee;
use App\Models\JobCardRecommendationTask;
use App\Models\LoadUnity;
use App\Models\LogisticCustomer;
use App\Models\LogisticTripDestination;
use App\Models\LogisticTripLoadStatus;
use App\Models\LogisticTypeLoad;
use App\Models\Mcscr;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\Province;
use App\Models\Reason;
use App\Models\Recommendation;
use App\Models\Role;
use App\Models\Solution;
use App\Models\StockCenter;
use App\Models\StockCenterProduct;
use App\Models\StockSupplier;
use App\Models\SubTask;
use App\Models\Supplier;
use App\Models\Task;
use App\Models\TaskMcscr;
use App\Models\TaskPlanEquipment;
use App\Models\TaskPlanTask;
use App\Models\TaskPlanTaskDepartment;
use App\Models\TaskPlanTaskMaterial;
use App\Models\TaxIva;
use App\Models\Technician;
use App\Models\ToolShop;
use App\Models\TypeDocument;
use App\Models\TypeEquipment;
use App\Models\TypeMalfunction;
use App\Models\TypeOfMeeting;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class GlobalController extends Controller
{
    //

    public function locale(){
        request()->validate(['locale' => 'in:en,pt']);
        Session::put('locale', request('locale'));
        App::setLocale(request('locale'));
        return response()->json(['success' => true]);
    }
    public function excel(Request $request){
        $data = $request->all();

        dd($data);

        $file = $request->file('file');
        $fileContents = file($file->getPathname());

        foreach ($fileContents as $line) {
            $data = str_getcsv($line);

            dd($data[0]);
    
        }

    }

    public function profile(){
        $user = Auth::user();
        $actualUser = User::
        with('role')
        ->find($user->id);

        if($actualUser->signature != null){
           
                $actualUser->signature = Storage::disk('s3')->temporaryUrl(
                    $actualUser->signature,
                    now()->addMinutes(10),
                    ['ResponseContentDisposition' => 'attachment']
                );
        }

        return response()->json(['user'=>$actualUser]);
    }

    public function uploadsignature(Request $request){
        $data = $request->all();
        $user = User::
        with('role')
        ->find($data['user_id']);
        $allowedfileExtension=['pdf'];
        $files = $request->file('image');

            if($request->has('image')){
                // foreach($files as $file){
                    $filename = $files->getClientOriginalName();
                    $extension = $files->getClientOriginalExtension();
                    $imagePath = $files->store('signature','s3');
    
                    $user->update([
                        'signature'=>$imagePath,
                    ]);
    
                    $user->signature = Storage::disk('s3')->temporaryUrl(
                        $user->signature,
                        now()->addMinutes(10),
                        ['ResponseContentDisposition' => 'attachment']
                    );
                }
        



        return response()->json([
            'user'=>$user,
        ],200);

    }
    public function auxiliardatameeting($id){
        $participants = User::where('role_id',$id)->get();

        return response()->json([
            'participants' => $participants
        ]);
    }

    public function auxiliardatalogistic($id){

        $equipments = Equipment::where('destination_id',$id)->orderBy('ref','asc')->get();
        
        return [
            'equipments' => $equipments
        ];

    }

    public function auxiliardata(){

        $roles = Role::orderBy('id','asc')->get();
        $account_statuses = AccountStatus::orderBy('name','asc')->get();
        $user_statuses = UserStatus::orderBy('name','asc')->get();
        $province = Province::orderBy('name','asc')->get();
        $cities = City::orderBy('name','asc')->get();
        $areas = Area::orderBy('name','asc')->get();
        $destinations = Destination::orderBy('name','asc')->get();
        // $typedocuments = TypeDocument::orderBy('name','asc')->get();
        $loads = LogisticTripLoadStatus::all();
        $drivers = Driver::orderBy('name','asc')->get();
        $equipments = Equipment::all();
        $logisticdestinations = LogisticTripDestination::with('loadstatus')->orderBy('departure','asc')->get();

        $islogistc = Destination::where('is_logistic',1)->get();
        $customers = LogisticCustomer::orderBy('customer_name','asc')->get();
        $typeloads = LogisticTypeLoad::orderBy('name','asc')->get();
        $coins = Coin::orderBy('name','asc')->get();


        $roles2 = $roles->except(1);
        $typemeetings = TypeOfMeeting::orderBy('name','asc')->get();

        return [
                'roles'=>$roles,
                'account_statuses'=>$account_statuses,
                'user_statuses'=>$user_statuses,
                'provinces'=>$province , 
                'cities'=>$cities,
                'areas' =>$areas,
                'destinations' =>$destinations,
                'loads'=>$loads,
                'drivers'=>$drivers,
                'equipments'=>$equipments,
                'logisticdestinations'=>$logisticdestinations,
                'islogistic'=>$islogistc,
                'customers'=>$customers,
                'typeloads'=>$typeloads,
                'coins'=>$coins,
                'typemeetings'=>$typemeetings,
                ];
    }


    public function auxiliardatacity($id){
        $cities = City::where('province_id',$id)->orderBy('name','asc')->get();
        return [
           
            'cities'=>$cities,
            ];
    }

    public function auxiliardataequipment(){
        $criticalies = Criticaly::orderBy('id','asc')->get();
        $type_equipments = TypeEquipment::orderBy('name','asc')->get();
        $equipment_statuses = EquipmentStatus::orderBy('name','asc')->get();
        $destinations = Destination::orderBy('name','asc')->get();
        $areas = Area::orderBy('name','asc')->get();
        $suppliers = Supplier::orderBy('name','asc')->get();
        $center_costs = CenterCost::orderBy('name','asc')->get();
        $center_cost_accounts = CenterCostAccount::orderBy('name','asc')->get();
        $acquisitions = Acquisition::orderBy('name')->get();
        $distance_controls = DistanceControl::get();
        $coins = Coin::orderBy('id','asc')->get();
        $load_unities = LoadUnity::orderBy('id','asc')->get();
        $categories = EquipmentCategory::orderBy('id','asc')->get();
        $fees = Fee::orderBy('name','asc')->get();

        return [
            'criticalies'=>$criticalies,
            'type_equipments'=>$type_equipments,
            'equipment_statuses'=>$equipment_statuses,
            'destinations'=>$destinations,
            'areas'=>$areas,
            'suppliers'=>$suppliers,
            'center_costs'=>$center_costs,
            'categories'=>$categories,
            // 'center_cost_accounts'=>$center_cost_accounts,
            'distance_controls'=>$distance_controls,
            'acquisitions'=>$acquisitions,
            'coins'=>$coins,
            'load_unities'=>$load_unities,
            'fees'=>$fees
        ];
    }


    public function auxiliardataequipmentaccount($id){
        $accounts = CenterCostAccount::where('center_cost_id',$id)->orderBy('name','asc')->get();
        return [
            'accounts'=>$accounts,
            ];
        }


    public function auxiliardatamcscr(){

        $type_equipments = TypeEquipment::orderBy('name','asc')->get();
        $malfunctions = TypeMalfunction::orderBy('name','asc')->get();
        $tasks = Task::orderBy('name','asc')->get();
        $reasons = Reason::orderBy('name','asc')->get();
        $destinations = Destination::orderBy('name','asc')->get();
        $coins = Coin::orderBy('name','asc')->get();
        $typemeetings = TypeOfMeeting::orderBy('name','asc')->get();
        $equipments = Equipment::with('destination')->orderBy('name','asc')->get();

        
        return[
            'type_equipments'=>$type_equipments,
            'malfunctions'=>$malfunctions,
            'reasons'=>$reasons,
            'tasks'=>$tasks,
            'destinations'=>$destinations,
            'coins'=>$coins,
            'typemeetings'=>$typemeetings,
            'equipments'=>$equipments,

        ];

    }

        public function auxiliardatainvoice(){

        $equipments = Equipment::with('destination')->where('is_building',1)->orderBy('name','asc')->get();

        
        return[
            'equipments'=>$equipments,
        ];

    }

    public function auxiliardatataskmcscrrecommendation($id){

        $type_equipments = TypeEquipment::orderBy('name','asc')->get();
        $malfunctions = TypeMalfunction::orderBy('name','asc')->get();
        $tasks = Task::orderBy('name','asc')->get();
        $reasons = Reason::orderBy('name','asc')->get();
        $destinations = Destination::orderBy('name','asc')->get();
        $coins = Coin::orderBy('name','asc')->get();
        $typemeetings = TypeOfMeeting::orderBy('name','asc')->get();
        $job = JobCardRecommendationTask::
        with('type_equipment')
            ->with('equipment')
            ->with('status')
            ->with('area')
            ->with('destination')
        ->find($id);
        
        return[
            'type_equipments'=>$type_equipments,
            'malfunctions'=>$malfunctions,
            'reasons'=>$reasons,
            'tasks'=>$tasks,
            'destinations'=>$destinations,
            'coins'=>$coins,
            'typemeetings'=>$typemeetings,
            'jobs'=>$job,

        ];

    }

    public function auxiliardatamcscrdestinationtypeequipment($id){
        // $type_equipments = TypeEquipment::orderBy('name','asc')->get();
        $type_equipments = Equipment::where('destination_id',$id)->get()->groupBy('type_equipment.name');
        return[
            'type_equipments'=>$type_equipments
        ];
    }

    public function auxiliardatamcscrtypeequipmentdestination($id,$destinationid){
        $equipments = Equipment::where('type_equipment_id',$id)->where('destination_id',$destinationid)->with('distance_control')->orderBy('name','asc')->get();
        // $equipments = Equipment::where('type_equipment_id',$id)->with('distance_control')->orderBy('name','asc')->get();

        return[
            'equipments'=>$equipments
        ];
    }

    public function auxiliardatamcscrtypeequipment($id){
        $equipments = Equipment::where('type_equipment_id',$id)->with('distance_control')->orderBy('name','asc')->get();
        // $equipments = Equipment::where('type_equipment_id',$id)->with('distance_control')->orderBy('name','asc')->get();

        return[
            'equipments'=>$equipments
        ];
    }

    public function auxiliardatamcscrcomponent($id){
        $components = EquipmentComponent::where('equipment_id',$id)->orderBy('name','asc')->get();
        return[
            'components'=>$components
        ];
    }

    public function auxiliardatamcscrsubcomponent($id){
        $sub_components = EquipmentSubComponent::where('equipment_component_id',$id)->orderBy('name','asc')->get();
        return[
            'sub_components'=>$sub_components
        ];
    }


    public function auxiliardatamcscrreasons(){
        $searchQuery = request('query');

        $reasons = Reason::where('name','like',"%{$searchQuery}%")->orWhere('code','like',"%{$searchQuery}%")->orderBy('name','asc')->get();

        return $reasons;
    } 

    public function auxiliardatamcscrcauses(){
        $searchQuery = request('query');

        $causes = Cause::where('name','like',"%{$searchQuery}%")->orWhere('code','like',"%{$searchQuery}%")->orderBy('name','asc')->get();

        return $causes;
    } 

    public function auxiliardatamcscrsolutions(){
        $searchQuery = request('query');

        $solutions = Solution::where('name','like',"%{$searchQuery}%")->orWhere('code','like',"%{$searchQuery}%")->orderBy('name','asc')->get();

        return $solutions;
    } 

    public function auxiliardatamcscrrecommendations(){
        $searchQuery = request('query');

        $recommendations = Recommendation::where('name','like',"%{$searchQuery}%")->orWhere('code','like',"%{$searchQuery}%")->orderBy('name','asc')->get();

        return $recommendations;
    } 

    public function auxiliardatamcscrconsequences(){
        $searchQuery = request('query');

        $consequences = Consequence::where('name','like',"%{$searchQuery}%")->orWhere('code','like',"%{$searchQuery}%")->orderBy('name','asc')->get();

        return $consequences;
    } 


    public function auxiliardatataskmcscr($id){
        $task_plan_equipment = TaskPlanEquipment::where('equipment_id',$id)->first();
        $equipment = Equipment::with('distance_control')->find($id);
        if($task_plan_equipment != null){
            $taskplantasks = TaskPlanTask::where('task_plan_id',$task_plan_equipment->task_plan_id)->get();
        }else{
            $taskplantasks = [];
        }
        
        


        return[
            'taskplantasks'=>$taskplantasks,
            'distance_control'=>$equipment->distance_control->name
        ];

    }
    

    public function auxiliardatataskplantaskmcscr($id){
        $subtasks = SubTask::where('task_plan_task_id',$id)->get();
        $materials = TaskPlanTaskMaterial::where('task_plan_task_id',$id)->with('product')->limit(50)->get();
        $departments = TaskPlanTaskDepartment::where('task_plan_task_id',$id)->with('department')->get();


        return[
            'subtasks'=>$subtasks,
            'materials'=>$materials,
            'departments'=>$departments
        ];

    }


    public function auxiliardataproducts(){
        $brands = ProductBrand::orderBy('name','asc')->get();
        $unities = Unit::orderBy('name','asc')->get();
        $ivas = TaxIva::orderBy('name','asc')->get();
        $categories = ProductCategory::orderBy('name','asc')->get();


        return [
            'brands'=>$brands,
            'ivas'=>$ivas,
            'unities'=>$unities,
            'categories'=>$categories
        ];

    }


    public function auxiliardatainventoryproduct($id){

        

        $stockcenterproducts = StockCenterProduct::with('stockproduct')->with('stockcenter')->where('stock_center_id',$id)->get();

        // $stockcenterproducts2 = StockCenterProduct::with('stockproduct')->where('stock_center_id',$id)->get()->map(function($stockcenterproduct){
        //     return[
        //         'id'=>$stockcenterproduct->id,
        //         'quantity'=>$stockcenterproduct->quantity,
        //         'stockcentername'=>$stockcenterproduct->id,
        //         'productcode'=>$stockcenterproduct->stockproduct->code ?? '',
        //         'productname'=>$stockcenterproduct->stockproduct->name ?? '',

        //     ];
        // });

        // $admins = User::where('role_id',1)->orderBy('name','asc')->paginate(3)->map(function($admin){
            //     return [
            //         'id'=>$admin->id,
            //         'name'=>$admin->name,
            //         'role'=>$admin->role->name,
            //         'mobile'=>$admin->mobile,
            //         'email'=>$admin->email,
            //         'created_at'=>$admin->created_at->format('d-m-Y')
            //     ];
            // });

        return [
            'stockcenterproducts' => $stockcenterproducts
        ];

    }


    public function auxiliardatainventory(){

        $stockcenters = StockCenter::get();
        $stocksuppliers = StockSupplier::get();

        return [
            'stockcenters' => $stockcenters,
            'stocksuppliers'=>$stocksuppliers
        ];

    }


    public function auxiliarcreatetechnician(){
        $departments = Department::orderBy('name','asc')->get();
        $areas = Area::orderBy('name','asc')->get();
        $contractTypes = ContractType::active()->orderBy('name','asc')->get();

        return[
            'departments'=>$departments,
            'areas'=>$areas,
            'contract_types'=>$contractTypes,
        ];
    }

    public function auxiliarcreatestockrequest(){

        $products = Product::with('unity')->orderBy('name','asc')->get();
        return[
            'products'=>$products
        ];

    }

    public function auxiliarcreatetechnicianrequest(){

        $departments = Department::orderBy('name','asc')->get();
        return[
            'departments'=>$departments
        ];

    }


    public function auxiliarcreatetoolrequest(){

        $tools = ToolShop::where('status',1)->orderBy('name','asc')->get();
        return[
            'tools'=>$tools
        ];

    }

    

    public function auxiliarcreaterequest($id){

        // dd($id);
        if($id == 1){
            $mcscr = Mcscr::where('mcscr_status_id','!=',1)->orderBy('id','desc')->get();
            return[
                'requests'=>$mcscr
            ];
        }else{
            $taskmcscr = TaskMcscr::where('task_mcscr_status_id','!=',2)->orderBy('id','desc')->get();
            return[
                'requests'=>$taskmcscr
            ];
        }

       

    }

    public function auxiliarcreateschedule($id){
        $techincians = Technician::where('department_id',$id)->get();

        return [
            'technicians'=>$techincians
        ];
    }

    

}
