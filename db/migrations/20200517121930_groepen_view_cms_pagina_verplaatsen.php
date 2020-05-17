<?php

use Phinx\Migration\AbstractMigration;

class GroepenViewCmsPaginaVerplaatsen extends AbstractMigration
{
	public function up() {
		$this->query('UPDATE cms_paginas SET naam = \'groepsbeschrijving_Activiteiten\' WHERE naam = \'Activiteiten\'');
		$this->query('UPDATE cms_paginas SET naam = \'groepsbeschrijving_Besturen\' WHERE naam = \'Besturen\'');
		$this->query('UPDATE cms_paginas SET naam = \'groepsbeschrijving_Commissies\' WHERE naam = \'Commissies\'');
		$this->query('UPDATE cms_paginas SET naam = \'groepsbeschrijving_Ketzers\' WHERE naam = \'Ketzers\'');
		$this->query('UPDATE cms_paginas SET naam = \'groepsbeschrijving_Lichtingen\' WHERE naam = \'Lichtingen\'');
		$this->query('UPDATE cms_paginas SET naam = \'groepsbeschrijving_Onderverenigingen\' WHERE naam = \'Onderverenigingen\'');
		$this->query('UPDATE cms_paginas SET naam = \'groepsbeschrijving_RechtenGroepen\' WHERE naam = \'RechtenGroepen\'');
		$this->query('UPDATE cms_paginas SET naam = \'groepsbeschrijving_Verticalen\' WHERE naam = \'Verticalen\'');
		$this->query('UPDATE cms_paginas SET naam = \'groepsbeschrijving_Werkgroepen\' WHERE naam = \'Werkgroepen\'');
		$this->query('UPDATE cms_paginas SET naam = \'groepsbeschrijving_Woonoorden\' WHERE naam = \'Woonoorden\'');
	}

	public function down() {
		$this->query('UPDATE cms_paginas SET naam = \'Activiteiten\' WHERE naam = \'groepsbeschrijving_Activiteiten\'');
		$this->query('UPDATE cms_paginas SET naam = \'Besturen\' WHERE naam = \'groepsbeschrijving_Besturen\'');
		$this->query('UPDATE cms_paginas SET naam = \'Commissies\' WHERE naam = \'groepsbeschrijving_Commissies\'');
		$this->query('UPDATE cms_paginas SET naam = \'Ketzers\' WHERE naam = \'groepsbeschrijving_Ketzers\'');
		$this->query('UPDATE cms_paginas SET naam = \'Lichtingen\' WHERE naam = \'groepsbeschrijving_Lichtingen\'');
		$this->query('UPDATE cms_paginas SET naam = \'Onderverenigingen\' WHERE naam = \'groepsbeschrijving_Onderverenigingen\'');
		$this->query('UPDATE cms_paginas SET naam = \'RechtenGroepen\' WHERE naam = \'groepsbeschrijving_RechtenGroepen\'');
		$this->query('UPDATE cms_paginas SET naam = \'Verticalen\' WHERE naam = \'groepsbeschrijving_Verticalen\'');
		$this->query('UPDATE cms_paginas SET naam = \'Werkgroepen\' WHERE naam = \'groepsbeschrijving_Werkgroepen\'');
		$this->query('UPDATE cms_paginas SET naam = \'Woonoorden\' WHERE naam = \'groepsbeschrijving_Woonoorden\'');
	}
}
