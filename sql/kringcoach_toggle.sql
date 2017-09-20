-- Refactor voor kringcoach toggle 27/10/2016
UPDATE profielen SET kringcoach = '1' WHERE `kringcoach` IS NOT NULL;
UPDATE profielen SET kringcoach = '0' WHERE `kringcoach` IS NULL;
ALTER TABLE profielen CHANGE kringcoach kringcoach tinyint(1) NULL DEFAULT NULL;