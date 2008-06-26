<?php
class VBDiscussionSource extends VBSource
{
	public static function getEditDiv()
	{
		return VBSource::generateEditFields("<img src='images/discussion.png'/>Forum discussie bewerken",'discussion',
			"<input type='hidden' name='link' value='-1'/>");
	}
}
?>