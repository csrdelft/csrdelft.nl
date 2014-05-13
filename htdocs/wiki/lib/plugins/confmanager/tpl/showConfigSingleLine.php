<?php $helper = plugin_load('helper', 'confmanager'); ?>
<div class="table">
	<h3><?php echo $helper->getLang('user_defined_values') ?></h3>
	<table class="inline confmanager_singleLine" id="local">
        <tr>
            <th><?php echo $helper->getLang('value') ?></th>
            <th><?php echo $helper->getLang('actions') ?></th>
        </tr>
        <?php $lineCounter = 0; ?>
        <?php foreach ($local as $config): ?>
            <?php
            $defaultValue = false;
            if (in_array($config, $default)) {
                $defaultValue = true;
            }
            ?>
            <tr>
                <td>
                <input
                		id="value<?php echo $lineCounter ?>"
                        type="text"
                        name="line[]"
                        value="<?php echo hsc($config) ?>"
                        class="<?php echo $class ?>"
 				/>
                </td>
                <td>
                    <?php include DOKU_PLUGIN . 'confmanager/tpl/deleteButton.php' ?>
                </td>
            </tr>
            <?php $lineCounter++; ?>
        <?php endforeach ?>
        <tr>
            <td>
                <input type="text" name="line[]" class="newItem submitOnTab" />
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
		<table class="inline confmanager_singleLine">
			<tr>
				<th><?php echo $helper->getLang('value') ?></th>
	            <th><?php echo $helper->getLang('actions') ?></th>
			</tr>
			<?php foreach($default as $item): ?>
				<tr>
					<td>
						<div class="defaultValue" title="<?php echo hsc($helper->getLang('default_value_tooltip')) ?>">
	                    <?php echo hsc($item) ?>
	                    </div>
					</td>
					<td>
						<img src="<?php echo DOKU_PLUGIN_ICONS.'delete_disabled.png' ?>"
	                        alt="<?php echo hsc($helper->getLang('delete_action')) ?>"
	                        title="<?php echo hsc($helper->getLang('delete_action_tooltip_disabled')) ?>" />
	                </td>
				</tr>
			<?php endforeach ?>
		</table>
	</div>
</div>
