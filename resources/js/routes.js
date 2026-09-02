

//IMPORT COMPONENT FOR ADMIN ROUTES
import Login from './pages/auth/Login.vue';
import DashboardAdmin from './components/DashboardAdmin.vue';

import IndexUsers from './pages/admin/users/IndexUsers.vue';
import CreateUsers from './pages/admin/users/CreateUsers.vue';
import ShowUsers from './pages/admin/users/ShowUsers.vue';
import EditUsers from './pages/admin/users/EditUsers.vue';

import IndexFees from './pages/admin/fees/IndexFees.vue';
import CreateFees from './pages/admin/fees/CreateFees.vue';
import ShowFees from './pages/admin/fees/ShowFees.vue';
import EditFees from './pages/admin/fees/EditFees.vue';

import IndexFeeInvoice from './pages/admin/feeinvoice/IndexFeeInvoice.vue';
import CreateFeeInvoice from './pages/admin/feeinvoice/CreateFeeInvoice.vue';
import ShowFeeInvoice from './pages/admin/feeinvoice/ShowFeeInvoice.vue';
import EditFeeInvoice from './pages/admin/feeinvoice/EditFeeInvoice.vue';

import IndexEnergyInvoice from './pages/admin/energyinvoice/IndexEnergyInvoice.vue';
import CreateEnergyInvoice from './pages/admin/energyinvoice/CreateEnergyInvoice.vue';
import ShowEnergyInvoice from './pages/admin/energyinvoice/ShowEnergyInvoice.vue';
import ShowEnergyInvoiceClient from './pages/admin/energyinvoice/ShowEnergyInvoiceClient.vue';

import EditEnergyInvoice from './pages/admin/energyinvoice/EditEnergyInvoice.vue';

import IndexDriver from './pages/admin/driver/IndexDriver.vue';
import CreateDriver from './pages/admin/driver/CreateDriver.vue';
import ShowDriver from './pages/admin/driver/ShowDriver.vue';
import EditDriver from './pages/admin/driver/EditDriver.vue';

import IndexMeeting from './pages/admin/meeting/IndexMeeting.vue';
import CreateMeeting from './pages/admin/meeting/CreateMeeting.vue';
import ShowMeeting from './pages/admin/meeting/ShowMeeting.vue';
import EditMeeting from './pages/admin/meeting/EditMeeting.vue';


import IndexMeetingTask from './pages/admin/meetingtask/IndexMeetingTask.vue';
import CreateMeetingTask from './pages/admin/meetingtask/CreateMeetingTask.vue';
import ShowMeetingTask from './pages/admin/meetingtask/ShowMeetingTask.vue';
import EditMeetingTask from './pages/admin/meetingtask/EditMeetingTask.vue';

import IndexMeetingParticipant from './pages/admin/meetingparticipant/IndexMeetingParticipant.vue';
import ShowMeetingParticipant from './pages/admin/meetingparticipant/ShowMeetingParticipant.vue';

import IndexMeetingType from './pages/admin/meetingtype/IndexMeetingType.vue';
import CreateMeetingType from './pages/admin/meetingtype/CreateMeetingType.vue';
import ShowMeetingType from './pages/admin/meetingtype/ShowMeetingType.vue';
import EditMeetingType from './pages/admin/meetingtype/EditMeetingType.vue';

import IndexLogisticDestination from './pages/admin/logisticdestinations/IndexLogisticDestination.vue';
import CreateLogisticDestination from './pages/admin/logisticdestinations/CreateLogisticDestination.vue';
import ShowLogisticDestination from './pages/admin/logisticdestinations/ShowLogisticDestination.vue';
import EditLogisticDestination from './pages/admin/logisticdestinations/EditLogisticDestination.vue';

import IndexLogisticTrip from './pages/admin/logistictrips/IndexLogisticTrip.vue';
import CreateLogisticTrip from './pages/admin/logistictrips/CreateLogisticTrip.vue';
import ShowLogisticTrip from './pages/admin/logistictrips/ShowLogisticTrip.vue';
import EditLogisticTrip from './pages/admin/logistictrips/EditLogisticTrip.vue';

import IndexLogisticCustomer from './pages/admin/logisticcustomer/IndexLogisticCustomer.vue';
import CreateLogisticCustomer from './pages/admin/logisticcustomer/CreateLogisticCustomer.vue';
import ShowLogisticCustomer from './pages/admin/logisticcustomer/ShowLogisticCustomer.vue';
import EditLogisticCustomer from './pages/admin/logisticcustomer/EditLogisticCustomer.vue';

import IndexLogisticQuotation from './pages/admin/logisticquotation/IndexLogisticQuotation.vue';
import CreateLogisticQuotation from './pages/admin/logisticquotation/CreateLogisticQuotation.vue';
import ShowLogisticQuotation from './pages/admin/logisticquotation/ShowLogisticQuotation.vue';
import EditLogisticQuotation from './pages/admin/logisticquotation/EditLogisticQuotation.vue';

import IndexAreas from './pages/admin/areas/IndexAreas.vue';
import CreateAreas from './pages/admin/areas/CreateAreas.vue';
import ShowAreas from './pages/admin/areas/ShowAreas.vue';
import EditAreas from './pages/admin/areas/EditAreas.vue';

import IndexTypeDocuments from './pages/admin/typedocuments/IndexTypeDocuments.vue';
import CreateTypeDocuments from './pages/admin/typedocuments/CreateTypeDocuments.vue';
import ShowTypeDocuments from './pages/admin/typedocuments/ShowTypeDocuments.vue';
import EditTypeDocuments from './pages/admin/typedocuments/EditTypeDocuments.vue';

import IndexTrips from './pages/admin/trips/IndexTrips.vue';
import CreateTrips from './pages/admin/trips/CreateTrips.vue';
import ShowTrips from './pages/admin/trips/ShowTrips.vue';
import EditTrips from './pages/admin/trips/EditTrips.vue';

import IndexDocuments from './pages/admin/documents/IndexDocuments.vue';
import CreateDocuments from './pages/admin/documents/CreateDocuments.vue';
import ShowDocuments from './pages/admin/documents/ShowDocuments.vue';
import EditDocuments from './pages/admin/documents/EditDocuments.vue';


import IndexSuppliers from './pages/admin/suppliers/IndexSuppliers.vue';
import CreateSuppliers from './pages/admin/suppliers/CreateSuppliers.vue';
import ShowSuppliers from './pages/admin/suppliers/ShowSuppliers.vue';
import EditSuppliers from './pages/admin/suppliers/EditSuppliers.vue';

import IndexShifts from './pages/admin/shifts/IndexShifts.vue';
import CreateShifts from './pages/admin/shifts/CreateShifts.vue';
import ShowShifts from './pages/admin/shifts/ShowShifts.vue';
import EditShifts from './pages/admin/shifts/EditShifts.vue';


import IndexTypeEquipments from './pages/admin/type_equipments/IndexTypeEquipments.vue';
import CreateTypeEquipments from './pages/admin/type_equipments/CreateTypeEquipments.vue';
import ShowTypeEquipments from './pages/admin/type_equipments/ShowTypeEquipments.vue';
import EditTypeEquipments from './pages/admin/type_equipments/EditTypeEquipments.vue';

import ShowFleet from './pages/admin/type_equipments/fleet/ShowFleet.vue';

import Calendar from './pages/admin/task_mcscr/Calendar.vue';

import CalendarMeetingTask from './pages/admin/meetingtask/Calendar.vue';

import MaintenanceCalendar from './pages/operator_maintenance/mcscr/Calendar.vue';

import DestinationCalendar from './pages/admin_destination/task_mcscr/Calendar.vue';



import IndexJobTask from './pages/admin/jobtasks/IndexJobTask.vue';
import CreateJobTask from './pages/admin/jobtasks/CreateJobTask.vue';
import ShowJobTask from './pages/admin/jobtasks/ShowJobTask.vue';
import EditJobTask from './pages/admin/jobtasks/EditJobTask.vue';

import IndexDestinations from './pages/admin/destinations/IndexDestinations.vue';
import CreateDestinations from './pages/admin/destinations/CreateDestinations.vue';
import ShowDestinations from './pages/admin/destinations/ShowDestinations.vue';
import EditDestinations from './pages/admin/destinations/EditDestinations.vue';
import ShowDestinationFleet from './pages/admin/destinations/fleet/ShowDestinationFleet.vue';

import IndexEntryGuides from './pages/admin/entry-guides/IndexEntryGuides.vue';
import CreateEntryGuide from './pages/admin/entry-guides/CreateEntryGuide.vue';
import ShowEntryGuide from './pages/admin/entry-guides/ShowEntryGuide.vue';
import EditEntryGuide from './pages/admin/entry-guides/EditEntryGuide.vue';

import IndexCenterCost from './pages/admin/centercost/IndexCenterCost.vue';
import CreateCenterCost from './pages/admin/centercost/CreateCenterCost.vue';
import ShowCenterCost from './pages/admin/centercost/ShowCenterCost.vue';
import EditCenterCost from './pages/admin/centercost/EditCenterCost.vue';

import ShowAccount from './pages/admin/centercost/account/ShowAccount.vue';
import EditAccount from './pages/admin/centercost/account/EditAccount.vue';

import ShowComponent from './pages/admin/equipments/components/ShowComponent.vue';
import EditComponent from './pages/admin/equipments/components/EditComponent.vue';

import ShowEquipmentSubComponent from './pages/admin/equipments/components/subcomponents/ShowEquipmentSubComponent.vue';
import EditEquipmentSubComponent from './pages/admin/equipments/components/subcomponents/EditEquipmentSubComponent.vue';

import ShowTypeEquipmentComponent from './pages/admin/type_equipments/components/ShowTypeEquipmentComponent.vue';
import EditTypeEquipmentComponent from './pages/admin/type_equipments/components/EditTypeEquipmentComponent.vue';

import ShowTypeEquipmentSubComponent from './pages/admin/type_equipments/components/subcomponents/ShowTypeEquipmentSubComponent.vue';
import EditTypeEquipmentSubComponent from './pages/admin/type_equipments/components/subcomponents/EditTypeEquipmentSubComponent.vue';

import IndexEquipment from './pages/admin/equipments/IndexEquipment.vue';
import CreateEquipment from './pages/admin/equipments/CreateEquipment.vue';
import ShowEquipment from './pages/admin/equipments/ShowEquipment.vue';
import EditEquipment from './pages/admin/equipments/EditEquipment.vue';
import UploadEquipment from './pages/admin/equipments/UploadEquipment.vue';
import FileEquipment from './pages/admin/equipments/FileEquipment.vue';


import IndexTireAllocation from './pages/admin/tireallocation/IndexTireAllocation.vue';
import CreateTireAllocation from './pages/admin/tireallocation/CreateTireAllocation.vue';
import ShowTireAllocation from './pages/admin/tireallocation/ShowTireAllocation.vue';
import EditTireAllocation from './pages/admin/tireallocation/EditTireAllocation.vue';

import IndexTireLayouts from './pages/admin/tirelayouts/IndexTireLayouts.vue';
import CreateTireLayouts from './pages/admin/tirelayouts/CreateTireLayouts.vue';
import ShowTireLayouts from './pages/admin/tirelayouts/ShowTireLayouts.vue';
import EditTireLayouts from './pages/admin/tirelayouts/EditTireLayouts.vue';


import IndexInspection from './pages/admin/inspection/IndexInspection.vue';
import CreateInspection from './pages/admin/inspection/CreateInspection.vue';
import ShowInspection from './pages/admin/inspection/ShowInspection.vue';
import EditInspection from './pages/admin/inspection/EditInspection.vue';

import IndexGeneralInspection from './pages/admin/inspectiongeneral/IndexGeneralInspection.vue';
import CreateGeneralInspection from './pages/admin/inspectiongeneral/CreateGeneralInspection.vue';
import ShowGeneralInspection from './pages/admin/inspectiongeneral/ShowGeneralInspection.vue';
import EditGeneralInspection from './pages/admin/inspectiongeneral/EditGeneralInspection.vue';


import IndexReason from './pages/admin/mcscr/reason/IndexReason.vue';
import CreateReason from './pages/admin/mcscr/reason/CreateReason.vue';
import ShowReason from './pages/admin/mcscr/reason/ShowReason.vue';
import EditReason from './pages/admin/mcscr/reason/EditReason.vue';

import IndexCause from './pages/admin/mcscr/cause/IndexCause.vue';
import CreateCause from './pages/admin/mcscr/cause/CreateCause.vue';
import ShowCause from './pages/admin/mcscr/cause/ShowCause.vue';
import EditCause from './pages/admin/mcscr/cause/EditCause.vue';

import IndexSolution from './pages/admin/mcscr/solution/IndexSolution.vue';
import CreateSolution from './pages/admin/mcscr/solution/CreateSolution.vue';
import ShowSolution from './pages/admin/mcscr/solution/ShowSolution.vue';
import EditSolution from './pages/admin/mcscr/solution/EditSolution.vue';

import IndexConsequence from './pages/admin/mcscr/consequence/IndexConsequence.vue';
import CreateConsequence from './pages/admin/mcscr/consequence/CreateConsequence.vue';
import ShowConsequence from './pages/admin/mcscr/consequence/ShowConsequence.vue';
import EditConsequence from './pages/admin/mcscr/consequence/EditConsequence.vue';

import IndexRecommendation from './pages/admin/mcscr/recommendation/IndexRecommendation.vue';
import CreateRecommendation from './pages/admin/mcscr/recommendation/CreateRecommendation.vue';
import ShowRecommendation from './pages/admin/mcscr/recommendation/ShowRecommendation.vue';
import EditRecommendation from './pages/admin/mcscr/recommendation/EditRecommendation.vue';

import IndexMalfunction from './pages/admin/malfunction/IndexMalfunction.vue';
import CreateMalfunction from './pages/admin/malfunction/CreateMalfunction.vue';
import ShowMalfunction from './pages/admin/malfunction/ShowMalfunction.vue';
import EditMalfunction from './pages/admin/malfunction/EditMalfunction.vue';

import IndexMcscr from './pages/admin/mcscr/IndexMcscr.vue';
import CreateMcscr from './pages/admin/mcscr/CreateMcscr.vue';
import ShowMcscr from './pages/admin/mcscr/ShowMcscr.vue';
import EditMcscr from './pages/admin/mcscr/EditMcscr.vue';
import UploadMcscr from './pages/admin/mcscr/UploadMcscr.vue';

import IndexTaskMcscr from './pages/admin/task_mcscr/IndexTaskMcscr.vue';
import CreateTaskMcscr from './pages/admin/task_mcscr/CreateTaskMcscr.vue';
import ShowTaskMcscr from './pages/admin/task_mcscr/ShowTaskMcscr.vue';
import EditTaskMcscr from './pages/admin/task_mcscr/EditTaskMcscr.vue';


import IndexTask from './pages/admin/task/IndexTask.vue';
import CreateTask from './pages/admin/task/CreateTask.vue';
import ShowTask from './pages/admin/task/ShowTask.vue';
import EditTask from './pages/admin/task/EditTask.vue';

import IndexTaskPlan from './pages/admin/task_plans/IndexTaskPlan.vue';
import CreateTaskPlan from './pages/admin/task_plans/CreateTaskPlan.vue';
import ShowTaskPlan from './pages/admin/task_plans/ShowTaskPlan.vue';
import EditTaskPlan from './pages/admin/task_plans/EditTaskPlan.vue';

import ShowTasks from './pages/admin/task_plans/tasks/ShowTasks.vue';
import EditTasks from './pages/admin/task_plans/tasks/EditTasks.vue';

import IndexBrand from './pages/admin/brand/IndexBrand.vue';
import CreateBrand from './pages/admin/brand/CreateBrand.vue';
import ShowBrand from './pages/admin/brand/ShowBrand.vue';
import EditBrand from './pages/admin/brand/EditBrand.vue';

