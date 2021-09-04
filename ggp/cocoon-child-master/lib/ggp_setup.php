<?php
/** ggp_setup.php
* @author arakawa asuka
* @date 2021/05/02
*/

require_once(get_stylesheet_directory().'/lib/db.php');

add_action('admin_menu', 'ggp_setup');

function is_btn_valid(){
$earth_no = $_POST['earth_no'];
$team_no = $_POST['team_no'];
$is_general = $_POST['is_general'];

//各フェーズの米の量を取得
$get_db_table_ggp_action_rice = get_db_table_ggp_action_rice($earth_no, $team_no);
$after_grow_quantity =  (int)$get_db_table_ggp_action_rice[0]->grow;
$after_to_factory_quantity =  (int)$get_db_table_ggp_action_rice[0]->to_factory;
$after_make_quantity =  (int)$get_db_table_ggp_action_rice[0]->make;

//配列のコピー
$is_btn_valid = get_option('ggp_cardinfo');

foreach($is_btn_valid as $key => &$each_card){
  foreach($each_card as &$value){

    // NULL or 1 を enable, disableに置き換え
    if($value['is_valid'] == NULL ){
      $value['is_valid'] = 'disabled';
    }else{
      $value['is_valid'] = 'enabled';
    }

    // チームリーダー以外はボタンをすべて無効化
    if($is_general == 'disabled'){
      $value['is_valid'] = 'disabled';
    }
    // 生産量が０以下の場合はボタンを無効化
    else if ($key == 'to_factory' && $after_grow_quantity <= 0){
      $value['is_valid'] = 'disabled';
    }
    else if ($key == 'make' && $after_to_factory_quantity <= 0){
      $value['is_valid'] = 'disabled';
    }
    else if ($key == 'to_store' && $after_make_quantity <= 0){
      $value['is_valid'] = 'disabled';
    }
  }
}
return $is_btn_valid;

}

//カードが無効になっている場合に背景をグレーに設定する
function is_card_disabled($is_disabled){
if($is_disabled == NULL){
  return 'style="background-color:#CCCCCC;"';
}
}

//非同期通信（地球毎のメッセージ取得）
function my_wp_get_ggp_msg_ajax() {

  $earth_no = $_POST['earth_no'];
  $ggp_message = get_db_table_ggp_message($earth_no);

  $nonce = $_REQUEST['nonce'];
  if ( wp_verify_nonce( $nonce, 'get_ggp_msg-nonce' ) ) {
    $response_msg = "";
    for($i = 0; $i < count($ggp_message); $i++) {
      $response_msg .= '<p class="p-ggp-msg">' . $ggp_message[$i]->msg . '</p>';
    }
    echo $response_msg;
  }
  die();
}
add_action( 'wp_ajax_get_ggp_msg_ajax', 'my_wp_get_ggp_msg_ajax' );
add_action( 'wp_ajax_nopriv_get_ggp_msg_ajax', 'my_wp_get_ggp_msg_ajax' );

//非同期通信（地球毎の最新メッセージのみ取得）
function my_wp_get_ggp_msg_newest_ajax() {

  $earth_no = $_POST['earth_no'];
  $ggp_message = get_db_table_ggp_message($earth_no);

  $nonce = $_REQUEST['nonce'];
  if ( wp_verify_nonce( $nonce, 'get_ggp_msg_newest-nonce' ) ) {
    $response_msg = $ggp_message[0]->msg;
    echo $response_msg;
  }
  die();
}
add_action( 'wp_ajax_get_ggp_msg_newest_ajax', 'my_wp_get_ggp_msg_newest_ajax' );
add_action( 'wp_ajax_nopriv_get_ggp_msg_newest_ajax', 'my_wp_get_ggp_msg_newest_ajax' );

//非同期通信（地球情報の更新）
function my_wp_get_ggp_earth_info_ajax() {
  $earth_no = $_POST['earth_no'];
  define('TABLE_NAME_GGP_TEAM',  $wpdb->prefix . 'ggp_team');
  $ggp_team = get_db_table_records_ggp(TABLE_NAME_GGP_TEAM,"earth_no",$earth_no);

  $nonce = $_REQUEST['nonce'];
  if ( wp_verify_nonce( $nonce, 'get_ggp_earth_info-nonce' ) ) {
    $response_msg = '<table class="team_info">
    <tr><th width="100px"></th>
    ';
    for($i=0; $i < count($ggp_team); $i++) {
    $response_msg .= '<th>' . $ggp_team[$i]->teamname . '</th>';
    }
    $response_msg .= '</tr>
    <tr><td><i class="fas fa-undo"></i>ターン</td>
    ';
    for($i=0; $i < count($ggp_team); $i++) {
    $response_msg .=  '<td>'. $ggp_team[$i]->turn .'ターン目</td>';
    }
    $response_msg .= '</tr>
    <tr><td><i class="fas fa-coins"></i>お金</td>
    ';
    for($i=0; $i < count($ggp_team); $i++) {
    $response_msg .= '<td>' . $ggp_team[$i]->money .'万円</td>';
    }
    $response_msg .= '</tr>
    <tr><td><i class="fas fa-skull-crossbones"></i>二酸化炭素</td>
    ';
    for($i=0; $i < count($ggp_team); $i++) {
    $response_msg .= '<td>' . $ggp_team[$i]->co2 . 'kg</td>';
    }
    $response_msg .= '
    </tr>
    </table>
    ';
    echo $response_msg;
  }
  die();
}
add_action( 'wp_ajax_get_ggp_earth_info_ajax', 'my_wp_get_ggp_earth_info_ajax' );
add_action( 'wp_ajax_nopriv_get_ggp_earth_info_ajax', 'my_wp_get_ggp_earth_info_ajax' );

//非同期通信（地球毎の二酸化炭素排出量取得）
/*
function my_wp_get_ggp_earth_co2_ajax() {

  $earth_no = $_POST['earth_no'];

  define('TABLE_NAME_GGP_EARTH',  $wpdb->prefix . 'ggp_earth');
  $ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);

  $nonce = $_REQUEST['nonce'];
  if ( wp_verify_nonce( $nonce, 'get_ggp_earth_co2-nonce' ) ) {
    echo $ggp_earth[0]->co2;
  }
  die();
}
add_action( 'wp_ajax_get_ggp_earth_co2_ajax', 'my_wp_get_ggp_earth_co2_ajax' );
add_action( 'wp_ajax_nopriv_get_ggp_earth_co2_ajax', 'my_wp_get_ggp_earth_co2_ajax' );
*/

//非同期通信（チームの詳細情報取得）
function my_wp_get_ggp_team_description_ajax() {
  $earth_no = $_POST['earth_no'];
  $team_no = $_POST['team_no'];
  define('TABLE_NAME_GGP_TEAM',  $wpdb->prefix . 'ggp_team');
  $ggp_team = get_db_table_records_ggp(TABLE_NAME_GGP_TEAM,"earth_no",$earth_no);

  $nonce = $_REQUEST['nonce'];
  if ( wp_verify_nonce( $nonce, 'get_ggp_team_description-nonce' ) ) {
    $response_msg = "";
    $response_msg .= '<i class="fas fa-undo"></i>ターン：'. $ggp_team[$team_no]->turn .'ターン目<br>';
    $response_msg .= '<i class="fas fa-coins"></i>お金：' . $ggp_team[$team_no]->money .'万円<br>';
    $response_msg .= '<i class="fas fa-skull-crossbones"></i>CO2：'. $ggp_team[$team_no]->co2 .'kg<br>';
    echo $response_msg;
  }
  die();
}
add_action( 'wp_ajax_get_ggp_team_description_ajax', 'my_wp_get_ggp_team_description_ajax' );
add_action( 'wp_ajax_nopriv_get_ggp_team_description_ajax', 'my_wp_get_ggp_team_description_ajax' );

//非同期通信（植樹イベント発生）
function my_wp_check_ggp_event_tree_ajax() {
  $earth_no = $_POST['earth_no'];
  $nonce = $_REQUEST['nonce'];
  $response_msg = "";
  if ( wp_verify_nonce( $nonce, 'check_ggp_event_tree-nonce' ) ) {
    $response_msg = get_option('ggp_event_tree');
    echo $response_msg;
  }
  die();
}
add_action( 'wp_ajax_check_ggp_event_tree_ajax', 'my_wp_check_ggp_event_tree_ajax' );
add_action( 'wp_ajax_nopriv_check_ggp_event_tree_ajax', 'my_wp_check_ggp_event_tree_ajax' );

//非同期通信（売り上げ増加イベント発生）
function my_wp_check_ggp_event_sales_ajax() {
  $earth_no = $_POST['earth_no'];
  $nonce = $_REQUEST['nonce'];
  $response_msg = "";

  if ( wp_verify_nonce( $nonce, 'check_ggp_event_sales-nonce' ) ) {
    $response_msg = get_option('ggp_event_sales');
    echo $response_msg;
  }
  die();
}
add_action( 'wp_ajax_check_ggp_event_sales_ajax', 'my_wp_check_ggp_event_sales_ajax' );
add_action( 'wp_ajax_nopriv_check_ggp_event_sales_ajax', 'my_wp_check_ggp_event_sales_ajax' );

function ggp_setup(){

  add_menu_page('GGPゲーム設定','GGPゲーム設定','manage_options','ggp_setup','add_ggp_setup','dashicons-admin-generic',4);
  add_submenu_page('ggp_setup','ゲーム設定','ゲーム設定','manage_options','ggp_game_setup','add_ggp_game_setup',1);
  add_submenu_page('ggp_setup','チーム設定','チーム設定','manage_options','ggp_team_setup','add_ggp_team_setup',2);
  remove_submenu_page('ggp_setup','ggp_setup');
  add_action('admin_init','register_ggp_game_setup');
  add_action('admin_init','register_ggp_team_setup');

}

// 特になにも表示しない
function add_ggp_setup(){
}

function register_ggp_game_setup(){
  register_setting('ggp_game_setup_group','ggp_quota_co2');
  register_setting('ggp_game_setup_group','ggp_init_money');
  register_setting('ggp_game_setup_group','ggp_init_sales');
  register_setting('ggp_game_setup_group','ggp_init_earth');
  register_setting('ggp_game_setup_group','ggp_init_perteam');
  register_setting('ggp_game_setup_group','ggp_cardinfo');
  register_setting('ggp_game_setup_group','ggp_cardcount');
  register_setting('ggp_game_setup_group','ggp_reduction_valid');
  register_setting('ggp_game_setup_group','ggp_event_tree');
  register_setting('ggp_game_setup_group','ggp_event_sales');
  register_setting('ggp_game_setup_group','ggp_event_tree_magnification');
  register_setting('ggp_game_setup_group','ggp_event_sales_magnification');

}

