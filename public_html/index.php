<?PHP

error_reporting(E_ERROR|E_CORE_ERROR|E_COMPILE_ERROR); # |E_ALL
ini_set('display_errors', 'On');
set_time_limit ( 3*60 ) ; // Seconds

$botmode = isset ( $_REQUEST['botmode'] ) ;

/*
header('Access-Control-Allow-Origin: http://petscan.wmflabs.org');  
header('Access-Control-Allow-Origin: https://petscan.wmflabs.org');  
header('Access-Control-Allow-Origin: http://petscan-dev.wmflabs.org');  
header('Access-Control-Allow-Origin: https://petscan-dev.wmflabs.org');  
*/

require_once ( 'php/Widar.php' ) ;
$widar = new Widar ( 'widar' ) ;
if ( !$widar->render_reponse ( $botmode ) ) $widar->output_widar_main_page () ;

?>