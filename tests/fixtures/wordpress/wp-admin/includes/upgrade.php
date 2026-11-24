<?php
// Test double for wp-admin/includes/upgrade.php: records the SQL handed to dbDelta().
if ( ! function_exists( 'dbDelta' ) ) {
    function dbDelta( $queries = '', $execute = true ) {
        $GLOBALS['wc_gf_test_dbdelta_sql'][] = $queries;
        return [];
    }
}
