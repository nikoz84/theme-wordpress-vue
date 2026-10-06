# Layouts Inventory

This document inventories the static HTML layout references in
`layouts-html/` at the repository root (one directory above this
theme). Each entry below lists the Safe Mídia visual sections present
in the reference HTML and the layout's disposition for v1 of the
Vue Blocks theme.

The home page (`01 - Home`) is the only layout that informs the v1
deliverable directly — its four core sections (navbar, hero, news
grid, footer) are implemented as `theme/template-parts/` files.
The other layouts are deferred and serve as visual reference for
future work.

> **Disambiguation**: the layout HTMLs at `layouts-html/` are
> design-only artifacts and are gitignored from the published
> theme. This inventory ships with the theme (per FR-007) so
> downstream maintainers can see what designs informed the
> shipped components.

## Inventory

| # | Title | v1 status | Informs | Safe Mídia sections in the HTML |
|---|---|---|---|---|
| 01 | Home | **in scope (v1)** | `theme/front-page.php`, `theme/template-parts/header/navbar.php`, `theme/template-parts/hero/hero.php`, `theme/template-parts/news/news-grid.php`, `theme/template-parts/news/news-card.php`, `theme/template-parts/footer/site-footer.php` | Navbar, Hero (featured + sidebar posts), News grid (3-column), "Para o Segurado" rail, Newsletter compact, Newsletter grande, "Análise de Mercado", Category tabs, "Mais Lidas da Semana", "Boletim Regulatório", Colunistas, Footer (logo + columns + newsletter form + copyright). v1 ships only navbar, hero, news grid, footer. |
| 02 | Listagem das notícias | deferred (v2+) | future `theme/archive.php` / `theme/category.php` | News listing grid (3-column) with featured, sidebar ad, pagination. |
| 03 | Interna das notícias | deferred (v2+) | future `theme/single.php` | Single article with featured image, body content, related posts, comments. |
| 04 | Listagem para o Segurado | reference only | not planned | Segmented insurance-buyer-facing listing. Distinct from the news listing; would need a custom post type or taxonomy. |
| 05 | Contato | deferred (v2+) | future `theme/page-contato.php` | Contact form, address, social links. |
| 06 | Quem somos | deferred (v2+) | future `theme/page-quem-somos.php` | Static-page layout with text + image blocks. |
| 07 | Política de privacidade | reference only | (not implemented; WordPress's default privacy page handles this) | Empty directory; placeholder. |
| 08 | Glossário | reference only | not planned (static-page) | Glossary index with letter-grouped entries. |
| 09 | Termos de Uso | reference only | (not implemented; WordPress's default terms-of-use page handles this) | Empty directory; placeholder. |

## What v1 ships

Per `specs/003-design-system-foundation/spec.md`'s clarification Q3,
v1 ships only the **four core sections** from the home layout:

1. **Navbar** — sticky header with logo, primary nav, socials,
   hamburger toggle (mobile), and subscribe CTA.
2. **Hero** — featured sticky post (else most-recent) on the left,
   4-post sidebar on the right.
3. **News grid** — 3-column grid of the next 6 most-recent posts after
   the hero.
4. **Footer** — logo + columns of links + newsletter form +
   copyright.

The other sections visible in `layouts-html/01 - Home/01 -
safemidia-home-fixed.html` (newsletter compact, "Para o Segurado",
"Boletim Regulatório", "Colunistas", "Análise de Mercado",
category tabs, "Mais Lidas da Semana", newsletter grande) are
**explicitly out of scope** for v1 and are deferred to a v2 follow-up
once the four core sections are ratified.

## Where the visual tokens come from

Every visual value in `theme/style.css` is declared as a CSS custom
property at `:root`, transcribed from the Safe Mídia reference. The
human-readable catalog of those tokens is `../DESIGN.md` at the
repository root (duplicated as `DESIGN.md` next to this file inside
the packaged theme).

## See also

- `../DESIGN.md` (this directory) — full token catalog + component
  catalog.
- `../specs/003-design-system-foundation/spec.md` — the design-system
  feature spec.
- `../specs/004-theme-customizer-and-branding/spec.md` — the
  branding + Customizer feature spec.