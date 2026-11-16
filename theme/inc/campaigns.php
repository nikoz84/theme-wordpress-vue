<?php
/**
 * Newsletter campaigns (Phase 1).
 *
 * Adds a `vb_campaign` post type with compose/schedule/send/report
 * capabilities on top of the existing `vb_subscriber` list.
 *
 * @package Vue_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================
   1. CAMPAIGN POST TYPE
   ========================================================= */

function vb_register_campaign_type() {
	register_post_type(
		'vb_campaign',
		array(
			'labels'       => array(
				'name'          => __( 'Campanhas', 'vue-blocks' ),
				'singular_name' => __( 'Campanha', 'vue-blocks' ),
				'add_new'       => __( 'Nova campanha', 'vue-blocks' ),
				'add_new_item'  => __( 'Nova campanha', 'vue-blocks' ),
				'edit_item'     => __( 'Editar campanha', 'vue-blocks' ),
				'all_items'     => __( 'Campanhas', 'vue-blocks' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_rest' => false,
			'menu_icon'    => 'dashicons-megaphone',
			'menu_position'=> 27,
			'supports'     => array( 'title', 'editor' ),
			'capabilities' => array(
				'edit_post'          => 'manage_options',
				'read_post'         => 'manage_options',
				'delete_post'       => 'manage_options',
				'edit_posts'        => 'manage_options',
				'edit_others_posts' => 'manage_options',
				'delete_posts'      => 'manage_options',
				'publish_posts'     => 'manage_options',
				'read_private_posts'=> 'manage_options',
				'create_posts'      => 'manage_options',
			),
			'map_meta_cap' => false,
		)
	);
}
add_action( 'init', 'vb_register_campaign_type' );

/* =========================================================
   2. META BOXES
   ========================================================= */

function vb_campaign_meta_boxes() {
	add_meta_box( 'vb_campaign_settings', __( 'Configurações da campanha', 'vue-blocks' ), 'vb_campaign_settings_box', 'vb_campaign', 'side', 'high' );
	add_meta_box( 'vb_campaign_report', __( 'Relatório', 'vue-blocks' ), 'vb_campaign_report_box', 'vb_campaign', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'vb_campaign_meta_boxes' );

function vb_campaign_settings_box( $post ) {
	wp_nonce_field( 'vb_campaign_save', 'vb_campaign_nonce' );
	$subject   = get_post_meta( $post->ID, '_vb_campaign_subject', true );
	$preview   = get_post_meta( $post->ID, '_vb_campaign_preview', true );
	$from_name = get_post_meta( $post->ID, '_vb_campaign_from_name', true );
	$from_email= get_post_meta( $post->ID, '_vb_campaign_from_email', true );
	$segment   = get_post_meta( $post->ID, '_vb_campaign_segment', true );
	$schedule  = get_post_meta( $post->ID, '_vb_campaign_schedule', true );
	$status    = get_post_meta( $post->ID, '_vb_campaign_status', true );
	if ( ! $status ) {
		$status = 'draft';
	}
	if ( ! $segment ) {
		$segment = 'confirmed';
	}
	if ( ! $from_name ) {
		$from_name = get_bloginfo( 'name' );
	}
	if ( ! $from_email ) {
		$from_email = get_option( 'admin_email' );
	}
	?>
	<p>
		<label for="vb_campaign_subject"><strong><?php esc_html_e( 'Assunto', 'vue-blocks' ); ?></strong></label><br>
		<input type="text" id="vb_campaign_subject" name="vb_campaign_subject" value="<?php echo esc_attr( $subject ); ?>" class="widefat">
	</p>
	<p>
		<label for="vb_campaign_preview"><strong><?php esc_html_e( 'Texto de prévia', 'vue-blocks' ); ?></strong></label><br>
		<input type="text" id="vb_campaign_preview" name="vb_campaign_preview" value="<?php echo esc_attr( $preview ); ?>" class="widefat">
	</p>
	<p>
		<label for="vb_campaign_from_name"><strong><?php esc_html_e( 'Nome do remetente', 'vue-blocks' ); ?></strong></label><br>
		<input type="text" id="vb_campaign_from_name" name="vb_campaign_from_name" value="<?php echo esc_attr( $from_name ); ?>" class="widefat">
	</p>
	<p>
		<label for="vb_campaign_from_email"><strong><?php esc_html_e( 'E-mail do remetente', 'vue-blocks' ); ?></strong></label><br>
		<input type="email" id="vb_campaign_from_email" name="vb_campaign_from_email" value="<?php echo esc_attr( $from_email ); ?>" class="widefat">
	</p>
	<p>
		<label for="vb_campaign_segment"><strong><?php esc_html_e( 'Segmento', 'vue-blocks' ); ?></strong></label><br>
		<select id="vb_campaign_segment" name="vb_campaign_segment" class="widefat">
			<option value="confirmed" <?php selected( $segment, 'confirmed' ); ?>><?php esc_html_e( 'Confirmados', 'vue-blocks' ); ?></option>
			<option value="pending" <?php selected( $segment, 'pending' ); ?>><?php esc_html_e( 'Aguardando confirmação', 'vue-blocks' ); ?></option>
			<option value="all" <?php selected( $segment, 'all' ); ?>><?php esc_html_e( 'Todos', 'vue-blocks' ); ?></option>
		</select>
	</p>
	<p>
		<label for="vb_campaign_schedule"><strong><?php esc_html_e( 'Agendar envio (data/hora)', 'vue-blocks' ); ?></strong></label><br>
		<input type="datetime-local" id="vb_campaign_schedule" name="vb_campaign_schedule" value="<?php echo esc_attr( $schedule ); ?>" class="widefat">
		<small><?php esc_html_e( 'Deixe vazio para enviar manualmente.', 'vue-blocks' ); ?></small>
	</p>
	<p>
		<label><strong><?php esc_html_e( 'Status', 'vue-blocks' ); ?></strong></label><br>
		<code><?php echo esc_html( $status ); ?></code>
	</p>
	<?php
}

function vb_campaign_report_box( $post ) {
	$status   = get_post_meta( $post->ID, '_vb_campaign_status', true );
	$total    = (int) get_post_meta( $post->ID, '_vb_campaign_total', true );
	$sent     = (int) get_post_meta( $post->ID, '_vb_campaign_sent_count', true );
	$finished = get_post_meta( $post->ID, '_vb_campaign_finished', true );
	$error    = get_post_meta( $post->ID, '_vb_campaign_last_error', true );
	?>
	<p><strong><?php esc_html_e( 'Status', 'vue-blocks' ); ?>:</strong> <?php echo esc_html( $status ? $status : 'draft' ); ?></p>
	<p><strong><?php esc_html_e( 'Enviados', 'vue-blocks' ); ?>:</strong> <?php echo esc_html( $sent . ' / ' . $total ); ?></p>
	<?php if ( $finished ) : ?>
		<p><strong><?php esc_html_e( 'Concluído em', 'vue-blocks' ); ?>:</strong> <?php echo esc_html( $finished ); ?></p>
	<?php endif; ?>
	<?php if ( $error ) : ?>
		<p style="color:#b32d2e;"><strong><?php esc_html_e( 'Erro', 'vue-blocks' ); ?>:</strong> <?php echo esc_html( $error ); ?></p>
	<?php endif; ?>

	<?php if ( 'sending' !== $status && 'sent' !== $status ) : ?>
		<p>
			<button type="submit" name="vb_campaign_action" value="send_now" class="button button-primary"><?php esc_html_e( 'Enviar agora', 'vue-blocks' ); ?></button>
		</p>
		<p>
			<button type="submit" name="vb_campaign_action" value="schedule" class="button"><?php esc_html_e( 'Agendar', 'vue-blocks' ); ?></button>
		</p>
	<?php elseif ( 'sending' === $status ) : ?>
		<p>
			<button type="submit" name="vb_campaign_action" value="process" class="button"><?php esc_html_e( 'Processar fila agora', 'vue-blocks' ); ?></button>
		</p>
	<?php endif; ?>
	<?php
}

function vb_campaign_save_meta( $post_id ) {
	if ( ! isset( $_POST['vb_campaign_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vb_campaign_nonce'] ) ), 'vb_campaign_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$fields = array(
		'vb_campaign_subject'    => '_vb_campaign_subject',
		'vb_campaign_preview'    => '_vb_campaign_preview',
		'vb_campaign_from_name'  => '_vb_campaign_from_name',
		'vb_campaign_from_email' => '_vb_campaign_from_email',
		'vb_campaign_segment'    => '_vb_campaign_segment',
		'vb_campaign_schedule'   => '_vb_campaign_schedule',
	);

	foreach ( $fields as $post_key => $meta_key ) {
		if ( isset( $_POST[ $post_key ] ) ) {
			update_post_meta( $post_id, $meta_key, sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) ) );
		}
	}

	// Handle action buttons.
	if ( isset( $_POST['vb_campaign_action'] ) ) {
		$action = sanitize_key( wp_unslash( $_POST['vb_campaign_action'] ) );
		if ( 'send_now' === $action ) {
			vb_campaign_start_send( $post_id );
		} elseif ( 'schedule' === $action ) {
			$schedule = get_post_meta( $post_id, '_vb_campaign_schedule', true );
			if ( $schedule ) {
				$ts = strtotime( $schedule );
				if ( $ts && $ts > time() ) {
					wp_clear_scheduled_hook( 'vb_campaign_send_event', array( $post_id ) );
					wp_schedule_single_event( $ts, 'vb_campaign_send_event', array( $post_id ) );
					update_post_meta( $post_id, '_vb_campaign_status', 'scheduled' );
				}
			}
		} elseif ( 'process' === $action ) {
			vb_campaign_send_batch( $post_id );
		}
	}
}
add_action( 'save_post_vb_campaign', 'vb_campaign_save_meta' );

