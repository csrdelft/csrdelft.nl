{if LidInstellingen::get('zijbalk', 'forum_preview') == 'ja'}
	<div class="hoverIntentContent" style="margin-left: 175px; cursor: pointer;" onclick="location.href = '/forum/reactie/{$post->post_id}#{$post->post_id}';">
		<table id="forumtabel">
			<tbody>
				<tr>
					<td class="auteur">
						{$post->lid_id|csrnaam:'user':'pain'}:<br />
						<span class="moment">
							{if LoginSession::instelling('forum_datumWeergave') === 'relatief'}
								{$post->datum_tijd|reldate}
							{else}
								{$post->datum_tijd}
							{/if}
						</span>
					</td>
					<td class="bericht1">
						<div class="bericht">{$post->tekst|ubb|html_substr:"100":"…"}</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
{/if}