<?php
/** ggp_setup.php
* @author arakawa asuka
* @date 2021/11/20
*/

require_once(get_stylesheet_directory().'/lib/db.php');

//非同期通信（地球毎のメッセージ取得）
//集約したため使っていない関数
/*
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

***/

//非同期通信（地球毎の最新メッセージのみ取得）
function my_wp_get_ggp_msg_newest_ajax() {

  $earth_no = $_POST['earth_no'];
  $msg_id = $_POST['msg_id'];
  $ggp_message = get_db_table_ggp_message_newest($earth_no, $msg_id);
  $return_param = Array();

  $nonce = $_REQUEST['nonce'];
  if ( wp_verify_nonce( $nonce, 'get_ggp_msg_newest-nonce' ) ) {
    for($i = 0; $i < count($ggp_message); $i++){
      array_push($return_param, Array('id' => $ggp_message[$i]->id, 'msg'=> $ggp_message[$i]->msg, 'important' => $ggp_message[$i]->important));
    }
    echo json_encode($return_param);
    exit;
  }
  die();
}
add_action( 'wp_ajax_get_ggp_msg_newest_ajax', 'my_wp_get_ggp_msg_newest_ajax' );
add_action( 'wp_ajax_nopriv_get_ggp_msg_newest_ajax', 'my_wp_get_ggp_msg_newest_ajax' );

//非同期通信（地球情報の更新）
//集約したため使っていない関数
/*
function my_wp_get_ggp_earth_info_ajax() {
  global $wpdb;
  $earth_no = $_POST['earth_no'];
  $team_no = $_POST['team_no'];
  $ggp_team = get_db_table_records_ggp(TABLE_NAME_GGP_TEAM,"earth_no",$earth_no);
  $ggp_quota_turn = get_option('ggp_quota_turn');

  $nonce = $_REQUEST['nonce'];
  if ( wp_verify_nonce( $nonce, 'get_ggp_earth_info-nonce' ) ) {
    //地球情報のテーブル作成
    $response_msg = '<table class="team_info">
    <tr><th width="100px"></th>
    ';
    for($i=0; $i < count($ggp_team); $i++) {
    $response_msg .= '<th>' . $ggp_team[$i]->teamname . '</th>';
    }
    $response_msg .= '</tr>
    <tr><td><i class="fas fa-heart"></i>残り回数</td>
    ';
    for($i=0; $i < count($ggp_team); $i++) {
      $response_msg .=  '<td>'. (intval($ggp_quota_turn) - intval($ggp_team[$i]->turn) + 1) . '/' . $ggp_quota_turn . 'ターン</td>';
    }
    $response_msg .= '</tr>
    <tr><td><i class="fas fa-coins"></i>お金</td>
    ';
    for($i=0; $i < count($ggp_team); $i++) {
      if($i == $team_no || $ggp_team[$team_no]->turn < $ggp_quota_turn/2 || $ggp_team[$team_no]->turn == (int)($ggp_quota_turn*0.8)){
        $response_msg .= '<td>' . $ggp_team[$i]->money .'万円</td>';
      }else{
        $response_msg .= '<td>???万円</td>';
      }
    }
    $response_msg .= '</tr>
    <tr><td><i class="fas fa-skull-crossbones"></i>二酸化炭素</td>
    ';
    for($i=0; $i < count($ggp_team); $i++) {
      if($i == $team_no || $ggp_team[$team_no]->turn < $ggp_quota_turn/2 || $ggp_team[$team_no]->turn == (int)($ggp_quota_turn*0.8)){
        $response_msg .= '<td>' . $ggp_team[$i]->co2 . 'kg/' . $ggp_team[$i]->co2_quota . 'kg</td>';
      }else{
        $response_msg .= '<td>???kg/???kg</td>';
      }
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
*/

//非同期通信（地球毎の二酸化炭素排出量取得）

