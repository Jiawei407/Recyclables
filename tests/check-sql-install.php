<?php
// Real SQL regression: disposable namespaces only; never connects to a normal database.
if (PHP_SAPI !== 'cli') exit(1);
$prefix=getenv('RECYCLABLES_TEST_DB_NAME')?:'';
if (!preg_match('/^recyclables_test_[a-z0-9_]{1,35}$/D',$prefix)) {
    fwrite(STDERR,"Set a new recyclables_test_* prefix.\n");exit(1);
}
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
$db=new mysqli(getenv('RECYCLABLES_TEST_DB_HOST')?:'127.0.0.1',getenv('RECYCLABLES_TEST_DB_USER')?:'root',getenv('RECYCLABLES_TEST_DB_PASSWORD')?:'','',(int)(getenv('RECYCLABLES_TEST_DB_PORT')?:3306));
$owned=[];$checks=0;
$assert=static function($condition,$message) use (&$checks){if(!$condition)throw new RuntimeException($message);$checks++;};
$run=static function($sql) use($db){$db->multi_query($sql);do{if($r=$db->store_result())$r->free();}while($db->more_results()&&$db->next_result());};
try {
    $legacy=$prefix.'_legacy';$ui=$prefix.'_ui';
    $assert($db->query("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='".$db->real_escape_string($ui)."'")->num_rows===0,'UI test namespace already exists');
    $db->query('CREATE DATABASE `'.$legacy.'`');$owned[]=$legacy;
    $db->select_db($legacy);
    $db->query('CREATE TABLE users (id INT PRIMARY KEY,student_id VARCHAR(20),full_name VARCHAR(100))');
    $db->query("INSERT INTO users VALUES(1,'TP123456','Existing legacy user')");
    $schema=file_get_contents(dirname(__DIR__).'/database/recyclables_db.sql');
    $schema=str_replace('recyclables_ui_db',$ui,$schema);
    $seed=file_get_contents(dirname(__DIR__).'/database/demo_accounts.sql');
    $seed=str_replace('recyclables_ui_db',$ui,$seed);
    // phpMyAdmin may have the older database selected when an import starts.
    // Crucially, no UI database/schema has been created before this import.
    $owned[]=$ui;
    $db->select_db($legacy);$run($seed);
    $assert($db->query('SHOW TABLES FROM `'.$ui.'`')->num_rows===preg_match_all('/^CREATE TABLE IF NOT EXISTS /m',$schema),'Standalone demo import did not create the full schema');
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
    $run($schema);
    $assert((int)$db->query('SELECT COUNT(*) n FROM `'.$ui.'`.users')->fetch_assoc()['n']===12,'Separate schema import changed demo records');
    $cli=$prefix.'_cli';
    $assert($db->query("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='".$db->real_escape_string($cli)."'")->num_rows===0,'CLI namespace already exists');
    $owned[]=$cli;
    $env=getenv();
    foreach (['HOST'=>'127.0.0.1','PORT'=>'3306','USER'=>'root','PASSWORD'=>''] as $key=>$fallback) {
        $value=getenv('RECYCLABLES_TEST_DB_'.$key);
        $env['RECYCLABLES_DB_'.$key]=$value===false?$fallback:$value;
    }
    $env['RECYCLABLES_DB_NAME']=$cli;
    $process=proc_open([PHP_BINARY,dirname(__DIR__).'/database/seed_demo.php'],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,null,$env);
    if(!is_resource($process))throw new RuntimeException('Could not launch CLI installer');
    fclose($pipes[0]);$output=stream_get_contents($pipes[1]).stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
    $assert(proc_close($process)===0,'CLI installer failed: '.$output);
    $assert((int)$db->query('SELECT COUNT(*) n FROM `'.$cli.'`.users')->fetch_assoc()['n']===12,'CLI did not initialize its configured database');
    $assert((int)$db->query('SELECT COUNT(*) n FROM `'.$ui.'`.users')->fetch_assoc()['n']===12,'CLI touched another database');
    echo "PASS: $checks SQL installation checks (legacy collision, all role logins, repeat import, preserved legacy data).\n";
} finally {
    foreach(array_reverse($owned) as $name)$db->query('DROP DATABASE IF EXISTS `'.$name.'`');
}
