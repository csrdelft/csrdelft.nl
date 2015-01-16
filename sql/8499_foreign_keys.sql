ALTER TABLE `biebbeschrijving`
  ADD CONSTRAINT `biebbeschrijving_ibfk_1` FOREIGN KEY (`boek_id`) REFERENCES `biebboek` (`id`);

ALTER TABLE `biebbeschrijving`
  ADD CONSTRAINT `biebbeschrijving_ibfk_2` FOREIGN KEY (`schrijver_uid`) REFERENCES `profielen` (`uid`);

ALTER TABLE `biebboek`
  ADD CONSTRAINT `biebboek_ibfk_1` FOREIGN KEY (`auteur_id`) REFERENCES `biebauteur` (`id`);

ALTER TABLE `biebboek`
  ADD CONSTRAINT `biebboek_ibfk_2` FOREIGN KEY (`categorie_id`) REFERENCES `biebcategorie` (`id`);

ALTER TABLE `biebcategorie`
  ADD CONSTRAINT `biebcategorie_ibfk_1` FOREIGN KEY (`p_id`) REFERENCES `biebcategorie` (`id`);

ALTER TABLE `biebexemplaar`
  ADD CONSTRAINT `biebexemplaar_ibfk_1` FOREIGN KEY (`boek_id`) REFERENCES `biebboek` (`id`);

ALTER TABLE `biebexemplaar`
  ADD CONSTRAINT `biebexemplaar_ibfk_2` FOREIGN KEY (`eigenaar_uid`) REFERENCES `profielen` (`uid`);

ALTER TABLE `biebexemplaar`
  ADD CONSTRAINT `biebexemplaar_ibfk_3` FOREIGN KEY (`uitgeleend_uid`) REFERENCES `profielen` (`uid`);

ALTER TABLE `courant`
  ADD CONSTRAINT `courant_ibfk_1` FOREIGN KEY (`verzender`) REFERENCES `profielen` (`uid`);

ALTER TABLE  `courantbericht` CHANGE  `uid`  `uid` VARCHAR( 4 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL

ALTER TABLE `courantbericht`
  ADD CONSTRAINT `courantbericht_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

ALTER TABLE `courantbericht`
  ADD CONSTRAINT `courantbericht_ibfk_2` FOREIGN KEY (`courantID`) REFERENCES `courant` (`ID`);

ALTER TABLE `courantcache`
  ADD CONSTRAINT `courantcache_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

ALTER TABLE `crv_kwalificaties`
  ADD CONSTRAINT `crv_kwalificaties_ibfk_1` FOREIGN KEY (`functie_id`) REFERENCES `crv_functies` (`functie_id`);

ALTER TABLE `crv_kwalificaties`
  ADD CONSTRAINT `crv_kwalificaties_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

ALTER TABLE `crv_taken`
  ADD CONSTRAINT `crv_taken_ibfk_4` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

ALTER TABLE `crv_voorkeuren`
  ADD CONSTRAINT `crv_voorkeuren_ibfk_1` FOREIGN KEY (`crv_repetitie_id`) REFERENCES `crv_repetities` (`crv_repetitie_id`);

ALTER TABLE `crv_voorkeuren`
  ADD CONSTRAINT `crv_voorkeuren_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);

ALTER TABLE `crv_vrijstellingen`
  ADD CONSTRAINT `crv_vrijstellingen_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `profielen` (`uid`);
