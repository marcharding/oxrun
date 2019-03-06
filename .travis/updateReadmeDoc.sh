#!/usr/bin/env bash

cd $(dirname $0);

BASE_DIR=$(pwd -P);
README="$BASE_DIR/../README.md";
SHOP_DIR=${1:-"$BASE_DIR/../oxid-esale/source"};

if [[ ! -d $SHOP_DIR ]]; then
    echo "$SHOP_DIR not found" >&2
    exit 2
fi


LINE=$(grep -n "Available commands" $README  | head -n 1 | cut -d: -f1);
if [ ! $LINE ]; then
    echo "'Available commands' not found in README.md" >&2
    exit 2;
fi

echo "Keep header of README.md";
LINE=$(expr $LINE - 1);
sed -i "${LINE}q" $README;

echo "Generate documentatio";
cd $SHOP_DIR;
../../bin/oxrun misc:generate:documentation >> $README