/* =========================================================
   3. ADMIN COLUMNS
   ========================================================= */

function vb_campaign_columns( $columns ) {
	return array(
		'cb'        => $columns['cb'],
		'title'     => __( 'Campanha', 'vue-blocks' ),
		'vb_status' => __( 'Status', 'vue-blocks' ),
		'vb_sched'  => __( 'Agendado', 'vue-blocks' ),
		'vb_progress'=> __( 'Enviados', 'vue-blocks' ),
		'date'      => __( 'Criado', 'vue-blocks' ),
	);
}
add_filter( 'manage_vb_campaign_posts_columns', 'vb_campaign_columns' );

function vb_campaign_column_value( $column, $post_id ) {
	if ( 'vb_status' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_vb_campaign_status', true ) ?: 'draft' );
	} elseif ( 'vb_sched' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_vb_campaign_schedule', true ) );
	} elseif ( 'vb_progress' === $column ) {
		$sent  = (int) get_post_meta( $post_id, '_vb_campaign_sent_count', true );
		$total = (int) get_post_meta( $post_id, '_vb_campaign_total', true );
		echo esc_html( $sent . ' / ' . $total );
	}
}
add_action( 'manage_vb_campaign_posts_custom_column', 'vb_campaign_column_value', 10, 2 );

/* =========================================================
   4. SENDING ENGINE
   ========================================================= */

