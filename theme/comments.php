<?php
/**
 * O template usado para exibir comentários (comments.php)
 *
 * @package Vue_Blocks
 */

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			$comments_number = get_comments_number();
			if ( 1 === (int) $comments_number ) {
				esc_html_e( '1 comentário', 'vue-blocks' );
			} else {
				printf(
					/* translators: %s: número de comentários */
					esc_html( _n( '%s comentário', '%s comentários', $comments_number, 'vue-blocks' ) ),
					esc_html( number_format_i18n( $comments_number ) )
				);
			}
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => __( '&laquo; Anterior', 'vue-blocks' ),
				'next_text' => __( 'Próximo &raquo;', 'vue-blocks' ),
			)
		);
		?>

	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="no-comments"><?php esc_html_e( 'Os comentários estão fechados.', 'vue-blocks' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_submit' => 'vb-btn',
		)
	);
	?>

</div><!-- #comments -->
