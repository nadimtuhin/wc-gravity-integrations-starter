<?php
require_once __DIR__ . '/../vendor/autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/fixtures/wordpress/' );
}

// Lightweight stubs for unit testing outside full WP runtime
if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $str ) {
        return trim( strip_tags( $str ) );
    }
}

if ( ! function_exists( 'add_action' ) ) {
    function add_action() {}
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter() {}
}

if ( ! function_exists( 'wp_remote_post' ) ) {
    function wp_remote_post( $url, $args ) {
        $GLOBALS['wc_gf_test_http_calls'][] = [ 'fn' => 'wp_remote_post', 'url' => $url ];
        return [
            'response' => [ 'code' => 200 ],
            'body'     => json_encode( [ 'success' => true ] ),
        ];
    }
}

if ( ! function_exists( 'is_wp_error' ) ) {
    function is_wp_error( $thing ) {
        return false;
    }
}

if ( ! function_exists( 'wp_remote_retrieve_response_code' ) ) {
    function wp_remote_retrieve_response_code( $response ) {
        return $response['response']['code'] ?? 200;
    }
}

if ( ! function_exists( 'wp_remote_retrieve_body' ) ) {
    function wp_remote_retrieve_body( $response ) {
        return $response['body'] ?? '';
    }
}

if ( ! function_exists( 'is_admin' ) ) {
    function is_admin() {
        return false;
    }
}

if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) {
        return $text;
    }
}

if ( ! function_exists( 'wp_doing_ajax' ) ) {
    function wp_doing_ajax() {
        return false;
    }
}

if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode( $data ) {
        return json_encode( $data );
    }
}

if ( ! function_exists( 'wp_safe_remote_post' ) ) {
    function wp_safe_remote_post( $url, $args ) {
        $GLOBALS['wc_gf_test_http_calls'][] = [ 'fn' => 'wp_safe_remote_post', 'url' => $url ];
        return [
            'response' => [ 'code' => 200 ],
            'body'     => json_encode( [ 'success' => true ] ),
        ];
    }
}
