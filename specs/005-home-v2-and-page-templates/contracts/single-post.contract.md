# Single Post Contract: Home-Page Sections v2 & Page Templates

**Date**: 2026-10-06
**Spec**: `specs/005-home-v2-and-page-templates/spec.md`
**Branch**: `005-home-v2-and-page-templates`

Defines the structure of `theme/single.php` per Clarification Q2
(full editorial layout).

## Required structure

`theme/single.php` MUST emit the following in source order:

```
<article id="post-{ID}" class="post type-post status-{status}">
  <header class="post-hero">
    {featured image — vb-card size or placeholder}
    <div class="post-meta">
      {category chip}
      {byline: "por {author} · {date}"}
    </div>
    <h1 class="post-title">{post title in Merriweather}</h1>
  </header>

  <div class="entry-content">
    {the_content()}
  </div>

  <footer class="post-footer">
    {taxonomy links if any}
  </footer>
</article>

<section class="related-posts">
  <h2>Leia também</h2>
  <div class="related-grid">
    {up to 3 related posts — see data-model E3}
  </div>
</section>
```

## Composition

`theme/single.php` uses `get_header()` and `get_footer()` to wrap
the article in the existing Safe Mídia navbar / footer.

## Required typography

- The article title (`.post-title`) MUST use `font-family: var(--font-serif)`
  (Merriweather) per FR-002.
- The article body (`.entry-content`) MUST use
  `font-family: var(--font-sans)` (Inter) per FR-002.
- The byline (`.post-meta`) uses the Safe Mídia meta-bar pattern.

## Validation

Per spec SC-002: a published post's permalink loads with the Safe
Mídia typography (the title's computed `font-family` starts with
`'Merriweather'`; the body's starts with `'Inter'`), the featured
image at the top, byline + category chip, and a related-posts rail.

Verification:

```sh
curl -s http://localhost:8080/?p=1 > /tmp/single.html
# Verify the title is in the post-hero and uses Merriweather
grep -q 'class="post-hero"' /tmp/single.html
# Verify related posts rail exists
grep -q 'class="related-posts"' /tmp/single.html
```