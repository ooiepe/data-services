<?php
require 'vendor/autoload.php';

// Prepare Slim app
$app = new \Slim\Slim(array(
  'templates.path' => 'templates',
));


/**
 * Service Homepage
 * Path: /
 */
$app->get('/', function() use ($app) {
  $app->render('homepage.php'); 
});

/**
 * Full Parameter List
 * Path: /parameters
 */
$app->get('/parameters', 'getParameters');
function getParameters() {
  $sql = "SELECT * FROM ts_parameters ORDER BY name";
  try {
    $db = DB::getInstance();
    $stmt = $db->query($sql);
    $parameters = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"parameters": ' . json_encode($parameters) . '}';
  } catch(PDOException $e) {
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/**
 * Parameter Details
 * Path: /parameters/[parameter_name]
 */
$app->get('/parameters/:id', 'getParameter');
function getParameter($name) {
  $sql = "SELECT * FROM ts_parameters WHERE name=:name";
  try {
    $db = DB::getInstance();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("name", $name);
    $stmt->execute();
    $parameter = $stmt->fetchObject();
    $db = null;
    echo json_encode($parameter);
  } catch(PDOException $e) {
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/**
 * Station Listing for a given Parameter - TBD
 * 
 */




/**
 * Full Station List
 * Path: /stations
 */
$app->get('/stations', 'getStations');
function getStations() {
  $geojson = array( 'type' => 'FeatureCollection', 'features' => array());
  $sql = "SELECT network,name,description,X(location) as longitude,Y(location) as latitude,start_time,end_time FROM ts_stations ORDER BY name";
  try {
    $db = DB::getInstance();
    $stmt = $db->query($sql);
    $stations = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    foreach ($stations as $sta) {
      $marker = array(
        'type' => 'Feature',
        'properties' => array(
          'network' => $sta->network,
          'name' => $sta->name,
          'description' => $sta->description,
          'start_time' => $sta->start_time,
          'end_time' => $sta->end_time,
        ),
        "geometry" => array(
          'type' => 'Point',
          'coordinates' => array($sta->longitude,$sta->latitude),
        )
      );        
      array_push($geojson['features'], $marker);
    }
    echo json_encode($geojson);
  } catch(PDOException $e) {
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/**
 * Station Details
 * Path: /stations/[network]/[station name]
 */
$app->get('/stations/:network/:name', 'getStation');
function getStation($network,$name) {
  try {
    $db = DB::getInstance();
    $sql = "SELECT id,network,name,description,X(location) as longitude,Y(location) as latitude,start_time,end_time FROM ts_stations WHERE network=:network AND name=:name";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("network", $network);
    $stmt->bindParam("name", $name);
    $stmt->execute();
    $station = $stmt->fetchObject();

    $sql = "SELECT parameter_name FROM ts_stations_parameters WHERE station_id=:station_id";
    $stmt = $db->prepare($sql);
    $stmt->bindValue("station_id", $station->id);
    $stmt->execute();
    $parameters = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    $station->parameters = $parameters;
    
    $db = null;
    echo json_encode($station);
  } catch(PDOException $e) {
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/**
 * Station Search 
 *  (returns only stations with valid parameter matches)
 * Path: /stations/search
 * Accepted parameters:
 *   location: lon_min,lat_min,lon_max,lat_max
 *   start_time: Either 0000-00-00T00:00Z or number of days before end_time
 *   end_time: Either 0000-00-00T00:00Z or now
 *   parameters: comma separated list of desired parameters (returns any matches, i.e. not just intersecting matches)
 *   networks: comma separated list of desired networks
 * Test URL: http://api.localhost/stations/search?location=-77,35,-69,42&start_time=1&end_time=now&networks=CO-OPS&parameters=salinity
 */
$app->get('/stations/search', 'searchStations');
function searchStations() {
  $params = new RequestParams();
  if (count($params->exceptions) > 0) {
    echo '{"error":'. json_encode($params->exceptions) .'}';
    exit;
  }
  $geojson = array( 'type' => 'FeatureCollection', 'features' => array());
  // Allow for searching over the International Date Line.
  if ($params->llon > $params->ulon) { 
	  $bbox = " MBRContains( GeomFromText(:loc1),location ) OR MBRContains( GeomFromText(:loc2),location )";
  } else {
	  $bbox = " MBRContains( GeomFromText(:loc),location )";
  }
  if ($params->networks!='all') {
    $str_net = " AND network IN ('" . implode("','",$params->networks) . "')";
  } else {
    $str_net ='';    
  }
  if ($params->parameters!='all') {
    $str_par = " AND parameter_name IN ('" . implode("','",$params->parameters) . "')";
  } else {
    $str_par ='';    
  }
  if (($params->start_time) && ($params->end_time)) {
    $str_tim = " AND start_time<= :et AND end_time>= :st ";
  } else {
    $str_tim ='';    
  }
  
  $sql = "SELECT network,name,description,X(location) as longitude,Y(location) as latitude,start_time,end_time 
  FROM ts_stations INNER JOIN ts_stations_parameters ON ts_stations.id = ts_stations_parameters.station_id
  WHERE $bbox $str_net $str_par $str_tim  
  GROUP BY name 
  ORDER BY name";

  try {
    $db = DB::getInstance();
    $stmt = $db->prepare($sql);
    if ($params->llon > $params->ulon) {
      $stmt->bindValue("loc1","Polygon(($params->llon $params->llat,$params->llon $params->ulat,180 $params->ulat,180 $params->llat,$params->llon $params->llat))");
      $stmt->bindValue("loc2","Polygon((-180 $params->llat,-180 $params->ulat,$params->ulon $params->ulat,$params->ulon $params->llat,-180 $params->llat))");
    } else {
      $stmt->bindValue("loc","Polygon(($params->llon $params->llat,$params->llon $params->ulat,$params->ulon $params->ulat,$params->ulon $params->llat,$params->llon $params->llat))");      
    }
    if (($params->start_time) && ($params->end_time)) {
      $stmt->bindParam("st",$params->start_time);
      $stmt->bindParam("et",$params->end_time);
    }
    $stmt->execute();
    $stations = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    foreach ($stations as $sta) {
      $marker = array(
        'type' => 'Feature',
        'properties' => array(
          'network' => $sta->network,
          'name' => $sta->name,
          'description' => $sta->description,
          'start_time' => $sta->start_time,
          'end_time' => $sta->end_time,
        ),
        "geometry" => array(
          'type' => 'Point',
          'coordinates' => array($sta->longitude,$sta->latitude),
        )
      );        
      array_push($geojson['features'], $marker);
    }
    //echo json_encode($stations);  //Used in testing
    echo json_encode($geojson);
  } catch(PDOException $e) {
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }


}

// Run Slim
$app->run();



/* RequestParams Class
 * Used to setup and validate search parameters
 */
class RequestParams {
	public $exceptions = array();
	public $llat = -90; 
	public $ulat = 90;
	public $llon = -180;
	public $ulon = 180;
	public $networks = 'all';
	public $parameters = 'all';
	public $start_time = null;
	public $end_time = null;
	private $params = array();
	
	public function __construct() {
    $app = \Slim\Slim::getInstance();
    $req = $app->request();
    $this->params['location']   = $req->get('location');
    $this->params['networks']   = $req->get('networks');
    $this->params['parameters'] = $req->get('parameters');
    $this->params['start_time'] = $req->get('start_time');
    $this->params['end_time']   = $req->get('end_time');
    if ($this->params['location']) {
		  $this->validateLocation();
		}
    if ($this->params['networks']) {
		  $this->validateNetworks();
    }
    if ($this->params['parameters']) {
		  $this->validateParameters();
    }
    if (($this->params['start_time']) || ($this->params['end_time'])) {
		  $this->validateTime();
		}
	}

  private function validateLocation() {
    if (preg_match('/^(-?\d+(\.\d+)?),(-?\d+(\.\d+)?),(-?\d+(\.\d+)?),(-?\d+(\.\d+)?)$/iD',$this->params['location'],$_matches) == 1) {
      $_llon = $_matches[1];
      $_llat = $_matches[3];
      $_ulon = $_matches[5];
      $_ulat = $_matches[7];
  		if ($_llat >= -90 && $_llat <= 90) {
  		  $this->llat = $_llat;
      } else {
  			$this->exceptions[] = 'Lower Latitude out of range (-90 to 90).';
  		}
  		if ($_ulat >= -90 && $_ulat <= 90) {
  		  $this->ulat = $_ulat;
      } else {
  			$this->exceptions[] = 'Upper Latitude out of range (-90 to 90).';
  		}
  		if ($_llon >= -360 && $_llon <= 360) {
  		  $this->llon = $_llon;
      } else {
  			$this->exceptions[] = 'Lower Longitude out of range (-360 to 360).';
  		}
  		if ($_ulon >= -360 && $_ulon <= 360) {
  		  $this->ulon = $_ulon;
      } else {
  			$this->exceptions[] = 'Upper Longitude out of range (-360 to 360).';
  		}
  		if ($_llat > $_ulat) {
  			$this->exceptions[] = 'Lower Latitude cannot be greater than Upper Latitude.';
  		}
  		//if ($_llon > $_ulon) {
  		//	$this->exceptions[] = 'Lower Longitude cannot be greater than Upper Longitude.';
  		//}
  		if (($_ulon - $_llon) > 360) {
  			$this->exceptions[] = 'Longitude range must not exceed 360 degrees.';
  		}
    } else {
			$this->exceptions[] = 'Bad location format.';
    }
  }
  
  private function validateNetworks() {  
    if (strcasecmp($this->params['networks'],'all')==0){
      $out = 'all';
    } else {
      $networks = array('NDBC','CO-OPS');
      $out=array();
      $items = explode(',',$this->params['networks']);
      foreach ($items as $it) {
        if (in_array($it,$networks)) {
          $out[] = $it;
        } else {
          $this->exceptions[] = "Network $it not found.";
        }
      }
    }
    $this->networks = $out;
  }  
  
  private function validateParameters() {  
    if (strcasecmp($this->params['parameters'],'all')==0){
      $out = 'all';
    } else {
      $parameters = array('air_pressure','air_temperature','depth','conductivity',
      'salinity','water_temperature','significant_wave_height','peak_wave_period',
      'mean_wave_period','wave_to_direction','wind_from_direction','wind_speed',
      'wind_gust','relative_humidity','predicted_tide','measured_tide');
      $out=array();
      $items = explode(',',$this->params['parameters']);
      foreach ($items as $it) {
        if (in_array($it,$parameters)) {
          $out[] = $it;
        } else {
          $this->exceptions[] = "Parameter $it not found.";
        }
      }
    }
    $this->parameters = $out;
  }  

  private function validateTime() {
  	if (strcasecmp($this->params['end_time'],'now') == 0) {
			$this->end_time = gmdate('Y-m-d H:i:s');
			$_tss = time();
	  } else {
			$_tss = readISO8601($this->params['end_time']);
			if ($_tss) {
			  $this->end_time = gmdate('Y-m-d H:i:s',$_tss);
		  } else {
        $this->exceptions[] = "Invalid end_time. Format should be 0000-00-00T00:00Z or 'now'.";
		  }
		}
    if (is_numeric($this->params['start_time']) && ($this->end_time)) {
			$this->start_time = gmdate('Y-m-d H:i:s',$_tss-$this->params['start_time']*60*60*24);
	  } else {
			$_tse = readISO8601($this->params['start_time']);
			if ($_tse) {
  			$this->start_time = gmdate('Y-m-d H:i:s',$_tse);
      } else {
        $this->exceptions[] = "Invalid start_time. Format should be 0000-00-00T00:00Z or number of days before end_time.";
      }
	  }	  
  }
  
}


/**
 * readISO8601
 * Adapted from NDBC SOS
 */
function readISO8601($str) {
	$_matches = array();
	$_ts = null;
	if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}(\.\d{2})?)Z$/D',$str,$_matches) === 1) {
		$_ts = gmmktime(intval($_matches[4]),intval($_matches[5]),round($_matches[6]),intval($_matches[2]),intval($_matches[3]),intval($_matches[1]));
	} elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})Z$/D',$str,$_matches) === 1) {
		$_ts = gmmktime(intval($_matches[4]),intval($_matches[5]),0,intval($_matches[2]),intval($_matches[3]),intval($_matches[1]));
	}
	return $_ts;
}


/**
 * Database Connection Class
 * Adapted from http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html
 */
class db{
  private static $instance = NULL;

  private function __construct() {
  }

  public static function getInstance() {
    if (!self::$instance) {
      require('config.php');
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