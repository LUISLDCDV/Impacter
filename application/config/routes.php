<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Base Methods
$route['default_controller'] = 'Base';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
// Base Methods END

// Views
$route['sinai'] = 'Importer/Main';
$route['sinai/result'] = 'Importer/Result';
$route['sinai/process'] = 'Importer/Process';
// Views END 


// Request and Actions Methods
$route['Importer_SINAI'] = 'Importer';
$route['Importer/GetInfractionsByDate/'] = 'Importer/GetInfractionsByDate/';
// Request and Actions Methods END