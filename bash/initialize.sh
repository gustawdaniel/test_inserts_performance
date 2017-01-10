#!/usr/bin/env bash
export LC_ALL=C

cd $(dirname ${BASH_SOURCE[0]}); mkdir -p build;

rm -rf build/*
for i in {1..2}
do
    sudo hdparm -t --direct /dev/sda1 2>&1 \
        | tee /dev/tty \
        | awk '/Timing/ {print $11}' >> build/log_read
done

for i in {1..10}
do
	dd if=/dev/zero of=build/temp_file bs=8k count=10000 conv=fdatasync 2>&1 \
	    | tee /dev/tty \
	    | awk '/copied/ {print $10}' >> build/log_write
done

dd if=/dev/zero of=build/temp_file bs=512 count=100 oflag=dsync 2>&1 \
    | tee /dev/tty \
    | awk '/copied/ {print $8}' | tr , . >> build/log_lat

rm build/temp_file

cat /proc/cpuinfo 2>&1 \
    | tee /dev/tty \
    | awk '/cpu MHz/ {print $4}' > build/log_cpu


for i in write read lat cpu
do
    concatenatedData+=","$(awk '{ total += $1; count++ } END { print total/count }' build/log_$i);
done


. lib/parse_yaml.sh
eval $(parse_yaml ../config/parameters.yml "config_")

#echo $config_parameters_machine;

mysql -u root $config_parameters_dbname -e \
    "TRUNCATE log; DELETE FROM machine;
    INSERT INTO machine (id, name, \`write\`, \`read\`, latency, cpu) VALUES
    (uuid(),'$config_parameters_name' $concatenatedData)";

sed -i -e "s/guid: [a-z0-9-]*/guid: `mysql -u root $config_parameters_dbname -s -e "SELECT id FROM machine" | cut -f1`/g" \
    ../config/parameters.yml

sed -i -e "s/ name: [a-zA-Z0-9-]*/ name: `uname -n`/g" \
    ../config/parameters.yml
