<?php
namespace WCGravityIntegrations\Tests;

use PHPUnit\Framework\TestCase;
use WCGravityIntegrations\GravityFormsAddon;

class GravityFormsAddonTest extends TestCase {
    public function testValidAustralianBusinessNumberWithSpaces(): void {
        $field = new \stdClass();
        $field->cssClass = 'abn_input';

        $result = [ 'is_valid' => true, 'message' => '' ];
        $validated = GravityFormsAddon::validate_australian_business_number( $result, '51 824 753 556', [], $field );

        $this->assertTrue( $validated['is_valid'] );
    }

    public function testValidAustralianBusinessNumberRaw(): void {
        $field = new \stdClass();
        $field->cssClass = 'abn_input';

        $result = [ 'is_valid' => true, 'message' => '' ];
        $validated = GravityFormsAddon::validate_australian_business_number( $result, '51824753556', [], $field );

        $this->assertTrue( $validated['is_valid'] );
    }

    public function testInvalidShortAustralianBusinessNumber(): void {
        $field = new \stdClass();
        $field->cssClass = 'abn_input';

        $result = [ 'is_valid' => true, 'message' => '' ];
        $validated = GravityFormsAddon::validate_australian_business_number( $result, '1234567890', [], $field );

        $this->assertFalse( $validated['is_valid'] );
        $this->assertStringContainsString( 'valid 11-digit Australian Business Number', $validated['message'] );
    }

    public function testInvalidCharactersInABN(): void {
        $field = new \stdClass();
        $field->cssClass = 'abn_input';

        $result = [ 'is_valid' => true, 'message' => '' ];
        $validated = GravityFormsAddon::validate_australian_business_number( $result, '5182475355A', [], $field );

        $this->assertFalse( $validated['is_valid'] );
    }
}
