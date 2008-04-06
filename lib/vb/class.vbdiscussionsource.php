<?php
class VBDiscussionSource extends VBSource
{
	public static function getEditDiv()
	{
		return VBSource::generateEditFields('discussion',
			"<input type='hidden' name='link' value='-1'/>");
	}
}
?>