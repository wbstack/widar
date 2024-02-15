<?PHP

error_reporting(E_ERROR|E_CORE_ERROR|E_COMPILE_ERROR); # |E_ALL
//ini_set('display_errors', 'On');
set_time_limit ( 3*60 ) ; // Seconds

// Session INI settings START
if(getenv('PHP_SESSION_SAVE_HANDLER')) {
    ini_set( 'session.save_handler', getenv('PHP_SESSION_SAVE_HANDLER') );
}
if(getenv('PHP_SESSION_SAVE_PATH')) {
    ini_set( 'session.save_path', getenv('PHP_SESSION_SAVE_PATH') );
}
if(
getenv('PHP_SESSION_SAVE_HANDLER') == "redis" &&
getenv('PHP_SESSION_SAVE_HANDLER_REDIS_HOST') &&
getenv('PHP_SESSION_SAVE_HANDLER_REDIS_PORT') &&
getenv('PHP_SESSION_SAVE_HANDLER_REDIS_DATABASE') &&
getenv('PHP_SESSION_SAVE_HANDLER_REDIS_AUTH') &&
getenv('PHP_SESSION_SAVE_HANDLER_REDIS_PREFIX')
){
    ini_set( 'session.save_path', "tcp://".getenv('PHP_SESSION_SAVE_HANDLER_REDIS_HOST').":".getenv('PHP_SESSION_SAVE_HANDLER_REDIS_PORT')."?database=".getenv('PHP_SESSION_SAVE_HANDLER_REDIS_DATABASE')."&auth=".getenv('PHP_SESSION_SAVE_HANDLER_REDIS_AUTH')."&prefix=".getenv('PHP_SESSION_SAVE_HANDLER_REDIS_PREFIX') );
}
// Session INI settings END

$botmode = isset ( $_REQUEST['botmode'] ) ;

/*
header('Access-Control-Allow-Origin: http://petscan.wmflabs.org');
header('Access-Control-Allow-Origin: https://petscan.wmflabs.org');
header('Access-Control-Allow-Origin: http://petscan-dev.wmflabs.org');
header('Access-Control-Allow-Origin: https://petscan-dev.wmflabs.org');
*/

require_once ( 'php/Widar.php' ) ;
$widar = new Widar ( 'widar', 60*60*24*30*3 ); // make all cookies expire in three months
if ( !$widar->render_reponse ( $botmode ) ) $widar->output_widar_main_page () ;

?>
