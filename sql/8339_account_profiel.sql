UPDATE `lid` SET studienr = null WHERE studienr = '';
UPDATE `lid` SET studienr = null WHERE studienr NOT REGEXP '^[0-9]*$';