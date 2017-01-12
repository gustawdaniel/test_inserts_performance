#!/usr/bin/env bash

cd $(dirname ${BASH_SOURCE[0]});

. lib/parse_yaml.sh
eval $(parse_yaml ../config/parameters.yml "config_")

    mysql -u $config_parameters_user $config_parameters_dbname -e \
        "TRUNCATE log; DELETE FROM machine";

for i in machine log
do
    curl -X POST https://api.dropboxapi.com/2/files/list_folder \
        --header "Authorization: Bearer 8CqvxAXxLjIAAAAAAAAXadmk8MuiotLEkiHlTqyw0SbKT3QCe4lwxXvwVh02iv6r" \
        --header "Content-Type: application/json" \
        --data "{\"path\": \"/$i\",\"recursive\": false,\"include_media_info\": false,\"include_deleted\": false,\"include_has_explicit_shared_members\": false}" \
         | jq  '.entries[] | .path_lower' | tr -d "\"" > build/${i}_ext_files_list.txt


    rm build/${i}.tsv
    while read p; do
        echo $p;
        curl -X POST https://content.dropboxapi.com/2/files/download \
            --header 'Authorization: Bearer 8CqvxAXxLjIAAAAAAAAXadmk8MuiotLEkiHlTqyw0SbKT3QCe4lwxXvwVh02iv6r' \
            --header "Dropbox-API-Arg: {\"path\":\"$p\"}" >> build/${i}.tsv

    done < build/${i}_ext_files_list.txt

    mysqlimport -u $config_parameters_user  --local $config_parameters_dbname build/${i}.tsv

done;