function add_ggp_game_setup(){

$ggp_cardinfo = get_option('ggp_cardinfo');
$ggp_cardcount = get_option('ggp_cardcount');
$ggp_reduction_valid = get_option('ggp_reduction_valid');
$ggp_event_tree = get_option('ggp_event_tree');
$ggp_event_sales = get_option('ggp_event_sales');
$ggp_event_tree_magnification = "2";
$ggp_event_sales_magnification = "0.1";


// $ggp_cardinfo = "";
if($ggp_cardinfo == NULL || $ggp_cardinfo == "" ){
  $ggp_cardcount =Array('grow'=>5,'to_factory'=>5, 'make'=>5, 'to_store'=>5, 'reduction'=>3);
  $ggp_cardinfo = Array();

  foreach($ggp_cardcount as $key => $value){
    $card_array = Array();
    for($i = 0; $i < (int)$value; $i++){
      array_push($card_array, Array('description'=>'','name'=>'','url'=>'','money'=>'','co2'=>'','turn'=>'','is_visible'=>'1', 'is_visible'=>'1') );
    }
    $ggp_cardinfo[$key] = $card_array;
  }

}

$tree_card_no = 0;
// key=treeとなっているカードの番号を探索する
for($tree_card_no = 0; $tree_card_no < count($ggp_cardinfo['reduction']); $tree_card_no++){
  if($ggp_cardinfo['reduction'][$tree_card_no]['key'] == 'tree') break;
}

?>
<div class="wrap">
  <h2>ゲーム設定</h2>
  <form method="post" action="options.php" enctype="multipart/form-data" encoding="multipart/form-data">
    <?php
    settings_fields('ggp_game_setup_group');
    do_settings_sections('ggp_game_setup_group');
     ?>
    <div class="metabox-holder">
      <div class="postbox">
        <h3 class="hndle"><span>ゲーム初期設定</span></h3>
        <div class="inside">
          <div class="main">
          <table>
          <tr><td>CO2排出上限</td><td><input type="text" id="ggp_quota_co2" name="ggp_quota_co2" value="<?php echo get_option('ggp_quota_co2'); ?>">トン（400kg／チームが設定目安）</td></tr>
          <tr><td>スタート時の所持金額</td><td><input type="text" id="ggp_init_money" name="ggp_init_money" value="<?php echo get_option('ggp_init_money'); ?>">万円</td></tr>
          <tr><td>売上設定（５トン毎）</td><td><input type="text" id="ggp_init_sales" name="ggp_init_sales" value="<?php echo get_option('ggp_init_sales'); ?>">万円</td></tr>
          <tr><td>地球の個数</td><td><input type="text" id="ggp_init_earth" name="ggp_init_earth" value="<?php echo get_option('ggp_init_earth'); ?>">個</td></tr>
          <tr><td>地球毎のチーム数</td><td><input type="text" id="ggp_init_perteam" name="ggp_init_perteam" value="<?php echo get_option('ggp_init_perteam'); ?>">チーム／地球</td></tr>
          </table>
          </div>
        </div>
      </div>
    </div>
    <div class="metabox-holder">
      <div class="postbox">
        <h3 class="hndle"><span>イベント設定</span></h3>
        <div class="inside">
          <div class="main">
          <p><input type="checkbox" id="ggp_event_tree" name="ggp_event_tree" value="1" <?php checked(1, $ggp_event_tree); ?>>&nbsp;植樹の効果<?=$ggp_event_tree_magnification ?>倍（チェックをつけると有効）<?php if($ggp_event_tree != NULL){ ?> <font color="red">植林の効果<?=$ggp_event_tree_magnification ?>倍発動中(<?=$ggp_cardinfo['reduction'][$tree_card_no]['co2'] ?>kg/毎ターンから<?=$ggp_cardinfo['reduction'][$tree_card_no]['co2']*$ggp_event_tree_magnification ?>kg/毎ターンに増加）</font> <?php }; ?>
            <input type="hidden" id="ggp_event_tree_magnification" name="ggp_event_tree_magnification" value=<?=$ggp_event_tree_magnification ?>>
          <p><input type="checkbox" id="ggp_event_sales" name="ggp_event_sales" value="1" <?php checked(1, $ggp_event_sales); ?>>&nbsp;売り上げ倍増：植樹回数&#x2613;<?=$ggp_event_sales_magnification ?>倍（チェックをつけると有効）<?php if($ggp_event_sales != NULL){ ?> <font color="red">売り上げ倍増発動中(植樹回数&#x2613;<?=$ggp_event_sales_magnification; ?>倍に増加）</font> <?php }; ?>
            <input type="hidden" id="ggp_event_sales_magnification" name="ggp_event_sales_magnification" value=<?=$ggp_event_sales_magnification ?>>
          </div>
        </div>
      </div>
    </div>    <div class="metabox-holder">
      <div class="postbox">
        <h3 class="hndle"><span>カード設定</span></h3>
        <div class="inside">
          <div class="main">
          <h4><span>米を育てる</span></h4>
          <table class="ggp_game_table">
          <tr><th style="width:10%;">名称</th><th style="width:20%;">説明</th><th style="width:10%;">画像ファイル名</th><th style="width:5%;">必要金額[万円]</th><th style="width:5%;">米の量[トン]</th><th style="width:5%;">CO2排出量[kg]</th><th style="width:5%;">必要ターン数</th><th style="width:2%;">表示</th><th style="width:2%;">有効</th></tr>
          <?php
          for($i = 0; $i < count($ggp_cardinfo['grow']); $i++){ ?>
          <tr>
          <td><input type="text" id="ggp_cardinfo[grow][<?php echo $i; ?>][name]" name="ggp_cardinfo[grow][<?php echo $i; ?>][name]" value="<?php echo $ggp_cardinfo['grow'][$i]['name']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[grow][<?php echo $i; ?>][description]" name="ggp_cardinfo[grow][<?php echo $i; ?>][description]" value="<?php echo $ggp_cardinfo['grow'][$i]['description']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[grow][<?php echo $i; ?>][url]" name="ggp_cardinfo[grow][<?php echo $i; ?>][url]" value="<?php echo $ggp_cardinfo['grow'][$i]['url']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[grow][<?php echo $i; ?>][money]" name="ggp_cardinfo[grow][<?php echo $i; ?>][money]" value="<?php echo $ggp_cardinfo['grow'][$i]['money']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[grow][<?php echo $i; ?>][rice]" name="ggp_cardinfo[grow][<?php echo $i; ?>][rice]" value="<?php echo $ggp_cardinfo['grow'][$i]['rice']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[grow][<?php echo $i; ?>][co2]" name="ggp_cardinfo[grow][<?php echo $i; ?>][co2]" value="<?php echo $ggp_cardinfo['grow'][$i]['co2']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[grow][<?php echo $i; ?>][turn]" name="ggp_cardinfo[grow][<?php echo $i; ?>][turn]" value="<?php echo $ggp_cardinfo['grow'][$i]['turn']; ?>" style="width:100%;"></td>
          <td style="text-align:center;"><input type="checkbox" id="ggp_cardinfo[grow][<?php echo $i; ?>][is_valid]" name="ggp_cardinfo[grow][<?php echo $i; ?>][is_visible]" value="1" <?php checked(1, $ggp_cardinfo['grow'][$i]['is_visible']); ?>"></td>
          <td style="text-align:center;"><input type="checkbox" id="ggp_cardinfo[grow][<?php echo $i; ?>][is_valid]" name="ggp_cardinfo[grow][<?php echo $i; ?>][is_valid]" value="1" <?php checked(1, $ggp_cardinfo['grow'][$i]['is_valid']); ?>"></td>
          </tr>
          <?php } ?>
          </table>
          </div>
          <div class="main">
          <h4><span>運搬（田んぼから工場）</span></h4>
          <table class="ggp_game_table">
          <tr><th style="width:10%;">名称</th><th style="width:20%;">説明</th><th style="width:10%;">画像ファイル名</th><th style="width:5%;">必要金額[万円]</th><th style="width:5%;">米の量[トン]</th><th style="width:5%;">CO2排出量[kg]</th><th style="width:5%;">必要ターン数</th><th style="width:2%;">表示</th><th style="width:2%;">有効</th></tr>
          <?php
          for($i = 0; $i < count($ggp_cardinfo['to_factory']); $i++){ ?>
          <tr>
          <td><input type="text" id="ggp_cardinfo[to_factory][<?php echo $i; ?>][name]" name="ggp_cardinfo[to_factory][<?php echo $i; ?>][name]" value="<?php echo $ggp_cardinfo['to_factory'][$i]['name']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[to_factory][<?php echo $i; ?>][description]" name="ggp_cardinfo[to_factory][<?php echo $i; ?>][description]" value="<?php echo $ggp_cardinfo['to_factory'][$i]['description']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[to_factory][<?php echo $i; ?>][url]" name="ggp_cardinfo[to_factory][<?php echo $i; ?>][url]" value="<?php echo $ggp_cardinfo['to_factory'][$i]['url']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[to_factory][<?php echo $i; ?>][money]" name="ggp_cardinfo[to_factory][<?php echo $i; ?>][money]" value="<?php echo $ggp_cardinfo['to_factory'][$i]['money']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[to_factory][<?php echo $i; ?>][rice]" name="ggp_cardinfo[to_factory][<?php echo $i; ?>][rice]" value="<?php echo $ggp_cardinfo['to_factory'][$i]['rice']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[to_factory][<?php echo $i; ?>][co2]" name="ggp_cardinfo[to_factory][<?php echo $i; ?>][co2]" value="<?php echo $ggp_cardinfo['to_factory'][$i]['co2']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[to_factory][<?php echo $i; ?>][turn]" name="ggp_cardinfo[to_factory][<?php echo $i; ?>][turn]" value="<?php echo $ggp_cardinfo['to_factory'][$i]['turn']; ?>" style="width:100%;"></td>
          <td style="text-align:center;"><input type="checkbox" id="ggp_cardinfo[to_factory][<?php echo $i; ?>][is_visible]" name="ggp_cardinfo[to_factory][<?php echo $i; ?>][is_visible]" value="1" <?php checked(1, $ggp_cardinfo['to_factory'][$i]['is_visible']); ?>"></td>
          <td style="text-align:center;"><input type="checkbox" id="ggp_cardinfo[to_factory][<?php echo $i; ?>][is_valid]" name="ggp_cardinfo[to_factory][<?php echo $i; ?>][is_valid]" value="1" <?php checked(1, $ggp_cardinfo['to_factory'][$i]['is_valid']); ?>"></td>
          </tr>
          <?php } ?>
          </table>
          </div>
          <div class="main">
          <h4><span>工場</span></h4>
          <table class="ggp_game_table">
          <tr><th style="width:10%;">名称</th><th style="width:20%;">説明</th><th style="width:10%;">画像ファイル名</th><th style="width:5%;">必要金額[万円]</th><th style="width:5%;">米の量[トン]</th><th style="width:5%;">CO2排出量[kg]</th><th style="width:5%;">必要ターン数</th><th style="width:2%;">表示</th><th style="width:2%;">有効</th></tr>
          <?php
          for($i = 0; $i < count($ggp_cardinfo['make']); $i++){ ?>
          <tr>
          <td><input type="text" id="ggp_cardinfo[make][<?php echo $i; ?>][name]" name="ggp_cardinfo[make][<?php echo $i; ?>][name]" value="<?php echo $ggp_cardinfo['make'][$i]['name']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[make][<?php echo $i; ?>][description]" name="ggp_cardinfo[make][<?php echo $i; ?>][description]" value="<?php echo $ggp_cardinfo['make'][$i]['description']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[make][<?php echo $i; ?>][url]" name="ggp_cardinfo[make][<?php echo $i; ?>][url]" value="<?php echo $ggp_cardinfo['make'][$i]['url']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[make][<?php echo $i; ?>][money]" name="ggp_cardinfo[make][<?php echo $i; ?>][money]" value="<?php echo $ggp_cardinfo['make'][$i]['money']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[make][<?php echo $i; ?>][rice]" name="ggp_cardinfo[make][<?php echo $i; ?>][rice]" value="<?php echo $ggp_cardinfo['make'][$i]['rice']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[make][<?php echo $i; ?>][co2]" name="ggp_cardinfo[make][<?php echo $i; ?>][co2]" value="<?php echo $ggp_cardinfo['make'][$i]['co2']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[make][<?php echo $i; ?>][turn]" name="ggp_cardinfo[make][<?php echo $i; ?>][turn]" value="<?php echo $ggp_cardinfo['make'][$i]['turn']; ?>" style="width:100%;"></td>
          <td style="text-align:center;"><input type="checkbox" id="ggp_cardinfo[make][<?php echo $i; ?>][is_visible]" name="ggp_cardinfo[make][<?php echo $i; ?>][is_visible]" value="1" <?php checked(1, $ggp_cardinfo['make'][$i]['is_visible']); ?>"></td>
          <td style="text-align:center;"><input type="checkbox" id="ggp_cardinfo[make][<?php echo $i; ?>][is_valid]" name="ggp_cardinfo[make][<?php echo $i; ?>][is_valid]" value="1" <?php checked(1, $ggp_cardinfo['make'][$i]['is_valid']); ?>"></td>
          </tr>
          <?php } ?>
          </table>
          </div>
          <div class="main">
          <h4><span>運搬（工場からお店）</span></h4>
          <table class="ggp_game_table">
          <tr><th style="width:10%;">名称</th><th style="width:20%;">説明</th><th style="width:10%;">画像ファイル名</th><th style="width:5%;">必要金額[万円]</th><th style="width:5%;">米の量[トン]</th><th style="width:5%;">CO2排出量[kg]</th><th style="width:5%;">必要ターン数</th><th style="width:2%;">表示</th><th style="width:2%;">有効</th></tr>
          <?php
          for($i = 0; $i < count($ggp_cardinfo['to_store']); $i++){ ?>
          <tr>
          <td><input type="text" id="ggp_cardinfo[to_store][<?php echo $i; ?>][name]" name="ggp_cardinfo[to_store][<?php echo $i; ?>][name]" value="<?php echo $ggp_cardinfo['to_store'][$i]['name']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[to_store][<?php echo $i; ?>][description]" name="ggp_cardinfo[to_store][<?php echo $i; ?>][description]" value="<?php echo $ggp_cardinfo['to_store'][$i]['description']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[to_store][<?php echo $i; ?>][url]" name="ggp_cardinfo[to_store][<?php echo $i; ?>][url]" value="<?php echo $ggp_cardinfo['to_store'][$i]['url']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[to_store][<?php echo $i; ?>][money]" name="ggp_cardinfo[to_store][<?php echo $i; ?>][money]" value="<?php echo $ggp_cardinfo['to_store'][$i]['money']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[to_store][<?php echo $i; ?>][rice]" name="ggp_cardinfo[to_store][<?php echo $i; ?>][rice]" value="<?php echo $ggp_cardinfo['to_store'][$i]['rice']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[to_store][<?php echo $i; ?>][co2]" name="ggp_cardinfo[to_store][<?php echo $i; ?>][co2]" value="<?php echo $ggp_cardinfo['to_store'][$i]['co2']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[to_store][<?php echo $i; ?>][turn]" name="ggp_cardinfo[to_store][<?php echo $i; ?>][turn]" value="<?php echo $ggp_cardinfo['to_store'][$i]['turn']; ?>" style="width:100%;"></td>
          <td style="text-align:center;"><input type="checkbox" id="ggp_cardinfo[to_store][<?php echo $i; ?>][is_visible]" name="ggp_cardinfo[to_store][<?php echo $i; ?>][is_visible]" value="1" <?php checked(1, $ggp_cardinfo['to_store'][$i]['is_visible']); ?>"></td>
          <td style="text-align:center;"><input type="checkbox" id="ggp_cardinfo[to_store][<?php echo $i; ?>][is_valid]" name="ggp_cardinfo[to_store][<?php echo $i; ?>][is_valid]" value="1" <?php checked(1, $ggp_cardinfo['to_store'][$i]['is_valid']); ?>"></td>
          </tr>
          <?php } ?>
          </table>
          </div>
          <div class="main">
          <h4><span>環境対策</span></h4>
          <p><input type="checkbox" id="ggp_reduction_valid" name="ggp_reduction_valid" value="1" <?php checked(1, $ggp_reduction_valid); ?>>&nbsp;表示（チェックをつけると表示）</p>
          <table class="ggp_game_table">
          <tr><th style="width:10%;">名称</th><th style="width:20%;">説明</th><th style="width:10%;">画像ファイル名</th><th style="width:10%;">キー値</th><th style="width:5%;">必要金額[万円]</th><th style="width:5%;">米の量[トン]</th><th style="width:5%;">CO2排出量[kg]</th><th style="width:5%;">必要ターン数</th><th style="width:2%;">表示</th><th style="width:2%;">有効</th></tr>
          <?php
          for($i = 0; $i < count($ggp_cardinfo['reduction']); $i++){ ?>
          <tr>
          <td><input type="text" id="ggp_cardinfo[reduction][<?php echo $i; ?>][name]" name="ggp_cardinfo[reduction][<?php echo $i; ?>][name]" value="<?php echo $ggp_cardinfo['reduction'][$i]['name']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[reduction][<?php echo $i; ?>][description]" name="ggp_cardinfo[reduction][<?php echo $i; ?>][description]" value="<?php echo $ggp_cardinfo['reduction'][$i]['description']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[reduction][<?php echo $i; ?>][url]" name="ggp_cardinfo[reduction][<?php echo $i; ?>][url]" value="<?php echo $ggp_cardinfo['reduction'][$i]['url']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[reduction][<?php echo $i; ?>][key]" name="ggp_cardinfo[reduction][<?php echo $i; ?>][key]" value="<?php echo $ggp_cardinfo['reduction'][$i]['key']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[reduction][<?php echo $i; ?>][money]" name="ggp_cardinfo[reduction][<?php echo $i; ?>][money]" value="<?php echo $ggp_cardinfo['reduction'][$i]['money']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[reduction][<?php echo $i; ?>][rice]" name="ggp_cardinfo[reduction][<?php echo $i; ?>][rice]" value="<?php echo $ggp_cardinfo['reduction'][$i]['rice']; ?>" style="width:100%;" disabled></td>
          <td><input type="text" id="ggp_cardinfo[reduction][<?php echo $i; ?>][co2]" name="ggp_cardinfo[reduction][<?php echo $i; ?>][co2]" value="<?php echo $ggp_cardinfo['reduction'][$i]['co2']; ?>" style="width:100%;"></td>
          <td><input type="text" id="ggp_cardinfo[reduction][<?php echo $i; ?>][turn]" name="ggp_cardinfo[reduction][<?php echo $i; ?>][turn]" value="<?php echo $ggp_cardinfo['reduction'][$i]['turn']; ?>" style="width:100%;"></td>
          <td style="text-align:center;"><input type="checkbox" id="ggp_cardinfo[reduction][<?php echo $i; ?>][is_visible]" name="ggp_cardinfo[reduction][<?php echo $i; ?>][is_visible]" value="1" <?php checked(1, $ggp_cardinfo['reduction'][$i]['is_visible']); ?>"></td>
          <td style="text-align:center;"><input type="checkbox" id="ggp_cardinfo[reduction][<?php echo $i; ?>][is_valid]" name="ggp_cardinfo[reduction][<?php echo $i; ?>][is_valid]" value="1" <?php checked(1, $ggp_cardinfo['reduction'][$i]['is_valid']); ?>"></td>
          </tr>
          <?php } ?>
          </table>
          </div>
        </div>
      </div>
    </div>
    <?php submit_button(); ?>
  </form>
</div>
<?php
}

function register_ggp_team_setup(){
}

// チーム設定管理画面の出力
function add_ggp_team_setup(){
define('TABLE_NAME_GGP_TEAM',  $wpdb->prefix . 'ggp_team');
define('TABLE_NAME_GGP_EARTH',  $wpdb->prefix . 'ggp_earth');

$is_reset = $_POST['reset'];
$earth_no = $_POST['earth_no'];
$team_no = $_POST['team_no'];
$teamname = $_POST['teamname'];
$money = $_POST['money'];
$co2 = $_POST['co2'];
$earth_update = $_POST['earth_update'];

if($is_reset == "初期化"){
  reset_table_ggp_earth();
  reset_table_ggp_team();
  reset_table_ggp_action();
  reset_table_ggp_action_rice();
  reset_table_ggp_action_tree();
  reset_table_ggp_message();
  $is_reset = "";
}else if($earth_update == true ){
      update_table_ggp_earth($earth_no, $co2);
}else{
  if($teamname != NULL){
      update_table_ggp_team($earth_no, $team_no, $teamname, $money, $co2);
      $ggp_team = get_db_table_records(TABLE_NAME_GGP_TEAM,'');
      $co2_sum = Array();
      foreach($ggp_team as $team ){
        $co2_sum[$team->earth_no] += $team->co2;
      }

      for($i = 0; $i < count($co2_sum); $i++ ){
        update_table_ggp_earth($i, $co2_sum[$i]);
      }
    }
}

$ggp_team = get_db_table_records(TABLE_NAME_GGP_TEAM,'');
$ggp_earth = get_db_table_records(TABLE_NAME_GGP_EARTH,'');

?>
<div class="wrap">
  <h2>チーム設定</h2>
    <?php
    settings_fields('ggp_team_setup_group');
    do_settings_sections('ggp_team_setup_group');
     ?>
    <div class="metabox-holder">
      <div class="postbox">
        <h3 class="hndle"><span>チーム設定</span></h3>
        <div class="inside">
          <div class="main">
          <table class="ggp_team_table">
          <tr><th style="width:5%">地球</th><th style="width:5%;">チーム</th><th style="width=20%;">チーム名</th><th style="width:10%;">ターン</th><th style="width:10%;">お金</th><th style="width:10%;">CO2排出量</th><th style="width:5%;"></th></tr>
          <?php
          for($i = 0; $i < count($ggp_team); $i++){
          ?>
          <tr><form method="post" action="">
          <td><input type="text" id="earth_no" name="earth_no" value=<?php echo $ggp_team[$i]->earth_no; ?> readonly style="width:100%;"></td>
          <td><input type="text" id="team_no" name="team_no" value=<?php echo $ggp_team[$i]->team_no; ?> readonly style="width:100%;"></td>
          <td><input type="text" id="teamname" name="teamname" value = <?php echo $ggp_team[$i]->teamname ?> style="width:100%;"></td>
          <td><input type="text" value=<?php echo $ggp_team[$i]->turn; ?> readonly style="width:100%;"></td>
          <td><input type="text" id="money" name="money" value = <?php echo $ggp_team[$i]->money ?> style="width:100%;"></td>
          <td><input type="text" id="co2" name="co2" value = <?php echo $ggp_team[$i]->co2 ?> style="width:100%;"></td>
          <td><?php submit_button("更新",'small','update',false) ?></td></form></tr>
          <?php } ?>
          </table>
          </div>
        </div>
        <h3 class="hndle"><span>地球設定</span></h3>
        <div class="inside">
        <table class="ggp_earth_table">
        <tr><th style="width:5%;">地球</th><th style="width:20%;">CO2排出量</th><th style="width:5%"></tr>
        <?php
        for($i = 0; $i < count($ggp_earth); $i++){
        ?>
        <tr><form method="post" action="">
        <td><input type="text" id="earth_no" name="earth_no" value=<?php echo $ggp_earth[$i]->earth_no; ?> readonly style="width:100%;"></td>
        <td><input type="text" id="co2" name="co2" value=<?php echo $ggp_earth[$i]->co2; ?> style="width:100%;"></td><input type="hidden" id="earth_update" name="earth_update" value=true></td>
          <td><?php submit_button("更新",'small','update',false) ?></td></form></tr>
      <?php } ?>
      </table>
      </div>
    </div>
  </form>
  <p></p>
  <form method="post" action="">
    <?php
    settings_fields('ggp_team_setup_group');
    do_settings_sections('ggp_team_setup_group');
     ?>
    <div class="metabox-holder">
      <div class="postbox">
        <h3 class="hndle"><span>チーム設定初期化</span></h3>
        <div class="inside">
        <input type="hidden" name="reset" value="reset">
        <p>チーム設定を初期化します。以下の情報がリセットされます。</p>
        <p>・地球の情報（地球の個数、地球毎の二酸化炭素排出量)<br>
        ・チームの情報（チーム数、チーム名、所持金、Co2排出量、現在のターン)<br>
        ・各チームの活動記録</p>
        <?php submit_button("初期化",'delete','reset',true); ?>
        </div>
      </div>
    </div>
  </form>
