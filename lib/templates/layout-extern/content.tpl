{include file='layout-extern/partials/_header.tpl'}
<!-- Banner -->
<section id="banner-small">
    <div class="inner">
        <a href="/"><img src="/assets/layout-extern/plaetjes/Logo.svg"></a>
    </div>
</section>

<!-- Wrapper -->
<section id="wrapper">
    <section class="wrapper spotlight detail style1">
        <div class="inner">
            <div class="content">
                {if isset($menutpl)}{include file="layout-extern/partials/_menu$menutpl.tpl"}{/if}
                {$body->view()}
            </div>
        </div>
    </section>
    <section id="footer">
        <div class="inner">
            <ul class="copyright">
                <li>&copy; 2016 - C.S.R. Delft</li>
            </ul>
        </div>
    </section>
</section>
{include file='layout-extern/partials/_footer.tpl'}
