

        <?php $this->helper->tplSaveButton() ?>
    </form>
    
    <?php $helper = plugin_load('helper', 'confmanager'); ?>
    <div class="popup_mask"></div>
    <div class="popup">
		<h3 class="popupheader">File Upload</h3>
		<div class="popupcontent" id="popup_select_file">
			<form id="fileuploadform" enctype="multipart/form-data" method="POST" action="<?php echo DOKU_BASE.'lib/exe/ajax.php' ?>">
				<div class="popupprompt"><?php echo $helper->getLang('file_upload_prompt') ?></div>
				<input type="file" name="icon" id="file_upload_input" />
				<br/>
				<br/>
				<input type="submit" class="button saveButton right" value="<?php echo $helper->getLang('upload') ?>" />
				<span class="right spacer"></span>
				<input id="popup_cancel" type="submit" class="right"  value="<?php echo $helper->getLang('cancel') ?>" />
				<input type="hidden" name="call" value="confmanager_upload" />
				<input type="hidden" name="configId" id="configIdParam" />
				<input type="hidden" name="key" id="keyParam" />
			</form>
		</div>
		<div class="popupcontent" id="popup_show_progress" >
			<p><?php echo $helper->getLang('uploading') ?></p>
			<div class="progress">
		        <div class="bar"></div>
		        <div class="percent">0%</div>
	        </div>
        </div>
        <div class="popupcontent" id="popup_success">
        	<h3><?php echo $helper->getLang('upload_success') ?></h3>
        	<a class="button continue right"><?php echo $helper->getLang('continue') ?></a>
        </div>
        <div class="popupcontent" id="popup_error">
        	<h3><?php echo $helper->getLang('upload_error') ?></h3>
        	<a class="button continue right"><?php echo $helper->getLang('continue') ?></a>
        </div>
	</div>
	
</div>
