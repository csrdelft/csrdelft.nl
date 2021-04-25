---
layout: default
parent: Frontend
nav_order: 1
title: Styles
---

# Styles

_Zie ook [Themas maken](themas-maken.md)_

We gebruiken [scss](https://sass-lang.com/) voor de styles. Deze worden door webpack verwerkt naar css. Ieder .scss bestand zonder `_` aan het begin wordt door webpack opgepikt. Een scss bestand in een submap krijgt de volgende vorm:

```
thema/roze.scss -> thema-roze.css
```

Dit omdat webpack niet helemaal goed overweg kan met output in verschillende mappen.

## Bootstrap

Het basis framework is bootstrap (4.x). Je kan in views klassen uit bootstrap gebruiken. In `_defaults.scss` worden wat dingen uit bootstrap overgeschreven.

Je kan het bestand `_defaults.scss` inladen in [bootstrap.build](https://bootstrap.build/app) om te zien welke veranderingen wij aan Bootstrap hebben gemaakt.

## z-index
Bootstrap definieert z'n eigen z-indexes. Deze hebben we niet veranderd, alleen wat toegevoegd.

```scss
$zindex-zijbalk:           800; // Van ons
$zindex-dropdown:          1000 !default;
$zindex-sticky:            1020 !default;
$zindex-fixed:             1030 !default;
$zindex-modal-backdrop:    1040 !default;
$zindex-modal:             1050 !default;
$zindex-popover:           1060 !default;
$zindex-tooltip:           1070 !default;
```
