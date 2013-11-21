<?php
/**
 * Normalized Parameter List
 * This array maps normalized parameter names (generally following IOOS convention)  
 *   to their respective request and response parameters.  The request param is used in
 *   making a request for data from a web service, and the response param is generally 
 *   the column to be extracted from the CSV response
 */
$normalizedParameters = array(
  //  
  'ndbc' => array(
    'air_pressure' => array('request'=>'air_pressure_at_sea_level','response'=>'air_pressure_at_sea_level'),
    'air_temperature' => array('request'=>'air_temperature','response'=>'air_temperature'),
    'depth' => array('request'=>'sea_floor_depth_below_sea_surface','response'=>'sea_floor_depth_below_sea_surface'),
    'conductivity' => array('request'=>'sea_water_electrical_conductivity','response'=>'sea_water_electrical_conductivity'),
    'salinity' => array('request'=>'sea_water_salinity','response'=>'sea_water_salinity'),
    'water_temperature' => array('request'=>'sea_water_temperature','response'=>'sea_water_temperature'),
    'significant_wave_height' => array('request'=>'waves','response'=>'sea_surface_wave_significant_height'),
    'peak_wave_period' => array('request'=>'waves','response'=>'sea_surface_wave_peak_period'),
    'mean_wave_period' => array('request'=>'waves','response'=>'sea_surface_wave_mean_period'),
    'wave_to_direction' => array('request'=>'waves','response'=>'sea_surface_wave_to_direction'),
    'wind_from_direction' => array('request'=>'winds','response'=>'wind_from_direction'),
    'wind_speed' => array('request'=>'winds','response'=>'wind_speed'),
    'wind_gust' => array('request'=>'winds','response'=>'wind_speed_of_gust'),
  ),
  'co-ops' => array(
    'air_pressure' => array('request'=>'air_pressure','response'=>'air_pressure'),
    'air_temperature' => array('request'=>'air_temperature','response'=>'air_temperature'),
    'relative_humidity' => array('request'=>'relative_humidity','response'=>'relative_humidity'),
    'predicted_tide' => array('request'=>'sea_surface_height_amplitude_due_to_equilibrium_ocean_tide', 'response'=>'sea_surface_height_amplitude_due_to_equilibrium_ocean_tide'),
    'conductivity' => array('request'=>'sea_water_electrical_conductivity','response'=>'sea_water_electrical_conductivity'),
    'salinity' => array('request'=>'sea_water_salinity','response'=>'sea_water_salinity'),
    'water_temperature' => array('request'=>'sea_water_temperature','response'=>'sea_water_temperature'),
    'measured_tide' => array('request'=>'water_surface_height_above_reference_datum','response'=>'water_surface_height_above_reference_datum'),
    'wind_from_direction' => array('request'=>'winds','response'=>'wind_from_direction'),
    'wind_speed' => array('request'=>'winds','response'=>'wind_speed'),
    'wind_gust' => array('request'=>'winds','response'=>'wind_speed_of_gust'),
  ),
);

/**
 * Parameter List
 * This array contains the set of parameters and associated metadata used by the sytem.
 * It is used to initialize the parameters in the database, which can be modifed afterwards.
 */
