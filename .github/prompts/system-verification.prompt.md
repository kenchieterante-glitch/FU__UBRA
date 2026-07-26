---
name: system-verification
description: "Use when building and verifying the evacuation management system is fully functional. Helps review functions, design patterns, backend database connections, and ensures all controllers, models, and views work together correctly."
parameters:
  - name: component
    description: "The system component to verify (e.g., 'authentication', 'database', 'controllers', 'all')"
    required: false
  - name: scope
    description: "The scope of verification (e.g., 'backend', 'frontend', 'integration', 'full')"
    required: false
---

# FU-UBRA System Verification & Building

You are helping build and ensure the **FU-UBRA Evacuation Management System** is fully functional. This system is built with **CodeIgniter 4** and manages personnel, vehicles, departments, and evacuation tracking.

## Your Role

When asked to help build or verify the system:
1. **Analyze** the relevant code components (controllers, models, database migrations, views)
2. **Check** for proper function implementations and database connections
3. **Verify** that backend operations integrate correctly with frontend views
4. **Design** patterns follow CodeIgniter 4 best practices
5. **Ensure** all dependencies and configurations are properly set up

## System Architecture

- **Framework**: CodeIgniter 4
- **Database**: MySQL/MariaDB (configured in `.env`)
- **Key Models**: User, Personnel, Department, Vehicle, Guardian, Travel, Borrow, Return, Safety, GPS, Prediction, Notification
- **Main Controllers**: AuthController, DashboardController, PersonnelController, VehicleController, ReportController, PredictionController, etc.
- **Database Path**: `app/Database/` (migrations and seeds)
- **Helpers**: inventory_helper, prediction_helper, report_helper
- **Libraries**: AiServices, Calendar, and others
- **Views**: Dynamic views using CodeIgniter's view system

## Verification Checklist

When verifying a component, check:
- ✓ Database migrations exist and are properly structured
- ✓ Model relationships are defined (hasMany, belongsTo)
- ✓ Controller methods handle requests and return appropriate responses
- ✓ Views are properly rendered with correct data
- ✓ Routes are configured in `app/Config/Routes.php`
- ✓ Database connections use proper prepared statements
- ✓ Authentication/authorization filters are applied where needed
- ✓ Error handling is implemented
- ✓ Input validation is performed
- ✓ Helper functions are properly loaded and used

## Common Tasks

When you see requests like:
- **"Make sure X is working"** → Check the controller, model, database, and view files related to X
- **"Build/implement X"** → Create/update the model, migration, controller, and view files as needed
- **"Check database connection"** → Verify `.env` configuration, migrations, and model methods
- **"Review the design"** → Ensure following CodeIgniter 4 conventions and MVC pattern
- **"Test the system"** → Suggest running PHPUnit tests or manual verification steps

## Tools & Files to Reference

- Migration files: `app/Database/Migrations/`
- Models: `app/Models/`
- Controllers: `app/Controllers/`
- Routes: `app/Config/Routes.php`
- Database config: `app/Config/Database.php` and `.env`
- Validation rules: `app/Config/Validation.php` or inline in models
- Views: `app/Views/`

Start by asking clarifying questions about what component or feature needs to be built or verified.
