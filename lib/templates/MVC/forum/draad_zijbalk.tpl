{strip}
	<div class="item">
		<div style="display: inline-block;">
			{assign var=timestamp value=strtotime($draad->laatst_gewijzigd)}
			<div id="cp{$post->post_id}" class="hoverdivcontent" style="cursor: pointer;" onclick="location.href = '/forum/reactie/{$post->post_id}#{$post->post_id}';">
				<table id="forumtabel">
					<tbody>
						<tr>
							<td class="auteur">
								{$post->lid_id|csrnaam:'user':'pain'}:<br />
								<span class="moment" style="color: #bbb;">
									{if LoginLid::instelling('forum_datumWeergave') === 'relatief'}
										{$post->datum_tijd|reldate}
									{else}
										{$post->datum_tijd}
									{/if}
								</span>
							</td>
							<td class="bericht1">
								<div class="bericht">{$post->tekst|ubb|html_substr:"300":"…"}</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<span id="dp{$post->post_id}" class="hoverdiv">
				{if date('d-m', $timestamp) === date('d-m')}
					{$timestamp|date_format:"%H:%M"}
				{elseif strftime('%U', $timestamp) === strftime('%U')}
					<div class="zijbalk-dag">{$timestamp|date_format:"%a"}&nbsp;</div>{$timestamp|date_format:"%d"}
				{else}
					{$timestamp|date_format:"%d-%m"}
				{/if}
				&nbsp;
				<a id="{$draad->draad_id}" href="/forum/onderwerp/{$draad->draad_id}{if LidInstellingen::get('forum', 'openDraadPagina') == 'ongelezen'}#ongelezen{elseif LidInstellingen::get('forum', 'openDraadPagina') == 'laatste'}#reageren{/if}" title="{$draad->titel}"{if !$draad->alGelezen()} class="opvallend"{/if}>
					{$draad->titel|truncate:25:"…":true}
				</a>
			</span>
		</div>
	</div>
{/strip}