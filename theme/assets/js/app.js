/**
 * Vue Blocks — camada de interação do frontend.
 *
 * Este arquivo contém três pequenas aplicações Vue 3, cada uma "montada"
 * (createApp().mount()) sobre um elemento já renderizado pelo PHP.
 * Isso é chamado de "in-DOM template": o Vue compila o HTML existente
 * (com as diretivas v-on, v-bind, v-for, {{ }}) como se fosse um template,
 * então o tema continua funcionando 100% sem JavaScript e o Vue apenas
 * "liga" a interatividade por cima (progressive enhancement).
 *
 * `vbData` é injetado pelo PHP via wp_localize_script() em functions.php.
 *
 * @package Vue_Blocks
 */

( function () {
	'use strict';

	/**
	 * 0) NAVBAR SAFE MÍDIA: abre/fecha o menu mobile pelo botão hambúrguer.
	 * JS puro (sem Vue) para funcionar mesmo se o CDN do Vue falhar.
	 */
	var hamburger = document.getElementById( 'safe-midia-navHamburger' );
	var mobileMenu = document.getElementById( 'safe-midia-mobileMenu' );
	if ( hamburger && mobileMenu ) {
		var setMenuOpen = function ( open ) {
			mobileMenu.classList.toggle( 'open', open );
			hamburger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		};

		hamburger.addEventListener( 'click', function () {
			setMenuOpen( ! mobileMenu.classList.contains( 'open' ) );
		} );

		// Fecha ao clicar em um link, ao pressionar Esc ou ao clicar fora.
		mobileMenu.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( 'a' ) ) {
				setMenuOpen( false );
			}
		} );
		document.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' && mobileMenu.classList.contains( 'open' ) ) {
				setMenuOpen( false );
				hamburger.focus();
			}
		} );
		document.addEventListener( 'click', function ( event ) {
			if ( ! mobileMenu.contains( event.target ) && ! hamburger.contains( event.target ) ) {
				setMenuOpen( false );
			}
		} );
	}

	/**
	 * 0b) LISTA / MATÉRIA: "Copiar link" e ordenação automática.
	 */
	document.addEventListener( 'click', function ( event ) {
		var copy = event.target.closest( '[data-copy-link]' );
		if ( ! copy || ! navigator.clipboard ) {
			return;
		}
		navigator.clipboard.writeText( copy.getAttribute( 'data-copy-link' ) ).then( function () {
			var label = copy.querySelector( 'span' );
			var original = label.textContent;
			label.textContent = copy.getAttribute( 'data-copied-label' );
			copy.classList.add( 'is-copied' );
			setTimeout( function () {
				label.textContent = original;
				copy.classList.remove( 'is-copied' );
			}, 2000 );
		} );
	} );
	document.querySelectorAll( 'select[data-autosubmit]' ).forEach( function ( select ) {
		select.addEventListener( 'change', function () {
			select.form.submit();
		} );
	} );

	if ( typeof Vue === 'undefined' ) {
		return;
	}

	var data = window.vbData || {
		restUrl: '/wp-json/wp/v2',
		nonce: '',
		i18n: {},
	};

	var createApp = Vue.createApp;

	/**
	 * 1) HEADER: menu mobile + alternância de modo escuro/claro.
	 */
	var headerEl = document.getElementById( 'masthead' );
	if ( headerEl ) {
		createApp( {
			data: function () {
				return {
					vbData: data,
					menuOpen: false,
					darkMode: document.documentElement.getAttribute( 'data-theme' ) === 'dark',
				};
			},
			methods: {
				toggleTheme: function () {
					this.darkMode = ! this.darkMode;
					var theme = this.darkMode ? 'dark' : 'light';
					document.documentElement.setAttribute( 'data-theme', theme );
					try {
						localStorage.setItem( 'vb-theme', theme );
					} catch ( e ) {
						/* localStorage indisponível: apenas ignora */
					}
				},
			},
			mounted: function () {
				// Fecha o menu mobile automaticamente ao clicar em um link.
				var links = this.$el.querySelectorAll( '.nav-wrapper a' );
				var self = this;
				links.forEach( function ( link ) {
					link.addEventListener( 'click', function () {
						self.menuOpen = false;
					} );
				} );
			},
		} ).mount( headerEl );
	}

	/**
	 * 2) BUSCA AO VIVO: consulta a REST API do WordPress enquanto o usuário digita.
	 */
	var searchEl = document.getElementById( 'vb-search' );
	if ( searchEl ) {
		createApp( {
			data: function () {
				return {
					vbData: data,
					query: searchEl.querySelector( 'input' ).value || '',
					results: [],
					loading: false,
					debounceTimer: null,
				};
			},
			methods: {
				onInput: function () {
					clearTimeout( this.debounceTimer );

					if ( this.query.trim().length < 2 ) {
						this.results = [];
						return;
					}

					this.loading = true;
					var self = this;

					this.debounceTimer = setTimeout( function () {
						self.fetchResults();
					}, 350 );
				},
				fetchResults: function () {
					var self = this;
					var url = data.restUrl + '/posts?search=' + encodeURIComponent( this.query ) + '&per_page=6&_fields=id,link,title';

					fetch( url )
						.then( function ( response ) {
							return response.ok ? response.json() : [];
						} )
						.then( function ( posts ) {
							self.results = posts;
						} )
						.catch( function () {
							self.results = [];
						} )
						.finally( function () {
							self.loading = false;
						} );
				},
			},
		} ).mount( searchEl );
	}

	/**
	 * 3) FEED DE POSTS (front-page.php): botão "Carregar mais" via REST API.
	 * A primeira página de posts já vem renderizada em PHP (SEO-friendly);
	 * o Vue só busca e acrescenta as páginas seguintes.
	 */
	var feedEl = document.getElementById( 'vb-feed' );
	if ( feedEl ) {
		var totalPages = parseInt( feedEl.getAttribute( 'data-total-pages' ), 10 ) || 1;

		createApp( {
			data: function () {
				return {
					vbData: data,
					page: 1,
					totalPages: totalPages,
					morePosts: [],
					loading: false,
				};
			},
			computed: {
				hasMore: function () {
					return this.page < this.totalPages;
				},
			},
			methods: {
				loadMore: function () {
					var self = this;
					this.loading = true;
					var nextPage = this.page + 1;
					var fields = 'id,link,title,excerpt,vb_thumbnail,vb_author_name';
					var url = data.restUrl + '/posts?page=' + nextPage + '&per_page=' + ( data.postsPerPage || 6 ) + '&_fields=' + fields;

					fetch( url )
						.then( function ( response ) {
							if ( ! response.ok ) {
								throw new Error( 'Falha ao carregar posts.' );
							}
							return response.json();
						} )
						.then( function ( posts ) {
							self.morePosts = self.morePosts.concat( posts );
							self.page = nextPage;
						} )
						.catch( function () {
							self.totalPages = self.page; // Interrompe tentativas futuras.
						} )
						.finally( function () {
							self.loading = false;
						} );
				},
			},
		} ).mount( feedEl );
	}

	/**
	 * 4) SEGURADO INLINE EDIT: auto-save + dynamic validation.
	 */
	var seguradoEl = document.querySelector( '.vb-segurado-edit' );
	if ( seguradoEl ) {
		var seguradoPostId = seguradoEl.getAttribute( 'data-segurado-post-id' );

		createApp( {
			data: function () {
				return {
					vbData: data,
					postId: seguradoPostId,
					editing: false,
					saving: false,
					saveSuccess: false,
					saveError: '',
					errors: {},
					fields: {
						full_name: '',
						document_type: '',
						document_number: '',
						email: '',
						phone: '',
						address_1: '',
						address_2: '',
						city: '',
						state: '',
						postal_code: '',
						country: ''
					}
				};
			},
			mounted: function () {
				var self = this;
				var definitions = seguradoEl.querySelectorAll( '.vb-segurado-definition' );

				definitions.forEach( function ( def ) {
					var field = def.getAttribute( 'data-segurado-field' );
					var value = def.getAttribute( 'data-segurado-value' );
					if ( field && self.fields.hasOwnProperty( field ) ) {
						self.fields[ field ] = value;
					}

					def.addEventListener( 'click', function () {
						self.startEdit( field, def );
					} );
				} );
			},
			methods: {
				startEdit: function ( field, element ) {
					if ( this.editing ) {
						return;
					}
					this.editing = true;
					element.classList.add( 'vb-segurado-editing' );
					element.setAttribute( 'contenteditable', 'true' );
					element.focus();

					var self = this;
					element.addEventListener( 'blur', function () {
						self.saveField( field, element );
					}, { once: true } );
				},
				saveField: function ( field, element ) {
					var self = this;
					var newValue = element.textContent.trim();
					this.fields[ field ] = newValue;
					element.classList.remove( 'vb-segurado-editing' );
					element.removeAttribute( 'contenteditable' );

					if ( ! this.validateField( field, newValue ) ) {
						this.editing = false;
						return;
					}

					this.saving = true;
					this.saveSuccess = false;
					this.saveError = '';

					var meta = {};
					meta[ 'vb_segurado_' + field ] = newValue;

					fetch( data.restUrl + '/posts/' + this.postId, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'X-WP-Nonce': data.nonce
						},
						body: JSON.stringify( { meta: meta } )
					} )
						.then( function ( response ) {
							if ( ! response.ok ) {
								throw new Error( 'REST request failed' );
							}
							return response.json();
						} )
						.then( function () {
							self.saveSuccess = true;
							self.editing = false;
							element.setAttribute( 'data-segurado-value', newValue );
						} )
						.catch( function () {
							self.saveError = data.i18n.seguradoSaveError || 'Erro ao salvar dados.';
							self.editing = false;
						} )
						.finally( function () {
							self.saving = false;
						} );
				},
				validateField: function ( field, value ) {
					this.errors = {};

					if ( 'full_name' === field && ! value ) {
						this.errors[ field ] = data.i18n.seguradoRequired || 'Campo obrigatório.';
						return false;
					}
					if ( 'email' === field && value && ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( value ) ) {
						this.errors[ field ] = data.i18n.seguradoInvalidEmail || 'E-mail inválido.';
						return false;
					}
					if ( 'document_number' === field && ! value ) {
						this.errors[ field ] = data.i18n.seguradoInvalidDoc || 'Número de documento inválido.';
						return false;
					}
					return true;
				}
			}
		} ).mount( seguradoEl );
	}
} )();
