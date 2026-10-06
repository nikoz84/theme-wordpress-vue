=== Vue Blocks ===
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Tema WordPress clássico (PHP + hierarquia de templates) com Vue.js 3 cuidando
da camada de interação do frontend.

== Descrição ==

Este tema segue a estrutura descrita na documentação oficial do WordPress
(https://developer.wordpress.org/themes/core-concepts/):

* style.css      -> registra o tema (cabeçalho obrigatório) e contém o CSS.
* functions.php  -> theme setup, enqueue de scripts/estilos, menus, widgets.
* index.php      -> template obrigatório / fallback da hierarquia.
* front-page.php, single.php, page.php, archive.php, search.php, 404.php
                 -> templates específicos da hierarquia de templates.
* template-parts/ -> partes reutilizáveis do Loop (get_template_part()).
* inc/            -> funções auxiliares (template tags).

Todo o HTML é renderizado pelo PHP (Server Side), garantindo SEO e
funcionamento mesmo sem JavaScript. O Vue.js 3 (via CDN, build UMD com
compilador embutido) é carregado por wp_enqueue_script() em functions.php
e "liga" a interatividade por cima do HTML já existente (técnica de
"in-DOM template" / progressive enhancement), sem precisar de Node.js,
Webpack ou build step.

== Onde o Vue entra ==

1. Cabeçalho (header.php + assets/js/app.js)
   - Menu mobile (abrir/fechar) com transição.
   - Alternância de modo claro/escuro persistida em localStorage.

2. Busca ao vivo (searchform.php)
   - Enquanto o usuário digita, o Vue consulta a REST API do WordPress
     (/wp-json/wp/v2/posts?search=) e mostra sugestões em um dropdown.
   - O <form> continua funcionando normalmente sem JS (fallback real).

3. Feed de posts da home (front-page.php)
   - Os primeiros posts são renderizados em PHP (WP_Query), bom para SEO.
   - O botão "Carregar mais posts" é do Vue: busca as páginas seguintes
     via REST API (/wp-json/wp/v2/posts) e acrescenta ao grid sem recarregar
     a página.

Os textos, a URL da REST API e o nonce de segurança são passados do PHP
para o Vue através de wp_localize_script() (variável global `vbData`).

== Instalação ==

1. Compacte a pasta `vue-blocks` (esta pasta) em um arquivo .zip
   OU copie a pasta inteira para `wp-content/themes/`.
2. No painel do WordPress, acesse Aparência > Temas.
3. Ative o tema "Vue Blocks".
4. Em Aparência > Menus, crie e associe um menu ao local "Menu Principal".
5. (Opcional) Adicione um screenshot.png (1200x900) na raiz do tema para
   a miniatura na tela de temas.

== Personalização ==

* Cores, tipografia e espaçamentos: edite as variáveis CSS em `:root`
  no topo de style.css.
* Comportamento do Vue: edite assets/js/app.js.
* Novos campos na REST API para o Vue consumir: veja
  `vb_register_rest_fields()` em functions.php.

== Licença ==

Este tema é distribuído sob a licença GPLv2 (ou posterior), a mesma do
WordPress. O Vue.js é licenciado sob MIT e carregado via CDN pública
(unpkg.com).

== Build opcional (Vite) ==

Por padrão, o tema carrega o Vue via CDN pública e o `assets/js/app.js`
diretamente — zero build step, drop-in para qualquer host WordPress.

Existe um caminho **opt-in** baseado em Vite (Constitution v1.1.0,
Principle V) que produz um bundle JS e um CSS compilado em `dist/`:

```bash
npm install     # instala Vite e plugins
npm run build   # emite dist/assets/app.[hash].js + style.[hash].css + dist/manifest.json
npm run dev     # build incremental (watch)
npm run clean   # rm -rf dist
```

Para **ativar** os artefatos do build, edite `functions.php` e
ajuste a constante `VB_USE_BUNDLED_ASSETS` para `true`. Quando
`false` (ou não definida), o enqueue padrão CDN-first é usado —
o build estar instalado **não** ativa o switch automaticamente.

Quando o switch está ligado, `functions.php` lê `dist/manifest.json`
e enfileira os arquivos com hash. Se o manifesto estiver ausente ou
ilegível, a falha é ruidosa (sem fallback silencioso para o caminho
CDN-first) — verifique `WP_DEBUG` para ver a stack traceante.

A Constituição do projeto (`.specify/memory/constitution.md`) deixa
claro que o `dist/` é `.gitignore`-d; um host que tem apenas os
arquivos versionados do tema continua recebendo um site funcional.

== Design system ==

O design system deste tema (tokens, componentes, breakpoints) é
documentado em `DESIGN.md` (incluído neste arquivo). O `:root`
de `style.css` é a fonte canônica dos valores; `DESIGN.md` espelha
essa tabela. O design visual da home page é inspirado no Safe Mídia
(veja o HTML de referência em `layouts-html/01 - Home/` no repo).

== Empacotamento ==

```bash
bash package.sh   # produz vue-blocks-<version>.zip no diretório pai
```

O script refresca `DESIGN.md` a partir da cópia canônica (no repo
root), constrói o zip excluindo caminhos de test harness
(`docker-compose.yml`, `bin/`, `seed/`, `config/`, `node_modules/`,
`dist/`, etc.) e verifica com fuga grep que o conteúdo está limpo.
O zip resultante sobe diretamente para qualquer site WordPress
6.0+ / PHP 7.4+ em `wp-content/themes/`.
