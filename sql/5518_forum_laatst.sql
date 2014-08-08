ALTER TABLE forum_posts CHANGE laatst_bewerkt laatst_gewijzigd datetime;
UPDATE forum_posts SET bewerkt_tekst = NULL WHERE bewerkt_tekst = '';
UPDATE forum_posts SET laatst_gewijzigd = NULL WHERE bewerkt_tekst IS NULL;
UPDATE forum_posts SET laatst_gewijzigd = datum_tijd WHERE laatst_gewijzigd IS NULL;