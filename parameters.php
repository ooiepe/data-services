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

?>