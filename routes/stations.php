<?php
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

  $app = \Slim\Slim::getInstance();
  $req = $app->request();
  
  // Validate Parameters
  $valid = new validateParams();
  if ($req->get('networks')) {
	  $valid->validateNetworks($req->get('networks'));
  }
  if ($req->get('parameters')) {
	  $valid->validateParameters($req->get('parameters'));
  }
  if (($req->get('start_time')) || ($req->get('end_time'))) {
	  $valid->validateTimes($req->get('start_time'),$req->get('end_time'));
	}
  if ($req->get('location')) {
	  $valid->validateLocation($req->get('location'));
	}
  if (count($valid->exceptions) > 0) {
    echo '{"error":'. json_encode($valid->exceptions) .'}';
    exit;
  }

  // Setup SQL Query
  if ($valid->llon > $valid->ulon) { 
    // Allow for searching over the International Date Line.
	  $str_bbox = " MBRContains( GeomFromText(:loc1),location ) OR MBRContains( GeomFromText(:loc2),location )";
  } else {
	  $str_bbox = " MBRContains( GeomFromText(:loc),location )";
  }
  if ($valid->networks!='all') {
    $str_net = " AND network IN ('" . implode("','",$valid->networks) . "')";
  } else {
    $str_net ='';    
  }
  if ($valid->parameters!='all') {
    $str_par = " AND parameter_name IN ('" . implode("','",$valid->parameters) . "')";
  } else {
    $str_par ='';    
  }
  if (($valid->start_time) && ($valid->end_time)) {
    $str_date = " AND start_time<= :et AND end_time>= :st ";
  } else {
    $str_date ='';    
  }
  $sql = "SELECT network,name,description,X(location) as longitude,Y(location) as latitude,start_time,end_time 
  FROM ts_stations INNER JOIN ts_stations_parameters ON ts_stations.id = ts_stations_parameters.station_id
  WHERE $str_bbox $str_net $str_par $str_date 
  GROUP BY name 
  ORDER BY name";

  // Process Data
  try {
    $geojson = array( 'type' => 'FeatureCollection', 'features' => array());
    $db = DB::getInstance();
    $stmt = $db->prepare($sql);
    if ($valid->llon > $valid->ulon) {
      $stmt->bindValue("loc1","Polygon(($valid->llon $valid->llat,$valid->llon $valid->ulat,180 $valid->ulat,180 $valid->llat,$valid->llon $valid->llat))");
      $stmt->bindValue("loc2","Polygon((-180 $valid->llat,-180 $valid->ulat,$valid->ulon $valid->ulat,$valid->ulon $valid->llat,-180 $valid->llat))");
    } else {
      $stmt->bindValue("loc","Polygon(($valid->llon $valid->llat,$valid->llon $valid->ulat,$valid->ulon $valid->ulat,$valid->ulon $valid->llat,$valid->llon $valid->llat))");      
    }
    if (($valid->start_time) && ($valid->end_time)) {
      $stmt->bindValue("st",gmdate("Y-m-d H:i:s",$valid->start_time));
      $stmt->bindValue("et",gmdate("Y-m-d H:i:s",$valid->end_time));
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


?>