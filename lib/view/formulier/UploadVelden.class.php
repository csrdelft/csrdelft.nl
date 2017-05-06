<?php
namespace CsrDelft\view\formulier;

require_once 'model/entity/Afbeelding.class.php';

/**
 * UploadVelden.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Verschillende manieren om een bestand of afbeelding(en) te uploaden.
 * 
 * 	- FileField						uitbreiding van KeuzeRondjeField
 * 		* ImageField
 * 
 * 	- BestandUploader				uitbreiding van InputField
 * 		* BestandBehouden
 * 		* UploadFileField
 * 			- DropZoneUploader
 * 		* ExistingFileField
 * 		* DownloadUrlField
 * 
 */

require_once 'view/formulier/uploadvelden/BestandBehouden.class.php';
require_once 'view/formulier/uploadvelden/DownloadUrlField.class.php';
require_once 'view/formulier/uploadvelden/ExistingFileField.class.php';
require_once 'view/formulier/uploadvelden/FileField.class.php';
require_once 'view/formulier/uploadvelden/ImageField.class.php';
require_once 'view/formulier/uploadvelden/UploadFileField.class.php';
require_once 'view/formulier/uploadvelden/RequiredFileField.class.php';
require_once 'view/formulier/uploadvelden/RequiredImageField.class.php';
