{* Het Topmost block *}
<div id="mededelingen-top3block" class="my-3 p-3 bg-white rounded shadow-sm">
    {foreach from=$topmost item=mededeling}
			<div class="media pt-3">
				<a href="/mededelingen/{$mededeling->id}">
					<img class="mr-3 mb-3 rounded" src="/plaetjes/mededelingen/{$mededeling->plaatje}" width="70px" height="70px"
							 alt="{$mededeling->plaatje|escape:'html'}"/>
				</a>
				<div class="media-body pb-3 mb-0 lh-125 border-bottom border-gray">
					<h6><a href="/mededelingen/{$mededeling->id}">{$mededeling->titel|bbcode|html_substr:"90":"…"}</a></h6>
					<div class="">
              {$mededeling->tekst|bbcode|html_substr:"250":"…"}
						<small class="float-right"><a href="/mededelingen/{$mededeling->id}">Verder lezen »</a></small>
					</div>
					<div class="clear"></div>
				</div>
			</div>
    {/foreach}
	<div class="text-right mt-3"><a href="/mededelingen">Alle mededelingen</a></div>
</div>
