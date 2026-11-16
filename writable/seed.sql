-- FU-UBRA demo seed data
SET FOREIGN_KEY_CHECKS=0;
TRUNCATE departments;
TRUNCATE personnel;
TRUNCATE tools;
TRUNCATE vehicles;
TRUNCATE notifications;
TRUNCATE janitorial;
TRUNCATE gps_logs;
TRUNCATE predictions;
TRUNCATE reports;
SET FOREIGN_KEY_CHECKS=1;

INSERT INTO departments (id,name) VALUES
(1,'Facilities'),(2,'Logistics'),(3,'Security'),(4,'Housekeeping'),
(5,'College of IT'),(6,'Administration'),(7,'Athletics'),(8,'Finance');

INSERT INTO personnel (emp_id,full_name,email,department_id,position,assigned_task,status) VALUES
('EMP-2024-089','Engr. James Diaz','james.diaz@foundation.edu.ph',1,'Physical Plant Supr.','AC Maintenance A','Active'),
('EMP-2021-042','Maria Clara Santos','maria.clara@foundation.edu.ph',2,'Lead Dispatcher','Unassigned','On Leave'),
('EMP-2019-115','Col. Arthur Miller','arthur.miller@foundation.edu.ph',3,'Safety & Security Chief','GPS Route Validation','Active'),
('EMP-2023-142','Sonia G. Ramirez','sonia.ramirez@foundation.edu.ph',4,'Cleaning Operative','Science Lab Cleaning','Active'),
('EMP-2022-071','Pedro Penduko','pedro.penduko@foundation.edu.ph',5,'IT Support','Network Deployment','Active'),
('EMP-2020-034','Juan dela Cruz','juan.delacruz@foundation.edu.ph',2,'Driver','Van-01 Dispatch','Active'),
('EMP-2023-210','Rodrigo S. Cruz','rodrigo.cruz@foundation.edu.ph',2,'Senior Driver','Bus-02 Assignment','Active'),
('EMP-2018-009','Dr. Helen Peralta','helen.peralta@foundation.edu.ph',6,'Department Head','Trip Approvals','On Leave');

INSERT INTO tools (asset_name,asset_code,category,location,custodian,condition_status,availability) VALUES
('Field Power Gen II','AST-10492','Generator','Main Utility Bldg','Engr. James Diaz','Fair','Maintenance'),
('MacBook Pro 16','AST-92041','IT Equipment','Deans Office, CCS','Maria Clara Santos','Excellent','Borrowed'),
('Floors Buffer Matt','AST-03481','Janitorial','Janitor Depot B','Sonia G. Ramirez','Excellent','Available'),
('Sony Alpha A7 III','AST-00612','Media Studio','Media Center','Col. Arthur Miller','Poor','Available'),
('Epson Projector X50','AST-77120','IT Equipment','AVR Room 2','Pedro Penduko','Good','Available'),
('Industrial Vacuum','AST-55019','Janitorial','Housekeeping Store','Sonia G. Ramirez','Good','Borrowed'),
('Cordless Drill Set','AST-30188','Tools','Maintenance Shop','Engr. James Diaz','Excellent','Available'),
('Water Dispenser Hot/Cold','AST-11002','Appliance','Admin Lobby','Juan dela Cruz','Fair','Available');

INSERT INTO vehicles (vehicle_name,plate_no,type,driver,department,gps_status,inspection_status,availability) VALUES
('Toyota Coaster','FUA-8802','30-Seater Bus','Juan Santos','Athletics','Online','Due Soon','In Use'),
('Toyota Hilux','FUA-4311','4x4 Utility Truck','Cardo Dalisay','Property & General','Online','Completed','Available'),
('Nissan Urvan','FUA-9915','15-Seater Van','Pedro Penduko','College of IT','Offline','Expired','Inactive'),
('Mitsubishi L300','FUA-2041','Multi-purpose Van','Juan dela Cruz','Human Resources','Online','Completed','In Use'),
('Hyundai County','FUA-8801','28-Seater Coaster','Andres Bonifacio','Administration','Online','Completed','Available'),
('Toyota Innova','FUA-7712','7-Seater MPV','Goyo del Pilar','College of Nursing','Online','Completed','Reserved'),
('Isuzu Elf Truck','FUA-5099','6-Wheeler Cargo','Jose Rizal','Campus Physical','Online','Expired','Maintenance');

INSERT INTO notifications (category,description,recipient,priority,status) VALUES
('Vehicle Inspection','Routine vehicle health compliance check is scheduled tomorrow.','Operations Team','Critical','Unread'),
('Air-Con Cleaning','Air Conditioner Building A preventive maintenance starts in 2 days.','Facilities Dept','Routine','Read'),
('Janitorial Assignment','Weekly deep disinfection assignment schedule for Team B begins tomorrow.','Team B Duty','Routine','Read'),
('Inventory Low Stock','Critical spare parts are low on warehouse inventory.','Office Supplies','Critical','Unread'),
('Vehicle Expiry','Registration for Utility Truck-04 completed earlier than expected.','Registration','Routine','Read');

INSERT INTO janitorial (team_name,assigned_area,task,schedule_date,status) VALUES
('Team A','Main Library','General Cleaning','2026-07-18','Pending'),
('Team B','Science Complex','Deep Disinfection','2026-07-19','Pending'),
('Team C','Admin Building','AC Filter Cleaning','2026-07-20','Completed');

INSERT INTO predictions (module,insight_text,suggestion_text) VALUES
('Dashboard','Vehicle Van-01 inspection is due tomorrow.','Generate Report'),
('Dashboard','Inventory of cleaning chemicals is running low.','Notify Personnel');

INSERT INTO reports (report_name,generated_by,type_module,status,file_path) VALUES
('Q3_Fleet_Compliance_v2','Admin Rodrigo','Vehicle Fleet','Completed',''),
('Weekly_Maintenance_Audit','Supervisor Gomez','Facilities Management','Completed','');

INSERT INTO gps_logs (vehicle_id,device_id,latitude,longitude,signal_strength,status) VALUES
(1,'FU-GPS-802',9.3103500,123.3080000,'Strong (98%)','Online'),
(2,'FU-GPS-431',9.3050000,123.3010000,'Strong (91%)','Online'),
(4,'FU-GPS-204',9.3120000,123.3150000,'Medium (74%)','Online');
