ALTER TABLE verticalen DROP kringcoach;
ALTER TABLE profielen ADD verticaleleider tinyint(1) NOT NULL AFTER verticale;
ALTER TABLE profielen ADD kringcoach char(1) NULL DEFAULT NULL AFTER verticaleleider;
ALTER TABLE profielen DROP motebal;