</div>

<?php
}

add_shortcode('show_ggp_gameboard','show_ggp_gameboard');

/****************************************************************
* ボードゲーム画面を表示
****************************************************************/
if (! function_exists( 'show_ggp_gameboard') ):
function show_ggp_gameboard(){

$ggp_init_earth = get_option('ggp_init_earth');
$ggp_init_perteam = get_option('ggp_init_perteam');
$mode = $_POST['mode'];
$earth_no = $_POST['earth_no'];

if($mode == "select_boardgame"){
  show_ggp_gameboard_main();
}
else if($mode == "select_team"){
  show_ggp_gameboard_team_select();
}
else{
// ショートコードは、htmlをreturnで返す必要があるため
// 編集のときに不正なjson形式と表示されてしまうため、else文は下記形式で記述
  return show_ggp_gameboard_earth_select();
}
}
endif;

/****************************************************************
* 地球の選択画面を表示
****************************************************************/
if( !function_exists('show_ggp_gameboard_earth_select') ):
function show_ggp_gameboard_earth_select(){
$ggp_init_earth = get_option('ggp_init_earth');
$ggp_init_perteam = get_option('ggp_init_perteam');
define('TABLE_NAME_GGP_TEAM',  $wpdb->prefix . 'ggp_team');
$ggp_team = get_db_table_records(TABLE_NAME_GGP_TEAM,'');
$token = uniqid('', true);

// 不正チェック　設定不備があった場合は画面を表示しない
if(count($ggp_team) != $ggp_init_earth*$ggp_init_perteam ){
  return 'チーム設定が不正です。管理画面からゲームを初期化してください。';
}

$is_general = $_POST['is_general'];
if( $is_general == NULL ) $is_general = "disabled" ;

$return_html ="";
$return_html .= '
<div align="center">
<table class="ggp_select_team">
';

for($i = 0; $i < $ggp_init_earth; $i++){
$return_html .= '
  <form method="post" action="">
  <tr><td>
    <input type="hidden" id="earth_no" name="earth_no" value="'.$i.'">
    <input type="hidden" id="is_general" name="is_general" value="'.$is_general.'">
    <input type="hidden" id="mode" name="mode" value="select_team">
    <input class="btn-select-team" type="submit" value="ルーム No.'.$i.'">
  </td></tr>
  </form>
';
}
if($is_general == 'disabled'){

$return_html .= '
  <form method="post" action="">
  <tr><td>
    <input type="hidden" id="earth_no" name="earth_no" value="'.$i.'">
    <input type="hidden" id="is_general" name="is_general" value="enabled">
    <input type="submit" value="チームリーダーはこちら">
  </td></tr>
  </form>
';
}

$return_html .= '

  </table>
  </div>
';

return $return_html;
}
endif;


