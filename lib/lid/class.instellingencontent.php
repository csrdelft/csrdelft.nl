<?php
/*
 * class.instellingencontent.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 * Instellingenketzerding.
 */

class InstellingenContent extends SimpleHTML{

	public function view(){
		$instellingen=Instelling::getDefaults();
		echo '<h1>Instelling csrdelft.nl</h1>Standaarwaarden tussen haakjes.<form method="post">';
		$current='';
		foreach($instellingen as $key =>  $inst){
			$parts=explode('_', $key);
			if($parts[0]!=$current){
				if($current!=''){ echo '</fieldset><br />'; }
				echo '<fieldset style="padding: 5px 10px;">';
				$current=$parts[0];
				echo '<legend><strong>'.ucfirst($current).'</strong></legend>';
			}
	
			echo '<label style="float: left; width: 200px;" for="'.$key.'">'.$parts[1].'</label>';
			if(is_array(Instelling::getEnumOptions($key))){
				echo '<select type="select" name="'.$key.'">';
				foreach(Instelling::getEnumOptions($key) as $option){
					echo '<option value="'.$option.'" ';
					if($option==Instelling::get($key)){
						echo 'selected="selected"';
					}
					echo '>'.ucfirst($option).'</option>';
				}
				echo '</select>';
			}else{
				echo ' <input type="text" name="'.$key.'" value="'.Instelling::get($key).'" />';
			}
			echo ' ('.ucfirst($inst).')<br /><br />';
		}
		echo '</fieldset><br />';
		echo 'Als de instellingen worden opgeslagen in de sessie, worden ze niet in het profiel opgeslagen en zij ze na in en uitloggen verdwenen.<br />';
		echo '<input type="submit" name="save_session" value="opslaan in sessie"> <input type="submit" name="save" value="opslaan in profiel"></form>';
	
	}
}
