<?php
/** ggp_game_event.php
 * @author arakawa asuka
 * @date 2022/01/03
 */

if( !function_exists( 'get_ggp_event_list')):
function get_ggp_event_list(){
    $ggp_event_subsidy = get_option('ggp_event_subsidy');
    $ggp_event_tax = get_option('ggp_event_tax');
    $ggp_event_emission_trading = get_option('ggp_event_emission_trading');
    $ggp_event_reduction_quota_co2 = get_option('ggp_event_reduction_quota_co2');
    $ggp_event_direct_store = get_option('ggp_event_direct_store');
    $ggp_event_popularity = get_option('ggp_event_popularity');
    $ggp_event_scandal = get_option('ggp_event_scandal');
    $ggp_event_list =  Array(
        Array('key'=>'disaster','subject'=>'(1)台風で田んぼと工場がやられる','text'=>'台風で田んぼと倉庫がやられてしまいました。作ったお米やおせんべいの材料がゼロになる。','value'=>''),
        Array('key'=>'subsidy','subject'=>'(2)電気自動車を使った会社に補助金','text'=>'電気自動車を使った会社に補助金が出ることになった。電気自動車を使った回数&times;'. $ggp_event_subsidy . '万円をもらう。','value'=>$ggp_event_subsidy),
        Array('key'=>'tax','subject'=>'(3)ガソリン車を使った会社に税金','text'=>'ガソリン車を使った会社に税金がかかることになった。ガソリン車を使った回数&times;' . $ggp_event_tax . '万円を支払う。','value'=>$ggp_event_tax),
        Array('key'=>'clean_energy','subject'=>'(4)クリーン電力を使用','text'=>'クリーンな電気でおせんべいを作ることにした。「おせんべいを作る」で選べるカードが少なくなる。'),
        Array('key'=>'reduction_quota_co2', 'subject'=>'(5)Co2排出量上限の引き下げ','text'=>'二酸化炭素を少なくする約束をした。全体の排出量上限が-' . $ggp_event_reduction_quota_co2 .' kg少なくなる。', 'value'=>$ggp_event_reduction_quota_co2),
        Array('key'=>'direct_store', 'subject'=>'(6)工場に直売所を設置','text'=>'工場に直売所を作った。おせんべいを作ると' . $ggp_event_direct_store .' 万円売り上げが増える。', 'value'=>$ggp_event_direct_store),
        Array('key'=>'popularity', 'subject'=>'(7)人気が出て売り上げUp','text'=>'作ったおせんべいが人気急上昇！売り上げが' . $ggp_event_popularity*100 .' %アップする。', 'value'=>$ggp_event_popularity),
        Array('key'=>'scandal', 'subject'=>'(8)虫が入っていて売り上げDown','text'=>'作ったおせんべいに虫が入っていた！売り上げが' . -$ggp_event_scandal*100 .' %ダウンする。', 'value'=>$ggp_event_scandal),
        Array('key'=>'protect_environment_action', 'subject'=>'(9)環境に良い取組みに補助金','text'=>'環境によい取り組みをしている会社を支援する人が現れた。環境によい取り組み&times;10%売り上げアップ。','value'=>''),
        Array('key'=>'restriction_gascars', 'subject'=>'(10)ガソリン車禁止','text'=>'環境に悪いガソリン車が使えなくなった。「お米を工場に運ぶ」「おせんべいを運んで売る」で選べるカードが少なくなる。','value'=>'')
 //       Array('key'=>'emissions_trading','subject'=>'(11)CO2排出権取引','text'=>'二酸化炭素排出権を買う。'. $ggp_event_emission_trading . '万円を支払って排出量上限を200kg増やす。','value'=>200),
        );

  return $ggp_event_list;
}
endif;



/****************************************************************
 * tag: boardgame_event
 * 【ユーザ画面】イベントのルーレット画面
 ****************************************************************/
