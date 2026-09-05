<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

// The 5 official Foundation University Facilities Maintenance Program forms
// (F-FAC-PMP-FMC-001, F-FAC-PMP-EML-002, F-FAC-PMP-AIL-003, F-FAC-PMP-VMI-004,
// F-FAC-GAL-RC-002), digitized as editable records instead of paper/Pages
// files. Each is a header table (one row per submission) plus a child table
// for its repeating rows — the same header+child+seed shape already used by
// aircon_units/aircon_checklist_items for the two forms with a fixed item
// list (Facility, Vehicle); the other three (Equipment, Aircon Inspection,
// Restroom) are simple log books where staff add one row per event.
class CreateMaintenanceFormsTables extends Migration
{
    public function up()
    {
        // F-FAC-PMP-FMC-001 — Facility Maintenance Checklist
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'inspector' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'building_area' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'inspection_date' => ['type' => 'DATE', 'null' => true],
            'inspection_type' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'overall_condition' => ['type' => 'ENUM', 'constraint' => ['Excellent', 'Satisfactory', 'Unsatisfactory'], 'null' => true],
            'summary_findings' => ['type' => 'TEXT', 'null' => true],
            'corrective_action_plan' => ['type' => 'TEXT', 'null' => true],
            'reviewed_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'reviewed_date' => ['type' => 'DATE', 'null' => true],
            'approved_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'is_archived' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('facility_checklists');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'checklist_id' => ['type' => 'INT', 'constraint' => 11],
            'section' => ['type' => 'VARCHAR', 'constraint' => 150],
            'item_code' => ['type' => 'VARCHAR', 'constraint' => 10],
            'item_label' => ['type' => 'TEXT'],
            'rating' => ['type' => 'ENUM', 'constraint' => ['C', 'MI', 'MJ', 'N/A'], 'null' => true],
            'corrective_action' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('checklist_id');
        $this->forge->createTable('facility_checklist_items');

        // F-FAC-PMP-EML-002 — Equipment Maintenance Log
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'department' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'date_submitted' => ['type' => 'DATE', 'null' => true],
            'reviewed_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'reviewed_date' => ['type' => 'DATE', 'null' => true],
            'approved_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'is_archived' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('equipment_maintenance_logs');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'log_id' => ['type' => 'INT', 'constraint' => 11],
            'entry_date' => ['type' => 'DATE', 'null' => true],
            'asset_name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'serial_number' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'maintenance_frequency' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'work_description' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'next_due_date' => ['type' => 'DATE', 'null' => true],
            'performed_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'signature' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('log_id');
        $this->forge->createTable('equipment_maintenance_entries');

        // F-FAC-PMP-AIL-003 — Aircon Inspection Log
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'performed_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'date_submitted' => ['type' => 'DATE', 'null' => true],
            'reviewed_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'reviewed_date' => ['type' => 'DATE', 'null' => true],
            'approved_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'is_archived' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('aircon_inspection_logs');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'log_id' => ['type' => 'INT', 'constraint' => 11],
            'entry_date' => ['type' => 'DATE', 'null' => true],
            'department' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'qty' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'room_no' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'aircon_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'work_done' => ['type' => 'TEXT', 'null' => true],
            'remarks' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('log_id');
        $this->forge->createTable('aircon_inspection_entries');

        // F-FAC-PMP-VMI-004 — Vehicle Maintenance Inspection Checklist
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'vehicle_type' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'plate_no' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'odometer_reading' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'mechanic_inspector' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'next_pm_schedule' => ['type' => 'DATE', 'null' => true],
            'inspection_date' => ['type' => 'DATE', 'null' => true],
            'is_archived' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('vehicle_inspection_checklists');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'checklist_id' => ['type' => 'INT', 'constraint' => 11],
            'section' => ['type' => 'VARCHAR', 'constraint' => 100],
            'item_label' => ['type' => 'VARCHAR', 'constraint' => 255],
            'response' => ['type' => 'ENUM', 'constraint' => ['Yes', 'No'], 'null' => true],
            'remarks' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('checklist_id');
        $this->forge->createTable('vehicle_inspection_items');

        // F-FAC-GAL-RC-002 — Restroom Checklist
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'location' => ['type' => 'VARCHAR', 'constraint' => 150],
            'reviewed_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'reviewed_date' => ['type' => 'DATE', 'null' => true],
            'is_archived' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('restroom_checklists');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'checklist_id' => ['type' => 'INT', 'constraint' => 11],
            'entry_date' => ['type' => 'DATE', 'null' => true],
            'entry_time' => ['type' => 'TIME', 'null' => true],
            'empty_trash' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'refill_paper' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'refill_soap' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'clean_floor' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'clean_sink' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'clean_toilet' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cleaned_by' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'signature' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('checklist_id');
        $this->forge->createTable('restroom_checklist_entries');
    }

    public function down()
    {
        $this->forge->dropTable('restroom_checklist_entries');
        $this->forge->dropTable('restroom_checklists');
        $this->forge->dropTable('vehicle_inspection_items');
        $this->forge->dropTable('vehicle_inspection_checklists');
        $this->forge->dropTable('aircon_inspection_entries');
        $this->forge->dropTable('aircon_inspection_logs');
        $this->forge->dropTable('equipment_maintenance_entries');
        $this->forge->dropTable('equipment_maintenance_logs');
        $this->forge->dropTable('facility_checklist_items');
        $this->forge->dropTable('facility_checklists');
    }
}
