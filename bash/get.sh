#!/usr/bin/env bash

cd $(dirname ${BASH_SOURCE[0]});


curl -X POST https://api.dropboxapi.com/2/files/list_folder \
    --header "Authorization: Bearer 8CqvxAXxLjIAAAAAAAAXadmk8MuiotLEkiHlTqyw0SbKT3QCe4lwxXvwVh02iv6r" \
    --header "Content-Type: application/json" \
    --data "{\"path\": \"\",\"recursive\": false,\"include_media_info\": false,\"include_deleted\": false,\"include_has_explicit_shared_members\": false}" \
     | jq  '.entries[] | .path_lower' | tee build/ext_files_list.txt




while read p; do
  echo $p;
  curl -X POST https://content.dropboxapi.com/2/files/download \
    --header 'Authorization: Bearer 8CqvxAXxLjIAAAAAAAAXadmk8MuiotLEkiHlTqyw0SbKT3QCe4lwxXvwVh02iv6r' \
    --header "Dropbox-API-Arg: {\"path\":$p}";
done < build/ext_files_list.txt