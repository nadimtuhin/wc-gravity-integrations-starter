<?php
namespace WCGravityIntegrations\Tests;

use PHPUnit\Framework\TestCase;
use WCGravityIntegrations\DbManager;

class DbManagerTest extends TestCase {
    private $previous_wpdb;

    protected function setUp(): void {
        global $wpdb;
        $this->previous_wpdb = $wpdb ?? null;
        $wpdb = new class {
            public $prefix = 'wp_';
            public function get_charset_collate() { return 'DEFAULT CHARSET=utf8mb4'; }
        };
        $GLOBALS['wc_gf_test_dbdelta_sql'] = [];
    }

    protected function tearDown(): void {
        global $wpdb;
        $wpdb = $this->previous_wpdb;
    }

    public function testMigrationSqlIsParseableByDbDelta(): void {
        DbManager::migrate();

        $this->assertCount( 1, $GLOBALS['wc_gf_test_dbdelta_sql'] );
        $sql = $GLOBALS['wc_gf_test_dbdelta_sql'][0];

        // dbDelta() takes the table name from /CREATE TABLE ([^ ]*)/; "IF NOT EXISTS" makes it read "IF".
        $this->assertMatchesRegularExpression( '/^CREATE TABLE wp_custom_sync_logs \(/', $sql );
        // dbDelta() needs exactly two spaces between PRIMARY KEY and the column list.
        $this->assertStringContainsString( 'PRIMARY KEY  (id)', $sql );
    }
}
