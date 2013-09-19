<?php
// Script to cache GetCapabilities data from various sites

  $dest_dir = '../xml';  // everything in here (including the dir) should be writable by the user running this script

  $u = 'http://sdf.ndbc.noaa.gov/sos/server.php?VERSION=1.0.0&SERVICE=SOS&REQUEST=GetCapabilities';
  echo "$u\n";
  file_put_contents("$dest_dir/ndbc.xml",file_get_contents($u));

  $u = 'http://opendap.co-ops.nos.noaa.gov/ioos-dif-sos/SOS?service=SOS&request=GetCapabilities';
  echo "$u\n";
  file_put_contents("$dest_dir/co-ops.xml",file_get_contents($u));

  //$u = 'http://www.weatherflow.com/sos/sos.pl?request=GetCapabilities&service=SOS';
  //echo "$u\n";
  //file_put_contents("$dest_dir/weatherflow.xml",file_get_contents($u));

?>