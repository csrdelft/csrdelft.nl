<?php
/*
 * class.instellingencontent.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 * Instellingenketzerding.
 */

class InstellingenContent extends SimpleHTML{
	public function getTitel(){
		return 'Websiteinstellingen';
	}
	public function view(){
		$instellingen=Instelling::getDefaults();
		echo '<h1>Instelling csrdelft.nl</h1>Standaardwaarden tussen haakjes.<form method="post">';
		$current='';
		foreach($instellingen as $key =>  $inst){
			$parts=explode('_', $key);
			//om tot 1 april de roze optie te verbergen
			if($parts[0]=='layout'){ continue; }
			if($parts[0]!=$current){
				if($current!=''){ echo '</fieldset><br />'; }
				echo '<fieldset style="padding: 5px 10px;">';
				$current=$parts[0];
				echo '<legend><strong>'.ucfirst($current).'</strong></legend>';
			}
	
			echo '<label style="float: left; width: 250px;" for="inst_'.$key.'">'.Instelling::getDescription($key).'</label>';
			if(is_array(Instelling::getEnumOptions($key))){
				echo '<select type="select" id="inst_'.$key.'" name="'.$key.'">';
				foreach(Instelling::getEnumOptions($key) as $option){
					echo '<option value="'.$option.'" ';
					if($option==Instelling::get($key)){
						echo 'selected="selected"';
					}
					echo '>'.ucfirst($option).'</option>';
				}
				echo '</select>';
			}else{
				echo ' <input type="text" id="inst_'.$key.'" name="'.$key.'" value="'.Instelling::get($key).'" />';
			}
			echo ' ('.ucfirst($inst).')<br /><br />';
		}
		echo '</fieldset><br />';
		if(LoginLid::instance()->hasPermission('P_ADMIN')){
			echo '<input type="submit" name="save_session" value="tijdelijk opslaan in sessie"> ';
		}
		echo '<input type="submit" name="save" value="opslaan"></form>';
	
	}
}
