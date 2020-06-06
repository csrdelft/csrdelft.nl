# Styles

_Zie ook [Themas maken](themas-maken.md)_

We gebruiken [scss](https://sass-lang.com/) voor de styles. Deze worden door webpack verwerkt naar css. Ieder .scss bestand zonder `_` aan het begin wordt door webpack opgepikt. Een scss bestand in een submap krijgt de volgende vorm:

```
opmaak/roze.scss -> opmaak-roze.css
```

Dit omdat webpack niet helemaal goed overweg kan met output in verschillende mappen.

## Bootstrap

Het basis framework is bootstrap (4.0.0). Je kan in views klassen uit bootstrap gebruiken. In `_variables.scss` worden wat dingen uit bootstrap overgeschreven.

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
