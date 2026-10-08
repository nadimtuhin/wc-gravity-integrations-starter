<?php
/**
 * WooCommerce checkout, cart and order lifecycle hooks.
 *
 * @package WCGravityIntegrations
 */

namespace WCGravityIntegrations;

/**
 * Registers WooCommerce customisations.
 */
class WooCommerceHooks {

	const DELIVERY_NOTES_FIELD = 'billing_delivery_notes';

	const BULK_CART_THRESHOLD = 5;

	const ECO_PACKAGING_FEE = 3.50;

	/**
	 * Hook into WooCommerce.
	 */
	public static function init(): void {
		add_filter( 'woocommerce_checkout_fields', [ __CLASS__, 'add_custom_checkout_fields' ] );

		// Runs after WooCommerce has verified the checkout nonce and collected posted data,
		// and writes through the order object so it works with HPOS custom order tables.
		add_action( 'woocommerce_checkout_create_order', [ __CLASS__, 'save_delivery_notes' ], 10, 2 );

		add_action( 'woocommerce_cart_calculate_fees', [ __CLASS__, 'add_conditional_environmental_fee' ] );
		add_action( 'woocommerce_order_status_completed', [ __CLASS__, 'handle_order_completed' ] );
	}

	/**
	 * Add a delivery-notes field to the billing section.
	 *
	 * @param array $fields Checkout fields grouped by section.
	 * @return array
	 */
	public static function add_custom_checkout_fields( array $fields ): array {
		$fields['billing'][ self::DELIVERY_NOTES_FIELD ] = [
			'type'        => 'text',
			'label'       => __( 'Special Delivery Instructions', 'wc-gf-integrations' ),
			'placeholder' => __( 'Gate code, drop-off spot, etc.', 'wc-gf-integrations' ),
			'required'    => false,
			'class'       => [ 'form-row-wide' ],
			'clear'       => true,
			'priority'    => 120,
		];
		return $fields;
	}

	/**
	 * Persist delivery notes on the order being created.
	 *
	 * @param \WC_Order $order Order being created.
	 * @param array     $data  Checkout data posted by the customer.
	 */
	public static function save_delivery_notes( $order, array $data ): void {
		$notes = sanitize_text_field( $data[ self::DELIVERY_NOTES_FIELD ] ?? '' );

		if ( '' !== $notes ) {
			$order->update_meta_data( '_billing_delivery_notes', $notes );
		}
	}

	/**
	 * Add an eco-packaging fee to large carts.
	 *
	 * @param \WC_Cart $cart Current cart.
	 */
	public static function add_conditional_environmental_fee( $cart ): void {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		if ( $cart->get_cart_contents_count() >= self::BULK_CART_THRESHOLD ) {
			$cart->add_fee( __( 'Eco-Packaging & Handling', 'wc-gf-integrations' ), self::ECO_PACKAGING_FEE, true );
		}
	}

	/**
	 * Notify the logistics system that an order is ready to ship.
	 *
	 * @param int $order_id Completed order ID.
	 */
	public static function handle_order_completed( int $order_id ): void {
		$endpoint = apply_filters( 'wc_gf_integrations_logistics_endpoint', 'https://api.example.com/logistics/dispatch' );
		$payload  = [
			'order_id'  => $order_id,
			'timestamp' => time(),
			'event'     => 'order.completed',
		];

		$delivered = ( new ApiClient() )->dispatch_webhook( $endpoint, $payload );

		DbManager::log_sync( 'wc_order', $order_id, $delivered ? 'sent' : 'failed', $payload );
	}
}
