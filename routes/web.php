<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\EggManagement\DailyProductionController;
use App\Http\Controllers\EggManagement\EggAlertController;
use App\Http\Controllers\EggManagement\EggCategoryController;
use App\Http\Controllers\EggManagement\EggCustomerController;
use App\Http\Controllers\EggManagement\EggCustomerPortalController;
use App\Http\Controllers\EggManagement\EggClassificationController;
use App\Http\Controllers\EggManagement\EggController;
use App\Http\Controllers\EggManagement\EggDashboardController;
use App\Http\Controllers\EggManagement\EggInventoryController;
use App\Http\Controllers\EggManagement\EggKpiController;
use App\Http\Controllers\EggManagement\EggOrderController;
use App\Http\Controllers\EggManagement\EggReportController;
use App\Http\Controllers\EggManagement\EggShippingController;
use App\Http\Controllers\EggManagement\EggTraceabilityController;
use App\Http\Controllers\EggManagement\FarmController;
use App\Http\Controllers\EggManagement\FlockController;
use App\Http\Controllers\EggManagement\HouseController;
use App\Http\Controllers\EggManagement\LineageController;
use App\Http\Controllers\EggManagement\MortalityController;
use App\Http\Controllers\EggManagement\PackagingController;
use App\Http\Controllers\EggManagement\RejectReasonController;
use App\Http\Controllers\EggManagement\VaccinationScheduleController;
use App\Http\Controllers\EggManagement\VaccineController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::post('/set-locale',[App\Http\Controllers\GlobalController::class, 'locale']);



Route::get('/', function () {
    return view('welcome');
});

Route::post('/excel/upload',[App\Http\Controllers\GlobalController::class, 'excel']);



Route::get('/auxiliar-create-meeting/{roleid}', [App\Http\Controllers\GlobalController::class, 'auxiliardatameeting']);

Route::get('/auxiliar-create-users', [App\Http\Controllers\GlobalController::class, 'auxiliardata']);

// Public Entry Guide Verification (outside auth middleware)
Route::get('/entry-guide/verify/{guideNumber}', [App\Http\Controllers\EntryGuideVerificationController::class, 'verify'])->name('entry-guide.verify');
Route::post('/entry-guide/{guideNumber}/entry', [App\Http\Controllers\EntryGuideVerificationController::class, 'recordEntry'])->name('entry-guide.record-entry');
Route::post('/entry-guide/{guideNumber}/exit', [App\Http\Controllers\EntryGuideVerificationController::class, 'recordExit'])->name('entry-guide.record-exit');
Route::get('/auxiliar-create-users/{id}', [App\Http\Controllers\GlobalController::class, 'auxiliardatacity']);

Route::get('/auxiliar-create-equipments', [App\Http\Controllers\GlobalController::class, 'auxiliardataequipment']);
Route::get('/auxiliar-create-equipments/{id}', [App\Http\Controllers\GlobalController::class, 'auxiliardataequipmentaccount']);

Route::get('/auxiliar-create-mcscr', [App\Http\Controllers\GlobalController::class, 'auxiliardatamcscr']);
Route::get('/auxiliar-create-invoice', [App\Http\Controllers\GlobalController::class, 'auxiliardatainvoice']);


Route::get('/auxiliar-create-task-mcscr/{id}', [App\Http\Controllers\GlobalController::class, 'auxiliardatataskmcscrrecommendation']);


Route::get('/auxiliar-create-mcscr-logistic/{id}', [App\Http\Controllers\GlobalController::class, 'auxiliardatalogistic']);


Route::get('/auxiliar-create-mcscr/{id}/{destinationid}', [App\Http\Controllers\GlobalController::class, 'auxiliardatamcscrtypeequipmentdestination']);
Route::get('/auxiliar-create-mcscr/{id}', [App\Http\Controllers\GlobalController::class, 'auxiliardatamcscrtypeequipment']);
// Route::get('/auxiliar-create-mcscr/{id}', [App\Http\Controllers\GlobalController::class, 'auxiliardatamcscrtypeequipment']);

Route::get('/auxiliar-create-mcscr-components/{id}', [App\Http\Controllers\GlobalController::class, 'auxiliardatamcscrcomponent']);
Route::get('/auxiliar-create-mcscr-subcomponents/{id}', [App\Http\Controllers\GlobalController::class, 'auxiliardatamcscrsubcomponent']);
Route::get('/auxiliar-create-mcscr-type-equipment/{id}', [App\Http\Controllers\GlobalController::class, 'auxiliardatamcscrdestinationtypeequipment']);


Route::get('/auxiliar-create-mcscr-plantask/{id}', [App\Http\Controllers\GlobalController::class, 'auxiliardatataskmcscr']);
Route::get('/auxiliar-create-mcscr-subtask/{id}', [App\Http\Controllers\GlobalController::class, 'auxiliardatataskplantaskmcscr']);



Route::get('/auxiliar-create-mcscr-reason', [App\Http\Controllers\GlobalController::class, 'auxiliardatamcscrreasons']);
Route::get('/auxiliar-create-mcscr-cause', [App\Http\Controllers\GlobalController::class, 'auxiliardatamcscrcauses']);
Route::get('/auxiliar-create-mcscr-solution', [App\Http\Controllers\GlobalController::class, 'auxiliardatamcscrsolutions']);
Route::get('/auxiliar-create-mcscr-consequence', [App\Http\Controllers\GlobalController::class, 'auxiliardatamcscrconsequences']);
Route::get('/auxiliar-create-mcscr-recommendation', [App\Http\Controllers\GlobalController::class, 'auxiliardatamcscrrecommendations']);

Route::get('/auxiliar-create-products', [App\Http\Controllers\GlobalController::class, 'auxiliardataproducts']);


