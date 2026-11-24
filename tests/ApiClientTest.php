<?php
namespace WCGravityIntegrations\Tests;

use PHPUnit\Framework\TestCase;
use WCGravityIntegrations\ApiClient;

class ApiClientTest extends TestCase {
    protected function setUp(): void {
        $GLOBALS['wc_gf_test_http_calls'] = [];
    }

    public function testWebhooksUseTheSsrfSafeHttpTransport(): void {
        $delivered = ( new ApiClient() )->dispatch_webhook( 'https://api.example.com/hook', [ 'a' => 1 ] );

        $this->assertTrue( $delivered );
        $this->assertSame( [ 'wp_safe_remote_post' ], array_column( $GLOBALS['wc_gf_test_http_calls'], 'fn' ) );
    }
}
