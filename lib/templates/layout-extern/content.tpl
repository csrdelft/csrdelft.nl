{include file='layout-extern/partials/_header.tpl'}
    <!-- Banner -->
    <section id="banner-small">
        <div class="inner">
            <a href="/"><img src="/plaetjes/layout-extern/Logo.svg"></a>
        </div>
    </section>

    <!-- Wrapper -->
    <section id="wrapper">
        <section class="wrapper spotlight detail style1">
            <div class="inner">
                {if isset($menutpl)}{include file="layout-extern/partials/_menu$menutpl.tpl"}{/if}
                {$body->view()}
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
