<?php
// Browser fixtures only: a new, marked and entirely empty disposable schema is required.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$name = getenv('RECYCLABLES_TEST_DB_NAME') ?: '';
if (!preg_match('/^recyclables_test_[a-z0-9_]+$/D', $name)) {
    fwrite(STDERR, "Set RECYCLABLES_TEST_DB_NAME to a new recyclables_test_<suffix> schema.\n");
    exit(1);
}
$password = getenv('RECYCLABLES_BROWSER_PASSWORD');
if ($password === false || strlen($password) < 8) {
    fwrite(STDERR, "Set RECYCLABLES_BROWSER_PASSWORD explicitly (at least eight characters).\n");
    exit(1);
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = null; $ownedFile = ''; $transaction = false;
try {
    $db = new mysqli(getenv('RECYCLABLES_TEST_DB_HOST') ?: '127.0.0.1',
        getenv('RECYCLABLES_TEST_DB_USER') ?: 'root', getenv('RECYCLABLES_TEST_DB_PASSWORD') ?: '',
        $name, (int)(getenv('RECYCLABLES_TEST_DB_PORT') ?: 3306));
    $db->set_charset('utf8mb4');
    $markers = $db->query('SELECT purpose FROM _test_harness')->fetch_all(MYSQLI_ASSOC);
    if (count($markers) !== 1 || $markers[0]['purpose'] !== 'recyclables-tests') {
        throw new RuntimeException('Missing disposable test database marker. Run tests/prepare-database.php first.');
    }
    // Never reset fixtures or assume an empty users table means the rest of the schema is empty.
    foreach ($db->query('SHOW TABLES')->fetch_all() as $row) {
        if ($row[0] === '_test_harness') continue;
        $table = str_replace('`', '``', $row[0]);
        if ($db->query('SELECT 1 FROM `' . $table . '` LIMIT 1')->num_rows) {
            throw new RuntimeException('Refusing nonempty table: ' . $row[0]);
        }
    }
    $db->begin_transaction(); $transaction = true;
    $execute = static function (string $sql, array $params = []) use ($db): void {
        $statement = $db->prepare($sql);
        if ($params) $statement->bind_param(str_repeat('s', count($params)), ...$params);
        $statement->execute(); $statement->close();
    };
    $hash = password_hash($password, PASSWORD_DEFAULT);
    foreach (['user', 'faculty', 'coordinator', 'admin'] as $index => $role) {
        $id = $index + 1;
        $execute('INSERT INTO users (id,full_name,email,university_id,faculty,password,role) VALUES (?,?,?,?,?,?,?)',
            [$id, 'Audit ' . ucfirst($role), $role === 'user' ? 'tp810001@mail.apu.edu.my' : $role . '@mail.apu.edu.my',
                $role === 'user' ? 'TP810001' : 'AUDIT' . $id, 'School of Computing', $hash, $role]);
    }
    $folder = dirname(__DIR__, 2) . '/storage/uploads';
    if (!is_dir($folder) && !mkdir($folder, 0755, true)) throw new RuntimeException('Could not create upload folder.');
    $filename = bin2hex(random_bytes(16)) . '.png';
    $fixture = $folder . '/' . $filename;
    $handle = fopen($fixture, 'xb');
    if (!$handle) throw new RuntimeException('Could not create a new private fixture file.');
    $ownedFile = $fixture;
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=', true);
    $written = fwrite($handle, $png); fclose($handle);
    if ($written !== strlen($png)) throw new RuntimeException('Could not write the private fixture file.');

    $execute("INSERT INTO challenges (id,title,category,description,tracked_item,points,start_date,end_date,submission_limit,completion_requirement,status,created_by) VALUES (1,'Campus bottle collection','Recycling','Collect and sort clean plastic bottles.','Plastic bottles',20,DATE_SUB(CURDATE(),INTERVAL 1 YEAR),DATE_ADD(CURDATE(),INTERVAL 1 YEAR),10,'Submit a clear photograph and quantity.','open',3)");
    $execute('INSERT INTO user_challenges (user_id,challenge_id) VALUES (1,1)');
    foreach (['pending', 'approved', 'rejected', 'needs-revision'] as $index => $status) {
        $id = $index + 1;
        $execute('INSERT INTO submissions (id,user_id,challenge_id,description,quantity,photo,file_hash,status,points_awarded,reviewer_id,review_notes,reviewed_at) VALUES (?,1,1,?,?,?,?,?,?,?, ?,?)',
            [$id, 'Audit fixture: ' . $status . ' bottle collection.', $status === 'approved' ? 10 : 1, $filename,
                str_repeat(dechex($id), 64), $status, $status === 'approved' ? 20 : 0,
                $status === 'pending' ? null : 3, $status === 'pending' ? null : 'Evidence checked.',
                $status === 'pending' ? null : date('Y-m-d H:i:s')]);
    }
    $queries = [
        "INSERT INTO point_transactions (user_id,submission_id,points,reason,actor_id) VALUES (1,2,20,'Approved evidence',3)",
        "INSERT INTO achievements (id,name,status) VALUES (1,'Recycling Helper','active'),(2,'Campus Champion','active')",
        "INSERT INTO achievement_rules (achievement_id,required_count,item,condition_name) VALUES (1,10,'Plastic bottles','At least'),(2,100,'Plastic bottles','At least')",
        "INSERT INTO campaigns (id,name,description,participant_target,start_date,end_date,bonus_points,created_by) VALUES (1,'Campus collection week','Audit campaign fixture',100,DATE_SUB(CURDATE(),INTERVAL 1 YEAR),DATE_ADD(CURDATE(),INTERVAL 1 YEAR),10,3)",
        'INSERT INTO campaign_challenges (campaign_id,challenge_id) VALUES (1,1)',
        "INSERT INTO announcements (id,title,message,publish_date,audience,faculty,created_by) VALUES (1,'Faculty collection day','Bring clean recyclable bottles to the collection point.',CURDATE(),'My faculty','School of Computing',2)",
        "INSERT INTO faculty_activities (id,name,faculty,activity_date,location,participant_limit,details,created_by) VALUES (1,'Faculty cleanup','School of Computing',DATE_ADD(NOW(),INTERVAL 1 MONTH),'Campus courtyard',50,'Bring reusable gloves.',2)",
        "INSERT INTO faculty_promotions (faculty,challenge_id,created_by) VALUES ('School of Computing',1,2)",
        "INSERT INTO faculty_reports (id,activity,period,participants,outcome,faculty,created_by) VALUES (1,'Faculty cleanup','Browser fixture period',25,'Collected and sorted recyclable material.','School of Computing',2)",
        "INSERT INTO audit_logs (id,actor_id,action,details) VALUES (1,4,'Audit fixture','Created a disposable browser audit fixture.')"
    ];
    foreach ($queries as $sql) $execute($sql);
    $db->commit(); $transaction = false;
    echo 'Browser fixtures created in disposable schema ' . $name . '. Private fixture: ' . $filename . "\n";
} catch (Throwable $exception) {
    if ($transaction && $db) $db->rollback();
    if ($ownedFile !== '' && is_file($ownedFile)) unlink($ownedFile);
    fwrite(STDERR, 'Browser fixture setup failed: ' . $exception->getMessage() . "\n");
    exit(1);
}
