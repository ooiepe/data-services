<?php
/**
 * Load Parameters
 */
$app->get('/load/parameters', $authenticateForRole('admin'), function () use ($app) {
  $output = addParameters();
  $app->render('report.php',array('output'=>$output));
});

/**
 * Load NDBC Stations
 */
$app->get('/load/ndbc', $authenticateForRole('admin'), function () use ($app) {
  $u = 'http://sdf.ndbc.noaa.gov/sos/server.php?VERSION=1.0.0&SERVICE=SOS&REQUEST=GetCapabilities';
  $xml = simplexml_load_string(url_get_contents($u));
  $output = parseSOS($xml,'NDBC');
  $app->render('report.php',array('output'=>$output));
});

/**
 * Load CO-OPS Stations
 */
$app->get('/load/co-ops', $authenticateForRole('admin'), function () use ($app) {
  $u = 'http://opendap.co-ops.nos.noaa.gov/ioos-dif-sos/SOS?service=SOS&request=GetCapabilities';
  $xml = simplexml_load_string(url_get_contents($u));
  $output = parseSOS($xml,'CO-OPS');
  $app->render('report.php',array('output'=>$output));
});


/* -------------------------------------------------- */
/**
 * Basic Curl Request
 */
function url_get_contents($url) {
  if (!function_exists('curl_init')){ 
    die('CURL is not installed!');
  }
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $output = curl_exec($ch);
  curl_close($ch);
  return $output;
}


/* -------------------------------------------------- */
/**
 * Parse SOS GetCapabilities response
 */
function parseSOS ($xml,$network) {
  global $normalizedParameters;
  $output='';
  // Step 1: Loop through each station
  foreach ($xml->children('http://www.opengis.net/sos/1.0')->{'Contents'}[0]->{'ObservationOfferingList'}[0]->{'ObservationOffering'} as $o) {
    $chld = $o->children('http://www.opengis.net/gml');  
    $name = str_replace('station-','',sprintf("%s",$o->attributes('http://www.opengis.net/gml')->{'id'}));
    if(!strncasecmp($name,'network-',8)) {
      $output .= "Skipping network: $name\n";
      continue; 
    }  
    $description = sprintf("$network Station %s%s",$name,$chld->{'description'} != 'GetCapabilities' ? ' - '.$chld->{'description'} : '');
    $location = explode(' ',sprintf("%s",$chld->{'boundedBy'}[0]->{'Envelope'}[0]->{'lowerCorner'}));
    $location2 = explode(' ',sprintf("%s",$chld->{'boundedBy'}[0]->{'Envelope'}[0]->{'upperCorner'}));
    if ($location!=$location2) {
      $output .= "Skipping moving platform: $description\n";
      continue;
    }
    $start_time = strtotime(sprintf("%s",$o->{'time'}[0]->children('http://www.opengis.net/gml')->{'TimePeriod'}->children('http://www.opengis.net/gml')->{'beginPosition'}[0]));
    $end_time = strtotime(sprintf("%s",$o->{'time'}[0]->children('http://www.opengis.net/gml')->{'TimePeriod'}->children('http://www.opengis.net/gml')->{'endPosition'}[0]));
    //$output .= "Found: $name\n";
    
    // Step 2: if lookup station update date, else add station
    $res = addStation($network,$name,$description,$location,$start_time,$end_time);
    $sid = $res['id'];
    $output .= $res['output'];
  
    // Step 3: Loop through each parameter
    foreach ($o->{'observedProperty'} as $prop) {
      $p = explode('/',$prop->attributes('http://www.w3.org/1999/xlink')->{'href'});
      $p = sprintf("%s",$p[count($p)-1]);
      // Step 4: check and normalize parameter
      foreach ($normalizedParameters[strtolower($network)] as $key => $value) {
        if ($value['request']==$p) {
          // Step 5: if lookup parameter update date, else add parameter
          $output .= addStationParameter($sid,$key);
        }
      }
    }
  }  
  return $output;
}

/**
 * addStation
 */
