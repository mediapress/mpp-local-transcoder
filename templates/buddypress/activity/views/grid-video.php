<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php

$mppq = new MPP_Cached_Media_Query( array( 'in' => mpp_activity_get_displayable_media_ids( $activity_id ) ) );

if ( $mppq->have_media() ) : ?>
	<div class="mpp-container mpp-activity-container mpp-media-list mpp-activity-media-list mpp-activity-video-list mpp-activity-video-player mpp-media-list-view-grid mpp-activity-media-list-view-grid mpp-video-view-grid mpp-activity-video-view-grid">

		<?php while ( $mppq->have_media() ) : $mppq->the_media(); ?>
			<?php $type = mpp_get_media_type(); ?>
			<div class="mpp-item-content mpp-activity-item-content mpp-video-content mpp-activity-video-content" data-mpp-type="<?php echo $type;?>">
				<?php if ( mpplt_media_in_queue( mpp_get_current_media_id() ) ) : ?>
                    <img src="<?php echo esc_url( mpp_get_option( 'mpplt_default_thumbnail', mpplt_local_transcoder()->url . 'assets/img/default-thumbnail.png' ) ); ?>" title="<?php esc_html_e( 'Media under coversion', 'mpp-local-transcoder' );?>" alt="<?php esc_html_e( 'Media under coversion', 'mpp-local-transcoder' );?>" />
				<?php else: ?>
                <?php mpp_media_content(); ?>
                <a class="mpp-activity-item-title mpp-activity-video-title" href="<?php mpp_media_permalink() ?>" title="<?php echo esc_attr( mpp_get_media_title() ); ?>" data-mpp-type="<?php echo $type;?>" data-mpp-activity-id="<?php echo $activity_id; ?>" data-mpp-media-id="<?php mpp_media_id(); ?>"><?php mpp_media_title(); ?></a>
                <?php endif; ?>
			</div>

		<?php endwhile; ?>
		<script type='text/javascript'>
			mpp_mejs_activate(<?php echo $activity_id;?>);
		</script>
	</div>
<?php endif; ?>
<?php mpp_reset_media_data(); ?>
