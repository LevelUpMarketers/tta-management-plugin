<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Read a CSV of email addresses and fetch Authorize.Net transactions.
 */
class TTA_CSV_Transaction_Lookup {
    /**
     * Process a CSV file of emails and return lines describing transactions.
     *
     * @param string               $file Uploaded CSV path.
     * @param TTA_AuthorizeNet_API $api  Authorize.Net API instance.
     * @return string[] Lines for textarea output.
     */
    public static function process_csv( $file, TTA_AuthorizeNet_API $api ) {
        $results = [];
        if ( ! file_exists( $file ) ) {
            return $results;
        }

        $handle = fopen( $file, 'r' );
        if ( false === $handle ) {
            return $results;
        }

        $first = true;
        while ( ( $row = fgetcsv( $handle ) ) !== false ) {
            if ( $first && isset( $row[0] ) && stripos( $row[0], 'email' ) !== false ) {
                $first = false;
                continue;
            }
            $first = false;

            $email = sanitize_email( $row[0] ?? '' );
            if ( '' === $email ) {
                continue;
            }

            TTA_Debug_Logger::log( 'csv_lookup email=' . $email );
            $transactions = $api->find_transactions_by_email( $email, 65 );

            if ( $transactions ) {
                foreach ( $transactions as $txn ) {
                    $line = sprintf(
                        '%s - %s %s %s %s %s %s',
                        $email,
                        $txn['id'],
                        $txn['amount'],
                        $txn['date'],
                        $txn['transaction_status'],
                        $txn['invoice'],
                        $txn['details']
                    );
                    $results[] = $line;
                    TTA_Debug_Logger::log( 'csv_lookup result=' . $line );
                }
            } else {
                $line = sprintf( '%s - no transactions found', $email );
                $results[] = $line;
                TTA_Debug_Logger::log( 'csv_lookup result=' . $line );
            }
        }

        fclose( $handle );
        return $results;
    }
}