Route::get('/auxiliar-create-inventory-product/{id}', [App\Http\Controllers\GlobalController::class, 'auxiliardatainventoryproduct']);
Route::get('/auxiliar-create-inventory', [App\Http\Controllers\GlobalController::class, 'auxiliardatainventory']);
Route::get('auxiliar-create-technicians', [\App\Http\Controllers\GlobalController::class,'auxiliarcreatetechnician']);
Route::get('auxiliar-create-stockrequest', [\App\Http\Controllers\GlobalController::class,'auxiliarcreatestockrequest']);
Route::get('auxiliar-create-technicianrequest', [\App\Http\Controllers\GlobalController::class,'auxiliarcreatetechnicianrequest']);
Route::get('auxiliar-create-toolrequest', [\App\Http\Controllers\GlobalController::class,'auxiliarcreatetoolrequest']);
Route::get('auxiliar-create-schedule/{id}', [\App\Http\Controllers\GlobalController::class,'auxiliarcreateschedule']);


Route::get('/calendars', [App\Http\Controllers\Admin\TaskMcscrController::class, 'calendar']);
Route::get('/detailcalendar/{parms}', [App\Http\Controllers\Admin\TaskMcscrController::class, 'detailcalendar']);








Route::get('/auxiliar-create-requeststock/{id}', [App\Http\Controllers\GlobalController::class, 'auxiliarcreaterequest']);