if( !function_exists( ' show_ggp_event' )):
  function show_ggp_event(){
    $token = $_POST['token'];
    $is_general = $_POST['is_general'];
    $select_no = $_POST['select_no'];
    $token_new = uniqid('', true);
    $earth_no = $_POST['earth_no'];
    $show_all = $_POST['show_all'];
    $ggp_init_perteam = get_option('ggp_init_perteam');
    $ggp_event_preselect = get_option('ggp_event_preselect');
    $ggp_event_mode = (int)$_POST['ggp_event_mode'];
    $ggp_event_list = get_ggp_event_list();
    $ggp_team = get_db_table_records_ggp(TABLE_NAME_GGP_TEAM,"earth_no",$earth_no);
    $ggp_earth = get_db_table_records_ggp(TABLE_NAME_GGP_EARTH,"earth_no",$earth_no);
    $ggp_event_subsidy = get_option('ggp_event_subsidy');
    $ggp_event_tax = get_option('ggp_event_tax');
    $ggp_event_emission_trading = get_option('ggp_event_emission_trading');
    $ggp_event_reduction_quota_co2 = get_option('ggp_event_reduction_quota_co2');
    $ggp_event_direct_store = get_option('ggp_event_direct_store');
    $ggp_event_popularity = get_option('ggp_event_popularity');
    $ggp_event_scandal = get_option('ggp_event_scandal');
    $ggp_event_protect_environment_action = get_option('ggp_event_protect_environment_action');
    $ggp_quota_co2 = $ggp_earth[0]->co2_quota;
    $error_msg = "";
    $ggp_event_not_clean_energy = get_option("ggp_event_not_clean_energy");
    $ggp_event_restriction_gascars = get_option("ggp_event_restriction_gascars");

    if($is_general == NULL)$is_general='disabled';

    // イベントのカードをランダムに選ぶ
    $random_no = Array();
    while(count($random_no) < count($ggp_event_list)){
      /** 一時的な乱数を作成 */
      $tmp = mt_rand(0, count($ggp_event_list) - 1);
      if( ! in_array( $tmp, $random_no, true) ){
        array_push( $random_no, $tmp );
      }
    }

    if($ggp_earth[0]->event_card_count >= 3){
      $error_msg .= "イベント回数が規定の数より多いです。イベント回数：" . $ggp_earth[0]->event_card_count + 1 . "回目";
      $select_no = "";
    }
if($select_no != "" && allow_insert_table_ggp_action($token) ){
      $preselect = $ggp_event_preselect[$ggp_earth[0]->event_card_count];

      if($preselect != ""){
        $ggp_event_mode = $preselect;
      }else{
        set_parameter_ggp_event_preselect($ggp_event_mode, $ggp_earth[0]->event_card_count);
      }

      insert_table_ggp_message($earth_no,'【イベント】' . $ggp_event_list[$ggp_event_mode]['text'], 1);
      switch( $ggp_event_list[$ggp_event_mode]['key'] ){
      case "disaster" :
      //台風で田んぼと工場がやられる
          for($team_no = 0; $team_no < $ggp_init_perteam; $team_no++){
            event_disaster($earth_no, $team_no);
            insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,$ggp_team[$team_no]->turn - 1, $ggp_event_list[$ggp_event_mode]['text'],'blackbox_question_open.png','','',0,0,0);
            update_table_ggp_earth_event_card_count($earth_no, $ggp_earth[0]->event_card_count + 1 );
          }
        break;
      case "subsidy" :
      //電気自動車を使った会社に補助金
          for($team_no = 0; $team_no < $ggp_init_perteam; $team_no++){
            $count =  event_count_electric_car($earth_no, $team_no);
            $count = (int)$count[0]->count;
            update_table_ggp_team_transaction($earth_no, $team_no, 0, - ($count * $ggp_event_subsidy), 0);
            insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'', $ggp_event_list[$ggp_event_mode]['text'] .$ggp_event_subsidy . '万円）','blackbox_question_open.png','',$count * $ggp_event_subsidy,0,0,0);
            update_table_ggp_earth_event_card_count($earth_no, $ggp_earth[0]->event_card_count + 1 );
          }
        break;
      case "tax" :
      //ガソリン車を使った会社に税金
          for($team_no = 0; $team_no < $ggp_init_perteam; $team_no++){
            $count =  event_count_car_truck($earth_no, $team_no);
            $count = (int)$count[0]->count;
            echo("team_no = " . $team_no . " count = " . $count . " tax = " . $count * $ggp_event_tax);
            update_table_ggp_team_transaction($earth_no, $team_no, 0, $count * $ggp_event_tax, 0);
            insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'',$ggp_event_list[$ggp_event_mode]['text'],'blackbox_question_open.png','',-$count * $ggp_event_tax,0,0,0);
            update_table_ggp_earth_event_card_count($earth_no, $ggp_earth[0]->event_card_count + 1 );
          }
        break;
      case "emissions_trading" :
      //Co2排出権取引
          for($team_no = 0; $team_no < $ggp_init_perteam; $team_no++){
            update_table_ggp_team_transaction($earth_no, $team_no, 0, $ggp_event_emission_trading, 0);
            insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'',$ggp_event_list[$ggp_event_mode]['text'],'blackbox_question_open.png','',-$ggp_event_emission_trading,0,0,0);
            update_table_ggp_earth_quota($earth_no, $ggp_quota_co2 + 200);
            update_table_ggp_team_co2_quota($earth_no, $team_no, $ggp_team[$team_no]->co2_quota + 200);
            update_table_ggp_earth_event_card_count($earth_no, $ggp_earth[0]->event_card_count + 1 );
          }
        break;
      case "clean_energy" :
      //クリーン電力を使用（工場）
          for($team_no = 0; $team_no < $ggp_init_perteam; $team_no++){
            update_table_ggp_event_clean_energy($earth_no, $team_no, 1);
            insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'',$ggp_event_list[$ggp_event_mode]['text'],'blackbox_question_open.png','',0,0,0,0);
            update_table_ggp_earth_event_card_count($earth_no, $ggp_earth[0]->event_card_count + 1 );
            foreach($ggp_event_not_clean_energy as $line){
              update_table_ggp_cardinfo_instance_is_valid($earth_no, $team_no, $line, NULL);
            }
          }
        break;
      case "reduction_quota_co2" :
      //Co2排出量上限の引き下げ
          for($team_no = 0; $team_no < $ggp_init_perteam; $team_no++){
            update_table_ggp_earth_quota($earth_no, $ggp_quota_co2 - $ggp_event_reduction_quota_co2);
            insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'',$ggp_event_list[$ggp_event_mode]['text'],'blackbox_question_open.png','',0,0,0,0);
            update_table_ggp_earth_event_card_count($earth_no, $ggp_earth[0]->event_card_count + 1 );
          }
        break;
      case "direct_store" :
      //工場に直売所を設置
          for($team_no = 0; $team_no < $ggp_init_perteam; $team_no++){
            update_table_ggp_event_direct_store($earth_no, $team_no, 1);
            insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'',$ggp_event_list[$ggp_event_mode]['text'],'blackbox_question_open.png','',0,0,0,0);
            update_table_ggp_earth_event_card_count($earth_no, $ggp_earth[0]->event_card_count + 1 );
            update_table_ggp_cardinfo_instance_is_valid($earth_no, $team_no, "direct_store",NULL);
          }
        break;
      case "popularity" :
      //人気が出て売り上げUP
          for($team_no = 0; $team_no < $ggp_init_perteam; $team_no++){
            update_table_ggp_event_popularity($earth_no, $team_no, 1);
            insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'',$ggp_event_list[$ggp_event_mode]['text'],'blackbox_question_open.png','',0,0,0,0);
            update_table_ggp_earth_event_card_count($earth_no, $ggp_earth[0]->event_card_count + 1 );
          }
       break;
      case "scandal" :
      //虫が入っていて売り上げDown
          for($team_no = 0; $team_no < $ggp_init_perteam; $team_no++){
            update_table_ggp_event_scandal($earth_no, $team_no, 1);
            insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'',$ggp_event_list[$ggp_event_mode]['text'],'blackbox_question_open.png','',0,0,0,0);
            update_table_ggp_earth_event_card_count($earth_no, $ggp_earth[0]->event_card_count + 1 );
          }
      break;
      case "protect_environment_action" :
      //環境に良い取り組みに補助金
          for($team_no = 0; $team_no < $ggp_init_perteam; $team_no++){
            update_table_ggp_event_protect($earth_no, $team_no, 1);
            insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'',$ggp_event_list[$ggp_event_mode]['text'],'blackbox_question_open.png','',0,0,0,0);
            update_table_ggp_earth_event_card_count($earth_no, $ggp_earth[0]->event_card_count + 1 );
          }
      break;
      case "restriction_gascars" :
      //ガソリン車禁止
          for($team_no = 0; $team_no < $ggp_init_perteam; $team_no++){
            insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'',$ggp_event_list[$ggp_event_mode]['text'],'blackbox_question_open.png','',0,0,0,0);
            update_table_ggp_event_restriction_gascars($earth_no, $team_no, 1);
            update_table_ggp_earth_event_card_count($earth_no, $ggp_earth[0]->event_card_count + 1 );
            foreach($ggp_event_restriction_gascars as $line){
              update_table_ggp_cardinfo_instance_is_valid($earth_no, $team_no, $line, NULL);
            }
          }
      break;
      default :
        $error_msg .="不正なキーです。key:" . $ggp_event_list[$ggp_event_mode]['key'];
        //何もしない
        break;
      }
    }//if $select_no != "" & allow_insert_table_ggp_action($token)


    ?>
      <div class="header-div">
      <div class="return">
      <form method="post" action="">
      <input type="hidden" name="earth_no" value="<?=$earth_no; ?>">
      <input type="hidden" id="is_general" name="is_general" value="<?=$is_general ?>">
      <input type="hidden" name="mode" value="select_team">
      <input type="submit" class="btn-return" value="戻る">
      </form>
      </div>
      </div>
      <h1>地球 No.<?=chr(ord('A') + $earth_no) ?> イベントルーレット</h1>
      <?php if($error_msg != ""){ ?>
      <div class="error-msg">
        <?=$error_msg ?>
      </div>
      <?php } ?>

      <div align="center">
      <table class="ggp_select_team">

      <?php 
      for( $i = 0; $i < count($ggp_event_list); $i++){
        ?>
          <form method="post" action="">
          <tr><td>
          <input type="hidden" name="token" value="<?=$token_new ?>">
          <input type="hidden" name="earth_no" value="<?=$earth_no; ?>">
          <input type="hidden" id="is_general" name="is_general" value="<?=$is_general ?>">
          <input type="hidden" id="mode" name="mode" value="select_event">
          <input type="hidden" id="select_no" name="select_no" value="<?=$i?>">
          <?php if($show_all == "show_all"){
            // 全ての選択肢を表示する
          ?>
            <input class="btn-select-event-selected" type="submit" value="<?=$ggp_event_list[$i]['text'] ?>" disabled>
            <input type="hidden" id="ggp_event_mode" name="ggp_event_mode" value="<?=$i ?>">
          <?php }else if($select_no == NULL){
            // 選択されていない状態（初期状態）
          ?>
            <input class="btn-select-event" type="submit" value="　No.<?=$i+1 ?>　">
            <input type="hidden" id="ggp_event_mode" name="ggp_event_mode" value="<?=$random_no[$i] ?>">
          <?php }else if ($select_no != NULL && $i == $select_no){
            // カードが選択された場合、かつ選択された番号の場合
          ?>
            <input class="btn-select-event-selected" type="submit" value="<?=$ggp_event_list[$ggp_event_mode]['text'] ?>" disabled>
          <?php }else { ?>
            <input class="btn-select-event" type="submit" value="　No.<?=$i+1 ?>　" disabled>
          <?php } ?>
          </td></tr>
          </form>
      <?php } 
      if($show_all == "show_all"){
      ?>
      <form method="post" action="">
      <tr><td>
      <input type="hidden" id="earth_no" name="earth_no" value="<?=$earth_no ?>">
      <input type="hidden" id="is_general" name="is_general" value="<?=$is_general ?>">
      <input type="hidden" id="mode" name="mode" value="select_event">
      <input type="submit" value="カードをシャッフルする">
      </td></tr>
      </form>
      <?php }else{ ?>

      <form method="post" action="">
      <tr><td>
      <input type="hidden" id="earth_no" name="earth_no" value="<?=$earth_no ?>">
      <input type="hidden" id="is_general" name="is_general" value="<?=$is_general ?>">
      <input type="hidden" id="mode" name="mode" value="select_event">
      <input type="hidden" id="show_all" name="show_all" value="show_all">
      <input type="submit" value="すべてのカードを表示する">
      </td></tr>
      </form>
      </table>
      </div>

      <?php
      }
  }
endif;

