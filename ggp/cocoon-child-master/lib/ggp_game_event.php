<?php
/** ggp_game_event.php
 * @author arakawa asuka
 * @date 2022/01/03
 */


/****************************************************************
 * tag: boardgame_event
 * 【ユーザ画面】イベントのルーレット画面
 ****************************************************************/
if( !function_exists( ' show_ggp_event' )):
  function show_ggp_event(){
    $earth_no = $_POST['earth_no'];
    $team_no = $_POST['team_no'];
    $token = $_POST['token'];
    $is_general = $_POST['is_general'];
    $select_no = $_POST['select_no'];
    $token_new = uniqid('', true);
    $show_all = $_POST['show_all'];
    $ggp_event_mode = (int)$_POST['ggp_event_mode'];
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

    if($is_general == NULL)$is_general='disabled';

    $ggp_event_list =  Array(
        Array('key'=>'disaster','text'=>'台風で田んぼと倉庫がやられてしまいました。振り出しに戻る。','value'=>''),
        //                          Array('key'=>'donation','text'=>'環境を大事にする団体へ500万円を寄付する。', 'value'=>500),
        Array('key'=>'subsidy','text'=>'電気自動車を使った会社に補助金が出ることになった。電気自動車を使った回数&times;'. $ggp_event_subsidy . '万円をもらう。','value'=>$ggp_event_subsidy),
        Array('key'=>'tax','text'=>'ガソリン車を使った会社に税金がかかることになった。ガソリン車を使った回数&times;' . $ggp_event_tax . '万円を支払う。','value'=>$ggp_event_tax),
        Array('key'=>'emissions_trading','text'=>'二酸化炭素排出権を買う。'. $ggp_event_emission_trading . '万円を支払って排出量上限を200kg増やす。','value'=>200),
        Array('key'=>'clean_energy','text'=>'クリーンな電気でおせんべいを作ることにした。「おせんべいを作る」で選べるカードが少なくなる。'),
        Array('key'=>'reduction_quota_co2', 'text'=>'二酸化炭素を少なくする約束をした。全体の排出量上限が-' . $ggp_event_reduction_quota_co2 .' kg少なくなる。', 'value'=>$ggp_event_reduction_quota_co2),
        Array('key'=>'direct_store', 'text'=>'工場に直売所を作った。おせんべいを作ると' . $ggp_event_direct_store .' 万円売り上げが増える。', 'value'=>$ggp_event_direct_store),
        Array('key'=>'popularity', 'text'=>'作ったおせんべいが人気急上昇！売り上げが' . $ggp_event_popularity*100 .' %アップする。', 'value'=>$ggp_event_popularity),
        Array('key'=>'scandal', 'text'=>'作ったおせんべいに虫が入っていた！売り上げが' . -$ggp_event_scandal*100 .' %ダウンする。', 'value'=>$ggp_event_scandal),
        Array('key'=>'protect_environment_action', 'text'=>'環境によい取り組みをしている会社を支援するお店がでた。環境によい取り組み&times;10%売り上げアップ。','value'=>'')
        );

    // イベントのカードをランダムに選ぶ
    $random_no = Array();
    while(count($random_no) < count($ggp_event_list)){
      /** 一時的な乱数を作成 */
      $tmp = mt_rand(0, count($ggp_event_list) - 1);
      if( ! in_array( $tmp, $random_no, true) ){
        array_push( $random_no, $tmp );
      }
    }

    if(allow_insert_table_ggp_action($token) ){
      switch( $ggp_event_list[$ggp_event_mode]['key'] ){
      case "disaster" :
        event_disaster($earth_no, $team_no);
        insert_db_table_ggp_message($earth_no,'【イベント】「' . $ggp_team[$team_no]->teamname . '」チームが台風で田んぼと倉庫がやられてしまいました。');
        insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'台風で田んぼと倉庫がやられてしまいました','blackbox_question_open.png','','',0,0);
        break;
      case "donation" :
        update_table_ggp_team_transaction($earth_no, $team_no, 0, 500, 0);
        insert_db_table_ggp_message($earth_no,'【イベント】「' . $ggp_team[$team_no]->teamname . '」チームが環境を大事にする団体へ寄付をしました。');
        insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'環境を大事にする団体へ寄付をしました','blackbox_question_open.png','',-500,0,0);
        break;
      case "subsidy" :
        $count =  event_count_electric_car($earth_no, $team_no);
        $count = (int)$count[0]->count;
        update_table_ggp_team_transaction($earth_no, $team_no, 0, - ($count * $ggp_event_subsidy), 0);
        insert_db_table_ggp_message($earth_no,'【イベント】「' . $ggp_team[$team_no]->teamname . '」チームが電気自動車を使った分だけ補助金を受け取りました。');
        insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'補助金を受け取りました（トラック（電気自動車）を使った回数&times;' .$ggp_event_subsidy . '万円）','blackbox_question_open.png','',$count * $ggp_event_subsidy,0,0);
        break;
      case "tax" :
        $count =  event_count_car_truck($earth_no, $team_no);
        $count = (int)$count[0]->count;
        update_table_ggp_team_transaction($earth_no, $team_no, 0, $count * $ggp_event_tax, 0);
        insert_db_table_ggp_message($earth_no,'【イベント】「' . $ggp_team[$team_no]->teamname . '」チームがトラックを使用した分だけ税金を支払いました。');
        insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'税金を支払いました（トラック（ガソリン）を使った回数&times;' . $ggp_event_tax.'万円）','blackbox_question_open.png','',-$count * $ggp_event_tax,0,0);
        break;
      case "emissions_trading" :
        update_table_ggp_team_transaction($earth_no, $team_no, 0, $ggp_event_emission_trading, 0);
        insert_db_table_ggp_message($earth_no,'【イベント】「' . $ggp_team[$team_no]->teamname . '」チームが環境対策をしました（二酸化炭素排出権を買う）。');
        insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'環境対策をしました（二酸化炭素排出を買う）','blackbox_question_open.png','',-$ggp_event_emission_trading,0,0);
        update_table_ggp_earth_quota($earth_no, $ggp_quota_co2 + 200);
        update_table_ggp_team_co2_quota($earth_no, $team_no, $ggp_team[$team_no]->co2_quota + 200);
        break;
      case "clean_energy" :
        update_table_ggp_event_clean_energy($earth_no, $team_no, 1);
        insert_db_table_ggp_message($earth_no,'【イベント】「' . $ggp_team[$team_no]->teamname . '」チームがクリーンな電気でおせんべいを作ることにしました。');
        insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'クリーンな電気でおせんべいを作ることにした','blackbox_question_open.png','',0,0,0);
        break;
      case "reduction_quota_co2" :
        update_table_ggp_earth_quota($earth_no, $ggp_quota_co2 - $ggp_event_reduction_quota_co2);
        insert_db_table_ggp_message($earth_no,'【イベント】「' . $ggp_team[$team_no]->teamname . '」チームが二酸化炭素を少なくする約束をしました。');
        insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'二酸化炭素を少なくする約束をした','blackbox_question_open.png','',0,0,0);
        break;
      case "direct_store" :
        update_table_ggp_event_direct_store($earth_no, $team_no, 1);
        insert_db_table_ggp_message($earth_no,'【イベント】「' . $ggp_team[$team_no]->teamname . '」チームが直売所を作りました。');
        insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'直売所を作った。','blackbox_question_open.png','',0,0,0);
        break;
      case "popularity" :
        update_table_ggp_event_popularity($earth_no, $team_no, 1);
        insert_db_table_ggp_message($earth_no,'【イベント】「' . $ggp_team[$team_no]->teamname . '」チームのおせんべいの人気があがりました。');
        insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'おせんべいの人気があがった。売り上げが'.$ggp_event_popularity*100 . '%アップする。','blackbox_question_open.png','',0,0,0);
       break;
      case "scandal" :
        update_table_ggp_event_scandal($earth_no, $team_no, 1);
        insert_db_table_ggp_message($earth_no,'【イベント】「' . $ggp_team[$team_no]->teamname . '」チームのおせんべいに虫が入っていて人気が下がりました。');
        insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'おせんべいに虫が入っていた。売り上げが'.- $ggp_event_scandal*100 . '%ダウンする。','blackbox_question_open.png','',0,0,0);
      break;
      case "protect_environment_action" :
        update_table_ggp_event_protect($earth_no, $team_no, 1);
        insert_db_table_ggp_message($earth_no,'【イベント】「' . $ggp_team[$team_no]->teamname . '」チームが環境によい取り組みをした企業への支援を受けました。');
        insert_table_ggp_action($token, $earth_no, $team_no, 'event' ,'環境によい取り組みを支援するお店が現れた。環境によい取り組み&times;10%売り上げがアップする。','blackbox_question_open.png','',0,0,0);
      break;
      default :
        //何もしない
        break;
      }
    }


    ?>
      <div class="header-div">
      <div class="return">
      <form method="post" action="">
      <input type="hidden" name="is_general" value="<?php echo $is_general; ?>">
      <input type="hidden" name="earth_no" value="<?php echo $earth_no; ?>">
      <input type="hidden" name="token" value="<?=$token_new ?>">
      <input type="hidden" id="team_no" name="team_no" value="<?=$team_no ?>">
      <input type="hidden" id="is_general" name="is_general" value="<?=$is_general ?>">
      <input type="hidden" name="mode" value="select_boardgame">
      <input type="submit" class="btn-return" value="戻る">
      </form>
      </div>
      </div>

      <div align="center">
      <table class="ggp_select_team">

      <?php 
      for( $i = 0; $i < count($ggp_event_list); $i++){
        ?>
          <form method="post" action="">
          <tr><td>
          <input type="hidden" id="earth_no" name="earth_no" value="<?=$earth_no ?>">
          <input type="hidden" id="team_no" name="team_no" value="<?= $team_no ?>">
          <input type="hidden" name="token" value="<?=$token_new ?>">
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
            <input class="btn-select-event" type="submit" value="No.<?=$i+1 ?> ">
            <input type="hidden" id="ggp_event_mode" name="ggp_event_mode" value="<?=$random_no[$i] ?>">
          <?php }else if ($select_no != NULL && $i == $select_no){
            // カードが選択された場合、かつ選択された番号の場合
          ?>
            <input class="btn-select-event-selected" type="submit" value="<?=$ggp_event_list[$ggp_event_mode]['text'] ?>" disabled>
          <?php }else { ?>
            <input class="btn-select-event" type="submit" value="No.<?=$i+1 ?>" disabled>
          <?php } ?>
          </td></tr>
          </form>
      <?php } ?>

      <form method="post" action="">
      <tr><td>
      <input type="hidden" id="earth_no" name="earth_no" value="<?=$earth_no ?>">
      <input type="hidden" id="team_no" name="team_no" value="<?=$team_no ?>">
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
endif;

