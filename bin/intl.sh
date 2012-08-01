. ./confv.sh
xgettext --package-name=$PROYECTO --package-version=$PRY_VERSION --foreign-user --copyright-holder="Public Domain according to Colombian legislation" --msgid-bugs-address=vtamara@pasosdeJesus.org -kT_gettext -kT_ --from-code utf-8 -d $PROYECTO -o locale/$PROYECTO.pot -L PHP --no-wrap *php DataObjects/*php sitios/pordefecto/*php modulos/anexos/*php modulos/anexos/DataObjects/*php modulos/estrotulos/*php modulos/mapag/*php modulos/belicas/*php modulos/belicas/DataObjects/*php modulos/etiquetas/*php modulos/etiquetas/DataObjects/*php modulos/segjudicial/*php modulos/segjudicial/DataObjects/*php
if (test ! -f locale/en/LC_MESSAGES/$PROYECTO.po) then {
	msginit -l en -o locale/en/LC_MESSAGES/$PROYECTO.po -i locale/$PROYECTO.pot
} else {
	msgmerge -U locale/en/LC_MESSAGES/$PROYECTO.po locale/$PROYECTO.pot
} fi;
msgfmt --output=locale/en/LC_MESSAGES/$PROYECTO.mo locale/en/LC_MESSAGES/$PROYECTO.po