// Route::group(['middleware'=>['auth','admin']], function(){
    Route::group(['middleware'=>['auth']], function(){

    Route::delete('/mcscr-resolution/{id}', [App\Http\Controllers\Admin\MCSCRController::class, 'deleteresolutions']);

    //Admins Route
    //rotas para CRUD Administradores
    Route::get('/admins/dashboard/getdashboarddata', [App\Http\Controllers\Admin\DashboardController::class, 'dashboarddata']);
    Route::get('/updatedashboard/{id}', [App\Http\Controllers\Admin\DashboardController::class, 'updatedashboard']);
    
    Route::get('/admins/destination/dashboard/getdashboarddata', [App\Http\Controllers\Admin\DashboardController::class, 'dashboarddatadestination']);

    Route::get('/mcscrjobtask', [App\Http\Controllers\Admin\JobCardRecommendationTaskController::class, 'mcscrjobtask']);

    Route::resource('jobtasks', 'App\Http\Controllers\Admin\JobCardRecommendationTaskController');

    Route::resource('users', 'App\Http\Controllers\Admin\UsersController');
    Route::resource('areas', 'App\Http\Controllers\Admin\AreasController');

    Route::resource('fees', 'App\Http\Controllers\Admin\FeeController');
    
    // Rotas específicas para faturamento de taxas (DEVEM vir antes da resource)
    Route::get('fee-invoices/equipments-with-fees', [App\Http\Controllers\Admin\FeeInvoiceController::class, 'getEquipmentsWithFees'])->name('fee-invoices.equipments-with-fees');
    Route::post('fee-invoices/{id}/approve', [App\Http\Controllers\Admin\FeeInvoiceController::class, 'approve'])->name('fee-invoices.approve');
    Route::post('fee-invoices/{invoiceId}/items/{itemId}/toggle-payment', [App\Http\Controllers\Admin\FeeInvoiceController::class, 'toggleItemPayment'])->name('fee-invoices.toggle-payment');
    Route::get('fee-invoices/{id}/report', [App\Http\Controllers\Admin\FeeInvoiceController::class, 'generateReport'])->name('fee-invoices.report');
    Route::get('fee-invoices/{id}/destination-report/{destinationId}', [App\Http\Controllers\Admin\FeeInvoiceController::class, 'generateDestinationReport'])->name('fee-invoices.destination-report');
    Route::get('fee-invoices-dashboard', [App\Http\Controllers\Admin\FeeInvoiceController::class, 'dashboard'])->name('fee-invoices.dashboard');
    
    // Rota resource para faturamento de taxas
    Route::resource('fee-invoices', 'App\Http\Controllers\Admin\FeeInvoiceController');

    Route::resource('departments', 'App\Http\Controllers\Admin\DepartmentsController');
    Route::resource('technicians', 'App\Http\Controllers\Admin\TechnicianController');
    
    // Rotas para plano de férias
    Route::resource('vacation-plans', 'App\Http\Controllers\Admin\VacationPlanController');
    Route::post('vacation-plans/{id}/approve', [App\Http\Controllers\Admin\VacationPlanController::class, 'approve'])->name('vacation-plans.approve');
    Route::post('vacation-plans/{id}/reject', [App\Http\Controllers\Admin\VacationPlanController::class, 'reject'])->name('vacation-plans.reject');
    Route::post('vacation-plans/{id}/execute', [App\Http\Controllers\Admin\VacationPlanController::class, 'execute'])->name('vacation-plans.execute');
    Route::get('vacation-plans-statistics', [App\Http\Controllers\Admin\VacationPlanController::class, 'statistics'])->name('vacation-plans.statistics');
    
    // Rotas para processamento salarial
    Route::resource('salary-processes', 'App\Http\Controllers\Admin\SalaryProcessController');
    Route::post('salary-processes/{id}/approve', [App\Http\Controllers\Admin\SalaryProcessController::class, 'approve'])->name('salary-processes.approve');
    Route::post('salary-processes/{id}/mark-as-paid', [App\Http\Controllers\Admin\SalaryProcessController::class, 'markAsPaid'])->name('salary-processes.mark-as-paid');
    Route::get('salary-processes/{id}/report', [App\Http\Controllers\Admin\SalaryProcessController::class, 'generateReport'])->name('salary-processes.report');
    Route::get('salary-processes/{processId}/payslip/{itemId}', [App\Http\Controllers\Admin\SalaryProcessController::class, 'generatePayslip'])->name('salary-processes.payslip');
    Route::get('technicians-with-salary', [App\Http\Controllers\Admin\SalaryProcessController::class, 'getTechnicians'])->name('technicians.with-salary');
    Route::get('technician-absences', [App\Http\Controllers\Admin\SalaryProcessController::class, 'getTechnicianAbsences'])->name('technician.absences');
    
    // Rotas para faltas de técnicos
    Route::get('absences/technicians', [App\Http\Controllers\Admin\TechnicianAbsenceController::class, 'getTechnicians'])->name('absences.technicians');
    Route::resource('absences', 'App\Http\Controllers\Admin\TechnicianAbsenceController');
    Route::post('absences/{id}/approve', [App\Http\Controllers\Admin\TechnicianAbsenceController::class, 'approve'])->name('absences.approve');
    Route::post('absences/{id}/reject', [App\Http\Controllers\Admin\TechnicianAbsenceController::class, 'reject'])->name('absences.reject');
    Route::get('absences-report', [App\Http\Controllers\Admin\TechnicianAbsenceController::class, 'report'])->name('absences.report');
    
    // Rotas para Escala de Trabalho
    Route::prefix('work-schedule')->group(function () {
        // Dashboard
        Route::get('dashboard', [App\Http\Controllers\WorkScheduleController::class, 'dashboard'])->name('work-schedule.dashboard');
        
        // Schedules
        Route::get('/', [App\Http\Controllers\WorkScheduleController::class, 'index'])->name('work-schedule.index');
        Route::get('create', [App\Http\Controllers\WorkScheduleController::class, 'create'])->name('work-schedule.create');
        Route::post('/', [App\Http\Controllers\WorkScheduleController::class, 'store'])->name('work-schedule.store');
        Route::get('{workSchedule}', [App\Http\Controllers\WorkScheduleController::class, 'show'])->name('work-schedule.show');
        Route::get('{workSchedule}/edit', [App\Http\Controllers\WorkScheduleController::class, 'edit'])->name('work-schedule.edit');
        Route::put('{workSchedule}', [App\Http\Controllers\WorkScheduleController::class, 'update'])->name('work-schedule.update');
        Route::delete('{workSchedule}', [App\Http\Controllers\WorkScheduleController::class, 'destroy'])->name('work-schedule.destroy');
        
        // Schedule actions
        Route::post('copy', [App\Http\Controllers\WorkScheduleController::class, 'copy'])->name('work-schedule.copy');
        Route::patch('{workSchedule}/toggle-status', [App\Http\Controllers\WorkScheduleController::class, 'toggleStatus'])->name('work-schedule.toggle-status');
        
        // Shifts
        Route::get('shifts/initial-data', [App\Http\Controllers\ShiftController::class, 'initialData'])->name('shifts.initial-data');
        Route::get('shifts', [App\Http\Controllers\ShiftController::class, 'index'])->name('shifts.index');
        Route::get('shifts/create', [App\Http\Controllers\ShiftController::class, 'create'])->name('shifts.create');
        Route::post('shifts', [App\Http\Controllers\ShiftController::class, 'store'])->name('shifts.store');
        Route::get('shifts/{shift}', [App\Http\Controllers\ShiftController::class, 'show'])->name('shifts.show');
        Route::get('shifts/{shift}/edit', [App\Http\Controllers\ShiftController::class, 'edit'])->name('shifts.edit');
        Route::put('shifts/{shift}', [App\Http\Controllers\ShiftController::class, 'update'])->name('shifts.update');
        Route::delete('shifts/{shift}', [App\Http\Controllers\ShiftController::class, 'destroy'])->name('shifts.destroy');
        
        // Shift actions
        Route::post('shifts/{shift}/copy', [App\Http\Controllers\ShiftController::class, 'copy'])->name('shifts.copy');
        Route::patch('shifts/{shift}/toggle-status', [App\Http\Controllers\ShiftController::class, 'toggleStatus'])->name('shifts.toggle-status');
        Route::get('shifts/by-schedule', [App\Http\Controllers\ShiftController::class, 'bySchedule'])->name('shifts.by-schedule');
        Route::post('shifts/bulk-action', [App\Http\Controllers\ShiftController::class, 'bulkAction'])->name('shifts.bulk-action');
    });
    
    // Client view (public endpoint)
    Route::get('work-schedule/client-view', [App\Http\Controllers\WorkScheduleController::class, 'clientView'])->name('work-schedule.client-view');
    
    Route::resource('tasks', 'App\Http\Controllers\Admin\TaskController');
    Route::resource('taskplans', 'App\Http\Controllers\Admin\TaskPlanController');
    Route::resource('taskplanequipments', 'App\Http\Controllers\Admin\TaskPlanEquipmentController');
    Route::resource('taskplantasks', 'App\Http\Controllers\Admin\TaskPlanTaskController');
    Route::resource('subtasks', 'App\Http\Controllers\Admin\SubTaskController');
    Route::resource('taskmaterials', 'App\Http\Controllers\Admin\TaskMaterialsController');
    Route::resource('taskdepartments', 'App\Http\Controllers\Admin\TaskDepartmentsController');
    Route::resource('malfunctions', 'App\Http\Controllers\Admin\MalfunctionsController');
    Route::resource('destinations', 'App\Http\Controllers\Admin\DestinationsController');
    
    // Entry Guides routes
    Route::resource('entry-guides', 'App\Http\Controllers\Admin\EntryGuideController')->names([
        'index' => 'admin.entry-guides.index',
        'create' => 'admin.entry-guides.create',
        'store' => 'admin.entry-guides.store',
        'show' => 'admin.entry-guides.show',
        'edit' => 'admin.entry-guides.edit',
        'update' => 'admin.entry-guides.update',
        'destroy' => 'admin.entry-guides.destroy'
    ]);
    Route::get('entry-guides/{entryGuide}/pdf', [App\Http\Controllers\Admin\EntryGuideController::class, 'downloadPdf'])->name('admin.entry-guides.pdf');
    Route::patch('entry-guides/{entryGuide}/cancel', [App\Http\Controllers\Admin\EntryGuideController::class, 'cancel'])->name('admin.entry-guides.cancel');
    Route::resource('centercost', 'App\Http\Controllers\Admin\CenterCostController');
    Route::resource('centercostaccount', 'App\Http\Controllers\Admin\CenterCostAccountController');
    Route::resource('equipments', 'App\Http\Controllers\Admin\EquipmentsController');
    Route::resource('type_equipments', 'App\Http\Controllers\Admin\TypeEquipmentsController');
    Route::resource('fleets', 'App\Http\Controllers\Admin\FleetController');
    Route::resource('suppliers', 'App\Http\Controllers\Admin\SuppliersController');
    Route::resource('equipmentcomponent', 'App\Http\Controllers\Admin\EquipmentComponentsController');
    Route::resource('equipmentsubcomponent','App\Http\Controllers\Admin\EquipmentSubComponentController');
    Route::resource('typeequipmentcomponent', 'App\Http\Controllers\Admin\TypeEquipmentComponentsController');
    Route::resource('typeequipmentsubcomponent','App\Http\Controllers\Admin\TypeEquipmentSubComponentController');
    Route::resource('reasons', 'App\Http\Controllers\Admin\ReasonsController');
    Route::resource('causes', 'App\Http\Controllers\Admin\CausesController');
    Route::resource('solutions', 'App\Http\Controllers\Admin\SolutionsController');
    Route::resource('consequences', 'App\Http\Controllers\Admin\ConsequencesController');
    Route::resource('recommendations', 'App\Http\Controllers\Admin\RecommendationsController');
    Route::resource('mcscr', 'App\Http\Controllers\Admin\MCSCRController');
    Route::resource('inspections', 'App\Http\Controllers\Admin\InspectionController');
    Route::resource('generalinspections', 'App\Http\Controllers\Admin\GeneralInspectionController');
    Route::resource('brands', 'App\Http\Controllers\Admin\ProductBrandController');
    Route::resource('categories', 'App\Http\Controllers\Admin\ProductCategoryController');
    Route::resource('products', 'App\Http\Controllers\Admin\ProductController');
    Route::resource('taskmcscr', 'App\Http\Controllers\Admin\TaskMcscrController');
    Route::resource('stockcenters', 'App\Http\Controllers\Admin\StockCentersController');
    Route::resource('inventories', 'App\Http\Controllers\Admin\InventoryController');
    Route::resource('exitnotes', 'App\Http\Controllers\Admin\ExitNoteController');
    Route::resource('shifts', 'App\Http\Controllers\Admin\ShiftController');
    Route::resource('entrynotes', 'App\Http\Controllers\Admin\EntryNoteController');
    Route::resource('stocksuppliers', 'App\Http\Controllers\Admin\StockSupplierController');
    Route::resource('stocktransfers', 'App\Http\Controllers\Admin\StockTransferController');
    Route::resource('stockrequests', 'App\Http\Controllers\Admin\RequestStockController');
    Route::resource('technicianrequests', 'App\Http\Controllers\Admin\RequestTechnicianController');
    Route::resource('toolrequests', 'App\Http\Controllers\Admin\RequestToolController');
    Route::resource('toolshops', 'App\Http\Controllers\Admin\ToolShopController');
    Route::resource('notifications', 'App\Http\Controllers\Admin\NotificationController');
    Route::resource('hourdistances', 'App\Http\Controllers\Admin\HoursDistanceEquipmentController');
    Route::resource('schedulework', 'App\Http\Controllers\Admin\ScheduleWorkController');

    Route::resource('quotation', 'App\Http\Controllers\Admin\QuotationController');

    Route::get('/calendarquotation', [App\Http\Controllers\Admin\QuotationController::class, 'calendar']);

    Route::resource('quotationitem', 'App\Http\Controllers\Admin\QuotationItemController');
    Route::resource('scheduleworkitem', 'App\Http\Controllers\Admin\ScheduleWorkItemController');
    Route::resource('fuel', 'App\Http\Controllers\Admin\FuelController');
    Route::resource('typedocuments', 'App\Http\Controllers\Admin\TypeDocumentController');
    Route::resource('documents', 'App\Http\Controllers\Admin\DocumentController');
    Route::resource('trips', 'App\Http\Controllers\Admin\TripController');
    Route::resource('tripexpenses', 'App\Http\Controllers\Admin\TripExpensesController');

    Route::resource('waterconsumption', 'App\Http\Controllers\Admin\WaterConsumptionController');
    Route::resource('energyconsumption', 'App\Http\Controllers\Admin\EnergyConsumptionController');


    Route::resource('driver', 'App\Http\Controllers\Admin\DriverController');
    Route::resource('logisticdestination', 'App\Http\Controllers\Admin\LogisticDestinationController');
    Route::resource('logistictrip', 'App\Http\Controllers\Admin\LogisticTripController');
    Route::resource('destinationexpense', 'App\Http\Controllers\Admin\LogisticDestinationExpenseController');
    Route::resource('tripexpense', 'App\Http\Controllers\Admin\LogisticTripExpenseController');


    Route::resource('logisticcustomer', 'App\Http\Controllers\Admin\LogisticCustomerController');
    Route::resource('logisticquotation', 'App\Http\Controllers\Admin\LogisticQuotationController');


    Route::resource('meeting', 'App\Http\Controllers\Admin\MeetingController');
    Route::resource('meetingtype', 'App\Http\Controllers\Admin\MeetingTypeController');
    Route::resource('meetingattachment', 'App\Http\Controllers\Admin\MeetingAttachmentController');
    Route::resource('meetingparticipant', 'App\Http\Controllers\Admin\MeetingParticipantController');
    Route::resource('meetingtask', 'App\Http\Controllers\Admin\MeetingTaskController');
    Route::post('/copymeetingtask',[App\Http\Controllers\Admin\MeetingTaskController::class, 'copy']);

    Route::get('/mcscr/{id}/upload',[App\Http\Controllers\Admin\MCSCRController::class, 'viewupload']);
    Route::post('/mcscr/upload',[App\Http\Controllers\Admin\MCSCRController::class, 'upload']);
    Route::delete('/mcscr/upload/{id}',[App\Http\Controllers\Admin\MCSCRController::class, 'deleteupload']);

    Route::get('/equipments/{id}/upload',[App\Http\Controllers\Admin\EquipmentsController::class, 'viewupload']);
    Route::post('/equipments/upload',[App\Http\Controllers\Admin\EquipmentsController::class, 'upload']);
    Route::delete('/equipments/upload/{id}',[App\Http\Controllers\Admin\EquipmentsController::class, 'deleteupload']);


    Route::get('profile',[App\Http\Controllers\GlobalController::class, 'profile']);
    Route::post('/profile/upload',[App\Http\Controllers\GlobalController::class, 'uploadsignature']);

    Route::post('/excel/upload',[App\Http\Controllers\Admin\ProductController::class, 'excel']);
    

    Route::get('/equipments/reconciliation/{id}',[App\Http\Controllers\Admin\EquipmentsController::class, 'reconciliation']);

    Route::get('/destinationsfleet/{fleet_id}/destination/{destination_id}', [App\Http\Controllers\Admin\FleetDestinationsController::class, 'show']);

    Route::get('/fleets/{id}/mcscrcount',[App\Http\Controllers\Admin\FleetController::class,'mcscrcount']);
    Route::get('/fleets/{id}/taskcount',[App\Http\Controllers\Admin\FleetController::class,'taskcount']);
    Route::get('/fleets/{id}/fuelcount',[App\Http\Controllers\Admin\FleetController::class,'fuelcount']);
    Route::get('/fleets/{id}/hourdistancecount',[App\Http\Controllers\Admin\FleetController::class,'hourdistancecount']);
    Route::get('/equipments/{id}/mcscrcount',[App\Http\Controllers\Admin\EquipmentsController::class,'mcscrcount']);
    Route::get('/equipments/{id}/taskcount',[App\Http\Controllers\Admin\EquipmentsController::class,'taskcount']);
    Route::get('/equipments/{id}/fuelcount',[App\Http\Controllers\Admin\EquipmentsController::class,'fuelcount']);

    Route::get('/equipments/{id}/watercount',[App\Http\Controllers\Admin\EquipmentsController::class,'watercount']);
    Route::get('/equipments/{id}/energycount',[App\Http\Controllers\Admin\EquipmentsController::class,'energycount']);

    Route::get('/equipments/{id}/hourdistancecount',[App\Http\Controllers\Admin\EquipmentsController::class,'hourdistancecount']);
    Route::get('/taskplantasks/{id}/copy',[App\Http\Controllers\Admin\TaskPlanTaskController::class,'copytask']);

    Route::get('/taskplans/{id}/copy',[App\Http\Controllers\Admin\TaskPlanController::class,'copytask']);
    Route::get('/equipments/{id}/copy',[App\Http\Controllers\Admin\EquipmentsController::class,'copyequipment']);
    Route::get('/type_equipments/{id}/copy',[App\Http\Controllers\Admin\TypeEquipmentsController::class,'copytypeequipment']);

    Route::get('/type_equipments_component/{id}/copy',[App\Http\Controllers\Admin\TypeEquipmentComponentsController::class,'copytypeequipmentcomponent']);

    Route::get('/download-mcscr/{id}', [App\Http\Controllers\Admin\MCSCRController::class, 'download']);
    Route::get('/download-taskmcscr/{id}', [App\Http\Controllers\Admin\TaskMcscrController::class, 'download']);

    Route::resource('groupshift', 'App\Http\Controllers\Admin\GroupShiftController');
    Route::resource('groupshiftoperator', 'App\Http\Controllers\Admin\GroupShiftOperatorController');
    Route::resource('shiftequipmentrequest', 'App\Http\Controllers\Admin\ShiftEquipmentRequestController');
    Route::resource('shiftequipmentrequestitem', 'App\Http\Controllers\Admin\ShiftEquipmentRequestItemController');

    Route::get('/destination-calendars', [App\Http\Controllers\Destination\TaskMcscrController::class, 'calendar']);
    Route::get('/destination-detailcalendar/{parms}', [App\Http\Controllers\Admin\TaskMcscrController::class, 'detailcalendar']);

    Route::get('/meeting-task-calendars', [App\Http\Controllers\Admin\MeetingTaskController::class, 'calendar']);
    Route::get('/meeting-task-detailcalendar/{parms}', [App\Http\Controllers\Admin\MeetingTaskController::class, 'detailcalendar']);

    


    Route::resource('tirelayouts', 'App\Http\Controllers\Admin\TireLayoutController');




    //ROUTES FOR DESTINATION
    Route::resource('destination-equipments', 'App\Http\Controllers\Destination\EquipmentController');
    Route::resource('destination-type_equipments', 'App\Http\Controllers\Destination\TypeEquipmentsController');
    Route::resource('destination-hourdistances', 'App\Http\Controllers\Destination\HoursDistanceController');
    Route::resource('destination-fuel', 'App\Http\Controllers\Destination\FuelController');
    Route::resource('destination-taskmcscr', 'App\Http\Controllers\Destination\TaskMcscrController');
    Route::resource('destination-mcscr', 'App\Http\Controllers\Destination\McscrController');
    Route::resource('destination-quotation', 'App\Http\Controllers\Destination\QuotationController');
    
    // Fee Invoices routes for destinations
    Route::prefix('destination/fee-invoices')->middleware(['auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\Destination\FeeInvoiceController::class, 'index']);
        Route::get('/statistics', [App\Http\Controllers\Destination\FeeInvoiceController::class, 'statistics']);
        Route::get('/{id}', [App\Http\Controllers\Destination\FeeInvoiceController::class, 'show']);
        Route::get('/{id}/report', [App\Http\Controllers\Destination\FeeInvoiceController::class, 'generateReport']);
    });



    Route::resource('energyinvoice', 'App\Http\Controllers\Admin\EnergyInvoiceController');
    Route::resource('energyinvoiceitem', 'App\Http\Controllers\Admin\EnergyInvoiceItemController');

    Route::get('energyinvoice/client/{id}', [App\Http\Controllers\Admin\EnergyInvoiceController::class, 'showClient'])->name('energyinvoice.client.show');
    
    // Energy Invoice Payment Routes
    Route::post('energyinvoice/{invoiceId}/items/{itemId}/toggle-payment', [App\Http\Controllers\Admin\EnergyInvoiceController::class, 'toggleItemPayment'])->name('energyinvoice.toggle-payment');
    Route::get('energyinvoice/dashboard', [App\Http\Controllers\Admin\EnergyInvoiceController::class, 'dashboard'])->name('energyinvoice.dashboard');

    // Energy Invoice Readings Routes
    Route::get('energyinvoice/{invoiceId}/readings', [App\Http\Controllers\Admin\EnergyInvoiceController::class, 'getReadings'])->name('energyinvoice.readings.index');
    Route::post('energyinvoice/{invoiceId}/readings', [App\Http\Controllers\Admin\EnergyInvoiceController::class, 'storeReading'])->name('energyinvoice.readings.store');
    Route::put('energyinvoice/{invoiceId}/readings/{readingId}', [App\Http\Controllers\Admin\EnergyInvoiceController::class, 'updateReading'])->name('energyinvoice.readings.update');
    Route::delete('energyinvoice/{invoiceId}/readings/{readingId}', [App\Http\Controllers\Admin\EnergyInvoiceController::class, 'destroyReading'])->name('energyinvoice.readings.destroy');
    Route::get('energyinvoice/{invoiceId}/readings/stats', [App\Http\Controllers\Admin\EnergyInvoiceController::class, 'getReadingStats'])->name('energyinvoice.readings.stats');

    
    // Rotas para gerenciamento de despesas do condomínio
    Route::resource('expense-categories', 'App\Http\Controllers\Admin\ExpenseCategoryController');
    Route::post('expense-categories/{id}/toggle-status', [App\Http\Controllers\Admin\ExpenseCategoryController::class, 'toggleStatus'])->name('expense-categories.toggle-status');
    Route::get('expense-categories-active', [App\Http\Controllers\Admin\ExpenseCategoryController::class, 'getActive'])->name('expense-categories.active');
    Route::get('expense-categories-statistics', [App\Http\Controllers\Admin\ExpenseCategoryController::class, 'statistics'])->name('expense-categories.statistics');
    Route::get('expense-categories-colors', [App\Http\Controllers\Admin\ExpenseCategoryController::class, 'getColorSuggestions'])->name('expense-categories.colors');
    Route::get('expense-categories-icons', [App\Http\Controllers\Admin\ExpenseCategoryController::class, 'getIconSuggestions'])->name('expense-categories.icons');
    
    Route::resource('expenses', 'App\Http\Controllers\Admin\ExpenseController');
    Route::post('expenses/{id}/approve', [App\Http\Controllers\Admin\ExpenseController::class, 'approve'])->name('expenses.approve');
    Route::post('expenses/{id}/reject', [App\Http\Controllers\Admin\ExpenseController::class, 'reject'])->name('expenses.reject');
    Route::post('expenses/{id}/pay', [App\Http\Controllers\Admin\ExpenseController::class, 'pay'])->name('expenses.pay');
    Route::post('expenses/{id}/mark-as-paid', [App\Http\Controllers\Admin\ExpenseController::class, 'markAsPaid'])->name('expenses.mark-as-paid');
    Route::get('expenses-statistics', [App\Http\Controllers\Admin\ExpenseController::class, 'statistics'])->name('expenses.statistics');
    Route::get('expenses-filter-options', [App\Http\Controllers\Admin\ExpenseController::class, 'getFilterOptions'])->name('expenses.filter-options');
    Route::delete('expenses/{id}/attachment', [App\Http\Controllers\Admin\ExpenseController::class, 'removeAttachment'])->name('expenses.remove-attachment');


    Route::prefix('admin')->group(function () {
    
        // ============================================
        // 1. Farm Management
        // ============================================
        Route::get('farms-all', [FarmController::class, 'getAll'])->name('farms.all');
        Route::resource('farms', FarmController::class);
        Route::post('farms/{farm}/toggle-status', [FarmController::class, 'toggleStatus'])->name('farms.toggle-status');
        Route::get('farms-export', [FarmController::class, 'export'])->name('farms.export');
    
        // ============================================
        // 2. House Management
        // ============================================
        Route::get('houses-all', [HouseController::class, 'getAll'])->name('houses.all');
        Route::resource('houses', HouseController::class);
        Route::post('houses/{house}/toggle-status', [HouseController::class, 'toggleStatus'])->name('houses.toggle-status');
        Route::get('houses-by-farm/{farm}', [HouseController::class, 'getByFarm'])->name('houses.by-farm');
    
        // ============================================
        // 3. Lineage Management
        // ============================================
        Route::get('lineages-all', [LineageController::class, 'getAll'])->name('lineages.all');
        Route::resource('lineages', LineageController::class);
        Route::post('lineages/{lineage}/toggle-status', [LineageController::class, 'toggleStatus'])->name('lineages.toggle-status');
    
        // ============================================
        // 4. Flock Management
        // ============================================
        Route::get('flocks-all', [FlockController::class, 'getAll'])->name('flocks.all');
        Route::get('flocks-active', [FlockController::class, 'getActive'])->name('flocks.active');
        Route::resource('flocks', FlockController::class);
        Route::post('flocks/{flock}/change-status', [FlockController::class, 'changeStatus'])->name('flocks.change-status');
        Route::get('flocks/{flock}/production-chart', [FlockController::class, 'productionChart'])->name('flocks.production-chart');
        Route::post('flocks/{flock}/dispose', [FlockController::class, 'dispose'])->name('flocks.dispose');
    
        // ============================================
        // 5. Daily Production
        // ============================================
        Route::resource('daily-production', DailyProductionController::class);
        Route::post('daily-production/bulk-store', [DailyProductionController::class, 'bulkStore'])->name('daily-production.bulk-store');
        Route::get('daily-production/by-flock/{flock}', [DailyProductionController::class, 'getByFlock'])->name('daily-production.by-flock');
        Route::get('daily-production/by-date/{date}', [DailyProductionController::class, 'getByDate'])->name('daily-production.by-date');
    
        // ============================================
        // 6. Mortality Management
        // ============================================
        Route::get('mortality/dashboard-stats', [MortalityController::class, 'dashboardStats'])->name('mortality.dashboard-stats');
        Route::get('mortality/by-flock/{flock}', [MortalityController::class, 'getByFlock'])->name('mortality.by-flock');
        Route::resource('mortality', MortalityController::class);
    
        // ============================================
        // 7. Vaccine Management
        // ============================================
        Route::get('vaccines-all', [VaccineController::class, 'getAll'])->name('vaccines.all');
        Route::get('vaccines/expiring-soon', [VaccineController::class, 'expiringSoon'])->name('vaccines.expiring-soon');
        Route::resource('vaccines', VaccineController::class);
        Route::post('vaccines/{vaccine}/adjust-stock', [VaccineController::class, 'adjustStock'])->name('vaccines.adjust-stock');
    
        // ============================================
        // 8. Vaccination Schedule
        // ============================================
        Route::get('vaccination-schedule/pending-today', [VaccinationScheduleController::class, 'pendingToday'])->name('vaccination-schedule.pending-today');
        Route::get('vaccination-schedule/by-flock/{flock}', [VaccinationScheduleController::class, 'getByFlock'])->name('vaccination-schedule.by-flock');
        Route::resource('vaccination-schedule', VaccinationScheduleController::class);
        Route::post('vaccination-schedule/{vaccination_schedule}/apply', [VaccinationScheduleController::class, 'apply'])->name('vaccination-schedule.apply');
        Route::post('vaccination-schedule/{vaccination_schedule}/cancel', [VaccinationScheduleController::class, 'cancel'])->name('vaccination-schedule.cancel');
    
        // ============================================
        // 9. Egg Categories
        // ============================================
        Route::get('egg-categories-all', [EggCategoryController::class, 'getAll'])->name('egg-categories.all');
        Route::post('egg-categories/{egg_category}/toggle-status', [EggCategoryController::class, 'toggleStatus'])->name('egg-categories.toggle-status');
        Route::resource('egg-categories', EggCategoryController::class);
    
        // ============================================
        // 10. Egg Classification
        // ============================================
        Route::get('egg-classifications/reject-report', [EggClassificationController::class, 'rejectReport'])->name('egg-classifications.reject-report');
        Route::get('egg-classifications-all', [EggClassificationController::class, 'getAll'])->name('egg-classifications.all');
        Route::get('egg-classifications/by-flock/{flock}', [EggClassificationController::class, 'getByFlock'])->name('egg-classifications.by-flock');
        Route::post('egg-classifications/process', [EggClassificationController::class, 'process'])->name('egg-classifications.process');
        Route::resource('egg-classifications', EggClassificationController::class);
    
        // ============================================
        // 11. Egg Management (Individual Eggs)
        // ============================================
        Route::get('eggs-all', [EggController::class, 'getAll'])->name('eggs.all');
        Route::get('eggs/traceability/{code}', [EggController::class, 'getByTraceabilityCode'])->name('eggs.by-traceability');
        Route::post('eggs/bulk-classify', [EggController::class, 'bulkClassify'])->name('eggs.bulk-classify');
        Route::resource('eggs', EggController::class);
    
        // ============================================
        // 12. Packaging Management
        // ============================================
        Route::get('packaging/generate-qr/{packaging}', [PackagingController::class, 'generateQrCode'])->name('packaging.generate-qr');
        Route::post('packaging/validate-qr', [PackagingController::class, 'validateQrCode'])->name('packaging.validate-qr');
        Route::resource('packaging', PackagingController::class);
    
        // ============================================
        // 13. Egg Inventory
        // ============================================
        Route::get('egg-inventory/fifo-list', [EggInventoryController::class, 'fifoList'])->name('egg-inventory.fifo-list');
        Route::get('egg-inventory/stock-alerts', [EggInventoryController::class, 'stockAlerts'])->name('egg-inventory.stock-alerts');
        Route::get('egg-inventory/by-category/{category}', [EggInventoryController::class, 'getByCategory'])->name('egg-inventory.by-category');
        Route::post('egg-inventory/{egg_inventory}/reserve', [EggInventoryController::class, 'reserve'])->name('egg-inventory.reserve');
        Route::post('egg-inventory/{egg_inventory}/release', [EggInventoryController::class, 'release'])->name('egg-inventory.release');
        Route::resource('egg-inventory', EggInventoryController::class);
    
        // ============================================
        // 14. Egg Customers
        // ============================================
        Route::get('egg-customers-all', [EggCustomerController::class, 'getAll'])->name('egg-customers.all');
        Route::post('egg-customers/{egg_customer}/regenerate-portal-code', [EggCustomerController::class, 'regeneratePortalCode'])->name('egg-customers.regenerate-portal-code');
        Route::resource('egg-customers', EggCustomerController::class);

        // ============================================
        // 15. Egg Orders (Sales)
        // ============================================
        Route::get('egg-orders/pending-orders', [EggOrderController::class, 'pendingOrders'])->name('egg-orders.pending');
        Route::get('egg-orders/invoice/{egg_order}', [EggOrderController::class, 'generateInvoice'])->name('egg-orders.invoice');
        Route::post('egg-orders/{egg_order}/approve', [EggOrderController::class, 'approve'])->name('egg-orders.approve');
        Route::post('egg-orders/{egg_order}/cancel', [EggOrderController::class, 'cancel'])->name('egg-orders.cancel');
        Route::post('egg-orders/{egg_order}/pick', [EggOrderController::class, 'pick'])->name('egg-orders.pick');
        Route::resource('egg-orders', EggOrderController::class);
    
        // ============================================
        // 16. Egg Shipping / Expedition
        // ============================================
        Route::get('egg-shipping/today-shipping', [EggShippingController::class, 'todayShipping'])->name('egg-shipping.today');
        Route::get('egg-shipping/calendar-events', [EggShippingController::class, 'calendarEvents'])->name('egg-shipping.calendar');
        Route::get('egg-shipping/invoice/{egg_shipping}/print', [EggShippingController::class, 'printInvoice'])->name('egg-shipping.print-invoice');
        Route::post('egg-shipping/validate-temperature', [EggShippingController::class, 'validateTemperature'])->name('egg-shipping.validate-temperature');
        Route::post('egg-shipping/{egg_shipping}/dispatch', [EggShippingController::class, 'dispatch'])->name('egg-shipping.dispatch');
        Route::resource('egg-shipping', EggShippingController::class);
    
        // ============================================
        // 16. Egg Traceability
        // ============================================
        Route::prefix('traceability')->name('traceability.')->group(function () {
            Route::get('search', [EggTraceabilityController::class, 'search'])->name('search');
            Route::get('by-date-range', [EggTraceabilityController::class, 'byDateRange'])->name('by-date-range');
            Route::get('export', [EggTraceabilityController::class, 'export'])->name('export');
            Route::get('by-flock/{flock}', [EggTraceabilityController::class, 'byFlock'])->name('by-flock');
            Route::get('by-packaging/{packaging}', [EggTraceabilityController::class, 'byPackage'])->name('by-package');
            Route::get('qr/{code}', [EggTraceabilityController::class, 'showByQrCode'])->name('qr');
            Route::get('/', [EggTraceabilityController::class, 'index'])->name('index');
        });
    
        // ============================================
        // 17. Egg Dashboard (BI / KPIs)
        // ============================================
        Route::prefix('egg-dashboard')->name('egg-dashboard.')->group(function () {
            Route::get('/', [EggDashboardController::class, 'index'])->name('index');
            Route::get('production-stats', [EggDashboardController::class, 'productionStats'])->name('production-stats');
            Route::get('mortality-stats', [EggDashboardController::class, 'mortalityStats'])->name('mortality-stats');
            Route::get('inventory-stats', [EggDashboardController::class, 'inventoryStats'])->name('inventory-stats');
            Route::get('financial-stats', [EggDashboardController::class, 'financialStats'])->name('financial-stats');
            Route::get('realtime-alerts', [EggDashboardController::class, 'realtimeAlerts'])->name('realtime-alerts');
        });
    
        // ============================================
        // 18. Egg KPIs (Indicators)
        // ============================================
        Route::prefix('egg-kpis')->name('egg-kpis.')->group(function () {
            Route::get('laying-rate', [EggKpiController::class, 'layingRate'])->name('laying-rate');
            Route::get('mortality-rate', [EggKpiController::class, 'mortalityRate'])->name('mortality-rate');
            Route::get('feed-conversion', [EggKpiController::class, 'feedConversion'])->name('feed-conversion');
            Route::get('laying-curve', [EggKpiController::class, 'layingCurve'])->name('laying-curve');
            Route::get('house-ranking', [EggKpiController::class, 'houseRanking'])->name('house-ranking');
            Route::get('cost-per-dozen', [EggKpiController::class, 'costPerDozen'])->name('cost-per-dozen');
            Route::get('reject-rate', [EggKpiController::class, 'rejectRate'])->name('reject-rate');
            Route::get('efficiency-index', [EggKpiController::class, 'efficiencyIndex'])->name('efficiency-index');
        });
    
        // ============================================
        // 19. Egg Reports
        // ============================================
        Route::prefix('egg-reports')->name('egg-reports.')->group(function () {
            Route::get('export-excel/{report}', [EggReportController::class, 'exportExcel'])->name('export-excel');
            Route::get('export-pdf/{report}/{format?}', [EggReportController::class, 'exportPdf'])->name('export-pdf');
            Route::get('daily-production', [EggReportController::class, 'dailyProduction'])->name('daily-production');
            Route::get('rejects', [EggReportController::class, 'rejects'])->name('rejects');
            Route::get('inventory', [EggReportController::class, 'inventory'])->name('inventory');
            Route::get('sanitary', [EggReportController::class, 'sanitary'])->name('sanitary');
            Route::get('traceability', [EggReportController::class, 'traceability'])->name('traceability');
            Route::get('vaccination', [EggReportController::class, 'vaccination'])->name('vaccination');
            Route::get('financial', [EggReportController::class, 'financial'])->name('financial');
        });
    
        // ============================================
        // 20. Egg Alerts
        // ============================================
        Route::prefix('egg-alerts')->name('egg-alerts.')->group(function () {
            Route::get('unread-count', [EggAlertController::class, 'unreadCount'])->name('unread-count');
            Route::post('bulk-mark-read', [EggAlertController::class, 'bulkMarkAsRead'])->name('bulk-mark-read');
            Route::get('trigger-test', [EggAlertController::class, 'triggerTestAlert'])->name('trigger-test');
            Route::get('/', [EggAlertController::class, 'index'])->name('index');
            Route::get('{egg_alert}', [EggAlertController::class, 'show'])->name('show');
            Route::post('{egg_alert}/mark-as-read', [EggAlertController::class, 'markAsRead'])->name('mark-as-read');
            Route::post('{egg_alert}/mark-as-resolved', [EggAlertController::class, 'markAsResolved'])->name('mark-as-resolved');
        });
    
        // ============================================
        // 21. Reject Reasons (Configuration)
        // ============================================
        Route::get('reject-reasons-all', [RejectReasonController::class, 'getAll'])->name('reject-reasons.all');
        Route::post('reject-reasons/{reject_reason}/toggle-status', [RejectReasonController::class, 'toggleStatus'])->name('reject-reasons.toggle-status');
        Route::resource('reject-reasons', RejectReasonController::class);
    });
});




// Portal de pedidos de ovos (clientes)
Route::prefix('portal/ovos')->group(function () {
    Route::post('login', [EggCustomerPortalController::class, 'login']);
    Route::post('logout', [EggCustomerPortalController::class, 'logout']);

    Route::middleware('egg.customer.portal')->group(function () {
        Route::get('me', [EggCustomerPortalController::class, 'me']);
        Route::get('categories', [EggCustomerPortalController::class, 'categories']);
        Route::get('orders', [EggCustomerPortalController::class, 'orders']);
        Route::post('orders', [EggCustomerPortalController::class, 'storeOrder']);
    });
});

Route::get('portal/pedidos-ovos/{any?}', function () {
    return view('portal.egg.app');
})->where('any', '.*');

//Ultima rota

Route::get('{view}', ApplicationController::class)->where('view','(.*)')->middleware('auth');

