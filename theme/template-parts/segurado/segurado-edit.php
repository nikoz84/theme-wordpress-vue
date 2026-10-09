<?php
/**
 * Vue Blocks — Segurado edit form template.
 *
 * Renders a standard HTML form for editing segurado data.
 * Works without JavaScript. All output is escaped.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vb_post_id = get_the_ID();
$vb_segurado = vb_get_segurado( $vb_post_id );

$vb_full_name        = $vb_segurado['full_name'] ?? '';
$vb_document_type    = $vb_segurado['document_type'] ?? '';
$vb_document_number  = $vb_segurado['document_number'] ?? '';
$vb_email            = $vb_segurado['email'] ?? '';
$vb_phone            = $vb_segurado['phone'] ?? '';
$vb_address_1        = $vb_segurado['address']['line_1'] ?? '';
$vb_address_2        = $vb_segurado['address']['line_2'] ?? '';
$vb_city             = $vb_segurado['address']['city'] ?? '';
$vb_state            = $vb_segurado['address']['state'] ?? '';
$vb_postal_code      = $vb_segurado['address']['postal_code'] ?? '';
$vb_country          = $vb_segurado['address']['country'] ?? 'BR';

$vb_errors = array();
if ( isset( $_GET['vb_segurado_error'] ) ) {
	$vb_errors['form'] = __( 'Erro ao salvar dados. Verifique os campos e tente novamente.', 'vue-blocks' );
}
if ( isset( $_GET['vb_segurado_success'] ) ) {
	$vb_success = __( 'Dados salvos com sucesso.', 'vue-blocks' );
}
?>

<section class="vb-segurado-section vb-segurado-edit" data-segurado-post-id="<?php echo esc_attr( $vb_post_id ); ?>">
	<h2 class="vb-segurado-title"><?php esc_html_e( 'Editar Segurado', 'vue-blocks' ); ?></h2>

	<?php if ( ! empty( $vb_success ) ) : ?>
		<p class="vb-segurado-form-success"><?php echo esc_html( $vb_success ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $vb_errors['form'] ) ) : ?>
		<p class="vb-segurado-form-error"><?php echo esc_html( $vb_errors['form'] ); ?></p>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="vb-segurado-form">
		<input type="hidden" name="action" value="vb_update_segurado" />
		<input type="hidden" name="vb_segurado_post_id" value="<?php echo esc_attr( $vb_post_id ); ?>" />
		<?php wp_nonce_field( 'vb_update_segurado', 'vb_segurado_nonce' ); ?>

		<div class="vb-segurado-field">
			<label for="vb_segurado_full_name"><?php esc_html_e( 'Nome completo', 'vue-blocks' ); ?> *</label>
			<input type="text" id="vb_segurado_full_name" name="vb_segurado_full_name" value="<?php echo esc_attr( $vb_full_name ); ?>" required maxlength="200" />
			<?php if ( ! empty( $vb_errors['vb_segurado_full_name'] ) ) : ?>
				<p class="vb-segurado-field-error"><?php echo esc_html( $vb_errors['vb_segurado_full_name'] ); ?></p>
			<?php endif; ?>
		</div>

		<div class="vb-segurado-field">
			<label for="vb_segurado_document_type"><?php esc_html_e( 'Tipo de documento', 'vue-blocks' ); ?> *</label>
			<select id="vb_segurado_document_type" name="vb_segurado_document_type" required>
				<option value="cpf" <?php selected( $vb_document_type, 'cpf' ); ?>>CPF</option>
				<option value="cnpj" <?php selected( $vb_document_type, 'cnpj' ); ?>>CNPJ</option>
				<option value="passport" <?php selected( $vb_document_type, 'passport' ); ?>>Passaporte</option>
				<option value="other" <?php selected( $vb_document_type, 'other' ); ?>>Outro</option>
			</select>
		</div>

		<div class="vb-segurado-field">
			<label for="vb_segurado_document_number"><?php esc_html_e( 'Número do documento', 'vue-blocks' ); ?> *</label>
			<input type="text" id="vb_segurado_document_number" name="vb_segurado_document_number" value="<?php echo esc_attr( $vb_document_number ); ?>" required />
			<?php if ( ! empty( $vb_errors['vb_segurado_document_number'] ) ) : ?>
				<p class="vb-segurado-field-error"><?php echo esc_html( $vb_errors['vb_segurado_document_number'] ); ?></p>
			<?php endif; ?>
		</div>

		<div class="vb-segurado-field">
			<label for="vb_segurado_email"><?php esc_html_e( 'E-mail', 'vue-blocks' ); ?></label>
			<input type="email" id="vb_segurado_email" name="vb_segurado_email" value="<?php echo esc_attr( $vb_email ); ?>" />
			<?php if ( ! empty( $vb_errors['vb_segurado_email'] ) ) : ?>
				<p class="vb-segurado-field-error"><?php echo esc_html( $vb_errors['vb_segurado_email'] ); ?></p>
			<?php endif; ?>
		</div>

		<div class="vb-segurado-field">
			<label for="vb_segurado_phone"><?php esc_html_e( 'Telefone', 'vue-blocks' ); ?></label>
			<input type="tel" id="vb_segurado_phone" name="vb_segurado_phone" value="<?php echo esc_attr( $vb_phone ); ?>" maxlength="20" />
		</div>

		<div class="vb-segurado-field">
			<label for="vb_segurado_address_1"><?php esc_html_e( 'Endereço linha 1', 'vue-blocks' ); ?></label>
			<input type="text" id="vb_segurado_address_1" name="vb_segurado_address_1" value="<?php echo esc_attr( $vb_address_1 ); ?>" maxlength="200" />
		</div>

		<div class="vb-segurado-field">
			<label for="vb_segurado_address_2"><?php esc_html_e( 'Endereço linha 2', 'vue-blocks' ); ?></label>
			<input type="text" id="vb_segurado_address_2" name="vb_segurado_address_2" value="<?php echo esc_attr( $vb_address_2 ); ?>" maxlength="200" />
		</div>

		<div class="vb-segurado-field">
			<label for="vb_segurado_city"><?php esc_html_e( 'Cidade', 'vue-blocks' ); ?></label>
			<input type="text" id="vb_segurado_city" name="vb_segurado_city" value="<?php echo esc_attr( $vb_city ); ?>" maxlength="100" />
		</div>

		<div class="vb-segurado-field">
			<label for="vb_segurado_state"><?php esc_html_e( 'Estado', 'vue-blocks' ); ?></label>
			<input type="text" id="vb_segurado_state" name="vb_segurado_state" value="<?php echo esc_attr( $vb_state ); ?>" maxlength="100" />
		</div>

		<div class="vb-segurado-field">
			<label for="vb_segurado_postal_code"><?php esc_html_e( 'CEP', 'vue-blocks' ); ?></label>
			<input type="text" id="vb_segurado_postal_code" name="vb_segurado_postal_code" value="<?php echo esc_attr( $vb_postal_code ); ?>" maxlength="20" />
		</div>

		<div class="vb-segurado-field">
			<label for="vb_segurado_country"><?php esc_html_e( 'País', 'vue-blocks' ); ?></label>
			<input type="text" id="vb_segurado_country" name="vb_segurado_country" value="<?php echo esc_attr( $vb_country ); ?>" maxlength="100" />
		</div>

		<button type="submit" class="vb-btn"><?php esc_html_e( 'Salvar', 'vue-blocks' ); ?></button>
	</form>
</section>