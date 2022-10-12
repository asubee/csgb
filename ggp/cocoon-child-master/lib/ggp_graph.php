<?php
/** ggp_setup.php
* @author arakawa asuka
* @date 2021/11/20
*/

require_once(get_stylesheet_directory().'/lib/db.php');

add_shortcode('show_ggp_graph', 'show_ggp_graph');

if(! function_exists( 'show_ggp_graph') ):
function show_ggp_graph(){

session_start();

//情報の取得
$mode = $_POST['mode'];
$earth_no = $_POST['earth_no'];
$team_no = $_POST['team_no'];
$action_details_earth_no = $_POST['action_details_earth_no'];
$action_details_team_no = $_POST['action_details_team_no'];
$vote_earth_no = $_POST['vote_earth_no'];
$vote_team_no = $_POST['vote_team_no'];
$is_general = $_POST['is_general'];
$emission_trading_value = get_option('ggp_init_emission_trading');
$team_info = get_table_ggp_team_all();
$earth_info = get_table_ggp_earth_all();
$team_action_tree_info = get_table_ggp_action_tree_all();
$ggp_action_rice = get_db_table_records(TABLE_NAME_GGP_ACTION_RICE,'');
$ggp_action = get_db_table_ggp_action($action_details_earth_no, $action_details_team_no);
$error_msg = "";

if($earth_no != "" && $team_no == "") ggp_error("earth_no、またはteam_noが不正な値です。earth_no=" . $earth_no . " team_no = ". $team_no);
if($earth_no == "" && $team_no != "") ggp_error("earth_no、またはteam_noが不正な値です。earth_no=" . $earth_no . " team_no = ". $team_no);


if( ($vote_earth_no != "") &&
    ($vote_team_no != "") &&
    (isset($_POST['token']) == TRUE) &&
    (isset($_SESSION['token']) == TRUE) &&
    ($_POST['token'] == $_SESSION['token'])){
  update_table_ggp_team_vote($vote_earth_no, $vote_team_no);
  $team_info = get_table_ggp_team_all();
  $error_msg = "「" . $_POST['submit'] . "」チームに投票しました！";
}

// 新しいトークンの発行
$token = uniqid('',true);
$_SESSION['token'] = $token;

// 出力のバッファリング開始
ob_start();


?>
<div class="header-div">
  <div class="return">
    <form method="post" action="/">
    <input type="submit" class="btn-return" value="戻る">
<?php if($earth_no != "" && $team_no != ""){ ?>
    <input type="hidden" name="earth_no" value="<?=$earth_no ?>">
    <input type="hidden" name="team_no" value="<?=$team_no ?>">
    <input type="hidden" name="is_general" value="<?=$is_general ?>">
    <input type="hidden" name="mode" value="select_boardgame">
<?php } ?>
    </form>
  </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.6.0/chart.min.js"></script>

<?php if($error_msg != ""){ ?>
  <div class="error-msg">
  <?=$error_msg ?>
  </div>
<?php } ?>

<!-- お金のグラフを出力 -->
<div class="result_canvas_single_column">
<canvas id="money_graph"></canvas>
</div>

<script>
  var ctx = document.getElementById("money_graph").getContext("2d");
  var chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: [<?php foreach($team_info as $iterator){echo "\"" . $iterator->teamname . "\","; } ?>],
      datasets : [{
        label: 'お金',
        data: [<?php foreach($team_info as $iterator){
                        if($mode == 'emission_trading' && $iterator->co2 >= 0){
                          echo "\"" . ($iterator->money - ($iterator->co2/200)*$emission_trading_value) . "\",";
                        }else{
                          echo "\"" . $iterator->money . "\",";
                        }
                    }
       ?>],
        backgroundColor: [<?php foreach($team_info as $iterator){ echo "'rgba(234, 238, 127, 0.8)',"; } ?>],
        borderColor: [<?php foreach($team_info as $iterator){ echo "'rgba(0, 72, 46, 1.0)', "; } ?>],
        borderWidth: 1
      },
<?php if($mode == 'emission_trading'){ ?>
      {
        label: '二酸化炭素排出権',
        data: [<?php foreach($team_info as $iterator){ 
                if($iterator->co2 < 0){
                  echo "\"" . ($iterator->co2/200) * ( - $emission_trading_value) . "\",";
                }else{
                  echo "\"0\",";
                }
            } ?>],
        backgroundColor: [<?php foreach($team_info as $iterator){ echo "'rgba(234, 238, 127, 0.8)',"; } ?>],
        borderColor: [<?php foreach($team_info as $iterator){ echo "'rgba(0, 72, 46, 1.0)', "; } ?>],
        borderWidth: 1
      },
      {
        label: '二酸化炭素排出権',
        data: [<?php foreach($team_info as $iterator){ 
                if($iterator->co2 > 0){
                  echo "\"" . ($iterator->co2/200) * ($emission_trading_value) . "\",";
                }else{
                  echo "\"0\",";
                }
            } ?>],
        backgroundColor: [<?php foreach($team_info as $iterator){ echo "'rgba(255, 255, 255, 0)',"; } ?>],
        borderColor: [<?php foreach($team_info as $iterator){ echo "'rgba(200, 200, 200, 1.0)', "; } ?>],
        borderWidth: 1,
      }
    <?php } ?>
    ]
    },
    options: {
      maintainAspectRatio: false,
      scales: {
        x: {
          ticks: {
            color: "#000000",
            font:{
              size: 16
            }
          },
          stacked: true,
        },
        y: {
          ticks: {
            color: "#000000",
            font: {
              size: 16
            }
          },
          stacked: true,
          title: {
            display : true,
            color: "#000000",
            font: {
              size: 16
            },
            text: "お金[万円]"
          }
        },
      },
      plugins: {
        legend: {
          display: false,
        },
        title: {
          display: true,
          position: "top",
          color: "#000000",
          font : {
            size: 20
          },
          text: 'お金（うりあげ）'
        }
      }
    }
  });

