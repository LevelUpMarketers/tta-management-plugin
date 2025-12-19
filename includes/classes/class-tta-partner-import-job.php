<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_Partner_Import_Job {
    const OPTION_KEY = 'tta_partner_import_jobs';
    const ACTION_HOOK = 'tta_run_partner_import';

    public static function init() {
        add_action( self::ACTION_HOOK, [ __CLASS__, 'process_job' ], 10, 1 );
    }

    public static function create_job( $args ) {
        $jobs = self::get_jobs();
        $job_id = uniqid( 'tta_partner_job_', true );
        $jobs[ $job_id ] = array_merge(
            [
                'status'      => 'pending',
                'created_at'  => current_time( 'mysql' ),
                'updated_at'  => current_time( 'mysql' ),
                'partner_id'  => 0,
                'page_id'     => 0,
                'partner_uid' => '',
                'license_limit' => 0,
                'file'        => '',
                'total_rows'  => 0,
                'offset'      => 0,
                'added'       => 0,
                'skipped'     => 0,
                'message'     => '',
                'error'       => '',
            ],
            $args
        );
        self::save_jobs( $jobs );
        wp_schedule_single_event( time() + 5, self::ACTION_HOOK, [ $job_id ] );
        return $job_id;
    }

    public static function process_job( $job_id ) {
        $jobs = self::get_jobs();
        if ( empty( $jobs[ $job_id ] ) ) {
            return;
        }
        $job = $jobs[ $job_id ];
        if ( ! in_array( $job['status'], [ 'pending', 'running' ], true ) ) {
            return;
        }

        $job['status']     = 'running';
        $job['updated_at'] = current_time( 'mysql' );
        self::save_jobs( $jobs );

        $result = self::process_batch( $job );

        $jobs = self::get_jobs();
        if ( empty( $jobs[ $job_id ] ) ) {
            return;
        }
        $jobs[ $job_id ] = $result['job'];
        self::save_jobs( $jobs );

        if ( $result['continue'] ) {
            wp_schedule_single_event( time() + 10, self::ACTION_HOOK, [ $job_id ] );
        }
    }

    protected static function process_batch( $job ) {
        $batch_size = 200;
        $job['error'] = '';
        $job['message'] = '';

        $file = $job['file'];
        if ( ! file_exists( $file ) ) {
            $job['status'] = 'failed';
            $job['error']  = __( 'Import file missing.', 'tta' );
            return [ 'job' => $job, 'continue' => false ];
        }

        global $wpdb;
        $members_table = $wpdb->prefix . 'tta_members';

        $remaining_limit = null;
        if ( $job['license_limit'] > 0 ) {
            $current = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$members_table} WHERE partner = %s",
                    $job['partner_uid']
                )
            );
            $remaining_limit = max( 0, $job['license_limit'] - $current );
            if ( $remaining_limit <= 0 ) {
                $job['status'] = 'completed';
                $job['message'] = __( 'License limit reached. No additional members added.', 'tta' );
                self::cleanup_file( $file );
                TTA_Cache::flush();
                return [ 'job' => $job, 'continue' => false ];
            }
        }

        $rows = self::read_csv_slice( $file, $job['offset'], $batch_size + 1 );
        if ( empty( $rows ) ) {
            $job['status'] = 'completed';
            $job['message'] = sprintf(
                /* translators: 1: added, 2: skipped */
                __( 'Import finished. Added: %1$d, Skipped: %2$d', 'tta' ),
                $job['added'],
                $job['skipped']
            );
            self::cleanup_file( $file );
            TTA_Cache::flush();
            return [ 'job' => $job, 'continue' => false ];
        }

        $processed = 0;
        foreach ( $rows as $row ) {
            if ( $remaining_limit !== null && $job['added'] >= $job['license_limit'] ) {
                break;
            }

            $first_name = tta_sanitize_text_field( $row['first_name'] ?? '' );
            $last_name  = tta_sanitize_text_field( $row['last_name'] ?? '' );
            $email      = tta_sanitize_email( $row['email'] ?? '' );

            if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) || ! is_email( $email ) ) {
                $job['skipped']++;
                $processed++;
                continue;
            }

            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$members_table} WHERE email = %s LIMIT 1",
                    $email
                )
            );

            if ( $existing ) {
                $job['skipped']++;
                $processed++;
                continue;
            }

            $inserted = $wpdb->insert(
                $members_table,
                [
                    'wpuserid'          => 0,
                    'first_name'        => $first_name,
                    'last_name'         => $last_name,
                    'email'             => $email,
                    'partner'           => $job['partner_uid'],
                    'profileimgid'      => 0,
                    'joined_at'         => current_time( 'mysql' ),
                    'address'           => '',
                    'phone'             => null,
                    'dob'               => null,
                    'member_type'       => 'member',
                    'membership_level'  => 'free',
                    'subscription_id'   => null,
                    'subscription_status' => null,
                    'facebook'          => null,
                    'linkedin'          => null,
                    'instagram'         => null,
                    'twitter'           => null,
                    'biography'         => null,
                    'notes'             => null,
                    'interests'         => null,
                    'opt_in_marketing_email'    => 0,
                    'opt_in_marketing_sms'      => 0,
                    'opt_in_event_update_email' => 0,
                    'opt_in_event_update_sms'   => 0,
                    'hide_event_attendance'     => 0,
                    'no_show_offset'            => 0,
                    'banned_until'              => null,
                ],
                [
                    '%d','%s','%s','%s','%s','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d','%d','%d','%d','%d','%d','%d','%s',
                ]
            );

            if ( $inserted ) {
                $job['added']++;
            } else {
                $job['skipped']++;
            }
            $processed++;
        }

        $job['offset'] += $processed;
        $job['updated_at'] = current_time( 'mysql' );

        $more = $processed >= $batch_size;
        if ( ! $more ) {
            $job['status'] = 'completed';
            $job['message'] = sprintf(
                /* translators: 1: added, 2: skipped */
                __( 'Import finished. Added: %1$d, Skipped: %2$d', 'tta' ),
                $job['added'],
                $job['skipped']
            );
            self::cleanup_file( $file );
            TTA_Cache::flush();
        }

        return [ 'job' => $job, 'continue' => $more ];
    }

    protected static function read_csv_slice( $path, $offset, $length ) {
        $rows = [];
        if ( ! file_exists( $path ) ) {
            return $rows;
        }
        $handle = fopen( $path, 'r' );
        if ( ! $handle ) {
            return $rows;
        }
        $headers = [];
        if ( ( $data = fgetcsv( $handle ) ) !== false ) {
            $headers = array_map( 'strtolower', array_map( 'trim', $data ) );
        }

        if ( empty( $headers ) ) {
            fclose( $handle );
            return $rows;
        }

        // Skip rows to offset
        for ( $i = 0; $i < $offset; $i++ ) {
            if ( false === fgetcsv( $handle ) ) {
                fclose( $handle );
                return $rows;
            }
        }

        $count = 0;
        while ( $count < $length && ( $data = fgetcsv( $handle ) ) !== false ) {
            if ( empty( array_filter( $data, 'strlen' ) ) ) {
                continue;
            }
            $rows[] = self::map_row_by_headers( $headers, $data );
            $count++;
        }

        fclose( $handle );
        return $rows;
    }

    protected static function map_row_by_headers( $headers, $values ) {
        $first_idx = array_search( 'first name', $headers, true );
        $last_idx  = array_search( 'last name', $headers, true );
        $email_idx = array_search( 'email', $headers, true );

        return [
            'first_name' => $first_idx !== false ? ( $values[ $first_idx ] ?? '' ) : '',
            'last_name'  => $last_idx !== false ? ( $values[ $last_idx ] ?? '' ) : '',
            'email'      => $email_idx !== false ? ( $values[ $email_idx ] ?? '' ) : '',
        ];
    }

    public static function status( $job_id ) {
        $jobs = self::get_jobs();
        return $jobs[ $job_id ] ?? null;
    }

    protected static function get_jobs() {
        $jobs = get_option( self::OPTION_KEY, [] );
        return is_array( $jobs ) ? $jobs : [];
    }

    protected static function save_jobs( $jobs ) {
        update_option( self::OPTION_KEY, $jobs, false );
    }

    protected static function cleanup_file( $path ) {
        if ( $path && file_exists( $path ) ) {
            unlink( $path );
        }
    }
}

TTA_Partner_Import_Job::init();
