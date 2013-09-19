<pre>
<?php
/**
 * Normalized Parameter List
 */
$parameters = array(
  //  Raw Value => Normalized IOOS Parameter
  'NDBC' => array(
    'air_pressure_at_sea_level' => 'air_pressure',
    'air_temperature' => 'air_temperature',
    'sea_floor_depth_below_sea_surface' => 'depth',
    'sea_water_electrical_conductivity' => 'conductivity',
    'sea_water_salinity' => 'salinity',
    'sea_water_temperature' => 'water_temperature',
    'waves' => array('significant_wave_height','peak_wave_period','mean_wave_period','wave_to_direction'),
    'winds' => array('wind_from_direction','wind_speed','wind_gust'),
  ),
  'CO-OPS' => array(
    'air_pressure' => 'air_pressure',
    'air_temperature' => 'air_temperature',
    'relative_humidity' => 'relative_humidity',
    'sea_surface_height_amplitude_due_to_equilibrium_ocean_tide' => 'predicted_tide',
    'sea_water_electrical_conductivity' => 'conductivity',
    'sea_water_salinity' => 'salinity',
    'sea_water_temperature' => 'water_temperature',
    'water_surface_height_above_reference_datum' => 'measured_tide',
    'winds' => array('wind_from_direction','wind_speed','wind_gust'),
  ),
);

/**
 * Parse NDBC Stations file
 */
$network = 'CO-OPS';

// Step 1: Open XML File
$xmlLoc = '../xml/CO-OPS.xml';
$xml = @simplexml_load_file($xmlLoc);

// Step 2: Loop through each station
foreach ($xml->children('http://www.opengis.net/sos/1.0')->{'Contents'}[0]->{'ObservationOfferingList'}[0]->{'ObservationOffering'} as $o) {
  $chld = $o->children('http://www.opengis.net/gml');  
  $name = str_replace('station-','',sprintf("%s",$o->attributes('http://www.opengis.net/gml')->{'id'}));
  if(!strncasecmp($name,'network-',8)) {
    echo "Skipping network: $name\n";
    continue; 
  }  
  $description = sprintf("$network Station %s%s",$name,$chld->{'description'} != 'GetCapabilities' ? ' - '.$chld->{'description'} : '');
  $location = explode(' ',sprintf("%s",$chld->{'boundedBy'}[0]->{'Envelope'}[0]->{'lowerCorner'}));
  $location2 = explode(' ',sprintf("%s",$chld->{'boundedBy'}[0]->{'Envelope'}[0]->{'upperCorner'}));
  if ($location!=$location2) {
    echo "Skipping moving platform: $description\n";
    continue;
  }
  $start_time = strtotime(sprintf("%s",$o->{'time'}[0]->children('http://www.opengis.net/gml')->{'TimePeriod'}->children('http://www.opengis.net/gml')->{'beginPosition'}[0]));
  $end_time = strtotime(sprintf("%s",$o->{'time'}[0]->children('http://www.opengis.net/gml')->{'TimePeriod'}->children('http://www.opengis.net/gml')->{'endPosition'}[0]));
  //echo "Found: $name\n";
  
  // Step 3: if lookup station update date, else add station
  $sid = addStation($network,$name,$description,$location,$start_time,$end_time);

  // Step 4: Loop through each parameter
  foreach ($o->{'observedProperty'} as $prop) {
    $p = explode('/',$prop->attributes('http://www.w3.org/1999/xlink')->{'href'});
    $p = sprintf("%s",$p[count($p)-1]);
    // Step 5: check and normalize parameter
    $pnorm = (array_key_exists($p,$parameters[$network])?$parameters[$network][$p]:'');
    if ($pnorm) {
      // Step 6: if lookup parameter update date, else add parameter
      addParameter($sid,$pnorm);
    } else {
      echo "  Not Found: $p\n";
    }
  }
}

/**
 * addStation
 */
function addStation($network,$name,$description,$location,$start_time,$end_time) {
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
      echo "Updated: $name\n";    
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
      echo "Inserted: $name\n";    
      $id = $db->lastInsertId('id');
    }
  } catch(PDOException $e) {
      echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
  return $id;
}

/**
 * addParameter
 */
function addParameter($sid,$name) {
  if (is_array($name)) {
    foreach ($name as $n) {
      addParameter($sid,$n);    
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
        echo "  Updated: $name\n";    
      } else {
        // else insert and return id
        $stmt = $db->prepare("INSERT INTO ts_stations_parameters (station_id, parameter_name, modified) VALUES (:station_id, :parameter_name, NOW() )");
        $stmt->bindParam("station_id", $sid);
        $stmt->bindParam("parameter_name", $name);
        $stmt->execute();
        echo "  Inserted: $name\n";    
      }
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
  }
}

/**
 * Database Class
 * Adapted from http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html
 */
class db{
  private static $instance = NULL;

  private function __construct() {
  }

  public static function getInstance() {
    if (!self::$instance) {
      require('../config.php');
      $dbhost=$config['database']['host'];
      $dbuser=$config['database']['login'];
      $dbpass=$config['database']['password'];
      $dbname=$config['database']['database'];
      self::$instance = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
      self::$instance-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return self::$instance;
  }

  private function __clone(){
  }
}
?>