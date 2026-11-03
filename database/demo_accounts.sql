-- Standalone demo installation: creates the UI database and all tables before adding accounts.
-- Safe to re-import; existing accounts and the older recyclables_db database are preserved.
-- Dedicated UI-version database. The older recyclables_db database is left untouched.
-- Import into phpMyAdmin. Existing data is not dropped.
CREATE DATABASE IF NOT EXISTS recyclables_ui_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE recyclables_ui_db;
CREATE TABLE IF NOT EXISTS users (
 id INT AUTO_INCREMENT PRIMARY KEY, full_name VARCHAR(100) NOT NULL,
 email VARCHAR(254) NOT NULL UNIQUE, university_id VARCHAR(20) NOT NULL UNIQUE,
 faculty VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL,
 role ENUM('user','faculty','coordinator','admin') NOT NULL DEFAULT 'user',
 status ENUM('active','suspended','inactive') NOT NULL DEFAULT 'active',
 can_join TINYINT NOT NULL DEFAULT 1, can_submit TINYINT NOT NULL DEFAULT 1,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT chk_student_identity CHECK (role <> 'user' OR (email LIKE '%@mail.apu.edu.my' AND email REGEXP '^[^[:space:]@]+@mail[.]apu[.]edu[.]my$' AND CHAR_LENGTH(university_id)=8 AND BINARY university_id REGEXP '^TP[0-9]{6}$'))
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS challenges (
 id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(180) NOT NULL, category VARCHAR(50) NOT NULL,
 description TEXT NOT NULL, tracked_item VARCHAR(80) NOT NULL DEFAULT 'Approved activity', points INT NOT NULL DEFAULT 0, start_date DATE NOT NULL, end_date DATE NOT NULL,
 submission_limit INT NOT NULL DEFAULT 1, eligibility VARCHAR(50) NOT NULL DEFAULT 'Students and staff',
 completion_requirement VARCHAR(255) NOT NULL DEFAULT '', status ENUM('open','closed','draft') NOT NULL DEFAULT 'open',
 image VARCHAR(100) NOT NULL DEFAULT '', created_by INT NULL,
 FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS user_challenges (
 user_id INT NOT NULL, challenge_id INT NOT NULL, joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(user_id,challenge_id), FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(challenge_id) REFERENCES challenges(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS submissions (
 id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, challenge_id INT NOT NULL,
 description TEXT NOT NULL, quantity INT NOT NULL DEFAULT 1, photo VARCHAR(100) NOT NULL, file_hash CHAR(64) NOT NULL,
 status ENUM('pending','approved','rejected','needs-revision') NOT NULL DEFAULT 'pending',
 review_notes TEXT NULL, reviewer_id INT NULL, points_awarded INT NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, reviewed_at DATETIME NULL,
 FOREIGN KEY(user_id) REFERENCES users(id), FOREIGN KEY(challenge_id) REFERENCES challenges(id),
 FOREIGN KEY(reviewer_id) REFERENCES users(id) ON DELETE SET NULL, INDEX(file_hash)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS point_transactions (
 id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, submission_id INT NULL UNIQUE,
 points INT NOT NULL, reason VARCHAR(255) NOT NULL, actor_id INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id), FOREIGN KEY(submission_id) REFERENCES submissions(id),
 FOREIGN KEY(actor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS achievements (
 id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120) NOT NULL, status ENUM('draft','active','archived') NOT NULL DEFAULT 'draft',
 image VARCHAR(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS achievement_rules (
 id INT AUTO_INCREMENT PRIMARY KEY, achievement_id INT NOT NULL, required_count INT NOT NULL,
 item VARCHAR(80) NOT NULL, condition_name VARCHAR(80) NOT NULL,
 FOREIGN KEY(achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS campaigns (
 id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(180) NOT NULL, description TEXT NOT NULL,
 participant_target INT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL,
 bonus_points INT NOT NULL DEFAULT 0, achievement_id INT NULL, created_by INT NULL,
 FOREIGN KEY(achievement_id) REFERENCES achievements(id) ON DELETE SET NULL,
 FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS announcements (
 id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(180) NOT NULL, message TEXT NOT NULL,
 publish_date DATE NOT NULL, audience VARCHAR(100) NOT NULL, faculty VARCHAR(100) NULL, created_by INT NULL,
 FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS campaign_challenges (
 campaign_id INT NOT NULL, challenge_id INT NOT NULL, PRIMARY KEY(campaign_id,challenge_id),
 FOREIGN KEY(campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
 FOREIGN KEY(challenge_id) REFERENCES challenges(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS campaign_awards (
 campaign_id INT NOT NULL, user_id INT NOT NULL, awarded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(campaign_id,user_id), FOREIGN KEY(campaign_id) REFERENCES campaigns(id),
 FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS user_achievements (
 user_id INT NOT NULL, achievement_id INT NOT NULL, PRIMARY KEY(user_id,achievement_id),
 FOREIGN KEY(user_id) REFERENCES users(id), FOREIGN KEY(achievement_id) REFERENCES achievements(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS faculty_activities (
 id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(180) NOT NULL, faculty VARCHAR(100) NOT NULL,
 activity_date DATETIME NOT NULL, location VARCHAR(180) NOT NULL, participant_limit INT NOT NULL,
 details TEXT NOT NULL, created_by INT NULL, FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS faculty_promotions (
 faculty VARCHAR(100) NOT NULL, challenge_id INT NOT NULL, created_by INT NULL,
 PRIMARY KEY(faculty,challenge_id), FOREIGN KEY(challenge_id) REFERENCES challenges(id) ON DELETE CASCADE,
 FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS faculty_reports (
 id INT AUTO_INCREMENT PRIMARY KEY, activity VARCHAR(180) NOT NULL, period VARCHAR(80) NOT NULL,
 participants INT NOT NULL, outcome TEXT NOT NULL, attachment VARCHAR(100) NOT NULL DEFAULT '',
 faculty VARCHAR(100) NOT NULL, created_by INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS audit_logs (
 id INT AUTO_INCREMENT PRIMARY KEY, actor_id INT NULL, action VARCHAR(80) NOT NULL, details TEXT NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(actor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO users (full_name,email,university_id,faculty,role,status,password,can_join,can_submit) VALUES ('Demo User','tp900001@mail.apu.edu.my','TP900001','School of Computing','user','active','$2y$10$WL4uOrJ8eti/PP8fZuNA.O3ZY9ODpM5IaWlsGVmmZ0iGV.uonqAUm','1','1');
INSERT IGNORE INTO users (full_name,email,university_id,faculty,role,status,password,can_join,can_submit) VALUES ('Demo Faculty','faculty@mail.apu.edu.my','DEMOFACULTY','School of Computing','faculty','active','$2y$10$gHBXTzyzUwblB00OdTsnd.BlzVJMx2kGbYLrGS96KYDGV0.Y6.OLS','1','1');
INSERT IGNORE INTO users (full_name,email,university_id,faculty,role,status,password,can_join,can_submit) VALUES ('Test Coordinator','coordinator@mail.apu.edu.my','DEMOCOORD','Campus Sustainability','coordinator','active','$2y$10$.vEDfjXTYClw9GS1XIfUU.fpXFDnn3M3RJSK5xfsNVFTroH03.wrC','1','1');
INSERT IGNORE INTO users (full_name,email,university_id,faculty,role,status,password,can_join,can_submit) VALUES ('Demo Administrator','admin@mail.apu.edu.my','DEMOADMIN','Campus Sustainability','admin','active','$2y$10$OHxQxI6y4P6jejt4iioMyO67PWunFkOz0HZ1ECpo578Am1zSEmwWa','1','1');
INSERT IGNORE INTO users (full_name,email,university_id,faculty,role,status,password,can_join,can_submit) VALUES ('TEST Workflow Student','tp900002@mail.apu.edu.my','TP900002','School of Computing','user','active','$2y$10$MewJ6rb2OY/Rx0xOH351t.9m28qzkD4RAlNxXbZyhf0pKTrcSgqi6','1','1');
INSERT IGNORE INTO users (full_name,email,university_id,faculty,role,status,password,can_join,can_submit) VALUES ('TEST Computing Student','tp900003@mail.apu.edu.my','TP900003','School of Computing','user','active','$2y$10$5APpl/BAkmt/JR.8aroJEuaPTWoFafmDlymyp00Dikgo21D5ieAV6','1','1');
INSERT IGNORE INTO users (full_name,email,university_id,faculty,role,status,password,can_join,can_submit) VALUES ('TEST Engineering Student','tp900004@mail.apu.edu.my','TP900004','School of Engineering','user','active','$2y$10$hqXdQnThK./UeijHmxrE8OyrQcYqcoGZ4yD2Ql513rd2Nk18fh.g.','1','1');
INSERT IGNORE INTO users (full_name,email,university_id,faculty,role,status,password,can_join,can_submit) VALUES ('TEST Business Student','tp900005@mail.apu.edu.my','TP900005','School of Business','user','active','$2y$10$Aop0nOmn8OwpsjZraZQVC.hoS1de30oiycjzoUOjMerfKZDEp8jPu','1','1');
INSERT IGNORE INTO users (full_name,email,university_id,faculty,role,status,password,can_join,can_submit) VALUES ('TEST Engineering Representative','engineering.rep@mail.apu.edu.my','TESTENGREP01','School of Engineering','faculty','active','$2y$10$uxe6fQ2GU0XiJw/sXBYDpO9By3a1dHalTgC7PAZofDB8RdXB0YTK2','1','1');
INSERT IGNORE INTO users (full_name,email,university_id,faculty,role,status,password,can_join,can_submit) VALUES ('TEST Suspended Student','tp900006@mail.apu.edu.my','TP900006','School of Business','user','suspended','$2y$10$DanecgJ10DX4LOXi.GOGhO15PmuACPiseBs0PQkTaFqdRBw7W48J.','1','1');
INSERT IGNORE INTO users (full_name,email,university_id,faculty,role,status,password,can_join,can_submit) VALUES ('Test-Create Updated','tp900007@mail.apu.edu.my','TP900007','School of Computing','user','suspended','$2y$10$/L/d8AfaC9Qz5DDf/DCxau978tYUM7rfs9Xs7Ygxkme3/KqX6rCQq','1','1');
INSERT IGNORE INTO users (full_name,email,university_id,faculty,role,status,password,can_join,can_submit) VALUES ('TEST Registration User Updated','tp900008@mail.apu.edu.my','TP900008','School of Computing','user','active','$2y$10$KG7DmzGWLgplS4L2R7JU7etsS70tTod2WWaxQHZd6PBLhQ43Lsr7G','1','1');
