<?php global $ID; ?>
<h1><?php echo $this->helper->getLang('welcomehead') ?></h1>
<noscript>
	<div class="noscript">
		<h2><?php echo $this->helper->getLang('no_script_title')?></h2>
		<p>
			<?php echo $this->helper->getLang('no_script_message')?>
		</p>
	</div>
</noscript>
<div class="level1">
    <p>
        <?php echo $this->helper->getLang('welcome') ?>
    </p>
    <form action="<?php echo wl($ID, 'do=admin,page=confmanager') ?>" method="get" id="select_config_form">
        <input type="hidden" name="do" value="admin" />
        <input type="hidden" name="page" value="confmanager" />
        <label for="confmanager__config__files">
            <?php echo $this->helper->getLang('select_config') ?>
        </label>
        <select name="configFile" id="confmanager__config__files" class="edit">
            <option>
            	<?php echo $this->helper->getLang('please_select') ?>
            </option>
            <?php foreach ($configFiles as $config): ?>
            <?php $id = $this->helper->getConfigId($config) ?>
            <option value="<?php echo hsc($id) ?>"
                <?php if ($default === $id): ?>selected="selected"<?php endif ?>>
                <?php echo hsc($config->getName()) ?>
            </option>
            <?php endforeach ?>
        </select>
        <noscript>
        	<input type="submit" value="<?php echo $this->helper->getLang('edit') ?>" class="button" />
        </noscript>
    </form>
</div>
