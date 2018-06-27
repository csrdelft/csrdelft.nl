import $ from 'jquery';

/*
 * bb-slideshow
 */
let slideshowSpeed=7000;

$(function() {
	let play, $active;

	//Set Default State of each portfolio piece
	$('.paging').show();
	$('.paging a:first').addClass('active');

	//Get size of images, how many there are, then determine the size of the image reel.
	let imageWidth = $('.bb-slideshow').width();
	let imageSum = $('.image_reel img').size();
	let imageReelWidth = imageWidth * imageSum;

	//Adjust the image reel to its new size
	$('.image_reel').css({'width' : imageReelWidth});

	//Paging + Slider Function
	function rotate(){
		let triggerID = $active.attr('rel') - 1; //Get number of times to slide
		let imageReelPosition = triggerID * imageWidth; //Determines the distance the image reel needs to slide

		$('.paging a').removeClass('active'); //Remove all active class
		$active.addClass('active'); //Add active class (the $active is declared in the rotateSwitch function)

		//Slider Animation
		$('.image_reel').animate({
			left: -imageReelPosition
		}, 500 );
	}

	//Rotation + Timing Event
	function rotateSwitch(){
		play = setInterval(() => { //Set timer - this will repeat itself every 3 seconds
			$active = $('.paging a.active').next();
			if ( $active.length === 0) { //If paging reaches the end...
				$active = $('.paging a:first'); //go back to first
			}
			rotate(); //Trigger the paging and slider function
		}, slideshowSpeed); //Timer speed in milliseconds (3 seconds)
	}

	rotateSwitch(); //Run function on launch

	//On Hover
	$('.image_reel a').hover(() => {
		clearInterval(play); //Stop the rotation
	}, () => {
		rotateSwitch(); //Resume rotation
	});

	//On Click
	$('.paging a').click(function() {
		$active = $(this); //Activate the clicked paging
		//Reset Timer
		clearInterval(play); //Stop the rotation
		rotate(); //Trigger rotation immediately
		rotateSwitch(); // Resume rotation
		return false; //Prevent browser jump to link anchor
	});

});
