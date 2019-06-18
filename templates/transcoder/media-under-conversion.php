<?php
/**
 * Copy it to yourttheme/mediapress/default/transcoder and change as you please.
 */
?>

<img src="<?php echo esc_url( mpp_get_option( 'mpplt_default_thumbnail', mpplt_local_transcoder()->url . 'assets/img/default-thumbnail.png' ) ); ?>" title="<?php esc_html_e( 'Media under coversion', 'mpp-local-transcoder' );?>" alt="<?php esc_html_e( 'Media under coversion', 'mpp-local-transcoder' );?>" />
