<?php
/**
 * Formulário de busca — melhorado com Vue.js (busca ao vivo via REST API).
 * O <form> nativo garante que a busca funcione mesmo sem JavaScript.
 *
 * @package Vue_Blocks
 */
?>
<div id="vb-search" class="vb-search" v-cloak>
	<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<span class="vb-search-icon" aria-hidden="true">&#128269;</span>
		<label class="screen-reader-text" for="vb-search-input"><?php esc_html_e( 'Pesquisar por:', 'vue-blocks' ); ?></label>
		<input
			id="vb-search-input"
			type="search"
			name="s"
			:placeholder="vbData.i18n.searchPlaceholder"
			v-model="query"
			@input="onInput"
			autocomplete="off"
			value="<?php echo esc_attr( get_search_query() ); ?>"
		>
	</form>

	<div class="vb-search-results" v-if="query.length > 1">
		<p class="vb-search-loading" v-if="loading">{{ vbData.i18n.loading }}</p>
		<template v-else>
			<a v-for="result in results" :key="result.id" :href="result.link" v-html="result.title.rendered"></a>
			<p class="vb-search-empty" v-if="!results.length">{{ vbData.i18n.noResults }}</p>
		</template>
	</div>
</div>