import IndexCategory from './pages/admin/category/IndexCategory.vue';
import CreateCategory from './pages/admin/category/CreateCategory.vue';
import ShowCategory from './pages/admin/category/ShowCategory.vue';
import EditCategory from './pages/admin/category/EditCategory.vue';

import IndexProduct from './pages/admin/product/IndexProduct.vue';
import CreateProduct from './pages/admin/product/CreateProduct.vue';
import ShowProduct from './pages/admin/product/ShowProduct.vue';
import EditProduct from './pages/admin/product/EditProduct.vue';

import IndexOperatorMaintenanceProduct from './pages/operator_maintenance/product/IndexOperatorMaintenanceProduct.vue';
import CreateOperatorMaintenanceProduct from './pages/operator_maintenance/product/CreateOperatorMaintenanceProduct.vue';
import ShowOperatorMaintenanceProduct from './pages/operator_maintenance/product/ShowOperatorMaintenanceProduct.vue';
import EditOperatorMaintenanceProduct from './pages/operator_maintenance/product/EditOperatorMaintenanceProduct.vue';


import IndexStockCenter from './pages/admin/stockcenter/IndexStockCenter.vue';
import CreateStockCenter from './pages/admin/stockcenter/CreateStockCenter.vue';
import ShowStockCenter from './pages/admin/stockcenter/ShowStockCenter.vue';
import EditStockCenter from './pages/admin/stockcenter/EditStockCenter.vue';

import IndexInventory from './pages/admin/inventory/IndexInventory.vue';
import CreateInventory from './pages/admin/inventory/CreateInventory.vue';
import ShowInventory from './pages/admin/inventory/ShowInventory.vue';
import EditInventory from './pages/admin/inventory/EditInventory.vue';


import IndexEntryNote from './pages/admin/entrynote/IndexEntryNote.vue';
import CreateEntryNote from './pages/admin/entrynote/CreateEntryNote.vue';
import ShowEntryNote from './pages/admin/entrynote/ShowEntryNote.vue';
import EditEntryNote from './pages/admin/entrynote/EditEntryNote.vue';

import IndexExitNote from './pages/admin/exitnote/IndexExitNote.vue';
import CreateExitNote from './pages/admin/exitnote/CreateExitNote.vue';
import ShowExitNote from './pages/admin/exitnote/ShowExitNote.vue';
import EditExitNote from './pages/admin/exitnote/EditExitNote.vue';

import IndexStockSupplier from './pages/admin/stocksupplier/IndexStockSupplier.vue';
import CreateStockSupplier from './pages/admin/stocksupplier/CreateStockSupplier.vue';
import ShowStockSupplier from './pages/admin/stocksupplier/ShowStockSupplier.vue';
import EditStockSupplier from './pages/admin/stocksupplier/EditStockSupplier.vue';


import IndexStockTransfer from './pages/admin/stocktransfer/IndexStockTransfer.vue';
import CreateStockTransfer from './pages/admin/stocktransfer/CreateStockTransfer.vue';
import ShowStockTransfer from './pages/admin/stocktransfer/ShowStockTransfer.vue';
import EditStockTransfer from './pages/admin/stocktransfer/EditStockTransfer.vue';

import IndexDepartment from './pages/admin/department/IndexDepartment.vue';
import CreateDepartment from './pages/admin/department/CreateDepartment.vue';
import ShowDepartment from './pages/admin/department/ShowDepartment.vue';
import EditDepartment from './pages/admin/department/EditDepartment.vue';

import IndexTechnician from './pages/admin/technician/IndexTechnician.vue';
import CreateTechnician from './pages/admin/technician/CreateTechnician.vue';
import ShowTechnician from './pages/admin/technician/ShowTechnician.vue';
import EditTechnician from './pages/admin/technician/EditTechnician.vue';

import IndexContractType from './pages/admin/contract-type/IndexContractType.vue';
import CreateContractType from './pages/admin/contract-type/CreateContractType.vue';
import ShowContractType from './pages/admin/contract-type/ShowContractType.vue';
import EditContractType from './pages/admin/contract-type/EditContractType.vue';

import IndexVacationPlan from './pages/admin/vacation-plans/IndexVacationPlan.vue';
import CreateVacationPlan from './pages/admin/vacation-plans/CreateVacationPlan.vue';
import ShowVacationPlan from './pages/admin/vacation-plans/ShowVacationPlan.vue';
import EditVacationPlan from './pages/admin/vacation-plans/EditVacationPlan.vue';

import IndexExpenseCategory from './pages/admin/expense-categories/Index.vue';

import IndexExpenses from './pages/admin/expenses/Index.vue';
import CreateExpense from './pages/admin/expenses/Create.vue';
import ShowExpense from './pages/admin/expenses/Show.vue';
import EditExpense from './pages/admin/expenses/Edit.vue';

import IndexFarms from './pages/admin/egg-module/farms/IndexFarms.vue';
import CreateFarms from './pages/admin/egg-module/farms/CreateFarms.vue';
import ShowFarms from './pages/admin/egg-module/farms/ShowFarms.vue';
import EditFarms from './pages/admin/egg-module/farms/EditFarms.vue';

import IndexHouses from './pages/admin/egg-module/houses/IndexHouses.vue';
import CreateHouses from './pages/admin/egg-module/houses/CreateHouses.vue';
import ShowHouses from './pages/admin/egg-module/houses/ShowHouses.vue';
import EditHouses from './pages/admin/egg-module/houses/EditHouses.vue';

import IndexFlocks from './pages/admin/egg-module/flocks/IndexFlocks.vue';
import CreateFlocks from './pages/admin/egg-module/flocks/CreateFlocks.vue';
import ShowFlocks from './pages/admin/egg-module/flocks/ShowFlocks.vue';
import EditFlocks from './pages/admin/egg-module/flocks/EditFlocks.vue';

import IndexLineages from './pages/admin/egg-module/lineages/IndexLineages.vue';
import CreateLineages from './pages/admin/egg-module/lineages/CreateLineages.vue';
import ShowLineages from './pages/admin/egg-module/lineages/ShowLineages.vue';
import EditLineages from './pages/admin/egg-module/lineages/EditLineages.vue';

import IndexDailyProduction from './pages/admin/egg-module/daily-production/IndexDailyProduction.vue';
import CreateDailyProduction from './pages/admin/egg-module/daily-production/CreateDailyProduction.vue';
import ShowDailyProduction from './pages/admin/egg-module/daily-production/ShowDailyProduction.vue';
import EditDailyProduction from './pages/admin/egg-module/daily-production/EditDailyProduction.vue';

import IndexMortality from './pages/admin/egg-module/mortality/IndexMortality.vue';
import CreateMortality from './pages/admin/egg-module/mortality/CreateMortality.vue';
import ShowMortality from './pages/admin/egg-module/mortality/ShowMortality.vue';
import EditMortality from './pages/admin/egg-module/mortality/EditMortality.vue';

import IndexVaccinationSchedule from './pages/admin/egg-module/vaccination-schedule/IndexVaccinationSchedule.vue';
import CreateVaccinationSchedule from './pages/admin/egg-module/vaccination-schedule/CreateVaccinationSchedule.vue';
import ShowVaccinationSchedule from './pages/admin/egg-module/vaccination-schedule/ShowVaccinationSchedule.vue';
import EditVaccinationSchedule from './pages/admin/egg-module/vaccination-schedule/EditVaccinationSchedule.vue';

import IndexVaccines from './pages/admin/egg-module/vaccines/IndexVaccines.vue';
import CreateVaccines from './pages/admin/egg-module/vaccines/CreateVaccines.vue';
import ShowVaccines from './pages/admin/egg-module/vaccines/ShowVaccines.vue';
import EditVaccines from './pages/admin/egg-module/vaccines/EditVaccines.vue';

import IndexEggExpenses from './pages/admin/egg-module/egg-expenses/IndexEggExpenses.vue';
import CreateEggExpenses from './pages/admin/egg-module/egg-expenses/CreateEggExpenses.vue';
import ShowEggExpenses from './pages/admin/egg-module/egg-expenses/ShowEggExpenses.vue';
import EditEggExpenses from './pages/admin/egg-module/egg-expenses/EditEggExpenses.vue';
import DashboardEggExpenses from './pages/admin/egg-module/egg-expenses/DashboardEggExpenses.vue';

import IndexEggClassifications from './pages/admin/egg-module/egg-classifications/IndexEggClassifications.vue';
import CreateEggClassifications from './pages/admin/egg-module/egg-classifications/CreateEggClassifications.vue';
import ShowEggClassifications from './pages/admin/egg-module/egg-classifications/ShowEggClassifications.vue';
import EditEggClassifications from './pages/admin/egg-module/egg-classifications/EditEggClassifications.vue';

import IndexEggInventory from './pages/admin/egg-module/egg-inventory/IndexEggInventory.vue';
import CreateEggInventory from './pages/admin/egg-module/egg-inventory/CreateEggInventory.vue';
import ShowEggInventory from './pages/admin/egg-module/egg-inventory/ShowEggInventory.vue';
import EditEggInventory from './pages/admin/egg-module/egg-inventory/EditEggInventory.vue';

import IndexRejectReasons from './pages/admin/egg-module/reject-reasons/IndexRejectReasons.vue';
import CreateRejectReasons from './pages/admin/egg-module/reject-reasons/CreateRejectReasons.vue';
import ShowRejectReasons from './pages/admin/egg-module/reject-reasons/ShowRejectReasons.vue';
import EditRejectReasons from './pages/admin/egg-module/reject-reasons/EditRejectReasons.vue';

import IndexEggCategories from './pages/admin/egg-module/egg-categories/IndexEggCategories.vue';
import CreateEggCategories from './pages/admin/egg-module/egg-categories/CreateEggCategories.vue';
import ShowEggCategories from './pages/admin/egg-module/egg-categories/ShowEggCategories.vue';
import EditEggCategories from './pages/admin/egg-module/egg-categories/EditEggCategories.vue';

import IndexEggs from './pages/admin/egg-module/eggs/IndexEggs.vue';
import CreateEggs from './pages/admin/egg-module/eggs/CreateEggs.vue';
import ShowEggs from './pages/admin/egg-module/eggs/ShowEggs.vue';
import EditEggs from './pages/admin/egg-module/eggs/EditEggs.vue';

import IndexPackaging from './pages/admin/egg-module/packaging/IndexPackaging.vue';
import CreatePackaging from './pages/admin/egg-module/packaging/CreatePackaging.vue';
import ShowPackaging from './pages/admin/egg-module/packaging/ShowPackaging.vue';
import EditPackaging from './pages/admin/egg-module/packaging/EditPackaging.vue';

import IndexEggOrders from './pages/admin/egg-module/egg-orders/IndexEggOrders.vue';
import CalendarEggOrders from './pages/admin/egg-module/egg-orders/CalendarEggOrders.vue';
import CreateEggOrders from './pages/admin/egg-module/egg-orders/CreateEggOrders.vue';
import ShowEggOrders from './pages/admin/egg-module/egg-orders/ShowEggOrders.vue';
import EditEggOrders from './pages/admin/egg-module/egg-orders/EditEggOrders.vue';

import IndexEggCustomers from './pages/admin/egg-module/egg-customers/IndexEggCustomers.vue';
import CreateEggCustomers from './pages/admin/egg-module/egg-customers/CreateEggCustomers.vue';
import ShowEggCustomers from './pages/admin/egg-module/egg-customers/ShowEggCustomers.vue';
import EditEggCustomers from './pages/admin/egg-module/egg-customers/EditEggCustomers.vue';

import PortalLogin from './pages/portal/egg/PortalLogin.vue';
import PortalOrders from './pages/portal/egg/PortalOrders.vue';

import IndexEggShipping from './pages/admin/egg-module/egg-shipping/IndexEggShipping.vue';
import CalendarEggShipping from './pages/admin/egg-module/egg-shipping/CalendarEggShipping.vue';
import CreateEggShipping from './pages/admin/egg-module/egg-shipping/CreateEggShipping.vue';
import ShowEggShipping from './pages/admin/egg-module/egg-shipping/ShowEggShipping.vue';
import EditEggShipping from './pages/admin/egg-module/egg-shipping/EditEggShipping.vue';
import IndexEggSeparation from './pages/admin/egg-module/egg-separation/IndexEggSeparation.vue';
import SeparateEggOrder from './pages/admin/egg-module/egg-separation/SeparateEggOrder.vue';

import IndexTraceability from './pages/admin/egg-module/traceability/IndexTraceability.vue';
import ShowTraceability from './pages/admin/egg-module/traceability/ShowTraceability.vue';
import EggDashboard from './pages/admin/egg-module/dashboard/EggDashboard.vue';

import IndexEggAlerts from './pages/admin/egg-module/egg-alerts/IndexEggAlerts.vue';
import ShowEggAlerts from './pages/admin/egg-module/egg-alerts/ShowEggAlerts.vue';

import KpiLayingRate from './pages/admin/egg-module/kpis/KpiLayingRate.vue';
import KpiMortalityRate from './pages/admin/egg-module/kpis/KpiMortalityRate.vue';
import KpiFeedConversion from './pages/admin/egg-module/kpis/KpiFeedConversion.vue';
import KpiLayingCurve from './pages/admin/egg-module/kpis/KpiLayingCurve.vue';
import KpiHouseRanking from './pages/admin/egg-module/kpis/KpiHouseRanking.vue';
import KpiCostPerDozen from './pages/admin/egg-module/kpis/KpiCostPerDozen.vue';

import ReportDailyProduction from './pages/admin/egg-module/reports/ReportDailyProduction.vue';
import ReportRejects from './pages/admin/egg-module/reports/ReportRejects.vue';
import ReportInventory from './pages/admin/egg-module/reports/ReportInventory.vue';
import ReportSanitary from './pages/admin/egg-module/reports/ReportSanitary.vue';
import ReportTraceability from './pages/admin/egg-module/reports/ReportTraceability.vue';

import IndexSalaryProcess from './pages/admin/salaryprocess/IndexSalaryProcess.vue';
import CreateSalaryProcess from './pages/admin/salaryprocess/CreateSalaryProcess.vue';
import ShowSalaryProcess from './pages/admin/salaryprocess/ShowSalaryProcess.vue';
import EditSalaryProcess from './pages/admin/salaryprocess/EditSalaryProcess.vue';

import IndexAbsences from './pages/admin/absences/IndexAbsences.vue';
import CreateAbsence from './pages/admin/absences/CreateAbsence.vue';
import ShowAbsence from './pages/admin/absences/ShowAbsence.vue';
import EditAbsence from './pages/admin/absences/EditAbsence.vue';

// Work Schedule (Escala de Trabalho)
import WorkScheduleDashboard from './pages/admin/work-schedule/Dashboard.vue';
import WorkScheduleIndex from './pages/admin/work-schedule/Index.vue';
import WorkScheduleCreate from './pages/admin/work-schedule/Create.vue';
import WorkScheduleShow from './pages/admin/work-schedule/Show.vue';
import WorkScheduleClientView from './pages/admin/work-schedule/ClientView.vue';
import ShiftIndex from './pages/admin/work-schedule/shifts/Index.vue';
import ShiftCreate from './pages/admin/work-schedule/shifts/Create.vue';
import ShiftEdit from './pages/admin/work-schedule/shifts/Edit.vue';

import IndexToolShop from './pages/admin/toolshop/IndexToolShop.vue';
import CreateToolShop from './pages/admin/toolshop/CreateToolShop.vue';
import ShowToolShop from './pages/admin/toolshop/ShowToolShop.vue';
import EditToolShop from './pages/admin/toolshop/EditToolShop.vue';

import IndexOperatorMaintenanceToolShop from './pages/operator_maintenance/toolshop/IndexOperatorMaintenanceToolShop.vue';
import CreateOperatorMaintenanceToolShop from './pages/operator_maintenance/toolshop/CreateOperatorMaintenanceToolShop.vue';
import ShowOperatorMaintenanceToolShop from './pages/operator_maintenance/toolshop/ShowOperatorMaintenanceToolShop.vue';
import EditOperatorMaintenanceToolShop from './pages/operator_maintenance/toolshop/EditOperatorMaintenanceToolShop.vue';

