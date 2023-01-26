#!/bin/bash

# 参考：https://www.marketechlabo.com/bash-batch-best-practice/#%E3%83%87%E3%83%BC%E3%82%BF%E5%87%A6%E7%90%86%E3%83%90%E3%83%83%E3%83%81%E3%81%A7%E3%82%B7%E3%82%A7%E3%83%AB%E3%82%B9%E3%82%AF%E3%83%AA%E3%83%97%E3%83%88%E3%81%AF%E4%BE%BF%E5%88%A9

# エラーハンドリングのために必要。
# -e: エラーが発生したら（exit statusが0以外だったら）スクリプトの実行を終了する
# -o pipefail: パイプラインの途中でエラーが発生してもスクリプトの実行を終了する
set -e -o pipefail

################################################################################
# 定数定義
################################################################################
readonly GGP_VOLUME_DIR="/var/lib/docker/volumes/wordpress-files-ggp/_data/wp-content/themes/cocoon-child-master"
readonly GIT_REPOSITORY="/home/ec2-user/csgb/ggp"


################################################################################
# エラーハンドリング
################################################################################
# 任意のエラーを生成する関数。コマンドがエラーを出さない場合でもエラー扱いにするためのもの
# 引数はエラーメッセージ
function raise() {
  echo $1 1>&2
  return 1
}

# エラー時の処理。いわゆるcatch。エラーの原因個所を特定できるようにするための引数を受け取る
# 引数は行番号と関数（コマンド）名
err_buf=""
function err() {
  # Usage: trap 'err ${LINENO[0]} ${FUNCNAME[1]}' ERR
  status=$?
  lineno=$1
  func_name=${2:-main}
  err_str="ERROR: [`date +'%Y-%m-%d %H:%M:%S'`] ${SCRIPT}:${func_name}() returned non-zero exit status ${status} at line ${lineno}"
  echo ${err_str}
  err_buf+=${err_str}
}

# エラーの有無にかかわらず、最後に実行する関数
function finally() {
 :
}

# エラー時の挙動を指定。エラーの発生個所（行番号と関数名）を引数としてエラー処理関数に送る。
trap 'err ${LINENO[0]} ${FUNCNAME[1]}' ERR

# エラーの有無にかかわらず最後に実行する（最後に実行する処理がない場合は不要）
trap finally EXIT


################################################################################
# メインのバックアップ処理
# エラーを捕捉する行はすべて最後に「 2>&1」をつける
################################################################################

sudo cp -r ${GGP_VOLUME_DIR} ${GIT_REPOSITORY} 2>&1
cd ${GIT_REPOSITORY}
git add -A ${GIT_REPOSITORY}/* 2>&1
git commit -m "backup :`date "+%Y%m%d_%H%M%S"`" 2>&1
git push 2>&1

################################################################################
# 後処理
################################################################################

# エラーの扱いを元に戻す（エラーが発生したらスクリプトの実行が停止される）
set -e

# 進捗をファイルに出力しておく（終了）
echo "`date +'%Y-%m-%d %H:%M:%S'` Finished." | tee -a .//execute.log

