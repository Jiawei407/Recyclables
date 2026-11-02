<?php
// Real SQL regression: disposable namespaces only; never connects to a normal database.
if (PHP_SAPI !== 'cli') exit(1);
$prefix=getenv('RECYCLABLES_TEST_DB_NAME')?:'';
if (!preg_match('/^recyclables_test_[a-z0-9_]{1,35}$/D',$prefix)) exit("Set a new recyclables_test_* prefix.\n");
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
$db=new mysqli(getenv('RECYCLABLES_TEST_DB_HOST')?:'127.0.0.1',getenv('RECYCLABLES_TEST_DB_USER')?:'root',getenv('RECYCLABLES_TEST_DB_PASSWORD')?:'','',(int)(getenv('RECYCLABLES_TEST_DB_PORT')?:3306));
$owned=[];$checks=0;
$assert=static function($condition,$message) use (&$checks){if(!$condition)throw new RuntimeException($message);$checks++;};
$run=static function($sql) use($db){$db->multi_query($sql);do{if($r=$db->store_result())$r->free();}while($db->more_results()&&$db->next_result());};
try {
    $legacy=$prefix.'_legacy';$ui=$prefix.'_ui';
    foreach ([$legacy,$ui] as $name){$db->query('CREATE DATABASE `'.$name.'`');$owned[]=$name;}
    $db->select_db($legacy);
    $db->query('CREATE TABLE users (id INT PRIMARY KEY,student_id VARCHAR(20),full_name VARCHAR(100))');
    $db->query("INSERT INTO users VALUES(1,'TP123456','Existing legacy user')");
    $schema=file_get_contents(dirname(__DIR__).'/database/recyclables_db.sql');
    $schema=str_replace('recyclables_ui_db',$ui,$schema);
    $run($schema);
    $seed=file_get_contents(dirname(__DIR__).'/database/demo_accounts.sql');
    $seed=str_replace('USE recyclables_ui_db;','USE `'.$ui.'`;',$seed);
    // phpMyAdmin may have the older database selected when an import starts.
    $db->select_db($legacy);$run($seed);
    $assert((int)$db->query('SELECT COUNT(*) n FROM `'.$ui.'`.users')->fetch_assoc()['n']===12,'Missing demo accounts');
    foreach (['tp900001@mail.apu.edu.my'=>'student123@','faculty@mail.apu.edu.my'=>'faculty123@','coordinator@mail.apu.edu.my'=>'coordinator123@','admin@mail.apu.edu.my'=>'admin123@'] as $email=>$password){
        $row=$db->query("SELECT * FROM `".$ui."`.users WHERE email='".$db->real_escape_string($email)."'")->fetch_assoc();
        $assert($row&&password_verify($password,$row['password']),'Demo login failed: '.$email);
    }
    $db->query("UPDATE `".$ui."`.users SET full_name='Keep my changes' WHERE email='tp900001@mail.apu.edu.my'");
    $db->select_db($legacy);$run($seed);
    $assert((int)$db->query('SELECT COUNT(*) n FROM `'.$ui.'`.users')->fetch_assoc()['n']===12,'Repeat import duplicates accounts');
    $assert($db->query("SELECT full_name FROM `".$ui."`.users WHERE email='tp900001@mail.apu.edu.my'")->fetch_assoc()['full_name']==='Keep my changes','Repeat import overwrites account');
    $assert($db->query('SELECT * FROM `'.$legacy.'`.users')->fetch_assoc()===['id'=>'1','student_id'=>'TP123456','full_name'=>'Existing legacy user'],'Legacy account changed');
    $assert($db->query('SHOW COLUMNS FROM `'.$legacy.'`.users LIKE \'university_id\'')->num_rows===0,'Legacy schema changed');
    echo "PASS: $checks SQL installation checks (legacy collision, all role logins, repeat import, preserved legacy data).\n";
} finally {
    foreach(array_reverse($owned) as $name)$db->query('DROP DATABASE `'.$name.'`');
}
