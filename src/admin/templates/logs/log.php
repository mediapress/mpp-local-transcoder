<?php
/**
 * Log item list.
 *
 * @package    MPP Local Transcoder
 * @subpackage Admin/Queue
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'History', 'mpp-local-transcoder'); ?></h1>
	<hr class="wp-header-end">
	<?php mpplt_admin_notice(); ?>
	<form method="get" action="<?php echo esc_url( mpplt_admin_get_queue_url( array( 'page' => 'mpplt-log' ) ) ); ?>">

		<?php
		$table = $this->get_table();
		$table->views();
		$table->prepare_items();
		$table->display();
		$this->hidden_fields();
		?>

	</form>
</div>
