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
} )();
