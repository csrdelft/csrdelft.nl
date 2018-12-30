#!/usr/bin/env bash

lib_dir="${BASH_SOURCE%/*}/../../lib"
grep -P "define\\((['\"])ONDERHOUD\1, ?(true|false)\\);" ${lib_dir}/defines.include.php > /dev/null
in_bestand=$?
if [[ -z "$1" ]] || [[ $1 = true ]]; then
	echo "Stek in onderhoudsmodus aan het zetten"
	if [[ $in_bestand -eq 0 ]]; then # ONDERHOUD define staat al in defines.include.php
		sed -ri "s/define\\((['\"])ONDERHOUD\1, ?(true|false)\\);/define(\1ONDERHOUD\1, true);/" ${lib_dir}/defines.include.php
	else # ONDERHOUD define staat nog niet in defines.include.php
		echo -e "\ndefine('ONDERHOUD', true);" >> ${lib_dir}/defines.include.php
	fi
	echo "Stek in onderhoudsmodus gezet"
else
	echo "Stek uit onderhoudsmodus aan het halen"
	if [[ $in_bestand -eq 0 ]]; then # ONDERHOUD define staat al in defines.include.php
		sed -ri "s/define\\((['\"])ONDERHOUD\1, ?(true|false)\\);/define(\1ONDERHOUD\1, false);/" ${lib_dir}/defines.include.php
	else # ONDERHOUD define staat nog niet in defines.include.php
		echo -e "\ndefine('ONDERHOUD', false);" >> ${lib_dir}/defines.include.php
	fi
	echo "Stek uit onderhoudsmodus gehaald"
fi