import UploadMcscrOperatorMaintenance from './pages/operator_maintenance/mcscr/UploadMcscrOperatorMaintenance.vue';

import IndexProfile from './pages/admin/profile/IndexProfile.vue';
import IndexProfileAdminDestination from './pages/admin_destination/profile/IndexProfileAdminDestination.vue';
import IndexProfileMaintenanceOperator from './pages/operator_maintenance/profile/IndexProfileMaintenanceOperator.vue';


import IndexStockRequest from './pages/admin/stockrequest/IndexStockRequest.vue';
import CreateStockRequest from './pages/admin/stockrequest/CreateStockRequest.vue';
import ShowStockRequest from './pages/admin/stockrequest/ShowStockRequest.vue';
import EditStockRequest from './pages/admin/stockrequest/EditStockRequest.vue';

import IndexTechnicianRequest from './pages/admin/technicianrequest/IndexTechnicianRequest.vue';
import CreateTechnicianRequest from './pages/admin/technicianrequest/CreateTechnicianRequest.vue';
import ShowTechnicianRequest from './pages/admin/technicianrequest/ShowTechnicianRequest.vue';
import EditTechnicianRequest from './pages/admin/technicianrequest/EditTechnicianRequest.vue';

import IndexToolRequest from './pages/admin/toolrequest/IndexToolRequest.vue';
import CreateToolRequest from './pages/admin/toolrequest/CreateToolRequest.vue';
import ShowToolRequest from './pages/admin/toolrequest/ShowToolRequest.vue';
import EditToolRequest from './pages/admin/toolrequest/EditToolRequest.vue';

import IndexNotifications from './pages/admin/notifications/IndexNotifications.vue';

import IndexHourDistance from './pages/admin/hourdistance/IndexHourDistance.vue';
import CreateHourDistance from './pages/admin/hourdistance/CreateHourDistance.vue';
import ShowHourDistance from './pages/admin/hourdistance/ShowHourDistance.vue';
import EditHourDistance from './pages/admin/hourdistance/EditHourDistance.vue';

import IndexFuel from './pages/admin/fuel/IndexFuel.vue';
import CreateFuel from './pages/admin/fuel/CreateFuel.vue';
import ShowFuel from './pages/admin/fuel/ShowFuel.vue';
import EditFuel from './pages/admin/fuel/EditFuel.vue';

import IndexWaterConsumption from './pages/admin/waterconsumption/IndexWaterConsumption.vue';
import CreateWaterConsumption from './pages/admin/waterconsumption/CreateWaterConsumption.vue';
import ShowWaterConsumption from './pages/admin/waterconsumption/ShowWaterConsumption.vue';
import EditWaterConsumption from './pages/admin/waterconsumption/EditWaterConsumption.vue';

import IndexEnergyConsumption from './pages/admin/energyconsumption/IndexEnergyConsumption.vue';
import CreateEnergyConsumption from './pages/admin/energyconsumption/CreateEnergyConsumption.vue';
import ShowEnergyConsumption from './pages/admin/energyconsumption/ShowEnergyConsumption.vue';
import EditEnergyConsumption from './pages/admin/energyconsumption/EditEnergyConsumption.vue';

import IndexShiftRequest from './pages/admin/shiftsrequest/IndexShiftRequest.vue';
import CreateShiftRequest from './pages/admin/shiftsrequest/CreateShiftRequest.vue';
import ShowShiftRequest from './pages/admin/shiftsrequest/ShowShiftRequest.vue';
import EditShiftRequest from './pages/admin/shiftsrequest/EditShiftRequest.vue';

import ShowGroup from './pages/admin/shifts/group/ShowGroup.vue';
import ShowRequests from './pages/admin/shifts/requests/ShowRequests.vue';
import EditRequests from './pages/admin/shifts/requests/EditRequests.vue';


import IndexScheduleWork from './pages/admin/schedulework/IndexScheduleWork.vue';
import CreateScheduleWork from './pages/admin/schedulework/CreateScheduleWork.vue';
import ShowScheduleWork from './pages/admin/schedulework/ShowScheduleWork.vue';
import EditScheduleWork from './pages/admin/schedulework/EditScheduleWork.vue';


import IndexQuotation from './pages/admin/quotation/IndexQuotation.vue';
import CreateQuotation from './pages/admin/quotation/CreateQuotation.vue';
import ShowQuotation from './pages/admin/quotation/ShowQuotation.vue';
import EditQuotation from './pages/admin/quotation/EditQuotation.vue';



//IMPORT ROUTER FOR ADMIN STOCK

import DashboardAdminStock from './components/DashboardAdminStock.vue';
import IndexNotificationsAdminStock from './pages/admin/notifications/IndexNotifications.vue';



//IMPORT ROUTER FOR OPERATOR MAINTENANCE

import DashboardOperatorMaintenance from './components/DashboardOperatorMaintenance.vue';
import IndexNotificationsOperatorMaintenance from './pages/operator_maintenance/notifications/IndexNotifications.vue';

import IndexMcscrOperatorMaintenance from './pages/operator_maintenance/mcscr/IndexMcscrOperatorMaintenance.vue';
import CreateMcscrOperatorMaintenance from './pages/operator_maintenance/mcscr/CreateMcscrOperatorMaintenance.vue';
import ShowMcscrOperatorMaintenance from './pages/operator_maintenance/mcscr/ShowMcscrOperatorMaintenance.vue';
import EditMcscrOperatorMaintenance from './pages/operator_maintenance/mcscr/EditMcscrOperatorMaintenance.vue';

import IndexTaskMcscrOperatorMaintenance from './pages/operator_maintenance/task_mcscr/IndexTaskMcscr.vue';
import CreateTaskMcscrOperatorMaintenance from './pages/operator_maintenance/task_mcscr/CreateTaskMcscr.vue';
import ShowTaskMcscrOperatorMaintenance from './pages/operator_maintenance/task_mcscr/ShowTaskMcscr.vue';
import EditTaskMcscrOperatorMaintenance from './pages/operator_maintenance/task_mcscr/EditTaskMcscr.vue';

import IndexInspectionOperatorMaintenance from './pages/operator_maintenance/inspection/IndexInspection.vue';
import CreateInspectionOperatorMaintenance from './pages/operator_maintenance/inspection/CreateInspection.vue';
import ShowInspectionOperatorMaintenance from './pages/operator_maintenance/inspection/ShowInspection.vue';
import EditInspectionOperatorMaintenance from './pages/operator_maintenance/inspection/EditInspection.vue';



//IMPORT ROUTER FOR MANAGER MAINTENANCE

import DashboardManagerMaintenance from './components/DashboardManagerMaintenance.vue';
import IndexNotificationsManagerMaintenance from './pages/manager_maintenance/notifications/IndexNotifications.vue';
import IndexMcscrManagerMaintenance from './pages/manager_maintenance/mcscr/IndexMcscrManagerMaintenance.vue';
import CreateMcscrManagerMaintenance from './pages/manager_maintenance/mcscr/CreateMcscrManagerMaintenance.vue';
import ShowMcscrManagerMaintenance from './pages/manager_maintenance/mcscr/ShowMcscrManagerMaintenance.vue';
import EditMcscrManagerMaintenance from './pages/manager_maintenance/mcscr/EditMcscrManagerMaintenance.vue';

import IndexReasonManagerMaintenance from './pages/manager_maintenance/mcscr/reason/IndexReason.vue';
import CreateReasonManagerMaintenance from './pages/manager_maintenance/mcscr/reason/CreateReason.vue';
import ShowReasonManagerMaintenance from './pages/manager_maintenance/mcscr/reason/ShowReason.vue';
import EditReasonManagerMaintenance from './pages/manager_maintenance/mcscr/reason/EditReason.vue';

import IndexCauseManagerMaintenance from './pages/manager_maintenance/mcscr/cause/IndexCause.vue';
import CreateCauseManagerMaintenance from './pages/manager_maintenance/mcscr/cause/CreateCause.vue';
import ShowCauseManagerMaintenance from './pages/manager_maintenance/mcscr/cause/ShowCause.vue';
import EditCauseManagerMaintenance from './pages/manager_maintenance/mcscr/cause/EditCause.vue';

import IndexSolutionManagerMaintenance from './pages/manager_maintenance/mcscr/solution/IndexSolution.vue';
import CreateSolutionManagerMaintenance from './pages/manager_maintenance/mcscr/solution/CreateSolution.vue';
import ShowSolutionManagerMaintenance from './pages/manager_maintenance/mcscr/solution/ShowSolution.vue';
import EditSolutionManagerMaintenance from './pages/manager_maintenance/mcscr/solution/EditSolution.vue';

import IndexConsequenceManagerMaintenance from './pages/manager_maintenance/mcscr/consequence/IndexConsequence.vue';
import CreateConsequenceManagerMaintenance from './pages/manager_maintenance/mcscr/consequence/CreateConsequence.vue';
import ShowConsequenceManagerMaintenance from './pages/manager_maintenance/mcscr/consequence/ShowConsequence.vue';
import EditConsequenceManagerMaintenance from './pages/manager_maintenance/mcscr/consequence/EditConsequence.vue';

import IndexRecommendationManagerMaintenance from './pages/manager_maintenance/mcscr/recommendation/IndexRecommendation.vue';
import CreateRecommendationManagerMaintenance from './pages/manager_maintenance/mcscr/recommendation/CreateRecommendation.vue';
import ShowRecommendationManagerMaintenance from './pages/manager_maintenance/mcscr/recommendation/ShowRecommendation.vue';
import EditRecommendationManagerMaintenance from './pages/manager_maintenance/mcscr/recommendation/EditRecommendation.vue';

import IndexEquipmentManagerMaintenance from './pages/manager_maintenance/equipments/IndexEquipment.vue';
import CreateEquipmentManagerMaintenance from './pages/manager_maintenance/equipments/CreateEquipment.vue';
import ShowEquipmentManagerMaintenance from './pages/manager_maintenance/equipments/ShowEquipment.vue';
import EditEquipmentManagerMaintenance from './pages/manager_maintenance/equipments/EditEquipment.vue';

import IndexHourDistanceManagerMaintenance from './pages/manager_maintenance/hourdistance/IndexHourDistance.vue';
import CreateHourDistanceManagerMaintenance from './pages/manager_maintenance/hourdistance/CreateHourDistance.vue';
import ShowHourDistanceManagerMaintenance from './pages/manager_maintenance/hourdistance/ShowHourDistance.vue';
import EditHourDistanceManagerMaintenance from './pages/manager_maintenance/hourdistance/EditHourDistance.vue';

import IndexFuelManagerMaintenance from './pages/manager_maintenance/fuel/IndexFuel.vue';
import CreateFuelManagerMaintenance from './pages/manager_maintenance/fuel/CreateFuel.vue';
import ShowFuelManagerMaintenance from './pages/manager_maintenance/fuel/ShowFuel.vue';
import EditFuelManagerMaintenance from './pages/manager_maintenance/fuel/EditFuel.vue';

import IndexTaskMcscrManagerMaintenance from './pages/manager_maintenance/task_mcscr/IndexTaskMcscr.vue';
import CreateTaskMcscrManagerMaintenance from './pages/manager_maintenance/task_mcscr/CreateTaskMcscr.vue';
import ShowTaskMcscrManagerMaintenance from './pages/manager_maintenance/task_mcscr/ShowTaskMcscr.vue';
import EditTaskMcscrManagerMaintenance from './pages/manager_maintenance/task_mcscr/EditTaskMcscr.vue';

import IndexTypeEquipmentsManagerMaintenance from './pages/manager_maintenance/type_equipments/IndexTypeEquipments.vue';
import CreateTypeEquipmentsManagerMaintenance from './pages/manager_maintenance/type_equipments/CreateTypeEquipments.vue';
import ShowTypeEquipmentsManagerMaintenance from './pages/manager_maintenance/type_equipments/ShowTypeEquipments.vue';
import EditTypeEquipmentsManagerMaintenance from './pages/manager_maintenance/type_equipments/EditTypeEquipments.vue';

import ShowFleetManagerMaintenance from './pages/manager_maintenance/type_equipments/fleet/ShowFleet.vue';

import IndexAreasManagerMaintenance from './pages/manager_maintenance/areas/IndexAreas.vue';
import CreateAreasManagerMaintenance from './pages/manager_maintenance/areas/CreateAreas.vue';
import ShowAreasManagerMaintenance from './pages/manager_maintenance/areas/ShowAreas.vue';
import EditAreasManagerMaintenance from './pages/manager_maintenance/areas/EditAreas.vue';


import IndexSuppliersManagerMaintenance from './pages/manager_maintenance/suppliers/IndexSuppliers.vue';
import CreateSuppliersManagerMaintenance from './pages/manager_maintenance/suppliers/CreateSuppliers.vue';
import ShowSuppliersManagerMaintenance from './pages/manager_maintenance/suppliers/ShowSuppliers.vue';
import EditSuppliersManagerMaintenance from './pages/manager_maintenance/suppliers/EditSuppliers.vue';


import IndexDestinationsManagerMaintenance from './pages/manager_maintenance/destinations/IndexDestinations.vue';
import CreateDestinationsManagerMaintenance from './pages/manager_maintenance/destinations/CreateDestinations.vue';
import ShowDestinationsManagerMaintenance from './pages/manager_maintenance/destinations/ShowDestinations.vue';
import EditDestinationsManagerMaintenance from './pages/manager_maintenance/destinations/EditDestinations.vue';

import IndexMalfunctionManagerMaintenance from './pages/manager_maintenance/malfunction/IndexMalfunction.vue';
import CreateMalfunctionManagerMaintenance from './pages/manager_maintenance/malfunction/CreateMalfunction.vue';
import ShowMalfunctionManagerMaintenance from './pages/manager_maintenance/malfunction/ShowMalfunction.vue';
import EditMalfunctionManagerMaintenance from './pages/manager_maintenance/malfunction/EditMalfunction.vue';

import IndexTaskManagerMaintenance from './pages/manager_maintenance/task/IndexTask.vue';
import CreateTaskManagerMaintenance from './pages/manager_maintenance/task/CreateTask.vue';
import ShowTaskManagerMaintenance from './pages/manager_maintenance/task/ShowTask.vue';
import EditTaskManagerMaintenance from './pages/manager_maintenance/task/EditTask.vue';

import ShowComponentManagerMaintenance from './pages/manager_maintenance/equipments/components/ShowComponent.vue';
import EditComponentManagerMaintenance from './pages/manager_maintenance/equipments/components/EditComponent.vue';

import ShowEquipmentSubComponentManagerMaintenance from './pages/manager_maintenance/equipments/components/subcomponents/ShowEquipmentSubComponent.vue';
import EditEquipmentSubComponentManagerMaintenance from './pages/manager_maintenance/equipments/components/subcomponents/EditEquipmentSubComponent.vue';

import ShowTypeEquipmentComponentManagerMaintenance from './pages/manager_maintenance/type_equipments/components/ShowTypeEquipmentComponent.vue';
import EditTypeEquipmentComponentManagerMaintenance from './pages/manager_maintenance/type_equipments/components/EditTypeEquipmentComponent.vue';

import ShowTypeEquipmentSubComponentManagerMaintenance from './pages/manager_maintenance/type_equipments/components/subcomponents/ShowTypeEquipmentSubComponent.vue';
import EditTypeEquipmentSubComponentManagerMaintenance from './pages/manager_maintenance/type_equipments/components/subcomponents/EditTypeEquipmentSubComponent.vue';



////IMPORT ROUTER FOR ADMIN DESTINATION
import DashboardAdminDestination from './components/DashboardAdminDestination.vue';

import IndexEquipmentAdminDestination from './pages/admin_destination/equipments/IndexEquipment.vue';
import CreateEquipmentAdminDestination from './pages/admin_destination/equipments/CreateEquipment.vue';
import ShowEquipmentAdminDestination from './pages/admin_destination/equipments/ShowEquipment.vue';
import EditEquipmentAdminDestination from './pages/admin_destination/equipments/EditEquipment.vue';

