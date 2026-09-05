<?php

namespace App\Controllers;

use App\Models\FacilityChecklistModel;
use App\Models\FacilityChecklistItemModel;
use App\Models\EquipmentMaintenanceLogModel;
use App\Models\EquipmentMaintenanceEntryModel;
use App\Models\AirconInspectionLogModel;
use App\Models\AirconInspectionEntryModel;
use App\Models\VehicleInspectionChecklistModel;
use App\Models\VehicleInspectionItemModel;
use App\Models\RestroomChecklistModel;
use App\Models\RestroomChecklistEntryModel;

// The 5 official Facilities Maintenance Program forms (F-FAC-PMP-FMC-001,
// F-FAC-PMP-EML-002, F-FAC-PMP-AIL-003, F-FAC-PMP-VMI-004, F-FAC-GAL-RC-002),
// digitized from the department's Pages/Word originals into editable records.
// Facility & Vehicle are "fixed checklist" forms (seeded items, admin fills in
// ratings); Equipment/Aircon/Restroom are "log book" forms (admin adds one row
// per event) — same shape split as the header+child tables behind them.
class MaintenanceFormsController extends BaseController
{
    // ---------- F-FAC-PMP-FMC-001: Facility Maintenance Checklist ----------

    public function facilityChecklist()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        return view('MaintenanceForms/facility_checklist', [
            'title'      => 'Facility Maintenance Checklist',
            'pageCss'    => 'maintenance-forms.css',
            'checklists' => (new FacilityChecklistModel())->getAllWithItems(),
        ]);
    }

    public function facilityChecklistAdd()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new FacilityChecklistModel())->createWithItems([
            'inspector'        => $this->request->getPost('inspector'),
            'building_area'    => $this->request->getPost('building_area'),
            'inspection_date'  => $this->request->getPost('inspection_date') ?: date('Y-m-d'),
            'inspection_type'  => $this->request->getPost('inspection_type'),
        ]);

        return redirect()->to('/maintenance-forms/facility')->with('success', 'New checklist created — fill in ratings per item below.');
    }

    // Saves every item on the checklist in one submit — editing 21 items
    // one at a time would mean 21 separate page reloads, so the whole
    // checklist is one big form instead (items[<item_id>][rating/corrective_action]).
    public function facilityChecklistUpdateItems($checklistId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $itemModel = new FacilityChecklistItemModel();
        foreach ((array) $this->request->getPost('items') as $itemId => $fields) {
            $itemModel->update((int) $itemId, [
                'rating'            => $fields['rating'] ?: null,
                'corrective_action' => $fields['corrective_action'] ?? null,
            ]);
        }

        return redirect()->to('/maintenance-forms/facility')->with('success', 'Checklist items saved.');
    }

    public function facilityChecklistUpdateHeader($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new FacilityChecklistModel())->update($id, [
            'overall_condition'      => $this->request->getPost('overall_condition') ?: null,
            'summary_findings'       => $this->request->getPost('summary_findings'),
            'corrective_action_plan' => $this->request->getPost('corrective_action_plan'),
            'reviewed_by'            => $this->request->getPost('reviewed_by'),
            'reviewed_date'          => $this->request->getPost('reviewed_date') ?: null,
            'approved_by'            => $this->request->getPost('approved_by'),
        ]);

        return redirect()->to('/maintenance-forms/facility')->with('success', 'Checklist summary saved.');
    }

    public function facilityChecklistDelete($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new FacilityChecklistModel())->update($id, ['is_archived' => 1]);

        return redirect()->to('/maintenance-forms/facility')->with('success', 'Checklist archived.');
    }

    // ---------- F-FAC-PMP-EML-002: Equipment Maintenance Log ----------

    public function equipmentLog()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        return view('MaintenanceForms/equipment_log', [
            'title'   => 'Equipment Maintenance Log',
            'pageCss' => 'maintenance-forms.css',
            'logs'    => (new EquipmentMaintenanceLogModel())->getAllWithEntries(),
        ]);
    }

    public function equipmentLogAdd()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new EquipmentMaintenanceLogModel())->insert([
            'department'     => $this->request->getPost('department'),
            'date_submitted' => $this->request->getPost('date_submitted') ?: date('Y-m-d'),
        ]);

        return redirect()->to('/maintenance-forms/equipment-log')->with('success', 'New log sheet created.');
    }

    public function equipmentLogUpdateHeader($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new EquipmentMaintenanceLogModel())->update($id, [
            'reviewed_by'   => $this->request->getPost('reviewed_by'),
            'reviewed_date' => $this->request->getPost('reviewed_date') ?: null,
            'approved_by'   => $this->request->getPost('approved_by'),
        ]);

        return redirect()->to('/maintenance-forms/equipment-log')->with('success', 'Sign-off saved.');
    }

    public function equipmentLogAddEntry($logId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new EquipmentMaintenanceEntryModel())->insert([
            'log_id'                => $logId,
            'entry_date'            => $this->request->getPost('entry_date') ?: date('Y-m-d'),
            'asset_name'            => $this->request->getPost('asset_name'),
            'serial_number'         => $this->request->getPost('serial_number'),
            'maintenance_frequency' => $this->request->getPost('maintenance_frequency'),
            'work_description'      => $this->request->getPost('work_description'),
            'status'                => $this->request->getPost('status'),
            'next_due_date'         => $this->request->getPost('next_due_date') ?: null,
            'performed_by'          => $this->request->getPost('performed_by'),
            'signature'             => $this->request->getPost('signature'),
        ]);

        return redirect()->to('/maintenance-forms/equipment-log')->with('success', 'Entry added.');
    }

    public function equipmentLogEditEntry($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new EquipmentMaintenanceEntryModel())->update($id, [
            'entry_date'            => $this->request->getPost('entry_date') ?: null,
            'asset_name'            => $this->request->getPost('asset_name'),
            'serial_number'         => $this->request->getPost('serial_number'),
            'maintenance_frequency' => $this->request->getPost('maintenance_frequency'),
            'work_description'      => $this->request->getPost('work_description'),
            'status'                => $this->request->getPost('status'),
            'next_due_date'         => $this->request->getPost('next_due_date') ?: null,
            'performed_by'          => $this->request->getPost('performed_by'),
            'signature'             => $this->request->getPost('signature'),
        ]);

        return redirect()->to('/maintenance-forms/equipment-log')->with('success', 'Entry updated.');
    }

    public function equipmentLogDeleteEntry($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new EquipmentMaintenanceEntryModel())->delete($id);

        return redirect()->to('/maintenance-forms/equipment-log')->with('success', 'Entry deleted.');
    }

    public function equipmentLogDelete($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new EquipmentMaintenanceLogModel())->update($id, ['is_archived' => 1]);

        return redirect()->to('/maintenance-forms/equipment-log')->with('success', 'Log sheet archived.');
    }

    // ---------- F-FAC-PMP-AIL-003: Aircon Inspection Log ----------

    public function airconLog()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        return view('MaintenanceForms/aircon_log', [
            'title'   => 'Aircon Inspection Log',
            'pageCss' => 'maintenance-forms.css',
            'logs'    => (new AirconInspectionLogModel())->getAllWithEntries(),
        ]);
    }

    public function airconLogAdd()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new AirconInspectionLogModel())->insert([
            'performed_by'   => $this->request->getPost('performed_by'),
            'date_submitted' => $this->request->getPost('date_submitted') ?: date('Y-m-d'),
        ]);

        return redirect()->to('/maintenance-forms/aircon-log')->with('success', 'New log sheet created.');
    }

    public function airconLogUpdateHeader($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new AirconInspectionLogModel())->update($id, [
            'reviewed_by'   => $this->request->getPost('reviewed_by'),
            'reviewed_date' => $this->request->getPost('reviewed_date') ?: null,
            'approved_by'   => $this->request->getPost('approved_by'),
        ]);

        return redirect()->to('/maintenance-forms/aircon-log')->with('success', 'Sign-off saved.');
    }

    public function airconLogAddEntry($logId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new AirconInspectionEntryModel())->insert([
            'log_id'      => $logId,
            'entry_date'  => $this->request->getPost('entry_date') ?: date('Y-m-d'),
            'department'  => $this->request->getPost('department'),
            'qty'         => $this->request->getPost('qty') ?: null,
            'room_no'     => $this->request->getPost('room_no'),
            'aircon_type' => $this->request->getPost('aircon_type'),
            'work_done'   => $this->request->getPost('work_done'),
            'remarks'     => $this->request->getPost('remarks'),
        ]);

        return redirect()->to('/maintenance-forms/aircon-log')->with('success', 'Entry added.');
    }

    public function airconLogEditEntry($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new AirconInspectionEntryModel())->update($id, [
            'entry_date'  => $this->request->getPost('entry_date') ?: null,
            'department'  => $this->request->getPost('department'),
            'qty'         => $this->request->getPost('qty') ?: null,
            'room_no'     => $this->request->getPost('room_no'),
            'aircon_type' => $this->request->getPost('aircon_type'),
            'work_done'   => $this->request->getPost('work_done'),
            'remarks'     => $this->request->getPost('remarks'),
        ]);

        return redirect()->to('/maintenance-forms/aircon-log')->with('success', 'Entry updated.');
    }

    public function airconLogDeleteEntry($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new AirconInspectionEntryModel())->delete($id);

        return redirect()->to('/maintenance-forms/aircon-log')->with('success', 'Entry deleted.');
    }

    public function airconLogDelete($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new AirconInspectionLogModel())->update($id, ['is_archived' => 1]);

        return redirect()->to('/maintenance-forms/aircon-log')->with('success', 'Log sheet archived.');
    }

    // ---------- F-FAC-PMP-VMI-004: Vehicle Maintenance Inspection Checklist ----------

    public function vehicleChecklist()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        return view('MaintenanceForms/vehicle_checklist', [
            'title'      => 'Vehicle Maintenance Inspection Checklist',
            'pageCss'    => 'maintenance-forms.css',
            'checklists' => (new VehicleInspectionChecklistModel())->getAllWithItems(),
        ]);
    }

    public function vehicleChecklistAdd()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new VehicleInspectionChecklistModel())->createWithItems([
            'vehicle_type'       => $this->request->getPost('vehicle_type'),
            'plate_no'           => $this->request->getPost('plate_no'),
            'odometer_reading'   => $this->request->getPost('odometer_reading'),
            'mechanic_inspector' => $this->request->getPost('mechanic_inspector'),
            'next_pm_schedule'   => $this->request->getPost('next_pm_schedule') ?: null,
            'inspection_date'    => $this->request->getPost('inspection_date') ?: date('Y-m-d'),
        ]);

        return redirect()->to('/maintenance-forms/vehicle-checklist')->with('success', 'New checklist created — fill in Yes/No per item below.');
    }

    // Same batch-save reasoning as facilityChecklistUpdateItems() — 33 items,
    // one submit.
    public function vehicleChecklistUpdateItems($checklistId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        $itemModel = new VehicleInspectionItemModel();
        foreach ((array) $this->request->getPost('items') as $itemId => $fields) {
            $itemModel->update((int) $itemId, [
                'response' => $fields['response'] ?: null,
                'remarks'  => $fields['remarks'] ?? null,
            ]);
        }

        return redirect()->to('/maintenance-forms/vehicle-checklist')->with('success', 'Checklist items saved.');
    }

    public function vehicleChecklistDelete($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new VehicleInspectionChecklistModel())->update($id, ['is_archived' => 1]);

        return redirect()->to('/maintenance-forms/vehicle-checklist')->with('success', 'Checklist archived.');
    }

    // ---------- F-FAC-GAL-RC-002: Restroom Checklist ----------

    public function restroomChecklist()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        return view('MaintenanceForms/restroom_checklist', [
            'title'      => 'Restroom Checklist',
            'pageCss'    => 'maintenance-forms.css',
            'checklists' => (new RestroomChecklistModel())->getAllWithEntries(),
        ]);
    }

    public function restroomChecklistAdd()
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new RestroomChecklistModel())->insert([
            'location' => $this->request->getPost('location'),
        ]);

        return redirect()->to('/maintenance-forms/restroom')->with('success', 'New checklist created.');
    }

    public function restroomChecklistUpdateHeader($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new RestroomChecklistModel())->update($id, [
            'reviewed_by'   => $this->request->getPost('reviewed_by'),
            'reviewed_date' => $this->request->getPost('reviewed_date') ?: null,
        ]);

        return redirect()->to('/maintenance-forms/restroom')->with('success', 'Sign-off saved.');
    }

    public function restroomChecklistAddEntry($checklistId)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new RestroomChecklistEntryModel())->insert([
            'checklist_id' => $checklistId,
            'entry_date'   => $this->request->getPost('entry_date') ?: date('Y-m-d'),
            'entry_time'   => $this->request->getPost('entry_time') ?: date('H:i:s'),
            'empty_trash'  => $this->request->getPost('empty_trash') ? 1 : 0,
            'refill_paper' => $this->request->getPost('refill_paper') ? 1 : 0,
            'refill_soap'  => $this->request->getPost('refill_soap') ? 1 : 0,
            'clean_floor'  => $this->request->getPost('clean_floor') ? 1 : 0,
            'clean_sink'   => $this->request->getPost('clean_sink') ? 1 : 0,
            'clean_toilet' => $this->request->getPost('clean_toilet') ? 1 : 0,
            'cleaned_by'   => $this->request->getPost('cleaned_by'),
            'signature'    => $this->request->getPost('signature'),
        ]);

        return redirect()->to('/maintenance-forms/restroom')->with('success', 'Entry added.');
    }

    public function restroomChecklistEditEntry($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new RestroomChecklistEntryModel())->update($id, [
            'entry_date'   => $this->request->getPost('entry_date') ?: null,
            'entry_time'   => $this->request->getPost('entry_time') ?: null,
            'empty_trash'  => $this->request->getPost('empty_trash') ? 1 : 0,
            'refill_paper' => $this->request->getPost('refill_paper') ? 1 : 0,
            'refill_soap'  => $this->request->getPost('refill_soap') ? 1 : 0,
            'clean_floor'  => $this->request->getPost('clean_floor') ? 1 : 0,
            'clean_sink'   => $this->request->getPost('clean_sink') ? 1 : 0,
            'clean_toilet' => $this->request->getPost('clean_toilet') ? 1 : 0,
            'cleaned_by'   => $this->request->getPost('cleaned_by'),
            'signature'    => $this->request->getPost('signature'),
        ]);

        return redirect()->to('/maintenance-forms/restroom')->with('success', 'Entry updated.');
    }

    public function restroomChecklistDeleteEntry($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new RestroomChecklistEntryModel())->delete($id);

        return redirect()->to('/maintenance-forms/restroom')->with('success', 'Entry deleted.');
    }

    public function restroomChecklistDelete($id)
    {
        if (!session()->get('isLoggedIn')) return redirect()->to('/login');

        (new RestroomChecklistModel())->update($id, ['is_archived' => 1]);

        return redirect()->to('/maintenance-forms/restroom')->with('success', 'Checklist archived.');
    }
}
