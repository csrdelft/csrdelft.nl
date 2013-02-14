<?php global $ID; ?>
<div class="level2">
    <form action="<?php echo wl($ID, 'do=admin,page=confmanager') ?>" method="post" enctype="multipart/form-data" id="configForm" >
        <input type="hidden" name="do" value="admin" />
        <input type="hidden" name="page" value="confmanager" />
        <input type="hidden" name="configFile" value="<?php echo hsc($id) ?>" />
