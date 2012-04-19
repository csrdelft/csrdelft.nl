jQuery(document).ready(function($){
	$('#forumBericht').each(function(){
		$(this).wrap('<div id="meldingen"></div>');
		
		if($(this).hasClass('extern')){
			$('#meldingen').prepend('<div id="extern_melding"><strong>Openbaar forum</strong><br />Voor iedereen leesbaar, doorzoekbaar door zoekmachines.</div>');
		}
	}).keyup(function(event){
		var textarea=$(this);
		
		if(event.keyCode==13){ //enter == 13
			if(/\[.*\]/.test(textarea.val())){
				//detected ubb tag use, trigger preview and display message.
				previewPost('forumBericht', 'berichtPreview');

				if($('#ubb_melding').length==0){
					textarea.before('<div id="ubb_melding">UBB gevonden:<br /> controleer het voorbeeld.</div>');
					
					$('#ubb_melding').click(function(){
						$('#ubbhulpverhaal').toggle();
					});
				}
			}
		}
		if($('#ketzer_melding').length==0 && /ketzer/.test(textarea.val())){
			textarea.before('<div id="ketzer_melding">Ketzer hebben?<br /><a href="/actueel/groepen/Ketzers" target="_blank">&raquo; Maak er zelf een aan.</a></div>');
		}
	});
	
	$('.togglePasfoto').each(function(){
		$(this).attr('title', 'Toon pasfoto van dit lid');
		var postid=$(this).attr('id').substr(1).split('-')[1];
		var pasfoto=$('#p'+postid);
		if(pasfoto.html()!=''){
			pasfoto.toggleClass('verborgen');
			$(this).html('v');
		}
	});
	$('.togglePasfoto').click(function(){
		var parts=$(this).attr('id').substr(1).split('-');
		var pasfoto=$('#p'+parts[1]);

		if(pasfoto.html()==''){
			pasfoto.html('<img src="/tools/pasfoto/'+parts[0]+'.png" class="lidfoto" />');
		}
		if(!pasfoto.hasClass('verborgen')){
			$(this).html("&raquo;");
		}else{
			$(this).html('v');
		}
		pasfoto.toggleClass('verborgen');
	});
});
