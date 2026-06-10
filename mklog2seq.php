<?php
//
// コールフローを整形するクラス
//


	const LOG_FILENAME = 0;
	const LOG_LINENO = 1;
	const LOG_FUNCNAME = 2;
	const LOG_FUNCARG = 3;
	const LOG_MODE = 4;
	const LOG_RESULT = 5;
	const LOG_MODE_RESULT = 6;
	const LOG_TASK = 0;

	$START_LOG  = "^.*\\s+(.*\\..*)\\s*\\:\\s*(\\d+)\\s*\\:\\s*([a-zA-Z0-9@_\\:\\$\\.\\-\\+\\[\\] \\,\\*\\(\\)]*)(\\(.*\\))\\s+(start|block-start).*$";
	$START_LOG2  = "^.*\\s+(.*\\..*)\\s*\\:\\s*(\\d+)\\s*\\:\\s*([a-zA-Z0-9@_\\:\\$\\.\\-\\+\\[\\] \\,\\*]*)(\\(.*\\))\\s+(start|block-start).*$";
	$start_log = [ 1, 2, 3, 4, 5 ];
	$RETURN_LOG = "^.*\\s+(.*\\..*)\\s*\\:\\s*(\\d+)\\s*\\:\\s*([a-zA-Z0-9@_\\:\\$\\.\\-\\+\\[\\] \\,\\*\\(\\)]*)\\s+((return)\\((.*?)\\)|return|end|block-end|break).*$";
	$RETURN_LOG2 = "^.*\\s+(.*\\..*)\\s*\\:\\s*(\\d+)\\s*\\:\\s*([a-zA-Z0-9@_\\:\\$\\.\\-\\+\\[\\] \\,\\*]*)\\s+((return)\\((.*?)\\)|return|end|block-end|break).*$";
	$return_log = [ 1, 2, 3, -1, 4, 6, 5 ];
	$TASK_LOG = "";
	$task_log = [ 1 ];

	$mUseOutputMemory = true;

	const TAG_START = 33;
	const TAG_START_END = 77;
	const TAG_END = 99;

	const LOG_NORMAL = 0;
	const LOG_NOTEND = 1;
	const LOG_NOTSTART = 2;


	$mGroupXS = 400;		//グループ描画間隔
	$mTaskXS = 400;		//タスク描画間隔
	$mCallnumsXS = 20;
	$mCellYS = 26;
	$mCellXS = 26;

	$mFuncColor = "\"black\""; // 関数開始標準色
	$mDiffColor = "\"red\""; // diff標準色
	$mLifeColor = "\"black\""; // ライフライン標準色
	$mWarnColor = "\"red\""; // ワーニング標準色
	$mApiColor = "\"red\""; // API標準色

	$mUnused_api = false;
	$mDiffs = [];
	$mDiffs_skip = [];

	$mPos_arg = 225;
	$mPos_com = 190;
	$mPos_life = 3;
	$mPos_task = 15;

	$mFlag_group_func = false;

	$mIs_logging = false;

	$mHeadder = "";
	$mFootter = "";
	$mOutput = "";

	$mTasks = [];




	//
	// エラー処理
	//
	function error($mes) {
		//
	}

	//
	// 出力処理
	//

	function runMacro_make_tag( $tagname, $classname, $attr, $title, $body, $mode) {
		$result = "";

		if (!is_null($classname) && strlen($classname) > 0) {
			$attr = $attr . " class=\"" . $classname . "\"";
		}

		if (!is_null($title) && strlen($title) > 0) {
			$attr = $attr . " title=\"" . $title . "\"";
		}
		$attr = trim($attr);

		if ($mode == TAG_START) {
			$result = $result . "<" . $tagname . " " . $attr . ">" . $body;
		}
		if ($mode == TAG_END) {
			$result = $result . $body . "</" . $tagname . ">";
		}
		if ($mode == TAG_START_END) {
			$result = $result . "<" . $tagname . " " . $attr . ">";
			$result = $result . $body;
    		$result = $result . "</" . $tagname . ">";
		}

		return $result;
	}

    function runMacro_make_position($lineno, $tno, $gno, $callnums, $last_tno, $last_gno, $last_callnums, $body, $mode, $headder) {
		global$mGroupXS;
		global$mTaskXS;
		global$mCellXS;
		global$mCellYS;
		global$mCallnumsXS;

		$x = $gno * $mGroupXS + $tno * $mTaskXS + $callnums * $mCellXS;
		$y = $lineno * $mCellYS;
		$last_x = $last_gno * $mGroupXS + $last_tno * $mTaskXS + $last_callnums * $mCellXS;
		if ($last_gno < 0) {
			$last_x = 0;
		}
		$x_offset = $x - $last_x;

		//2017/02/22 px埋め込みからスタイルシートへ変更
		$relpos = "";
		$abspos = "";
		if (true) {
			$mov_no = (($gno + $tno) - ($last_gno + $last_tno));
			if ($last_gno < 0) {
				$mov_no = $gno + $tno;
			}
			if ($mov_no >= 0)
			{
				$relpos = " style=\"position:relative;\" class=\"movno_r_" . $mov_no . "\";";
				$abspos = " style=\"position:absolute; top:" . $y . "px;" . "\" class=\"movno_r_" . $mov_no . "\";";
			}
			else if ($mov_no < 0)
			{
				$mov_no = abs($mov_no);
				$relpos = " style=\"position:relative;\" class=\"movno_l_" . $mov_no . "\";";
				$abspos = " style=\"position:absolute;\" top:" . $y . "px;" . "\" class=\"movno_l_" . $mov_no . "\";";
			}

		}
		else
		{
			$relpos = " style=\"position:relative; left:" . $x_offset . "px;\"";
			$abspos = " style=\"position:absolute; left:" . $x . "px;" . " top:" . $y . "px;" . "\"";
		}
		// $abspos = $abspos . " top:" . $y . "px;\"";
		// $attr = " pos=""\absolute\" " . runMacro_make_tgno_attr($tno, $gno, $callnums, $last_tno, $last_gno, $last_callnums):

		if ($headder) {
			// シーケンス以外の部分
			$body = runMacro_make_tag("span", "abspos", $abspos, "", $body, $mode);
		} else {
			// シーケンス本体
			//$body = runMacro_make_tag("span", "abspos", $abspos, "", $body, $mode);
			$body = runMacro_make_tag("span", "relpos", $relpos, "", $body, $mode);
		}
		//$body = runMacro_make_tag("span", "abspos", $abspos, "", $body, $mode);
		//$body = runMacro_make_tag("div", "abspos", $abspos, "", $body, $mode);

		return $body;
    }

	//
	// (x,y)へグループ名文字列を書く
	//
	function runMacro_make_groupName($gno, $name, $title, $tno) {
		$result = "";

		// tgnoを付ける
		$tgno = runMacro_make_tgno_attr($tno, $gno, 0, 0, 0, 0);

        $result = runMacro_make_tag("span", "groupname", $tgno, $title, $name, TAG_START_END);
        $result = runMacro_make_position(0, $tno, $gno, 0, $tno, $gno, 0, $result, TAG_START_END, false);
        $result = $result . "&nbsp;&nbsp;";

		return $result;
	}

	//
	// (x,y)へタスク名文字列を書く
	//
	function runMacro_make_taskName($tno, $name, $title) {
		$result = "";

		// tgnoを付ける
		$tgno = runMacro_make_tgno_attr($tno, 0, 0, 0, 0, 0);

        $result = runMacro_make_tag("span", "taskname", $tgno, $title, $name, TAG_START_END);
        $result = runMacro_make_position(0, $tno, 0, 0, $tno, 0, 0, $result, TAG_START_END, false);
        $result = $result . "&nbsp;&nbsp;";

		return $result;
	}

	//
	// (x,y)へcolorでコメントをつける
	//
	function runMacro_setFindComments($lineno, $tno, $gno, $callnums, $last_tno, $last_gno, $last_callnums, $text, $title) {
		$result = "";

        $result = runMacro_make_tag("span", "comment", "", $title, $text, TAG_START_END);
        $result = runMacro_make_position($lineno, $tno, $gno, $callnums, $last_tno, $last_gno, $last_callnums, $result, TAG_START_END, false);

        runMacro_write_output( $result );
	}

	function runMacro_make_tgno_attr($tno, $gno, $callnums, $last_tno, $last_gno, $last_callnums) {
		  // tgnoを付ける
		  $result = "tno=" . $tno . " gno=" . $gno . " cno=" . $callnums;;

		  return $result;
	}

	//
	// (x,y)へ関数開始文字列を書く
	//
	function runMacro_setApiName($lineno, $funcname,
			$args, $color, $gno, $tno, $callnums, $last_gno, $last_tno, $last_callnums, $filename, $lineNo, $mode, $log, $taskname ) {
		global$mGroupXS;
		global$mTaskXS;
		global$mCellXS;
		global$mCellYS;
		global$mCallnumsXS;

		  $x = $gno * $mGroupXS + $tno * $mGroupXS;
		  $last_x = $last_gno * $mGroupXS + $last_tno * $mTaskXS;
		  if ($last_gno < 0) {
			  $last_x = 0;
		  }
		  $x_offset = $x - $last_x;

		// tgnoを付ける
		$tgno = runMacro_make_tgno_attr($tno, $gno, $callnums, $last_tno, $last_gno, $last_callnums);

		$fn = runMacro_make_tag("span", "funcname", "", $log,  $funcname, TAG_START_END);
        $fa = runMacro_make_tag("span", "funcargs", "", $args, "()", TAG_START_END);
		$fb = runMacro_make_tag("span", "funcbody", "", "", $fn . $fa, TAG_START_END);
        $func = runMacro_make_tag("div", "", $tgno, "", $fb, TAG_START_END);

        $line = "";

		  $line_width = ($gno + $tno) - ($last_gno + $last_tno);
		  if ($last_gno < 0)
		  {
			  $line_width = ($gno + $tno);
		  }
		  $title = $funcname . $args;
		  $body = $filename;
		  $indexof = strrpos($filename, "/");
		  if ($indexof == false) {
			  $indexof = strrpos($filename, "\\");
		  }
		  if ($indexof != false) {
			  $body = substr($filename, $indexof+1);
		  }

		  if ($last_gno >= 0) {
			  // 呼び出し線を引く

			  $line_width = abs($line_width);
			  $line = "";
			  if ($x_offset >= 0) {
				  if (($gno != $last_gno) || ($tno != $last_tno)) {
					  $ls = runMacro_make_tag("span", "groupname", "", $title,  $body, TAG_START_END);
					  $line = runMacro_make_position($lineno, $tno, $gno, $callnums, $last_tno, $last_gno, $last_callnums, $ls, TAG_START_END, false);
				  }
				  $line = $line . runMacro_make_tag( "hr", "callline call_width_" . $line_width, "align=\"left\"", $title, "", TAG_START_END );
			  } else {
				  if (($gno != $last_gno) || ($tno != $last_tno)) {
					  $ls = runMacro_make_tag("span", "groupname", "", $title,  $body, TAG_START_END);
					  $line = runMacro_make_position($lineno, $tno, $gno, $callnums, $tno, $gno, $last_callnums, $ls, TAG_START_END, false);
				  }
				  $line = $line . runMacro_make_tag( "hr", "callline call_width_" . $line_width, "align=\"left\"", $title, "", TAG_START_END );
				  $line = runMacro_make_position($lineno, $tno, $gno, $callnums, $last_tno, $last_gno, $last_callnums, $line, TAG_START_END, false);
			  }
		  } else {
			  // 新しい始まり
			  //@@@
			  $line = runMacro_make_tag( "span", "logstartnewline", "", "", "<br><br>", TAG_START_END );
			  $ls  = runMacro_make_tag( "span", "logstart", "", "", "logstart " . $taskname, TAG_START_END );
			  $line = $line . runMacro_make_position($lineno, $tno, $gno, $callnums, $last_tno, $last_gno, $last_callnums, $ls, TAG_START_END, false);	//@@@ classname logstart なし
			  $line = $line . runMacro_make_tag( "span", "", "", "", "<br>", TAG_START_END );
			  $line = $line . runMacro_make_tag( "hr", "logstart", "width=100%", "", "", TAG_START_END );
			  $ls = runMacro_make_tag("span", "groupname", "", $title,  $body, TAG_START_END);
			  $line = $line . runMacro_make_position($lineno, $tno, $gno, $callnums, $last_tno, $last_gno, $last_callnums, $ls, TAG_START_END, false);
  		  }
		  $line = runMacro_make_tag( "div", "", $tgno, "", $line, TAG_START_END );

		  $td1 = runMacro_make_tag( "td", "lifeline", "rowspan=3 width=3 " . $tgno, "", "", TAG_START_END );
		  $td2 = runMacro_make_tag( "td", "", "" , "", $func, TAG_START_END );
		  $td3 = runMacro_make_tag( "td", "funcchild", "" , "", "", TAG_START );
		  $tr1 = runMacro_make_tag( "tr", "", "" , "", $td1 . $td2, TAG_START_END );
		  $tr2 = runMacro_make_tag( "tr", "", "" , "", $td3, TAG_START );
		  $tb  = runMacro_make_tag( "table", "function", "" , "", $tr1 . $tr2, TAG_START );

		  $tag = runMacro_make_position($lineno, $tno, $gno, $callnums, $last_tno, $last_gno, $last_callnums, $tb, TAG_START, false);

		  runMacro_write_output( $line );
		  runMacro_write_output( $tag );

	}

	//
	// (x,y)関数の戻り文字列を書く
	//
	function runMacro_setApiResult($lineno, $funcname,
			$result, $gno, $tno, $callnums, $last_gno, $last_tno, $last_callnums, $filename, $lineNo, $mode, $log, $log_mode, $taskname) {
		global$mGroupXS;
		global$mTaskXS;
		global$mCellXS;
		global$mCellYS;
		global$mCallnumsXS;

		  $x = $gno * $mGroupXS + $tno * $mTaskXS;
		  $last_x = $last_gno * $mGroupXS + $last_tno * $mTaskXS;
		  if ($last_gno < 0) {
			  $last_x = 0;
		  }
		  $x_offset = $x - $last_x;

		  $classfuncne = "funcend";
		  $classfuncresult = "funcresult";
		  $classfuncresultbody = "funcresultbody";

		  if ( $log_mode == LOG_NORMAL ) {
		  } else if ( $log_mode == LOG_NOTEND ) {
			  $classfuncne = "NotFoundfuncend";
			  $classfuncresult = "NotFoundfuncresult";
			  $classfuncresultbody = "NotFoundfuncresultbody";
		  } else if ( $log_mode == LOG_NOTSTART ) {
			  $classfuncne = "NotStartFuncend";
			  $classfuncresult = "NotStartFuncresult";
			  $classfuncresultbody = "NotStartFuncresultbody";
		  }



		// tgnoを付ける
		$tgno = runMacro_make_tgno_attr($tno, $gno, $callnums, $last_tno, $last_gno, $last_callnums);

		$fe = runMacro_make_tag("span", $classfuncne, "", $log,  $mode, TAG_START_END);
		$fr = runMacro_make_tag("span", $classfuncresult, "", "",  $result, TAG_START_END);
		$fb = runMacro_make_tag("span", $classfuncresultbody, "", "",  $fe . $fr, TAG_START_END);
		$rs = runMacro_make_tag("div", "", $tgno, "", $fb, TAG_START_END);

		$td1 = runMacro_make_tag("td", "", "", "",  "", TAG_END);
		$tr1 = runMacro_make_tag("tr", "", "", "",  $td1, TAG_END);
		$td2 = runMacro_make_tag("td", "", "", "",  $rs, TAG_START_END);
		$tr2 = runMacro_make_tag("tr", "", "", "",  $td2, TAG_START_END);
		$tb  = runMacro_make_tag("table", "", "", "",  "", TAG_END);
        $ps = runMacro_make_position($lineno, $last_tno, $last_gno, $last_callnums, $tno, $gno, $callnums, "", TAG_END, false);

		$line = "";

		  // 戻り線を引く
		  if ($last_gno >= 0) {
			  $line_width = ($gno + $tno) - ($last_gno + $last_tno);
			  if ($last_gno < 0)
			  {
				  $line_width = ($gno + $tno);
			  }

			  $line_width = abs($line_width);
			  $line = "";
			  $line = runMacro_make_tag( "hr", "returnline call_width_" . $line_width, "align=\"left\"", $result, "", TAG_START_END );
			  if ($x_offset < 0) {
				  $line = runMacro_make_position($lineno, $tno, $gno, $callnums, $last_tno, $last_gno, $last_callnums, $line, TAG_START_END, false);
			  }
		  } else {
			  // ログの終了
			  $line = runMacro_make_tag( "hr", "logend", "width=100%", "", "", TAG_START_END );
			  $le = runMacro_make_tag( "span", "logend", "", "", "logend " . $taskname, TAG_START_END );
			  $line = $line . runMacro_make_position($lineno, $tno, $gno, $callnums, $last_tno, $last_gno, $last_callnums, $le, TAG_START_END, false);  //@@@ classname logend なし
			  $line = $line . runMacro_make_tag( "span", "logendnewline", "", "", "<br><br>", TAG_START_END );

		  }
		  $line = runMacro_make_tag( "div", "", $tgno, "", $line, TAG_START_END );

	        $tag = "";
			if ( $log_mode == LOG_NORMAL || $log_mode == LOG_NOTEND ) {
		        $tag = $tr1 . $tr2 . $tb . $ps;
			} else if ( $log_mode == LOG_NOTSTART) {
		        $tag = $rs;
		        $line = "";
			} else {
				$tag = $line = "";
			}


		  if ( strlen($tag) > 0 ) {
			  runMacro_write_output( $tag );
		  }
		  if ( strlen($line) > 0 ) {
			  runMacro_write_output( $line );
		  }
	}

	//
	// (x1,y1) - (x2, y2)の背景色をcolorにする
	//
	function runMacro_setBGColor($y1, $y2, $color) {
		// 四角を描く
	}

	//
	// 出力ファイルに新しいページを用意する
	//
	function runMacro_newpage_output() {
		$tag = runMacro_make_tag("span", "newPage", "", "", "<br><br><br><br><br><br>", TAG_START_END);
		$tag = $tag . runMacro_make_tag( "hr", "newPageline", "width=100%", "", "", TAG_START_END );
		$tag = $tag . runMacro_make_tag("span", "newPage", "", "", "<br><br>", TAG_START_END);
		runMacro_write_output( $tag );
	}

	//
	// 出力ファイルにタイトル文字列を書く
	//
	function runMacro_make_title($title) {
		$result = "";

		$result = runMacro_make_tag("h1", "title", "", "", $title, TAG_START_END);

		return $result;
	}

	//
	// フォーマット変更処理
	//
	function runMacro_mkseq_change_format() {
		$result = "";

		$result = runMacro_make_tag("span", "newpage", "", "", "<br><br><br><br>", TAG_START_END);

		runMacro_write_output( $result );
	}

	//
	// 出力ファイルに1行書き込む
	//
	function runMacro_write_output($text) {
		runMacro_write_output_text( $text . "\n" );
	}

	//
	// 出力ファイルにテキストを書き込む
	//
	function runMacro_write_output_text($text) {
		global$mOutput;
		$mOutput = $mOutput . $text;
	}

	//
	// 出力ファイルを準備する
	//
	function runMacro_open_output() {
		global$mOutput;
		$mOutput = "";
	}

	//
	// 出力ファイルをクローズする
	//
	function runMacro_close_output() {
		global$mOutput;
		$result;

		$result = $mOutput;
		return $result;
	}

	//
	// 文字列が正規表現配列のどれかと一致したら true を返す
	//
	// arg1 : 正規表現の文字列の配列
	// arg2 : 文字列
	//
	function array_match($array, $str) {
		$result = false;

		if (!is_null($str)) {
			foreach  ($array as $pattern) {
				if (preg_match("/" . $pattern . "/", $str)) {
					$result = true;
					break;
				}
			}
		}
		return $result;
	}

	//
	// グループ定義の配列
	// mGroupdefs[0][0] = グループ一致正規表現パターン文字列
	// mGroupdefs[0][1] = グループ番号(0は再左端)
	// mGroupdefs[0][0] = グループ名文字列
	//
	class Group {
		public $pattern;
		public $no;
		public $name;
		public $callnums;

        //コンストラクタの定義
        function __construct()    {
            $this->pattern = "";
            $this->no = 0;
            $this->name = "";
            $this->callnums = 0;
        }
	}

	$mGroupdefs = [];

	//
	// グループファイルの読み込み mGroupdefs に格納する
	// ファイルが存在しない場合は mFlag_group_rexp = false に設定する
	//
	// arg1 : グループファイル名
	//
	// ファイル記載例)
	// #
	// #正規表現パターン,グループ番号(0-n),グループタイトル
	// #※最初の@endまでが有効
	// #
	// # #@task タスク名パターン別にシーケンスを出力
	// # ※タスク指定の場合はパターンにタスク判別用の正規表現パターンを記述する
	// # #@file ファイル名パターン別にシーケンスを出力
	// # #@func 関数名パターン別にシーケンスを出力
	// #
	//
	// ##@file
	// ##@end
	//
	//
	// #@task
	// pid\=[^ ]+
	// #@end
	//
	//
	// #@file
	// fs\/.*,0,fs
	// #@end
	//
	//
	// #@func
	// sys_.*,0,sys
	// yaffs_.*,1,yaffs
	// .*,2,other
	// #@end
	//
	function readGroupdefs($groupInput) {
		global$START_LOG;
		global$START_LOG2;
		global$start_log;
		global$RETURN_LOG;
		global$RETURN_LOG2;
		global$return_log;
		global$TASK_LOG;
		global$task_log;

		global$mFlag_group_func;
		global$mGroupXS;

		global$mPos_arg;
		global$mPos_com;
		global$mPos_life;
		global$mPos_task;

		if (!is_null($groupInput)) {
			// グループファイルの配列への読み込み
			$mPos_life = 30;

			foreach ($groupInput as $buff) {
				$line = trim($buff);
				if (strpos($line, "#") == 0 || strpos($line, ";") == 0) {
					//comment
				} else if ($line == "@file") {
					$mFlag_group_func = false;
				} else if ($line == "@func") {
					$mFlag_group_func = true;
				} else if (strpos($line, "@task=") == 0) {
					$TASK_LOG = substr($line, 6);
				} else if (strpos($line, "@task_def=") == 0) {
					$line = substr($line, 10);
					$tmp = explode(",", $line);
					for ($i = 0; $i < count($tmp); $i++) {
						$task_log[$i] = intval(trim($tmp[$i]));
					}
				} else if (strpos($line, "@func_return=") == 0) {
					$RETURN_LOG = substr(line, 13);
				} else if (strpos(line, "@func_return_def=") == 0) {
					$line = substr($line, 17);
					$tmp = explode(",", $line);
					for ( $i = 0; $i < count($tmp); $i++) {
						$return_log[$i] = intval(trim($tmp[$i]));
					}
				} else if (strpos($line, "@func_start=") == 0) {
					$START_LOG = substr($line, 12);
				} else if (strpos($line, "@func_start_def=") == 0) {
					$line = substr($line, 16);
					$tmp = explode(",", $line);
					for ( $i = 0; $i < count($tmp); $i++) {
						$start_log[$i] = intval(trim($tmp[$i]));
					}
				} else if (strpos($line, "@group=") == 0) {
					$line = substr($line, 7);
					$tmp = explode(",", $line);
					// group pattern
					$group = new Group();
					$group->pattern = $tmp[0]; // pattern
					$group->no = intval(trim($tmp[1])); // group no
					$group->name = $tmp[2]; // name
					$group->callnums = 0;
					$mGroupdefs [] = $group;
				} else if ($line == "@none") {
					// $group_list = [];
					break;
				} else if ($line == "@end") {
					break;
				} else if (strpos($line, "@pos_life=") == 0) {
					$mPos_life = intval(substr($line, 10));
					break;
				} else if (strpos($line, "@pos_task=") == 0) {
					$mPos_task = intval(substr($line, 10));
				} else if (strpos($line, "@TaskXs=") == 0) {
					$mGroupXS = intval(substr($line, 8));
					break;
				} else if (strpos($line, "@groupXs=") == 0) {
					$mGroupXS = intval(substr($line, 9));
					break;
				} else if (strpos($line, "@pos_arg=") == 0) {
					$mPos_arg = intval(substr($line, 9));
					break;
				} else if (strpos($line, "@pos_com=") == 0) {
					$mPos_com = intval(substr($line, 9));
					break;
				} else if (preg_match("/" . ".*,[0-9]+,.*" . "/", $line)) {
					$tmp = explode(",", $line);
					// group pattern
					$group = new Group();
					$group->pattern = $tmp[0]; // pattern
					$group->no = intval(trim($tmp[1])); // group no
					$group->name = $tmp[2]; // name
					$group->callnums = 0;
					$mGroupdefs [] = $group;
				}
			}
			// p group_list;
		}
	}

	//
	// グループ一致チェック
	// mGroupdefs内で一致するグループを探す
	// 一致しなかった場合は新規グループを引数のグループ名で最後に追加してそのグループ番号を返す
	// 新規グループは引数の名前とするため正規表現は使われない
	// 新規グループの位置が100を超える場合はその他として全てまとめられる
	// (正規表現できると良い)
	//
	// arg1 : 比較するグループ名
	//
	// ret : 一致したグループ番号
	//

	function findTaskGroupdefs($task, $name) {
		$nn = 0;
		foreach ($task->groupDefs as $group) {
			if (is_null($group->pattern) || strlen($group->pattern) == 0) {
				if ($name == $group->name) {
					return $group;
				}
			} else if (preg_match("/" . $group->pattern . "/", $name)) {
				return $group;
			}
			if ( $nn <= $group->no){
				$nn = $group->no + 1;
			}
		}

		$group = new Group();
		$group->name = $name;
		$group->no = $nn;
		$group->pattern = null;
		$group->callnums = 0;

		if ($nn > 50) {
			// 横幅制限なので全てを対象とする
			$group->pattern = ".*";
			$group->name = "その他";
		}

		$task->groupDefs [] = $group;
		return $group;
	}

	class FuncCall {
		public $funcname;
		public $filename;
		public $returnname;
		public $lastreturnname;
		public $top;
		public $logLineNo;
		public $log;
		public $group;

        //コンストラクタの定義
        function __construct()    {
            $this->funcname = "";
            $this->filename = "";
            $this->returnname = "";
            $this->lastreturnname = "";
            $this->top = 0;
            $this->logLineNo = 0;
            $this->log = "";
            $this->group = new Group();
        }
	}

	//
	// tasks[0][0] = タスク名
	// tasks[0][1] = グループ情報
	// tasks[0][2] = タスク番号
	//
	class Task {
		public $name;
		public $no;
        public $funccalls;
        public $groupDefs;

        //コンストラクタの定義
        function __construct()    {
            $this->name = "";
            $this->no = 0;
            $this->funccalls = [];
            $this->groupDefs = [];
        }
	}

	//
	// 登録済みのタスク検索
	// 一致するタスクがあればそのテーブルを返す
	// 存在しなければ最後に追加してそのグループ情報を初期化する
	//
	// arg1 : タスク名
	//
	// ret : 一致したタスクグテーブル
	//
	function getTaskDefs($name) {
		global$mTasks;
		global$mGroupdefs;
		global$mFlag_group_func;

		$nn = 0;
		foreach ($mTasks as $task) {
			if ($task->name == $name) {
				// p work
				return $task;
			}
			$nn = $nn + 1;
		}

		$task = new Task();
		$task->name = $name;
		$task->funccalls = [];
		$task->no = $nn;
		$task->groupDefs = $mGroupdefs;
		if ( count($task->groupDefs) == 0 && $mFlag_group_func) {
			// グループ未定義時は全部を１つのグループで処理する
			$group = new Group();
			$group->no = 0;
			$group->name = "ALL";
			$group->pattern = ".*";
			$task->groupDefs [] = $group;
		}
		$mTasks [] = $task;

		return $task;
	}

	class FindNameResult {
		public $top;
		public $task;
		public $funccall;

        //コンストラクタの定義
        function __construct()    {
            $this->top = 0;
            $this->task = new Task();
            $this->funccall = new FuncCall();
        }
	}

	function makeFindNameResult($task, $callnums, $funccall) {
		$result = new FindNameResult();

		$result->task = $task; // task no
		$result->funccall = $funccall;

		return $result;
	}

	function addName($taskname, $funcname, $filename, $returnname, $lastreturnname,
			$top, $pattern, $logLineNo, $log) {
		$task = getTaskDefs($taskname);

		$funccall = new FuncCall();
		$funccall->funcname = $funcname;
		$funccall->filename = $filename;
		$funccall->returnname = $returnname;
		$funccall->lastreturnname = $lastreturnname;
		$funccall->logLineNo = $logLineNo;
		$funccall->top = $top;
		$funccall->log = $log;
		$funccall->group = findTaskGroupdefs($task, $pattern);
		$task->funccalls [] = $funccall;

	}

	function findName($taskname, $name, $filename, $dir) {

		$task = getTaskDefs($taskname);

		if ($dir == 0) {
			$dir = 1;
		}

			$idx = 0;
			if ($dir >= 0) {
				$idx = 0;
			} else {
				$idx = count($task->funccalls) - 1;
			}

			for ($i = 0; $i < count($task->funccalls); $i++) {
				$funccall = $task->funccalls[$idx];
				if (!is_null($funccall)) {
					if ($funccall->funcname == $name && ($funccall->filename == $filename || $filename == ".*")) {
						$callnums = 0;
						$result = makeFindNameResult($task, $callnums, $funccall);

						// printf( "findlName(%s, %d, %d, %d, %s)\n", name, gno,
						// offset, top, returnname );
						return $result;
					}

				}
				$idx = $idx + $dir;
			}
		return null;
	}

	function popName() {		// 挙動はpopではない
		global$mTasks;
		foreach ($mTasks as $task) {
				$idx = 0;
				foreach ($task->funccalls as $funccall) {
					if (!is_null($funccall)) {
						$callnums = 0;
						$result = makeFindNameResult($task, $callnums, $funccall);
						array_splice($task->funccalls, $idx, 1);

						// printf( "findlName(%s, %d, %d, %d, %s)\n", name, gno,
						// offset, top, returnname );
						return $result;
					}
					$idx = $idx + 1;
				}
			}
		return null;
	}

	function delName($taskname, $name, $filename) {
		$result = [];
		$findNameResult = findName($taskname, $name, $filename, -1);	//@@@

		if (!is_null($findNameResult)) {
			//@@@ $idx = findNameResult.task.funccalls.indexOf(findNameResult.funccall);
            $idx = 0;
			foreach ($findNameResult->task->funccalls as $funccall) {
                if ($funccall == $findNameResult->funccall) {
                    break;
                }
                $idx = $idx + 1;
            }

			$task = getTaskDefs($taskname);

			while ($idx < count($task->funccalls)) {
				$funccall = $task->funccalls[$idx];
				array_splice($task->funccalls, $idx, 1);

				$callnums = 0;
				$tmp = makeFindNameResult($task , $callnums, $funccall);

				$result [] = $tmp;
			}
		}

		return $result;
	}

	function checkDiffs($diffs, $lineNo, $is_diff_right) {
		$pos = 0;
		$mDiffs_skip = [];

		foreach ($diffs as$buff) {
			$line = trim($buff);
			$left_top = 0;
			$left_end = 0;
			$right_top = 0;
			$right_end = 0;
			$mode = null;

			if (preg_match("/" . "^[0-9]+.[0-9]+,[0-9]+$" . "/", $line)) {
				$tmp = explode("[,a-zA-Z]", $line);
				$left_top = intval($tmp[0]);
				$left_end = intval($tmp[0]);
				$mode = str_replace ("[0-9]\\,", "", $line);
				$right_top = intval($tmp[1]);
				$right_end = intval($tmp[2]);
			}
			if (preg_match("/" . "^[0-9]+,[0-9]+.[0-9]+$" . "/", $line)) {
				$tmp = explode("[,a-zA-Z]", $line);
				$left_top = intval($tmp[0]);
				$left_end = intval($tmp[1]);
				$mode = str_replace ("[0-9]\\,", "", $line);
				$right_top = intval($tmp[2]);
				$right_end = intval($tmp[2]);
			}
			if (preg_match("/" . "^[0-9]+,[0-9]+.[0-9]+,[0-9]+$" . "/", $line)) {
				$tmp = explode("[,a-zA-Z]", $line);
				$left_top = intval(tmp[0]);
				$left_end = intval(tmp[1]);
				$mode = line.replaceAll("[0-9]\\,", "");
				$right_top = intval(tmp[2]);
				$right_end = intval(tmp[3]);
			}

			if (!is_null(mode)) {
				// printf( "diffs %d : (%d-%d),(%d-%d)\n", lineNo, left_top,
				// left_end, right_top, right_end );
				$lines = [0, 0, 0];

				if ($is_diff_right == true && $lineNo >= $right_top
						&& $lineNo <= $right_end) {
					if ($mode == "a") {
						$lines[0] = 1;
						$lines[1] = $right_end - $right_top;
						$lines[2] = 1;
					}

					if ($mode == "d") {
						$lines[0] = $left_end - $left_top + 2;
						$lines[1] = $left_end - $left_top;
						$lines[2] = 2;
					}

					if ($mode == "c") {
						$lines[0] = $left_end - $left_top;
						$lines[1] = $right_end - $right_top;
						if ($lines[0] > $lines[1]) {
							$lines[1] = $lines[0];
						}
						$lines[2] = 1;
					}
				}

				if ($is_diff_right == false && $lineNo >= $left_top
						&& $lineNo <= $left_end) {
					if ($mode == "a") {
						$lines[0] = $right_end - $right_top + 2;
						$lines[1] = $right_end - $right_top;
						$lines[2] = 2;
					}

					if ($mode == "d") {
						$lines[0] = 1;
						$lines[1] = $left_end - $left_top;
						$lines[2] = 1;
					}

					if ($mode == "c") {
						$lines[0] = $right_end - $right_top;
						$lines[1] = $left_end - $left_top;
						if ($lines[0] > $lines[1]) {
							$lines[1] = $lines[0];
						}
						$lines[2] = 1;
					}
				}

				if ($lines[0] != 0 || $lines[1] != 0 || $lines[2] != 0) {
					// printf( "diffs %d : (%d-%d),(%d-%d)\n", lineNo, left_top,
					// left_end, right_top, right_end );
					$mDiffs_skip [] = pos;
					return $lines;
				}

			}

			$pos = $pos + 1;
		}
		return $null;
	}

	//
	//
	//

	function mkseq_exec($title, $logInput, $groupInput, $func_api, $pickup_api, $is_diff_mode, $is_diff_right) {
		global$mHeadder;
		global$mFootter;
		global$mTasks;

		global$START_LOG;
		global$START_LOG2;
		global$start_log;
		global$RETURN_LOG;
		global$RETURN_LOG2;
		global$return_log;
		global$TASK_LOG;
		global$task_log;

		global$mUseOutputMemory;

		global$mFuncColor;
		global$mDiffColor;
		global$mLifeColor;
		global$mWarnColor;
		global$mApiColor;

		global$mUnused_api;
		global$mDiffs;
		global$mDiffs_skip;

		global$mPos_arg;
		global$mPos_com;
		global$mPos_life;
		global$mPos_task;

		global$mFlag_group_func;

		global$mIs_logging;




		$result = null;

		runMacro_open_output();

		// make new page
		runMacro_newpage_output();

		$lno = 0;

		// p pickup_api;

		readGroupdefs($groupInput);

		// java task
		// if ( mFlag_task ) {
		// mPos_life = 4;
		// mPos_task = 20;
		// end

		// printf("====== autolog converter =====\n");

		// ARGV.each {|filename|

		// printf("=== start %s ===\n", logfilename);

		$logLineNo = 0;
		$diffLineNo = 0;
		$lno = 1;
		$lastname = ""; // 最後に登録した関数名
		$lastfilename = ""; // 最後に登録した関数のファイル名

		$stock_buf = "";

        try {
			foreach ($logInput as $buff) {
				if ($buff == null) {
					break;
				}
				$line = trim($buff);

				$logLineNo = $logLineNo + 1;

				// printf( "%d:%s\n", lineNo, line );

				// [sz3]カスタム
				// if ( stock_buf.length() > 0 ) {
				// if ( !(/\[sz3\]/ =~ line) ) {
				// /\<[0-9]\>\[[0-9\. ]+\] (.+)/ =~ line;
				// if ( $1 == null ) {
				// line = stock_buf;
				// else
				// line = stock_buf + $1;
				// end
				// else
				// printf( "%s(%d) : log format error\n", logfilename, lineNo );
				// end
				// stock_buf = "";
				// end

				// printf( "log=[%s]\n", line );

				$funcname = "";
				$pattern = $START_LOG;
				if (!preg_match("/" . $pattern . "/", $line)) {
					$pattern = $START_LOG2;
				}
				if (preg_match_all("/" . $pattern . "/", $line, $matches)) {
					// start function
					$funcname = null;
					if ($start_log[LOG_FUNCNAME] > 0) {
//@@@todo@@@
						$funcname = $matches[$start_log[LOG_FUNCNAME]][0];
					}
				}
				if ($funcname == null) {
					$funcname = "";
				}

				// トリガーとなる関数名まで読み飛ばす
				if ($mUnused_api == true || $mIs_logging == true
						|| count($pickup_api) == 0
						|| array_match($pickup_api, $funcname)) {
					$mIs_logging = true;

					// split word
					$pattern = $START_LOG;
					if (!preg_match("/" . $pattern . "/", $line)) {
						$pattern = $START_LOG2;
					}
					if (preg_match_all("/" . $pattern . "/", $line, $matches)) {
						// start function
						$filename = null;
						if ($start_log[LOG_FILENAME] > 0) {
//@@@todo@@@
							$filename = $matches[$start_log[LOG_FILENAME]][0];
						}
						$lineno = null;
						if ($start_log[LOG_LINENO] > 0 ) {
//@@@todo@@@
							$lineno = $matches[$start_log[LOG_LINENO]][0];
						}
						$funcname = null;
						if ($start_log[LOG_FUNCNAME] > 0) {
//@@@todo@@@
							$funcname = $matches[$start_log[LOG_FUNCNAME]][0];
						}
						$args = null;
						if ($start_log[LOG_FUNCARG] > 0) {
//@@@todo@@@
							$args = $matches[$start_log[LOG_FUNCARG]][0];
						}
						$mode = null;
						if ($start_log[LOG_MODE] > 0) {
//@@@todo@@@
							$mode = $matches[$start_log[LOG_MODE]][0];
						}
//echo $funcname . "\n";
						if ($funcname == null) {
							continue;
						}
						if ($filename == null) {
							$filename = "???";
						}
						if ($lineno == null) {
							$lineno = "???";
						}
						if ($args == null) {
							$args = "";
						}
						if ($mode == null) {
							$mode = "";
						}
						// p "start " . funcname

						$taskname = "メインタスク";
						if ($TASK_LOG != null && strlen($TASK_LOG) > 0) {
							$pattern = $TASK_LOG;
							if (preg_match_all("/" . $pattern . "/", $line, $matches)) {
								$taskname = null;
								if ($task_log[LOG_TASK] > 0) {
//@@@todo@@@
									$taskname = $matches[$task_log[LOG_TASK]][0];
								}
							}
						}
						if ($taskname == null) {
							$taskname = "???";
						}
						// 関数名あるいはファイル名によるグループの検索
						$tmpname;
						if ($mFlag_group_func) {
							$tmpname = $funcname;
						} else {
							$tmpname = $filename;
						}
						//
						// タスク情報から最後の関数名をトップダウン検索
						//
						$last = findName($taskname, $lastname, $lastfilename, -1);	//@@@

						//
						// タスク情報から関数名のグループ位置を検索、なければ追加
						//
						$findNameResult = findName($taskname, $funcname, $filename, -1);	//@@@
						// javaでは同一関数名を定義できるため無条件追加
						// Ｃではロジックエラーのはず
						$top = $lno + 1;
						addName($taskname, $funcname, $filename, $lastname, $lastfilename, $top, $tmpname, $logLineNo, $line);
						// end
						$findNameResult = findName($taskname, $funcname, $filename, -1);	//@@@

						$gno = $findNameResult->funccall->group->no;
						$tno = $findNameResult->task->no;
						$callnums = $findNameResult->funccall->group->callnums;
						$top = $findNameResult->top;
						$returnname = $findNameResult->funccall->returnname;
						$lastreturnname = $findNameResult->funccall->lastreturnname;

						$findNameResult = null;

						// 現在の関数位置を移動
						$lastname = $funcname;
						$lastfilename = $filename;

						$last_gno = -1;
						$last_tno = -1;
						$last_callnums = -1;
						$last_top = -1;
						$last_returnname = "";
						$last_lastreturnname = "";

						// グループが変わったので横線を引く
						if ($last != null) {
							$last_gno = $last->funccall->group->no;
							$last_tno = $last->task->no;
							$last_callnums = $last->funccall->group->callnums;
							$last_top = $last->top;
							$last_returnname = $last->funccall->returnname;
							$last_lastreturnname = $last->funccall->lastreturnname;
						}
						$lno = $lno + 1;

						$color = $mFuncColor;

						$api = strpos($func_api, $funcname) >= 0;
						if ($api == true) {
							$color = $mApiColor;
						}
						// 現在位置(gno,offset)に関数を出力
						runMacro_setApiName($lno, $funcname, $args, $color, $gno, $tno, $callnums, $last_gno, $last_tno, $last_callnums, $filename, $lineno, $mode, "Log(" . $logLineNo . "):" . $line, $taskname);

						// 関数の引数出力
						// printf( "%s%s\n", funcname, args, filename, mode );
						// 関数概要
						// runMacro_setFindComments(mPos_com, y, funcname);

						$stock_buf = "";

						// diffs
						$diffLineNo = $diffLineNo + 1;
						if ($is_diff_mode) {
							$lines = checkDiffs($mDiffs, $diffLineNo, $is_diff_right);
							if ($lines != null) {
								runMacro_setBGColor($lno + $lines[2], $lno
										+ $lines[2] + $lines[1], $mDiffColor);
								$lno = $lno + $lines[0];
							}
						}
					}
					$pattern = $RETURN_LOG;
					if (!preg_match("/" . $pattern . "/", $line)) {
						$pattern = $RETURN_LOG2;
					}
					if (preg_match_all("/" . $pattern . "/", $line, $matches)) {
						// end function

						$filename = null;
						if ( $return_log[LOG_FILENAME] > 0 ) {
//@@@todo@@@
							$funcname = $matches[$return_log[LOG_FILENAME]][0];
						}
						$lineno = null;
						if ($return_log[LOG_LINENO] > 0) {
//@@@todo@@@
							$lineno = $matches[$return_log[LOG_LINENO]][0];
						}
						$funcname = null;
						if ( $return_log[LOG_FUNCNAME] > 0 ) {
//@@@todo@@@
							$funcname = $matches[$return_log[LOG_FUNCNAME]][0];
						}
						$mode = null;
						if ( $return_log[LOG_MODE] > 0) {
//@@@todo@@@
							$mode = $matches[$return_log[LOG_MODE]][0];
						}
						$funcresult = null;
						if ($return_log[LOG_RESULT] > 0) {
//@@@todo@@@
							$funcresult = $matches[$return_log[LOG_RESULT]][0];
						}
						if ( $funcresult == null) {
							$funcresult = null;
						} else {
							$mode = null;
							if ($return_log[LOG_MODE_RESULT] > 0) {
//@@@todo@@@
								$mode = $matches[$return_log[LOG_MODE_RESULT]][0];
							}
						}

						if ($funcname == null) {
							continue;
						}
						if ($filename == null) {
							$filename = "???";
						}
						if ($lineno == null) {
							$lineno = "???";
						}
						if ($mode == null) {
							$mode = "";
						}
						if ( $funcresult == null) {
							$funcresult = "";
						}
						// p "end " . funcname

						$taskname = "メインタスク";
						if ($TASK_LOG != null && strlen($TASK_LOG) > 0) {
							$pattern = TASK_LOG;
							if (preg_match_all("/" . $pattern . "/", $line, $matches)) {
//@@@todo@@@
								$taskname = $matches[$task_log[LOG_TASK]][0];
							}
						}
						// ファイル名によるグループの検索
						$delNameResult = delName($taskname, $funcname, ".*");	//@@@

						if (count($delNameResult) == 0) {
							$mes = $filename . "(" . $logLineNo . ")" . " : can't found start(" . $funcname . ")\n";
							error($mes);
							runMacro_setApiResult($lno + 1, "???:func start log not found!!, " . $funcname,
									$funcresult, -1, -1, -1, -1, -1, -1, $filename, $lineno, "no start log(func=" . $funcname . "():" . $mode, "Log(" . $logLineNo . "):" . $line, LOG_NOTSTART, $taskname);
						} else {
							while (count($delNameResult) > 0) {
								//@@@todo@@@
								$findNameResult = array_pop($delNameResult);
								$chkwarn = false;
								if (count($delNameResult) > 0) {
									$chkwarn = true;

								}

								// printf( "found start : %s\n", returnname );
								$gno = $findNameResult->funccall->group->no;
								$tno = $findNameResult->task->no;
								$callnums = $findNameResult->funccall->group->callnums;
								$top = $findNameResult->top;
								$returnname = $findNameResult->funccall->returnname;
								$lastreturnname = $findNameResult->funccall->lastreturnname;
								$stack_funcname = $findNameResult->funccall->funcname;
								$log = $findNameResult->funccall->log;
								$logNo = $findNameResult->funccall->logLineNo;
								$findNameResult = null;

								$last_gno = -1;
								$last_tno = -1;
								$last_callnums = -1;
								$last_top = -1;
								$last_returnname = "";
								$last_lastreturnname = "";


								// 関数の終了なので関数の始まりであるtopから現在位置(lno)まで縦線を引く
								$color = $mLifeColor;
								if ($chkwarn == true) {
									$color = $mWarnColor;
								}
								// runMacro_life_Click(gno, tno, columsx, top, lno, color);

								// グループが変わったので関数の戻り用に横線を引く
								$last = findName($taskname, $returnname, $lastreturnname, -1);		//@@@
								if ($last != null) {
									$last_gno = $last->funccall->group->no;
									$last_tno = $last->task->no;
									$last_callnums = $last->funccall->group->callnums;
									$last_top = $last->top;
									$last_returnname = $last->funccall->returnname;
									$last_lastreturnname = $last->funccall->lastreturnname;

								} else {
									// if ( returnname != null ) {
									// printf( "can't find return fuc : %s\n",
									// returnname );
									// end
								}
								// 関数の戻り値処理
								if ($chkwarn == true) {
									// 戻りのログが見つからなかった関数は強制的に閉じる(※遡ってまで関数は閉じない)
									runMacro_setApiResult($lno + 1, "???:func end log not found!!, " . $stack_funcname,
											"???", $gno, $tno, $callnums, $gno, $tno, $callnums, "???", "???", "no end log", "Log(" . $logNo . "):" . $log, LOG_NOTEND, "???");
								} else {
									if ( $stack_funcname != $funcname ) {
										$stack_funcname = "???:mismatch func!!, log='" . $funcname . "', stack:'" . $stack_funcname . "'";
									}
									runMacro_setApiResult($lno + 1, $stack_funcname,
											$funcresult, $gno, $tno, $callnums, $last_gno, $last_tno, $last_callnums, $filename, $lineno, $mode, "Log(" . $logLineNo . "):" . $line, LOG_NORMAL, $taskname);
								}

								// 現在位置を元に戻す
								$lastname = $returnname;
								$lastfilename = $lastreturnname;
							}
						}
						$lno = $lno + 1;

						$stock_buf = "";

						// ロギング終了チェック

						// diffs
						$diffLineNo = $diffLineNo + 1;
						if ($is_diff_mode) {
							$lines = checkDiffs($mDiffs, $diffLineNo, $is_diff_right);
							if ($lines != null) {
								runMacro_setBGColor($lno + $lines[2], $lno
										+ $lines[2] + $lines[1], $mDiffColor);
								$lno = $lno + $lines[0];
							}
						}
						// [sz3]カスタム
						// } else if ( (/\[sz3\]/ =~ line) && !(/(start|end)
						// \=\=\=\=\=$/ =~ line) ) {
						// stock_buf = line;
						
						
						//@@@break
					}
				}
			}

			$findNameResult = null;

			while (true) {
				$findNameResult = popName();
				if ($findNameResult == null) {
					break;
				} else {
					// 関数の終了していなので関数の始まりであるtopから現在位置(lno)まで縦線を引く
					$gno = $findNameResult->funccall->group->no;
					$tno = $findNameResult->task->no;
					$callnums = $findNameResult->funccall->group->callnums;
					$top = $findNameResult->top;
					$returnname = $findNameResult->funccall->returnname;
					$lastreturnname = $findNameResult->funccall->lastreturnname;
					$funcname = $findNameResult->funccall->funcname;
					$log = $findNameResult->funccall->log;

					// runMacro_life_Click(gno, tno, columsx, top, lno, color);

					// 関数の戻り値処理
					runMacro_setApiResult($lno + 1, "???:func end log not found!!, " . $funcname . "->" . $returnname,
							"???", $gno, $tno, $callnums, $gno, $tno, $callnums, "???", "???", "out of log", "Log(" . "???" . "):" . $log, LOG_NOTEND, "???");

					$mes = "can't func end(" . $funcname . ":[" . $log . "])\n";
					error($mes);
				}
			}

			$taskname = "";
			$groupname = "";
			// printf("\n\n=== end %s ===\n", logfilename);
			$cntTask = 0;
			foreach ($mTasks as $task) {
				$taskname = $taskname . runMacro_make_taskName($task->no, $task->name, $task->name);

				$cntGroup = 0;
				foreach ($task->groupDefs as $group) {
					$name = "";
					if ($mFlag_group_func) {
						$name = $group->name;
					} else {
						//@@@todo@@@
						//$file = new File($group->name);
						//$name = $file->getName();
						$name = $group->name;
						$pos = strrpos($group->name, "/");
						if ($pos >= 0) {
                        	$name = substr($group->name, $pos + 1);
						}
					}
					$groupname = $groupname . runMacro_make_groupName($group->no, $name, $group->name, $task->no);
					$cntGroup++;
					if ($cntGroup >= 8) {
				        $groupname = $groupname . runMacro_make_tag("span", "groupnameNewline", "", "", "<br><br>", TAG_START_END);
				        $cntGroup = 0;
					}
				}
		        $groupname = $groupname . runMacro_make_tag("span", "groupnameNewline", "", "", "<br><br>", TAG_START_END);

		        $cntTask++;
				if ($cntTask >= 8) {
					$cntTask = 0;
					$taskname = $taskname . runMacro_make_tag("span", "tasknameNewline", "", "", "<br><br>", TAG_START_END);
				}
			}
			$taskname = $taskname . runMacro_make_tag("span", "tasknameNewline", "", "", "<br><br>", TAG_START_END);

			$seqtitle = runMacro_make_title($title);
			$seqtitle = $seqtitle . runMacro_make_tag("span", "titleNewline", "", "", "<br>", TAG_START_END);

			runMacro_mkseq_change_format();
			$htmlbody = runMacro_close_output();
			$posStart = ""; //@@@"style=\"position:absolute;\"";
			$htmlbody = runMacro_make_tag("div", "sequencebBody", "", "",  $htmlbody, TAG_START_END);

			// make html
			runMacro_open_output();

			// append html headder
			runMacro_write_output_text( $mHeadder );

			// append title, taskname, groupname
			$title = runMacro_make_tag("div", "titleDef", "", "",  $seqtitle, TAG_START_END);
			$groupname = runMacro_make_tag("div", "groupDef", $posStart, "",  $groupname, TAG_START_END);
			$taskname = runMacro_make_tag("div", "taskDef", $posStart, "",  $taskname, TAG_START_END);
			$htmlheadder = runMacro_make_tag("div", "sequenceHeadder", "", "",  $title . $taskname . $groupname, TAG_START_END);
			runMacro_write_output_text($htmlheadder);

			runMacro_write_output_text($htmlbody);

			// append html footter
			runMacro_write_output_text( $mFootter );

			// printf("\n\n=== exit mkseq() ===\n");

			$result = runMacro_close_output();

/*@@@ write local file
			if ( mUseOutputMemory && outputFilename != null ) {
				putFile(outputFilename, "utf-8", result);
			}
*/

		} finally {
		}

		return $result;
	}

	function execCmd($cmd) {
		// @@@
		return "";
	}

	function mkseq_buff($title, $logInput, $groupInput, $func_api, $pickup_api, $headder, $footer) {
		global$mHeadder;
		global$mFootter;

		$result = "";
		// diffs
		$is_diff_mode = false;
		$is_diff_right = false;

		$mHeadder = $headder;
		$mFootter = $footer;

		$result = mkseq_exec($title, $logInput, $groupInput, $func_api, $pickup_api, $is_diff_mode, $is_diff_right);
		return $result;
	}


    $username = "user";
    if(isset($_POST['username'])) {
        $username = $_POST['username'];
    }

	$title = "test";
    if(isset($_POST['title'])) {
        $title = $_POST['title'];
    }

	$log = "test";
    if(isset($_POST['log'])) {
        $log = $_POST['log'];
    }

    $logInput = explode("\n", $log);
//var_dump($logInput);

	$header = "header";
    if(isset($_POST['header'])) {
        $header = $_POST['header'];
    }

	$footer = "footer";
    if(isset($_POST['footer'])) {
        $footer = $_POST['footer'];
    }

	$grouop = "";
    if(isset($_POST['grouop'])) {
        $grouop = $_POST['grouop'];
    }
    $groupInput = explode("\n", $grouop);

	$pickup = "";
    if(isset($_POST['pickup'])) {
        $pickup = $_POST['pickup'];
    }
    $pickup_api = explode("\n", $pickup);

	$api = "";
    if(isset($_POST['api'])) {
        $api = $_POST['api'];
    }
    $func_api = $api;


	$result = mkseq_buff($title, $logInput, $groupInput, $func_api, $pickup_api, $header, $footer);

//fclose($file);

//アクセスを書き込むファイルの場所
$log = "./count.txt";

//count.txtにアクセス数を書き込む処理
$fp = fopen($log, "r+") or die($log."が開けません");
flock($fp, LOCK_EX); 
$count = intval(fgets($fp, 64)); 
$count++; 
rewind($fp);
fputs($fp, $count); 
fclose($fp); 

    //header("Content-type: text/plain; charset=utf-8");
    echo $result;
?>