function vb_campaign_subscriber_ids( $segment ) {
	$meta_value = '';
	if ( 'confirmed' === $segment ) {
		$meta_value = 'confirmed';
	} elseif ( 'pending' === $segment ) {
		$meta_value = 'pending';
	}

	$args = array(
		'post_type'      => 'vb_subscriber',
		'post_status'    => 'private',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'ID',
		'order'          => 'ASC',
	);

	if ( $meta_value ) {
		$args['meta_query'] = array(
			array(
				'key'   => 'vb_status',
				'value' => $meta_value,
			),
		);
	}

	return get_posts( $args );
}

function vb_campaign_start_send( $campaign_id ) {
	$segment = get_post_meta( $campaign_id, '_vb_campaign_segment', true ) ?: 'confirmed';
	$ids     = vb_campaign_subscriber_ids( $segment );

	update_post_meta( $campaign_id, '_vb_campaign_status', 'sending' );
	update_post_meta( $campaign_id, '_vb_campaign_total', count( $ids ) );
	update_post_meta( $campaign_id, '_vb_campaign_offset', 0 );
	update_post_meta( $campaign_id, '_vb_campaign_sent_count', 0 );
	delete_post_meta( $campaign_id, '_vb_campaign_finished' );
	delete_post_meta( $campaign_id, '_vb_campaign_last_error' );

	vb_campaign_send_batch( $campaign_id );
}

function vb_campaign_unsubscribe_token( $email, $subscriber_id ) {
	return wp_hash( $email . '|' . $subscriber_id );
}

