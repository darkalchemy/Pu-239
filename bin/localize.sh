#!/bin/bash

LANG=$1

if [[ $LANG == '' ]];then
    LANG="en_US"
fi

cd $PWD
cp vendor/delight-im/i18n/i18n.sh .
chmod a+x i18n.sh
echo Processing $LANG locale
./i18n.sh $LANG
rm i18n.sh