/****************************************************************
* チームの選択画面を表示
****************************************************************/
if( !function_exists( 'show_ggp_gameboard_team_select' ) ):
function show_ggp_gameboard_team_select(){
define('TABLE_NAME_GGP_TEAM',  $wpdb->prefix . 'ggp_team');
$ggp_team = get_db_table_records(TABLE_NAME_GGP_TEAM,'');

$ggp_init_perteam = get_option('ggp_init_perteam');
$earth_no = $_POST['earth_no'];
$is_general = $_POST['is_general'];
?>
  <div align="center">
  <table class="ggp_select_team">
<!--  <tr><td> 地球No.<?=$earth_no ?></td></tr> -->
<?php
  for($i = 0; $i < $ggp_init_perteam; $i++){
?>
  <form method="post" action="">
  <tr><td>
    <input type="hidden" id="earth_no" name="earth_no" value="<?=$earth_no ?>">
    <input type="hidden" id="team_no" name="team_no" value="<?= $i ?>">
    <input type="hidden" id="mode" name="mode" value="select_boardgame">
    <input type="hidden" id="is_general" name="is_general" value="<?=$is_general ?>">
    <input class="btn-select-team" type="submit" value="<?=$ggp_team[$earth_no*$ggp_init_perteam+$i]->teamname ?>">
  </td></tr>
  </form>
  <?php } ?>
  <form method="post" action="">
  <input type="hidden" id="is_general" name="is_general" value="<?=$is_general ?>">
  <tr><td></td></tr>
  <tr><td><input type="submit" value="戻る"></td></tr>
  </form>
  </table>
  </div>
<?php
}
endif;

/****************************************************************
* ボードゲームを表示
****************************************************************/
if( !function_exists( 'show_ggp_gameboard_main' ) ):
function show_ggp_gameboard_main(){
$earth_no = $_POST['earth_no'];
$team_no = $_POST['team_no'];
$phase = $_POST['phase'];
$card_no = $_POST['card_no'];
$is_general = $_POST['is_general'];
$token = $_POST['token'];
$ggp_quota_co2 = get_option('ggp_quota_co2');
$ggp_cardinfo = get_option('ggp_cardinfo');
$ggp_init_sales = get_option('ggp_init_sales');
$ggp_reduction_valid = get_option('ggp_reduction_valid');
$ggp_event_tree = get_option('ggp_event_tree');
$ggp_event_sales = get_option('ggp_event_sales');
$ggp_event_tree_magnification = (double)get_option('ggp_event_tree_magnification');
$ggp_event_sales_magnification = (double)get_option('ggp_event_sales_magnification');
$token_new = uniqid('', true);
$error_msg = "";

$phase_name = Array('grow'=>'お米を育てる',
                    'to_factory'=>'お米を工場に運ぶ',
                    'make'=>'おせんべいを作る',
                    'to_store'=>'おせんべいをお店に運ぶ',
                    'reduction'=>'環境対策',
                    'sales'=>'おせんべいを売る');

$msg_array = Array( 'grow'=>'お米を育てました',
                    'to_factory'=>'お米を田んぼから工場に運びました',
                    'make'=>'工場でおせんべいを作りました',
                    'to_store'=>'おせんべいを工場からお店に運びました',
                    'reduction'=>'環境対策をしました',
                    'tree'=>'植林により二酸化炭素が減りました',
                    'sales'=>'おせんべいを売りました');


// $card_valid = Array( 'grow' => $is_general);

//イベント用の変数
$event_tree_msg_in_card = "【イベント発生中】「木を植える」カードの効果が" . $ggp_event_tree_magnification ."倍になりました。";
$event_sales_msg_in_card = "【イベント発生中】売り上げが木を植えた回数&#x2613;" . (100*(double)$ggp_event_sales_magnification) ."％アップになりました。";

$event_tree_msg = "【イベント発生】「木を植える」カードの効果が" . $ggp_event_tree_magnification ."倍になりました。画面を更新してください。";
$event_sales_msg = "【イベント発生】売り上げが木を植えた回数&#x2613;" . (100*(double)$ggp_event_sales_magnification) ."％アップになりました。画面を更新してください。";
$event_cancel_msg = "【イベントキャンセル】イベントがキャンセルされました。画面を更新してください。";

$reduction_quantity_tree = (int)(get_table_ggp_action_tree($earth_no, $team_no))[0]->reduction_co2;
$tree_num = (int)(get_table_ggp_action_tree($earth_no, $team_no))[0]->tree_num;

if($ggp_event_tree == NULL) $ggp_event_tree_magnification = 1;
if($ggp_event_sales == NULL || $tree_num == 0) $ggp_event_sales_magnification = 1;


define('TABLE_NAME_GGP_TEAM',  $wpdb->prefix . 'ggp_team');
define('TABLE_NAME_GGP_EARTH',  $wpdb->prefix . 'ggp_earth');
$ggp_team = get_db_table_records_ggp(TABLE_NAME_GGP_TEAM,"earth_no",$earth_no);
$ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);