function my_wp_get_ggp_earth_co2_ajax() {
  global $wpdb;
  $earth_no = $_POST['earth_no'];
  $team_no = $_POST['team_no'];
  $ggp_team = get_db_table_records_ggp(TABLE_NAME_GGP_TEAM,"earth_no",$earth_no);
  $ggp_quota_turn = get_option('ggp_quota_turn');
  $ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);
  $ggp_message = get_db_table_ggp_message($earth_no);
  //error_log()

  $nonce = $_REQUEST['nonce'];
  if ( wp_verify_nonce( $nonce, 'get_ggp_earth_co2-nonce' ) ) {
    //各チームの情報（テーブルHTML）の作成
    $response_msg = '<table class="team_info">
    <tr><th width="100px"></th>
    ';
    for($i=0; $i < count($ggp_team); $i++) {
    $response_msg .= '<th>' . $ggp_team[$i]->teamname . '</th>';
    }
    $response_msg .= '</tr>
    <tr><td><i class="fas fa-heart"></i>残り回数</td>
    ';
    for($i=0; $i < count($ggp_team); $i++) {
      $response_msg .=  '<td>'. (intval($ggp_quota_turn) - intval($ggp_team[$i]->turn) + 1) . '/' . $ggp_quota_turn . 'ターン</td>';
    }
    $response_msg .= '</tr>
    <tr><td><i class="fas fa-coins"></i>お金</td>
    ';
    for($i=0; $i < count($ggp_team); $i++) {
      if($i == $team_no || $ggp_team[$team_no]->turn < $ggp_quota_turn/2 || $ggp_team[$team_no]->turn == (int)($ggp_quota_turn*0.8)){
        $response_msg .= '<td>' . $ggp_team[$i]->money .'万円</td>';
      }else{
        $response_msg .= '<td>???万円</td>';
      }
    }
    $response_msg .= '</tr>
    <tr><td><i class="fas fa-skull-crossbones"></i>二酸化炭素</td>
    ';
    for($i=0; $i < count($ggp_team); $i++) {
      if($i == $team_no || $ggp_team[$team_no]->turn < $ggp_quota_turn/2 || $ggp_team[$team_no]->turn == (int)($ggp_quota_turn*0.8)){
        $response_msg .= '<td>' . $ggp_team[$i]->co2 . 'kg/' . $ggp_team[$i]->co2_quota . 'kg</td>';
      }else{
        $response_msg .= '<td>???kg/???kg</td>';
      }
    }
    $response_msg .= '
    </tr>
    </table>
    ';

    //メッセージ一覧の取得
    $all_msg = "";
    for($i = 0; $i < count($ggp_message); $i++) {
      $all_msg .= '<p class="p-ggp-msg">' . $ggp_message[$i]->msg . '</p>';
    }

    // レスポンスメッセージ作成
    $return_param = [ 'co2' => $ggp_earth[0]->co2,
                      'quota_co2' => $ggp_earth[0]->co2_quota,
                      'earth_info_html' => $response_msg,
                      'all_msg' => $all_msg,
                      'event_tree_arise' => $ggp_earth[0]->event_valid_tree,
                      'event_sales_arise' => $ggp_earth[0]->event_valid_sales ];
    echo json_encode($return_param);
    exit;
  }
  die();
}
add_action( 'wp_ajax_get_ggp_earth_co2_ajax', 'my_wp_get_ggp_earth_co2_ajax' );
add_action( 'wp_ajax_nopriv_get_ggp_earth_co2_ajax', 'my_wp_get_ggp_earth_co2_ajax' );


//非同期通信（チームの詳細情報取得）
//集約したため使っていない関数
/*
function my_wp_get_ggp_team_description_ajax() {
  $earth_no = $_POST['earth_no'];
  $team_no = $_POST['team_no'];
  $ggp_team = get_db_table_records_ggp(TABLE_NAME_GGP_TEAM,"earth_no",$earth_no);

  $nonce = $_REQUEST['nonce'];
  if ( wp_verify_nonce( $nonce, 'get_ggp_team_description-nonce' ) ) {
    $response_msg = "";
    $response_msg .= '<i class="fas fa-heart"></i>ターン：'. $ggp_team[$team_no]->turn .'ターン目<br>';
    $response_msg .= '<i class="fas fa-coins"></i>お金：' . $ggp_team[$team_no]->money .'万円<br>';
    $response_msg .= '<i class="fas fa-skull-crossbones"></i>CO2：'. $ggp_team[$team_no]->co2 .'kg<br>';
    echo $response_msg;
  }
  die();
}
add_action( 'wp_ajax_get_ggp_team_description_ajax', 'my_wp_get_ggp_team_description_ajax' );
add_action( 'wp_ajax_nopriv_get_ggp_team_description_ajax', 'my_wp_get_ggp_team_description_ajax' );
*/


//非同期通信（植樹イベント発生）
//集約したため使っていない関数

/**
function my_wp_check_ggp_event_tree_ajax() {
  error_log("in ggp_event_tree_ajax");
  $earth_no = $_POST['earth_no'];
  $nonce = $_REQUEST['nonce'];
  $response_msg = "";
  $ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);

  if ( wp_verify_nonce( $nonce, 'check_ggp_event_tree-nonce' ) ) {
    $response_msg = $ggp_earth[0]->event_valid_tree;
    echo $response_msg;
  }
  die();
}
add_action( 'wp_ajax_check_ggp_event_tree_ajax', 'my_wp_check_ggp_event_tree_ajax' );
add_action( 'wp_ajax_nopriv_check_ggp_event_tree_ajax', 'my_wp_check_ggp_event_tree_ajax' );

//非同期通信（売り上げ増加イベント発生）
//集約したため使っていない関数
function my_wp_check_ggp_event_sales_ajax() {
  error_log("in ggp_event_sales_ajax");

  $earth_no = $_POST['earth_no'];
  $nonce = $_REQUEST['nonce'];
  $response_msg = "";
  $ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);

  if ( wp_verify_nonce( $nonce, 'check_ggp_event_sales-nonce' ) ) {
    $response_msg = $ggp_earth[0]->event_valid_sales;
    echo $response_msg;
  }
  die();
}
add_action( 'wp_ajax_check_ggp_event_sales_ajax', 'my_wp_check_ggp_event_sales_ajax' );
add_action( 'wp_ajax_nopriv_check_ggp_event_sales_ajax', 'my_wp_check_ggp_event_sales_ajax' );

**/

//非同期通信（イベント発生）
function my_wp_check_ggp_event_ajax(){
  error_log("in ggp_event_ajax[*********************]");
  $earth_no = $_POST['earth_no'];
  $response_msg = "";
  $ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);

  $nonce = $_REQUEST['nonce'];
  if ( wp_verify_nonce( $nonce, 'check_ggp_event-nonce' ) ) {
    $return_param = [ 'event_tree_arise' => $ggp_earth[0]->event_valid_tree,
                      'event_sales_arise' => $ggp_earth[0]->event_valid_sales ];
    echo json_encode($return_param);
    exit;
  }
  die();
}
add_action( 'wp_ajax_check_ggp_event_ajax', 'my_wp_check_ggp_event_ajax' );
add_action( 'wp_ajax_nopriv_check_ggp_event_ajax', 'my_wp_check_ggp_event_ajax' );


