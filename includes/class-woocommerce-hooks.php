<?php
namespace WCGravityIntegrations;

class WooCommerceHooks {
    public static function init(): void {
        // Custom checkout fields
        add_filter( 'woocommerce_checkout_fields', [ __CLASS__, 'add_custom_checkout_fields' ] );
        add_action( 'woocommerce_checkout_update_order_meta', [ __CLASS__, 'save_custom_checkout_fields' ] );

        // Custom conditional fee calculation
        add_action( 'woocommerce_cart_calculate_fees', [ __CLASS__, 'add_conditional_environmental_fee' ] );

        // Order lifecycle event
        add_action( 'woocommerce_order_status_completed', [ __CLASS__, 'handle_order_completed' ] );
    }

    public static function add_custom_checkout_fields( array $fields ): array {
        $fields['billing']['billing_delivery_notes'] = [
            'type'        => 'text',
            'label'       => 'Special Delivery Instructions',
            'placeholder' => 'Gate code, drop-off spot, etc.',
            'required'    => false,
            'class'       => [ 'form-row-wide' ],
            'clear'       => true,
            'priority'    => 120,
        ];
        return $fields;
    }

    public static function save_custom_checkout_fields( int $order_id ): void {
        if ( ! empty( $_POST['billing_delivery_notes'] ) ) {
            $notes = sanitize_text_field( wp_unslash( $_POST['billing_delivery_notes'] ) );
            if ( function_exists( 'update_post_meta' ) ) {
                update_post_meta( $order_id, '_billing_delivery_notes', $notes );
            }
        }
    }

    public static function add_conditional_environmental_fee( $cart ): void {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }

        // Apply sustainable packaging fee for heavy carts
        if ( $cart && method_exists( $cart, 'get_cart_contents_count' ) && $cart->get_cart_contents_count() >= 5 ) {
            $cart->add_fee( 'Eco-Packaging & Handling', 3.50, true );
        }
    }

    public static function handle_order_completed( int $order_id ): void {
        $client = new ApiClient();
        $payload = [
            'order_id'  => $order_id,
            'timestamp' => time(),
            'event'     => 'order.completed',
        ];
        $client->dispatch_webhook( 'https://api.example.com/logistics/dispatch', $payload );
    }
}
