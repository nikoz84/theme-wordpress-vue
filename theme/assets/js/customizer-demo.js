/**
 * Vue Blocks — Customizer "Demo content" buttons.
 *
 * Calls the `vb_demo` admin-ajax action and refreshes the preview.
 * `vbDemo` is injected by wp_localize_script() in inc/demo-import.php.
 *
 * @package Vue_Blocks
 */

( function () {
	'use strict';

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.vb-demo-btn' );
		if ( ! button || button.disabled ) {
			return;
		}

		var action = button.getAttribute( 'data-demo-action' );
		if ( action === 'remove' && ! window.confirm( vbDemo.i18n.confirmRemove ) ) {
			return;
		}

		var control = button.closest( '.customize-control' );
		var status = control.querySelector( '.vb-demo-status' );
		var buttons = control.querySelectorAll( '.vb-demo-btn' );

		var previous = Array.prototype.map.call( buttons, function ( b ) {
			return b.disabled;
		} );
		var restore = function () {
			buttons.forEach( function ( b, i ) {
				b.disabled = previous[ i ];
			} );
		};

		buttons.forEach( function ( b ) {
			b.disabled = true;
		} );
		status.textContent = vbDemo.i18n.working;

		var body = new FormData();
		body.append( 'action', 'vb_demo' );
		body.append( 'demo_action', action );
		body.append( 'nonce', vbDemo.nonce );

		fetch( vbDemo.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' } )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( result ) {
				var data = result.data || {};
				if ( ! result.success ) {
					status.textContent = data.message || vbDemo.i18n.error;
					restore();
					return;
				}
				status.textContent = data.message + ' ' + ( data.imported ? vbDemo.i18n.imported : vbDemo.i18n.notImported );
				control.querySelector( '[data-demo-action="import"]' ).disabled = data.imported;
				control.querySelector( '[data-demo-action="remove"]' ).disabled = ! data.imported;
				if ( window.wp && wp.customize && wp.customize.previewer ) {
					wp.customize.previewer.refresh();
				}
			} )
			.catch( function () {
				status.textContent = vbDemo.i18n.error;
				restore();
			} );
	} );
} )();