if($phase != NULL && $card_no != NULL ){

if($ggp_team[$team_no]->money < $ggp_cardinfo[$phase][$card_no]['money'] ){
  //お金が足りない場合
  $error_msg = 'お金が足りません。';
} else if(allow_insert_table_ggp_action($token)){


// 通常のカードの場合、情報を更新する
  if($phase != 'reduction'){
      //選んだカードの内容を更新する
      update_db_table_ggp_action_rice($earth_no, $team_no, $phase, $ggp_cardinfo[$phase][$card_no]['rice']);
      insert_table_ggp_action($token, $earth_no, $team_no, $phase, $ggp_cardinfo[$phase][$card_no]['name'], $ggp_cardinfo[$phase][$card_no]['url'],"", -$ggp_cardinfo[$phase][$card_no]['money'], $ggp_cardinfo[$phase][$card_no]['co2'],$ggp_cardinfo[$phase][$card_no]['turn']);
      update_table_ggp_earth($earth_no, $ggp_earth[0]->co2+$ggp_cardinfo[$phase][$card_no]['co2']);
      update_table_ggp_team_transaction($earth_no, $team_no, $ggp_cardinfo[$phase][$card_no]['turn'], $ggp_cardinfo[$phase][$card_no]['money'], $ggp_cardinfo[$phase][$card_no]['co2']);
      insert_db_table_ggp_message($earth_no, $ggp_team[$team_no]->turn.'ターン目 : 「'.$ggp_team[$team_no]->teamname.'」チームが'.$msg_array[$phase].'（'.$ggp_cardinfo[$phase][$card_no]['name'].'）。');

    }

// 環境対策のカードを選んだ場合
    else {
      if($ggp_cardinfo[$phase][$card_no]['key'] == 'tree'){
        //植樹 カード切ったタイミングではCo2削減は実施しない

        $tree_card_no = 0;
        // key=treeとなっているカードの番号を探索する
       for($tree_card_no = 0; $tree_card_no < count($ggp_cardinfo['reduction']); $tree_card_no++){
          if($ggp_cardinfo['reduction'][$tree_card_no]['key'] == 'tree') break;
        }

        insert_table_ggp_action($token, $earth_no, $team_no, $phase, $ggp_cardinfo[$phase][$card_no]['name'],$ggp_cardinfo[$phase][$card_no]['url'],$ggp_cardinfo[$phase][$card_no]['key'],-$ggp_cardinfo[$phase][$card_no]['money'], 0 ,$ggp_cardinfo[$phase][$card_no]['turn']);
        insert_db_table_ggp_message($earth_no, $ggp_team[$team_no]->turn.'ターン目 : 「'.$ggp_team[$team_no]->teamname.'」チームが'.$msg_array[$phase].'（'.$ggp_cardinfo[$phase][$card_no]['name'].' '.'二酸化炭素が'.$ggp_cardinfo['reduction'][$tree_card_no]['co2']*$ggp_event_tree_magnification.'kg/ターン減少）。');
        update_table_ggp_team_transaction($earth_no, $team_no, $ggp_cardinfo[$phase][$card_no]['turn'], $ggp_cardinfo[$phase][$card_no]['money'], 0);
        update_table_ggp_action_tree($earth_no, $team_no, $ggp_cardinfo['reduction'][$tree_card_no]['co2']*$ggp_event_tree_magnification);

      } else if ($ggp_cardinfo[$phase][$card_no]['key'] == 'buy'){
        //排出権購入の場合
        insert_table_ggp_action($token, $earth_no, $team_no, $phase, $ggp_cardinfo[$phase][$card_no]['name'],$ggp_cardinfo[$phase][$card_no]['url'],$ggp_cardinfo[$phase][$card_no]['key'],-$ggp_cardinfo[$phase][$card_no]['money'], $ggp_cardinfo[$phase][$card_no]['co2'],$ggp_cardinfo[$phase][$card_no]['turn']);
        update_table_ggp_earth($earth_no, $ggp_earth[0]->co2+$ggp_cardinfo[$phase][$card_no]['co2']);
        update_table_ggp_team_transaction($earth_no, $team_no, $ggp_cardinfo[$phase][$card_no]['turn'], $ggp_cardinfo[$phase][$card_no]['money'], $ggp_cardinfo[$phase][$card_no]['co2']);
        insert_db_table_ggp_message($earth_no, $ggp_team[$team_no]->turn.'ターン目 : 「'.$ggp_team[$team_no]->teamname.'」チームが'.$msg_array[$phase].'（'.$ggp_cardinfo[$phase][$card_no]['name'].'）。');
      }
    }

    //お店に運ぶフェーズの場合、売り上げを計上
    if($phase == 'to_store'){
      update_table_ggp_team_transaction($earth_no, $team_no, 0, -$ggp_init_sales*(1+$tree_num*$ggp_event_sales_magnification) , 0);
      insert_table_ggp_action($token, $earth_no, $team_no, 'sales', $msg_array['sales'],"money.png","", $ggp_init_sales*(1+$tree_num*$ggp_event_sales_magnification),0,0);
      insert_db_table_ggp_message($earth_no, $ggp_team[$team_no]->turn.'ターン目 : 「'.$ggp_team[$team_no]->teamname.'」チームが'.$msg_array['sales'].'。');
    }

//毎ターンずつ植樹によりCo2排出量を削減する
    if($reduction_quantity_tree < 0 ){
      $ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);
      $tree_card_no = 0;
      // key=treeとなっているカードの番号を探索する
      for($tree_card_no = 0; $tree_card_no < count($ggp_cardinfo['reduction']); $tree_card_no++){
        if($ggp_cardinfo['reduction'][$tree_card_no]['key'] == 'tree') break;
      }
      //insert_table_ggp_action($token, $earth_no, $team_no, "",$msg_array['tree'], "tree_reduction_co2.png","", 0, $ggp_cardinfo['reduction'][$tree_card_no]['co2']*$reduction_quantity_tree,0);
      //update_table_ggp_earth($earth_no, $ggp_earth[0]->co2+$ggp_cardinfo['reduction'][$tree_card_no]['co2']*$reduction_quantity_tree);
      //update_table_ggp_team_transaction($earth_no, $team_no, 0, 0, $ggp_cardinfo['reduction'][$tree_card_no]['co2']*$reduction_quantity_tree);
      insert_table_ggp_action($token, $earth_no, $team_no, "",$msg_array['tree'], "tree_reduction_co2.png","", 0, $reduction_quantity_tree,0);
      update_table_ggp_earth($earth_no, $ggp_earth[0]->co2+$reduction_quantity_tree);
      update_table_ggp_team_transaction($earth_no, $team_no, 0, 0, $reduction_quantity_tree);
    }

  }
}

//更新後の最新情報を取得
$ggp_team = get_db_table_records_ggp(TABLE_NAME_GGP_TEAM,"earth_no",$earth_no);
$ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);
$ggp_message = get_db_table_ggp_message($earth_no);
$ggp_action = get_db_table_ggp_action($earth_no, $team_no);

//メッセージの1行目を取得
$ggp_message_1st = $ggp_message[0]->msg;

//各フェーズの米の量を取得
$get_db_table_ggp_action_rice = get_db_table_ggp_action_rice($earth_no, $team_no);
$after_grow_quantity =  (int)$get_db_table_ggp_action_rice[0]->grow;
$after_to_factory_quantity =  (int)$get_db_table_ggp_action_rice[0]->to_factory;
$after_make_quantity =  (int)$get_db_table_ggp_action_rice[0]->make;

//CO2排出量が地球の上限を超えたらゲームオーバーとする
if($ggp_earth[0]->co2 > $ggp_quota_co2) {
  $_POST['is_general'] = 'disabled';
  $is_general = 'disabled';
  $error_msg = 'ゲームオーバー：二酸化炭素ガスの排出量が地球の上限を超えました';
?>
<style>
.header-div {
  background-color: red;
}
</style>
<?php }

//ボタンの表示・非表示のロジック
$is_btn_valid = is_btn_valid();

?>


<link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
<!--Chart.jsの読み込み -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.js"></script>

<!-- ***********ヘッダ部分*********** -->
<div class="header-div">
  <div class="team-name">
    <?php echo $ggp_team[$team_no]->teamname ?>
  </div>
  <div class="team-name">
    <form method="post" action="">
    <input type="hidden" name="is_general" value="<?php echo $is_general; ?>">
    <input type="hidden" name="earth_no" value="<?php echo $earth_no; ?>">
    <input type="hidden" name="team_no" value="<?php echo $team_no; ?>">
    <input type="hidden" name="mode" value="select_boardgame">
    <input type="submit" class="fas btn-reload" value="&#xf2f1 更新">
    </form>
  </div>

  <div class="team-name">
    <?php if($is_general == 'disabled'){ ?>
      <span style="font-size:0.5em; color:red;">【参照のみ】カードをえらぶことはできません</span>
    <?php ; }?>
  </div>
  <div class="return">
    <form method="post" action="">
    <input type="hidden" name="is_general" value="<?php echo $is_general; ?>">
    <input type="hidden" name="earth_no" value="<?php echo $earth_no; ?>">
    <input type="hidden" name="mode" value="select_team">
    <input type="submit" class="btn-return" value="戻る">
    </form>
  </div>
  <div class="return">
    <p style="width:20px;"></p>
  </div>
  <div class="return">
    <form method="post" name="timer">
      <input type="text" class="textbox-timer" value="1">分
      <input type="text" class="textbox-timer" value="0">秒
      <input type="button" class="btn-timer" id="btn-timer" value="スタート" onclick="cntStart()">
      <input type="button" class="btn-timer" value="リセット" onclick="reSet()">
      <div id="event_valid_tree" class="popup_msg"><?=$ggp_event_tree ?></div>
      <div id="event_valid_sales" class="popup_msg"><?=$ggp_event_sales ?></div>
      <div id="popup_msg" class="popup_msg"><?=$ggp_message[0]->msg ?></div>
      <div id="popup_event_msg" class="popup_msg"></div>
      <input type="hidden" name="is_general" value="<?php echo $is_general; ?>">
      <input type="hidden" name="earth_no" value="<?php echo $earth_no; ?>">
      <input type="hidden" name="team_no" value="<?php echo $team_no; ?>">
    </form>
  </div>
