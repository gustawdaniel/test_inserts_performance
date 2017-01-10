#!/usr/bin/env bash

cd $(dirname ${BASH_SOURCE[0]}); mkdir -p build; rm -rf build/*


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

dd if=/dev/zero of=build/temp_file bs=512 count=10000 oflag=dsync 2>&1 \
    | tee /dev/tty \
    | awk '/copied/ {print $8}' | tr , . >> build/log_lat

cat /proc/cpuinfo 2>&1 \
    | tee /dev/tty \
    | awk '/cpu MHz/ {print $4}' > build/log_cpu


for i in log_write log_read log_lat log_cpu
do
    concatenatedData+=","$(awk '{ total += $1; count++ } END { print total/count }' build/$i);
done


. lib/parse_yaml.sh
eval $(parse_yaml ../config/parameters.yml "config_")

mysql -u root $config_parameters_dbname -e \
    "INSERT INTO machine (id, name, \`write\`, \`read\`, latency, cpu) VALUES
    (uuid(),'$config_parameters_machine' $concatenatedData)";

