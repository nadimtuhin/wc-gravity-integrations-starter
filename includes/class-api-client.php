<?php
namespace WCGravityIntegrations;

class ApiClient {
    private int $max_retries;

    public function __construct( int $max_retries = 3 ) {
        $this->max_retries = $max_retries;
    }

    public function dispatch_webhook( string $endpoint, array $payload ): bool {
        $args = [
            'timeout'     => 15,
            'redirection' => 2,
            'headers'     => [
                'Content-Type' => 'application/json',
                'User-Agent'   => 'WordPress-WCGravityBridge/1.0',
            ],
            'body'        => wp_json_encode( $payload ),
        ];

        for ( $attempt = 1; $attempt <= $this->max_retries; $attempt++ ) {
            $response = wp_remote_post( $endpoint, $args );

            if ( ! is_wp_error( $response ) ) {
                $code = wp_remote_retrieve_response_code( $response );
                if ( $code >= 200 && $code < 300 ) {
                    return true;
                }
            }

            // Exponential backoff if not in testing
            if ( ! defined( 'PHPUNIT_RUNNING' ) ) {
                usleep( (int) ( pow( 2, $attempt ) * 100000 ) );
            }
        }

        return false;
    }
}
