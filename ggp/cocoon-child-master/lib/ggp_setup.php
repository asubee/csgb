<?php
/** ggp_setup.php
* @author arakawa asuka
* @date 2021/05/02
*/

require_once(get_stylesheet_directory().'/lib/db.php');
require_once(get_stylesheet_directory().'/lib/ggp_ajax.php');
require_once(get_stylesheet_directory().'/lib/ggp_game_event.php');

add_action('admin_menu', 'ggp_setup');

function ggp_setup(){

  add_menu_page('GGPゲーム設定','GGPゲーム設定','manage_options','ggp_setup','add_ggp_setup','dashicons-admin-generic',4);
  add_submenu_page('ggp_setup','ゲーム設定','ゲーム設定','manage_options','ggp_game_setup','add_ggp_game_setup',1);
  add_submenu_page('ggp_setup','チーム設定','チーム設定','manage_options','ggp_team_setup','add_ggp_team_setup',2);
  add_submenu_page('ggp_setup','状況監視','状況監視','manage_options','ggp_game_watch','add_ggp_game_watch',3);
  add_submenu_page('ggp_setup','ロールバック','ロールバック','manage_options','ggp_rollback','add_ggp_rollback',4);
  remove_submenu_page('ggp_setup','ggp_setup');
  add_action('admin_init','register_ggp_game_setup');
  add_action('admin_init','register_ggp_team_setup');
  add_action('admin_init','register_ggp_game_watch');
  add_action('admin_init','register_ggp_rollback');

}

// 特になにも表示しない
function add_ggp_setup(){
}

/****************************************************************
* tag: admin_game_setup
* 【管理画面】ゲーム設定
****************************************************************/
function register_ggp_game_setup(){
  register_setting('ggp_game_setup_group','ggp_quota_turn');
  register_setting('ggp_game_setup_group','ggp_quota_co2');
  register_setting('ggp_game_setup_group','ggp_init_money');
  register_setting('ggp_game_setup_group','ggp_init_quota_co2_perteam');
  register_setting('ggp_game_setup_group','ggp_init_sales');
  register_setting('ggp_game_setup_group','ggp_init_earth');
  register_setting('ggp_game_setup_group','ggp_init_perteam');
  register_setting('ggp_game_setup_group','ggp_cardcount');
  register_setting('ggp_game_setup_group','ggp_phase_name');
  register_setting('ggp_game_setup_group','ggp_msg_array');
  register_setting('ggp_game_setup_group','ggp_url_array');
  register_setting('ggp_game_setup_group','ggp_event_preselect');
  register_setting('ggp_game_setup_group','ggp_event_tree');
  register_setting('ggp_game_setup_group','ggp_event_tree_magnification');
  register_setting('ggp_game_setup_group','ggp_event_sales');
  register_setting('ggp_game_setup_group','ggp_event_sales_magnification');
  register_setting('ggp_game_setup_group','ggp_event_arise_turn');
  register_setting('ggp_game_setup_group','ggp_event_not_clean_energy');
  register_setting('ggp_game_setup_group','ggp_event_emission_trading');
  register_setting('ggp_game_setup_group','ggp_event_reduction_quota_co2');
  register_setting('ggp_game_setup_group','ggp_event_subsidy');
  register_setting('ggp_game_setup_group','ggp_event_tax');
  register_setting('ggp_game_setup_group','ggp_event_direct_store');
  register_setting('ggp_game_setup_group','ggp_event_popularity');
  register_setting('ggp_game_setup_group','ggp_event_scandal');
  register_setting('ggp_game_setup_group','ggp_event_protect_environment_action');
  register_setting('ggp_game_setup_group','ggp_event_restriction_gascars');
  register_setting('ggp_game_setup_group','ggp_other_clean_energy');
  register_setting('ggp_game_setup_group','ggp_other_solar_panel');
  register_setting('ggp_game_setup_group','ggp_other_eco_package');
  register_setting('ggp_game_setup_group','ggp_other_cm');
}