function addStation($network,$name,$description,$location,$start_time,$end_time) {
  $output='';
  try {
    // Lookup station
    $db = DB::getInstance();
    $stmt = $db->prepare("SELECT * from ts_stations WHERE network= :network AND name= :name");
    $stmt->bindParam("network", $network);
    $stmt->bindParam("name", $name);
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row) {
      // if exists, update modified date, return id
      $id = $row['id'];
      $stmt = $db->prepare("UPDATE ts_stations SET network=:network, name=:name, description=:description, location=GeomFromText(:loc), start_time=:start_time, end_time=:end_time, modified=NOW() WHERE id= :id");
      $stmt->bindParam("id", $id);
      $stmt->bindParam("network", $network);
      $stmt->bindParam("name", $name);
      $stmt->bindParam("description", $description);
      $stmt->bindValue("loc", "POINT(" . $location[1] . " " . $location[0] . ")");  // SOS format is Lat/Lon, flip to Lon/Lat
      $stmt->bindValue("start_time", date("Y-m-d H:i:s",$start_time));
      $stmt->bindValue("end_time", ($end_time==''?"9999-01-01":date("Y-m-d H:i:s",$end_time)));
      $stmt->execute();
      $output .= "Updated: $name\n";    
    } else {
      // else insert and return id
      $stmt = $db->prepare("INSERT INTO ts_stations (network, name, description, location, start_time, end_time, modified) VALUES (:network, :name, :description, GeomFromText(:loc), :start_time, :end_time, NOW() )");
      $stmt->bindParam("network", $network);
      $stmt->bindParam("name", $name);
      $stmt->bindParam("description", $description);
      $stmt->bindValue("loc", "POINT(" . $location[1] . " " . $location[0] . ")");  // SOS format is Lat/Lon, flip to Lon/Lat
      $stmt->bindValue("start_time", date("Y-m-d H:i:s",$start_time));
      $stmt->bindValue("end_time", ($end_time==''?"9999-01-01":date("Y-m-d H:i:s",$end_time)));
      $stmt->execute();
      $output .= "Inserted: $name\n";    
      $id = $db->lastInsertId('id');
    }
  } catch(PDOException $e) {
      $output .= '{"error":{"text":'. $e->getMessage() .'}}';
  }
  return array ('id'=>$id,'output'=>$output);
}

/**
 * addStationParameter
 */
function addStationParameter($sid,$name) {
  $output='';
  if (is_array($name)) {
    foreach ($name as $n) {
      addStationParameter($sid,$n);    
    }
  } else {
    try {
      // Lookup parameter
      $db = DB::getInstance();
      $stmt = $db->prepare("SELECT * from ts_stations_parameters WHERE station_id=:station_id AND parameter_name=:parameter_name");
      $stmt->bindParam("station_id", $sid);
      $stmt->bindParam("parameter_name", $name);
      $stmt->execute();
      $row = $stmt->fetch();
      if ($row) {
        // if exists, update modified date, return id
        $id = $row['id'];
        $stmt = $db->prepare("UPDATE ts_stations_parameters SET modified=NOW() WHERE id= :id");
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $output .= "  Updated: $name\n";    
      } else {
        // else insert and return id
        $stmt = $db->prepare("INSERT INTO ts_stations_parameters (station_id, parameter_name, modified) VALUES (:station_id, :parameter_name, NOW() )");
        $stmt->bindParam("station_id", $sid);
        $stmt->bindParam("parameter_name", $name);
        $stmt->execute();
        $output .= "  Inserted: $name\n";    
      }
    } catch(PDOException $e) {
        $output .= '{"error":{"text":'. $e->getMessage() .'}}';
    }
    return $output;
  }
}



/**
 * addParameters
 */
function addParameters() {
  global $parameters;
  $output='';
  foreach ($parameters as $param) {
    $output .= addParameter($param['name'],$param['category'],$param['description'],$param['units'],$param['cf_url'],$param['ioos_url']);
  }
  return $output;
}

/**
 * addParameter
 */
function addParameter($name,$category,$description,$units,$cf_url,$ioos_url) {
  $output='';
  try {
    // Lookup parameter
    $db = DB::getInstance();
    $stmt = $db->prepare("SELECT * from ts_parameters WHERE name=:name");
    $stmt->bindParam("name", $name);
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row) {
      // if exists, update modified date, return id
      $id = $row['name'];
      $stmt = $db->prepare("UPDATE ts_parameters SET modified=NOW() WHERE name=:id");
      $stmt->bindParam("id", $id);
      $stmt->execute();
      $output .= "  Updated: $name\n";    
    } else {
      // else insert and return id
      $stmt = $db->prepare("INSERT INTO ts_parameters (name, category, description, units, cf_url, ioos_url, modified) VALUES (:name, :category, :description, :units, :cf_url, :ioos_url, NOW() )");
      $stmt->bindParam("name", $name);
      $stmt->bindParam("category", $category);
      $stmt->bindParam("description", $description);
      $stmt->bindParam("units", $units);
      $stmt->bindParam("cf_url", $cf_url);
      $stmt->bindParam("ioos_url", $ioos_url);
      $stmt->execute();
      $output .= "  Inserted: $name\n";    
    }
  } catch(PDOException $e) {
      $output .= '{"error":{"text":'. $e->getMessage() .'}}';
  }
  return $output;
}

?>