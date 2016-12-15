ALTER TABLE mlt_maaltijden CHANGE aanmeld_limiet aanmeld_limiet int(11) NOT NULL;
ALTER TABLE mlt_maaltijden CHANGE prijs prijs int(11) NOT NULL;
ALTER TABLE mlt_maaltijden CHANGE gesloten gesloten tinyint(1) NOT NULL;
ALTER TABLE mlt_maaltijden CHANGE laatst_gesloten laatst_gesloten int(11) NULL DEFAULT NULL;
ALTER TABLE mlt_maaltijden CHANGE verwijderd verwijderd tinyint(1) NOT NULL;
ALTER TABLE mlt_maaltijden CHANGE omschrijving omschrijving text NULL DEFAULT NULL;


ALTER TABLE mlt_aanmeldingen CHANGE aantal_gasten aantal_gasten int(11) NOT NULL;
ALTER TABLE mlt_aanmeldingen CHANGE gasten_eetwens gasten_eetwens varchar(255) NULL DEFAULT NULL;


ALTER TABLE mlt_repetities CHANGE dag_vd_week dag_vd_week int(11) NOT NULL;
ALTER TABLE mlt_repetities CHANGE standaard_prijs standaard_prijs int(11) NOT NULL;
ALTER TABLE mlt_repetities CHANGE abonneerbaar abonneerbaar tinyint(1) NOT NULL;
