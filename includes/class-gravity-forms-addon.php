<?php
/**
 * Gravity Forms validation and submission pipeline.
 *
 * @package WCGravityIntegrations
 */

namespace WCGravityIntegrations;

/**
 * Registers Gravity Forms customisations.
 */
class GravityFormsAddon {

	const ABN_FIELD_CSS_CLASS = 'abn_input';

	// ATO-published weights for the ABN modulus-89 checksum.
	const ABN_WEIGHTS = [ 10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19 ];

	/**
	 * Hook into Gravity Forms.
	 */
	public static function init(): void {
		add_filter( 'gform_field_validation', [ __CLASS__, 'validate_australian_business_number' ], 10, 4 );
		add_action( 'gform_after_submission', [ __CLASS__, 'sync_submission_to_crm' ], 10, 2 );
	}

	/**
	 * Validate fields with the `abn_input` CSS class as an Australian Business Number.
	 *
	 * @param array     $result Validation result with `is_valid` and `message`.
	 * @param mixed     $value  Submitted value.
	 * @param array     $form   Form object; required by the filter signature.
	 * @param \GF_Field $field  Field being validated.
	 * @return array
	 */
	public static function validate_australian_business_number( array $result, $value, $form, $field ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed -- Signature fixed by gform_field_validation.
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Gravity Forms property.
		$css_class = (string) ( $field->cssClass ?? '' );

		if ( self::ABN_FIELD_CSS_CLASS !== $css_class || empty( $value ) ) {
			return $result;
		}

		if ( ! self::is_valid_abn( (string) $value ) ) {
			$result['is_valid'] = false;
			$result['message']  = __( 'Please enter a valid 11-digit Australian Business Number (ABN).', 'wc-gf-integrations' );
		}

		return $result;
	}

	/**
	 * Check format and checksum, e.g. "51 824 753 556" is valid.
	 *
	 * @param string $abn Raw ABN, spaces allowed.
	 */
	public static function is_valid_abn( string $abn ): bool {
		$digits = preg_replace( '/\s+/', '', $abn );

		if ( ! preg_match( '/^\d{11}$/', $digits ) ) {
			return false;
		}

		$sum = 0;
		foreach ( self::ABN_WEIGHTS as $position => $weight ) {
			$digit = (int) $digits[ $position ];
			if ( 0 === $position ) {
				--$digit;
			}
			$sum += $digit * $weight;
		}

		return 0 === $sum % 89;
	}

	/**
	 * Push a new lead to the CRM.
	 *
	 * @param array $entry Gravity Forms entry.
	 * @param array $form  Form object.
	 */
	public static function sync_submission_to_crm( array $entry, array $form ): void {
		$first_name = sanitize_text_field( $entry['2.3'] ?? '' );
		$last_name  = sanitize_text_field( $entry['2.6'] ?? '' );

		$lead_data = [
			'form_id'    => absint( $entry['form_id'] ?? 0 ),
			'form_title' => sanitize_text_field( $form['title'] ?? '' ),
			'entry_id'   => absint( $entry['id'] ?? 0 ),
			'email'      => sanitize_email( $entry['1'] ?? '' ),
			'full_name'  => trim( $first_name . ' ' . $last_name ),
			'created_at' => $entry['date_created'] ?? gmdate( 'Y-m-d H:i:s' ),
		];

		$endpoint  = apply_filters( 'wc_gf_integrations_crm_endpoint', 'https://api.example.com/crm/leads' );
		$delivered = ( new ApiClient() )->dispatch_webhook( $endpoint, $lead_data );

		DbManager::log_sync( 'gf_entry', $lead_data['entry_id'], $delivered ? 'sent' : 'failed', $lead_data );
	}
}