function add_ggp_game_setup(){
$ggp_cardinfo_db_post = $_POST['ggp_cardinfo_db'];
$ggp_cardinfo_db = get_table_ggp_cardinfo();
$ggp_cardcount = get_option('ggp_cardcount');
$ggp_phase_name = get_option('ggp_phase_name');
$ggp_msg_array = get_option('ggp_msg_array');
$ggp_url_array = get_option('ggp_url_array');
$ggp_reduction_valid = get_option('ggp_reduction_valid');
$ggp_event_preselect = get_option('ggp_event_preselect');
$ggp_event_list = get_ggp_event_list();
$ggp_event_tree = get_option('ggp_event_tree');
$ggp_event_sales = get_option('ggp_event_sales');
$ggp_event_subsidy = get_option('ggp_event_subsidy');
$ggp_event_tax = get_option('ggp_event_tax');
$ggp_event_emission_trading = get_option('ggp_event_emission_trading');
$ggp_event_reduction_quota_co2 = get_option('ggp_event_reduction_quota_co2');
$ggp_event_tree_magnification = "2";
$ggp_event_sales_magnification = "0.1";
$ggp_event_arise_turn = get_option('ggp_event_arise_turn');
$ggp_event_not_clean_energy = get_option('ggp_event_not_clean_energy');
$ggp_event_direct_store = get_option('ggp_event_direct_store');
$ggp_event_popularity = get_option('ggp_event_popularity');
$ggp_event_scandal = get_option('ggp_event_scandal');
$ggp_event_protect_environment_action = get_option('ggp_event_protect_environment_action');
$ggp_event_restriction_gascars = get_option('ggp_event_restriction_gascars');
$ggp_other_clean_energy = get_option('ggp_other_clean_energy');
$ggp_other_solar_panel = get_option('ggp_other_solar_panel');
$ggp_other_eco_package = get_option('ggp_other_eco_package');
$ggp_other_cm = get_option('ggp_other_cm');



if(!is_array($ggp_event_protect_environment_action)){
  $ggp_event_protect_environment_action = array_fill(0, 5, "");
}

if(!is_array($ggp_event_not_clean_energy)){
  $ggp_event_not_clean_energy =array_fill(0, 5, "");
}

if(!is_array($ggp_event_restriction_gascars)){
  $ggp_event_restriction_gascars =array_fill(0, 5, "");
}

if(!is_array($ggp_phase_name)){
  $ggp_phase_name =Array('grow'=>'','to_factory'=>'', 'make'=>'', 'to_store'=>'', 'reduction'=>'', 'sales'=>'', 'event'=>'');
}

if(!is_array($ggp_msg_array) || !is_array($ggp_url_array)){
  $template = Array('grow'=>'','to_factory'=>'', 'make'=>'', 'to_store'=>'', 'reduction'=>'', 'tree'=>'','sales'=>'', 'direct_store'=>'','solar_panel'=>'');
  $ggp_url_array = $template;
  $ggp_msg_array = $template;
}

if(!is_array($ggp_event_arise_turn)){
  $ggp_event_arise_turn = array_fill(0, 3, "");
}

if(!is_array($ggp_event_preselect)){
  $ggp_event_preselect = array_fill(0, 3, "");
}

// カード情報の削除(DB修正)
if(false){
   reset_table_ggp_cardinfo();
   $ggp_cardinfo_db = get_table_ggp_cardinfo();
}

// カード情報が何もなかった場合に初期化する（箱だけ作成）
if( !is_array($ggp_cardinfo_db) || count($ggp_cardinfo_db) == 0 ){
  $ggp_cardcount = Array('grow'=>5,'to_factory'=>5, 'make'=>5, 'to_store'=>5, 'reduction'=>10);
  foreach($ggp_cardcount as $key => $value){
    for($i = 0; $i < (int)$value; $i++){
      insert_table_ggp_cardinfo($key,"","","","",0,0,0,0,0,0);
    }
  }
  $ggp_cardinfo_db = get_table_ggp_cardinfo();
}

// カード情報が更新された場合
if(isset($ggp_cardinfo_db_post) && is_array($ggp_cardinfo_db_post)){
  reset_table_ggp_cardinfo();
  foreach($ggp_cardinfo_db_post as $value){
    insert_table_ggp_cardinfo(
      $value['phase'],
      $value['name'],
      $value['description'],
      $value['url'],
      $value['keyword'],
      $value['money'],
      $value['rice'],
      $value['co2'],
      $value['turn'],
      (isset($value['is_visible'])?"1":NULL),
      (isset($value['is_valid'])?"1":NULL),
      );
  }
  $ggp_cardinfo_db = get_table_ggp_cardinfo();
}


$tree_card_no = 0;
// key=treeとなっているカードの番号を探索する
for($tree_card_no = 0; $tree_card_no < count($ggp_cardinfo_db); $tree_card_no++){
  if($ggp_cardinfo_db[$tree_card_no]->keyword == 'tree') break;
}

?>
<div class="wrap">
  <h2>ゲーム設定</h2>
  <div class="metabox-holder">
  <form method="post" action="options.php" enctype="multipart/form-data" encoding="multipart/form-data">
  <?php
    settings_fields('ggp_game_setup_group');
    do_settings_sections('ggp_game_setup_group');
  ?>
  <div class="postbox">
    <h3 class="hndle section"><span>ゲーム初期設定</span></h3>
    <div class="inside">
      <div class="main">
        <table class="setup_parameter">
          <tr><td>ターン数</td><td><input type="text" id="ggp_quota_turn" name="ggp_quota_turn" value="<?php echo get_option('ggp_quota_turn'); ?>">ターン</td></tr>
          <tr><td>CO2排出上限</td><td><input type="text" id="ggp_quota_co2" name="ggp_quota_co2" value="<?php echo get_option('ggp_quota_co2'); ?>">kg/地球 (3チーム制：1,500kg、4チーム制：1,800kgが目安）</td></tr>
          <tr><td>スタート時の所持金額</td><td><input type="text" id="ggp_init_money" name="ggp_init_money" value="<?php echo get_option('ggp_init_money'); ?>">万円</td></tr>
          <tr><td>各チームのCO2排出上限</td><td><input type="text" id="ggp_init_quota_co2_perteam" name="ggp_init_quota_co2_perteam" value="<?php echo get_option('ggp_init_quota_co2_perteam'); ?>">kg/チーム</td></tr>
          <tr><td>売上設定（５トン毎）</td><td><input type="text" id="ggp_init_sales" name="ggp_init_sales" value="<?php echo get_option('ggp_init_sales'); ?>">万円</td></tr>
          <tr><td>地球の個数</td><td><input type="text" id="ggp_init_earth" name="ggp_init_earth" value="<?php echo get_option('ggp_init_earth'); ?>">個</td></tr>
          <tr><td>地球毎のチーム数</td><td><input type="text" id="ggp_init_perteam" name="ggp_init_perteam" value="<?php echo get_option('ggp_init_perteam'); ?>">チーム／地球</td></tr>
        </table>
      </div> <!-- main -->
    </div>   <!-- inside -->
    <h3 class="hndle section"><span>イベント設定</span></h3>
    <div class="inside">
        <div class="main">
          <h4>デフォルトイベント</h4>
          <p>経済活動１回目終了後（デモ時はOffにすること）</p>
          <p><input type="checkbox" id="ggp_event_tree" name="ggp_event_tree" value="1" <?php checked(1, $ggp_event_tree); ?>>&nbsp;植樹の効果<?=$ggp_event_tree_magnification ?>倍（チェックをつけると有効）<?php if($ggp_event_tree != NULL){ ?> <font color="red">植林の効果<?=$ggp_event_tree_magnification ?>倍発動中(<?=$ggp_cardinfo_db[$tree_card_no]->co2 ?>kg/毎ターンから<?=$ggp_cardinfo_db[$tree_card_no]->co2*$ggp_event_tree_magnification ?>kg/毎ターンに増加）</font> <?php }; ?>
            <input type="hidden" id="ggp_event_tree_magnification" name="ggp_event_tree_magnification" value=<?=$ggp_event_tree_magnification ?>>
          <p>経済活動２回目終了後</p>
          <p><input type="checkbox" id="ggp_event_sales" name="ggp_event_sales" value="1" <?php checked(1, $ggp_event_sales); ?>>&nbsp;売り上げ倍増：植樹回数&#x2613;<?=$ggp_event_sales_magnification ?>倍（チェックをつけると有効）<?php if($ggp_event_sales != NULL){ ?> <font color="red">売り上げ倍増発動中(植樹回数&#x2613;<?=$ggp_event_sales_magnification; ?>倍に増加）</font> <?php }; ?>
            <input type="hidden" id="ggp_event_sales_magnification" name="ggp_event_sales_magnification" value=<?=$ggp_event_sales_magnification ?>>
          <h4>ルーレットイベント</h4>
          <table class="setup_parameter">
          <tr><td>-</td><td>イベント発生ターン</td><td>
          <?php for($i = 0; $i < count($ggp_event_arise_turn); $i++){
          ?><input type="text" id="ggp_event_arise_turn[<?=$i ?>]" name="ggp_event_arise_turn[<?=$i ?>]" value="<?=$ggp_event_arise_turn[$i] ?>">
          <?php } ?></td>
          <tr><td>-</td><td>イベントの設定<br>(初期化時にリセットされます)</td><td>
          <?php for($i = 0; $i < count($ggp_event_preselect); $i++){
          ?><select id="ggp_event_preselect[<?=$i ?>]" name="ggp_event_preselect[<?=$i ?>]">
              <option value="">ランダム</option>
              <?php for($j = 0; $j < count($ggp_event_list); $j++){ ?> <option value="<?=$j ?>" <?php if($ggp_event_preselect[$i]==$j){ ?>selected<?php } ?>><?=$ggp_event_list[$j]['subject'] ?> </option> <?php } ?>
            </select>
          <?php } ?></td>
          <tr><td>(1)</td><td>台風で田んぼと工場がやられる</td><td>(設定値なし)</td></tr>
          <tr><td>(2)</td><td>電気自動車を使った会社に補助金</td><td><input type="text" id="ggp_event_subsidy" name="ggp_event_subsidy" value="<?=$ggp_event_subsidy ?>">万円／電気自動車を使った回数</td></tr>
          <tr><td>(3)</td><td>ガソリン車を使った会社に税金</td><td><input type="text" id="ggp_event_tax" name="ggp_event_tax" value="<?=$ggp_event_tax ?>">万円／ガソリン車を使った回数</td></tr>
          <tr><td>(4)</td><td>クリーン電力を使用<br>「工場」で選択不可とするカードのキー値</td>
          <td>
          <?php for($i = 0; $i < count($ggp_event_not_clean_energy); $i++){ ?>
          <input type="text" id="ggp_event_not_clean_energy[<?=$i ?>]" name="ggp_event_not_clean_energy[<?=$i ?>]" value="<?=$ggp_event_not_clean_energy[$i]; ?>">
          <? } ?>
          </td></tr>
          <tr><td>(5)</td><td>Co2排出量上限の引き下げ</td><td><input type="text" id="ggp_event_reduction_quota_co2" name="ggp_event_reduction_quota_co2" value="<?=$ggp_event_reduction_quota_co2 ?>">kg/地球</td></tr>
          <tr><td>(6)</td><td>工場に直売所を設置<br>売り上げ金額</td><td><input type="text" id="ggp_event_direct_store" name="ggp_event_direct_store" value="<?=$ggp_event_direct_store ?>">万円</td></tr>
          <tr><td>(7)</td><td>人気が出て売り上げUp</td><td><input type="text" id="ggp_event_popularity" name="ggp_event_popularity" value="<?=$ggp_event_popularity ?>">倍</td></tr>
          <tr><td>(8)</td><td>虫が入っていて売り上げDown</td><td><input type="text" id="ggp_event_scandal" name="ggp_event_scandal" value="<?=$ggp_event_scandal ?>">倍</td></tr>
          <tr><td>(9)</td><td>環境に良い取り組みに補助金<br>環境に良い取り組みのカードのキー値</td>
          <td>
          <?php for($i = 0; $i < count($ggp_event_protect_environment_action); $i++){ ?>
          <input type="text" id="ggp_event_protect_environment_action[<?=$i ?>]" name="ggp_event_protect_environment_action[<?=$i ?>]" value="<?=$ggp_event_protect_environment_action[$i]; ?>">
          <? } ?>
          </td></tr>
          <tr><td>(10)</td><td>ガソリン車禁止<br>選択不可とするガソリン車のキー値</td>
          <td>
          <?php for($i = 0; $i < count($ggp_event_restriction_gascars); $i++){ ?>
          <input type="text" id="ggp_event_restriction_gascars[<?=$i ?>]" name="ggp_event_restriction_gascars[<?=$i ?>]" value="<?=$ggp_event_restriction_gascars[$i]; ?>">
          <? } ?>
          </td></tr>
          </table>
      </div> <!-- main -->
    </div>   <!-- inside -->
    <h3 class="hndle section"><span>その他カード設定</span></h3>
    <div class="inside">
      <div class="main">
        <table class="setup_parameter">
        <tr><td>(1)</td><td>工場のクリーンエネルギー宣言による売り上げUp</td><td><input type="text" id="ggp_other_clean_energy" name="ggp_other_clean_energy" value="<?=$ggp_other_clean_energy ?>">倍（増分のみ）</td></tr>
        <tr><td>(2)</td><td>ソーラーパネルを設置</td><td><input type="text" id="ggp_other_solar_panel" name="ggp_other_solar_panel" value="<?=$ggp_other_solar_panel ?>">kg/ターン削減</td></tr>
        <tr><td>(3)</td><td>エコパッケージを使うことでCo2削減</td><td><input type="text" id="ggp_other_eco_package" name="ggp_other_eco_package" value="<?=$ggp_other_eco_package ?>">倍</td></tr>
        <tr><td>(4)</td><td>CMを打つことで売り上げUp</td><td><input type="text" id="ggp_other_cm" name="ggp_other_cm" value="<?=$ggp_other_cm ?>">倍</td></tr>
        </table>
      </div> <!-- main -->
    </div>   <!-- inside -->
    <h3 class="hndle section"><span>表示メッセージ設定</span></h3>
    <div class="inside">
      <div class="main">
        <h4>各フェーズの表示文字列(ggp_phase_name)</h4>
        <table>
          <tr><th>キー値</th><th>表示文字列</th></tr>
          <?php foreach(array_keys($ggp_phase_name) as $iterator){ ?>
          <tr><td><input type="text" readonly value="<?=$iterator ?>"></td><td><input type="text" id="ggp_phase_name[<?=$iterator ?>]" name="ggp_phase_name[<?=$iterator ?>]" value="<?=$ggp_phase_name[$iterator] ?>" style="width:300px;"></td></tr>
          <?php } ?>
          </table>
          </div>
          <div class="main">
          <h4>各フェーズのメッセージ文言(ggp_msg_array, ggp_url_array):各チームの行動欄やメッセージ欄に表示される文言</h4>
          <table>
          <tr><th>キー値</th><th>表示文字列</th><th>画像ファイル名</th></tr>
          <?php foreach(array_keys($ggp_msg_array) as $iterator){ ?>
          <tr><td><input type="text" readonly value="<?=$iterator ?>"></td>
              <td><input type="text" id="ggp_msg_array[<?=$iterator ?>]" name="ggp_msg_array[<?=$iterator ?>]" value="<?=$ggp_msg_array[$iterator] ?>" style="width:300px;"></td>
              <td><input type="text" id="ggp_url_array[<?=$iterator ?>]" name="ggp_url_array[<?=$iterator ?>]" value="<?=$ggp_url_array[$iterator] ?>" style="width:300px;"></td>
          </tr>
          <?php } ?>
          </table>
      </div> <!-- main -->
    </div>   <!-- inside -->
    <h3 class="hndle section"><span>変更を保存</span></h3>
    <div class="inside">
      <div class="main">
      <h4 style="color:red;"><span>ゲーム設定を変更する場合は、ゲームをリセットしてください。（チーム設定＞チーム設定初期化）</span></h4>
        <?php submit_button(); ?>
      </div> <!-- main -->
    </div>   <!-- inside -->
  </div>     <!-- postbox -->
  </form>
  </div>     <!-- metabox-holder -->
</div> <!-- wrap -->
<div class="wrap">
  <h2>カード設定</h2>
  <div class="metabox-holder">
  <form method="post" action="">
    <div class="postbox">
    <h3 class="hndle section"><span>カード設定</span></h3>
    <div class="inside">
    <div class="main">
    <span>カード設定を変更する場合は、ゲームをリセットしてください。（チーム設定＞チーム設定初期化）</span>
    <?php 
      for($i = 0; $i < count($ggp_cardinfo_db); $i++){
      if($i == 0){ ?>
        <h4><span><?=$ggp_phase_name[$ggp_cardinfo_db[$i]->phase] ?></span></h4>
        <table class="ggp_game_table">
        <tr><th style="width:10%;">名称</th><th style="width:20%;">説明</th><th style="width:10%;">画像ファイル名</th><th style="width:10%;">キーワード</th><th style="width:5%;">必要金額[万円]</th><th style="width:5%;">米の量[トン]</th><th style="width:5%;">CO2排出量[kg]</th><th style="width:5%;">必要ターン数</th><th style="width:2%;">表示</th><th style="width:2%;">有効</th></tr>
      <?php }else if($ggp_cardinfo_db[$i-1]->phase != $ggp_cardinfo_db[$i]->phase){ ?>
        <h4><span><?=$ggp_phase_name[$ggp_cardinfo_db[$i]->phase] ?></span></h4>
        <table class="ggp_game_table">
        <tr><th style="width:10%;">名称</th><th style="width:20%;">説明</th><th style="width:10%;">画像ファイル名</th><th style="width:10%;">キーワード</th><th style="width:5%;">必要金額[万円]</th><th style="width:5%;">米の量[トン]</th><th style="width:5%;">CO2排出量[kg]</th><th style="width:5%;">必要ターン数</th><th style="width:2%;">表示</th><th style="width:2%;">有効</th></tr>
      <?php } ?>

      <tr>
      <input type="hidden" id="ggp_cardinfo_db[<?=$i ?>][phase]" name="ggp_cardinfo_db[<?=$i ?>][phase]" value="<?=$ggp_cardinfo_db[$i]->phase ?>">
      <td><input type="text" id="ggp_cardinfo_db[<?=$i ?>][name]" name="ggp_cardinfo_db[<?=$i ?>][name]" value="<?=$ggp_cardinfo_db[$i]->name ?>" style="width:100%;"></td>
      <td><input type="text" id="ggp_cardinfo_db[<?=$i ?>][description]" name="ggp_cardinfo_db[<?=$i ?>][description]" value="<?=$ggp_cardinfo_db[$i]->description ?>" style="width:100%;"></td>
      <td><input type="text" id="ggp_cardinfo_db[<?=$i ?>][url]" name="ggp_cardinfo_db[<?=$i ?>][url]" value="<?=$ggp_cardinfo_db[$i]->url ?>" style="width:100%;"></td>
      <td><input type="text" id="ggp_cardinfo_db[<?=$i ?>][keyword]" name="ggp_cardinfo_db[<?=$i ?>][keyword]" value="<?=$ggp_cardinfo_db[$i]->keyword ?>" style="width:100%;"></td>
      <td><input type="text" id="ggp_cardinfo_db[<?=$i ?>][money]" name="ggp_cardinfo_db[<?=$i ?>][money]" value="<?=$ggp_cardinfo_db[$i]->money ?>" style="width:100%;"></td>
      <td><input type="text" id="ggp_cardinfo_db[<?=$i ?>][rice]" name="ggp_cardinfo_db[<?=$i; ?>][rice]" value="<?=$ggp_cardinfo_db[$i]->rice ?>" <?php if($ggp_cardinfo_db[$i]->phase == "reduction"){ ?>disabled<?php } ?> style="width:100%;"></td>
      <td><input type="text" id="ggp_cardinfo_db[<?=$i ?>][co2]" name="ggp_cardinfo_db[<?=$i ?>][co2]" value="<?=$ggp_cardinfo_db[$i]->co2 ?>" style="width:100%;"></td>
      <td><input type="text" id="ggp_cardinfo_db[<?=$i ?>][turn]" name="ggp_cardinfo_db[<?=$i ?>][turn]" value="<?=$ggp_cardinfo_db[$i]->turn ?>" style="width:100%;"></td>
      <td style="text-align:center;"><input type="checkbox" id="ggp_cardinfo_db[<?=$i ?>][is_visible]" name="ggp_cardinfo_db[<?=$i ?>][is_visible]" value="1" <?php checked(1, $ggp_cardinfo_db[$i]->is_visible); ?>></td>
      <td style="text-align:center;"><input type="checkbox" id="ggp_cardinfo_db[<?=$i ?>][is_valid]" name="ggp_cardinfo_db[<?=$i ?>][is_valid]" value="1" <?php checked(1, $ggp_cardinfo_db[$i]->is_valid); ?>></td>
      </tr>
      <?php if($i == count($ggp_cardinfo_db) - 1 || $ggp_cardinfo_db[$i]->phase != $ggp_cardinfo_db[$i+1]->phase){ ?>
        </table>
  <?php }
      }
  ?><h4 style="color:red;"><span>カード設定を変更する場合は、ゲームをリセットしてください。（チーム設定＞チーム設定初期化）</span></h4>
  <?php submit_button(); ?>
      </div> <!-- main -->
    </div>   <!-- inside -->
  </div>     <!-- postbox -->
  </form>
  </div>  <!-- metabox-holder -->
</div> <!-- wrap -->
<?php
}

function register_ggp_team_setup(){

}

/****************************************************************
* tag: admin_team_setup
* 【管理画面】チーム設定
****************************************************************/
// チーム設定管理画面の出力
function add_ggp_team_setup(){
global $wpdb;

$is_reset = $_POST['reset'];
$earth_no = $_POST['earth_no'];
$team_no = $_POST['team_no'];
$teamname = $_POST['teamname'];
$money = $_POST['money'];
$co2 = $_POST['co2'];
$earth_update = $_POST['earth_update'];

if($is_reset == "初期化"){
  reset_table_ggp_all();
  $is_reset = "";
}else if($is_reset == "トランザクション初期化"){
  reset_table_ggp_transaction();
  $is_reset = "";
}else if($earth_update == true ){
      update_table_ggp_earth($earth_no, $co2);
}else{
  if($teamname != NULL){
    for($i = 0; $i < count($teamname); $i++){
      update_table_ggp_team($earth_no[$i], $team_no[$i], $teamname[$i], $money[$i], $co2[$i]);
    }
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
          <form method="post" action="">
          <table class="ggp_team_table">
          <tr><th style="width:5%">地球</th><th style="width:5%;">チーム</th><th style="width=20%;">チーム名</th><th style="width:10%;">ターン</th><th style="width:10%;">お金</th><th style="width:10%;">CO2排出量</th></tr>
          <?php
          for($i = 0; $i < count($ggp_team); $i++){
          ?>
          <tr>
          <td><input type="text" id="earth_no[<?=$i ?>]" name="earth_no[<?=$i ?>]" value=<?=$ggp_team[$i]->earth_no ?> readonly style="width:100%;"></td>
          <td><input type="text" id="team_no[<?=$i ?>]" name="team_no[<?=$i ?>]" value=<?=$ggp_team[$i]->team_no ?> readonly style="width:100%;"></td>
          <td><input type="text" id="teamname[<?=$i ?>]" name="teamname[<?=$i ?>]" value=<?=$ggp_team[$i]->teamname ?> style="width:100%;"></td>
          <td><input type="text" value=<?=$ggp_team[$i]->turn ?> readonly style="width:100%;"></td>
          <td><input type="text" id="money[<?=$i ?>]" name="money[<?=$i ?>]" value = <?=$ggp_team[$i]->money ?> style="width:100%;"></td>
          <td><input type="text" id="co2[<?=$i ?>]" name="co2[<?=$i ?>]" value = <?=$ggp_team[$i]->co2 ?> style="width:100%;"></td></tr>
          <?php } ?>
          </table>
          <?php submit_button("チーム設定を更新",'primary','update',false) ?></form>
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
        <td><input type="text" id="earth_no" name="earth_no" value=<?= $ggp_earth[$i]->earth_no ?> readonly style="width:100%;"></td>
        <td><input type="text" id="co2" name="co2" value=<?=$ggp_earth[$i]->co2 ?> style="width:100%;"></td><input type="hidden" id="earth_update" name="earth_update" value=true></td>
          <td><?php submit_button("更新",'small','update',false) ?></td></form></tr>
      <?php } ?>
      </table>
      </div>
    </div>
  </form>
  <form method="post" action="">
    <?php
    settings_fields('ggp_team_setup_group');
    do_settings_sections('ggp_team_setup_group');
     ?>
    <div class="metabox-holder">
      <div class="postbox">
        <h3 class="hndle"><span>チームトランザクション初期化</span></h3>
        <div class="inside">
        <input type="hidden" name="reset" value="reset">
        <p>各トランザクションデータを初期化します。以下の情報がリセットされます。</p>
        <p>・地球の情報（CO2排出量、排出量上限)<br>
        ・チームの情報（お金、CO2排出量、CO2排出量上限、現在のターン)<br>
        ・各チームの活動記録</p>
        <?php submit_button("トランザクション初期化",'delete','reset',true); ?>
        </div>
      </div>
    </div>
  </form>

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

/****************************************************************
* tag: admin_game_watch
* 【管理画面】ゲーム状況監視
****************************************************************/
function register_ggp_game_watch(){
}

function add_ggp_game_watch(){
$ggp_team = get_db_table_records(TABLE_NAME_GGP_TEAM,'');
$ggp_earth = get_db_table_records(TABLE_NAME_GGP_EARTH,'');
$ggp_action_rice = get_db_table_records(TABLE_NAME_GGP_ACTION_RICE,'');
$ggp_action_tree = get_db_table_records(TABLE_NAME_GGP_ACTION_TREE,'');
$earth_no = $_POST['earth_no'];
$team_no = $_POST['team_no'];
$auto_reload = $_POST['auto_reload'];
$ggp_phase_name = get_option('ggp_phase_name');
$ggp_init_perteam = get_option('ggp_init_perteam');

if($earth_no == "")$earth_no = 0;
if($team_no == "")$team_no = 0;

$ggp_action = get_db_table_ggp_action($earth_no, $team_no);
$ggp_other_solar_panel = 0;
if($ggp_team[$earth_no*$ggp_init_perteam + $team_no]->other_valid_solar_panel == "1"){
  $ggp_other_solar_panel = - get_option('ggp_other_solar_panel');
}

?>
<link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">

<div class="wrap">
  <h2>ゲーム状況監視</h2>
  <form method="post" action="">
    <input type="hidden" name="earth_no" value=<?=$earth_no ?>>
    <input type="hidden" name="team_no" value=<?=$team_no ?>>
    <input type="checkbox" id="auto_reload" name="auto_reload" value="1" <?php checked(1, $auto_reload); ?>>自動更新(ON/OFF)
    <input id="reload-btn" type="submit" class="fas btn-reload" value="設定">
  </form>
  <div class="metabox-holder">
    <div class="postbox">
      <div class="inside">
        <div class="main-watch-left">
          <h3 class="hndle"><span>地球の状況</span></h3>
          <table class="ggp_team_table_watch">
          <tr><th style="width:10%;">地球</th><th style="width:30%;">CO2排出量</th><th style="width=30%;">CO2排出量上限</th><th style="width:15%; ">植林効果倍増</th><th style="width:15%;">売り上げUP</th></tr>
          <?php
          for($i = 0; $i < count($ggp_earth); $i++){
          ?>
          <tr>
          <td align="center"><?=$ggp_earth[$i]->earth_no ?></td>
          <td align="right"><?=$ggp_earth[$i]->co2 ?></td>
          <td align="right"><?=$ggp_earth[$i]->co2_quota ?></td>
          <td align="center"><?php if($ggp_earth[$i]->event_valid_tree == "1"){ echo '<font color="red">有効</font>'; }else{ echo "無効"; }?></td>
          <td align="center"><?php if($ggp_earth[$i]->event_valid_sales == "1"){ echo '<font color="red">有効</font>'; }else{ echo "無効"; }?></td>

          <?php } ?>
          </table>
        </div>
        <div class="main-watch-right">
          <h3 class="hndle"><span>ゲームパラメータ</span></h3>
          <table class="init_param">
          <tr><td>ゲーム初期設定</td><td>ターン数</td><td><?php echo get_option('ggp_quota_turn'); ?>ターン</td></tr>
          <tr><td></td><td>CO2排出上限</td><td><?php echo get_option('ggp_quota_co2'); ?>トン</td></tr>
          <tr><td></td><td>スタート時の所持金額</td><td><?php echo get_option('ggp_init_money'); ?>万円</td></tr>
          <tr><td></td><td>各チームのCO2排出上限</td><td><?php echo get_option('ggp_init_quota_co2_perteam'); ?>kg/チーム</td></tr>
          <tr><td></td><td>売上設定（５トン毎）</td><td><?php echo get_option('ggp_init_sales'); ?>万円</td></tr>
          <tr><td>イベント設定</td><td>植樹効果２倍</td><td><?php if(get_option('ggp_event_tree') == "1"){ echo '<font color="blue">有効</font>'; } else { echo '<font color="red">無効</font>'; } ?></td></tr>
          <tr><td></td><td>売り上げ倍増</td><td><?php if(get_option('ggp_event_sales') == "1"){ echo '<font color="blue">有効</font>'; } else { echo '<font color="red">無効</font>'; } ?></td></tr>

          </table>
        </div>
      </div>
    </div>
    <div class="postbox">
      <h3 class="hndle"><span>各チームの状況</span></h3>
      <div class="inside">
        <div class="main">
          <table class="ggp_team_table_watch">
          <tr><th style="width:5%;">地球</th><th style="width:5%;">チーム</th><th style="width=30%;">チーム名</th><th style="width:5%; ">ターン</th><th style="width:5%">経済活動</th><th style="width:5%">植樹回数</th><th style="width:10%;">CO2削減量/ターン</th><th style="width:10%; ">お金</th><th style="width:10%; ">CO2排出量</th><th style="width:10%; ">CO2排出上限</th><th style="width:5%;">行動詳細</th></tr>
          <?php
          for($i = 0; $i < count($ggp_team); $i++){
          ?>
          <tr><form method="post" action="">
          <input type="hidden" name="auto_reload" value="<?=$auto_reload ?>">
          <td align="center"><?=$ggp_team[$i]->earth_no ?></td>
          <input type="hidden" name="earth_no" value=<?=$ggp_team[$i]->earth_no ?>>
          <td align="center"><?=$ggp_team[$i]->team_no ?></td>
          <input type="hidden" name="team_no" value=<?=$ggp_team[$i]->team_no ?>>
          <td><?=$ggp_team[$i]->teamname ?></td>
          <td align="center"><?=$ggp_team[$i]->turn ?></td>
          <td align="center"><?=$ggp_action_rice[$i]->to_store/5 ?></td>
          <td align="center"><?=$ggp_action_tree[$i]->tree_num ?></td>
          <td align="center"><?=$ggp_team[$i]->other_valid_solar_panel == "1" ? $ggp_action_tree[$i]->reduction_co2 + $ggp_other_solar_panel : $ggp_action_tree[$i]->reduction_co2 ?></td>
          <td align="right"><?=$ggp_team[$i]->money ?></td>
          <td align="right"><?=$ggp_team[$i]->co2 ?></td>
          <td align="right"><?=$ggp_team[$i]->co2_quota ?></td>
          <td align="center" style="border: none; background-color: #F9F9F9;"><?php submit_button("表示",'small','update',false) ?></td></form></tr>
          <?php } ?>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="metabox-holder">
    <div class="postbox postbox-watch">
      <h3 class="hndle"><span>チームの詳細（地球：<?=$earth_no ?> チーム：<?=$team_no ?>)</span></h3>
      <div class="inside">
        <div class="main">
          <table class="action_table">
            <tr><th style="width:15%;">ターン</th><th style="width:40%;">行動</th><th style="width:15%;">お金</th><th style="width:15%;">二酸化炭素</th><th style="width:15%;">必要なターン数</th></tr>
            <?php for ($i = 0 ; $i < count($ggp_action); $i++){ ?>
            <tr>
            <td><?php echo ($ggp_action[$i]->require_turn == 0 ? "-" : $ggp_action[$i]->turn); ?></td>
            <td style="text-align:left !important;"><img class="icon-action" src="/wp-content/uploads/<?=$ggp_action[$i]->url ?>"><?php echo $ggp_phase_name[$ggp_action[$i]->phase].'('.$ggp_action[$i]->cardname.')'; ?></td>
            <td><?php echo $ggp_action[$i]->money; ?>万円</td>
            <td><?php echo $ggp_action[$i]->co2; ?>kg</td>
            <td><?php echo $ggp_action[$i]->require_turn; ?>ターン</td>
            </tr>
            <?php } ?>
          </table>
        </div>
      </div>
    <div>
  <div>
</div>

<script>
window.addEventListener('load', function () {
    setInterval( function() {
                  reload_page();
                  }, 10000);
}
);

function reload_page(){
   const start = document.getElementById('reload-btn');
   const auto_reload = document.getElementById('auto_reload').checked;
   if(auto_reload == true) start.click();
}

</script>

<?php
}

/****************************************************************
* tag: admin_rollback
* 【管理画面】ロールバック
****************************************************************/
function register_ggp_rollback(){
}

//ロールバックの実行画面を出力
function add_ggp_rollback(){
//define('TABLE_NAME_GGP_TEAM',  $wpdb->prefix . 'ggp_team');
//define('TABLE_NAME_GGP_EARTH',  $wpdb->prefix . 'ggp_earth');
$ggp_init_perteam = get_option('ggp_init_perteam');
$mode = $_POST['mode'];
$earth_no = $_POST['earth_no'];
$team_no = $_POST['team_no'];
$ggp_phase_name = get_option('ggp_phase_name');
$ggp_team = get_db_table_records(TABLE_NAME_GGP_TEAM,'');
$rollback_turn = $ggp_team[$earth_no*$ggp_init_perteam + $team_no]->turn - 1;
$team_rollback_transaction = get_db_table_ggp_action_turn($earth_no, $team_no, $rollback_turn);
$error_msg = "";
$ggp_other_solar_panel = - get_option('ggp_other_solar_panel');

if($mode == "rollback"){
  foreach($team_rollback_transaction as $iterator){
    if($iterator->phase == "event"){
      $error_msg = "イベントのロールバックはできません";
      break;
    }
  }
}



// ロールバックが実行された場合の処理
// ロールバック対象にeventが含まれる場合はロールバック不可
if($mode == "rollback" && $error_msg == ""){

  delete_table_ggp_action_rollback($earth_no, $team_no, $rollback_turn);
  foreach($team_rollback_transaction as $iterator){
    $ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);
    $ggp_team = get_db_table_records(TABLE_NAME_GGP_TEAM,'');
    update_table_ggp_earth($earth_no, $ggp_earth[0]->co2-$iterator->co2);
    update_table_ggp_team_transaction($earth_no, $team_no, -$iterator->require_turn, $iterator->money, -$iterator->co2);
    switch ( $iterator->phase ){
      case "grow" :
      case "to_factory" :
      case "make" :
      case "to_store" :
        // お米・おせんべいの生産量テーブルを更新する
        update_table_ggp_action_rice($earth_no, $team_no, $iterator->phase, -$iterator->quantity);
        break;
      case "sales" :
        // 特殊処理は特段不要
        break;
      case "reduction" :
        switch($iterator->keyword){
          case "tree" :
            update_table_ggp_action_tree($earth_no, $team_no, -$iterator->quantity);
          break;
          case "buy" :
            $ggp_quota_co2 = $ggp_earth[0]->co2_quota;
            update_table_ggp_earth_quota($earth_no, $ggp_quota_co2 - $iterator->quantity);
            update_table_ggp_team_co2_quota($earth_no, $team_no, $ggp_team[$team_no]->co2_quota - $iterator->quantity);
          break;
          case "ev_collaboration" :
            rollback_table_ggp_cardinfo_instance_ev_collaboration($earth_no, $team_no);
            update_table_ggp_cardinfo_instance_is_valid($earth_no, $team_no, "ev_collaboration", 1);
          break;
          case "clean_energy" :
            $ggp_event_not_clean_energy = get_option("ggp_event_not_clean_energy");
            update_table_ggp_cardinfo_instance_is_valid($earth_no, $team_no, "clean_energy", 1);
            update_table_ggp_team_other_clean_energy($earth_no, $team_no, 0);
            foreach($ggp_event_not_clean_energy as $line){
              if($line == "")continue;
              update_table_ggp_cardinfo_instance_is_valid($earth_no, $team_no, $line, 1);
            }
          break;
          case "robot" :
            update_table_ggp_cardinfo_instance_is_valid($earth_no, $team_no, "robot", 1);
            update_table_ggp_cardinfo_instance_is_visible($earth_no, $team_no, "robot_grow_rice", NULL);
          break;
          case "direct_store" :
            update_table_ggp_cardinfo_instance_is_valid($earth_no, $team_no, "direct_store", 1);
            update_table_ggp_event_direct_store($earth_no, $team_no, 0);
          break;
          case "solar_panel" :
            update_table_ggp_cardinfo_instance_is_valid($earth_no, $team_no, "solar_panel", 1);
            update_table_ggp_team_other_solar_panel($earth_no, $team_no, 0);
          break;
          case "eco_package" :
            update_table_ggp_cardinfo_instance_is_valid($earth_no, $team_no, "eco_package", 1);
            rollback_table_ggp_cardinfo_instance_eco_package($earth_no, $team_no);
          break;
          case "cm" :
            update_table_ggp_cardinfo_instance_is_valid($earth_no, $team_no, "cm", 1);
            update_table_ggp_team_other_cm($earth_no, $team_no, 0);
          break;
          case "pickup_litter" :
            update_table_ggp_cardinfo_instance_is_valid($earth_no, $team_no, "pickup_litter", 1);
            update_table_ggp_cardinfo_instance_is_visible($earth_no, $team_no, "buy_rice", 0);
          break;
          default :
            // co2削減系は、reduction、かつkeywordが空欄なのでスルーする。
            // それ以外の別のkeywordが入っている場合は想定していない入力なのでエラーを吐く
            if($iterator->keyword != ""){
              $error_msg .= "不正なロールバックです。phase:" . $iterator->phase . " keyword:" .  $iterator->keyword;
              error_log("不正なロールバックです。phase:" . $iterator->phase . " keyword:" .  $iterator->keyword);
            }
          break;
        } // switch($iterator->keyword)
        break;
      default:
          $error_msg .= "不正なロールバックです。phase:" . $iterator->phase . " keyword:" .  $iterator->keyword;
          error_log("不正なロールバックです。phase:" . $iterator->phase . " keyword:" .  $iterator->keyword);
      break;
    } // switch($iterator->phase)
  }   // foreach($team_rollback_transaction as $iterator)
}     // if($mode=="rollback")

$ggp_team = get_db_table_records(TABLE_NAME_GGP_TEAM,'');
$ggp_action_rice = get_db_table_records(TABLE_NAME_GGP_ACTION_RICE,'');
$ggp_action_tree = get_db_table_records(TABLE_NAME_GGP_ACTION_TREE,'');

if($earth_no == "")$earth_no = 0;
if($team_no == "")$team_no = 0;
$ggp_action = get_db_table_ggp_action($earth_no, $team_no);

?>
<link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">

<div class="wrap">
  <h2>ロールバック</h2>
  <?php if($error_msg != ""){ ?>
    <div class="error-msg">
      <?=$error_msg ?>
    </div>
  <?php } ?>

  <div class="metabox-holder">
    <div class="postbox">
      <h3 class="hndle"><span>各チームの状況／ロールバック</span></h3>
      <div class="inside">
        <div class="main">
          <table class="ggp_team_table_watch">
          <tr><th style="width:5%;">地球</th><th style="width:5%;">チーム</th><th style="width=22%;">チーム名</th><th style="width:5%; ">ターン</th><th style="width:5%">経済活動</th><th style="width:5%">植樹回数</th><th style="width:10%;">CO2削減量/ターン</th><th style="width:10%; ">お金</th><th style="width:10%; ">CO2排出量</th><th style="width:10%; ">CO2排出上限</th><th style="width:5%;">行動詳細</th><th style="width:8%;">ロールバック</th></tr>
          <?php
          for($i = 0; $i < count($ggp_team); $i++){
          ?>
          <tr>
          <td align="center"><?=$ggp_team[$i]->earth_no ?></td>
          <td align="center"><?=$ggp_team[$i]->team_no ?></td>
          <td><?=$ggp_team[$i]->teamname ?></td>
          <td align="center"><?=$ggp_team[$i]->turn ?></td>
          <td align="center"><?=$ggp_action_rice[$i]->to_store/5 ?></td>
          <td align="center"><?=$ggp_action_tree[$i]->tree_num ?></td>
          <td align="center"><?=$ggp_team[$i]->other_valid_solar_panel == "1" ? $ggp_action_tree[$i]->reduction_co2 + $ggp_other_solar_panel : $ggp_action_tree[$i]->reduction_co2 ?></td>
          <td align="right"><?=$ggp_team[$i]->money ?></td>
          <td align="right"><?=$ggp_team[$i]->co2 ?></td>
          <td align="right"><?=$ggp_team[$i]->co2_quota ?></td>
          <form method="post" action="">
          <input type="hidden" name="earth_no" value=<?=$ggp_team[$i]->earth_no ?>>
          <input type="hidden" name="team_no" value=<?=$ggp_team[$i]->team_no ?>>
          <td align="center" style="border: none; background-color: #F9F9F9;"><?php submit_button("表示",'small','update',false) ?></td>
          </form>
          <form method="post" action="">
          <input type="hidden" name="earth_no" value=<?=$ggp_team[$i]->earth_no ?>>
          <input type="hidden" name="team_no" value=<?=$ggp_team[$i]->team_no ?>>
          <input type="hidden" name="mode" value="rollback">
          <td align="center" style="border: none; background-color: #F9F9F9;"><?php if($ggp_team[$i]->earth_no == $earth_no && $ggp_team[$i]->team_no == $team_no){ submit_button("取り消す",'primary','update',false); }else{ ?><input type="submit" name="update" id="update" class="button button-primary" value="取り消す" disabled> <?php } ?></td>
          </form>
          </tr>
          <?php } ?>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="metabox-holder">
    <div class="postbox postbox-watch">
      <h3 class="hndle"><span>チームの詳細（<?=$earth_no ?>-<?=$team_no ?> : <?=$ggp_team[$earth_no*$ggp_init_perteam + $team_no]->teamname ?>)</span></h3>
      <div class="inside">
        <div class="main">
          <table class="action_table">
            <tr><th style="width:15%;">ターン</th><th style="width:40%;">行動</th><th style="width:15%;">お金</th><th style="width:15%;">二酸化炭素</th><th style="width:15%;">必要なターン数</th></tr>
            <?php for ($i = 0 ; $i < count($ggp_action); $i++){ ?>
            <tr>
            <td><?php echo ($ggp_action[$i]->require_turn == 0 ? "-" : $ggp_action[$i]->turn); ?></td>
            <td style="text-align:left !important;"><img class="icon-action" src="/wp-content/uploads/<?=$ggp_action[$i]->url ?>"><?php echo $ggp_phase_name[$ggp_action[$i]->phase].'('.$ggp_action[$i]->cardname.')'; ?></td>
            <td><?php echo $ggp_action[$i]->money; ?>万円</td>
            <td><?php echo $ggp_action[$i]->co2; ?>kg</td>
            <td><?php echo $ggp_action[$i]->require_turn; ?>ターン</td>
            </tr>
            <?php } ?>
            </table>
        </div>
      </div>
    <div>
  <div>
</div>

<?php
}
