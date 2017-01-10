#!/usr/bin/env bash

cd $(dirname ${BASH_SOURCE[0]});

. lib/parse_yaml.sh
eval $(parse_yaml ../config/parameters.yml "config_")

#curl -X POST https://api.dropboxapi.com/2/files/list_folder \
#    --header "Authorization: Bearer 8CqvxAXxLjIAAAAAAAAXadmk8MuiotLEkiHlTqyw0SbKT3QCe4lwxXvwVh02iv6r" \
#    --header "Content-Type: application/json" \
#    --data "{\"path\": \"\",\"recursive\": false,\"include_media_info\": false,\"include_deleted\": false,\"include_has_explicit_shared_members\": false}" \
#     | jq  '.entries[] | .path_lower' | tr -d "\"" > build/ext_files_list.txt

mysql -u root $config_parameters_dbname -e \
    "TRUNCATE log; DELETE FROM machine";

while read p; do
  echo $p;
#  curl -X POST https://content.dropboxapi.com/2/files/download \
#    --header 'Authorization: Bearer 8CqvxAXxLjIAAAAAAAAXadmk8MuiotLEkiHlTqyw0SbKT3QCe4lwxXvwVh02iv6r' \
#    --header "Dropbox-API-Arg: {\"path\":\"$p\"}" > build$p;
#

    if [[ $p == "log_"* ]]
    then
      echo "LOG: $p !";
      mysql -u root $config_parameters_dbname -e \
      "LOAD DATA INFILE 'build/machine_fcb75356-d759-11e6-bbe0-c04a003d4415.txt' INTO TABLE log;";
    fi


    if [[ $p == "machine_"* ]]
    then
      echo "MACHINE: $p !";
        mysql -u root $config_parameters_dbname -e \
        "LOAD DATA INFILE 'build$p' INTO TABLE machine;";
    fi

done < build/ext_files_list.txt