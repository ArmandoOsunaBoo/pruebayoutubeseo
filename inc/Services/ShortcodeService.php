<?php

namespace SmashBalloon\YouTubeFeed\Services;

use Smashballoon\Stubs\Services\ServiceProvider;
use SmashBalloon\YouTubeFeed\Helpers\Util;
use SmashBalloon\YouTubeFeed\Pro\SBY_Cron_Updater_Pro;
use SmashBalloon\YouTubeFeed\Pro\SBY_Feed_Pro;
use SmashBalloon\YouTubeFeed\Pro\SBY_Settings_Pro;
use SmashBalloon\YouTubeFeed\SBY_Feed;

class ShortcodeService extends ServiceProvider {

	public function register() {
		add_shortcode('youtube-feed', [$this, 'sby_youtube_feed']);
        add_filter('sby_render_shortcode', [$this, 'sby_youtube_feed'], 10, 2);
	}

	public function sby_youtube_feed( $atts = array(), $preview_settings = false ) {
		$database_settings = sby_get_database_settings();
		$youtube_feed_settings = new SBY_Settings_Pro( $atts, $database_settings, $preview_settings );
		$youtube_feed_settings->set_feed_type_and_terms();
		$youtube_feed_settings->set_transient_name();
		$transient_name = $youtube_feed_settings->get_transient_name();
		$settings = $youtube_feed_settings->maybe_get_settings_or_legacy_settings( $atts );
		$feed_type_and_terms = $youtube_feed_settings->get_feed_type_and_terms();

        do_action('sby_enqueue_scripts', $settings);

		if ( !$database_settings['ajaxtheme'] ) {
			wp_enqueue_script( 'sby_scripts' );
		}

		if ( $database_settings['enqueue_css_in_shortcode'] ) {
			wp_enqueue_style( 'sby_styles' );
		}

		if ( empty( $database_settings['connected_accounts'] ) && empty( $database_settings['api_key'] ) ) {
			$style = current_user_can( 'manage_youtube_feed_options' ) ? ' style="display: block;"' : '';
			ob_start(); ?>
			<div id="sbi_mod_error" <?php echo $style; ?>>
				<span><?php _e('This error message is only visible to WordPress admins', 'feeds-for-youtube' ); ?></span><br />
				<p><b><?php _e( 'Error: No connected account.', 'feeds-for-youtube' ); ?></b>
				<p><?php _e( 'Please go to the YouTube Feed settings page to connect an account.', 'feeds-for-youtube' ); ?></p>
			</div>
			<?php
			$html = ob_get_contents();
			ob_get_clean();
			return $html;
		}

		$youtube_feed = new SBY_Feed_Pro( $transient_name );

		if ( $settings['caching_type'] === 'background' ) {
			$youtube_feed->add_report( 'background caching used' );
			if ( $youtube_feed->regular_cache_exists() ) {
				$youtube_feed->add_report( 'setting posts from cache' );
				$youtube_feed->set_post_data_from_cache();
			}

			if ( $youtube_feed->need_to_start_cron_job() ) {
				$youtube_feed->add_report( 'setting up feed for cron cache' );
				$to_cache = array(
					'atts' => $atts,
					'last_requested' => time(),
				);

				$youtube_feed->set_cron_cache( $to_cache, $youtube_feed_settings->get_cache_time_in_seconds() );

				if(Util::isPro()) {
					SBY_Cron_Updater_Pro::do_single_feed_cron_update( $youtube_feed_settings, $to_cache, $atts, false );
				}

				$youtube_feed->set_post_data_from_cache();

			} elseif ( $youtube_feed->should_update_last_requested() ) {
				$youtube_feed->add_report( 'updating last requested' );
				$to_cache = array(
					'last_requested' => time(),
				);

				$youtube_feed->set_cron_cache( $to_cache, $youtube_feed_settings->get_cache_time_in_seconds() );
			}

		} elseif ( $youtube_feed->regular_cache_exists() ) {
			$youtube_feed->add_report( 'page load caching used and regular cache exists' );
			$youtube_feed->set_post_data_from_cache();

			if ( $youtube_feed->need_posts( $settings['num'] ) && $youtube_feed->can_get_more_posts() ) {
				while ( $youtube_feed->need_posts( $settings['num'] ) && $youtube_feed->can_get_more_posts() ) {
					$youtube_feed->add_remote_posts( $settings, $feed_type_and_terms, $youtube_feed_settings->get_connected_accounts_in_feed() );
				}
				$youtube_feed->cache_feed_data( $youtube_feed_settings->get_cache_time_in_seconds() );
			}

		} else {
			$youtube_feed->add_report( 'no feed cache found' );

			while ( $youtube_feed->need_posts( $settings['num'] ) && $youtube_feed->can_get_more_posts() ) {
				$youtube_feed->add_remote_posts( $settings, $feed_type_and_terms, $youtube_feed_settings->get_connected_accounts_in_feed() );
			}

			if ( ! $youtube_feed->should_use_backup() ) {
				$youtube_feed->cache_feed_data( $youtube_feed_settings->get_cache_time_in_seconds() );
			}

		}

		if ( $youtube_feed->should_use_backup() ) {
			$youtube_feed->add_report( 'trying to use backup' );
			$youtube_feed->maybe_set_post_data_from_backup();
			$youtube_feed->maybe_set_header_data_from_backup();
		}

		$settings['feed_avatars'] = array();
		if ( $youtube_feed->need_avatars( $settings ) ) {
			$youtube_feed->set_up_feed_avatars( $youtube_feed_settings->get_connected_accounts_in_feed(), $feed_type_and_terms );
			$settings['feed_avatars'] = $youtube_feed->get_channel_id_avatars();
		}

		// if need a header
		if ( $youtube_feed->need_header( $settings, $feed_type_and_terms ) && ! $youtube_feed->should_use_backup() ) {
			if ( $database_settings['caching_type'] === 'background' ) {
				$youtube_feed->add_report( 'background header caching used' );
				$youtube_feed->set_header_data_from_cache();
			} elseif ( $youtube_feed->regular_header_cache_exists() ) {
				// set_post_data_from_cache
				$youtube_feed->add_report( 'page load caching used and regular header cache exists' );
				$youtube_feed->set_header_data_from_cache();
			} else {
				$youtube_feed->add_report( 'no header cache exists' );
				$youtube_feed->set_remote_header_data( $settings, $feed_type_and_terms, $youtube_feed_settings->get_connected_accounts_in_feed() );

				$youtube_feed->cache_header_data( $youtube_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );
			}
		} else {
			if ( $settings['showheader'] ) {
				$settings['generic_header'] = true;
				$youtube_feed->add_report( 'using generic header' );
			} else {
				$youtube_feed->add_report( 'no header needed' );
			}
		}

		return $youtube_feed->get_the_feed_html( $settings, $atts, $youtube_feed_settings->get_feed_type_and_terms(), $youtube_feed_settings->get_connected_accounts_in_feed() );
	}
}