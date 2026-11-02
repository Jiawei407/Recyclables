<?php
use PHPUnit\Framework\TestCase;

final class SqlNamespaceTest extends TestCase
{
    public function testSqlImportsAndDefaultConnectionUseTheDedicatedUiDatabase(): void
    {
        $previous=getenv('RECYCLABLES_DB_NAME');
        try {
            putenv('RECYCLABLES_DB_NAME');
            require dirname(__DIR__,2).'/includes/config.php';
            self::assertSame('recyclables_ui_db',$db_name);
            foreach (['recyclables_db.sql','demo_accounts.sql','account_updates.sql'] as $file) {
                $sql=file_get_contents(dirname(__DIR__,2).'/database/'.$file);
                self::assertMatchesRegularExpression('/^USE '.preg_quote($db_name,'/').';$/m',str_replace("\r",'',$sql));
                self::assertDoesNotMatchRegularExpression('/^USE recyclables_db;/m',$sql);
            }
            putenv('RECYCLABLES_DB_NAME=recyclables_custom');
            require dirname(__DIR__,2).'/includes/config.php';
            self::assertSame('recyclables_custom',$db_name);
        } finally {
            putenv($previous===false?'RECYCLABLES_DB_NAME':'RECYCLABLES_DB_NAME='.$previous);
        }
    }
}
