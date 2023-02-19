<?php
/** ggp_testcode.php
* @author arakawa asuka
* @date 2021/11/26
*/

add_shortcode('ggp_testcode','ggp_testcode');
require_once(get_stylesheet_directory().'/lib/ggp_common.php');

if (! function_exists( 'ggp_testcode' ) ):
function ggp_testcode(){

ob_start();

?>

<form method="post" action="/ggp_graph/">
  <input type="hidden" name="team_no" value="0">
  <input type="hidden" name="earth_no" value="0">
  <input type="submit" value="submit">
</form>

<?php
//バッファした出力を変数に格納
$return_html = ob_get_contents();
ob_end_clean();

return $return_html;

}
endif;

add_shortcode('error','error');

if (! function_exists( 'error' ) ):
function error(){
session_start();
$message = $_SESSION['error_message'];
$dump = $_SESSION['dump'];

unset($_SESSION['error_message']);
unset($_SESSION['dump']);


ob_start();
?>
<?php
echo $message;
if(isset($dump) )var_dump($dump);

//バッファした出力を変数に格納
$return_html = ob_get_contents();
ob_end_clean();

return $return_html;

}
endif;

add_shortcode('shortcode_test','shortcode_test');
function shortcode_test(){
  return "<p>test</p>";
}

