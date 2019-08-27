<?php
/**
 * List all items as unordered list
 *
 */
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
$gallery = mpp_get_current_gallery();
$type    = $gallery->type;
?>
<ul class="mpp-u mpp-item-list mpp-list-item-<?php echo $type; ?>">

	<?php while ( mpp_have_media() ) : mpp_the_media(); ?>

		<li class="mpp-list-item-entry mpp-list-item-entry-<?php echo $type; ?>" data-mpp-type="<?php echo $type;?>">

			<?php if ( mpplt_media_in_queue( mpp_get_current_media_id() ) ) : ?>
                <img src="<?php echo esc_url( mpp_get_option( 'mpplt_default_thumbnail', mpplt_local_transcoder()->url . 'assets/img/default-thumbnail.png' ) ); ?>" title="<?php esc_html_e( 'Media under coversion', 'mpp-local-transcoder' );?>" alt="<?php esc_html_e( 'Media under coversion', 'mpp-local-transcoder' );?>" />
			<?php else: ?>
			<?php do_action( 'mpp_before_media_item' ); ?>

			<a href="<?php mpp_media_permalink(); ?>" class="mpp-item-title mpp-media-title" data-mpp-type="<?php echo $type;?>"><?php mpp_media_title(); ?></a>

			<div class="mpp-item-actions mpp-media-actions">
				<?php mpp_media_action_links(); ?>
			</div>

			<?php do_action( 'mpp_after_media_item' ); ?>
            <?php endif; ?>
		</li>

	<?php endwhile; ?>

</ul>
