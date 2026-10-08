<?php
namespace WCGravityIntegrations\Tests;

use PHPUnit\Framework\TestCase;
use WCGravityIntegrations\WooCommerceHooks;

class WooCommerceHooksTest extends TestCase {
    public function testAddCustomCheckoutFields(): void {
        $fields = [ 'billing' => [] ];
        $result = WooCommerceHooks::add_custom_checkout_fields( $fields );

        $this->assertArrayHasKey( 'billing_delivery_notes', $result['billing'] );
        $this->assertEquals( 'Special Delivery Instructions', $result['billing']['billing_delivery_notes']['label'] );
        $this->assertFalse( $result['billing']['billing_delivery_notes']['required'] );
        $this->assertEquals( 120, $result['billing']['billing_delivery_notes']['priority'] );
    }

    public function testConditionalFeeAddedForBulkCart(): void {
        $cart = new class {
            public $fees = [];
            public function get_cart_contents_count() { return 6; }
            public function add_fee( $name, $amount, $taxable ) {
                $this->fees[] = compact( 'name', 'amount', 'taxable' );
            }
        };

        WooCommerceHooks::add_conditional_environmental_fee( $cart );
        $this->assertCount( 1, $cart->fees );
        $this->assertEquals( 'Eco-Packaging & Handling', $cart->fees[0]['name'] );
        $this->assertEquals( 3.50, $cart->fees[0]['amount'] );
    }

    public function testConditionalFeeSkippedForSmallCart(): void {
        $cart = new class {
            public $fees = [];
            public function get_cart_contents_count() { return 2; }
            public function add_fee( $name, $amount, $taxable ) {
                $this->fees[] = compact( 'name', 'amount', 'taxable' );
            }
        };

        WooCommerceHooks::add_conditional_environmental_fee( $cart );
        $this->assertCount( 0, $cart->fees );
    }

    public function testDeliveryNotesAreSanitizedOntoOrderMeta(): void {
        $order = $this->fake_order();

        WooCommerceHooks::save_delivery_notes( $order, [ 'billing_delivery_notes' => '  <b>Gate 4521</b>  ' ] );

        $this->assertSame( 'Gate 4521', $order->meta['_billing_delivery_notes'] );
    }

    public function testEmptyDeliveryNotesAreNotSaved(): void {
        $order = $this->fake_order();

        WooCommerceHooks::save_delivery_notes( $order, [] );

        $this->assertSame( [], $order->meta );
    }

    private function fake_order() {
        return new class {
            public $meta = [];
            public function update_meta_data( $key, $value ) {
                $this->meta[ $key ] = $value;
            }
        };
    }
}
