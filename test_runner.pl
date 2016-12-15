#!/usr/bin/env perl

# This script wget HTML from wikipedia, and parse it saving languages to database.

use Modern::Perl;       # modern syntax
use HTML::TableExtract; # parse html table
use YAML::Tiny;         # open symfony config
use strict;             # strict mode
use DBI();              # database

use Time::Format qw/%time/;
use Time::HiRes qw/gettimeofday/;

use strict;
use warnings;

my $start_time = gettimeofday;
my $last_time = gettimeofday;


#------------------------------------------#
#             Configuration                #
#------------------------------------------#

my $parameters = 'app/config/parameters.yml';

#------------------------------------------#
#                 Script                   #
#------------------------------------------#


#----------------------------------#
#    get config from symfony yml   #
#----------------------------------#
my $yaml = YAML::Tiny->read( $parameters );
my $config = $yaml->[0]->{parameters};
if($config->{database_password} eq "null") { #    filter null password
    $config->{database_password}="";
}


#----------------------------------#
#    Connect to the database.      #
#----------------------------------#
my $dbh = DBI->connect("DBI:mysql:database=".$config->{database_name}.";host=".$config->{database_host},
    $config->{database_user}, $config->{database_password}, {
        'PrintError'         => 0,
        'RaiseError'         => 1,
        'mysql_enable_utf8'  => 1,
        'AutoCommit'         => 0
    }) or die "Connect to database failed";



my @list = join(",", map { "minor_". $_ ."_id" } (1..10));
my $query = "INSERT INTO log (n, l, k0, k, execution_time, operation) VALUES (?,?,?,?,?,?)";
my $mainquery = "INSERT INTO main_1 (id, @list ) VALUES (" . "?,"x10 . "?)";
my $delete = "DELETE FROM main_1";
my $mainStatement = $dbh->prepare($mainquery);
my $statement = $dbh->prepare($query);

my $deleteStatement = $dbh->prepare($delete);


my $n=10;
my $l=50;
my $k0=1;

#----------------------------------#
#        wget file from wiki       #
#----------------------------------#
my $imin=3;
my $imax=10;
for(my $i=$imin;$i<=$imax;$i++){
    my $k=100000*$i;
#    system("(http -b --timeout=3600 GET localhost:8000/main/$n/$l/$k0/$k/1/1) > /dev/null 2>&1");
    $deleteStatement->execute();

    for(my $ind1=$k0;$ind1<=$k;$ind1++){
                    my @content = ($ind1);
        for(my $ind2=1;$ind2<=$n;$ind2++){
            push @content, int(rand($l))+1;
        }

#        print "@content\n";
        $mainStatement->execute(@content);
        if($ind1%1e3==0){
            print ".";
            if($ind1%1e4==0){
                print "| ".$ind1."\n";
            }
        }
    }

    $dbh->commit();


    my $now_time = gettimeofday;
    print " $k \t| $i - ". ($now_time - $start_time) ." - ". ($now_time - $last_time) ."\t\t | $i/$imax \n";
    $statement->execute($n,$l,$k0,$k,$now_time - $last_time,"debug_save_k_to_m1_trans_with_del_by_perl");
    $dbh->commit();
    $last_time = gettimeofday;
}


$deleteStatement->finish();

$statement->finish();
$mainStatement->finish();
#----------------------------------#
#   Disconnect from the database.  #
#----------------------------------#
$dbh->disconnect();
