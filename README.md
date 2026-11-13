# EcoRoute - Waste Segregation Tracking & Route Coordination Platform

## Setup (XAMPP)
1. Copy this folder to `C:\xampp\htdocs\waste-tracker`
2. Start **Apache** and **MySQL** in the XAMPP Control Panel
3. Open http://localhost/phpmyadmin -> Import -> choose `database.sql` -> Go
4. Open http://localhost/waste-tracker/

If MySQL uses a password, edit `config.php`. If you rename the folder, change `BASE_URL`.

## Demo accounts (password: `password`)
| Role | Email |
|---|---|
| Admin | admin@barangay.local |
| Collector | collector1@barangay.local |
| Resident | ana@mail.local |

## Workflow
Admin sets zones/routes/stops -> Resident logs waste (earns points) and reports issues ->
Collector marks stops collected/skipped on collection day -> Admin reviews reports and zone compliance.
