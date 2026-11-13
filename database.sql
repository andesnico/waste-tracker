-- Waste Segregation Tracking & Route Coordination Platform
-- Import in phpMyAdmin (Import tab). Demo password for all accounts: password
CREATE DATABASE IF NOT EXISTS waste_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE waste_tracker;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS collection_logs, route_stops, routes, reports, segregation_logs, announcements, users, zones;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE zones(
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  description VARCHAR(255) DEFAULT ''
);
CREATE TABLE users(
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('resident','collector','admin') NOT NULL DEFAULT 'resident',
  zone_id INT NULL,
  phone VARCHAR(30) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(zone_id) REFERENCES zones(id) ON DELETE SET NULL
);
CREATE TABLE routes(
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  zone_id INT NOT NULL,
  collector_id INT NULL,
  collection_day ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  waste_type ENUM('biodegradable','recyclable','residual','special') NOT NULL,
  start_time TIME NOT NULL DEFAULT '07:00:00',
  FOREIGN KEY(zone_id) REFERENCES zones(id) ON DELETE CASCADE,
  FOREIGN KEY(collector_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE TABLE route_stops(
  id INT AUTO_INCREMENT PRIMARY KEY,
  route_id INT NOT NULL,
  stop_order INT NOT NULL DEFAULT 1,
  location_name VARCHAR(120) NOT NULL,
  FOREIGN KEY(route_id) REFERENCES routes(id) ON DELETE CASCADE
);
CREATE TABLE collection_logs(
  id INT AUTO_INCREMENT PRIMARY KEY,
  stop_id INT NOT NULL,
  collector_id INT NULL,
  log_date DATE NOT NULL,
  status ENUM('collected','skipped') NOT NULL,
  note VARCHAR(255) DEFAULT '',
  UNIQUE KEY one_per_day(stop_id,log_date),
  FOREIGN KEY(stop_id) REFERENCES route_stops(id) ON DELETE CASCADE
);
CREATE TABLE segregation_logs(
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  waste_type ENUM('biodegradable','recyclable','residual','special') NOT NULL,
  weight_kg DECIMAL(6,2) NOT NULL,
  properly_segregated TINYINT(1) NOT NULL DEFAULT 1,
  points INT NOT NULL DEFAULT 0,
  notes VARCHAR(255) DEFAULT '',
  log_date DATE NOT NULL,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE reports(
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type ENUM('missed_collection','illegal_dumping','pickup_request','other') NOT NULL,
  location VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  status ENUM('pending','in_progress','resolved') NOT NULL DEFAULT 'pending',
  admin_note VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE announcements(
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(120) NOT NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO zones(name,description) VALUES
('Zone 1 - Poblacion','Town center and market area'),
('Zone 2 - Riverside','Residential area along the river'),
('Zone 3 - Hillview','Upper residential subdivision');

-- password for every demo account: password
INSERT INTO users(name,email,password,role,zone_id,phone) VALUES
('Barangay Admin','admin@barangay.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin',NULL,'09170000001'),
('Juan Dela Cruz','collector1@barangay.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','collector',NULL,'09170000002'),
('Maria Santos','collector2@barangay.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','collector',NULL,'09170000003'),
('Ana Reyes','ana@mail.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','resident',1,'09170000004'),
('Pedro Lim','pedro@mail.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','resident',2,'09170000005'),
('Liza Cruz','liza@mail.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','resident',3,'09170000006');

INSERT INTO routes(name,zone_id,collector_id,collection_day,waste_type,start_time) VALUES
('Poblacion Biodegradable Run',1,2,DAYNAME(CURDATE()),'biodegradable','07:00:00'),
('Riverside Recyclables Run',2,3,'Wednesday','recyclable','08:00:00'),
('Hillview Residual Run',3,2,'Friday','residual','07:30:00');

INSERT INTO route_stops(route_id,stop_order,location_name) VALUES
(1,1,'Public Market Gate'),(1,2,'Plaza Street Corner'),(1,3,'Health Center'),
(2,1,'Riverside Bridge'),(2,2,'Purok 4 Entrance'),(2,3,'Covered Court'),
(3,1,'Hillview Gate 1'),(3,2,'Subdivision Clubhouse'),(3,3,'Water Tank Road');

INSERT INTO segregation_logs(user_id,waste_type,weight_kg,properly_segregated,points,log_date) VALUES
(4,'biodegradable',3.5,1,14,CURDATE()-INTERVAL 1 DAY),(4,'recyclable',2.0,1,12,CURDATE()-INTERVAL 3 DAY),
(4,'residual',1.5,0,2,CURDATE()-INTERVAL 5 DAY),(5,'recyclable',4.0,1,14,CURDATE()-INTERVAL 2 DAY),
(5,'biodegradable',2.5,1,13,CURDATE()-INTERVAL 6 DAY),(5,'residual',2.0,0,2,CURDATE()-INTERVAL 8 DAY),
(6,'biodegradable',5.0,1,15,CURDATE()-INTERVAL 1 DAY),(6,'special',0.5,1,11,CURDATE()-INTERVAL 4 DAY),
(6,'recyclable',3.0,0,2,CURDATE()-INTERVAL 7 DAY);

INSERT INTO reports(user_id,type,location,description,status) VALUES
(4,'missed_collection','Plaza Street','Garbage truck did not pass by last Monday.','pending'),
(5,'illegal_dumping','Riverside Bridge','Mixed waste dumped beside the bridge.','in_progress');

INSERT INTO announcements(title,body) VALUES
('Segregation Reminder','Please separate biodegradable, recyclable, residual, and special waste before collection day.'),
('New Collection Schedule','Check your zone schedule on your dashboard. Collectors start at the time listed for each route.');