</div>
<div class="header-div">
<?php /*
  <!-- 各チームの情報 -->
  <div class="team-description">
  <span id="team-description"></span>
    <i class="fas fa-globe"></i>地球全体の二酸化炭素排出量：<span id="ggp_earth_co2"><?php echo $ggp_earth[0]->co2; ?></span>kg／<?php echo $ggp_quota_co2; ?>kg(上限)<br>
*/
?>
  <!--ほかのチームの情報 -->
  <div class="earth-info">
    <!-- 一覧表を表示する -->
    <div id="earth-info">
    </div>
    <!-- 地球全体の二酸化炭素排出量のグラフを表示する -->
    <div>
      <canvas id="co2_graph" height="50px" width="500px"></canvas>
    </div>
  </div>

  <!-- ***********メッセージ一覧*********** -->
  <div class="ggp-msg" id = "ggp-msg">
  <?php for($i = 0; $i < count($ggp_message); $i++) { ?>
  <p class="p-ggp-msg"><?php echo $ggp_message[$i]->msg; ?></p>
  <?php } ?>
  </div>

</div>
<hr>

<?php if($error_msg != ""){ ?>
<div class="error-msg">
<?=$error_msg ?>
</div>
<?php } ?>



<!-- ***********カード選択部分*********** -->

<p class="subtitle">カード</p>
  <table class="card_image">
    <tr>
  <?php if($ggp_reduction_valid != NULL ) { ?>
    <td width="200px" style="border-right: 1px solid #CCCCCC !important;">
      <img src="/wp-content/uploads/earth_good.png" width="160px" onclick="OnClick_reduction()"><br>
      <?=$phase_name['reduction'] ?><br>
      <input type="button" class="btn-select-phase" id="btn_reduction" value="表示" onclick="OnClick_reduction()">
    </td>
  <?php } ?>
    <td width="200px">
      <img src="/wp-content/uploads/ine.png" width="160px" onclick="OnClick_grow()"><br>
      <?=$phase_name['grow'] ?><br>
      <input type="button" class="btn-select-phase" id="btn_grow" value="表示" onclick="OnClick_grow()"></td>
    <td style="vertical-align:middle;"><div class="triangle"></div><br><?=$after_grow_quantity ?>トン</td>
    <td width="200px">
      <img src="/wp-content/uploads/to_factory.png" width="180px" onclick="OnClick_to_factory()"><br>
      <?=$phase_name['to_factory'] ?><br>
      <input type="button" class="btn-select-phase" id="btn_to_factory" value="表示" onclick="OnClick_to_factory()">
    </td>
    <td style="vertical-align:middle;"><div class="triangle"></div><br><?=$after_to_factory_quantity ?>トン</td>
    <td width="200px">
      <img src="/wp-content/uploads/factory.png" width="160px" onclick="OnClick_make()"><br>
      <?=$phase_name['make'] ?><br>
      <input type="button" class="btn-select-phase" id="btn_make" value="表示" onclick="OnClick_make()">
    </td>
    <td style="vertical-align:middle;"><div class="triangle"></div><br><?=$after_make_quantity ?>トン</td>
    <td width="200px">
      <img src="/wp-content/uploads/to_store.png" width="180px" onclick="OnClick_to_store()"><br>
      <?=$phase_name['to_store'] ?><br>
      <input type="button" class="btn-select-phase" id="btn_to_store" value="表示" onclick="OnClick_to_store()">
    </td>
  </tr>
  </table>

<!-- ***********カード詳細部分*********** -->

  <div class="card_area" id="card_area_grow">
  <p class="card_text"><?=$phase_name['grow'] ?></p>
  <a class="close-btn" onclick="OnClick_grow()" style="color:black;"><i class="fas fa-times" ></i></a>
  <?php for($i = 0; $i < count($ggp_cardinfo['grow']); $i++){
    if($ggp_cardinfo['grow'][$i]['is_visible'] == NULL) continue; ?>
    <form method="post" action="">
      <div class="card" <?=is_card_disabled($ggp_cardinfo['grow'][$i]['is_valid']) ?>>
        <input type="hidden" name="is_general" value="<?=$is_general ?>">
        <input type="hidden" name="token" value="<?=$token_new ?>">
        <input type="hidden" name="earth_no" value="<?=$earth_no ?>">
        <input type="hidden" name="mode" value="select_boardgame">
        <input type="hidden" name="team_no" value="<?=$team_no ?>">
        <input type="hidden" name="phase" value="grow">
        <input type="hidden" name="card_no" value="<?=$i ?>">
        <div class="icon"><img src="/wp-content/uploads/<?=$ggp_cardinfo['grow'][$i]['url'] ?>"></div>
        <p class="card_text"><?=$ggp_cardinfo['grow'][$i]['name'] ?></p>
        <p class="card_text"><?=$ggp_cardinfo['grow'][$i]['description'] ?></p>
        <p class="card_text"><i class="fas fa-coins"></i>&nbsp;必要なお金<br>&nbsp;<?=$ggp_cardinfo['grow'][$i]['rice'] ?>トンの米　<?=$ggp_cardinfo['grow'][$i]['money'] ?>万円</p>
        <p class="card_text"><i class="fas fa-undo"></i>&nbsp;必要なターン数<br>&nbsp;<?=$ggp_cardinfo['grow'][$i]['turn'] ?>ターン</p>
        <p class="card_text"><i class="fas fa-skull-crossbones"></i>&nbsp;二酸化炭素ガス<br>&nbsp;<?=$ggp_cardinfo['grow'][$i]['co2'] ?>kg</p>
        <div style="text-align:center;"><input class="btn-select-card" type="submit" value="<?php echo($is_btn_valid['grow'][$i]['is_valid'] == 'disabled' ? 'えらべません' :'えらぶ'); ?>" <?=$is_btn_valid['grow'][$i]['is_valid'] ?>></div>

      </div>
    </form>
  <?php } ?>
  </div>

  <div class="card_area" id="card_area_to_factory">
  <p class="card_text"><?=$phase_name['to_factory'] ?></p>
  <?php if($after_grow_quantity <= 0){ ;?><p class="card_text_err"><i class="fas fa-ban"></i>お米を育てないとこのカードはえらべません</p> <?php  };?>
  <a class="close-btn" onclick="OnClick_to_factory()" style="color:black;"><i class="fas fa-times" ></i></a>
  <?php for($i = 0; $i < count($ggp_cardinfo['to_factory']); $i++){
    if($ggp_cardinfo['to_factory'][$i]['is_visible'] == NULL) continue; ?>
    <form method="post" action="">
      <div class="card" <?=is_card_disabled($ggp_cardinfo['to_factory'][$i]['is_valid']) ?>>
        <input type="hidden" name="token" value="<?=$token_new ?>">
        <input type="hidden" name="is_general" value="<?=$is_general ?>">
        <input type="hidden" name="earth_no" value="<?=$earth_no ?>">
        <input type="hidden" name="mode" value="select_boardgame">
        <input type="hidden" name="team_no" value="<?=$team_no ?>">
        <input type="hidden" name="phase" value="to_factory">
        <input type="hidden" name="card_no" value="<?=$i ?>">
        <div class="icon"><img src="/wp-content/uploads/<?=$ggp_cardinfo['to_factory'][$i]['url'] ?>"></div>
        <p class="card_text"><?=$ggp_cardinfo['to_factory'][$i]['name'] ?></p>
        <p class="card_text"><?=$ggp_cardinfo['to_factory'][$i]['description'] ?></p>
        <p class="card_text"><i class="fas fa-coins"></i>&nbsp;必要なお金<br>&nbsp;<?=$ggp_cardinfo['to_factory'][$i]['rice'] ?>トンの米　<?=$ggp_cardinfo['to_factory'][$i]['money'] ?>万円</p>
        <p class="card_text"><i class="fas fa-undo"></i>&nbsp;必要なターン数<br>&nbsp;<?=$ggp_cardinfo['to_factory'][$i]['turn'] ?>ターン</p>
        <p class="card_text"><i class="fas fa-skull-crossbones"></i>&nbsp;二酸化炭素ガス<br>&nbsp;<?=$ggp_cardinfo['to_factory'][$i]['co2'] ?>kg</p>
        <div style="text-align:center;"><input class="btn-select-card" type="submit" value="<?php echo($is_btn_valid['to_factory'][$i]['is_valid'] == 'disabled' ? 'えらべません' :'えらぶ'); ?>" <?=$is_btn_valid['to_factory'][$i]['is_valid'] ?>></div>
      </div>
    </form>
  <?php } ?>
  </div>

  <div class="card_area" id="card_area_make">
  <p class="card_text"><?=$phase_name['make'] ?></p>
  <?php if($after_to_factory_quantity <= 0){ ;?><p class="card_text_err"><i class="fas fa-ban"></i>お米を工場に運ばないとこのカードはえらべません</p> <?php  };?>
  <a class="close-btn" onclick="OnClick_make()" style="color:black;"><i class="fas fa-times" ></i></a>
  <?php for($i = 0; $i < count($ggp_cardinfo['make']); $i++){
    if($ggp_cardinfo['make'][$i]['is_visible'] == NULL) continue; ?>
    <form method="post" action="">
      <div class="card" <?=is_card_disabled($ggp_cardinfo['make'][$i]['is_valid']) ?>>
        <input type="hidden" name="token" value="<?=$token_new ?>">
        <input type="hidden" name="is_general" value="<?=$is_general ?>">
        <input type="hidden" name="earth_no" value="<?=$earth_no ?>">
        <input type="hidden" name="mode" value="select_boardgame">
        <input type="hidden" name="team_no" value="<?=$team_no ?>">
        <input type="hidden" name="phase" value="make">
        <input type="hidden" name="card_no" value="<?=$i ?>">
        <div class="icon"><img src="/wp-content/uploads/<?=$ggp_cardinfo['make'][$i]['url'] ?>"></div>
        <p class="card_text"><?=$ggp_cardinfo['make'][$i]['name'] ?></p>
        <p class="card_text"><?=$ggp_cardinfo['make'][$i]['description'] ?></p>
        <p class="card_text"><i class="fas fa-coins"></i>&nbsp;必要なお金<br>&nbsp;<?=$ggp_cardinfo['make'][$i]['rice'] ?>トンの米　<?=$ggp_cardinfo['make'][$i]['money'] ?>万円</p>
        <p class="card_text"><i class="fas fa-undo"></i>&nbsp;必要なターン数<br>&nbsp;<?=$ggp_cardinfo['make'][$i]['turn'] ?>ターン</p>
        <p class="card_text"><i class="fas fa-skull-crossbones"></i>&nbsp;二酸化炭素ガス<br>&nbsp;<?=$ggp_cardinfo['make'][$i]['co2'] ?>kg</p>
        <div style="text-align:center;"><input class="btn-select-card" type="submit" value="<?php echo($is_btn_valid['make'][$i]['is_valid'] == 'disabled' ? 'えらべません' :'えらぶ'); ?>" <?=$is_btn_valid['make'][$i]['is_valid'] ?>></div>
      </div>
    </form>
  <?php } ?>
  </div>

  <div class="card_area" id="card_area_to_store">
  <p class="card_text"><?=$phase_name['to_store'] ?></p>
  <?php if($ggp_event_sales != NULL){ ?><p class="card_text" style="color:red;"><?=$event_sales_msg_in_card ?><?php }; ?></p>
  <?php if($after_make_quantity <= 0){ ;?><p class="card_text_err"><i class="fas fa-ban"></i>おせんべいを作らないとこのカードはえらべません</p> <?php  };?>
  <a class="close-btn" onclick="OnClick_to_store()" style="color:black;"><i class="fas fa-times" ></i></a>
  <?php for($i = 0; $i < count($ggp_cardinfo['to_store']); $i++){
    if($ggp_cardinfo['to_store'][$i]['is_visible'] == NULL) continue; ?>
    <form method="post" action="">
      <div class="card" <?=is_card_disabled($ggp_cardinfo['to_store'][$i]['is_valid']) ?>>
        <input type="hidden" name="token" value="<?=$token_new ?>">
        <input type="hidden" name="is_general" value="<?=$is_general ?>">
        <input type="hidden" name="earth_no" value="<?=$earth_no ?>">
        <input type="hidden" name="mode" value="select_boardgame">
        <input type="hidden" name="team_no" value="<?=$team_no ?>">
        <input type="hidden" name="phase" value="to_store">
        <input type="hidden" name="card_no" value="<?=$i ?>">
        <div class="icon"><img src="/wp-content/uploads/<?=$ggp_cardinfo['to_store'][$i]['url'] ?>"></div>
        <p class="card_text"><?=$ggp_cardinfo['to_store'][$i]['name']; ?></p>
        <p class="card_text"><?=$ggp_cardinfo['to_store'][$i]['description'] ?></p>
        <p class="card_text"><i class="fas fa-coins"></i>&nbsp;必要なお金<br>&nbsp;<?=$ggp_cardinfo['to_store'][$i]['rice'] ?>トンの米　<?=$ggp_cardinfo['to_store'][$i]['money'] ?>万円</p>
        <p class="card_text"><i class="fas fa-undo"></i>&nbsp;必要なターン数<br>&nbsp;<?=$ggp_cardinfo['to_store'][$i]['turn'] ?>ターン</p>
        <p class="card_text"><i class="fas fa-skull-crossbones"></i>&nbsp;二酸化炭素ガス<br>&nbsp;<?=$ggp_cardinfo['to_store'][$i]['co2'] ?>kg</p>
        <div style="text-align:center;"><input class="btn-select-card" type="submit" value="<?php echo($is_btn_valid['to_store'][$i]['is_valid'] == 'disabled' ? 'えらべません' :'えらぶ'); ?>" <?=$is_btn_valid['to_store'][$i]['is_valid'] ?>></div>
      </div>
    </form>
  <?php } ?>
  </div>

  <div class="card_area" id="card_area_reduction">
  <p class="card_text"><?=$phase_name['reduction'] ?></p>
  <?php if($ggp_event_tree != NULL){ ?><p class="card_text" style="color:red;"><?=$event_tree_msg_in_card ?><?php }; ?></p>

  <a class="close-btn" onclick="OnClick_reduction()" style="color:black;"><i class="fas fa-times" ></i></a>
  <?php for($i = 0; $i < count($ggp_cardinfo['reduction']); $i++){
    if($ggp_cardinfo['reduction'][$i]['is_visible'] == NULL) continue; ?>
    <form method="post" action="">
      <div class="card" <?=is_card_disabled($ggp_cardinfo['reduction'][$i]['is_valid']) ?>>
        <input type="hidden" name="token" value="<?=$token_new ?>">
        <input type="hidden" name="is_general" value="<?=$is_general; ?>">
        <input type="hidden" name="earth_no" value="<?=$earth_no ?>">
        <input type="hidden" name="mode" value="select_boardgame">
        <input type="hidden" name="team_no" value="<?=$team_no ?>">
        <input type="hidden" name="phase" value="reduction">
        <input type="hidden" name="card_no" value="<?=$i ?>">
        <div class="icon"><img src="/wp-content/uploads/<?=$ggp_cardinfo['reduction'][$i]['url'] ?>"></div>
        <p class="card_text"><?=$ggp_cardinfo['reduction'][$i]['name'] ?></p>
        <p class="card_text"><?=$ggp_cardinfo['reduction'][$i]['description'] ?></p>
        <p class="card_text"><i class="fas fa-coins"></i>&nbsp;必要なお金<br>&nbsp;<?=$ggp_cardinfo['reduction'][$i]['money'] ?>万円</p>
        <p class="card_text"><i class="fas fa-undo"></i>&nbsp;必要なターン数<br>&nbsp;<?=$ggp_cardinfo['reduction'][$i]['turn'] ?>ターン</p>
        <?php if($ggp_cardinfo['reduction'][$i]['key'] == 'tree'){ ?> <p class="card_text"><i class="fas fa-skull-crossbones"></i>&nbsp;二酸化炭素ガス<br>&nbsp;<?=$ggp_cardinfo['reduction'][$i]['co2']*$ggp_event_tree_magnification ?>kg</p> <?php } else { ?>
        <p class="card_text"><i class="fas fa-skull-crossbones"></i>&nbsp;二酸化炭素ガス<br>&nbsp;<?=$ggp_cardinfo['reduction'][$i]['co2'] ?>kg</p><?php } ?>
        <div style="text-align:center;"><input class="btn-select-card" type="submit" value="<?php echo($is_btn_valid['reduction'][$i]['is_valid'] == 'disabled' ? 'えらべません' :'えらぶ'); ?>" <?=$is_btn_valid['reduction'][$i]['is_valid'] ?>></div>
      </div>
    </form>
  <?php } ?>
  </div>



