#!/usr/bin/env bash
# Script transform log, and machine tables to files in dropbox



curl -X POST https://content.dropboxapi.com/2/files/upload \
--header "Authorization: Bearer 8CqvxAXxLjIAAAAAAAAXXM-V4GP1LZTxBC-Gw9PaUpwue4tP_uLSeuYUBTZt7JF5" \
--header "Dropbox-API-Arg: {\"path\": \"/log_lat.txt\",\"mode\": \"overwrite\",\"autorename\": false,\"mute\": false}" \
--header "Content-Type: application/octet-stream" \
--data-binary @log_lat.txt