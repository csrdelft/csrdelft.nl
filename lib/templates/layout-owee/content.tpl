{include file='layout-owee/partials/_header.tpl'}
<!-- Banner -->
<section id="banner-small">
    <div class="inner">
        <a href="/"><img src="/images/logo-next-level.svg" height="140"></a>
    </div>
</section>

<!-- Wrapper -->
<section id="wrapper">
    <section class="wrapper detail kleur1">
        <div class="inner">
            <div class="content">
                {if isset($menutpl)}{include file="layout-owee/partials/_menu$menutpl.tpl"}{/if}
                {$body->view()}
            </div>
        </div>
    </section>
    <section id="footer">
        <div class="inner">
            <ul class="copyright">
                <li>&copy; {date('Y')} - C.S.R. Delft - <a href="/download/Privacyverklaring%20C.S.R.%20Delft%20-%20Extern%20-%2025-05-2018.pdf">Privacy</a></li>
            </ul>
        </div>
    </section>
</section>
{include file='layout-owee/partials/_footer.tpl'}