<!-- ***********行動記録*********** -->
<p class="subtitle">行動</p>
<table class="action_table">
<tr><th style="width:15%;">ターン</th><th style="width:40%;">行動</th><th style="width:15%;">お金</th><th style="width:15%;">二酸化炭素</th><th style="width:15%;">必要なターン数</th></tr>
<?php for ($i = 0 ; $i < count($ggp_action); $i++){ ?>
<tr>
<td><?php echo ($ggp_action[$i]->require_turn == 0 ? "-" : $ggp_action[$i]->turn); ?></td>
<td style="text-align:left !important;"><img class="icon-action" src="/wp-content/uploads/<?=$ggp_action[$i]->url ?>"><?php echo $phase_name[$ggp_action[$i]->phase].'('.$ggp_action[$i]->cardname.')'; ?></td>
<td><?php echo $ggp_action[$i]->money; ?>万円</td>
<td><?php echo $ggp_action[$i]->co2; ?>kg</td>
<td><?php echo $ggp_action[$i]->require_turn; ?>ターン</td>
</tr>
<?php } ?>
</table>

<script>
    document.getElementById("card_area_grow").style.display ="none";
    document.getElementById("card_area_to_factory").style.display ="none";
    document.getElementById("card_area_make").style.display ="none";
    document.getElementById("card_area_to_store").style.display ="none";
    document.getElementById("card_area_reduction").style.display ="none";

function OnClick_grow(){
  const p = document.getElementById("card_area_grow");
  const btn = document.getElementById("btn_grow");
  if(p.style.display=="block"){
    // noneで非表示
    p.style.display ="none";
    btn.value="表示";
  }else{
    // blockで表示
    p.style.display ="block";
    btn.value="非表示";
    document.getElementById("card_area_to_factory").style.display ="none";
    document.getElementById("card_area_make").style.display ="none";
    document.getElementById("card_area_to_store").style.display ="none";
    document.getElementById("card_area_reduction").style.display ="none";
    document.getElementById("btn_to_factory").value ="表示";
    document.getElementById("btn_make").value ="表示";
    document.getElementById("btn_to_store").value ="表示";
    document.getElementById("btn_reduction").value ="表示";
  }
}

function OnClick_to_factory(){
  const p = document.getElementById("card_area_to_factory");
  const btn = document.getElementById("btn_to_factory");
  if(p.style.display=="block"){
    // noneで非表示
    p.style.display ="none";
    btn.value="表示";
  }else{
    // blockで表示
    p.style.display ="block";
    btn.value="非表示";
    document.getElementById("card_area_grow").style.display ="none";
    document.getElementById("card_area_make").style.display ="none";
    document.getElementById("card_area_to_store").style.display ="none";
    document.getElementById("card_area_reduction").style.display ="none";
    document.getElementById("btn_grow").value ="表示";
    document.getElementById("btn_make").value ="表示";
    document.getElementById("btn_to_store").value ="表示";
    document.getElementById("btn_reduction").value ="表示";
  }
}

function OnClick_make(){
  const p = document.getElementById("card_area_make");
  const btn = document.getElementById("btn_make");
  if(p.style.display=="block"){
    // noneで非表示
    p.style.display ="none";
    btn.value="表示";
  }else{
    // blockで表示
    p.style.display ="block";
    btn.value="非表示";
    document.getElementById("card_area_grow").style.display ="none";
    document.getElementById("card_area_to_factory").style.display ="none";
    document.getElementById("card_area_to_store").style.display ="none";
    document.getElementById("card_area_reduction").style.display ="none";
    document.getElementById("btn_grow").value ="表示";
    document.getElementById("btn_to_factory").value ="表示";
    document.getElementById("btn_to_store").value ="表示";
    document.getElementById("btn_reduction").value ="表示";
  }
}

function OnClick_to_store(){
  const p = document.getElementById("card_area_to_store");
  const btn = document.getElementById("btn_to_store");
  if(p.style.display=="block"){
    // noneで非表示
    p.style.display ="none";
    btn.value="表示";
  }else{
    // blockで表示
    p.style.display ="block";
    btn.value="非表示";
    document.getElementById("card_area_grow").style.display ="none";
    document.getElementById("card_area_to_factory").style.display ="none";
    document.getElementById("card_area_make").style.display ="none";
    document.getElementById("card_area_reduction").style.display ="none";
    document.getElementById("btn_grow").value ="表示";
    document.getElementById("btn_to_factory").value ="表示";
    document.getElementById("btn_make").value ="表示";
    document.getElementById("btn_reduction").value ="表示";
  }
}

function OnClick_reduction(){
  const p = document.getElementById("card_area_reduction");
  const btn = document.getElementById("btn_reduction");
  if(p.style.display=="block"){
    // noneで非表示
    p.style.display ="none";
    btn.value="表示";
  }else{
    // blockで表示
    p.style.display ="block";
    btn.value="非表示";
    document.getElementById("card_area_grow").style.display ="none";
    document.getElementById("card_area_to_factory").style.display ="none";
    document.getElementById("card_area_make").style.display ="none";
    document.getElementById("card_area_to_store").style.display ="none";
    document.getElementById("btn_grow").value ="表示";
    document.getElementById("btn_to_factory").value ="表示";
    document.getElementById("btn_make").value ="表示";
    document.getElementById("btn_to_store").value ="表示";
  }
}

