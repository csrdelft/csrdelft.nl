</main>

<?php
$wiki->getBody()->view();

if (isset($modal)) {
	$display = ' style="display:block;"';
}
echo '<div id="modal-background"' . $display . '></div>';

if (isset($modal)) {
	$modal->view();
} else {
	echo '<div id="modal" class="modal-content outer-shadow dragobject" tabindex="-1"></div>';
}
echo '</div>';
