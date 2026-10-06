<?php
/**
 * Template part exibido quando nenhum conteúdo é encontrado.
 *
 * @package Vue_Blocks
 */
?>
<section class="no-results not-found">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'Nada encontrado', 'vue-blocks' ); ?></h1>
	</header>

	<div class="page-content">
		<?php if ( is_search() ) : ?>
			<p><?php esc_html_e( 'Desculpe, mas nada corresponde aos termos pesquisados. Tente novamente com outras palavras-chave.', 'vue-blocks' ); ?></p>
			<?php get_search_form(); ?>
		<?php elseif ( is_home() && current_user_can( 'publish_posts' ) ) : ?>
			<p>
				<?php
				printf(
					wp_kses(
						/* translators: %s: link para criar um novo post */
						__( 'Pronto para publicar seu primeiro post? <a href="%s">Comece aqui</a>.', 'vue-blocks' ),
						array( 'a' => array( 'href' => array() ) )
					),
					esc_url( admin_url( 'post-new.php' ) )
				);
				?>
			</p>
		<?php else : ?>
			<p><?php esc_html_e( 'Nenhum conteúdo disponível no momento.', 'vue-blocks' ); ?></p>
		<?php endif; ?>
	</div>
</section>