// 初回にjavascript関数を実行する
window.addEventListener('load',co2_graph(<?=$ggp_earth[0]->co2 ?>, <?=$ggp_quota_co2 ?>));
window.addEventListener('load',get_ggp_earth_info_ajax());
window.addEventListener('load',check_ggp_event_tree_ajax("1"));
window.addEventListener('load',check_ggp_event_sales_ajax("1"));
// window.addEventListener('load',get_ggp_team_description_ajax());

function co2_graph(co2, ggp_quota_co2) {
let co2_graph_green = [];
let co2_graph_yellow = [];
let co2_graph_red = [];
co2_graph_green.push(0);
co2_graph_yellow.push(0);
co2_graph_red.push(0);

if( co2 / ggp_quota_co2 <= 0.5 ) {
   co2_graph_green[0] = co2;
}else if( co2 / ggp_quota_co2 <= 0.8) {
   co2_graph_green[0] = ggp_quota_co2*0.5;
   co2_graph_yellow[0] = co2 - co2_graph_green;
}else{
   co2_graph_green[0] = ggp_quota_co2*0.5;
   co2_graph_yellow[0] = ggp_quota_co2*0.3;
   co2_graph_red[0] = co2 - co2_graph_green - co2_graph_yellow;
}

    var ctx = document.getElementById("co2_graph").getContext('2d');
    window.myChart = new Chart(ctx, {
        type: "horizontalBar",
        data: {
            labels:  ["二酸化炭素("+co2+"kg/"+ggp_quota_co2+"kg)"],
            datasets: [
                {
                    label: "green",
                    data: co2_graph_green,
                    backgroundColor: "#00B06B"
                },
                {
                    label: "yellow",
                    data: co2_graph_yellow,
                    backgroundColor: "#F2E700"
                },
                {
                    label: "red",
                    data: co2_graph_red,
                    backgroundColor: "#FF4B00"
                }
            ]
        },
        options: {
          animation: {
            duration: 0
          },
          tooltips: {
            enabled: false
          },
          responsive: false,
          title: {
            display: false,
            fontSize: 20,
            text: "二酸化炭素"
          },
          legend: {
            display : false
          },
          scales: {
            xAxes: [
              {
                stacked: true,  // 積み上げの指定
                ticks: {
                  min: 0,
                  max:<?=$ggp_quota_co2 ?>,
                  stepSize:200
                }
              }
            ],
            yAxes: [
              {
                stacked: true  //  積み上げの指定
              }
            ]
          }
        }
    });
}

function co2_graph_reload(co2, ggp_quota_co2) {
let co2_graph_green = 0;
let co2_graph_yellow = 0;
let co2_graph_red = 0;

if( co2 / ggp_quota_co2 <= 0.5 ) {
   co2_graph_green = co2;
}else if( co2 / ggp_quota_co2 <= 0.8) {
   co2_graph_green = ggp_quota_co2*0.5;
   co2_graph_yellow = co2 - co2_graph_green;
}else{
   co2_graph_green = ggp_quota_co2*0.5;
   co2_graph_yellow = ggp_quota_co2*0.3;
   co2_graph_red = co2 - co2_graph_green - co2_graph_yellow;
}

myChart.data.datasets[0].data[0] = co2_graph_green;
myChart.data.datasets[1].data[0] = co2_graph_yellow;
myChart.data.datasets[2].data[0] = co2_graph_red;
myChart.data.labels[0] = "二酸化炭素("+co2+"kg/"+ggp_quota_co2+"kg)";

myChart.update({duration: 0});

}

// 定期的（5000ms）に関数を実行する
window.addEventListener('load', function () {
    setInterval( function() {
                  get_ggp_msg_ajax();
                  get_ggp_earth_info_ajax();
                  get_ggp_msg_newest_ajax();
                  check_ggp_event_tree_ajax("0");
                  check_ggp_event_sales_ajax("0");
                  }, 5000);
}
);

function get_ggp_msg_ajax() {
let ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php', __FILE__ ) ); ?>';

$.ajax({
  type: 'POST',
  url: ajaxUrl,
  data: {
    'action' : 'get_ggp_msg_ajax',
    'earth_no' : '<?=$earth_no ?>',
    'nonce': '<?php echo wp_create_nonce( 'get_ggp_msg-nonce' ); ?>'
  },
  success: function( response ) {
    document.getElementById('ggp-msg').innerHTML = response;
  }
});
}

function get_ggp_msg_newest_ajax() {
let ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php', __FILE__ ) ); ?>';

$.ajax({
  type: 'POST',
  url: ajaxUrl,
  data: {
    'action' : 'get_ggp_msg_newest_ajax',
    'earth_no' : '<?=$earth_no ?>',
    'nonce': '<?php echo wp_create_nonce( 'get_ggp_msg_newest-nonce' ); ?>'
  },
  success: function( response ) {
    if( document.getElementById('popup_msg').innerHTML != response){
      document.getElementById('popup_msg').innerHTML = response;
      popup_msg();
    }
  }
});
}

function check_ggp_event_tree_ajax( initial ) {
let ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php', __FILE__ ) ); ?>';

$.ajax({
  type: 'POST',
  url: ajaxUrl,
  data: {
    'action' : 'check_ggp_event_tree_ajax',
    'earth_no' : '<?=$earth_no ?>',
    'nonce': '<?php echo wp_create_nonce( 'check_ggp_event_tree-nonce' ); ?>'
  },
  success: function( response ) {
    if(response != document.getElementById('event_valid_tree').innerHTML){
      document.getElementById("event_valid_tree").innerHTML = response;
      document.getElementById("popup_event_msg").style.display = "block";
      if(response != "" ){
        document.getElementById("popup_event_msg").innerHTML = "<?=$event_tree_msg ?>";
      }else{
        document.getElementById("popup_event_msg").innerHTML = "<?=$event_cancel_msg ?>";
      }
      window.setTimeout(window_reload, 10000);
    }
  }
});
}


function check_ggp_event_sales_ajax( initial ) {
let ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php', __FILE__ ) ); ?>';

$.ajax({
  type: 'POST',
  url: ajaxUrl,
  data: {

    'action' : 'check_ggp_event_sales_ajax',
    'earth_no' : '<?=$earth_no ?>',
    'nonce': '<?php echo wp_create_nonce( 'check_ggp_event_sales-nonce' ); ?>'
  },
  success: function( response ) {
    if(response != document.getElementById('event_valid_sales').innerHTML){
      document.getElementById("event_valid_sales").innerHTML = response;
      document.getElementById("popup_event_msg").style.display = "block";
      if(response != "" ){
        document.getElementById("popup_event_msg").innerHTML = "<?=$event_sales_msg ?>";
      }else{
        document.getElementById("popup_event_msg").innerHTML = "<?=$event_cancel_msg ?>";
      }
      window.setTimeout(window_reload, 10000);
    }
  }
});
}

function window_reload(){
  location.reload();
}


function get_ggp_earth_info_ajax() {
let ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php', __FILE__ ) ); ?>';

$.ajax({
  type: 'POST',
  url: ajaxUrl,
  data: {
    'action' : 'get_ggp_earth_info_ajax',
    'earth_no' : '<?=$earth_no ?>',
    'nonce': '<?php echo wp_create_nonce( 'get_ggp_earth_info-nonce' ); ?>'
  },
  success: function( response ) {
    document.getElementById('earth-info').innerHTML = response;
  }
});
}

/*
function get_ggp_earth_co2_ajax() {
let ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php', __FILE__ ) ); ?>';

$.ajax({
  type: 'POST',
  url: ajaxUrl,
  data: {
    'action' : 'get_ggp_earth_co2_ajax',
    'earth_no' : '<?=$earth_no ?>',
    'nonce': '<?php echo wp_create_nonce( 'get_ggp_earth_co2-nonce' ); ?>'
  },
  success: function( response ) {
    co2_graph_reload(response, <?=$ggp_quota_co2 ?>);
    document.getElementById('ggp_earth_co2').innerHTML = response;
  }
});
}
*/

/* カウントダウンタイマー */
var timer1; //タイマーを格納する変数（タイマーID）の宣言
var timer_status = 0;

//カウントダウン関数を1000ミリ秒毎に呼び出す関数
function cntStart()
{

  if(timer_status == 0) {
    timer_status = 1;
    document.getElementById("btn-timer").value ="ストップ";
    timer1=setInterval("countDown()",1000);
  } else {
    timer_status = 0;
    document.getElementById("btn-timer").value ="スタート";
    clearInterval(timer1);
  }

}

//タイマー停止関数
function cntStop()
{
  document.getElementById("btn-timer").value ="スタート";
  clearInterval(timer1);
}

//カウントダウン関数
function countDown()
{
  var min=document.timer.elements[0].value;
  var sec=document.timer.elements[1].value;

  if( (min=="") && (sec=="") )
  {
    alert("時刻を設定してください！");
    reSet();
  }
  else
  {
    if (min=="") min=0;
    min=parseInt(min);

    if (sec=="") sec=0;
    sec=parseInt(sec);

    tmWrite(min*60+sec-1);
  }
}

//残り時間を書き出す関数
function tmWrite(int)
{
  int=parseInt(int);

  if (int<=0)
  {
    reSet();
    alert("時間です！");
  }
  else
  {
    //残り分数はintを60で割って切り捨てる
    document.timer.elements[0].value=Math.floor(int/60);
    //残り秒数はintを60で割った余り
    document.timer.elements[1].value=int % 60;
  }
}

//フォームを初期状態に戻す（リセット）関数
function reSet()
{
  document.timer.elements[0].value="1";
  document.timer.elements[1].value="0";
  document.timer.elements[2].disabled=false;
  document.getElementById("btn-timer").value ="スタート";
  clearInterval(timer1);
}

//他チームのActionが発生したらポップアップを表示する
function popup_msg(){
    // ツールチップの class="invisible" を削除
    document.getElementById("popup_msg").style.display = "block";

  // 表示されたツールチップを隠す処理（ツールチップ領域以外をマウスクリックで隠す）
    $('html').mousedown(function(event){
        // イベント発生源である要素を取得（クリックされた要素を取得）
        var clickedElement = event.target;
 
        // 取得要素が"toolTip"クラスを持ってなかったら、ツールチップを非表示に
        if(!$(clickedElement).hasClass('popup_msg')){
            document.getElementById("popup_msg").style.display ="none";
        }
    });
}


</script>

<?php 
}
endif;


