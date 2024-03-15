#!/bin/sh

PATH_TO_BUILD="build/static/js"

npm run lint:fix &&
npm run build &&
composer lint &&
rm -rf -f /Themes/default/scripts/breezeComponents/*;

cp -a $PATH_TO_BUILD/. Themes/default/scripts/breezeComponents/  &&
echo "Copied front build to SMF's Themes folder";

JS_FILE=$(ls $PATH_TO_BUILD/main*.js);

for eachfile in $JS_FILE
do
basename "$eachfile"
f="$(basename -s .js -- "$eachfile")"

delimiter="."
hash="${f#*${delimiter}}"
hash="${hash%${delimiter}*}"

done

mainBreezeFilePath="Sources/Breeze/Breeze.php"
lineNum="$(grep -n "REACT_HASH" ${mainBreezeFilePath} | head -n 1 | cut -d: -f1)"
fileLine=$(sed "${lineNum}q;d" ${mainBreezeFilePath})
delimiter="'"
old_hash="${fileLine#*${delimiter}}"
old_hash="${old_hash%${delimiter}*}"

sed -i "s/$old_hash/$hash/g" Sources/Breeze/Breeze.php;

echo "replaced $old_hash with $hash";

composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader &&
  rm -f "Breeze.zip" &&
  zip -r Breeze breezeVendor/ Sources/ Themes/ tasks/ install.php installCheck.php License package-info.xml README.md &&
echo "Created zip file";

composer update &&
composer dump-autoload &&
echo "Restored dev dependencies";

