<?php
/**
 * Vue Blocks — Newsletter sidebar widget.
 *
 * Posts to admin-post.php (`vb_newsletter_subscribe`, inc/news.php).
 *
 * @param array $args {
 *     @type string $title     Heading.
 *     @type string $desc      Optional description.
 *     @type bool   $show_name Show the "Seu nome" field.
 * }
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vb_nl_args = wp_parse_args(
	$args ?? array(),
	array(
		'title'     => __( 'Receba as notícias no e-mail', 'vue-blocks' ),
		'desc'      => '',
		'show_name' => true,
	)
);
$vb_nl_msg = vb_newsletter_message( 'newsletter' );
?>
<div class="sidebar-nl" id="newsletter">
	<div class="sidebar-nl-title"><?php echo esc_html( $vb_nl_args['title'] ); ?></div>
	<?php if ( $vb_nl_args['desc'] ) : ?>
		<p class="sidebar-nl-desc"><?php echo esc_html( $vb_nl_args['desc'] ); ?></p>
	<?php endif; ?>
	<form class="sidebar-nl-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php vb_newsletter_hidden_fields( 'newsletter' ); ?>
		<?php if ( $vb_nl_args['show_name'] ) : ?>
			<label class="screen-reader-text" for="vb-nl-name"><?php esc_html_e( 'Seu nome', 'vue-blocks' ); ?></label>
			<input class="sidebar-nl-input" id="vb-nl-name" type="text" name="vb_name" autocomplete="name" placeholder="<?php esc_attr_e( 'Seu nome', 'vue-blocks' ); ?>" />
		<?php endif; ?>
		<label class="screen-reader-text" for="vb-nl-email"><?php esc_html_e( 'Seu melhor e-mail', 'vue-blocks' ); ?></label>
		<input class="sidebar-nl-input" id="vb-nl-email" type="email" name="vb_email" required autocomplete="email" placeholder="<?php esc_attr_e( 'Seu melhor e-mail', 'vue-blocks' ); ?>" />
		<button class="sidebar-nl-btn" type="submit"><?php esc_html_e( 'Assinar grátis', 'vue-blocks' ); ?></button>
		<?php if ( $vb_nl_msg ) : ?>
			<p class="sidebar-nl-msg" role="status"><?php echo esc_html( $vb_nl_msg ); ?></p>
		<?php endif; ?>
	</form>
</div>
