<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php while ( mpp_have_media() ): mpp_the_media(); ?>

		<?php $type = mpp_get_media_type(); ?>
        <div class="<?php mpp_media_class( 'mpp-u-12-24' ); ?>" data-mpp-type="<?php echo $type;?>">

            <?php if ( mpplt_media_in_queue( mpp_get_current_media_id() ) ) : ?>
                <img src="<?php echo esc_url( mpp_get_option( 'mpplt_default_thumbnail', mpplt_local_transcoder()->url . 'assets/img/default-thumbnail.png' ) ); ?>" title="<?php esc_html_e( 'Media under coversion', 'mpp-local-transcoder' );?>" alt="<?php esc_html_e( 'Media under coversion', 'mpp-local-transcoder' );?>" />
            <?php else: ?>

			<?php do_action( 'mpp_before_media_item' ); ?>

            <div class="mpp-item-meta mpp-media-meta mpp-media-meta-top">
				<?php do_action( 'mpp_media_meta_top' ); ?>
            </div>

			<?php

			$args = array(
				'src'      => mpp_get_media_src(),
				'loop'     => false,
				'autoplay' => false,
				'poster'   => mpp_get_media_src( 'thumbnail' ),
				'width'    => 320,
				'height'   => 180,
			);


			// $ids = mpp_get_all_media_ids();
			// echo wp_playlist_shortcode( array( 'ids' => $ids));
			?>
            <div class='mpp-item-entry mpp-media-entry mpp-audio-entry'>

            </div>

            <div class="mpp-item-content mpp-video-content mpp-video-player">
				<?php if ( mpp_is_oembed_media( mpp_get_media_id() ) ) : ?>
					<?php echo mpp_get_oembed_content( mpp_get_media_id(), 'mid' ); ?>
				<?php else : ?>
					<?php echo wp_video_shortcode( $args ); ?>
				<?php endif; ?>
            </div>

            <a href="<?php mpp_media_permalink(); ?>"
               class="mpp-item-title mpp-media-title mpp-audio-title"><?php mpp_media_title(); ?></a>

            <div class="mpp-item-actions mpp-media-actions mpp-video-actions">
				<?php mpp_media_action_links(); ?>
            </div>

            <div class="mpp-type-icon"><?php do_action( 'mpp_type_icon', mpp_get_media_type(), mpp_get_media() ); ?></div>

            <div class="mpp-item-meta mpp-media-meta mpp-media-meta-bottom">
				<?php do_action( 'mpp_media_meta' ); ?>
            </div>

			<?php do_action( 'mpp_after_media_item' ); ?>

	<?php endif; ?>
        </div>

<?php endwhile; ?>