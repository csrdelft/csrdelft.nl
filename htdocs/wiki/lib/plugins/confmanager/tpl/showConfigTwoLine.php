<?php $helper = plugin_load('helper', 'confmanager'); ?>
<div class="table">
	<h3><?php echo $helper->getLang('user_defined_values') ?></h3>
    <table class="inline confmanager_twoLine">
        <tr>
            <th><?php echo $helper->getLang('key') ?></th>
            <th><?php echo $helper->getLang('value') ?></th>
            <th><?php echo $helper->getLang('actions') ?></th>
        </tr>
        <?php foreach ($local as $key => $value): ?>
            <tr>
                <td>
                	<input type="text" name="keys[]" id="key<?php echo $configCounter ?>" value="<?php echo hsc($key) ?>">
                </td>
                <td>
                    <input
                    	id="value<?php echo $configCounter ?>"
                        type="text"
                        name="values[<?php echo hsc($key) ?>]"
                        value="<?php echo hsc($value) ?>"
                        class="edit"
                        />
                </td>
                <td>
                    <?php include DOKU_PLUGIN . 'confmanager/tpl/deleteButton.php' ?>
                </td>
            </tr>
        <?php endforeach ?>
        <tr>
            <td>
                <input class="newItem" type="text" name="newKey[]">
            </td>
            <td>
                <input class="newItem submitOnTab" type="text" name="newValue[]" />
            </td>
            <td/>
        </tr>
    </table>
    <?php $this->helper->tplSaveButton() ?>
    <h3 class="clickable hoverFeedback" title="<?php echo $helper->getLang('toggle_defaults') ?>">
		<a id="toggleDefaults">
			<?php echo $helper->getLang('default_values') ?>
			<img id="defaults_toggle_button"/>
		</a>
	</h3>
	<div class="defaults">
		<p>
			<?php echo hsc($helper->getLang('defaults_description')) ?>
		</p>
		<table class="inline confmanager_twoLine">
			<tr>
				<th><?php echo $helper->getLang('key') ?></th>
	            <th><?php echo $helper->getLang('value') ?></th>
			</tr>
			<?php foreach($default as $key => $value): ?>
				<tr>
					<td class="defaultValue">
						<?php echo hsc($key); ?>
					</td>
					<td class="defaultValue">
						<?php echo hsc($value); ?>
					</td>
				</tr>
			<?php endforeach ?>
		</table>
	</div>
</div>