function vb_campaign_render_email( $campaign_id, $subscriber_id ) {
	$subject   = get_post_meta( $campaign_id, '_vb_campaign_subject', true );
	if ( ! $subject ) {
		$subject = get_the_title( $campaign_id );
	}
	$body = get_post_field( 'post_content', $campaign_id );
	$body = apply_filters( 'the_content', $body );

	$email     = get_the_title( $subscriber_id );
	$name      = get_post_meta( $subscriber_id, 'vb_name', true );
	$token     = vb_campaign_unsubscribe_token( $email, $subscriber_id );
	$unsub_url = add_query_arg(
		array(
			'action' => 'vb_newsletter_unsubscribe',
			'email'  => rawurlencode( $email ),
			'token'  => $token,
		),
		admin_url( 'admin-post.php' )
	);

	$body = str_replace( '{name}', esc_html( $name ? $name : $email ), $body );
	$body = str_replace( '{unsubscribe_url}', esc_url( $unsub_url ), $body );

	$preview = get_post_meta( $campaign_id, '_vb_campaign_preview', true );

	$html  = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>';
	if ( $preview ) {
		$html .= '<span style="display:none!important;visibility:hidden;opacity:0;color:transparent;height:0;width:0;overflow:hidden;mso-hide:all;">' . esc_html( $preview ) . '</span>';
	}
	$html .= $body;
	$html .= '<p style="font-size:12px;color:#666;margin-top:24px;"><a href="' . esc_url( $unsub_url ) . '">' . esc_html__( 'Cancelar inscrição', 'vue-blocks' ) . '</a></p>';
	$html .= '</body></html>';

	return array( 'subject' => $subject, 'html' => $html );
}

function vb_campaign_send_batch( $campaign_id ) {
	$status = get_post_meta( $campaign_id, '_vb_campaign_status', true );
	if ( 'sending' !== $status ) {
		return;
	}

	$segment    = get_post_meta( $campaign_id, '_vb_campaign_segment', true ) ?: 'confirmed';
	$offset     = (int) get_post_meta( $campaign_id, '_vb_campaign_offset', true );
	$batch_size = 50;
	$from_name  = get_post_meta( $campaign_id, '_vb_campaign_from_name', true ) ?: get_bloginfo( 'name' );
	$from_email = get_post_meta( $campaign_id, '_vb_campaign_from_email', true ) ?: get_option( 'admin_email' );

	$ids = vb_campaign_subscriber_ids( $segment );
	$batch = array_slice( $ids, $offset, $batch_size );

	if ( empty( $batch ) ) {
		update_post_meta( $campaign_id, '_vb_campaign_status', 'sent' );
		update_post_meta( $campaign_id, '_vb_campaign_finished', current_time( 'mysql' ) );
		wp_clear_scheduled_hook( 'vb_campaign_send_event', array( $campaign_id ) );
		return;
	}

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: ' . $from_name . ' <' . $from_email . '>',
	);

	foreach ( $batch as $subscriber_id ) {
		$email = get_the_title( $subscriber_id );
		if ( ! is_email( $email ) ) {
			continue;
		}
		$rendered = vb_campaign_render_email( $campaign_id, $subscriber_id );
		wp_mail( $email, $rendered['subject'], $rendered['html'], $headers );
	}

	$new_offset = $offset + count( $batch );
	update_post_meta( $campaign_id, '_vb_campaign_offset', $new_offset );
	update_post_meta( $campaign_id, '_vb_campaign_sent_count', $new_offset );

	$total = (int) get_post_meta( $campaign_id, '_vb_campaign_total', true );
	if ( $new_offset >= $total ) {
		update_post_meta( $campaign_id, '_vb_campaign_status', 'sent' );
		update_post_meta( $campaign_id, '_vb_campaign_finished', current_time( 'mysql' ) );
		wp_clear_scheduled_hook( 'vb_campaign_send_event', array( $campaign_id ) );
	} else {
		wp_schedule_single_event( time() + 60, 'vb_campaign_send_event', array( $campaign_id ) );
	}
}
add_action( 'vb_campaign_send_event', 'vb_campaign_send_batch' );

/* =========================================================
   5. UNSUBSCRIBE ENDPOINT
   ========================================================= */

function vb_newsletter_unsubscribe_campaign() {
	$email = isset( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : '';
	$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';

	if ( ! is_email( $email ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	$subs = get_posts(
		array(
			'post_type'   => 'vb_subscriber',
			'post_status' => 'private',
			'title'       => $email,
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);

	if ( $subs ) {
		$sub_id = (int) $subs[0];
		$expected = vb_campaign_unsubscribe_token( $email, $sub_id );
		if ( hash_equals( $expected, $token ) ) {
			update_post_meta( $sub_id, 'vb_status', 'unsubscribed' );
		}
	}

	wp_safe_redirect( add_query_arg( 'newsletter', 'unsubscribed', home_url( '/' ) ) );
	exit;
}
add_action( 'admin_post_nopriv_vb_newsletter_unsubscribe', 'vb_newsletter_unsubscribe_campaign' );
add_action( 'admin_post_vb_newsletter_unsubscribe', 'vb_newsletter_unsubscribe_campaign' );