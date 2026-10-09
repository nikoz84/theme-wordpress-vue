<?php
/**
 * Vue Blocks — "Mais Lidas" sidebar widget (by view count).
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vb_most_read = vb_get_most_read( 5 );
if ( ! $vb_most_read ) {
	return;
}
?>
<div class="sidebar-block">
	<div class="sidebar-title"><?php esc_html_e( 'Mais Lidas', 'vue-blocks' ); ?></div>
	<?php foreach ( $vb_most_read as $vb_i => $vb_post ) : ?>
		<a href="<?php echo esc_url( get_permalink( $vb_post ) ); ?>" class="trend-item">
			<span class="trend-num"><?php echo esc_html( sprintf( '%02d', $vb_i + 1 ) ); ?></span>
			<span class="trend-title"><?php echo esc_html( get_the_title( $vb_post ) ); ?></span>
		</a>
	<?php endforeach; ?>
</div>