</script>

<div class="result_canvas_single_column">
<canvas id="co2_graph_team"></canvas>
</div>

<!-- CO2排出量のグラフを出力 -->
<script>
  var ctx = document.getElementById("co2_graph_team").getContext("2d");
  var chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: [<?php foreach($team_info as $iterator){ echo "\"" . $iterator->teamname . "\","; } ?>],
      datasets : [{
        label: '二酸化炭素ガス排出量',
        data: [<?php foreach($team_info as $iterator){ echo "\"" . $iterator->co2 . "\","; } ?>],
<?php if($mode == 'emission_trading'){ ?>
        backgroundColor: [<?php foreach($team_info as $iterator){ echo "'rgba(255, 255, 255, 0)',"; } ?>],
        borderColor: [<?php foreach($team_info as $iterator){ echo "'rgba(200, 200, 200, 1.0)', "; } ?>],
<?php }else{ ?>
        backgroundColor: [<?php foreach($team_info as $iterator){ echo "'rgba(234, 238, 127, 0.8)',"; } ?>],
        borderColor: [<?php foreach($team_info as $iterator){ echo "'rgba(0, 72, 46, 1.0)', "; } ?>],
<?php } ?>
        borderWidth: 1
      }]
    },
    options: {
      maintainAspectRatio: false,
      scales: {
        x: {
          ticks: {
            color: "#000000",
            font: {
              size: 16
            }
          }
        },
        y: {
          ticks: {
            color: "#000000",
            font: {
              size: 16
            }
          },
          title: {
            display : true,
            color: "#000000",
            font: {
              size: 16
            },
            text: "二酸化炭素ガス排出量[kg]"
          }
        },
      },
      plugins: {
        legend: {
          display: false,
        },
        title: {
          display: true,
          position: "top",
          color: "#000000",
          font : {
            size: 20
          },
          text: '二酸化炭素ガス排出量'
        }
      }
    }
  });

</script>

<div align="center">
  <table class="ggp_select_team">
    <form method="post" action="/ggp_graph/">
      <tr><td>
        <input type="hidden" id="mode" name="mode" value="emission_trading">
        <input type="hidden" name="earth_no" value="<?=$earth_no ?>">
        <input type="hidden" name="team_no" value="<?=$team_no ?>">
        <input type="hidden" name="is_general" value="<?=$is_general ?>">
        <?php if($mode == 'emission_trading'){ ?>
        <input class="btn-select-event" type="submit" value="二酸化炭素排出量を買う(<?=$emission_trading_value?>万円／200kg）" disabled>
        <?php }else{ ?>
        <input class="btn-select-event" type="submit" value="二酸化炭素排出量を買う(<?=$emission_trading_value?>万円／200kg）">
        <?php } ?>
      </td></tr>
    </form>
  </table>
</div>

