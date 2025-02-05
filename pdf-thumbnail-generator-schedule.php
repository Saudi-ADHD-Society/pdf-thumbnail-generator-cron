<?php
/*
	Plugin Name: PDF Thumbnail Generator - Generate Missing on Schedule
	Plugin URI: https://adhd.org.sa
	Description: Generates missing PDF thumbnails hourly using server cron. Requires PDF Thumbnail Generator plugin.
	Version: 1.1.2
	Author: Jeremy Varnham
	Author URI: https://abuyasmeen.com
*/

defined('ABSPATH') || exit;

function initialize_pdf_thumbnail_generator_curl() {
    if ( class_exists( 'pdf_thumbnail_generator' ) ) {
        class pdf_thumbnail_generator_curl extends pdf_thumbnail_generator {
        
            function __construct() {
                parent::__construct();
                add_action( 'generate_missing_pdf_thumbnails_curl', array( $this, 'generate_missing_pdf_thumbnails' ) );
                
                // Add to WP Cron
                if ( ! wp_next_scheduled( 'generate_missing_pdf_thumbnails_curl' ) ) {
					wp_schedule_event( time(), 'hourly', 'generate_missing_pdf_thumbnails_curl' );
				}
            }
            
            function generate_missing_pdf_thumbnails() {
                global $wpdb;

                $pdfs = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE {$wpdb->posts}.post_type = 'attachment' AND {$wpdb->posts}.post_mime_type = 'application/pdf'");
                
                if ( $pdfs ) {
                    foreach ( $pdfs as $pdf ) {
                        $thumbnail = get_post_meta( $pdf, '_pdf_thumbnail', true );
                        if (!$thumbnail) {
                            $generated = $this->generate_thumbnail( $pdf, false );
                            $thumbnail = get_post_meta( $pdf, '_pdf_thumbnail', true );
                        }
                    }
                }
            }
        }

        new pdf_thumbnail_generator_curl();
    } else {
        add_action( 'admin_notices', function() {
            echo '<div class="error"><p>PDF Thumbnail Generator plugin must be activated for this plugin to work.</p></div>';
        } );
    }
}

add_action( 'plugins_loaded', 'initialize_pdf_thumbnail_generator_curl' );