import IndexTypeEquipmentAdminDestination from './pages/admin_destination/type_equipments/IndexTypeEquipments.vue';
import CreateTypeEquipmentAdminDestination from './pages/admin_destination/type_equipments/CreateTypeEquipments.vue';
import ShowTypeEquipmentAdminDestination from './pages/admin_destination/type_equipments/ShowTypeEquipments.vue';
import EditTypeEquipmentAdminDestination from './pages/admin_destination/type_equipments/EditTypeEquipments.vue';

import IndexHourDistanceAdminDestination from './pages/admin_destination/hourdistance/IndexHourDistance.vue';
import CreateHourDistanceAdminDestination from './pages/admin_destination/hourdistance/CreateHourDistance.vue';
import ShowHourDistanceAdminDestination from './pages/admin_destination/hourdistance/ShowHourDistance.vue';
import EditHourDistanceAdminDestination from './pages/admin_destination/hourdistance/EditHourDistance.vue';

import IndexFuelAdminDestination from './pages/admin_destination/fuel/IndexFuel.vue';
import CreateFuelAdminDestination from './pages/admin_destination/fuel/CreateFuel.vue';
import ShowFuelAdminDestination from './pages/admin_destination/fuel/ShowFuel.vue';
import EditFuelAdminDestination from './pages/admin_destination/fuel/EditFuel.vue';

import IndexTaskMcscrAdminDestination from './pages/admin_destination/task_mcscr/IndexTaskMcscr.vue';
import CreateTaskMcscrAdminDestination from './pages/admin_destination/task_mcscr/CreateTaskMcscr.vue';
import ShowTaskMcscrAdminDestination from './pages/admin_destination/task_mcscr/ShowTaskMcscr.vue';
import EditTaskMcscrAdminDestination from './pages/admin_destination/task_mcscr/EditTaskMcscr.vue';

import IndexMcscrAdminDestination from './pages/admin_destination/mcscr/IndexMcscr.vue';
import CreateMcscrAdminDestination from './pages/admin_destination/mcscr/CreateMcscr.vue';
import ShowMcscrAdminDestination from './pages/admin_destination/mcscr/ShowMcscr.vue';
import EditMcscrAdminDestination from './pages/admin_destination/mcscr/EditMcscr.vue';

import IndexQuotationAdminDestination from './pages/admin_destination/quotation/IndexQuotation.vue';
import CreateQuotationAdminDestination from './pages/admin_destination/quotation/CreateQuotation.vue';
import ShowQuotationAdminDestination from './pages/admin_destination/quotation/ShowQuotation.vue';
import EditQuotationAdminDestination from './pages/admin_destination/quotation/EditQuotation.vue';

import IndexFeeInvoiceAdminDestination from './pages/admin_destination/feeinvoices/Index.vue';

import Home from './pages/Home.vue';


import AdminHome from './pages/admin/AdminHome.vue';







