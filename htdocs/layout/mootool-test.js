window.addEvent('domready', function(){
	Element.Events.extend({
		'wheelup': {
			type: Element.Events.mousewheel.type,
			map: function(event){
				event = new Event(event);
				if (event.wheel >= 0) this.fireEvent('wheelup', event)
			}
		},
	 
		'wheeldown': {
			type: Element.Events.mousewheel.type,
			map: function(event){
				event = new Event(event);
				if (event.wheel <= 0) this.fireEvent('wheeldown', event)
			}
		}
	});
	/* Color */
	var background = $('zijkolom').getStyle('background-color');
	var color = new Color(background).hsb;
	 
	$('zijkolom').addEvents({
		'wheelup': function(e) {
			e = new Event(e).stop();
	 
			var hue = color[0] + 5;
			if (hue > 360) {
				hue = 0;
			}
			color[0] = hue;
			document.getElementById('menu').setStyle('background-color', color.hsbToRgb().rgbToHex());
			this.setStyle('background-color', color.hsbToRgb().rgbToHex());
			
		},
	 
		'wheeldown': function(e) {
			e = new Event(e).stop();
	 
			var hue = color[0] - 5;
			if (hue < 0) {
				hue = 360;
			}
			color[0] = hue;
			document.getElementById('menu').setStyle('background-color', color.hsbToRgb().rgbToHex());
			this.setStyle('background-color', color.hsbToRgb().rgbToHex());
		}
	});
});