<?php if( count($earth_info) > 1 ){
?>
<div class="result_canvas_single_column">
<canvas id="co2_graph_earth"></canvas>
</div>

<!-- ルーム単位のCO2排出量のグラフを出力 -->
<script>
  var ctx = document.getElementById("co2_graph_earth").getContext("2d");
  var chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: [<?php foreach($earth_info as $iterator){echo "\"ルーム No." . $iterator->earth_no . "\","; } ?>],
      datasets : [{
        label: '二酸化炭素ガス排出量',
        data: [<?php foreach($earth_info as $iterator){ echo "\"" . $iterator->co2 . "\","; } ?>],
        backgroundColor: [<?php foreach($team_info as $iterator){ echo "'rgba(234, 238, 127, 0.8)',"; } ?>],
        borderColor: [<?php foreach($team_info as $iterator){ echo "'rgba(0, 72, 46, 1.0)', "; } ?>],
        borderWidth: 1
      }]
    },
    options: {
      maintainAspectRatio: false,
      scales: {
        x: {
          ticks: {
            color: "#000000",
            font:{
              size: 16
            }
          }
        },
        y: {
          ticks: {
            color: "#000000",
            font: {
              size: 16
            }
          },
          title: {
            display : true,
            color: "#000000",
            font: {
              size: 16
            },
            text: "二酸化炭素ガス排出量[kg]"
          }
        },
      },
      plugins: {
        legend: {
          display: false,
        },
        title: {
          display: true,
          position: "top",
          color: "#000000",
          font : {
            size: 20
          },
          text: '各ルームの二酸化炭素ガス排出量'
        }
      }
    }
  });
<?php }
?>
</script>

<div class="result_canvas_single_column">
<canvas id="tree_graph"></canvas>
</div>

<!-- 植樹回数のグラフを出力 -->
<script>
  var ctx = document.getElementById("tree_graph").getContext("2d");
  var chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: [<?php foreach($team_info as $iterator){echo "\"" . $iterator->teamname . "\","; } ?>],
      datasets : [{
        label: '木を植えた回数',
        data: [<?php foreach($team_action_tree_info as $iterator){ echo "\"" . $iterator->tree_num . "\","; } ?>],
        backgroundColor: [<?php foreach($team_info as $iterator){ echo "'rgba(234, 238, 127, 0.8)',"; } ?>],
        borderColor: [<?php foreach($team_info as $iterator){ echo "'rgba(0, 72, 46, 1.0)', "; } ?>],
        borderWidth: 1
      }]
    },
    options: {
      maintainAspectRatio: false,
      scales: {
        x: {
          ticks: {
            color: "#000000",
            font:{
              size: 16
            }
          }
        },
        y: {
          ticks: {
            color: "#000000",
            font: {
              size: 16
            },
            stepSize: 1,
          },
          title: {
            display : true,
            color: "#000000",
            font: {
              size: 16
            },
            text: "木を植えた回数[回]"
          }
        },
      },
      plugins: {
        legend: {
          display: false,
        },
        title: {
          display: true,
          position: "top",
          color: "#000000",
          font : {
            size: 20
          },
          text: '木を植えた回数'
        }
      }
    }
  });

</script>


<!-- 売り上げ回数のグラフを出力 -->
<div class="result_canvas_single_column">
<canvas id="uriage_graph"></canvas>
</div>

<script>
  var ctx = document.getElementById("uriage_graph").getContext("2d");
  var chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: [<?php foreach($team_info as $iterator){echo "\"" . $iterator->teamname . "\","; } ?>],
      datasets : [{
        label: 'おせんべいを売った回数',
        data: [<?php foreach($ggp_action_rice as $iterator){ echo "\"" . ($iterator->to_store)/5 . "\","; } ?>],
        backgroundColor: [<?php foreach($ggp_action_rice as $iterator){ echo "'rgba(234, 238, 127, 0.8)',"; } ?>],
        borderColor: [<?php foreach($ggp_action_rice as $iterator){ echo "'rgba(0, 72, 46, 1.0)', "; } ?>],
        borderWidth: 1
      }]
    },
    options: {
      maintainAspectRatio: false,
      scales: {
        x: {
          ticks: {
            color: "#000000",
            font:{
              size: 16
            }
          }
        },
        y: {
          ticks: {
            color: "#000000",
            font: {
              size: 16
            },
            stepSize: 1
          },
          title: {
            display : true,
            color: "#000000",
            font: {
              size: 16
            },
            text: "回数[回]"
          }
        },
      },
      plugins: {
        legend: {
          display: false,
        },
        title: {
          display: true,
          position: "top",
          color: "#000000",
          font : {
            size: 20
          },
          text: 'おせんべいを売った回数'
        }
      }
    }
  });

</script>

