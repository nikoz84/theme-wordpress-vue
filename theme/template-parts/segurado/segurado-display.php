<?php
/**
 * Vue Blocks — Segurado display template.
 *
 * Renders segurado (insured/policyholder) data in semantic HTML.
 * Works without JavaScript. All output is escaped.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vb_post_id = get_the_ID();
$vb_segurado = vb_get_segurado( $vb_post_id );

if ( ! $vb_segurado ) {
	return;
}
?>

<section class="vb-segurado-section" data-segurado-post-id="<?php echo esc_attr( $vb_post_id ); ?>">
	<h2 class="vb-segurado-title"><?php esc_html_e( 'Segurado', 'vue-blocks' ); ?></h2>

	<dl class="vb-segurado-list">
		<div class="vb-segurado-item">
			<dt class="vb-segurado-term"><?php esc_html_e( 'Nome completo', 'vue-blocks' ); ?></dt>
			<dd class="vb-segurado-definition" data-segurado-field="full_name" data-segurado-value="<?php echo esc_attr( $vb_segurado['full_name'] ); ?>"><?php echo esc_html( $vb_segurado['full_name'] ); ?></dd>
		</div>

		<div class="vb-segurado-item">
			<dt class="vb-segurado-term"><?php esc_html_e( 'Tipo de documento', 'vue-blocks' ); ?></dt>
			<dd class="vb-segurado-definition" data-segurado-field="document_type" data-segurado-value="<?php echo esc_attr( $vb_segurado['document_type'] ); ?>"><?php echo esc_html( $vb_segurado['document_type'] ); ?></dd>
		</div>

		<div class="vb-segurado-item">
			<dt class="vb-segurado-term"><?php esc_html_e( 'Número do documento', 'vue-blocks' ); ?></dt>
			<dd class="vb-segurado-definition" data-segurado-field="document_number" data-segurado-value="<?php echo esc_attr( $vb_segurado['document_number'] ); ?>"><?php echo esc_html( $vb_segurado['document_number'] ); ?></dd>
		</div>

		<?php if ( ! empty( $vb_segurado['email'] ) ) : ?>
		<div class="vb-segurado-item">
			<dt class="vb-segurado-term"><?php esc_html_e( 'E-mail', 'vue-blocks' ); ?></dt>
			<dd class="vb-segurado-definition" data-segurado-field="email" data-segurado-value="<?php echo esc_attr( $vb_segurado['email'] ); ?>"><?php echo esc_html( $vb_segurado['email'] ); ?></dd>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $vb_segurado['phone'] ) ) : ?>
		<div class="vb-segurado-item">
			<dt class="vb-segurado-term"><?php esc_html_e( 'Telefone', 'vue-blocks' ); ?></dt>
			<dd class="vb-segurado-definition" data-segurado-field="phone" data-segurado-value="<?php echo esc_attr( $vb_segurado['phone'] ); ?>"><?php echo esc_html( $vb_segurado['phone'] ); ?></dd>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $vb_segurado['address']['line_1'] ) ) : ?>
		<div class="vb-segurado-item">
			<dt class="vb-segurado-term"><?php esc_html_e( 'Endereço', 'vue-blocks' ); ?></dt>
			<dd class="vb-segurado-definition" data-segurado-field="address_1" data-segurado-value="<?php echo esc_attr( $vb_segurado['address']['line_1'] ); ?>">
				<?php
				echo esc_html( $vb_segurado['address']['line_1'] );
				if ( ! empty( $vb_segurado['address']['line_2'] ) ) {
					echo ', ' . esc_html( $vb_segurado['address']['line_2'] );
				}
				if ( ! empty( $vb_segurado['address']['city'] ) ) {
					echo ', ' . esc_html( $vb_segurado['address']['city'] );
				}
				if ( ! empty( $vb_segurado['address']['state'] ) ) {
					echo ' - ' . esc_html( $vb_segurado['address']['state'] );
				}
				if ( ! empty( $vb_segurado['address']['postal_code'] ) ) {
					echo ', ' . esc_html( $vb_segurado['address']['postal_code'] );
				}
				?>
			</dd>
		</div>
		<?php endif; ?>
	</dl>
</section>