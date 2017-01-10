#!/usr/bin/env bash
# Script transform log, and machine tables to files in dropbox
cd $(dirname ${BASH_SOURCE[0]});

. lib/parse_yaml.sh
eval $(parse_yaml ../config/parameters.yml "config_")

for i in log machine
do
    mysql -uroot training -e "select * from $i" -B | tail -n +2 > build/$i.tsv

    curl -X POST https://content.dropboxapi.com/2/files/upload \
    --header "Authorization: Bearer $config_parameters_token" \
    --header "Dropbox-API-Arg: {\"path\": \"/${i}_$config_parameters_guid.txt\",\"mode\": \"overwrite\",\"autorename\": false,\"mute\": false}" \
    --header "Content-Type: application/octet-stream" \
    --data-binary @build/${i}.tsv
done