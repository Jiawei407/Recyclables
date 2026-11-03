<?php
use PHPUnit\Framework\TestCase;

final class SqlNamespaceTest extends TestCase
{
    public function testStandaloneDemoIncludesTheCompleteCanonicalSchemaBeforeItsAccounts(): void
    {
        $folder=dirname(__DIR__,2).'/database/';
        $schema=str_replace("\r",'',trim(file_get_contents($folder.'recyclables_db.sql')));
        $demo=str_replace("\r",'',file_get_contents($folder.'demo_accounts.sql'));
        self::assertStringContainsString($schema,$demo,'Keep the standalone installer synchronized with the canonical schema.');
        self::assertLessThan(strpos($demo,'INSERT IGNORE INTO users'),strpos($demo,$schema));
    }

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
