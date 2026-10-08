<?php
namespace WCGravityIntegrations;

class GravityFormsAddon {
    public static function init(): void {
        // Custom field validation rule
        add_filter( 'gform_field_validation', [ __CLASS__, 'validate_australian_business_number' ], 10, 4 );

        // Post-submission pipeline: sync entry to CRM/ERP API
        add_action( 'gform_after_submission', [ __CLASS__, 'sync_submission_to_crm' ], 10, 2 );
    }

    public static function validate_australian_business_number( array $result, $value, $form, $field ): array {
        if ( 'abn_input' === ( $field->cssClass ?? '' ) && ! empty( $value ) ) {
            // Remove spaces
            $cleaned = preg_replace( '/\s+/', '', $value );
            if ( ! preg_match( '/^\d{11}$/', $cleaned ) ) {
                $result['is_valid'] = false;
                $result['message']  = 'Please enter a valid 11-digit Australian Business Number (ABN).';
            }
        }
        return $result;
    }

    public static function sync_submission_to_crm( array $entry, array $form ): void {
        $client = new ApiClient();

        $lead_data = [
            'form_id'    => $entry['form_id'],
            'entry_id'   => $entry['id'],
            'email'      => $entry['1'] ?? '', // Field ID 1: Email
            'full_name'  => ( $entry['2.3'] ?? '' ) . ' ' . ( $entry['2.6'] ?? '' ), // Name field
            'created_at' => $entry['date_created'] ?? gmdate( 'Y-m-d H:i:s' ),
        ];

        $client->dispatch_webhook( 'https://api.example.com/crm/leads', $lead_data );
    }
}