<h2>投票</h2>
<p>いちばん良いと思ったチームに投票してください！</p>
<div class="vote">
  <?php foreach($team_info as $iterator){ ?>
  <div class="vote-submit">
    <form method="post" action="/ggp_graph/">
      <input type="hidden" id="mode" name="mode" value="<?=$mode ?>">
      <input type="hidden" name="earth_no" value="<?=$earth_no ?>">
      <input type="hidden" name="team_no" value="<?=$team_no ?>">
      <input type="hidden" name="token" value="<?=$token ?>">
      <input type="hidden" name="is_general" value="<?=$is_general ?>">
      <input type="hidden" id="vote_earth_no" name="vote_earth_no" value="<?=$iterator->earth_no ?>">
      <input type="hidden" id="vote_team_no" name="vote_team_no" value="<?=$iterator->team_no ?>">
      <input type="submit" class="vote-submit" name="submit" value="<?=$iterator->teamname ?>">
    </form>
  </div>
  <?php } ?>
</div>

<?php if($earth_no == "" && $team_no == ""){ ?>
<!-- 投票結果のグラフを出力 -->
<div class="result_canvas_single_column">
<canvas id="score_graph"></canvas>
</div>

<script>
  var ctx = document.getElementById("score_graph").getContext("2d");
  var chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: [<?php foreach($team_info as $iterator){echo "\"" . $iterator->teamname . "\","; } ?>],
      datasets : [{
        label: '得票数',
        data: [<?php foreach($team_info as $iterator){ echo "\"" . ($iterator->score) . "\","; } ?>],
        backgroundColor: [<?php foreach($ggp_action_rice as $iterator){ echo "'rgba(234, 238, 127, 0.8)',"; } ?>],
        borderColor: [<?php foreach($ggp_action_rice as $iterator){ echo "'rgba(0, 72, 46, 1.0)', "; } ?>],
        borderWidth: 1
      }]
    },
    options: {
      maintainAspectRatio: false,
      scales: {
        x: {
          ticks: {
            color: "#000000",
            font:{
              size: 16
            }
          }
        },
        y: {
          ticks: {
            color: "#000000",
            font: {
              size: 16
            },
            stepSize: 1
          },
          title: {
            display : true,
            color: "#000000",
            font: {
              size: 16
            },
            text: "得票数"
          }
        },
      },
      plugins: {
        legend: {
          display: false,
        },
        title: {
          display: true,
          position: "top",
          color: "#000000",
          font : {
            size: 20
          },
          text: '得票数'
        }
      }
    }
  });

</script>

<?php } ?>
  <h2 id="action_details">チームの詳細（<?=$_POST['submit'] ?>)</h2>
    <div class="vote">
      <?php foreach($team_info as $iterator){ ?>
      <div class="vote-submit">
          <form method="post" action="/ggp_graph/#action_details">
          <input type="hidden" id="mode" name="mode" value="<?=$mode ?>">
          <input type="hidden" name="earth_no" value="<?=$earth_no ?>">
          <input type="hidden" name="team_no" value="<?=$team_no ?>">
          <input type="hidden" name="is_general" value="<?=$is_general ?>">
          <input type="hidden" id="action_details_earth_no" name="action_details_earth_no" value="<?=$iterator->earth_no ?>">
          <input type="hidden" id="action_details_team_no" name="action_details_team_no" value="<?=$iterator->team_no ?>">
          <input type="submit" class="vote-submit" name="submit" value="<?=$iterator->teamname ?>">
        </form>
      </div>
      <?php } ?>
    </div>

          <table class="action_table">
            <tr><th style="width:15%;">ターン</th><th style="width:40%;">行動</th><th style="width:15%;">お金</th><th style="width:15%;">二酸化炭素</th><th style="width:15%;">必要なターン数</th></tr>
            <?php for ($i = 0 ; $i < count($ggp_action); $i++){ ?>
            <tr>
            <td><?php echo ($ggp_action[$i]->require_turn == 0 ? "-" : $ggp_action[$i]->turn); ?></td>
            <td style="text-align:left !important;"><img class="icon-action" src="/wp-content/uploads/<?=$ggp_action[$i]->url ?>"><?php echo $phase_name[$ggp_action[$i]->phase].'('.$ggp_action[$i]->cardname.')'; ?></td>
            <td><?=$ggp_action[$i]->money; ?>万円</td>
            <td><?=$ggp_action[$i]->co2; ?>kg</td>
            <td><?php echo $ggp_action[$i]->require_turn; ?>ターン</td>
            </tr>
            <?php } ?>
            </table>

<?php

//バッファした出力を変数に格納
$return_html = ob_get_contents();
ob_end_clean();

// Shortcodeの場合、出力されるHTMLをreturnで返す必要がある
return $return_html;

}
endif;
