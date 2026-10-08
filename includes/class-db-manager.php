<?php
namespace WCGravityIntegrations;

class DbManager {
    public static function migrate(): void {
        global $wpdb;

        if ( ! $wpdb ) {
            return;
        }

        $table_name = $wpdb->prefix . 'custom_sync_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            source_type varchar(50) NOT NULL,
            source_id bigint(20) unsigned NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            payload longtext NOT NULL,
            error_message text NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_status (status),
            KEY idx_source (source_type, source_id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public static function log_sync( string $source_type, int $source_id, string $status, array $payload, ?string $error = null ): bool {
        global $wpdb;

        if ( ! $wpdb ) {
            return false;
        }

        $table = $wpdb->prefix . 'custom_sync_logs';

        // Prepared query protecting against SQL injection
        return (bool) $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$table} (source_type, source_id, status, payload, error_message, created_at) VALUES (%s, %d, %s, %s, %s, %s)",
                $source_type,
                $source_id,
                $status,
                wp_json_encode( $payload ),
                $error,
                current_time( 'mysql' )
            )
        );
    }
}