$parameters = array(
  array(
    'name' => "air_pressure",
    'category' => "atmospheric",
    'description' => "Pressure exerted by overlying air",
    'units' => "hPa",
    'cf_url' => "http://mmisw.org/ont/cf/parameter/air_pressure",
    'ioos_url' => "http://mmisw.org/ont/ioos/parameter/air_pressure",
  ),
  array(
    'name' => "air_temperature",
    'category' => "atmospheric",
    'description' => "Temperature of air in situ",
    'units' => "celcius",
    'cf_url' => "http://mmisw.org/ont/cf/parameter/air_temperature",
    'ioos_url' => "http://mmisw.org/ont/ioos/parameter/air_temperature",
  ),
  array(
    'name' => "conductivity",
    'category' => "water_property",
    'description' => "Ability of a material to pass an electrical current. Inverse of resistance. In water, it is a proxy from which salinity is derived for water quality or to further derive water density.",
    'units' => "siemens | uS",
    'cf_url' => "http://mmisw.org/ont/cf/parameter/sea_water_electrical_conductivity",
    'ioos_url' => "http://mmisw.org/ont/ioos/parameter/conductivity",
  ),
  array(
    'name' => "depth",
    'category' => "relative_location",
    'description' => "Z-coordinate of observation in vertical distance below reference. Down is positive. (sea surface | geiod | ellipsoid | MSL | MLLW | AGL )",
    'units' => "meter",
    'cf_url' => "http://mmisw.org/ont/cf/parameter/depth",
    'ioos_url' => "http://mmisw.org/ont/ioos/parameter/depth",
  ),
  array(
    'name' => "mean_wave_period",
    'category' => "waves",
    'description' => "The average time it takes for successive wave crests to pass a single point. Mean wave period is estimated several ways from a measured wave spectrum.",
    'units' => "second",
    'cf_url' => "",
    'ioos_url' => "http://mmisw.org/ont/ioos/parameter/mean_wave_period",
  ),
  array(
    'name' => "measured_tide",
    'category' => "relative_location",
    'description' => "Height or altitude of the sea surface above specified reference.",
    'units' => "meter",
    'cf_url' => "http://mmisw.org/ont/cf/parameter/water_surface_height_above_reference_datum",
    'ioos_url' => "http://mmisw.org/ont/ioos/parameter/surface_elevation",
  ),
  array(
    'name' => "peak_wave_period",
    'category' => "waves",
    'description' => "The wave period as inverse of wave frequency at which the wave spectrum reaches its maximum. Sometimes referred to as the dominant wave period.",
    'units' => "s",
    'cf_url' => "http://mmisw.org/ont/cf/parameter/sea_surface_wave_period_at_variance_spectral_density_maximum",
    'ioos_url' => "http://mmisw.org/ont/ioos/parameter/peak_wave_period",
  ),
  array(
    'name' => "predicted_tide",
    'category' => "relative_location",
    'description' => "Height or altitude of the sea surface above specified reference.",
    'units' => "meter",
    'cf_url' => "http://mmisw.org/ont/cf/parameter/sea_surface_height_amplitude_due_to_equilibrium_ocean_tide",
    'ioos_url' => "http://mmisw.org/ont/ioos/parameter/surface_elevation",
  ),
  array(
    'name' => "salinity",
    'category' => "water_property",
    'description' => "Salinity is to the salt content of a water sample or body of water. The measure of salt content of a water sample follows UNESCO standards known as the Practical Salinity Scale (PSS) as the conductivity ratio of a sea water sample to a standard KCl solution. PSS is a ratio and has no units.",
    'units' => "psu",
    'cf_url' => "http://mmisw.org/ont/cf/parameter/sea_water_salinity",
    'ioos_url' => "http://mmisw.org/ont/ioos/parameter/salinity",
  ),
  array(
    'name' => "relative_humidity",
    'category' => "atmospheric",
    'description' => "Amount of moisture in the air as vapor relative to how much it can possibly hold at the same temperature.",
    'units' => "percent",
    'cf_url' => "",
    'ioos_url' => "http://mmisw.org/ont/ioos/parameter/relative_humidity",
  ),
  array(
    'name' => "significant_wave_height",
    'category' => "waves",
    'description' => "The mean height of highest third of the waves recorded during a sampling period. It is also estimated from measured wave energy, as four times the square-root of the first moment of the wave spectrum.",
    'units' => "meter",
    'cf_url' => "",
    'ioos_url' => "http://mmisw.org/ont/ioos/parameter/significant_wave_height",
  ),
  array(
    'name' => "water_temperature",
    'category' => "water_property",
    'description' => "In situ temperature of the ocean.",
    'units' => "celcius",
    'cf_url' => "http://mmisw.org/ont/cf/parameter/sea_water_temperature",
    'ioos_url' => "http://mmisw.org/ont/ioos/parameter/water_temperature",
  ),
  array(
    'name' => "wave_to_direction",
    'category' => "waves",
    'description' => "The direction toward which a wave propagates.",
    'units' => "degrees_true",
    'cf_url' => "http://mmisw.org/ont/cf/parameter/sea_surface_wave_to_direction",
    'ioos_url' => "http://mmisw.org/ont/ioos/parameter/wave_to_direction",
  ),
  array(
    'name' => "wind_from_direction",
    'category' => "atmospheric",
    'description' => "Direction from which wind is blowing. Meteorological Convention. Wind is motion of air relative to the surface of the earth.",
    'units' => "degrees_true",
    'cf_url' => "http://mmisw.org/ont/cf/parameter/wind_from_direction",
    'ioos_url' => "http://mmisw.org/ont/ioos/parameter/wind_from_direction",
  ),
  array(
    'name' => "wind_speed",
    'category' => "atmospheric",
    'description' => "Magnitude of wind velocity. Wind is motion of air relative to the surface of the earth.",
    'units' => "m s-1",
    'cf_url' => "http://mmisw.org/ont/cf/parameter/wind_speed",
    'ioos_url' => "http://mmisw.org/ont/ioos/parameter/wind_speed",
  ),
  array(
    'name' => "wind_gust",
    'category' => "atmospheric",
    'description' => "Maximum instantaneous wind speed (usually no more than but not limited to 10 seconds) within a sample averaging interval. Wind is motion of air relative to the surface of the earth.",
    'units' => "m s-1",
    'cf_url' => "http://mmisw.org/ont/cf/parameter/wind_speed_of_gust",
    'ioos_url' => "http://mmisw.org/ont/ioos/parameter/wind_gust",
  ),
);



?>