export default [

    //profiles

    {
        path: '/admin/home',
        name: 'admin.home',
        component: AdminHome,
    },

    {
        path: '/admin/profile',
        name: 'admin.profile',
        component: IndexProfile,
    },
    {
        path: '/admin/destination/profile',
        name: 'admin.destination.profile',
        component: IndexProfileAdminDestination,
    },
    {
        path: '/operator/maintenance/profile',
        name: 'operator.maintenance.profile',
        component: IndexProfileMaintenanceOperator,
    },

    //admins
    {
        path: '/admin/destination/dashboard',
        name: 'admin.destination.dashboard',
        component: DashboardAdminDestination,
    },

    //admins
    {
        path: '/admin/stock/dashboard',
        name: 'admin.stock.dashboard',
        component: DashboardAdminStock,
    },

     //manager maintenance
     {
        path: '/manager/maintenance/dashboard',
        name: 'manager.maintenance.dashboard',
        component: DashboardManagerMaintenance,
    },

    //operator maintenance
    {
        path: '/operator/maintenance/dashboard',
        name: 'operator.maintenance.dashboard',
        component: DashboardOperatorMaintenance,
    },
    //auth
    {
        path: '/login',
        name: 'users.login',
        component: Login,
    },

    // ADMIN ROUTES

    //admins
    {
        path: '/admin/dashboard',
        name: 'admin.dashboard',
        component: DashboardAdmin,
    },

    //users
    {
        path: '/admin/users',
        name: 'admin.users.index',
        component: IndexUsers,
    },
    {
        path: '/admin/users/create',
        name: 'admin.users.create',
        component: CreateUsers,
    },
    {
        path: '/admin/users/:id',
        name: 'admin.users.show',
        component: ShowUsers,
    },
    {
        path: '/admin/users/:id/edit',
        name: 'admin.users.edit',
        component: EditUsers,
    },

    //jobtasks
    {
        path: '/admin/jobtasks',
        name: 'admin.jobtasks.index',
        component: IndexJobTask,
    },
    {
        path: '/admin/jobtasks/create/:id',
        name: 'admin.jobtasks.create',
        component: CreateJobTask,
    },
    {
        path: '/admin/jobtasks/:id',
        name: 'admin.jobtasks.show',
        component: ShowJobTask,
    },
    {
        path: '/admin/jobtasks/:id/edit',
        name: 'admin.jobtasks.edit',
        component: EditJobTask,
    },


    //areas

    {
        path: '/admin/areas',
        name: 'admin.areas.index',
        component: IndexAreas,
    },
    {
        path: '/admin/areas/create',
        name: 'admin.areas.create',
        component: CreateAreas,
    },
    {
        path: '/admin/areas/:id',
        name: 'admin.areas.show',
        component: ShowAreas,
    },
    {
        path: '/admin/areas/:id/edit',
        name: 'admin.areas.edit',
        component: EditAreas,
    },

    //meeting

    {
        path: '/admin/meeting',
        name: 'admin.meeting.index',
        component: IndexMeeting,
    },
    {
        path: '/admin/meeting/create',
        name: 'admin.meeting.create',
        component: CreateMeeting,
    },
    {
        path: '/admin/meeting/:id',
        name: 'admin.meeting.show',
        component: ShowMeeting,
    },
    {
        path: '/admin/meeting/:id/edit',
        name: 'admin.meeting.edit',
        component: EditMeeting,
    },


    //meetingtype

    {
        path: '/admin/meetingtype',
        name: 'admin.meetingtype.index',
        component: IndexMeetingType,
    },
    {
        path: '/admin/meetingtype/create',
        name: 'admin.meetingtype.create',
        component: CreateMeetingType,
    },
    {
        path: '/admin/meetingtype/:id',
        name: 'admin.meetingtype.show',
        component: ShowMeetingType,
    },
    {
        path: '/admin/meetingtype/:id/edit',
        name: 'admin.meetingtype.edit',
        component: EditMeetingType,
    },

    //meetingtask

    {
        path: '/admin/meetingtask',
        name: 'admin.meetingtask.index',
        component: IndexMeetingTask,
    },
    {
        path: '/admin/meetingtask/create',
        name: 'admin.meetingtask.create',
        component: CreateMeetingTask,
    },
    {
        path: '/admin/meetingtask/:id',
        name: 'admin.meetingtask.show',
        component: ShowMeetingTask,
    },
    {
        path: '/admin/meetingtask/:id/edit',
        name: 'admin.meetingtask.edit',
        component: EditMeetingTask,
    },
    {
        path: '/admin/calendartask',
        name: 'admin.calendartask.edit',
        component: CalendarMeetingTask,
    },

    //meetingparticipant

    {
        path: '/admin/meetingparticipant',
        name: 'admin.meetingparticipant.index',
        component: IndexMeetingParticipant,
    },
    {
        path: '/admin/meetingparticipant/:id',
        name: 'admin.meetingparticipant.show',
        component: ShowMeetingParticipant,
    },
    

    //documents

    {
        path: '/admin/documents',
        name: 'admin.documents.index',
        component: IndexDocuments,
    },
    {
        path: '/admin/documents/create',
        name: 'admin.documents.create',
        component: CreateDocuments,
    },
    {
        path: '/admin/documents/:id',
        name: 'admin.documents.show',
        component: ShowDocuments,
    },
    {
        path: '/admin/documents/:id/edit',
        name: 'admin.documents.edit',
        component: EditDocuments,
    },

    //energyinvoice

    {
        path: '/admin/energyinvoice',
        name: 'admin.energyinvoice.index',
        component: IndexEnergyInvoice,
    },
    {
        path: '/admin/energyinvoice/create',
        name: 'admin.energyinvoice.create',
        component: CreateEnergyInvoice,
    },
    {
        path: '/admin/energyinvoice/:id',
        name: 'admin.energyinvoice.show',
        component: ShowEnergyInvoice,
    },
    {
        path: '/admin/energyinvoice/client/:id',
        name: 'admin.energyinvoiceclient.show',
        component: ShowEnergyInvoiceClient,
    },
    {
        path: '/admin/energyinvoice/:id/edit',
        name: 'admin.energyinvoice.edit',
        component: EditEnergyInvoice,
    },


    //drivers

    {
        path: '/admin/driver',
        name: 'admin.driver.index',
        component: IndexDriver,
    },
    {
        path: '/admin/driver/create',
        name: 'admin.driver.create',
        component: CreateDriver,
    },
    {
        path: '/admin/driver/:id',
        name: 'admin.driver.show',
        component: ShowDriver,
    },
    {
        path: '/admin/driver/:id/edit',
        name: 'admin.driver.edit',
        component: EditDriver,
    },

    //logisticdestination

    {
        path: '/admin/logisticdestination',
        name: 'admin.logisticdestination.index',
        component: IndexLogisticDestination,
    },
    {
        path: '/admin/logisticdestination/create',
        name: 'admin.logisticdestination.create',
        component: CreateLogisticDestination,
    },
    {
        path: '/admin/logisticdestination/:id',
        name: 'admin.logisticdestination.show',
        component: ShowLogisticDestination,
    },
    {
        path: '/admin/logisticdestination/:id/edit',
        name: 'admin.logisticdestination.edit',
        component: EditLogisticDestination,
    },

    //logisticdestination

    {
        path: '/admin/logistictrip',
        name: 'admin.logistictrip.index',
        component: IndexLogisticTrip,
    },
    {
        path: '/admin/logistictrip/create',
        name: 'admin.logistictrip.create',
        component: CreateLogisticTrip,
    },
    {
        path: '/admin/logistictrip/:id',
        name: 'admin.logistictrip.show',
        component: ShowLogisticTrip,
    },
    {
        path: '/admin/logistictrip/:id/edit',
        name: 'admin.logistictrip.edit',
        component: EditLogisticTrip,
    },

    //logisticcustomer

    {
        path: '/admin/logisticcustomer',
        name: 'admin.logisticcustomer.index',
        component: IndexLogisticCustomer,
    },
    {
        path: '/admin/logisticcustomer/create',
        name: 'admin.logisticcustomer.create',
        component: CreateLogisticCustomer,
    },
    {
        path: '/admin/logisticcustomer/:id',
        name: 'admin.logisticcustomer.show',
        component: ShowLogisticCustomer,
    },
    {
        path: '/admin/logisticcustomer/:id/edit',
        name: 'admin.logisticcustomer.edit',
        component: EditLogisticCustomer,
    },

    //logisticquotation

    {
        path: '/admin/logisticquotation',
        name: 'admin.logisticquotation.index',
        component: IndexLogisticQuotation,
    },
    {
        path: '/admin/logisticquotation/create',
        name: 'admin.logisticquotation.create',
        component: CreateLogisticQuotation,
    },
    {
        path: '/admin/logisticquotation/:id',
        name: 'admin.logisticquotation.show',
        component: ShowLogisticQuotation,
    },
    {
        path: '/admin/logisticquotation/:id/edit',
        name: 'admin.logisticquotation.edit',
        component: EditLogisticQuotation,
    },

    //typedocuments

    {
        path: '/admin/typedocuments',
        name: 'admin.typedocuments.index',
        component: IndexTypeDocuments,
    },
    {
        path: '/admin/typedocuments/create',
        name: 'admin.typedocuments.create',
        component: CreateTypeDocuments,
    },
    {
        path: '/admin/typedocuments/:id',
        name: 'admin.typedocuments.show',
        component: ShowTypeDocuments,
    },
    {
        path: '/admin/typedocuments/:id/edit',
        name: 'admin.typedocuments.edit',
        component: EditTypeDocuments,
    },

    //trips

    {
        path: '/admin/trips',
        name: 'admin.trips.index',
        component: IndexTrips,
    },
    {
        path: '/admin/trips/create',
        name: 'admin.trips.create',
        component: CreateTrips,
    },
    {
        path: '/admin/trips/:id',
        name: 'admin.trips.show',
        component: ShowTrips,
    },
    {
        path: '/admin/trips/:id/edit',
        name: 'admin.trips.edit',
        component: EditTrips,
    },

    //areas

    {
        path: '/admin/areas',
        name: 'admin.areas.index',
        component: IndexAreas,
    },
    {
        path: '/admin/areas/create',
        name: 'admin.areas.create',
        component: CreateAreas,
    },
    {
        path: '/admin/areas/:id',
        name: 'admin.areas.show',
        component: ShowAreas,
    },
    {
        path: '/admin/areas/:id/edit',
        name: 'admin.areas.edit',
        component: EditAreas,
    },

    //tire allocation

    {
        path: '/admin/tireallocations',
        name: 'admin.tireallocation.index',
        component: IndexTireAllocation,
    },
    {
        path: '/admin/tireallocations/create',
        name: 'admin.tireallocation.create',
        component: CreateTireAllocation,
    },
    {
        path: '/admin/tireallocations/:id',
        name: 'admin.tireallocation.show',
        component: ShowTireAllocation,
    },
    {
        path: '/admin/tireallocations/:id/edit',
        name: 'admin.tireallocation.edit',
        component: EditTireAllocation,
    },

    //tire layouts

    {
        path: '/admin/tirelayouts',
        name: 'admin.tirelayouts.index',
        component: IndexTireLayouts,
    },
    {
        path: '/admin/tirelayouts/create',
        name: 'admin.tirelayouts.create',
        component: CreateTireLayouts,
    },
    {
        path: '/admin/tirelayouts/:id',
        name: 'admin.tirelayouts.show',
        component: ShowTireLayouts,
    },
    {
        path: '/admin/tirelayouts/:id/edit',
        name: 'admin.tirelayouts.edit',
        component: EditTireLayouts,
    },

     //suppliers

     {
        path: '/admin/suppliers',
        name: 'admin.suppliers.index',
        component: IndexSuppliers,
    },
    {
        path: '/admin/suppliers/create',
        name: 'admin.suppliers.create',
        component: CreateSuppliers,
    },
    {
        path: '/admin/suppliers/:id',
        name: 'admin.suppliers.show',
        component: ShowSuppliers,
    },
    {
        path: '/admin/suppliers/:id/edit',
        name: 'admin.suppliers.edit',
        component: EditSuppliers,
    },

    //typeequipemnt

    {
        path: '/admin/type_equipments',
        name: 'admin.type_equipments.index',
        component: IndexTypeEquipments,
    },
    {
        path: '/admin/type_equipments/create',
        name: 'admin.type_equipments.create',
        component: CreateTypeEquipments,
    },
    {
        path: '/admin/type_equipments/:id',
        name: 'admin.type_equipments.show',
        component: ShowTypeEquipments,
    },
    {
        path: '/admin/type_equipments/:id/edit',
        name: 'admin.type_equipments.edit',
        component: EditTypeEquipments,
    },

    //fleet

    {
        path: '/admin/fleet/:id',
        name: 'admin.fleet.show',
        component: ShowFleet,
    },

    //typeequipment Components

    {
        path: '/admin/type_equipments/component/:id',
        name: 'admin.type_equipment.component.show',
        component: ShowTypeEquipmentComponent,
    },
    {
        path: '/admin/type_equipments/component/:id/edit',
        name: 'admin.type_equipment.component.edit',
        component: EditTypeEquipmentComponent,
    },

    //typeequipment SubComponents

    {
        path: '/admin/type_equipments/subcomponent/:id',
        name: 'admin.type_equipment.subcomponent.show',
        component: ShowTypeEquipmentSubComponent,
    },
    {
        path: '/admin/type_equipments/subcomponent/:id/edit',
        name: 'admin.type_equipment.subcomponent.edit',
        component: EditTypeEquipmentSubComponent,
    },


    //equipment SubComponents

    {
        path: '/admin/equipments/subcomponent/:id',
        name: 'admin.equipment.subcomponent.show',
        component: ShowEquipmentSubComponent,
    },
    {
        path: '/admin/equipments/subcomponent/:id/edit',
        name: 'admin.equipment.subcomponent.edit',
        component: EditEquipmentSubComponent,
    },

    //destinations
    {
        path: '/admin/destinations',
        name: 'admin.destinations.index',
        component: IndexDestinations,
    },
    {
        path: '/admin/destinations/create',
        name: 'admin.destinations.create',
        component: CreateDestinations,
    },
    {
        path: '/admin/destinations/:id',
        name: 'admin.destinations.show',
        component: ShowDestinations,
    },
    {
        path: '/admin/destinations/:id/edit',
        name: 'admin.destinations.edit',
        component: EditDestinations,
    },
    //fleet

    {
        path: '/admin/destinations/:destination_id/fleet/:fleet_id',
        name: 'admin.fleet.destination.show',
        component: ShowDestinationFleet,
    },

    //entry-guides
    {
        path: '/admin/entry-guides',
        name: 'admin.entry-guides.index',
        component: IndexEntryGuides,
    },
    {
        path: '/admin/entry-guides/create',
        name: 'admin.entry-guides.create',
        component: CreateEntryGuide,
    },
    {
        path: '/admin/entry-guides/:id',
        name: 'admin.entry-guides.show',
        component: ShowEntryGuide,
    },
    {
        path: '/admin/entry-guides/:id/edit',
        name: 'admin.entry-guides.edit',
        component: EditEntryGuide,
    },

    //centercost
    {
        path: '/admin/centercost',
        name: 'admin.centercost.index',
        component: IndexCenterCost,
    },
    {
        path: '/admin/centercost/create',
        name: 'admin.centercost.create',
        component: CreateCenterCost,
    },
    {
        path: '/admin/centercost/:id',
        name: 'admin.centercost.show',
        component: ShowCenterCost,
    },
    {
        path: '/admin/centercost/:id/edit',
        name: 'admin.centercost.edit',
        component: EditCenterCost,
    },

    //centercost account

    {
        path: '/admin/centercost/account/:id',
        name: 'admin.centercost.account.show',
        component: ShowAccount,
    },
    {
        path: '/admin/centercost/account/:id/edit',
        name: 'admin.centercost.account.edit',
        component: EditAccount,
    },


    //equipment Components

    {
        path: '/admin/equipments/component/:id',
        name: 'admin.equipment.component.show',
        component: ShowComponent,
    },
    {
        path: '/admin/equipments/component/:id/edit',
        name: 'admin.equipment.component.edit',
        component: EditComponent,
    },


    //equipment
    {
        path: '/admin/equipments',
        name: 'admin.equipments.index',
        component: IndexEquipment,
    },
    {
        path: '/admin/equipments/create',
        name: 'admin.equipments.create',
        component: CreateEquipment,
    },
    {
        path: '/admin/equipments/:id',
        name: 'admin.equipments.show',
        component: ShowEquipment,
    },
    {
        path: '/admin/equipments/:id/edit',
        name: 'admin.equipments.edit',
        component: EditEquipment,
    },
    {
        path: '/admin/equipments/:id/upload',
        name: 'admin.equipment.upload',
        component: UploadEquipment,
    },
    {
        path: '/admin/equipments/:id/file',
        name: 'admin.equipments.file',
        component: FileEquipment,
    },

    //reason

    {
        path: '/admin/mcscr/reasons',
        name: 'admin.reason.index',
        component: IndexReason,
    },
    {
        path: '/admin/mcscr/reasons/create',
        name: 'admin.reason.create',
        component: CreateReason,
    },
    {
        path: '/admin/mcscr/reasons/:id',
        name: 'admin.reason.show',
        component: ShowReason,
    },
    {
        path: '/admin/mcscr/reasons/:id/edit',
        name: 'admin.reason.edit',
        component: EditReason,
    },


    //cause

    {
        path: '/admin/mcscr/causes',
        name: 'admin.cause.index',
        component: IndexCause,
    },
    {
        path: '/admin/mcscr/causes/create',
        name: 'admin.cause.create',
        component: CreateCause,
    },
    {
        path: '/admin/mcscr/causes/:id',
        name: 'admin.cause.show',
        component: ShowCause,
    },
    {
        path: '/admin/mcscr/causes/:id/edit',
        name: 'admin.cause.edit',
        component: EditCause,
    },

        //solution

    {
        path: '/admin/mcscr/solutions',
        name: 'admin.solution.index',
        component: IndexSolution,
    },
    {
        path: '/admin/mcscr/solutions/create',
        name: 'admin.solution.create',
        component: CreateSolution,
    },
    {
        path: '/admin/mcscr/solutions/:id',
        name: 'admin.solution.show',
        component: ShowSolution,
    },
    {
        path: '/admin/mcscr/solutions/:id/edit',
        name: 'admin.solution.edit',
        component: EditSolution,
    },

     //consequence

     {
        path: '/admin/mcscr/consequences',
        name: 'admin.consequence.index',
        component: IndexConsequence,
    },
    {
        path: '/admin/mcscr/consequences/create',
        name: 'admin.consequence.create',
        component: CreateConsequence,
    },
    {
        path: '/admin/mcscr/consequences/:id',
        name: 'admin.consequence.show',
        component: ShowConsequence,
    },
    {
        path: '/admin/mcscr/consequences/:id/edit',
        name: 'admin.consequence.edit',
        component: EditConsequence,
    },
    
    //recommendation

    {
        path: '/admin/mcscr/recommendations',
        name: 'admin.recommendation.index',
        component: IndexRecommendation,
    },
    {
        path: '/admin/mcscr/recommendations/create',
        name: 'admin.recommendation.create',
        component: CreateRecommendation,
    },
    {
        path: '/admin/mcscr/recommendations/:id',
        name: 'admin.recommendation.show',
        component: ShowRecommendation,
    },
    {
        path: '/admin/mcscr/recommendations/:id/edit',
        name: 'admin.recommendation.edit',
        component: EditRecommendation,
    },

    //malfunction

    {
        path: '/admin/malfunctions',
        name: 'admin.malfunction.index',
        component: IndexMalfunction,
    },
    {
        path: '/admin/malfunctions/create',
        name: 'admin.malfunction.create',
        component: CreateMalfunction,
    },
    {
        path: '/admin/malfunctions/:id',
        name: 'admin.malfunction.show',
        component: ShowMalfunction,
    },
    {
        path: '/admin/malfunctions/:id/edit',
        name: 'admin.malfunction.edit',
        component: EditMalfunction,
    },

    //mcscr

    {
        path: '/admin/mcscr',
        name: 'admin.mcscr.index',
        component: IndexMcscr,
    },
    {
        path: '/admin/mcscr/create',
        name: 'admin.mcscr.create',
        component: CreateMcscr,
    },
    {
        path: '/admin/mcscr/:id',
        name: 'admin.mcscr.show',
        component: ShowMcscr,
    },
    {
        path: '/admin/mcscr/:id/edit',
        name: 'admin.mcscr.edit',
        component: EditMcscr,
    },
    {
        path: '/admin/mcscr/:id/upload',
        name: 'admin.mcscr.upload',
        component: UploadMcscr,
    },

    

    
     //taskmcscr

     {
        path: '/admin/taskmcscr',
        name: 'admin.taskmcscr.index',
        component: IndexTaskMcscr,
    },
    {
        path: '/admin/taskmcscr/create',
        name: 'admin.taskmcscr.create',
        component: CreateTaskMcscr,
    },
    {
        path: '/admin/taskmcscr/:id',
        name: 'admin.taskmcscr.show',
        component: ShowTaskMcscr,
    },
    {
        path: '/admin/taskmcscr/:id/edit',
        name: 'admin.taskmcscr.edit',
        component: EditTaskMcscr,
    },
    


    //task

    {
        path: '/admin/tasks',
        name: 'admin.task.index',
        component: IndexTask,
    },
    {
        path: '/admin/tasks/create',
        name: 'admin.task.create',
        component: CreateTask,
    },
    {
        path: '/admin/tasks/:id',
        name: 'admin.task.show',
        component: ShowTask,
    },
    {
        path: '/admin/tasks/:id/edit',
        name: 'admin.task.edit',
        component: EditTask,
    },

    //taskplans

    {
        path: '/admin/taskplans',
        name: 'admin.taskplan.index',
        component: IndexTaskPlan,
    },
    {
        path: '/admin/taskplans/create',
        name: 'admin.taskplan.create',
        component: CreateTaskPlan,
    },
    {
        path: '/admin/taskplans/:id',
        name: 'admin.taskplan.show',
        component: ShowTaskPlan,
    },
    {
        path: '/admin/taskplans/:id/edit',
        name: 'admin.taskplan.edit',
        component: EditTaskPlan,
    },


    //taskplanstask

    {
        path: '/admin/taskplans/tasks/:id',
        name: 'admin.taskplans.tasks.show',
        component: ShowTasks,
    },
    {
        path: '/admin/taskplans/tasks/:id/edit',
        name: 'admin.taskplans.tasks.edit',
        component: EditTasks,
    },

    //brands
    {
        path: '/admin/brands',
        name: 'admin.brands.index',
        component: IndexBrand,
    },
    {
        path: '/admin/brands/create',
        name: 'admin.brands.create',
        component: CreateBrand,
    },
    {
        path: '/admin/brands/:id',
        name: 'admin.brands.show',
        component: ShowBrand,
    },
    {
        path: '/admin/brands/:id/edit',
        name: 'admin.brands.edit',
        component: EditBrand,
    },

    //categories
    {
        path: '/admin/categories',
        name: 'admin.categories.index',
        component: IndexCategory,
    },
    {
        path: '/admin/categories/create',
        name: 'admin.categories.create',
        component: CreateCategory,
    },
    {
        path: '/admin/categories/:id',
        name: 'admin.categories.show',
        component: ShowCategory,
    },
    {
        path: '/admin/categories/:id/edit',
        name: 'admin.categories.edit',
        component: EditCategory,
    },

    //products
    {
        path: '/admin/products',
        name: 'admin.products.index',
        component: IndexProduct,
    },
    {
        path: '/admin/products/create',
        name: 'admin.products.create',
        component: CreateProduct,
    },
    {
        path: '/admin/products/:id',
        name: 'admin.products.show',
        component: ShowProduct,
    },
    {
        path: '/admin/products/:id/edit',
        name: 'admin.products.edit',
        component: EditProduct,
    },

        //products operator maintenance
        {
            path: '/operator/maintenance/products',
            name: 'operator.maintenance.products.index',
            component: IndexOperatorMaintenanceProduct,
        },
        {
            path: '/operator/maintenance/products/create',
            name: 'operator.maintenance.products.create',
            component: CreateOperatorMaintenanceProduct,
        },
        {
            path: '/operator/maintenance/products/:id',
            name: 'operator.maintenance.products.show',
            component: ShowOperatorMaintenanceProduct,
        },
        {
            path: '/operator/maintenance/products/:id/edit',
            name: 'operator.maintenance.products.edit',
            component: EditOperatorMaintenanceProduct,
        },


    //stockcenters
    {
        path: '/admin/stockcenters',
        name: 'admin.stockcenters.index',
        component: IndexStockCenter,
    },
    {
        path: '/admin/stockcenters/create',
        name: 'admin.stockcenters.create',
        component: CreateStockCenter,
    },
    {
        path: '/admin/stockcenters/:id',
        name: 'admin.stockcenters.show',
        component: ShowStockCenter,
    },
    {
        path: '/admin/stockcenters/:id/edit',
        name: 'admin.stockcenters.edit',
        component: EditStockCenter,
    },

     //inventories
     {
        path: '/admin/inventories',
        name: 'admin.inventory.index',
        component: IndexInventory,
    },
    {
        path: '/admin/inventories/create',
        name: 'admin.inventory.create',
        component: CreateInventory,
    },
    {
        path: '/admin/inventories/:id',
        name: 'admin.inventory.show',
        component: ShowInventory,
    },
    {
        path: '/admin/inventories/:id/edit',
        name: 'admin.inventory.edit',
        component: EditInventory,
    },


    //exitnote
    {
        path: '/admin/exitnotes',
        name: 'admin.exitnote.index',
        component: IndexExitNote,
    },
    {
        path: '/admin/exitnotes/create',
        name: 'admin.exitnote.create',
        component: CreateExitNote,
    },
    {
        path: '/admin/exitnotes/:id',
        name: 'admin.exitnote.show',
        component: ShowExitNote,
    },
    {
        path: '/admin/exitnotes/:id/edit',
        name: 'admin.exitnote.edit',
        component: EditExitNote,
    },


    //entrynote
    {
        path: '/admin/entrynotes',
        name: 'admin.entrynote.index',
        component: IndexEntryNote,
    },
    {
        path: '/admin/entrynotes/create',
        name: 'admin.entrynote.create',
        component: CreateEntryNote,
    },
    {
        path: '/admin/entrynotes/:id',
        name: 'admin.entrynote.show',
        component: ShowEntryNote,
    },
    {
        path: '/admin/entrynotes/:id/edit',
        name: 'admin.entrynote.edit',
        component: EditEntryNote,
    },


    //stocksupplier
    {
        path: '/admin/stocksuppliers',
        name: 'admin.stocksupplier.index',
        component: IndexStockSupplier,
    },
    {
        path: '/admin/stocksuppliers/create',
        name: 'admin.stocksupplier.create',
        component: CreateStockSupplier,
    },
    {
        path: '/admin/stocksuppliers/:id',
        name: 'admin.stocksupplier.show',
        component: ShowStockSupplier,
    },
    {
        path: '/admin/stocksuppliers/:id/edit',
        name: 'admin.stocksupplier.edit',
        component: EditStockSupplier,
    },

    //stocktransfer
    {
        path: '/admin/stocktransfers',
        name: 'admin.stocktransfer.index',
        component: IndexStockTransfer,
    },
    {
        path: '/admin/stocktransfers/create',
        name: 'admin.stocktransfer.create',
        component: CreateStockTransfer,
    },
    {
        path: '/admin/stocktransfers/:id',
        name: 'admin.stocktransfer.show',
        component: ShowStockTransfer,
    },
    {
        path: '/admin/stocktransfers/:id/edit',
        name: 'admin.stocktransfer.edit',
        component: EditStockTransfer,
    },

     //department
     {
        path: '/admin/departments',
        name: 'admin.department.index',
        component: IndexDepartment,
    },
    {
        path: '/admin/departments/create',
        name: 'admin.department.create',
        component: CreateDepartment,
    },
    {
        path: '/admin/departments/:id',
        name: 'admin.department.show',
        component: ShowDepartment,
    },
    {
        path: '/admin/departments/:id/edit',
        name: 'admin.department.edit',
        component: EditDepartment,
    },


    //contract types
    {
        path: '/admin/contract-types',
        name: 'admin.contract-type.index',
        component: IndexContractType,
    },
    {
        path: '/admin/contract-types/create',
        name: 'admin.contract-type.create',
        component: CreateContractType,
    },
    {
        path: '/admin/contract-types/:id',
        name: 'admin.contract-type.show',
        component: ShowContractType,
    },
    {
        path: '/admin/contract-types/:id/edit',
        name: 'admin.contract-type.edit',
        component: EditContractType,
    },


    //technician
    {
        path: '/admin/technicians',
        name: 'admin.technician.index',
        component: IndexTechnician,
    },
    {
        path: '/admin/technicians/create',
        name: 'admin.technician.create',
        component: CreateTechnician,
    },
    {
        path: '/admin/technicians/:id',
        name: 'admin.technician.show',
        component: ShowTechnician,
    },
    {
        path: '/admin/technicians/:id/edit',
        name: 'admin.technician.edit',
        component: EditTechnician,
    },

    //vacation plans
    {
        path: '/admin/vacation-plans',
        name: 'admin.vacation-plans.index',
        component: IndexVacationPlan,
    },
    {
        path: '/admin/vacation-plans/create',
        name: 'admin.vacation-plans.create',
        component: CreateVacationPlan,
    },
    {
        path: '/admin/vacation-plans/:id',
        name: 'admin.vacation-plans.show',
        component: ShowVacationPlan,
    },
    {
        path: '/admin/vacation-plans/:id/edit',
        name: 'admin.vacation-plans.edit',
        component: EditVacationPlan,
    },

    //granjas (produção avícola)
    {
        path: '/admin/granjas',
        name: 'admin.granjas.index',
        component: IndexFarms,
    },
    {
        path: '/admin/granjas/create',
        name: 'admin.granjas.create',
        component: CreateFarms,
    },
    {
        path: '/admin/granjas/:id',
        name: 'admin.granjas.show',
        component: ShowFarms,
    },
    {
        path: '/admin/granjas/:id/edit',
        name: 'admin.granjas.edit',
        component: EditFarms,
    },

    //galpões (produção avícola)
    {
        path: '/admin/galpoes',
        name: 'admin.galpoes.index',
        component: IndexHouses,
    },
    {
        path: '/admin/galpoes/create',
        name: 'admin.galpoes.create',
        component: CreateHouses,
    },
    {
        path: '/admin/galpoes/:id',
        name: 'admin.galpoes.show',
        component: ShowHouses,
    },
    {
        path: '/admin/galpoes/:id/edit',
        name: 'admin.galpoes.edit',
        component: EditHouses,
    },

    //lotes (produção avícola)
    {
        path: '/admin/lotes',
        name: 'admin.lotes.index',
        component: IndexFlocks,
    },
    {
        path: '/admin/lotes/create',
        name: 'admin.lotes.create',
        component: CreateFlocks,
    },
    {
        path: '/admin/lotes/:id',
        name: 'admin.lotes.show',
        component: ShowFlocks,
    },
    {
        path: '/admin/lotes/:id/edit',
        name: 'admin.lotes.edit',
        component: EditFlocks,
    },

    //linhagens (produção avícola)
    {
        path: '/admin/linhagens',
        name: 'admin.linhagens.index',
        component: IndexLineages,
    },
    {
        path: '/admin/linhagens/create',
        name: 'admin.linhagens.create',
        component: CreateLineages,
    },
    {
        path: '/admin/linhagens/:id',
        name: 'admin.linhagens.show',
        component: ShowLineages,
    },
    {
        path: '/admin/linhagens/:id/edit',
        name: 'admin.linhagens.edit',
        component: EditLineages,
    },

    //produção diária (produção avícola)
    {
        path: '/admin/producao-diaria',
        name: 'admin.producao-diaria.index',
        component: IndexDailyProduction,
    },
    {
        path: '/admin/producao-diaria/create',
        name: 'admin.producao-diaria.create',
        component: CreateDailyProduction,
    },
    {
        path: '/admin/producao-diaria/:id',
        name: 'admin.producao-diaria.show',
        component: ShowDailyProduction,
    },
    {
        path: '/admin/producao-diaria/:id/edit',
        name: 'admin.producao-diaria.edit',
        component: EditDailyProduction,
    },

    //mortalidade (produção avícola)
    {
        path: '/admin/mortalidade',
        name: 'admin.mortalidade.index',
        component: IndexMortality,
    },
    {
        path: '/admin/mortalidade/create',
        name: 'admin.mortalidade.create',
        component: CreateMortality,
    },
    {
        path: '/admin/mortalidade/:id',
        name: 'admin.mortalidade.show',
        component: ShowMortality,
    },
    {
        path: '/admin/mortalidade/:id/edit',
        name: 'admin.mortalidade.edit',
        component: EditMortality,
    },

    //calendário vacinal (produção avícola)
    {
        path: '/admin/calendario-vacinal',
        name: 'admin.calendario-vacinal.index',
        component: IndexVaccinationSchedule,
    },
    {
        path: '/admin/calendario-vacinal/create',
        name: 'admin.calendario-vacinal.create',
        component: CreateVaccinationSchedule,
    },
    {
        path: '/admin/calendario-vacinal/:id',
        name: 'admin.calendario-vacinal.show',
        component: ShowVaccinationSchedule,
    },
    {
        path: '/admin/calendario-vacinal/:id/edit',
        name: 'admin.calendario-vacinal.edit',
        component: EditVaccinationSchedule,
    },

    //vacinas (configurações avícolas)
    {
        path: '/admin/vacinas',
        name: 'admin.vacinas.index',
        component: IndexVaccines,
    },
    {
        path: '/admin/vacinas/create',
        name: 'admin.vacinas.create',
        component: CreateVaccines,
    },
    {
        path: '/admin/vacinas/:id',
        name: 'admin.vacinas.show',
        component: ShowVaccines,
    },
    {
        path: '/admin/vacinas/:id/edit',
        name: 'admin.vacinas.edit',
        component: EditVaccines,
    },

    //despesas de ovos (produção avícola)
    {
        path: '/admin/despesas-ovos',
        name: 'admin.despesas-ovos.index',
        component: IndexEggExpenses,
    },
    {
        path: '/admin/despesas-ovos/create',
        name: 'admin.despesas-ovos.create',
        component: CreateEggExpenses,
    },
    {
        path: '/admin/despesas-ovos/dashboard',
        name: 'admin.despesas-ovos.dashboard',
        component: DashboardEggExpenses,
    },
    {
        path: '/admin/despesas-ovos/:id',
        name: 'admin.despesas-ovos.show',
        component: ShowEggExpenses,
    },
    {
        path: '/admin/despesas-ovos/:id/edit',
        name: 'admin.despesas-ovos.edit',
        component: EditEggExpenses,
    },

    //classificação de ovos (produção avícola)
    {
        path: '/admin/classificacao-ovos',
        name: 'admin.classificacao-ovos.index',
        component: IndexEggClassifications,
    },
    {
        path: '/admin/classificacao-ovos/create',
        name: 'admin.classificacao-ovos.create',
        component: CreateEggClassifications,
    },
    {
        path: '/admin/classificacao-ovos/:id',
        name: 'admin.classificacao-ovos.show',
        component: ShowEggClassifications,
    },
    {
        path: '/admin/classificacao-ovos/:id/edit',
        name: 'admin.classificacao-ovos.edit',
        component: EditEggClassifications,
    },

    //estoque de ovos (produção avícola)
    {
        path: '/admin/estoque-ovos',
        name: 'admin.estoque-ovos.index',
        component: IndexEggInventory,
    },
    {
        path: '/admin/estoque-ovos/create',
        name: 'admin.estoque-ovos.create',
        component: CreateEggInventory,
    },
    {
        path: '/admin/estoque-ovos/:id',
        name: 'admin.estoque-ovos.show',
        component: ShowEggInventory,
    },
    {
        path: '/admin/estoque-ovos/:id/edit',
        name: 'admin.estoque-ovos.edit',
        component: EditEggInventory,
    },

    //motivos de refugo (produção avícola)
    {
        path: '/admin/motivos-refugo',
        name: 'admin.motivos-refugo.index',
        component: IndexRejectReasons,
    },
    {
        path: '/admin/motivos-refugo/create',
        name: 'admin.motivos-refugo.create',
        component: CreateRejectReasons,
    },
    {
        path: '/admin/motivos-refugo/:id',
        name: 'admin.motivos-refugo.show',
        component: ShowRejectReasons,
    },
    {
        path: '/admin/motivos-refugo/:id/edit',
        name: 'admin.motivos-refugo.edit',
        component: EditRejectReasons,
    },

    //categorias de ovos (produção avícola)
    {
        path: '/admin/categorias-ovos',
        name: 'admin.categorias-ovos.index',
        component: IndexEggCategories,
    },
    {
        path: '/admin/categorias-ovos/create',
        name: 'admin.categorias-ovos.create',
        component: CreateEggCategories,
    },
    {
        path: '/admin/categorias-ovos/:id',
        name: 'admin.categorias-ovos.show',
        component: ShowEggCategories,
    },
    {
        path: '/admin/categorias-ovos/:id/edit',
        name: 'admin.categorias-ovos.edit',
        component: EditEggCategories,
    },

    //ovos (produção avícola)
    {
        path: '/admin/ovos',
        name: 'admin.ovos.index',
        component: IndexEggs,
    },
    {
        path: '/admin/ovos/create',
        name: 'admin.ovos.create',
        component: CreateEggs,
    },
    {
        path: '/admin/ovos/:id',
        name: 'admin.ovos.show',
        component: ShowEggs,
    },
    {
        path: '/admin/ovos/:id/edit',
        name: 'admin.ovos.edit',
        component: EditEggs,
    },

    //embalagem (produção avícola)
    {
        path: '/admin/embalagem',
        name: 'admin.embalagem.index',
        component: IndexPackaging,
    },
    {
        path: '/admin/embalagem/create',
        name: 'admin.embalagem.create',
        component: CreatePackaging,
    },
    {
        path: '/admin/embalagem/:id',
        name: 'admin.embalagem.show',
        component: ShowPackaging,
    },
    {
        path: '/admin/embalagem/:id/edit',
        name: 'admin.embalagem.edit',
        component: EditPackaging,
    },

    //pedidos (produção avícola)
    {
        path: '/admin/pedidos',
        name: 'admin.pedidos.index',
        component: IndexEggOrders,
    },
    {
        path: '/admin/pedidos/calendario',
        name: 'admin.pedidos.calendar',
        component: CalendarEggOrders,
    },
    {
        path: '/admin/pedidos/create',
        name: 'admin.pedidos.create',
        component: CreateEggOrders,
    },
    {
        path: '/admin/pedidos/:id',
        name: 'admin.pedidos.show',
        component: ShowEggOrders,
    },
    {
        path: '/admin/pedidos/:id/edit',
        name: 'admin.pedidos.edit',
        component: EditEggOrders,
    },

    //separação de ovos
    {
        path: '/admin/separacao-ovos',
        name: 'admin.separacao-ovos.index',
        component: IndexEggSeparation,
    },
    {
        path: '/admin/separacao-ovos/:id/separar',
        name: 'admin.separacao-ovos.separate',
        component: SeparateEggOrder,
    },

    //clientes de ovos (produção avícola)
    {
        path: '/admin/clientes-ovos',
        name: 'admin.clientes-ovos.index',
        component: IndexEggCustomers,
    },
    {
        path: '/admin/clientes-ovos/create',
        name: 'admin.clientes-ovos.create',
        component: CreateEggCustomers,
    },
    {
        path: '/admin/clientes-ovos/:id',
        name: 'admin.clientes-ovos.show',
        component: ShowEggCustomers,
    },
    {
        path: '/admin/clientes-ovos/:id/edit',
        name: 'admin.clientes-ovos.edit',
        component: EditEggCustomers,
    },

    //portal de pedidos de ovos (clientes)
    {
        path: '/portal/pedidos-ovos',
        name: 'portal.pedidos-ovos.login',
        component: PortalLogin,
    },
    {
        path: '/portal/pedidos-ovos/pedidos',
        name: 'portal.pedidos-ovos.orders',
        component: PortalOrders,
    },

    //expedição de ovos (produção avícola)
    {
        path: '/admin/expedicao-ovos',
        name: 'admin.expedicao-ovos.index',
        component: IndexEggShipping,
    },
    {
        path: '/admin/expedicao-ovos/calendario',
        name: 'admin.expedicao-ovos.calendar',
        component: CalendarEggShipping,
    },
    {
        path: '/admin/expedicao-ovos/create',
        name: 'admin.expedicao-ovos.create',
        component: CreateEggShipping,
    },
    {
        path: '/admin/expedicao-ovos/:id',
        name: 'admin.expedicao-ovos.show',
        component: ShowEggShipping,
    },
    {
        path: '/admin/expedicao-ovos/:id/edit',
        name: 'admin.expedicao-ovos.edit',
        component: EditEggShipping,
    },

    //rastreabilidade (produção avícola)
    {
        path: '/admin/rastreabilidade',
        name: 'admin.rastreabilidade.index',
        component: IndexTraceability,
    },
    {
        path: '/admin/rastreabilidade/detalhe/:code',
        name: 'admin.rastreabilidade.show',
        component: ShowTraceability,
    },

    //dashboard ovos (produção avícola)
    {
        path: '/admin/dashboard-ovos',
        name: 'admin.dashboard-ovos.index',
        component: EggDashboard,
    },

    //alertas (produção avícola)
    {
        path: '/admin/alertas-ovos',
        name: 'admin.alertas-ovos.index',
        component: IndexEggAlerts,
    },
    {
        path: '/admin/alertas-ovos/:id',
        name: 'admin.alertas-ovos.show',
        component: ShowEggAlerts,
    },

    //kpis avícolas
    {
        path: '/admin/kpi-postura',
        name: 'admin.kpi-postura.index',
        component: KpiLayingRate,
    },
    {
        path: '/admin/kpi-mortalidade',
        name: 'admin.kpi-mortalidade.index',
        component: KpiMortalityRate,
    },
    {
        path: '/admin/kpi-conversao',
        name: 'admin.kpi-conversao.index',
        component: KpiFeedConversion,
    },
    {
        path: '/admin/curva-postura',
        name: 'admin.curva-postura.index',
        component: KpiLayingCurve,
    },
    {
        path: '/admin/ranking-galpoes',
        name: 'admin.ranking-galpoes.index',
        component: KpiHouseRanking,
    },
    {
        path: '/admin/custo-duzia',
        name: 'admin.custo-duzia.index',
        component: KpiCostPerDozen,
    },

    //relatórios ovos (produção avícola)
    {
        path: '/admin/relatorio-producao-diaria',
        name: 'admin.relatorio-producao-diaria.index',
        component: ReportDailyProduction,
    },
    {
        path: '/admin/relatorio-refugos',
        name: 'admin.relatorio-refugos.index',
        component: ReportRejects,
    },
    {
        path: '/admin/relatorio-estoque-ovos',
        name: 'admin.relatorio-estoque-ovos.index',
        component: ReportInventory,
    },
    {
        path: '/admin/relatorio-sanitario',
        name: 'admin.relatorio-sanitario.index',
        component: ReportSanitary,
    },
    {
        path: '/admin/relatorio-rastreabilidade',
        name: 'admin.relatorio-rastreabilidade.index',
        component: ReportTraceability,
    },

    //expense categories
    {
        path: '/admin/expense-categories',
        name: 'admin.expense-categories.index',
        component: IndexExpenseCategory,
    },

    //expenses
    {
        path: '/admin/expenses',
        name: 'admin.expenses.index',
        component: IndexExpenses,
    },
    {
        path: '/admin/expenses/create',
        name: 'admin.expenses.create',
        component: CreateExpense,
    },
    {
        path: '/admin/expenses/:id',
        name: 'admin.expenses.show',
        component: ShowExpense,
    },
    {
        path: '/admin/expenses/:id/edit',
        name: 'admin.expenses.edit',
        component: EditExpense,
    },

    //salary processes
    {
        path: '/admin/salary-processes',
        name: 'admin.salary-process.index',
        component: IndexSalaryProcess,
    },
    {
        path: '/admin/salary-processes/create',
        name: 'admin.salary-process.create',
        component: CreateSalaryProcess,
    },
    {
        path: '/admin/salary-processes/:id',
        name: 'admin.salary-process.show',
        component: ShowSalaryProcess,
    },
    {
        path: '/admin/salary-processes/:id/edit',
        name: 'admin.salary-process.edit',
        component: EditSalaryProcess,
    },

    //absences
    {
        path: '/admin/absences',
        name: 'admin.absences.index',
        component: IndexAbsences,
    },
    {
        path: '/admin/absences/create',
        name: 'admin.absences.create',
        component: CreateAbsence,
    },
    {
        path: '/admin/absences/:id',
        name: 'admin.absences.show',
        component: ShowAbsence,
    },
    {
        path: '/admin/absences/:id/edit',
        name: 'admin.absences.edit',
        component: EditAbsence,
    },

    // Work Schedule (Escala de Trabalho)
    {
        path: '/admin/work-schedule',
        name: 'admin.work-schedule.dashboard',
        component: WorkScheduleDashboard,
    },
    {
        path: '/admin/work-schedule/schedules',
        name: 'admin.work-schedule.index',
        component: WorkScheduleIndex,
    },
    {
        path: '/admin/work-schedule/schedules/create',
        name: 'admin.work-schedule.create',
        component: WorkScheduleCreate,
    },
    {
        path: '/admin/work-schedule/schedules/:id',
        name: 'admin.work-schedule.show',
        component: WorkScheduleShow,
    },
    {
        path: '/admin/work-schedule/client-view',
        name: 'admin.work-schedule.client-view',
        component: WorkScheduleClientView,
    },
    {
        path: '/admin/work-schedule/shifts',
        name: 'admin.work-schedule.shifts.index',
        component: ShiftIndex,
    },
    {
        path: '/admin/work-schedule/shifts/create',
        name: 'admin.work-schedule.shifts.create',
        component: ShiftCreate,
    },
    {
        path: '/admin/work-schedule/shifts/:id/edit',
        name: 'admin.work-schedule.shifts.edit',
        component: ShiftEdit,
    },

    //toolshop
    {
        path: '/admin/toolshops',
        name: 'admin.toolshop.index',
        component: IndexToolShop,
    },
    {
        path: '/admin/toolshops/create',
        name: 'admin.toolshop.create',
        component: CreateToolShop,
    },
    {
        path: '/admin/toolshops/:id',
        name: 'admin.toolshop.show',
        component: ShowToolShop,
    },
    {
        path: '/admin/toolshops/:id/edit',
        name: 'admin.toolshop.edit',
        component: EditToolShop,
    },

    //toolshop operator maintenance
    {
        path: '/operator/maintenance/toolshops',
        name: 'operator.maintenance.toolshop.index',
        component: IndexOperatorMaintenanceToolShop,
    },
    {
        path: '/operator/maintenance/toolshops/create',
        name: 'operator.maintenance.toolshop.create',
        component: CreateOperatorMaintenanceToolShop,
    },
    {
        path: '/operator/maintenance/toolshops/:id',
        name: 'operator.maintenance.toolshop.show',
        component: ShowOperatorMaintenanceToolShop,
    },
    {
        path: '/operator/maintenance/toolshops/:id/edit',
        name: 'operator.maintenance.toolshop.edit',
        component: EditOperatorMaintenanceToolShop,
    },

    //stockrequest
    {
        path: '/admin/stockrequests',
        name: 'admin.stockrequest.index',
        component: IndexStockRequest,
    },
    {
        path: '/admin/stockrequests/create',
        name: 'admin.stockrequest.create',
        component: CreateStockRequest,
    },
    {
        path: '/admin/stockrequests/:id',
        name: 'admin.stockrequest.show',
        component: ShowStockRequest,
    },
    {
        path: '/admin/stockrequests/:id/edit',
        name: 'admin.stockrequest.edit',
        component: EditStockRequest,
    },


    //technician request
    {
        path: '/admin/technicianrequests',
        name: 'admin.technicianrequest.index',
        component: IndexTechnicianRequest,
    },
    {
        path: '/admin/technicianrequests/create',
        name: 'admin.technicianrequest.create',
        component: CreateTechnicianRequest,
    },
    {
        path: '/admin/technicianrequests/:id',
        name: 'admin.technicianrequest.show',
        component: ShowTechnicianRequest,
    },
    {
        path: '/admin/technicianrequests/:id/edit',
        name: 'admin.technicianrequest.edit',
        component: EditTechnicianRequest,
    },

    //tool request
    {
        path: '/admin/toolrequests',
        name: 'admin.toolrequest.index',
        component: IndexToolRequest,
    },
    {
        path: '/admin/toolrequests/create',
        name: 'admin.toolrequest.create',
        component: CreateToolRequest,
    },
    {
        path: '/admin/toolrequests/:id',
        name: 'admin.toolrequest.show',
        component: ShowToolRequest,
    },
    {
        path: '/admin/toolrequests/:id/edit',
        name: 'admin.toolrequest.edit',
        component: EditToolRequest,
    },

    //hour distance
    {
        path: '/admin/hourdistances',
        name: 'admin.hourdistance.index',
        component: IndexHourDistance,
    },
    {
        path: '/admin/hourdistances/create',
        name: 'admin.hourdistance.create',
        component: CreateHourDistance,
    },
    {
        path: '/admin/hourdistances/:id',
        name: 'admin.hourdistance.show',
        component: ShowHourDistance,
    },
    {
        path: '/admin/hourdistances/:id/edit',
        name: 'admin.hourdistance.edit',
        component: EditHourDistance,
    },

    //fuel
    {
        path: '/admin/fuel',
        name: 'admin.fuel.index',
        component: IndexFuel,
    },
    {
        path: '/admin/fuel/create',
        name: 'admin.fuel.create',
        component: CreateFuel,
    },
    {
        path: '/admin/fuel/:id',
        name: 'admin.fuel.show',
        component: ShowFuel,
    },
    {
        path: '/admin/fuel/:id/edit',
        name: 'admin.fuel.edit',
        component: EditFuel,
    },

    //water consumption
    {
        path: '/admin/waterconsumption',
        name: 'admin.waterconsumption.index',
        component: IndexWaterConsumption,
    },
    {
        path: '/admin/waterconsumption/create',
        name: 'admin.waterconsumption.create',
        component: CreateWaterConsumption,
    },
    {
        path: '/admin/waterconsumption/:id',
        name: 'admin.waterconsumption.show',
        component: ShowWaterConsumption,
    },
    {
        path: '/admin/waterconsumption/:id/edit',
        name: 'admin.waterconsumption.edit',
        component: EditWaterConsumption,
    },

    //energy consumption
    {
        path: '/admin/energyconsumption',
        name: 'admin.energyconsumption.index',
        component: IndexEnergyConsumption,
    },
    {
        path: '/admin/energyconsumption/create',
        name: 'admin.energyconsumption.create',
        component: CreateEnergyConsumption,
    },
    {
        path: '/admin/energyconsumption/:id',
        name: 'admin.energyconsumption.show',
        component: ShowEnergyConsumption,
    },
    {
        path: '/admin/energyconsumption/:id/edit',
        name: 'admin.energyconsumption.edit',
        component: EditEnergyConsumption,
    },

    //notification 
    {
        path: '/admin/notifications',
        name: 'admin.notifications.index',
        component: IndexNotifications,
    },

    //operiton shift
    {
        path: '/admin/shifts',
        name: 'admin.shifts.index',
        component: IndexShifts,
    },
    {
        path: '/admin/shifts/create',
        name: 'admin.shifts.create',
        component: CreateShifts,
    },
    {
        path: '/admin/shifts/:id',
        name: 'admin.shifts.show',
        component: ShowShifts,
    },
    {
        path: '/admin/shifts/:id/edit',
        name: 'admin.shifts.edit',
        component: EditShifts,
    },


     //Group

     {
        path: '/admin/shifts/groupshift/:id',
        name: 'admin.shifts.group.show',
        component: ShowGroup,
    },


      //Request

      {
        path: '/admin/shifts/request/:id',
        name: 'admin.shifts.request.show',
        component: ShowRequests,
    },
    {
        path:  '/admin/shifts/requestitem/:id/edit',
        name: 'admin.shifts.request.edit',
        component: EditRequests,
    },

   

    // shift request
    {
        path: '/admin/shiftequipmentrequest',
        name: 'admin.shiftrequest.index',
        component: IndexShiftRequest,
    },
    {
        path: '/admin/shiftequipmentrequest/create',
        name: 'admin.shiftrequest.create',
        component: CreateShiftRequest,
    },
    {
        path: '/admin/shiftequipmentrequest/:id',
        name: 'admin.shiftrequest.show',
        component: ShowShiftRequest,
    },
    {
        path: '/admin/shiftequipmentrequest/:id/edit',
        name: 'admin.shiftrequest.edit',
        component: EditShiftRequest,
    },

    // shift request
    {
        path: '/admin/calendars',
        name: 'admin.calendars.index',
        component: Calendar,
    },

    {
        path: '/admin/destination/calendars',
        name: 'admin.destination.calendars.index',
        component: DestinationCalendar,
    },

    // schedule work
    {
        path: '/admin/schedulework',
        name: 'admin.schedulework.index',
        component: IndexScheduleWork,
    },
    {
        path: '/admin/schedulework/create',
        name: 'admin.schedulework.create',
        component: CreateScheduleWork,
    },
    {
        path: '/admin/schedulework/:id',
        name: 'admin.schedulework.show',
        component: ShowScheduleWork,
    },
    {
        path: '/admin/schedulework/:id/edit',
        name: 'admin.schedulework.edit',
        component: EditScheduleWork,
    },

    // schedule work
    {
        path: '/admin/quotation',
        name: 'admin.quotation.index',
        component: IndexQuotation,
    },
    {
        path: '/admin/quotation/create',
        name: 'admin.quotation.create',
        component: CreateQuotation,
    },
    {
        path: '/admin/quotation/:id',
        name: 'admin.quotation.show',
        component: ShowQuotation,
    },
    {
        path: '/admin/quotation/:id/edit',
        name: 'admin.quotation.edit',
        component: EditQuotation,
    },

    //inspection

    {
        path: '/admin/inspections',
        name: 'admin.inspection.index',
        component: IndexInspection,
    },
    {
        path: '/admin/inspections/create',
        name: 'admin.inspection.create',
        component: CreateInspection,
    },
    {
        path: '/admin/inspections/:id',
        name: 'admin.inspection.show',
        component: ShowInspection,
    },
    {
        path: '/admin/inspections/:id/edit',
        name: 'admin.inspection.edit',
        component: EditInspection,
    },

    //generaelinspection

    {
        path: '/admin/generalinspections',
        name: 'admin.generalinspection.index',
        component: IndexGeneralInspection,
    },
    {
        path: '/admin/generalinspections/create',
        name: 'admin.generalinspection.create',
        component: CreateGeneralInspection,
    },
    {
        path: '/admin/generalinspections/:id',
        name: 'admin.generalinspection.show',
        component: ShowGeneralInspection,
    },
    {
        path: '/admin/generalinspections/:id/edit',
        name: 'admin.generalinspection.edit',
        component: EditGeneralInspection,
    },
    
    // {
    //     path: '/admin/admins',
    //     name: 'admin.admins',
    //     component: AdminIndex,
    // },
    // {
    //     path: '/admin/admins/create',
    //     name: 'admin.admins.create',
    //     component: AdminCreate,
    // },
    // {
    //     path: '/admin/admins/:id',
    //     name: 'admin.admins.show',
    //     component: AdminShow,
    // },
    // {
    //     path: '/admin/admins/:id/edit',
    //     name: 'admin.admins.edit',
    //     component: AdminEdit,
    // },


  
    


    //OPERATOR MAINTENANCE


    //mcscr

    {
        path: '/operator/maintenance/mcscr',
        name: 'operator.maintenance.mcscr.index',
        component: IndexMcscrOperatorMaintenance,
    },
    {
        path: '/operator/maintenance/mcscr/create',
        name: 'operator.maintenance.mcscr.create',
        component: CreateMcscrOperatorMaintenance,
    },
    {
        path: '/operator/maintenance/mcscr/:id',
        name: 'operator.maintenance.mcscr.show',
        component: ShowMcscrOperatorMaintenance,
    },
    {
        path: '/operator/maintenance/mcscr/:id/edit',
        name: 'operator.maintenance.mcscr.edit',
        component: EditMcscrOperatorMaintenance,
    },
    {
        path: '/operator/maintenance/mcscr/:id/upload',
        name: 'operator.maintenance.mcscr.upload',
        component: UploadMcscrOperatorMaintenance,
    },

     //taskmcscr

     {
        path: '/operator/maintenance/taskmcscr',
        name: 'operator.maintenance.taskmcscr.index',
        component: IndexTaskMcscrOperatorMaintenance,
    },
    {
        path: '/operator/maintenance/taskmcscr/create',
        name: 'operator.maintenance.taskmcscr.create',
        component: CreateTaskMcscrOperatorMaintenance,
    },
    {
        path: '/operator/maintenance/taskmcscr/:id',
        name: 'operator.maintenance.taskmcscr.show',
        component: ShowTaskMcscrOperatorMaintenance,
    },
    {
        path: '/operator/maintenance/taskmcscr/:id/edit',
        name: 'operator.maintenance.taskmcscr.edit',
        component: EditTaskMcscrOperatorMaintenance,
    },
    //inspection

    {
        path: '/operator/maintenance/inspections',
        name: 'operator.maintenance.inspection.index',
        component: IndexInspectionOperatorMaintenance,
    },
    {
        path: '/operator/maintenance/inspections/create',
        name: 'operator.maintenance.inspection.create',
        component: CreateInspectionOperatorMaintenance,
    },
    {
        path: '/operator/maintenance/inspections/:id',
        name: 'operator.maintenance.inspection.show',
        component: ShowInspectionOperatorMaintenance,
    },
    {
        path: '/operator/maintenance/inspections/:id/edit',
        name: 'operator.maintenance.inspection.edit',
        component: EditInspectionOperatorMaintenance,
    },

    {
        path: '/operator/maintenance/calendars',
        name: 'operator.maintenance.calendars.index',
        component: MaintenanceCalendar,
    },


    //MANAGER MAINTENANCE

     //reason

     {
        path: '/manager/maintenance/mcscr/reasons',
        name: 'manager.maintenance.reason.index',
        component: IndexReasonManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/reasons/create',
        name: 'manager.maintenance.reason.create',
        component: CreateReasonManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/reasons/:id',
        name: 'manager.maintenance.reason.show',
        component: ShowReasonManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/reasons/:id/edit',
        name: 'manager.maintenance.reason.edit',
        component: EditReasonManagerMaintenance,
    },


    //cause

    {
        path: '/manager/maintenance/mcscr/causes',
        name: 'manager.maintenance.cause.index',
        component: IndexCauseManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/causes/create',
        name: 'manager.maintenance.cause.create',
        component: CreateCauseManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/causes/:id',
        name: 'manager.maintenance.cause.show',
        component: ShowCauseManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/causes/:id/edit',
        name: 'manager.maintenance.cause.edit',
        component: EditCauseManagerMaintenance,
    },

        //solution

    {
        path: '/manager/maintenance/mcscr/solutions',
        name: 'manager.maintenance.solution.index',
        component: IndexSolutionManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/solutions/create',
        name: 'manager.maintenance.solution.create',
        component: CreateSolutionManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/solutions/:id',
        name: 'manager.maintenance.solution.show',
        component: ShowSolutionManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/solutions/:id/edit',
        name: 'manager.maintenance.solution.edit',
        component: EditSolutionManagerMaintenance,
    },

     //consequence

     {
        path: '/manager/maintenance/mcscr/consequences',
        name: 'manager.maintenance.consequence.index',
        component: IndexConsequenceManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/consequences/create',
        name: 'manager.maintenance.consequence.create',
        component: CreateConsequenceManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/consequences/:id',
        name: 'manager.maintenance.consequence.show',
        component: ShowConsequenceManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/consequences/:id/edit',
        name: 'manager.maintenance.consequence.edit',
        component: EditConsequenceManagerMaintenance,
    },
    
    //recommendation

    {
        path: '/manager/maintenance/mcscr/recommendations',
        name: 'manager.maintenance.recommendation.index',
        component: IndexRecommendationManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/recommendations/create',
        name: 'manager.maintenance.recommendation.create',
        component: CreateRecommendationManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/recommendations/:id',
        name: 'manager.maintenance.recommendation.show',
        component: ShowRecommendationManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/recommendations/:id/edit',
        name: 'manager.maintenance.recommendation.edit',
        component: EditRecommendationManagerMaintenance,
    },



    //mcscr

    {
        path: '/manager/maintenance/mcscr',
        name: 'manager.maintenance.mcscr.index',
        component: IndexMcscrManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/create',
        name: 'manager.maintenance.mcscr.create',
        component: CreateMcscrManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/:id',
        name: 'manager.maintenance.mcscr.show',
        component: ShowMcscrManagerMaintenance,
    },
    {
        path: '/manager/maintenance/mcscr/:id/edit',
        name: 'manager.maintenance.mcscr.edit',
        component: EditMcscrManagerMaintenance,
    },



    //equipment
    {
        path: '/manager/maintenance/equipments',
        name: 'manager.maintenance.equipments.index',
        component: IndexEquipmentManagerMaintenance,
    },
    {
        path: '/manager/maintenance/equipments/create',
        name: 'manager.maintenance.equipments.create',
        component: CreateEquipmentManagerMaintenance,
    },
    {
        path: '/manager/maintenance/equipments/:id',
        name: 'manager.maintenance.equipments.show',
        component: ShowEquipmentManagerMaintenance,
    },
    {
        path: '/manager/maintenance/equipments/:id/edit',
        name: 'manager.maintenance.equipments.edit',
        component: EditEquipmentManagerMaintenance,
    },

     //hour distance
     {
        path: '/manager/maintenance/hourdistances',
        name: 'manager.maintenance.hourdistance.index',
        component: IndexHourDistanceManagerMaintenance,
    },
    {
        path: '/manager/maintenance/hourdistances/create',
        name: 'manager.maintenance.hourdistance.create',
        component: CreateHourDistanceManagerMaintenance,
    },
    {
        path: '/manager/maintenance/hourdistances/:id',
        name: 'manager.maintenance.hourdistance.show',
        component: ShowHourDistanceManagerMaintenance,
    },
    {
        path: '/manager/maintenance/hourdistances/:id/edit',
        name: 'manager.maintenance.hourdistance.edit',
        component: EditHourDistanceManagerMaintenance,
    },

    //fuel
    {
        path: '/manager/maintenance/fuel',
        name: 'manager.maintenance.fuel.index',
        component: IndexFuelManagerMaintenance,
    },
    {
        path: '/manager/maintenance/fuel/create',
        name: 'manager.maintenance.fuel.create',
        component: CreateFuelManagerMaintenance,
    },
    {
        path: '/manager/maintenance/fuel/:id',
        name: 'manager.maintenance.fuel.show',
        component: ShowFuelManagerMaintenance,
    },
    {
        path: '/manager/maintenance/fuel/:id/edit',
        name: 'manager.maintenance.fuel.edit',
        component: EditFuelManagerMaintenance,
    },

      //taskmcscr

      {
        path: '/manager/maintenance/taskmcscr',
        name: 'manager.maintenance.taskmcscr.index',
        component: IndexTaskMcscrManagerMaintenance,
    },
    {
        path: '/manager/maintenance/taskmcscr/create',
        name: 'manager.maintenance.taskmcscr.create',
        component: CreateTaskMcscrManagerMaintenance,
    },
    {
        path: '/manager/maintenance/taskmcscr/:id',
        name: 'manager.maintenance.taskmcscr.show',
        component: ShowTaskMcscrManagerMaintenance,
    },
    {
        path: '/manager/maintenance/taskmcscr/:id/edit',
        name: 'manager.maintenance.taskmcscr.edit',
        component: EditTaskMcscrManagerMaintenance,
    },

    //areas

    {
        path: '/manager/maintenance/areas',
        name: 'manager.maintenance.areas.index',
        component: IndexAreasManagerMaintenance,
    },
    {
        path: '/manager/maintenance/areas/create',
        name: 'manager.maintenance.areas.create',
        component: CreateAreasManagerMaintenance,
    },
    {
        path: '/manager/maintenance/areas/:id',
        name: 'manager.maintenance.areas.show',
        component: ShowAreasManagerMaintenance,
    },
    {
        path: '/manager/maintenance/areas/:id/edit',
        name: 'manager.maintenance.areas.edit',
        component: EditAreasManagerMaintenance,
    },

    

     //suppliers

     {
        path: '/manager/maintenance/suppliers',
        name: 'manager.maintenance.suppliers.index',
        component: IndexSuppliersManagerMaintenance,
    },
    {
        path: '/manager/maintenance/suppliers/create',
        name: 'manager.maintenance.suppliers.create',
        component: CreateSuppliersManagerMaintenance,
    },
    {
        path: '/manager/maintenance/suppliers/:id',
        name: 'manager.maintenance.suppliers.show',
        component: ShowSuppliersManagerMaintenance,
    },
    {
        path: '/manager/maintenance/suppliers/:id/edit',
        name: 'manager.maintenance.suppliers.edit',
        component: EditSuppliersManagerMaintenance,
    },

    //typeequipemnt

    {
        path: '/manager/maintenance/type_equipments',
        name: 'manager.maintenance.type_equipments.index',
        component: IndexTypeEquipmentsManagerMaintenance,
    },
    {
        path: '/manager/maintenance/type_equipments/create',
        name: 'manager.maintenance.type_equipments.create',
        component: CreateTypeEquipmentsManagerMaintenance,
    },
    {
        path: '/manager/maintenance/type_equipments/:id',
        name: 'manager.maintenance.type_equipments.show',
        component: ShowTypeEquipmentsManagerMaintenance,
    },
    {
        path: '/manager/maintenance/type_equipments/:id/edit',
        name: 'manager.maintenance.type_equipments.edit',
        component: EditTypeEquipmentsManagerMaintenance,
    },

    //fleet

    {
        path: '/manager/maintenance/fleet/:id',
        name: 'manager.maintenance.fleet.show',
        component: ShowFleetManagerMaintenance,
    },

    //destinations
    {
        path: '/manager/maintenance/destinations',
        name: 'manager.maintenance.destinations.index',
        component: IndexDestinationsManagerMaintenance,
    },
    {
        path: '/manager/maintenance/destinations/create',
        name: 'manager.maintenance.destinations.create',
        component: CreateDestinationsManagerMaintenance,
    },
    {
        path: '/manager/maintenance/destinations/:id',
        name: 'manager.maintenance.destinations.show',
        component: ShowDestinationsManagerMaintenance,
    },
    {
        path: '/manager/maintenance/destinations/:id/edit',
        name: 'manager.maintenance.destinations.edit',
        component: EditDestinationsManagerMaintenance,
    },

    //malfunction

    {
        path: '/manager/maintenance/malfunctions',
        name: 'manager.maintenance.malfunction.index',
        component: IndexMalfunctionManagerMaintenance,
    },
    {
        path: '/manager/maintenance/malfunctions/create',
        name: 'manager.maintenance.malfunction.create',
        component: CreateMalfunctionManagerMaintenance,
    },
    {
        path: '/manager/maintenance/malfunctions/:id',
        name: 'manager.maintenance.malfunction.show',
        component: ShowMalfunctionManagerMaintenance,
    },
    {
        path: '/manager/maintenance/malfunctions/:id/edit',
        name: 'manager.maintenance.malfunction.edit',
        component: EditMalfunctionManagerMaintenance,
    },

    //task

    {
        path: '/manager/maintenance/tasks',
        name: 'manager.maintenance.task.index',
        component: IndexTaskManagerMaintenance,
    },
    {
        path: '/manager/maintenance/tasks/create',
        name: 'manager.maintenance.task.create',
        component: CreateTaskManagerMaintenance,
    },
    {
        path: '/manager/maintenance/tasks/:id',
        name: 'manager.maintenance.task.show',
        component: ShowTaskManagerMaintenance,
    },
    {
        path: '/manager/maintenance/tasks/:id/edit',
        name: 'manager.maintenance.task.edit',
        component: EditTaskManagerMaintenance,
    },

     //equipment Components

     {
        path: '/manager/maintenance/equipments/component/:id',
        name: 'manager.maintenance.equipment.component.show',
        component: ShowComponentManagerMaintenance,
    },
    {
        path: '/manager/maintenance/equipments/component/:id/edit',
        name: 'manager.maintenance.equipment.component.edit',
        component: EditComponentManagerMaintenance,
    },

     //typeequipment SubComponents

     {
        path: '/manager/maintenance/type_equipments/subcomponent/:id',
        name: 'manager.maintenance.type_equipment.subcomponent.show',
        component: ShowTypeEquipmentSubComponentManagerMaintenance,
    },
    {
        path: '/manager/maintenance/type_equipments/subcomponent/:id/edit',
        name: 'manager.maintenance.type_equipment.subcomponent.edit',
        component: EditTypeEquipmentSubComponentManagerMaintenance,
    },

     //typeequipment Components

     {
        path: '/manager/maintenance/type_equipments/component/:id',
        name: 'manager.maintenance.type_equipment.component.show',
        component: ShowTypeEquipmentComponentManagerMaintenance,
    },
    {
        path: '/manager/maintenance/type_equipments/component/:id/edit',
        name: 'manager.maintenance.type_equipment.component.edit',
        component: EditTypeEquipmentComponentManagerMaintenance,
    },


    //equipment SubComponents

    {
        path: '/manager/maintenance/equipments/subcomponent/:id',
        name: 'manager.maintenance.equipment.subcomponent.show',
        component: ShowEquipmentSubComponentManagerMaintenance,
    },
    {
        path: '/manager/maintenance/equipments/subcomponent/:id/edit',
        name: 'manager.maintenance.equipment.subcomponent.edit',
        component: EditEquipmentSubComponentManagerMaintenance,
    },

    //notification 
    {
        path: '/manager/maintenance/notifications',
        name: 'manager.maintenance.notifications.index',
        component: IndexNotificationsManagerMaintenance,
    },


    //ROUTES FOR DESTINATION

     //equipment
     {
        path: '/admin/destination/equipments',
        name: 'admin.destination.equipments.index',
        component: IndexEquipmentAdminDestination,
    },
    {
        path: '/admin/destination/equipments/create',
        name: 'admin.destination.equipments.create',
        component: CreateEquipmentAdminDestination,
    },
    {
        path: '/admin/destination/equipments/:id',
        name: 'admin.destination.equipments.show',
        component: ShowEquipmentAdminDestination,
    },
    {
        path: '/admin/destination/equipments/:id/edit',
        name: 'admin.destination.equipments.edit',
        component: EditEquipmentAdminDestination,
    },

     //typeequipment
     {
        path: '/admin/destination/type_equipments',
        name: 'admin.destination.typeequipments.index',
        component: IndexTypeEquipmentAdminDestination,
    },
    {
        path: '/admin/destination/type_equipments/create',
        name: 'admin.destination.typeequipments.create',
        component: CreateTypeEquipmentAdminDestination,
    },
    {
        path: '/admin/destination/type_equipments/:id',
        name: 'admin.destination.typeequipments.show',
        component: ShowTypeEquipmentAdminDestination,
    },
    {
        path: '/admin/destination/type_equipments/:id/edit',
        name: 'admin.destination.typeequipments.edit',
        component: EditTypeEquipmentAdminDestination,
    },

     //typeequipment
     {
        path: '/admin/destination/hourdistances',
        name: 'admin.destination.hourdistances.index',
        component: IndexHourDistanceAdminDestination,
    },
    {
        path: '/admin/destination/hourdistances/create',
        name: 'admin.destination.hourdistances.create',
        component: CreateHourDistanceAdminDestination,
    },
    {
        path: '/admin/destination/hourdistances/:id',
        name: 'admin.destination.hourdistances.show',
        component: ShowHourDistanceAdminDestination,
    },
    {
        path: '/admin/destination/hourdistances/:id/edit',
        name: 'admin.destination.hourdistances.edit',
        component: EditHourDistanceAdminDestination,
    },

    //fuel
    {
        path: '/admin/destination/fuel',
        name: 'admin.destination.fuel.index',
        component: IndexFuelAdminDestination,
    },
    {
        path: '/admin/destination/fuel/create',
        name: 'admin.destination.fuel.create',
        component: CreateFuelAdminDestination,
    },
    {
        path: '/admin/destination/fuel/:id',
        name: 'admin.destination.fuel.show',
        component: ShowFuelAdminDestination,
    },
    {
        path: '/admin/destination/fuel/:id/edit',
        name: 'admin.destination.fuel.edit',
        component: EditFuelAdminDestination,
    },

    //taskmcscr
    {
        path: '/admin/destination/taskmcscr',
        name: 'admin.destination.taskmcscr.index',
        component: IndexTaskMcscrAdminDestination,
    },
    {
        path: '/admin/destination/taskmcscr/create',
        name: 'admin.destination.taskmcscr.create',
        component: CreateTaskMcscrAdminDestination,
    },
    {
        path: '/admin/destination/taskmcscr/:id',
        name: 'admin.destination.taskmcscr.show',
        component: ShowTaskMcscrAdminDestination,
    },
    {
        path: '/admin/destination/taskmcscr/:id/edit',
        name: 'admin.destination.taskmcscr.edit',
        component: EditTaskMcscrAdminDestination,
    },

    //mcscr
    {
        path: '/admin/destination/mcscr',
        name: 'admin.destination.mcscr.index',
        component: IndexMcscrAdminDestination,
    },
    {
        path: '/admin/destination/mcscr/create',
        name: 'admin.destination.mcscr.create',
        component: CreateMcscrAdminDestination,
    },
    {
        path: '/admin/destination/mcscr/:id',
        name: 'admin.destination.mcscr.show',
        component: ShowMcscrAdminDestination,
    },
    {
        path: '/admin/destination/mcscr/:id/edit',
        name: 'admin.destination.mcscr.edit',
        component: EditMcscrAdminDestination,
    },

    //quotation
    {
        path: '/admin/destination/quotation',
        name: 'admin.destination.quotation.index',
        component: IndexQuotationAdminDestination,
    },
    {
        path: '/admin/destination/quotation/create',
        name: 'admin.destination.quotation.create',
        component: CreateQuotationAdminDestination,
    },
    {
        path: '/admin/destination/quotation/:id',
        name: 'admin.destination.quotation.show',
        component: ShowQuotationAdminDestination,
    },
    {
        path: '/admin/destination/quotation/:id/edit',
        name: 'admin.destination.quotation.edit',
        component: EditQuotationAdminDestination,
    },

    // Fee Invoices for destination
    {
        path: '/admin/destination/fee-invoices',
        name: 'admin.destination.feeinvoices.index',
        component: IndexFeeInvoiceAdminDestination,
    },

    //fees

    {
        path: '/admin/fees',
        name: 'admin.fees.index',
        component: IndexFees,
    },
    {
        path: '/admin/fees/create',
        name: 'admin.fees.create',
        component: CreateFees,
    },
    {
        path: '/admin/fees/:id',
        name: 'admin.fees.show',
        component: ShowFees,
    },
    {
        path: '/admin/fees/:id/edit',
        name: 'admin.fees.edit',
        component: EditFees,
    },

    // Fee Invoices (Faturamento de Taxas)
    {
        path: '/admin/fee-invoices',
        name: 'admin.fee-invoices.index',
        component: IndexFeeInvoice,
    },
    {
        path: '/admin/fee-invoices/create',
        name: 'admin.fee-invoices.create',
        component: CreateFeeInvoice,
    },
    {
        path: '/admin/fee-invoices/:id',
        name: 'admin.fee-invoices.show',
        component: ShowFeeInvoice,
    },
    {
        path: '/admin/fee-invoices/:id/edit',
        name: 'admin.fee-invoices.edit',
        component: EditFeeInvoice,
    }





]

