<html>
<head>
<title>EPE Ocean Data Services</title>
<style type="text/css">
body {
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size: 14px;
  line-height: 20px;
  color: #333333;
  background-color: #ffffff;
  width: 640px;
  margin-right: auto;
  margin-left: auto;
  *zoom: 1;
  background: #eee;
}

}
a {
  color: #0088cc;
  text-decoration: none;
}
a:hover,
a:focus {
  color: #005580;
  text-decoration: underline;
}
#container {
  background:#fff;
  padding: 10px;
}
h3 {
  text-decoration: underline;
}
</style>
</head>
<body>
<div id="container">

<h1>EPE Ocean Data Services</h1>
<p><em>Access oceanographic data and information the easy way.</em></p>

<p>This server provides an easy way to access information and data from a number of popular oceanographic web services.  It was developed to support the educational visualization tools of the Ocean Observatories Initiative (OOI) education portal.</p>


<h2>Time-Series Service</h2>
<p>With the Time-Series API, you can retrieve station information and data from a number of popular oceanographic web services.  The following services are currently supported: </p>
<ul>
  <li><a href="http://sdf.ndbc.noaa.gov/sos/">NDBC SOS</a></li>
  <li><a href="http://opendap.co-ops.nos.noaa.gov/ioos-dif-sos/">NOAA CO-OPS</a></li>
</ul>

<h3>Full Parameter List: <a href="parameters">/parameters</a></h3>
<p>A full listing of all parameters available in the system.  Returns a json array of all parameters</p>

<h3>Parameter Details: /parameters/(name)</h3>
<p>Details for the specified parameter, given as (name).  Returns a json array of the selected parameter.</p>

<h3>Full Station List: <a href="stations">/stations</a></h3>
<p>A full listing of all stations currently in the system. Returns a geojson array of all stations.</p>

<h3>Station Details: /stations/(network)/(name)</h3>
<p>Details for the specified station, given as (network)/(name).  Returns a json array of the selected station.</p>
<p>(network) should be NDBC or CO-OPS.  (name) is the station's name, which can be found in the station listing.</p>

<h3>Station Search: /stations/search</h3>
<p>Search for stations within the specified criteria.  Returns a geojson array of all stations found.</p>
<p>Optional parameters:</p>
<ul>
  <li><strong>networks</strong>: comma separated list of desired networks</li>
  <li><strong>parameters</strong>: comma separated list of desired parameters (returns any matches, i.e. not just intersecting matches)</li>
  <li><strong>location</strong>: lon_min,lat_min,lon_max,lat_max</li>
  <li><strong>start_time</strong>: Either 0000-00-00T00:00Z or number of days before end_time</li>
  <li><strong>end_time</strong>: Either 0000-00-00T00:00Z or now.  Note, both start_time and end_time must be specified if either is given.</li>
</ul>

<h3>Data: /data.php</h3>
<p>Request data for a specific station.  Returns a csv file of time and observed values.</p>
<p>Optional parameters:</p>
<ul>
  <li><strong>network</strong>: comma separated list of desired networks</li>
  <li><strong>station</strong>: comma separated list of desired networks</li>
  <li><strong>parameter</strong>: comma separated list of desired parameters (returns any matches, i.e. not just intersecting matches)</li>
  <li><strong>start_time</strong>: Either 0000-00-00T00:00Z or number of days before end_time</li>
  <li><strong>end_time</strong>: Either 0000-00-00T00:00Z or 'now'.  Note, both start_time and end_time must be specified if either is given.</li>
  <li><strong>type</strong>: (optional): raw (default)</li>
</ul>

</div>
</body></html>