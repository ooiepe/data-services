<?php include('header.php');?>

<h1>EPE Ocean Data Services</h1>
<p><em>Access oceanographic data and information the easy way.</em></p>

<p>This server provides an easy way to access information and data from a number of popular oceanographic web services.  It was developed to support the educational visualization tools of the Ocean Observatories Initiative (OOI) education portal.</p>


<h2>Time-Series Service</h2>
<p>With the Time-Series API, you can retrieve station information and data from a number of popular oceanographic web services.  The following services are currently supported: </p>
<ul>
  <li><a href="http://sdf.ndbc.noaa.gov/sos/">NDBC SOS</a></li>
  <li><a href="http://opendap.co-ops.nos.noaa.gov/ioos-dif-sos/">NOAA CO-OPS</a></li>
</ul>

<h3>Full Parameter List</h3>
<p>Example: <a href="parameters">/parameters</a></p>
<p>A full listing of all parameters available in the system.</p>
<p>Returns a json array of all parameters</p>

<h3>Parameter Details</h3>
<p>Example: <a href="parameters/air_temperature">/parameters/air_temperature</a></p>
<p>Details for the specified parameter, given as /parameters/(name).</p>
<p>Returns a json array of the selected parameter.</p>

<h3>Full Station List </h3>
<p>Example: <a href="stations">/stations</a></p>
<p>A full listing of all stations currently in the system.</p>
<p>Returns a geojson array of all stations.</p>

<h3>Station Details</h3>
<p>Example: <a href="stations/NDBC/44025">/stations/NDBC/44025</a></p>
<p>Details for the specified station, given as /stations/(network)/(name).  (network) should be NDBC or CO-OPS.  (name) is the station's name, which can be found in the station listing.</p>
<p>Returns a json array of the selected station.</p>

<h3>Station Search</h3>
<p>Example: <a href="stations/search?networks=CO-OPS&parameters=salinity&location=-77,35,-69,42&start_time=1&end_time=now">/stations/search?networks=CO-OPS&amp;parameters=salinity&amp;location=-77,35,-69,42&amp;start_time=1&amp;end_time=now</a></p>
<p>Search for stations within the specified criteria.</p>
<p>Optional parameters:</p>
<ul>
  <li><strong>networks</strong>: comma separated list of desired networks</li>
  <li><strong>parameters</strong>: comma separated list of desired parameters (returns any matches, i.e. not just intersecting matches)</li>
  <li><strong>location</strong>: lon_min,lat_min,lon_max,lat_max</li>
  <li><strong>start_time</strong>: Either 0000-00-00T00:00Z or number of days before end_time</li>
  <li><strong>end_time</strong>: Either 0000-00-00T00:00Z or now.  Note, both start_time and end_time must be specified if either is given.</li>
</ul>
<p>Returns a geojson array of all stations found.</p>

<h3>Time-Series Data</h3>
<p>Example: <a href="timeseries?network=NDBC&station=44025&parameter=air_temperature&start_time=5&end_time=now">/timeseries?network=NDBC&amp;station=44025&amp;parameter=air_temperature&amp;start_time=5&amp;end_time=now</a></p>
<p>Example: <a href="timeseries?network=CO-OPS&station=8635750&parameter=air_temperature&start_time=1&end_time=2013-07-01">/timeseries?network=CO-OPS&amp;station=8635750&amp;parameter=air_temperature&amp;start_time=1&amp;end_time=2013-07-01</a></p>

<p>Request time-series data for a specific station and parameter over the specified time range.</p>
<p>Optional parameters:</p>
<ul>
  <li><strong>network</strong>: should be NDBC or CO-OPS</li>
  <li><strong>station</strong>: is the station's name, which can be found in the station listing</li>
  <li><strong>parameter</strong>: desired parameter (currently, only one request at a time is supported)</li>
  <li><strong>start_time</strong>: Either 0000-00-00T00:00Z or number of days before end_time</li>
  <li><strong>end_time</strong>: Either 0000-00-00T00:00Z or 'now'.  Note, both start_time and end_time must be specified if either is given.</li>
  <li><strong>type</strong>: (optional): raw (default)</li>
</ul>
<p>Returns a csv file of time and observed values.</p>

<?php include('footer.php');?>