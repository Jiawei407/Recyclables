-- Account-only revision. Existing user IDs, roles and all other records are preserved.
USE recyclables_db;
START TRANSACTION;
UPDATE users SET email='tp900001@mail.apu.edu.my', university_id='TP900001', password='$2y$10$WL4uOrJ8eti/PP8fZuNA.O3ZY9ODpM5IaWlsGVmmZ0iGV.uonqAUm' WHERE id=1;
UPDATE users SET email='faculty@mail.apu.edu.my', university_id='DEMOFACULTY', password='$2y$10$gHBXTzyzUwblB00OdTsnd.BlzVJMx2kGbYLrGS96KYDGV0.Y6.OLS' WHERE id=2;
UPDATE users SET email='coordinator@mail.apu.edu.my', university_id='DEMOCOORD', password='$2y$10$.vEDfjXTYClw9GS1XIfUU.fpXFDnn3M3RJSK5xfsNVFTroH03.wrC' WHERE id=3;
UPDATE users SET email='admin@mail.apu.edu.my', university_id='DEMOADMIN', password='$2y$10$OHxQxI6y4P6jejt4iioMyO67PWunFkOz0HZ1ECpo578Am1zSEmwWa' WHERE id=4;
UPDATE users SET email='tp900002@mail.apu.edu.my', university_id='TP900002', password='$2y$10$MewJ6rb2OY/Rx0xOH351t.9m28qzkD4RAlNxXbZyhf0pKTrcSgqi6' WHERE id=9;
UPDATE users SET email='tp900003@mail.apu.edu.my', university_id='TP900003', password='$2y$10$5APpl/BAkmt/JR.8aroJEuaPTWoFafmDlymyp00Dikgo21D5ieAV6' WHERE id=10;
UPDATE users SET email='tp900004@mail.apu.edu.my', university_id='TP900004', password='$2y$10$hqXdQnThK./UeijHmxrE8OyrQcYqcoGZ4yD2Ql513rd2Nk18fh.g.' WHERE id=11;
UPDATE users SET email='tp900005@mail.apu.edu.my', university_id='TP900005', password='$2y$10$Aop0nOmn8OwpsjZraZQVC.hoS1de30oiycjzoUOjMerfKZDEp8jPu' WHERE id=12;
UPDATE users SET email='engineering.rep@mail.apu.edu.my', university_id='TESTENGREP01', password='$2y$10$uxe6fQ2GU0XiJw/sXBYDpO9By3a1dHalTgC7PAZofDB8RdXB0YTK2' WHERE id=13;
UPDATE users SET email='tp900006@mail.apu.edu.my', university_id='TP900006', password='$2y$10$DanecgJ10DX4LOXi.GOGhO15PmuACPiseBs0PQkTaFqdRBw7W48J.' WHERE id=14;
UPDATE users SET email='tp900007@mail.apu.edu.my', university_id='TP900007', password='$2y$10$/L/d8AfaC9Qz5DDf/DCxau978tYUM7rfs9Xs7Ygxkme3/KqX6rCQq' WHERE id=16;
UPDATE users SET email='tp900008@mail.apu.edu.my', university_id='TP900008', password='$2y$10$KG7DmzGWLgplS4L2R7JU7etsS70tTod2WWaxQHZd6PBLhQ43Lsr7G' WHERE id=17;
COMMIT;
ALTER TABLE users ADD CONSTRAINT chk_student_identity CHECK (role <> 'user' OR (email LIKE '%@mail.apu.edu.my' AND email REGEXP '^[^[:space:]@]+@mail[.]apu[.]edu[.]my$' AND CHAR_LENGTH(university_id)=8 AND BINARY university_id REGEXP '^TP[0-9]{6}$'));
