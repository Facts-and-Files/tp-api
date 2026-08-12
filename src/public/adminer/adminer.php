<?php
/** Adminer - Compact database management
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2007 Jakub Vrana
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 6.0.0
*/namespace
Adminer;const
VERSION="6.0.0";error_reporting(24575);set_error_handler(function($Uc,$Wc){return!!preg_match('~^Undefined (array key|offset|index)~',$Wc);},E_WARNING|E_NOTICE);$yd=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($yd||ini_get("filter.default_flags")){foreach(array('_GET','_POST','_COOKIE','_SERVER')as$X){$Uk=filter_input_array(constant("INPUT$X"),FILTER_UNSAFE_RAW);if($Uk)$$X=$Uk;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");function
connection($g=null){return($g?:Db::$instance);}function
adminer(){return
Adminer::$instance;}function
driver(){return
Driver::$instance;}function
connect(){$Pb=adminer()->credentials();$I=Driver::connect($Pb[0],$Pb[1],$Pb[2]);return(is_object($I)?$I:null);}function
idf_unescape($u){if(!preg_match('~^[`\'"[]~',$u))return$u;$uf=substr($u,-1);return
str_replace($uf.$uf,$uf,substr($u,1,-1));}function
q($Q){return
connection()->quote($Q);}function
idx($_a,$x,$k=null){return($_a&&array_key_exists($x,$_a)?$_a[$x]:$k);}function
number($X){return
preg_replace('~[^0-9]+~','',$X);}function
int_type(){return'(tiny|small|medium|big)?int(eger|\d)?';}function
number_type(){return'(^('.int_type().'|decimal|numeric|real|(binary_|half_|scaled_)?float\d?|(binary_)?double( precision)?|(small)?money)$)';}function
remove_slashes(array$pl,$yd=false){$I=array();foreach($pl
as$x=>$X)$I[stripslashes($x)]=(is_array($X)?remove_slashes($X,$yd):($yd?$X:stripslashes($X)));return$I;}function
bracket_escape($u,$Ia=false){static$Ak=array(':'=>':1',']'=>':2','['=>':3','"'=>':4','='=>':5');return
strtr($u,($Ia?array_flip($Ak):$Ak));}function
url_escape($Q){static$Ak=array();if(!$Ak){$Ak=array(' '=>'+');foreach(str_split("\"'<>#%&+=?".ini_get("arg_separator.input"))as$ab)$Ak[$ab]=sprintf('%%%02X',ord($ab));for($s=0;$s<256;$s++){if($s<32||$s>126)$Ak[chr($s)]=sprintf('%%%02X',$s);}}return
strtr((string)$Q,$Ak);}function
min_version($sl,$Mf="",$g=null){$g=connection($g);$nj=$g->server_info;if($Mf&&preg_match('~([\d.]+)-MariaDB~',$nj,$B)){$nj=$B[1];$sl=$Mf;}return$sl&&version_compare($nj,$sl)>=0;}function
charset(Db$f){return(min_version("5.5.3",0,$f)?"utf8mb4":"utf8");}function
ini_set($ih,$Y){return(function_exists('ini_set')?\ini_set($ih,$Y):false);}function
ini_bool($Qe){$X=ini_get($Qe);return(preg_match('~^(on|true|yes)$~i',$X)||(int)$X);}function
ini_bytes($Qe){$X=ini_get($Qe);switch(strtolower(substr($X,-1))){case'g':$X=(int)$X*1024;case'm':$X=(int)$X*1024;case'k':$X=(int)$X*1024;}return$X;}function
max_input_vars($J,$xh){$Qf=(int)ini_get("max_input_vars");return($Qf?(int)floor(($Qf-$xh)/$J):0);}function
max_input_vars_error(){$Qe="max_input_vars";return
sprintf('Maximum number of allowed fields exceeded. Please increase %s.',"<b>$Qe = ".ini_get($Qe)."</b>");}function
sid(){static$I;if($I===null)$I=(SID&&!($_COOKIE&&ini_bool("session.use_cookies")));return$I;}function
set_password($rl,$N,$V,$E){$_SESSION["pwds"][$rl][$N][$V]=($_COOKIE["adminer_key"]&&is_string($E)?array(encrypt_string($E,$_COOKIE["adminer_key"])):$E);}function
get_password(){$I=get_session("pwds");if(is_array($I))$I=($_COOKIE["adminer_key"]?decrypt_string($I[0],$_COOKIE["adminer_key"]):false);return$I;}function
get_val($G,$m=0,$Ab=null){$Ab=connection($Ab);$H=$Ab->query($G);if(!is_object($H))return
false;$J=$H->fetch_row();return($J?$J[$m]:false);}function
get_vals($G,$d=0){$I=array();$H=connection()->query($G);if(is_object($H)){while($J=$H->fetch_row())$I[]=$J[$d];}return$I;}function
get_key_vals($G,$g=null,$qj=true){$g=connection($g);$I=array();$H=$g->query($G);if(is_object($H)){while($J=$H->fetch_row()){if($qj)$I[$J[0]]=$J[1];else$I[]=$J[0];}}return$I;}function
get_rows($G,$g=null,$l="<p class='error'>"){$Ab=connection($g);$I=array();$H=$Ab->query($G);if(is_object($H)){while($J=$H->fetch_assoc())$I[]=$J;}elseif(!$H&&!$g&&$l&&(defined('Adminer\PAGE_HEADER')||$l=="-- "))echo$l.error()."\n";return$I;}function
unique_array($J,array$w){foreach($w
as$v){if(preg_match("~^(PRIMARY|UNIQUE)$~",$v["type"])&&!$v["partial"]){$I=array();foreach($v["columns"]as$x){if(!isset($J[$x]))continue
2;$I[$x]=$J[$x];}return$I;}}}function
escape_key($x){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$x,$B))return$B[1].idf_escape(idf_unescape($B[2])).$B[3];return
idf_escape($x);}function
where(array$Z,array$n=array()){$I=array();foreach((array)$Z["where"]as$x=>$X){$x=bracket_escape($x,true);$d=escape_key($x);$m=idx($n,$x,array());$sd=$m["type"];$af=$m&&(is_blob($m)||preg_match('~binary~',$sd));$I[]=$d.($af&&!is_utf8($X)?" = ".driver()->quoteBinary($X):(JUSH=="sql"&&$sd=="json"?" = CAST(".q($X)." AS JSON)":(JUSH=="pgsql"&&preg_match('~^jsonb?$~',$m["full_type"])?"::jsonb = ".q($X)."::jsonb":(JUSH=="sql"&&is_numeric($X)&&preg_match('~\.~',$X)?" LIKE ".q($X):(JUSH=="mssql"&&strpos($sd,"datetime")===false?" LIKE ".q(preg_replace('~[_%[]~','[\0]',$X)):" = ".unconvert_field($m,q($X)))))));if(JUSH=="sql"&&preg_match('~char|text~',$sd)&&preg_match("~[^ -@]~",$X))$I[]="$d = ".q($X)." COLLATE ".charset(connection())."_bin";}foreach((array)$Z["null"]as$x)$I[]=escape_key($x)." IS NULL";return
implode(" AND ",$I);}function
where_columns(array$n){$I=array();foreach((array)$_GET["null"]as$x)$I[$x]=true;foreach((array)$_GET["where"]as$x=>$X){$x=bracket_escape($x,true);foreach($n
as$C=>$m){if($x==$C||strpos($x,idf_escape($C))!==false)$I[$C]=true;}}return$I;}function
where_check($X,array$n=array()){parse_str($X,$cb);remove_slashes(array(&$cb));return
where($cb,$n);}function
where_link($s,$d,$Y,$fh="="){$ch=($Y!==null?$fh:"IS NULL");return"&where[$s][col]=".url_escape($d).($ch!=first(adminer()->operators())?"&where[$s][op]=".url_escape($ch):"")."&where[$s][val]=".url_escape($Y);}function
convert_fields(array$e,array$n,array$M=array()){$I="";foreach($e
as$x=>$X){if($M&&!in_array(idf_escape($x),$M))continue;$Aa=convert_field($n[$x]);if($Aa)$I
.=", $Aa AS ".idf_escape($x);}return$I;}function
cookie_path(){return
strtr(preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),array(";"=>"%3B",","=>"%2C"));}function
cookie($C,$Y,$Df=2592000){header("Set-Cookie: $C=".rawurlencode($Y).($Df?"; expires=".gmdate("D, d M Y H:i:s",time()+$Df)." GMT":"")."; path=".cookie_path().(HTTPS?"; secure":"").($C=="adminer_import"?"":"; HttpOnly")."; SameSite=lax",false);}function
get_url($bl,$Hb){$http_response_header=null;$Vc=array();set_error_handler(function($Uc,$l)use(&$Vc){$Vc[]=preg_replace('~^file_get_contents\([^)]*\):\s*~','',$l);return
true;});$I=file_get_contents($bl,false,$Hb);restore_error_handler();$le=(function_exists('http_get_last_response_headers')?http_get_last_response_headers():$http_response_header);return
array($I,(preg_match('~^HTTP/[\d.]+ (\d+)~',idx($le,0,''),$B)?$B[1]:''),(array)$le,($I===false?implode("\n",$Vc):''),);}function
get_settings($Kb){parse_str($_COOKIE[$Kb],$rj);return$rj;}function
get_setting($x,$Kb="adminer_settings",$k=null){return
idx(get_settings($Kb),$x,$k);}function
save_settings(array$rj,$Kb="adminer_settings"){$Y=http_build_query($rj+get_settings($Kb));cookie($Kb,$Y);$_COOKIE[$Kb]=$Y;}function
restart_session(){if(!ini_bool("session.use_cookies")&&(!function_exists('session_status')||session_status()==PHP_SESSION_NONE))session_start();}function
stop_session($Gd=false){$el=ini_bool("session.use_cookies");if(!$el||$Gd){session_write_close();if($el&&ini_set("session.use_cookies",'0')===false)session_start();}}function&get_session($x){return$_SESSION[$x][DRIVER][SERVER][$_GET["username"]];}function
set_session($x,$X){$_SESSION[$x][DRIVER][SERVER][$_GET["username"]]=$X;}function
auth_url($rl,$N,$V,$j=null){$al=remove_from_uri(implode("|",array_keys(SqlDriver::$drivers))."|username|ext|".($j!==null?"db|":"").($rl=='mssql'||$rl=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$al,$B);return"$B[1]?".(sid()?SID."&":"").($_GET["ext"]?"ext=".url_escape($_GET["ext"])."&":"").($rl!="server"||$N!=""?url_escape($rl)."=".url_escape($N)."&":"")."username=".url_escape($V).($j!=""?"&db=".url_escape($j):"").($B[2]?"&$B[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($A,$fg=null){if($fg!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($A!==null?$A:$_SERVER["REQUEST_URI"]))][]=$fg;}if($A!==null){if($A=="")$A=".";header("Location: $A");exit;}}function
query_redirect($G,$A,$fg,$xi=true,$cd=true,$md=false,$ok=""){if($cd){$Jj=microtime(true);$md=!connection()->query($G);$ok=format_time($Jj);}$Cj=($G?adminer()->messageQuery($G,$ok,$md):"");if($md){adminer()->error
.=error().$Cj.script("messagesPrint();")."<br>";return
false;}if($xi)redirect($A,$fg.$Cj);return
true;}class
Queries{static$queries=array();static$start=0;}function
queries($G){if(!Queries::$start)Queries::$start=microtime(true);Queries::$queries[]=(driver()->delimiter!=';'?$G:(preg_match('~;$~',$G)?"DELIMITER ;;\n$G;\nDELIMITER ":$G).";");return
connection()->query($G);}function
apply_queries($G,array$T,$Xc='Adminer\table'){foreach($T
as$R){if(!queries("$G ".$Xc($R)))return
false;}return
true;}function
queries_redirect($A,$fg,$xi){$ri=implode("\n",Queries::$queries);$ok=format_time(Queries::$start);return
query_redirect($ri,$A,$fg,$xi,false,!$xi,$ok);}function
format_time($Jj){return
sprintf('%.3f s',max(0,microtime(true)-$Jj));}function
relative_uri(){return
preg_replace_callback('~^[^?]*~',function($B){return
str_replace(":","%3A",$B[0]);},preg_replace('~^[^?]*/([^?]*)~','\1',$_SERVER["REQUEST_URI"]));}function
remove_from_uri($Dh=""){return
substr(preg_replace("~(?<=[?&])($Dh".(SID?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_files($x,$dc=false){$ud=$_FILES[$x];if(!$ud)return
null;foreach($ud
as$x=>$X)$ud[$x]=(array)$X;$I=array();foreach($ud["error"]as$x=>$l){if($l)return$l;$C=$ud["name"][$x];$wk=$ud["tmp_name"][$x];$Fb=file_get_contents($dc&&preg_match('~\.gz$~',$C)?"compress.zlib://$wk":$wk);if($dc){$Jj=substr($Fb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$Jj))$Fb=iconv("utf-16","utf-8",$Fb);elseif($Jj=="\xEF\xBB\xBF")$Fb=substr($Fb,3);}$I[]=array($C,$Fb);}return$I;}function
get_file($x,$dc=false,$jc=""){$xd=get_files($x,$dc);if(!is_array($xd))return$xd;$I='';foreach($xd
as$ud){$Fb=$ud[1];$I
.=$Fb;if($jc)$I
.=(preg_match("($jc\\s*\$)",$Fb)?"":$jc)."\n\n";}return$I;}function
upload_error($l){$Yf=($l==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($l?'Unable to upload a file.'.($Yf?" ".sprintf('Maximum allowed file size is %sB.',$Yf):""):'File does not exist.');}function
repeat_pattern($Rh,$y){return
str_repeat("$Rh{0,65535}",$y/65535)."$Rh{0,".($y%65535)."}";}function
is_utf8($X){return(preg_match('~~u',$X)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$X));}function
format_number($X){return
strtr(number_format($X,0,".",','),preg_split('~~u','0123456789',-1,PREG_SPLIT_NO_EMPTY));}function
format_status(array$S,$x){$X=idx($S,$x,'?');if(!is_numeric($X))return
h($X);if($X<0)return'?';$xa=($x=="Rows"&&(JUSH=="sqlite"||$S["Engine"]==(JUSH=="pgsql"?"table":"InnoDB")));return($xa?"~ ":"").format_number($X);}function
friendly_url($X){return
preg_replace('~\W~i','-',$X);}function
table_status1($R,$nd=false){$I=table_status($R,$nd);return($I?reset($I):array("Name"=>$R));}function
column_foreign_keys($R){$I=array();foreach(adminer()->foreignKeys($R)as$p){foreach($p["source"]as$X)$I[$X][]=$p;}return$I;}function
fields_from_edit(){$I=array();foreach((array)$_POST["field_keys"]as$x=>$X){if($X!=""){$X=bracket_escape($X);$_POST["function"][$X]=$_POST["field_funs"][$x];$_POST["fields"][$X]=$_POST["field_vals"][$x];}}foreach((array)$_POST["fields"]as$x=>$X){$C=bracket_escape($x,true);$I[$C]=array("field"=>$C,"full_type"=>"","type"=>"","privileges"=>array("insert"=>1,"update"=>1,"where"=>1,"order"=>1),"null"=>true,"auto_increment"=>($C==driver()->primary),);}return$I;}function
dump_headers($we,$tg=false){$I=adminer()->dumpHeaders($we,$tg);$zh=$_POST["output"];if($zh!="text"||$I=="tar"){$xb=($zh!="text"&&$zh!="file"&&preg_match('~^[0-9a-z]+$~',$zh)?".$zh":"");header("Content-Disposition: attachment; filename=".adminer()->dumpFilename($we).".$I$xb");}session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$I;}function
dump_csv(array$J){$Lk=$_POST["format"]=="tsv";foreach($J
as$x=>$X){if(preg_match('~["\n]|^0[^.]|\.\d*0$|'.($Lk?'\t':'[,;]|^$').'~',$X))$J[$x]='"'.str_replace('"','""',$X).'"';}echo
implode(($_POST["format"]=="csv"?",":($Lk?"\t":";")),$J)."\r\n";}function
parse_csv($Sb,$gj){$I=array();preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$Sb,$Of);foreach($Of[0]as$J){preg_match_all("~((?>\"[^\"]*\")+|[^$gj]*)$gj~",$J.$gj,$Pf);$I[]=$Pf[1];}return$I;}function
csv_value($X){return(preg_match('~^".*"$~s',$X)?str_replace('""','"',substr($X,1,-1)):$X);}function
apply_sql_function($r,$d){return($r?($r=="unixepoch"?"DATETIME($d, '$r')":($r=="count distinct"?"COUNT(DISTINCT ":strtoupper("$r("))."$d)"):$d);}function
get_temp_dir(){return
ini_get("upload_tmp_dir")?:sys_get_temp_dir();}function
file_open_lock($o){if(is_link($o))return;$q=@fopen($o,"c+");if(!$q)return;@chmod($o,0660);if(!flock($q,LOCK_EX)){fclose($q);return;}return$q;}function
file_write_unlock($q,$Wb){rewind($q);fwrite($q,$Wb);ftruncate($q,strlen($Wb));file_unlock($q);}function
file_unlock($q){flock($q,LOCK_UN);fclose($q);}function
first(array$_a){return
reset($_a);}function
password_file($h){$o=get_temp_dir()."/adminer.key";if(!$h&&!file_exists($o))return'';$q=file_open_lock($o);if(!$q)return'';$I=stream_get_contents($q);if(!$I){$I=rand_string();file_write_unlock($q,$I);}else
file_unlock($q);return$I;}function
rand_string(){return(function_exists('random_bytes')?bin2hex(random_bytes(16)):md5(uniqid(strval(mt_rand()),true)));}function
select_value($X,$_,array$m,$nk){if(is_array($X)){$I="";if(array_filter($X,'is_array')==array_values($X)){$mf=array();foreach($X
as$W)$mf+=array_fill_keys(array_keys($W),null);foreach(array_keys($mf)as$kf)$I
.="<th>".h($kf);foreach($X
as$W){$I
.="<tr>";foreach(array_merge($mf,$W)as$ll)$I
.="<td>".select_value($ll,$_,$m,$nk);}}else{foreach($X
as$kf=>$W)$I
.="<tr>".($X!=array_values($X)?"<th>".h($kf):"")."<td>".select_value($W,$_,$m,$nk);}return"<table>$I</table>";}if(!$_)$_=adminer()->selectLink($X,$m);if($_===null){if(is_mail($X))$_="mailto:$X";if(is_url($X))$_=$X;}$X=driver()->value($X,$m);$I=adminer()->editVal($X,$m);if($I!==null){if(!is_utf8($I))$I="\0";elseif($nk!=""&&is_shortable($m))$I=shorten_utf8($I,max(0,+$nk));else$I=h($I);}return
adminer()->selectVal($I,$_,$m,$X);}function
is_blob(array$m){return
preg_match('~blob|bytea|raw|file'.(JUSH=="mssql"?'|binary|image':'').'~',$m["type"])&&!in_array($m["type"],idx(driver()->structuredTypes(),'User types',array()));}function
is_mail($Lc){$Ca='[-a-z0-9!#$%&\'*+/=?^_`{|}~]';$zc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';$Rh="$Ca+(\\.$Ca+)*@($zc?\\.)+$zc";return
is_string($Lc)&&preg_match("(^$Rh(,\\s*$Rh)*\$)i",$Lc);}function
is_url($Q){$zc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';return
preg_match("~^((https?):)?//($zc?\\.)+$zc(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i",$Q);}function
is_shortable(array$m){return!preg_match('~'.number_type().'|date|time|year~',$m["type"]);}function
host_port($N){return(preg_match('~^(:([^:].*)|(\[(.+)\]|(([^:]+://)?[^:]+))(:(\d+))?)$~',$N,$B)?array($B[4].$B[5],$B[2].$B[8]):array($N,''));}function
count_rows($R,array$Z,$bf,array$Xd){$G=" FROM ".table($R).($Z?" WHERE ".implode(" AND ",$Z):"");return($bf&&(JUSH=="sql"||count($Xd)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$Xd).")$G":"SELECT COUNT(*)".($bf?" FROM (SELECT 1$G GROUP BY ".implode(", ",$Xd).") x":$G));}function
slow_query($G){$j=adminer()->database();$pk=adminer()->queryTimeout();$wj=driver()->slowQuery($G,$pk);$g=null;if(!$wj&&support("kill")){$g=connect();if($g&&($j==""||$g->select_db($j))){$nf=get_val(connection_id(),0,$g);echo
script("const timeout = setTimeout(() => { ajax('".js_escape(ME)."script=kill', function () {}, 'kill=$nf&token=".get_token()."'); }, 1000 * $pk);");}}ob_flush();flush();$I=@get_key_vals(($wj?:$G),$g,false);if($g){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$I;}function
get_token(){$ui=rand(1,1e6);return($ui^$_SESSION["token"]).":$ui";}function
verify_token(){list($xk,$ui)=explode(":",$_POST["token"]);return($ui^$_SESSION["token"])==$xk&&in_array($_SERVER["HTTP_SEC_FETCH_SITE"],array("","same-origin"));}function
compress_alphabet(){return
strtr(implode(range('"','~')),"'\\","!\n");}function
decompress_string($Q,$pc=""){$va=array_flip(str_split(compress_alphabet()));$y=strlen($Q);$ol=($y?13*($y-1)/2-$va[$Q[0]]:0);$Oa="";$Ji=0;$Ki=0;for($s=1;$s<$y;$s+=2){$Ji=($Ji<<13)+$va[$Q[$s]]*93+$va[$Q[$s+1]];$Ki+=13;while($Ki>=8&&$ol>=8){$Ki-=8;$ol-=8;$Oa
.=chr($Ji>>$Ki);$Ji&=(1<<$Ki)-1;}}if($Oa=="")return"";if($pc!=""&&function_exists('inflate_init'))return
inflate_add(inflate_init(ZLIB_ENCODING_RAW,array('dictionary'=>$pc)),$Oa,ZLIB_FINISH);return($pc==""&&function_exists('gzinflate')?gzinflate($Oa):inflate($Oa,$pc));}function
inflate($Oa,$pc=""){$Af=array(3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258);$Bf=array(0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0);$tc=array(1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577);$vc=array(0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13);$I=$pc;$F=0;do{$zd=inflate_bits($Oa,$F,1);$U=inflate_bits($Oa,$F,2);if(!$U){$F=($F+7)&~7;$y=inflate_bits($Oa,$F,16);$F+=16;$I
.=substr($Oa,$F>>3,$y);$F+=$y<<3;}else{if($U==1){$Hf=array_merge(array_fill(0,144,8),array_fill(0,112,9),array_fill(0,24,7),array_fill(0,8,8));$wc=array_fill(0,30,5);}else{$Gf=inflate_bits($Oa,$F,5)+257;$uc=inflate_bits($Oa,$F,5)+1;$lh=array(16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15);$lg=array_fill(0,19,0);$kg=inflate_bits($Oa,$F,4)+4;for($s=0;$s<$kg;$s++)$lg[$lh[$s]]=inflate_bits($Oa,$F,3);$mg=inflate_table($lg);$Cf=array();while(count($Cf)<$Gf+$uc){$Tj=inflate_symbol($Oa,$F,$mg);if($Tj==16)$Cf=array_merge($Cf,array_fill(0,inflate_bits($Oa,$F,2)+3,end($Cf)));elseif($Tj==17)$Cf=array_merge($Cf,array_fill(0,inflate_bits($Oa,$F,3)+3,0));elseif($Tj==18)$Cf=array_merge($Cf,array_fill(0,inflate_bits($Oa,$F,7)+11,0));else$Cf[]=$Tj;}$Hf=array_slice($Cf,0,$Gf);$wc=array_slice($Cf,$Gf);}$If=inflate_table($Hf);$yc=inflate_table($wc);while(($Tj=inflate_symbol($Oa,$F,$If))!=256){if($Tj<256)$I
.=chr($Tj);else{$y=$Af[$Tj-257]+inflate_bits($Oa,$F,$Bf[$Tj-257]);$xc=inflate_symbol($Oa,$F,$yc);$Rg=strlen($I)-$tc[$xc]-inflate_bits($Oa,$F,$vc[$xc]);for($s=0;$s<$y;$s++)$I
.=$I[$Rg+$s];}}}}while(!$zd);return($pc==""?$I:substr($I,strlen($pc)));}function
inflate_bits($Oa,&$F,$Mb){$I=0;for($s=0;$s<$Mb;$s++){$I+=((ord($Oa[$F>>3])>>($F&7))&1)<<$s;$F++;}return$I;}function
inflate_table(array$Cf){$R=array();$mb=0;for($Pa=1;$Pa<=max($Cf);$Pa++){foreach($Cf
as$Tj=>$y){if($y==$Pa){$R[$Pa][$mb]=$Tj;$mb++;}}$mb<<=1;}return$R;}function
inflate_symbol($Oa,&$F,array$R){$mb=0;$Pa=0;do{$mb=($mb<<1)+inflate_bits($Oa,$F,1);$Pa++;}while(!isset($R[$Pa][$mb]));return$R[$Pa][$mb];}function
script($_j,$_k="\n"){return"<script".nonce().">$_j</script>$_k";}function
script_src($bl,$gc=false){return"<script src='".h($bl)."'".nonce().($gc?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
on($Yc,$de,$ya=null){$za=array();foreach(array_slice(func_get_args(),2)as$X)$za[]=json_encode($X,256);return" data-on$Yc='".str_replace(array('&','<',"'"),array('&amp;','&lt;','&#039;'),"$de(".implode(", ",$za).")")."'";}function
input_hidden($C,$Y=""){return"<input type='hidden' name='".h($C)."' value='".h($Y)."'>\n";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($Q){return
str_replace(array('&','<','"',"'","\0"),array('&amp;','&lt;','&quot;','&#039;','&#0;'),$Q);}function
nl_br($Q){return
str_replace("\n","<br>",$Q);}function
checkbox($C,$Y,$fb,$rf="",$c="",$kb="",$tf=""){$I="<input type='checkbox' name='$C' value='".h($Y)."'".($fb?" checked":"").($rf==""&&$kb?" class='$kb'":"").($tf?" aria-labelledby='$tf'":"").$c.">";return($rf!=""?"<label".($kb?" class='$kb'":"").">$I".h($rf)."</label>":$I);}function
optionlist($jh,$dj=null,$fl=false){$I="";foreach($jh
as$kf=>$W){$kh=array($kf=>$W);if(is_array($W)){$I
.='<optgroup label="'.h($kf).'">';$kh=$W;}foreach($kh
as$x=>$X)$I
.='<option'.($fl||is_string($x)?' value="'.h($x).'"':'').($dj!==null&&($fl||is_string($x)?(string)$x:$X)===$dj?' selected':'').'>'.h($X);if(is_array($W))$I
.='</optgroup>';}return$I;}function
html_select($C,array$jh,$Y="",$c="",$tf=""){static$rf=0;$sf="";if(!$tf&&substr($jh[""],0,1)=="("){$rf++;$tf="label-$rf";$sf="<option value='' id='$tf'>".h($jh[""]);unset($jh[""]);}return"<select name='".h($C)."'".($tf?" aria-labelledby='$tf'":"")."$c>".$sf.optionlist($jh,$Y)."</select>";}function
html_radios($C,array$jh,$Y="",$gj=""){$I="";foreach($jh
as$x=>$X)$I
.="<label><input type='radio' name='".h($C)."' value='".h($x)."'".($x==$Y?" checked":"").">".h($X)."</label>$gj";return$I;}function
confirm($fg=""){return
on('click','confirmClick',$fg?:'Are you sure?');}function
print_fieldset($t,$_f,$vl=false){echo"<fieldset><legend>","<a href='#fieldset-$t' class='toggle'>$_f</a>","</legend>","<div id='fieldset-$t'".($vl?"":" class='hidden'").">\n";}function
bold($Ra,$kb=""){return($Ra?" class='active $kb'":($kb?" class='$kb'":""));}function
js_escape($Q){return
str_replace("<","\\x3C",addcslashes($Q,"\r\n'\\"));}function
js_escape_re($Q){return
addcslashes(preg_quote($Q,"/"),"\r\n");}function
pagination_href($D){return
remove_from_uri("page|next").($D?"&page=$D".($_GET["next"]!=""?"&next=".url_escape($_GET["next"]):""):"");}function
pagination($D,$Tb){return" ".($D==$Tb?($D?"<b>".($D+1)."</b>":$D+1):'<a href="'.h(pagination_href($D)).'">'.($D+1)."</a>");}function
hidden_fields(array$oi,array$_e=array(),$fi=''){$I=false;foreach($oi
as$x=>$X){if(!in_array($x,$_e)){if(is_array($X))hidden_fields($X,array(),$x);else{$I=true;echo
input_hidden(($fi?$fi."[$x]":$x),$X);}}}return$I;}function
hidden_fields_get(){echo(sid()?input_hidden(session_name(),session_id()):''),($_GET["ext"]?input_hidden("ext",$_GET["ext"]):""),(isset($_GET[DRIVER])?input_hidden(DRIVER,SERVER):""),input_hidden("username",$_GET["username"]);}function
file_input($c,$Ji=""){$Sf="max_file_uploads";$Tf=ini_get($Sf);$Yf="upload_max_filesize";$Zf=ini_bytes($Yf);$ci=ini_bytes("post_max_size");if($ci&&$ci<$Zf){$Yf="post_max_size";$Zf=$ci;}$ag=ini_get($Yf);return(ini_bool("file_uploads")?"<input type='file'$c".on('change','fileChange',(int)$Tf,sprintf('Increase %s.',"$Sf = $Tf"),$Zf,sprintf('Increase %s.',"$Yf = $ag")).">$Ji":'File uploads are disabled.');}function
enum_input($U,$c,array$m,$Y,$Oc=""){preg_match_all("~'((?:[^']|'')*)'~",$m["length"],$Of);$fi=($m["type"]=="enum"?"val-":"");$fb=(is_array($Y)?in_array("null",$Y):$Y===null);$I=($m["null"]&&$fi?"<label><input type='$U'$c value='null'".($fb?" checked":"")."><i>$Oc</i></label>":"");foreach($Of[1]as$X){$X=stripcslashes(str_replace("''","'",$X));$fb=(is_array($Y)?in_array($fi.$X,$Y):$Y===$X);$I
.=" <label><input type='$U'$c value='".h($fi.$X)."'".($fb?' checked':'').'>'.h(adminer()->editVal($X,$m)).'</label>';}return$I;}function
input(array$m,$Y,$r,$Ga=false,$Yk=false){$C=h(bracket_escape($m["field"]));echo"<td class='function'>";if(is_array($Y)&&!$r)$r="json";$hf=($r=="json"||preg_match('~^jsonb?$~',$m["full_type"]));if($hf&&$Y!=''&&(JUSH!="pgsql"||$m["type"]!="json"))$Y=json_encode(is_array($Y)?$Y:json_decode($Y),128|64|256);$Ii=(JUSH=="mssql"&&$Yk&&$m["auto_increment"]);if($Ii&&!$_POST["save"])$r=null;$Sd=(isset($_GET["select"])||$Ii?array("orig"=>'original'):array())+adminer()->editFunctions($m);$Tc=driver()->enumLength($m);if($Tc){$m["type"]="enum";$m["length"]=$Tc;}$c=" name='fields[$C]".($m["type"]=="enum"||$m["type"]=="set"?"[]":"")."'".($Ga?" autofocus":"");echo
driver()->unconvertFunction($m)." ";$R=$_GET["edit"]?:$_GET["select"];if($m["type"]=="enum")echo
h($Sd[""])."<td>".adminer()->editInput($R,$m,$c,$Y);else{$fe=(in_array($r,$Sd)||isset($Sd[$r]));$_d=0;foreach($Sd
as$x=>$X){if($x===""||!$X)break;$_d++;}echo(count($Sd)>1?"<select name='function[$C]'".on('change','functionChange').on_help_value('^SQL$').">".optionlist($Sd,$r===null||$fe?$r:"")."</select>":h(reset($Sd)))."<td".($_d&&count($Sd)>1?on('input','skipOriginal',$_d):"").">";$Se=adminer()->editInput($R,$m,$c,$Y);if($Se!="")echo$Se;elseif(preg_match('~bool~',$m["type"]))echo"<input type='hidden'$c value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?" checked":"")."$c value='1'>";elseif($m["type"]=="set")echo
enum_input("checkbox",$c,$m,(is_string($Y)?explode(",",$Y):$Y));elseif(is_blob($m)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$C'>";elseif($hf)echo"<textarea$c cols='50' rows='12' class='jush-json'>".h($Y).'</textarea>';elseif(($lk=preg_match('~text|lob|memo~i',$m["type"]))||preg_match("~\n~",$Y)){if($lk&&JUSH!="sqlite")$c
.=" cols='50' rows='12'";else{$K=min(12,substr_count($Y,"\n")+1);$c
.=" cols='30' rows='$K'";}echo"<textarea$c>".h($Y).'</textarea>';}else{$Ok=driver()->types();$bg=(!preg_match('~int~',$m["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$m["length"],$B)?((preg_match("~binary~",$m["type"])?2:1)*$B[1]+($B[3]?1:0)+($B[2]&&!$m["unsigned"]?1:0)):($Ok[$m["type"]]?$Ok[$m["type"]]+($m["unsigned"]?0:1):0));if(JUSH=='sql'&&min_version(5.6)&&preg_match('~time~',$m["type"]))$bg+=7;echo"<input".((!$fe||$r==="")&&preg_match('~^'.int_type().'$~',$m["type"])&&!preg_match('~\[]~',$m["full_type"])?" type='number'":"")." value='".h($Y)."'".($bg?" data-maxlength='$bg'":"").(preg_match('~char|binary~',$m["type"])&&$bg>20?" size='".($bg>99?60:40)."'":"")."$c>";}echo
adminer()->editHint($R,$m,$Y),(count($Sd)>1?script("fire(qs('select', qsl('td').previousSibling), 'change');",""):"");}}function
process_input(array$m){$u=bracket_escape($m["field"]);$r=idx($_POST["function"],$u);if($r=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?idf_escape($m["field"]):false);if($r=="NULL")return"NULL";if(is_blob($m)&&ini_bool("file_uploads")){$ud=get_file("fields-$u");if(!is_string($ud))return
false;return
driver()->quoteBinary($ud);}$Y=idx($_POST["fields"],$u);if($Y===null)return
false;if($m["type"]=="enum"||driver()->enumLength($m)){$Y=idx($Y,0);if($Y=="orig"||!$Y)return
false;if($Y=="null")return"NULL";$Y=substr($Y,4);}if($m["auto_increment"]&&$Y=="")return
null;if($m["type"]=="set")$Y=implode(",",(array)$Y);if($r=="json"){$Y=json_decode($Y,true);if(!is_array($Y))return
false;return$Y;}return
adminer()->processInput($m,$Y,$r);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$fj="<ul>\n";foreach(table_status('',true)as$R=>$S){$C=adminer()->tableName($S);if(isset($S["Engine"])&&$C!=""&&(!$_POST["tables"]||in_array($R,$_POST["tables"]))){$H=connection()->query("SELECT".limit("1 FROM ".table($R)," WHERE ".implode(" AND ",adminer()->selectSearchProcess(fields($R),array())),1));if(!$H||$H->fetch_row()){$ki="<a href='".h(ME."select=".url_escape($R)."&where[0][op]=".url_escape($_GET["where"][0]["op"])."&where[0][val]=".url_escape($_GET["where"][0]["val"]))."'>$C</a>";echo"$fj<li>".($H?$ki:"<p class='error'>$ki: ".error())."\n";$fj="";}}}echo($fj?"<p class='message'>".'No tables.':"</ul>")."\n";}function
on_help($lk,$tj=0){return
on('mouseover','helpMouseover',$lk,$tj).on('mouseout','helpMouseout');}function
on_help_value($Di="",$Hi=""){return
on('mouseover','helpValueMouseover',$Di,$Hi).on('mouseout','helpMouseout');}function
edit_form($R,array$n,$J,$Yk,$l=''){$Yj=adminer()->tableName(table_status1($R,true));page_header(($Yk?'Edit':'Insert'),$l,array("select"=>array($R,$Yj)),$Yj);adminer()->editRowPrint($R,$n,$J,$Yk);if($J===false){echo"<p class='error'>".'No rows.'."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";$Jc=false;$Al=($Yk&&!isset($_GET["select"])?where_columns($n):array());$Ib=(count($Al)!=count($n));if(!$Ib)$Al=array();if(!$n)echo"<p class='error'>".'You have no privileges to update this table.'."\n";else{echo"<table class='layout nowrap'".on('keydown','editingKeydown').">\n";$Ga=!$_POST;foreach($n
as$C=>$m){echo"<tr".($Al[$C]?on('change','whereChange'):"")."><th>".adminer()->fieldName($m);$k=idx($_GET["set"],bracket_escape($C));if($k===null){$k=$m["default"];if($m["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$k,$Ei))$k=$Ei[1];if(JUSH=="sql"&&preg_match('~binary~',$m["type"]))$k=bin2hex($k);}$Y=($J!==null?($J[$C]!=""&&JUSH=="sql"&&preg_match("~enum|set~",$m["type"])&&is_array($J[$C])?implode(",",$J[$C]):(is_bool($J[$C])?+$J[$C]:$J[$C])):(!$Yk&&$m["auto_increment"]?"":(isset($_GET["select"])?false:$k)));if(!$_POST["save"]&&is_string($Y))$Y=adminer()->editVal($Y,$m);if(($Yk&&!isset($m["privileges"]["update"]))||$m["generated"])echo"<td class='function'><td>".select_value($Y,'',$m,null);else{$Jc=true;$r=($_POST["save"]?idx($_POST["function"],bracket_escape($C),""):($Yk&&preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(!$_POST&&!$Yk&&$Y==$m["default"]&&preg_match('~^[\w.]+\(~',$Y))$r="SQL";if(preg_match("~time~",$m["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$Y)){$Y="";$r="now";}if($m["type"]=="uuid"&&$Y=="uuid()"){$Y="";$r="uuid";}if($Ga!==false)$Ga=($m["auto_increment"]||$r=="now"||$r=="uuid"?null:true);input($m,$Y,$r,$Ga,$Yk);if($Ga)$Ga=false;}}if(!fields($R)&&driver()->primary!="")echo"<tr>"."<th><input name='field_keys[]'".on('input','fieldChange').">"."<td class='function'>".html_select("field_funs[]",adminer()->editFunctions(array("null"=>isset($_GET["select"]))))."<td><input name='field_vals[]'>";echo"</table>\n";}echo"<p>\n";if($Jc){echo"<input type='submit' value='".'Save'."'>\n";if(!isset($_GET["select"])&&$Ib){$qc=($Al&&($l!=""||adminer()->error!="")?" disabled":"");echo"<input type='submit' name='insert' value='".($Yk?'Save and continue edit':'Save and insert next')."' title='Ctrl+Shift+Enter'$qc".($Yk?on('click','ajaxForm','Saving…'):"").">\n";}}echo($Yk?"<input type='submit' name='delete' value='".'Delete'."'".confirm().">\n":"");if(isset($_GET["select"]))hidden_fields(array("check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]));echo
input_hidden("referer",(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"])),input_hidden("save",1),input_token(),"</form>\n";}function
shorten_utf8($Q,$y=80,$Pj=""){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$y).")($)?)u",$Q,$B))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$y).")($)?)",$Q,$B);return
h($B[1]).$Pj.(isset($B[2])?"":"<i>…</i>");}function
icon($ve,$C,$ue,$rk,$c=""){return"<button ".($C?"type='submit' name='$C'":"draggable='true' tabindex='-1'")." title='".h($rk)."' class='icon icon-$ve".($C?"":" jsonly")."'$c><span>$ue</span></button>";}function
copy_icon(){$Lb='Copy';return"<a href='' class='jsonly icon-copy' title='$Lb'><span>$Lb</span></a>";}if(isset($_GET["file"])){if(substr(VERSION,-4)!='-dev'){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");}ini_set("zlib.output_compression",'1');if($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
decompress_string('!c0=@iDZ*tV?H*{U)[Q;B/1SR=Dh9&hJv;rrHHN,.V&KGmzhDwb9E:tfItN#CwUSwX?Xyeqi5d/N>]A"1lTaK
Tx^G#)>.UM~&(MUO{shFwKG+g4,>C*S:
f1hRcL)KhkmZFtH^qWCMBf7tZ{.#f{8V6<
#Nk9.jSA&0km
lxTc6$tVXF.+.*cJeW<wG~51NPIP4xT,`5Fw(3!{(~-,9<s}YqWT+L%^[i[s<&8ErH[O8<a)
ljb
$LurL4t]W%a>H/b
X/{EMCz:LXX((.yD>6A0+]t%ACU_
:"Bp%c=`r4T.#6G1(p
xo=TMNIiX,W0G-OEkD}^/L"3iRuM0)KZQ^aWB9dsO%0WmcO<LgliJIDSwKw0uo4(Piokl7g)}Qq_R"C>
^,?D183n.@41e}1M3L@&rCG$;yG3^fAu1qCeb_`V5R)ywQ+^^}Y?,S-#YZFZG
*@I%I_vxm,Tu:<aGT4wdZr#t8h]Nq~_-mA_aP)C2W
3#$o
g`gA/T"apmp;"31><i"",jWq9Wx4|Kj$:Svf`fH`|l`L/=wn!GzOm+(2zYb@S?I6~Dgg51]s$GQ<f%*sZ)4os*u%H<]daIUU7+nOS>!R,?jI-ZyOTT8YA+<ro/FX
5%v%]D1&UG`Rk{"Wc.*PH+X"!Vb@SA=T#6)+N_
VgZ:[vm-?:-d-#LVMbB`M*
o3=!8PG}PV45(W`#.!4Aj#=
`|=e];={gdf>3&l{-kM.$C*+s{3":?S*Zv4|Rl!*UYvBXH@}(A,#om09^h1i;#LHmj2,KUT]s;#mi|*91KjF]nE
u?>^sG`oFK
)Wofomi0<!n"hdYaSs6[44(o8rHBG_@1V2u@D_*jz/#ZgKg<,ob6)a>B~0
Nc9PJ]bx=7K{0`!<w~"{8gg&A2+L#$C,xw#&#5qLhH:Y
6oD1wS)Hu:z&]%$L:*RH&&hm9*p.)J&x-8E0z+soB4Y.o:5!`DtOyw7783CWgj
WZ%`ELhCb!<9!`!t;k@5]}^L$~J|@.agt}?B1>
;"ZGyF-kRn"BIwMi;iFn0;f?!>s@V%wLZZ[kKwyDKfGko5=+|UjHeZjXy;0;#G@L"d`Um3u4Z@)WU.Kf:>6w?u|8l*.uRy`amgR$8Nv?MAbetW1fZC=.a/i!<lm+CgiuJdI)Ig2l@6xS*[@!@B.hXtesj)KZ`"D(QZyUo#,afykRAvt+#nz?,6c9u&`9kdt)X35?[Y<n!"C4r!0$AJl3>+#H(mk1pQn,Z3ZZ]8D)q@wst)_4|I5f}dsW#hqo*!.
4#"_/$:mCAq.5UCVL;oIlfE&U`w!f9m@e?)4t)~-8Kr.@Bm$9-|,R
ult.W=H4(dSM?+2D
gapxO[e_/=:kYP05Q|i+[N_N-YHIAe)0I*>{8]&Z?!aMCh.,oL@6p&lY$/
U=Fds9>*<FAc!>,5<A*;C+!_3@O6|?
//8+>*;@Um0hT[y8<Yt,@dvwiU()maH>967;d_]`={>5EWy&s32*#uINV)k5YG"ekF2}hI1O:Mj?8&AG/j[.-n!P5/("uWRm`3"j5%iI+qc5SJ+:9eOv83%i]U%[V*dHY/2lm8EP@h:*ITM4#//"X9KhV|EJ;q*De_$X_uTkg^D"0(-oU$AjZ^;N#1fw]3U1a`)mkv^ymmdQDDS;q71|/~(_`/BNq++E<jkdNVV@mh?.W_4M<w=_(ybU80Bn/@V^N!54"@
H!U(`":dm[rVdms6(S],j-batnN&O(^ru_<To+HJ~-NHu&
@=6dl$NMZ6.-yJ1jkQMe$lOAr{RGpt_56jj]YcYfGIIoQ"Gp(LKTcE%
(#A-Ss>OBN92I3S^/SZ[Y{fR5U]f3~"At`%-@82:PR%ue
?eN{]g1_DyuG)bqfX_Qs^{78KXKDK$X3a[Ecg5g;AJ4X-!D6[i7{=;"[e<PUxWs#8`
A
"RPK}9NBKH69/U1HuFO6us<2>Oj!}^0)84[gIeYd+TUi:!R?Fdgb1)D%@K@H-J^5m+l!YgY?3xK#mU+#Qm]0g"V)XSM0|uEUQFXQBk%K.*BURB
10iC:gT=p:fn<{1$Zp5Ovs@e;!HiB|gIW:-0v}QHRYkl1<T*o2TX81n2`o1uT)@|7jq%"/7@G)frLDr8H3rf_Na<86aPUN*MUbTee4EdGd"1t=NaEF
;iySU?Y;v.4>
RP2b=1]Ad#4rICDk=x"Z`z?~X!`9<#2>$)ds-gMK0Ux00NP!8ZqDXXp3GXgn;Q/Yh-#qdOQ)S}u]q?W-CFEV(7eTC:uKZPMYS5$i,I
2[0ov7,YH0pH6R`n,nr(8U[C-nTMc4~8NY~#n])3`!4/vklG
N%[#+;)i29O[fww}VbQ]rKi%y-ZW>Gfjs~p(*;U,"!Nb?-U]j|.+n]tcTM&fIT9Txd(Xod^%"{+[N9i2wzya_4MaMv!mcFuYxzK2uWypf-Yk^aYCxviP2qUT6}5(x~cMiq^HyEAUC"wnB}[#@KK)25n!nubey@KY7vpFug_hT>MRR-PyeDx`nbnx+Esrx8K7s3mrcwqM21p|blg?r41.s2Fe>OEZ`k/JpHne&Us&nDhatAWZv[y&$@`;Qn@WmZnmpc6]Y4TVtMp;4DQvyF0k8pZD=5bWwf
&@wXiEVG3L~r9x>f
DJv/ymXPyEB_ctnuw=s~Cw_nhh`{Ej14p4<A."no_E=r?V>
X/mcPhtkK
SHlA[=3)jxBjTN?Fe=K3mXc[JfhI)O+H?s4z>nCQ1zsK7lTpOp/EyNykw~PCe>j:YBo!af6g2s.elsKdH90yhlW|`Ib;pXh[h8+thI)*3^^VMelUX,D7=E%gNYB|!Ui(a%<e,qo(V~,OVWBQiQa{E~JjL.l)Z)s~SM<;1@H4S=U(RF;7uOW[""8dG;qK-cOo"FZYYl"dR%j%1)P33!1_jb2My9WJ$HcX;,>3z#p{Vl.-7WAo(snORM+[dimjqqHs6f=}g$mcji$bJZ[:XSHOb>Xmtx/<NJHc]$bOxnwttNq~T+A~i&.dMyF@w]c8_/6O8XCKwJyFbyBn[4e~Mb[,2oL?
nw.DpU|W%F+RddY/Zd.3$0W!sX9Lx^%b7@mO2x}SPPcu5gdBS;rHC:7^tfOQRKVQD&s)9oM`LZ_q;f]]#43gQkr=Z_L9p.?`P=j.yac@GbLQ^f<GGZ~]6kya;<8:F@hcNG,P!41@F)/:1Qq@&!L?]UN!3MG]s[~OAU`#nEbRUKQ*1(wpi7z-+cg_!Rbg$@^7a,TC#U_Is_vM"7L=&1(@d.AN*2H2hG(a<4JcT4QrvvmyR9>VCK0L;+;0K/BTI7@[lNzAgjG:)Y5`IIGxr!$lWV1aWii,3F!/{`s13+P
WmZ/+Rx>/5kfH^HZw96+`+Kg
d{skqlg_Z&_>xhPZN4XZ]LMvK_DQt{mZ>{EvrNTg9Ym"-?2=4,a]XVx$<gc$#&rc`0c@w"b
iJmT:GD~_
_*u!sl`,twN*82)AXsWx_7Chyn=14#77aNK<%RuEZMtV5bYfhw2RC,-2"(L,y<0T4qAY:ww{Z~<,Y>D}(Bk4n+&c"gcS0j9VkYQ=uJ<^:uQ
et&&B>f3yfuUqMh2DNQD;mk?ce`(?3S-WN4Q1mQBHMO(Gt2+nYFK`tncD)YIlKodjnrl]=O|6G5>V:Ex=5w]75MD501&2,gCqF
k]B*OK&O4=sBXd&q8BwW5hdkg4S1j#0X,E7vh8)NNmp6R]1curHvrKxu59J$S:1L6A;Mx/840/;]@BF)AkbJ~D"3>/"SoFH+&mO[y"EbOnY$evn)/<+-->rA,2QHBqRdJk;W4j#^S7AHal}2Hr|mYOBF`,Xw!J(7+RrSuO[aRC^yfg`Y^qE:o;0d/Y9/CaT?ZW{B^F/S1p:<?qS(|,a_M$6L5UzkJJXb=!>?qKg`yn*Wr@Nf1l/UF^3^*u?/3yJi?`lUmesKe.$4kv5*Ca(`?m[EX0/aO>(xbesGcRlE_Tzf7d<XX`)9G1`d>6VfK@Id
n;HULv1;x]a+n|H5$GpIYWE/2@2bH*b~Euozf/VRryH=4fpDuUa&LMWGpnMtJfxRu!YDf^_3r-=3nMt_v[3Z9s%iTc/<AA
"jF:YJ4c},/bNbT^[Ekpl"UA;J%)h%kQ4b*56aD9FCwnmmTb59/A%AR`60iM=y%Qxs_XZ)XQQ8O8pc7Z4-$?i5EYH$c#?7_-Yslj.FL$Er7
c$p(GPo*@RfuCsbme7[v:$ZGKTl"_M_)Ym0h+QDWXq?4Eoh-R7bn~ga%7@ZBwnyMh7a');}elseif($_GET["file"]=="dark.css"){header("Content-Type: text/css; charset=utf-8");echo
decompress_string('%OsbOb3V?!K0U*,j#-4V$+4lSl,oCh*02mX@fy~Y!-lFD?AZS5iE
nM`YKnnN5@7$,h]yHv0]"r/{_.;5=S+SNKE}<JYs`q%O%%)irj"Ua|G&>l)NqxPHIui")?!f$TF|nwt-nQCaG&Tzq)X$0a:"l<uhiWpN+Q>JUl.I??
0[m@%2{lZZ-SVaY0c(Abuipc;HrUB.?"L
&fe39+`O>CaP%DBGl_a;sKU:Vn{vUd)#z;(-4/lH:f/yqJRLo1D)]&Q)#F_Ex@I.Aoq!%P+x`#:u7a*NRit]e+S_#3_W;B1:p*qj1n&6tLeURFTa*Z%=PigZV?!E,M#fGWI7
Vby;v}uyiyNSk%!K32:q%~)Z7R]f7*[T1VD8GAHNE,gNAjPt3bJTq!),5tH82n<xEH5{06?o3=vyf/"d[Dx=^/`OW(R/VJpy<uN~pK
XY0h>3?PG;:6W2&H^g`XJac/.2vy_[sa[I@2XZ6h^)(qYAo-$5uc0Ep%,GX=n?^Dh<AHDPP6:^cBoLiHv;/&f"x+
Fxs2:m>cC)c>Lo
0T]2{suTY+[`^=g^8K@M"IJhD,eB]&O05-RUzKB;q=jP@t>t?wQJam-T
Ct4iGwsJeBb--L[GY@5KjZDe)KI2"iJ+I
sFktJV_tO_ae<,6L%wV]]G$83G65)NlCxcni0jK(!Hn+6;A/K;bfn9xSp=TVCsf``qH7Mimc,xAY3>O[u4w?fz&Fj
9f,[];aKusLC!8-;hiECD`(]x7[,6WvZQwb}-<xBhai*6z.x#y/,/,PfbzjJZY5<k)c%nD&#@k/fnmY$Bx2daWEELXWrfOnaM:!Fa_[qjXgtfwLcv6,3f~T:>3n3wR;MUKGkB;/1<=rsb.a0udo%x4L7HAUd8(4Q+6[S3/m5?BQNG}h9#D7rZ(C[A#`5XL+tAmR_k4;*wtK-0+ixONPclR9
9Q2)1cdU5,ODCdgYd!N6');}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string('$c4]`nsWl1ptWOv_h:.%y>(B8Jhsf@ooc^S6O.cGC9BHMu=?3)
3[.X?=Wv,ZyTxSc%"*Tj_GrEE9;FU$:J.f=X0d>JYBIVZj]D<aA3aJq*XnQqxcw)y-@0VgkGu?I^TmUVgb:3EfFdr)MNb!A,_(k]_9iV_"_(G,09n1q_nV?v_Ya}yH922HAvE)Q-Dca{7S&0T[#Dtk/#Bl_W&wjz.9y.XC]Yq%Jhb/AiQuuuIe`
H[3+1Q4&&~EB[RStfa@oiB?KrvDpHs&j#r_LJL`Fqt*g"xA[A8
[y~qK:8it:6_]`i&KGku1;72%Z|S"<"lD2ac}UGi{G0+g.v*]W)w>x/UcKbq9g^FuU`Cx2;l0o]3G78`XOn?+m4h40J%^y[,j8/jf`[Aa
ekiT
]!]LVm5+_"kYM9f,H8q4W[Pz%8**gF[*;oMUB}w6uH@/HF4QT,aW)t1GER
6x,RTi^tAv3wj1,rPT[<m[V[
v7w0=&nC3$Jan|l=tdXcXut7wuK[MZEdJ8uZSC^U)zk-h]L>2"Klj]:[sKC>q*P}c9tr,ySzuv&+rPNW!Ml13$YQx$tt6nbASO!]:H[_(b&6Q[7Z6!1nLF,X<wye,d3tfh"JO4b_,akkbL-gTUfs!onu3q,>=ZgGf}1)_HCzB[dxj}I&?d-:=[TFTqbm
jTGDH<FQsW):/7(`sMu5uZF%cu6-t%h-V@
S`nB8s;7eS9Y;P7uI*f%<Sj0e|;fCM1ZmLc-<V^"Aum=T-Xb2$xhvH+nfh%401Vm*95qb&;?hropn>:4HL6I`dBm#J4P+uZc-`3:8IP~y!z"&FTsPyc)eTPqO=MXC+&9GQY90mSIR/pOr,:dR/5J4sBQ:>.K8KeH)L2LK#+4%~avx><sm6t%D,?CM[__KlhK1M^6OLZafEXTfCH!s2s8;6E[yix:0<]pjSEtL^,m=iG%f?UkP|7Z?U:wqHDPQ|&E@>0Xlxb2W@Uz#x*yPK(-S)eaNJ&Bfs,vb8K*yQQ|X}s<m-Q1=FGg1p<:F&ULI@>Q[e`8]J$im:dY#D,Oty]u%`GZ<wZD7Xjt0(^oqGR6X7Z0=W+;g[FPTX7mbv@m_r1ox}ydcD&K2!q8$f3E8nwpiXvn,vJByWtZ*SBob5n7z#
Soub+!!"]Tv[d!h"a``]uxL);"fsAF"W=cgDso{(r,~(hkY9Rh5@}3F25%HS?"~>G!7C
%"uliWhZ[zfhK`&ZgH<Be(t5m]MN&m;9WMsZgD#j2/_(6tud34LhZa+*Y~HlkEP}LYr?I@k?[n=~Id2|"f]`<2x7^AP:DI%9+IOd!WwG+"""F_;a@hkicZ:FW0u@]upbdFl1*38IBbq
?l(~:S0P=gb<GY^t<5Ask!qJ+|d&0FR)XOV5eBb_yI?dmXVklmow;eIhOUyCO.K:RPg9G$j
`7se]e9iOE?w/B(R!V[CX4"("i2i@Cx>R9m+&8+Np3Y?k#3)JB_BEO*X1q?83pC
=aF2cPlq-e&L,
:~g1O(5WtRKTi$P:D:6-@!!7=oO/moc.$>c+]/hvY8Y*6wf_,+.k]84!J"
BcX&_j6iUX8-L;i(_oc
":03Pk]FuUv/1o9Cq+WNKqm74;VqvrBBoxm)D2@G_YjGVRky>9ao0pN;ZyR,*e/,.mchZXRZiND5i
E"R3/XkYDa!("eoNed99?<?rk_#
iVH8:NO({5N"18#lhc)O}F~;jr2kI!-0>Cr+lgIabnSY*oipd7oQ.o"#uh)+>"uIeuQV)jAuy3s5?twVBbXCdsrOQ&Qk(ayr<H3if)vVX.5ITL*^F3nbnPBBZ7ajgZ0$vMz?9s:@+JJbXAVS/fu7V>rRAKR)k_9`2oW"nT[fFAdFb
nfog:`79ePeSYtN0|(@-./s.v%_RmkF/&XLvK0VP1&b"i_<ph?o_-5h&:wB`lcI%,$7Oy?E;gs{en6s7o4mRS_=bi7KSrpZT=0c%ogvtFm#tC9kQ}3NamW]apPHV/#?biJP&JSw5O+a+`a[":"&[oMC2^D^IBZ&eua?IiB`H*N80:Wyg916?Gs"X0T,FX!]EJT(lIu{M0GjVP;/1F^J-Gb:WllMX3/_i/>RR:_*slBC6TlZCpr0k^yrGk6F#zwT
5NyFB@&AFGK&}sIdH)Z.I5E^tgYyVXe]%aac:aY#$Z/NGN21r0Z/vP;@%t/miyd_J^?.,ekJFXQ9V!f(|uaIdBA[cqr"eQ|<kX"OSE?C8AR"{Ory90uHYdR8(eh;_%U!2j.$6*}AVGI(7-d%5NFhUfL!(!^@CVR_h;znlt3qIg7pj0+8u"?S4rxvTN-o*;yT&9$PLZXT^T4=wI#rfRv+K&oTL:%P#_65sg#B&->[aYmIFSO),8W_qxe)qFk,)_M8ktxk"(|Oe/VC"DeCeu:yn!F0n+*&%&?;][YF?*sd<l)vWe*b5W$VZ6eC>Gx4"8+ah4^-J
n25m~hPP:vHJEy^l%c{?+rP&{c@DTcfm}au@$*6T@Kji)pM^*?ChLp6^q4wa1`vW&12T}HDNc_xr$5ngo_&S^7&#~nx<G;(BBeLmq/3>MY70.fj#ABt3.;*7!f&gM8+Ye!_1vc6pO9~pj(0Ek.xM[8*[Q.Z8&J9W|#4j$TWo(^V1Vo+Ktb3i1hP1hLUqcWCr2<r:6d+EK5PJ+f=U^e;D]lt,%Bq8zrf33%E!<_P+lQKg/SD%/wS_BS{x#?c`<*:kL*/5Rr^2}7ePX;w&T5LTYE<XHi!?nN^Xf,:*@iWD$+;0Lkk@IGn)3SUiqQL)b*(?{)q.DY/+j>jQUc>:E?`qx5OsjF,5I!P_W#i8.v~;0-gYJBFn(rg+V9o]mcEU<X#UF%_$fwL4aU5`Oe~bk$gqe)MU9V
$%FQc!`&W{
lr%*{wY@2ExA,V#x"@?XGZ@bf<lgg!/:&)Z+dh.*P5_kufS_7
<9;44lAyM+A,<!ct#Gnv~Vf?_yj]9LtEu[*)^JNY[o`QNRc6>UQldV;L;`;.
v`g$k
$f
$/-Q}N:3K[>uE#5O|Oinj2+0BP!cJ;^7/H,ssnkBd/[R}H&JsK%@n=bD8L@x`W)w."?,340*%8;#)uAq1!;))p_!n<%ni3Uo:7a5E>FT~.NZjZ;!f8=uZW9mU1zDyls0+9/"c0[CJeb#L:5C+Z5+O9J$+t)A48NE0D`lr^T,6^i^}wm98fNkUdg6bFdC,$LmQWoj`J;*XocC=w8*Wi^VupvA,BN8UQX&b*F-:xZ5j])T#PVpG(1.~Q;=S!&NB__=k#`C:2Rg)4;,$"d]-JasM[qu)**cA$9!NdKn7@N3@E-()VwmNiP8bNvNLV+abS)[[s:BWw8de,ZUpftEbm=)x?jk?+PG)^qO!M:b*UC$/_/Z__[=qT!6iq;
|!YW4[vC4
#?nRJG.t3k0n.YWkX*)1zGd+ol<dj:!D<.l,9)E>RxG",FBGUi)OoV(mwG(,?`$0
?g6:2wPTUSR+0aAEUlOaO"&F_yZL<65=WXPI5G*"a(,8PCYXvGiGyyIZH_xCI)5JNg_VeEM0+&n,y}:`.TL<W4(bX%5mb(]ZQ{a2x%E6^K-NJ*p0A@rPuXDt/U167_j8y1[R8I^;db2A%)^W+0:~Eu2fW$olA|c+1R:3KeO/^9`>VH>}sx48fzjE=ya:7)DP91</
IUA+.,S80mb-7^{YS%JPsgCd<s{B81Zf9F"K-C>l,.?C7jWq`,<K$myE[ON5u1<?$y7.P,->,4ma7_B
YOIdlna:sw
MDuC8y64K%VC6y.`a}yDe.KL8{C9oU]KO-U.aC#mBp+NaF*!.hAq&Cm&XB1XbD=;
eL5)C5t!}b72K_VP"l2F/hLXXffHHkGPaQeFFWZOJm#WaGLF;>:WXn2j`RM(GV^A36%YxbYsptKiBM{c4y?xq$E5qqxNb;gor*gE]0+T^IvNc96^0sq1{<:1#x%NR>bO*d{Ao5i<^"gE_ouy-3r8<SbS!/B+Eu=OK^{yJ3>f&lBk>pLM#(="v>WddPU&T4m@oZ{d0_WR"-8_(F+7ITpj33^n"9,]}7(>nUnbLyWdNL$3QPO_/thxF(|!{R2TVoMC!1;Ea*O<X?d0/*E0Yf@^YxfYV%xG91(!qP?>9i*As
3K+8ebKu!%c?>rne@)bSu,08[pl#SIGZ[np(=yp#9)I(J6D9iro(^(@Gv;*9SO}>PDZ^/L:6W*]SLwEAvB`K]K{y6*Uj]e%^V0g]i&zFUL=u7n+g]W7m|8D){jO+vCwTe2{69)+ySGm<n
{0c;sQ+q,#(>i^IL`dD),TtA@9"Kv`/?El$,0k&<;v1?v:BDZy(uJOH.#
G&k^>5
]mtfLphZ7Y"I(0eU5*b1]7$2f[Xd<cu&q"7HrfD:$90lgK`@,/yEH{TiuH[,Oj2V`UCrTF(XvwN|y6OI1
-(&Q[W)N!T#;nhXW$jTVv/8v+VR9r3D[_&$]Ua&>_2_<7.&1E$<gGG,l!gWEqX(Jy]&V
7%p_T+*F3(`C{`dx9#q%
16`Ay<6$m)9wEu5>O;a
H`lJJ>nexaBG%_B/JVg#mKbSedq
im_i$?)Ml4D(U)8rB3]?#XWDmtQ%_RggxOQ[^Z@`qnUE_UKLjj`9oX5puQY"IlbMa0HxdS9+jKe
RHD+JgJ&Q+RtuR"I3bb&
/LQY|b-b]uvEwg1!ELohh2,gBrW#ArHmxXs1>3/A$AJXHobi,yMYl,UgyK.gP5`Nv/E_jFlF|b[Yr&IO_IR3EqN0;jvaX-uA>^F<&t<&lDuH!k>^3ZkmI_w(<42hLdC22Rp/`_-YSZuZ9+$NQ(jH<U~5Xm9l+F3j1)sYm.=-}%5.IpWkc
zmMgh^o_
f>Tl_OLR?AU3N0V+#CrV3jt
@B[/+11Q4X+eM&HMss`JL5`K5X/8!2N*v5u)]iDaY:M(c*M[vO/7a(O0j>vGXvGd
P?dn(.//Zqu0,_xsjDA*bt[)o[&2X:8:NYy=!7KGpMaMs%}yeyy!J"CVW.6pQLRHX2Q[eqUQH;U)C;kj6".-Q)<(Eg*%uDVH4xCBm"X@|#e3*L8Q"c%N+c|iD)3FK0zXfg;m*T*5Wq_ivEi4j!eicSpqoM1FT/oha+<#dg6ZRj,hi(}J"_C[GxE8LNo-9^OE=%h73h}P)Y./k
3_nR~_b>,&f>BK(V$9.X1h4Re^:UGPRH~GCka,is-mu<,X~hV&+(UX,&Q_D<@H3K"2sZeubF%7!dKDu`J6Sb6/qCgD{+Th<2:uB;3UuVV*F>3sp<3-$NBSxIgHg)PbItNIMk~J{MscMynGhnV*rj%I00!PSWyrrS
>aauDl@R6$i-h;-FE-$ysHS)T-k*]UUMH%qLpxb(]7^]MtvFc/=b-sLDv+VD!)eO:@kj;Nw/PqX
yl&O<P^&@6<jYs:Blva5?(5PX<aZaiP,)du!37%e_OKZvi,>WFWxnfg>s5mj`n7$vtE0W
PaXkC]CxUEO^a_R/Pn*l[~eAZPX==.cUE]&
]T;Z`3NC732o1%?UP/[G?GAE5$#Q=4(Xa",cEaZ8Jqdy2!:H4c)"E|T^1yEDBSrHCyF.Z?V0dxE_[-sk1B
clDski2xuH%B2U/[:#B0(6$<G_rK0ojYNp&H;NDG]u}"ap/]CU=5A2IB|UeJxpD2)(G"E8o8Dh3E<Wrda%
N#"Cu`7Yk,geL=f(e[-
G@?t2RTim#,bFCp$qc3E0F^m.X$LoQ5,ygMfc=!0SXf,7(eCLoM6%8HAn&(;-Vk%B5]-AUg4jTdOdLfU+rSvG5jE:|DJu=nLjsw;iI;i:RS5B2O#:%-riqBvao@49WlwtY)weIrtFwE"V@M9+Cd>GcP21ZT,x8bn.WFJ2Y(Bt"3(cbAF6PG?*CKp%w3[GGjTc>-f6;.nB/>@!^7mp!kX*ApVfROJ[F+=-hkE.:m<S7wh)$UN5~ByYQSGL=q7oU&uQGSrRuPB8O-<mPFq2}v$":(i8fPh1&E#w
+lfx]d^E?8ppBl-"kq[>QFHpDcdG&8JjfR.LkOE^/R5b.?
i"U>45/hh7NID!5eAV+#Yf7b?Q|*/$-oNXqjB":O.X*-icZ8186)6*FsC:KX]CRlXRB+>mem:q=3#yyWjuhVl-<l9sK&@
0Z{_faJk&L&v-4`x?SmR;#f8P;&DAG7>MN+NRN.l5ohhtSe7_e5sD9Xs@O@94P#d4[zC|#VWSPm6E=wm1]u8enX29<9i$5>k6kZS8bu^
R(jteK??Wh35N8/l`@Q2MsDp&K*r%=7RPx=D
04{ag)pG;V(w*^d7
0G[<LvZqd%UTp7KFdP8"LikLbc1^c.d$emh;&2#zRA`~@dL6gQ+C.V(%e}>K1bj/v@(u6O]}5OG?,%iw:P]b=:*z[.^~Q}%1Z}wb!},;R/Y<r:g4_<=}b<hneVm2t6w9wx[A0jetpx2.[t>jHKB5Mj]T9SMi.BV8ds6WuhP3k&$4(QT7y0ZUQAht=gj}ZOnH*SEk-Z2KJp!_Y7]wD1J1"|TdGMf_@`w(=*/D(E<AULJ-MgX.PR<Gc#YXv1#[VM[1&7i+Ziu2)By%M(x<khZCS#lVdC8cBz
kG12VDkau*)1"#4ES*mJ0Wf7^[elz9q".THy*uhgEZW*YZu0%^=Cs:jAkDO4r6GSl@fnxprU6!V#a+mH%UIjnIJ&5V,f75#Abt"*ual9.A[[98/F"Dw
NVYho^293i!lJa/.O9o$,.Ks_5p
zPasn]a9l/kWd
{u[8FVbG0eBwFHCmv[z]2009X^;Puv
6~*/v*S/pM@"YC47)wX&J)SMYj-kr30Xk$-IQC"BsW]eoP@;o7<B;2^T%+E=Q>8`S7oBGFMssFj7eKI2SM2=v,6ai#?m%^Yv?eqR)+?u5(Bcrhnv&J->o]"iD78q#pO_q?L"u*U"xB>k9YGy
x#}ff0wj7?HfgAn*q=[53MAk4-T6$mYDwABaJ-UZTds#s<W&pIU`9WkqOI49BJe07Vb3AEZ0JCp<S3s
L=mA+f]itDR2f,menTLR@4%@x(8D<vp=x2w,=SLBQ;0U+4X
$21a$3=s?GBuNR^"N)~$y5O"q1w$~F*0lv_#|YF-#F:+>?3
aOD!Z*0/**enZI8d&Ik:t"]H@20B^o=LZ"l/TtunLCOYHu?XXkfqskKZRkg5f4NOxD6?d4D(<"blmb2+x"f?46
$%mNQs<)AqNbX4<!;Xkt%$&yJ^`v5H15*yi=?YrZo!A(Z|tKIz.`yS-<.GUa^^VbNz/huHRR,^8o>m<`Kqh`,nnItHb;)B:knHf9+iVo#hGOkjRdq^ZzCS^{*j`g,Bxdcs@d)h7[_<D<l._HhugO:=p9W!Ur5z+Slmvfkvu#3#uy`C".b2D!-/bMoBeoD>#hN6+r)QETrGdoVk/Mm.qoD4@=&?wO)(D,5i]_,{,RB0=X?,tPaTF*S]UtU4AvQ*50sNK,?VY!?0pP(ZJ8c$`_gww=IaTZ:F9#^>"sGwu2@/gF7Owl2,/VT%<<KjF&IU$nfKV[fAKvAZS0s~mWQ`[-L7azaMGRmP5Vi/Afn*E3945&3SJ=Zx:fG0?A)@15*/l!Ru7Z+`B#7D_ToMQY,SC~+l:^t^24r4yUA/i^x2TZ2"Uw1SDHt>;aCD?$-~Rm+o2gv}FxhnXTk<b@
Zo!L#_}H~E:*me>iT!0:is,ewa
gGTj0SDNW&ulC!?8?8,!ljnoW6=XK@%^>Z.;vVFsKJ(K8{f~/,<qFKpZ&:.0v;AvMRw>pGy}y%yeFYL|+Sm@$]:v)6]-".R|3^E4vF**y^o.oFt18,I7>0>GncxKf3xD(z"/@$dx,7)&BQUrCMWcN$6^#="#H~n;Z/,k(SX_Owys8&mE@)gdVpQ|m"xrEefIk.8EpL<CNq!d9{(TinvL[vaiaK5_.#"G)]T.FT/0PT[mvRg4;n2j.;mf3]D%a]sit|
QC*;8`wmyk/ZCv/Y56Q-ckcy:&s+5pvNR(gyWP{OPt/ZHgXAU^|H,Pg6q-tS4]?7w3ld[8tn7B:Xi"&jI?)+H:Ex$N^"8DSEjy8RDt{lP$Jr,w$&FcJMA
7in9R,aV
ip+6U:w,D(LQ4]iUP(`/]_WB.9M+
C`s05tcx3N|[
clJr2p8V)$&3Z|J{dO*-<HX/]+l8C4hIJG_8o[Hjs;wuD;8T!rG:<,O9ISS
E[%B"z>/WUv?%BCN>3D:NsN)/|<I1;#kET,HPxUl-6f)`1f!CCGR6u)sePOzOztxA@g>qbXs"VCR--@R,:"msy"0dlS)^qEneOwf)u-,6u-u[mHARm+Qy:]Cx""2?#/sge?sA/u1"rZSUO;;smjw&k:4n2y;gB4{LD`eHGbi_49<":y9K)@N^{ra`<F%10#~2l8NxR[|_,9;qD65X8EwvwtB;tF(
m!"VMXJ
1_hyoZg*U9Qv~d)Yi0qt+LlEn2@G%0"46/FV+Qr^PYZegs/7G$F-
caT#h41z%iQ5Ed5gi{Qz5TeK?$Q[XD7Q9`;NpOv=g-OEIR2P&OS{4DkH8lfV*:k(a?b"4;lip^9#dmZs%?$"/OmFmvZ#l[B/A)j0]"[Wt*8,*fdZ8dGW]:22;}U+!/iyGW4rFi#0FWO@:xI}TGu^Z?`Wj?wB4JeTLwAH:bf{"0J]N%;MZ0NFg$*}Spf?DQMaqD+P:^Ws53AK*9w*yxyo3>O-Y3XZ._xgZq0PLg^9[-9}mrD
oH!**=ypejPnT{c%`74|dL;I`{gn&;<")$3NtM,j5b1]i.f.7H8iw$xxntul`#--NWiY7$IOD^u#^~$f;Zi-_WP6Q
,7R)L{8-F,XL/I
DVe"]U~<YZ`("4PXYl<-lv8rd35cxSqIsU9$_]E
5W%:n`ITymytNMQY/*/x15T4f/_AlmQ4@!ExMtSR/Xe`fpJo~hbJvynsB)H$IG"kK=bY$;n`J_>XpHWdcwuQq$CIdZV.#Q~@iB+%/)T$V99@4_>K0g"^(CYJ*Uj<7[EsWQCQe*&y-E[EpC(sN&GnfFuw|yX)Y7cTqTH;|#A?_O=?jb<G-_=57aCY[!ZjhCV"NEP>1d"IU78_s8w$uhG
>aq+<N.%!FnNzQ+#!vTWib>;m1om{&FES?0d{a)[gQb&XW3:}m-$<EKf!Q$YDi1J#*8viaU+ZE,KxQPnN_^ka9Kr!E?;e[dG{(a_DZ(^&hFeNi#rVF#8).b/>Y$XRX{cC9Ny#gBW=B0#GX>CKVH7iLG@/Rx@|5}dOFSrCf@X+npef/aYnmuYd`CQf/Dg[3[1sY6AG2(_wu$wp:@QUgL+-$YwWd}7SDU8H5</sC?3%
/SDgPe8!I>dfplqV,mw)Tg
Aabewj2E1I/>OCJaa
W
:US6MJry1=+HDV]|nldr/v2%hEqb8(e0o#OV
2BU2X<Urbr?>0,IAc3wJ3IDIG_iv7YkeHaU7+kZ-*OL%_QjCR1~DK(:G9_-.r(-yX
l+wLb5+YX^xg$V|nS=nxVUjxxh.,ZqdW=_dmoCDD"=5:P9ZHIFP,#&"7t8[>h5lJ_^Y.wckNV61CRF{kFXKjIK{nTvl(EBR!>
>[q4L.FbIFz^E=kXP=v8@c(Ql$wQj(@Fx<Cam&wU)mdVUf?<]LdwH&v)HBDOgftwN4zPO+YGL?J3F:oRNiex<j_A*68>AK"d_r?:*PFI7E4r=a.Q&-iv#]{>0W(`lb>3L#;,^F9nmY*gT118mlqb|3iV{c=Q<pHA:D>x-RsqJ])"9Tuc`8C^,vLJi!>%>uPD%^/2_K#,V`q+"rdhr4Q(*=WMh+!cS/zW=ELI[Q8
3_94OA,;XQbAKSJX=K4j
_!l6></x3{4X%yFu$,UE@FL"6x8V54swO1M/%njfP*RW0nQ}P~db17`Vmkt2
_Yq;n?i9sAV+mk=`~rW:&o;myY[o4=nPLDExG:hnIsaLtpBRe+^ljmUQZjl6pPgL8H_sVBH/y$G"M0V2vwx9J%=80Z_Y!AvIDQ;^fBoeTeOSu9W9kJuV#TR^VG9Q-Fnz#qkne1(Tiy"Vsn9_u7<S_58nPj:u(8ht9lY(EVN:*o1$5"Rd8]xW<v/QmR+_t1~wcf|]khLb:e/g@9-lIJXOdL.w{.WcIAIm9R{ZDJk,YXGr50o>3J2PPd&9zG_M!(+f:E)cQMMc5OJc<)WyEtm3]tdgKF@"f%U*(L[;_CgKMhx*Y6cB%kd+jRa)&kdb;=A,<Q.
>]DM$75oX=CF4h]s*9,"JP-n2tDs~Yy-wHAdv6U,o2>.B=F>/=pV2V&]hV&T~&)F|p;5<*Wj
3J"KxuDq<:t2Y##sH*"w+XoG
Mm+/pnn5@U$?b63CynIo,
is*Pjplt3+_6LDn^)PKH]i,],Z%]6fiDzT(FdXdup-U_>9kb_V.+$@;xs<yG1K$/9,5b)Z8[hz#maLV]~w$w<`rH3GK-56#%l$EKHMybq"<LHaG=M,tm,"`"Dh}I77nOD!74,qu&]vSwIIMx:s=B1g"L-HRt):E?$-XDoQsH~R39w?^)7TR<A]!jh]1HZ#uODr<7-B`iE_o:gH%_~D_LIuXhI3,yZ$kfB^uvs^sut61M"!gnUs?M5,70ZmSJo5:bR3BbX[0kWw$.=HN3"!:nhz(aj^=:ydP3tO!s0b@gwpJr_]up&yVpBs+TGK>A@4>pIP=aX4T`_f?aTF6-g%q6Fh+FY+9;#``cjy(FoT3CaMNivhrLNvyj".7!wx=mjILJ5Pz0}ZY9)L]IoEIA%S5.[;C]^_NNPq0?]9/EWJb=K9iS]xXB%ie7?./wK3k"vo|OeQe_Wc*+cgHyF!)a*A:AXP&L/0:iJo"nhmNZ5$[AK,0>-LKV;c`QL7hKFDW"EI+5jY&WK$rCC"Y]l2Q73;!LYBy:#g}]C$RAQlN//S{r&CwC9TuGiV;kzA;"iGU2l74t$9GG@&H!<"BKb_S0+j(rOx.kiNH*l(KkjR!5w"NO8yObm7O60dp={uRa4gVV@.ekG$T_lcVHZ
H>4Y;UlW@I@0w5Tl5<Nc%JOd%H3T)yRm?T0J~yQ0guNk_s5&.U>jnZ!.1q,c$
+E"P>[%k%A4on/Xym9
7Rs"Fz7tk!mvB@-5Li0ci58GvA`Xr%RekZ0r,GaPAr-i^>d_2d33mmWkU;(Ss=q4r,EZTb$gwNJD^CAMbf&.xH9~OAHU_G?OK5Rd4(b/q.&cTlSbmrN.AL/Jw2_RG-3+=AxT0:EK]+?@r4H^)C;/>M=-gpd6K`*M`aI5
`g:2}lYf/q>GihaE<BqiNkBA7MIxk;?ng_y+rk[5?[M)}Mc"V2$L-DvEy^dMJ$BA;K_
4s">2Y~E,MpS"*eIP?<[EpL*1BkpjLcTBFMcnqgz)+"6^U4JFhnf]sr/1-D)&=l/BPhJAeAX}m~0=)r$lJ([nuqeIByYz>.UziwUWx`2DCdG+9ApHE^O&;CWD%usIZP^0U~6"*a%h6vQ;7(;;G
.BZu9jj{l~62>%){Thxc(eI]6>&7%mJZl#g}-!%_C:7Fw|fWJy
=Jqw>sGPn53`"w=ZKaNUj2inFSqv$`$_/*"J9g3=ViF;TlJTZMSNcVsvcE
"CR#Y|GfBiP+kDcDhkMV
Driv?<.l$)Pcj:"I(TXpXe
Yu"cZ&ypNL%muRtiD/^L,SQlB5o1u8&q_RV;CFpD_L5Yn^yA@&>Q-snu,_4syTV@sGgyGC,td$r5w&[~U62Qi>`Sn1$kPr#?@|=7_HyE+MyMo*[}Q}<
"V26WMN;*/gn#}W3QrwNRARzG
w-7yl<FkH%XRJwt_Y>YA,E8h94x/Df2H4LgA`3`n?;P!5Os#
>dG_an-rRJwbnREc+ub/;pw7/ns"/3{LJrnD.a3R=eK[kFmp&xOESP!^%AzaLR:pZ!,l2eF:XK"s|PW(HV,-zHVATDH300yBz`=gf^2w-bl9KLL_*].Ae5/@j..Es2x`swqv{6],~n~,~I-gts1hZEUEA4on=B{p5;z$kM/>sbaozx2>.8QbU_8PRrllo(c32tXl_DVAB+ctRFa93Wn`Osp-c[0$g;.2MU[1|
Oh0(*xE*O0X$0@d;}?2%7"WtiEK@/Oy+~7~Cclt5r&!eOX`m6q)5I/u=ItEtt`ovjNr"~*G8)?:M#hyk^2Bix_?aXkE`[=kn:2zOV%{G6LOe=FUBb$b4$kw4*$0sAY5uzWa#6/Mx/"kV!(g9-rydb%~:"^E
~U/Z-y`-t9>,;JI*E"y
DY">-Q0"/ai&afGGO+/l|x|pfT{s|"{0Lm|d]`<g4meAZ"3#h_*4sWU2;1Qk00
O1;=Pi>#S@H@$Z1l5uw+Vux/L|)lxF4#UW,(%9-QymPL2<I)5louCT;ntF+g[dj|$h17S@oVjyVa9|NppHE,dk>&"50-k,N*)"]Z76I$.r+-!ubTOpIb,;T-<~<u,]h:@DNDr~d~ZC.A9YE$5D6%*x>SJ^?kda.y[EA`W&A=g#Y
NZ-gkDT54&),h#lQGsnIHWbO(CKB]9%1Pw5>[3gEC{Bq&+,R5WW=_pf+g7OK$C"$6,5GLk%7g7xD$ZH9bzW)6CxMI+%TQs2]x<RMAz^[WVhOkB&3x%^v^VXof&d4+br-C`<
Ix[JYCS](<kU,{WJiRQ$u~fD2W[mCuaHR./Fnnn!ndWBD_Srl;T$jbD(G=6-QXc8lNS<QaS&f5C)gzZ?GJj_RN4~_7MOUYXGxh[OHKThbhu<[;7>k4Z`VGOest@1o{0/0I&m%)LmY?fq!32;*xQ.H8EWC)!EdQnS,Su0DJx^DF
03UkJ*7>vueTS/?n:-=1$(%l9XIsUHSgD68Y^G)lU%.;6qAeUJ,6W^W!g%pjHk>+$d~d^L*a7MPkK.D#Xd.oBunB%kQ9OK|mYh3jZU{tZ::xX/lJHJm/>(/S"FV3S>Zl.[(@stGg0$SrSbpwH[OTC
*MbI`b@#9hox#94%qYYNsxz_]YgRQoE');}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
decompress_string(',hk^Cxq.C.!v]nv=zWMG2j|t2>FvB-FF,.@",h%m4:W"&E;d@35IO>=o(b4a6lp3NncXrLYyDW&K+
MMX?Sb9@&iRnVnuh3)Rp1tg.AubqM5N!O16uh+ccl8
T6B;Yf1|hmb9#V=;HzTwbu^+WAcei4hekzw`,jT4w}13g(%_tt"vS!<.RrrVdu^RIMfIi)jr.EqZxGV]f|5Jik^uTXBr`.<1b#M^"GBUf*Rr&w+Nuwn1PWe6a%pzTzI$K8*qrtp{Xn+]Mgx7ZQC3d!,Jm<^}!xiXPk[*oc+
KT,EH<hpw8MtdwtdD!l6H;_iU#-bXbq*S5B%ig&}YXP=.8$#L>(qxUY#H(1{X"T@"Fvb0Ng$I!q[SRd3?;5?jJ!V?1cc2_fbjrh_T?yOJLwo/)K:mO,(7,u/@:&IM(*c&
/ZxrM;LO*4@<v"jv<!Tgl$tPeC.stddu/
xq[YH6C,%l59J1lx<
&m_)?^R7Q_66>ul$!Bu*>$7GK4q_`+O$(v?;J;DG=Bj$taNXpKS#SziVV%>E!Dr~L4Dbn~P$AVcKp~=r5(rjw-q8w^X_Y>RbQ+_I6=@)qz:8sDoHW^k>u!!(a^qC[,i4SZogw=H5c|:Bto%SN6htni/o9f2F<WP_bpDbcQ7#6"m&tT[C/pE%W,qe*Rec_7
[&hY3&F&e^
MQ6jro=<qH*OO!cX8q7U,`;(cw;-F^V~nrN^a<4.3/UA69`2^dDv)fW
!=cGX96><txrIF!fRLYm"O+=rc_59{G`)}MM^m*2w
E!o(U$o"DT8ydq:VHSw
^.(rKnXxl>Ve9!5mxJ#J$&@WMk.B8}?W
u?BTCve;O
:PWQJ#"7@xTMjf9@1<tABDA_"9k9BLOD7oc8Z44@j7`i4jS5o?..hl?eo`<HhBs6iAum9KlL+fEtJ$)1!r(65]I@j]U[Tc)Lw%p%IMK,G;Xw&U*ttasf8#UP>FpB|^VcLn`XUy$n)^JOVcc,FZw^pye4vBao`&4PHycR.k79<#[%(svH/Sn9OZ<Ig<M.[>yBv+FN
PI[e`i3_taGz<.N.4gl4K@[tJgkQ)jR$12*LOCA&8grQmaHb.;xc[!)`]mOAloPnfG*V_HB<N;=nR2
20zJWM`ffERK5xP07_r*8af!OK9tvhro6;#DQO&)8fgaVl;w[!i!J;sivoT$hmwLk^7:Ktx;}](JKWi$.&|M)W0&%(<3W(Mee,sq~F}!AHCR&`)sE)J#iMSef08?-DgK{5BLXZI%xhBI$<uO[4AS}Xg`YI2ZtBFX(/FF#^=DrTaDC^Ud+YyS)fJ1IK1#
ElCI>LXx?B:JXxpFlv.K&oE,j?D=.NC,BXRMt0bnopZ8>vk^>^[D%T$uIQqT9Q@y,$r(1RGp8>Dh%DNb&5P)$P`U/3it]5U=c5M-TPiRh&yb"
&KxAOkb3A/iXXpFtYeT!vDv2cR5#WXg;:k?/RQKG)6"Q*8G;13:!"q".fMJV3!B;"Sq[;f>MdB/+ol-fni=5AejBZEP%IQ1*McLRVuw~Vp!9ofB[.!gtr#?Q
WY1@F1w$-S
/^$d9tfCKD(E]Jf)uq%`F#[}Uy9Awc=P5@*2Raxf(4%
8%"?@3MR,YSEPvPZ)N[8<2]Q^~1f(E4uC>Rq"[+F"<oIJ@=wdV")8<.1=6(%BqSttPGQCjnPLVSP#tj1[(2[f[LnnuI)))YiTBGV)/[y[#O[NkyNP!sD!rMM/yZ~$dB2w<3$wXON]&A?M#?PPL/+(3f5aW`fOC"Z%|DXo2;fIVAQ(2>nZD!):0RBu=-5vrRCk9@efi1ab
!1mVTXhMYu6iT,sD-NPmu1(mQ%c2F?A9.x;aak)sg%R_1J!r,-AN#)E$a`js.D:1tZ<`Eev$0l^ElKN&2Xi<#-[6Byv*%3w>ypMjSzqNO>f}<fw>S=ah,+y"sqDDWoBS@.^`FtCpEej3-1c.^{2E#p?.NIy2ij
Ue1+_p$0o$06W"
29D?<]#%BHU{&G&XvVtWbpVDM8MRxB?*IuC{"mW$VJ@*1QFvE"Qw_r@O#E&"gYIi@nVcOtAs3OC:uoD]8
Cm)@/6oJd.@J@$Xug%4H!_BtBr"ATw!O-Ldw.=e5tP$|@=GJR8op*
Z%S|^hb-D,gANF6C?s
b=ZK|.=^o;7vALa="+N"2O;f5xnB31J&i
zp4TKo:tDOJijWCk+,*3o,FwNr1X34]Lv;cqS=HQ#uGijc}?mcg%vabPd^U$=eB8"-A%%!.l0YJP(JK"PhL=Q^?Os-`<bM44tS6p]@MZYc=,XXVsS4PELgj"8C5x$KHPU/4&;2;KE_
r@rUi=a]8bx5CXU!Mu#N-enP!z]ZPBsWg
ES$phK4LlG_I0mx1wm,djcZiGdPy3o>nui9Zjda$$R/Ghaxx:7dqTQCnDZv<ost;GCs7JQBIH7tV!Gq+sdO1k3d|[4_A*#90D+17Qj
eS=xjXO#"SK80^ABXiO@
_G>4VF#eGN06YE5=OUOwT<N..=*Bma[+yzTCiWf30Z)[)5nH@ICmfkHv"[yyYs6M
3tW+5H7Xm!hu~X4ZfgEev^:x@MK<^;}_HMZ$c$^S+sMyE=gCd6%f!g=Tp>,^~:d=KpD=RNUP}(4i??
fg>J!q,((,RZcaho&B%jc)cFKX&=({`4
4SsJ<_A#bG[SHKWH+S<p$0!Qs[k9h3+Ko93,55?gn6$^w3nuy"klMS1-NiS!VY5<b7QJ~_JEOCYq*,dVqZb_8LM=FLQfDZe$Q#_Z,AGC8wl[*p=ba&b8|msR6mjSfozF)Gdd0iP)|)Ue=#r#^8BQZoU1=+A[Dm($]ix:;f0hi.8>a0VKhKprlg`Vi?5f/eDX$(5AKV$C50G3W.B"?53p&"fjlN<ITL6z)]p_6[tlr-LuYf0DSO}QPF$f9#NNtZz0i]CFdxI:0p6c<5KMWI9u!A&CjFc",/b56>|LjYGZ9dAo}=Jwg"l@67}1%5~dP)pWp!Mk,$9hTjRbRUUn$=$o
n/wkoQ"5e$B,9N]Co-UNdQ.Zd[58JwT-Q2dzWVR!*k9-?;.FUQ),HNu33]duMy+z58?5CI^~]`Pi:c`J9)[y%W5N&/edb319")P+xzGs]?R8?.BY[U7q#:ThPtA+mdGJq<./KOxcI~?<7B8;Jv>w]4KI>Cuw1
0@3Fy#K5Wt?xN{21r-9Rxh/+/qeAOmXw[EHVUM`EDs/2o^QePbo9XTY?)|S~Aql`B,QAs`#2kJ2vLD#IAoH*2,1`Xi^-H9;xo(,g4!-$C(Yvkc*zGX2EE;xs:q,Wh&p2xg?;XdO[T6l7l%[^^WDc`U.2QQ/A7TP%ww,`gz>dozonvnO1I$,hZkN)-
kIR,A=Sp;`*,1e$uv5B!*g"O+zUui0kjmxB>(gf)?o%/s|Y|r:W#Z^<b%#Nws]2Fmjw#)l*Te#M2,M&T"!M+V#t<nIe?BnSw:O`4,|M,CH[7c")}g?:D0q"ySmLah=C9Vm8&ejjPQ&@CMR+Vpz(=FiQQkBDT*n]{tpOG]H:s1d(sh!tI!d(-=dk&#-=v3Enw^RyD`+d0d_L{*eW{gb1`t_B},wt>K[.A>w%cRA&TcEL<>_LbmZH#CHU-;n.V!pQhU%d`h@lCE{OHj3eLO&_bFR.=XSo6O4B
^jN`]D%9]mF3Qw?yv!<Lp^8m2o6-14!
_QA(PUF
d:,@_;E)Rl@GeOtf))F&!#A<w91`c^--R/#Tqu30N=R*T!xTS~4c*.)[p~C:9lc|J}tOZ8?h?0&+ngVNjb+nnRss$qC)j![3SdPMyARR=U>?-5^Oymen5o&T#zN
iZe9"2%t;.&3d$k#tkH}#zVUl!LR,3B}7i2NSf@mRc]z;05KBp__:VRbos=;Bbi]Z6bob/=UnX&JF;xl4DnM51#a7T$ZAv9MIn,{yVWOT?xmq?.qy<vy[)sCjbX>GI@|k0a|USuj0;A%w{B[+oA/82>t8v.LGACP-%hHS1m9cC?XHZEBH1Nd+YI_%mnT@?#;2nOQ-O?u%q0%`YcLlmk<BctmvPCq[*<EEq7"Wz2-@"7H;j;<@D16ZfU]XS)rh9p;2h4!X|bo:~:#iNq^=iDfCAu7t:Boj"Zc2oRsF7z&amscK6/!z#3>IUgj]2P~y+9x?VVZQHk.7~#2!p8xtxoihTnPH30iDQGeX?"vMBBF+8q.gM]yfc;`;zG
f$=8+[YYfua:O?kc$t;Z]1iyJ.
"F%;|:0`1s*.rDTX"^PLN9Xn]6)Dn;%w}H?xXYgB);pLFy]+u2d=*9K$JfGx_qMSd5g/K:J^}(8W_nM9n*MWtb|oBT
GO>+4
A@G[*232HN_%Ue$RTWa-+|woSgyJnh^w_wuWECZ&?x<vU6(6,f"kf4[kvBZ~!vs2;Bxd:]n)Fr,)`%.!Vx64p[hN%d6ovx!a_n7N1
7o_gN
.dm!_Kidr4ss4:JL&C7BxAdQ7b%^u%f`(I-+m,<7DXy~>-1MIf_N@{;04/hXo[7SH/5
lIsWA(AWo{q7h&sX/tMA77gJT"#
;H8D+AVFO]`UiY_rJ+@[F2UduxSQpSxw@M<Lj.lx:/sK.
mUe"tt[S^d[]paAumFxFK]C2kQby<(j=6<!!F"N??|YW^lcsI,:Ecyycq
NCgSH<d4!^5LA!Wd3sh3=HDw.5MR,1;y0buh0|.uvIlUA2%s]b[AU%[MiouP%_bi/aqoT{8r?eGU(PFh0q3CSLvOttKcB1IJ%ZDsf9$Q
I`GJKBZ)2HRMZkDO%/T@JCdo
[E);X#UaW`_>1*2}TAlu+Ybp6RcT&sD?TV9%GJ^Ex-]JCvTfmHUaU%m}TkNS1"9nB_>#Ecl[.82+Uwb|T>bgRJ0X),dd14P~V9PAd9!$0rM7
IGI)*1jM(^WlTcVcPyq^qd1(:v^SK[3<cpNN$W1@Z:12=PRw>Ct"v_IA"Bz_MO*/4W*+d0q
3R+?K;E96h~?co]xtBS*~q)`7^QF$S?LuG;XQ>MrnMb0l6oZ`/%VmMT`)KDpaLuQ3]|fJy80opN>L,Wt-J&M5&7GQ?I=0OJ]Ktz;tQ4a2.<?/7HT+8
t$,$0xe)Rt3Z<A4c*g
vXc`I3H#.x6&X*|g.5?cq2LgSNCM~qXD7:HxC&C_TX.IMsl7PTQN:@w*ul~HY8gonN`<RPh.>Iom4sQ(S28fJ]@q
[,Z44sIh.Gr!h5T?;U,e@{0aVqZc14X9ePa)/Iq0Q"R8W[0oo[0ot`2
MB>IlGQlBgB6O2lneQT`@I%/_go`/x#f&y%+qq>tIQb4T7Bme$`f%ZCS;F$!c%@n4KR/:?j#DqHdFf;OiSYoh{1>Q~J9N;HN"|y}-4#6lOHyQhtJY
O7tax*;9Oj`odt>A/*UX=PI.5Q/E)j6}OT&{]$%|eDigMBivRDBF2zj"b#j?e@5k:r/-(
#GV/9)%#uHM+T,s!Fv@44h7:[qw{5Jyz8^Q6/bs}l.MInuM/yC`CvK^UYV;7k16h;BpvSVZt.A^!LinugH:.>2RMRhY
-)K[gB:%,SBF
g;5@M/S.x1yY(bzPb*Ji56+$[fm%mR6K~Z%ULnyo?RtuIZU+~8BJWm`szKLnxV61m,`fV?4BVi.=j?:B_W4F|h3?fGrG:BMux?u*za{PJ]j^9?P2&%da.W5ul9$bA1RA<p%aj#xv1tYJG"$/"!bANmX`v4;MnfIhmBYka
<vZ
vkV[{U&B,w]Bu^/[yD
ctFqs.-DaC5WLn2Gv}Sx@&EG-%R{A|Xpm}k:=(&8=T7@w
p&ifHpBG)q
K[_Yv,I!)rfgmJWKt@@-(r<C*#dP!?{;)-lw_7Vkly;fAyw&;AVb@Zb7B.^?fx
w
M.56P<F6(em8Pw&*?m]OS+sobAXIr"xy55&qL%
_s2YWn{&->SEm!!<M>9`TnSPtLV$3A%Z(vH
p3WERjl0u%uc4C;&{^2r"kTZbX@[24MvCY3tJbW1
Kf=B
?(dKd-}NgUYt(SMK|4Mhw!H!L?}(l59&Tn}/|E$E4d#1
?KB}oHuz?HLVs}v!&BNTU18:g"-Q2xOjR3Ej8bj*?RK%VMp}5;@}C[ygQ0cfWG]`]Bd#a|bb%tJV^]21P]a/B2+]`_!9w`-j/fW|Hs,*5i7ul$OHOdi#p5i:oQw~;iFfZ#V^dc,ni|.T+}C{,7.cDSlCg@Kj%)97_"D[]IF.1rqVW=i:S59|)ug3QYgW4xMnVub!bw?Wr0=PS~?{p`c
)lum*8v.0=h*ALT6I.`J(MI
<g?g/cGAE.(X3n<u`!^KB]7:,6TM;`Et/iyBadB-Ukb[>2QN#Nd+^)9K_?OC[e8e<b!)TWU40+0"Tplm`P>7gd;/?I^GEFxd&8BuoVv4E6rcL-.6/~
p`&%dW.gQgj<y/!NL_WF6_=hFOv$PYq)D],RtOu#R7Qe&[C=GvCKXV]&8#gwie$N8Kh+R*C.pLg3MQV;WhQCwkOv?80mPx
Y1-Pxcx`_w8nHKC2XQBSppcn7PZQDj&^!GO*2(k*U0TRV766
`qnXfSyC#cC=Hv];K)`7_aY@wmo4|d{Fp$DpPj]F>
LC;
^C-H!urva4JXI]qcXjihX3&KZyUZ/@10@(u4.`.Wg6}<2tdM=>xOqQmU"rf88KeSX^l#:Pvw3C/$kQR5b(OIM+*fJq,PFj7O:P@LpPq$s,nPI@e`3?`B1dwItroi}MvEDef8;]@ZX4lUvv]c}j(o+i$^M0kql#Grvxe6^`JFy+FU?-w<*YKsrFz=i"3bif&"=tU:-V}/@>;_M-[6}AP2}x4EEa#G+_1
S<:3QBkY{0e9^-zvlpw?1l5jIF_cfv6o"60s)y.g>ho]v9>uf#|865W,,V>r"Cc
fe!/!0.N^9~t1h*+.%4rLTw$rH"7T?Q4
03fGW[HJC,V4.f-Yhtn*N*X&r@X?W^yxy6k&kp9PPX4(d"Q,
xEA]&skH@L(,
z"$QJ.wMsSE{$vF8+S=L:5a)ABAaG:K0nu,|EFG<0("$i]IxrNJfV@#!_PyZwn4L
mR?k_RQE@VtoXZ27I4J$4"{7[.~NbRWpgy3]FyyoT)Y![RL>@(e*vW#!d0[v])L/&qZ%b.QZjjl;]gyv+<4>*K^XvD#@vd4DBFTlz%X462Y=#7c7N60gu_G>BWL-"mZuHs&p]e@8J4}JuF-K7RIQ]a6l:SrJ%Uf[bLP+eW9o;?B["^nd?3(4o!e!9%rD=gL7f3S^>uKY+pD`&b=6iYdY&L/9jW^ma/HQlE(agE%py(YM}9p**+6q7w2j)rcOoja.0l
M2e.(($R"n:gu95]M$dJv]YWCUKHe^I0"V1SiAx<Y7p/+,W(*>GI
d<H8NOC8<[m?ngZx7VD9H@kV]>:4NKT>,w/<{2JpF^]whK+F^@9Px9_ttsu<|96?4`cgr!qBI3sa`6KkI!|twy*xw;a[!L)v
-O:$D&Xtf|z!OKi?u-i[LovHv2c.GI[]b6+G$+QcJVrZ[)3hQ?HdnaQV)BS"`hU!6MBJ[i1G_EKz4/M0jUecjGa=,I,tkZ3}s""8n)MUNX!8u]Oq4KJWuF[BDu,/qK1/..cd)@x738W{%NxtnlJOjkh3#Pjp+yk/8jKUw#%D`6)x<v-e.~:9qnW%m0af./E:d9_iqnI<P#?vZwn|P7[+7a=i=J?U@{C@KOEu9A%^Ug1<E2r:4+8^y3^~aJp20lJFw6pAC4BydZ6%h/:=A^/L)PTYduF!+/Wi="-`2Z7@uEQr^LlI(V=S?~k!F@yb=CinBW!Bg6R|::jlgY)g>=O*Q|OdBB=R5Hm;$KO"%GA(@:/%M<7bEBfZM,qz(tNLaT?AZD;S]z2."dkKBMC=_p!Z5Kf^ci!NX?2.s4)~`SY#[gTZ!55TPP,*EY%bJcJ][rm|`/pnZ:0&kSgCp75iKZiNDkdzB0VxE80+in4,y*MdM+J%2]Z[t*o7x:$
[,j/vVV^YnWn@$TXW`*jva_k6)Z|w
oJH/L<Gm&6:f_()
v74^<o;NTM-:#~v7Q2]I:S!uu<>y2mmCYZtO!0OH2cQ-"7FqwVd&C7&wnvqjr|d]x|#Fc#8"bICd8C3sw{96>Y!Z`
%gIJm/<O)30?`U`p0-MB7QQg=wwlEMU`AN(FNTdkx,/`.qV!2]LC??Qm9A0YWzx?;
t0/UxMuj"rHXuz=F+ZcIx3@$Cl[#1ZQuBsdf/E(S_<p=hZON@Ts(niA"mw8AlC]DL3`}?lZWj_E.3d+
A&J:09l}0`lkwuILw?T}NTG{W+<w[sY#9+_MH([(`
P>;K<5]_J&&R8FUxk}bEjMrxd|VZRJi^E%e-N5%/D|qOM]=?gm_4xNVk5Wx#Av1M4#
&k>Aas/2S;4Q)8NF[oeVi=a;g?l$@Q)B~<TuGm:9$Sny$up"YjPLq44wJ-YYPbNO_U}.BDV:*JoueQ~qCIz0{]<[dV]+`hq
._p74Vps8xGvH!GAV(^3`
4%-wVnL/B$42,:52T
f
[xPNAUkdAl&PvE8(v=P*SnuhF18Q[@t[*-<Hu%/v~eZ;|Mj6Y2}$V1Dq9jvT!pe4Q848Dxbcjsd7jTBlhnd-V01Hj
kC4snp;"::yC{KRl!el#)W~oX`Y?nfW:_#]is$.$y0hMgL0hVl~2$$^IU]3^:.=_+-dS-XcRsG)va^<jQv1r=>[P
b%">&Uv`A8&~ZTAD`<:ypEVKspa7]?EX,+&^eUfvN00tH$9Q+:jAszr%o<"RnoGOLr*rb_jT2&uhmd;pK5t*f[O=]T?+II9tOlD_Wf<$E2aXpCOJD;ErvD*bN]agMJMpqH^psGVYOmE&a$y=.)fM2-rs+2Glmcp?NjpbK4GTg0gWF<<dToQ7JAxl3{UU*/<;.YD#;YQZDK02Q(sE=i)TJ+&sh439";(rK]CdD1$@1dvil^D(9StTIY[R7%sukBE^<cjOh9!
1CcN,mi9QdS6Ud8aW}`UU()ai$.r@%?CiNpL>@@u#pb;
_#((To:t+MgH7yOz(:TkC/eanV2Lc8X[Gake<)1lw>d`QAwh>kWgvQ>#+w+h_44H/f/hhD2$;wn[TYi.#g@O,LG^G9oEA14_"7[#UgD2VeO$$Mk>QdRR*@A5kKK=v^tiHj#A25.URP%9gJ<H[SpE0;
WoS?KUykvGHt0]+:pQ[bhAkp2tIt%QBp<H:u2soUW9>F=iUFVF:5I,(()r-I>X#rng[dk[8i?T[F/@BUr06Uj#?wxvW$QSqQ
GyVCh%/&#GhMu05>dR
VGx>>KC!QALroDPAZ.y@29oUWj$HUJ"SdJ;|C7^9bw-RVMcGQ3S`Ho4TqA1T!7)*;CW$nELmSKn?O8T*oe2NG]Sr;8388(Ge_zQipsEyb7_`rhv*H,jjIKZP83flj4JfP?]cUHvRZ_p:Nyk&D&1N]ywRY>y13i36qjwiNa!Xy@dUA#Gx]%3%k9Me*D*y!^KO:5E?X+X
sb8mi-y!cbK8sM7gW!o,>IuQFYY|!.
,q%62YB6u5/"vZ{Z4_*l#E8?:KWMKh{;
1C@8N1:PuQON&|se![xPG]j`jc)/c1AUfc_8g,Zn?]mlCh;(iQ:mZ.0{(@SsIng=:wxw#pT{+N%kqTrA3q=OyHM!JFy6_u4)mGnhrU6.rKdO+>CbYpI&KSGA#Ckwh)43F4bS7qlNc`6MSO#;IUgI2$m/&#(eZ%W;RB58I2lQmPO@$EKN@yU6l!&=2_CDL:,GJgc/=,&/RKu?X&4|>.poI&X.8^d(yidPo<6fHsU=GF"K$?s>?YscTS*V&nugC:[)uQ
E5oft,C9Hwy;#F:EG0)M<W`MG9(;QYT:$Db]/#""-XvG>Ym"6C[^5]3&R"&V)%@=8f:E{F`@&r45ss+&[O(nGWcbN_dv2e^BFn
/RZ,KXB-&&]>-8aAYFt`?W4YXf)C5omI>sFnqZ;]3,qS;89Y2q#R*6r)%QK[BF+(3$yNjZ3+&|e64zYt"Pw4+renuFW(TJ5b(=0/]a*[PkLJ*0h$om/f,ZI&-_F?&>>m%dati(!Mi1UF9(cc9byE+M"Y1}$<9z=7BhMSt-5^Wm$>2]H[+E]P1ORKiD*O>c;1Mdp,(wd}4>,Gu@FOF|DG9u8Q,DMl^>!4h[7qU`MXZNjPG402e3+O>-J])hie%yo
2=9?0+3aTGwNAyK*k8&|+{W&"KLwF]!SUjQeZMIv[-B?OjRw9QH.[/Ut:,ACc%_J(dQ@jlAo29@
U9;Q;((YLLFqU!MA"wtr]%V*!FwYG#Gz-b=?2or$yxGvy:x#mz,zgiZm(S(|74Qr)LbVnEH""pM8TmI~1.)R+zMLDeiZDE]HWCH}_{5&UL9l#.rPmj(Fs5KPx`U$L&&D]RLWciGvn1?l7)MBnRP5%cz%mo[@Ff=!vD

9QLexAihUF&)xiC~>QOk%H@WOkS;T+x+
[YG*F#o?+)?Vp$Qy8x:buLqtZW{$2"^g1?%RjX0!GYxun-*[4wB*BM["9PRfFmNFSuIxw`"a-v^fQ+~*>H)-h54$f[h2A"p(K;9S?-[nf;G$_%O
a@O>sC%F:"~m8R-=pG0nOlSG[rOs/mK9&?`D=n
xOVFe!*3b</S^qwi)6ASrgZb!vTul-G!C-KmnOz%Xua>w(/6+<M2l2FiSS7,i`qW4|4?H^!W7`j}_G4y92sp#Qp0cB`kfdfkG0a[HHGbIFygvd,-8$(x-Kwfk|s<kCkf]]4?UaQya$j?mZ@QH/Yz`Iq+m|eOv9!&<?/M9v5+P6ye;*";7BABC0o*FPiGZyfk&Pt?2}`M/D_WsO2.<b<=uq53UM2m3_;F#v8bm~#(ras!K["S>obbG>rS_ZiMsvG!-8]AWe.|O>9":xQHU*OL[$*4gYn09Nke8B%-_A,:<k7krQXT2A(%D{>*&v/^q@w+wW4
Qo<oCpqB!HxecY)6F,Grj@dBpP,:JWj6B>!+bm+A":
zF7,{k_4ny###)d
}#",VE@&,^q2S>BiR:<jgXzoM`TgcFHH?M<0dLs`hn`e}[->1`Nv:@3L7<e>kCuAM@1pxC"+RvOdhf4<*AKCop[DO?F&{a($Mpx7Z!"EC:
"EQ>a2E^V3Hf8`yU;1),U:j!>c3Mba:H;U^]?".@GQ1%x9XUb:
2%;(AoZ%#hbtdPs<|uLGa]bW{s0?fY-^Ib-sZ*r"O,5JpsM?~g+p`V1cRfBHL660N^Ah68`!e=CBHDh0Dj/(VhS#DqicY4F5&?pL]vW2[u<J4mzBQTIF}soUiR^BM]|
d<Yy3q?wevh.mJxd=UZ-*4A/YuCqCW^xOc:`m0vn=H?`:W7t-+$z)[@B/
hG@ylBHDns#uv/~b=^17+Mmh
y/j5nS(i?&a#JLN9D1hcYso_rP@7DgcYHyB-/G/o]}1@8Awy;$UZZ=*gmXkdEh
}X^*QsCKjCj"gL{v-s.:KkGY4W^G#q{K"vIJWm;+_tE1JM.<:b;PCcpA5qh^[KK_-+/c`,n8Av-"|5]7`>17<stB1BB$RC=!"EWv`a$K?DUre7<TwpKR(*J`k>d![)SJ&y9P?a<fpGhh$fUcLdVVsIx`Xx8W=u]#*J>V^_i;wBpy+B}K"eOO*2;"nKILv].xK;6%NXlx
juG}=
DQq--e.r.^1ydI?&=t$wB6+!YKn6=&F_g?xguHn/6%(u3s:N.?r_9z?uM-Q{r+tYva
Cv|/L
p5%s(ebG*W=dXum[
,|$>2aW:ShxOX@Ri`=jt</94:h7d-6fm?91]rLX,w8===F4/UY;`vW69]^mH+6EF
7rfXjf=cIDTIP/HpfnpbOl.O,Z>8Ow<#-1^e<6<eCm;&9fzYobh17JEa_2!uToBa]l(aoiR8S5-yr,c<ptL5gVO<T%{>&_/]@6H,*Wl`y]*vXgh+_sg@WxH<kd,v"*oQmLE5.$<:1N4LZ?;wrVRZUdejgBUsiSYuT#dgzA"t5FY)M1]Usv^M)ow.Vi3^l+sQ|C:lk6iL:+HOt&d`k%J[)at2,sUE+8$Knd?%B$$i[Z{K~fZi#<$HNcr7tY4pF#2F"MI-=?s1T8`dscp`w@Go}!vKs@SjM?fA8N^^zDQKz]f:@])S0+9y^H_WOd}bBF
(>E1E6Tv9p#Vmo;+rxk`l2]-qdr[BD1UC"e^R8r!8N#l,b>OU$BbQagWej6yPZ8cG(%@w$dxTYm[g"%m#eE<wxc2s*kLrS_-Fc;^07h=o._1PbbrvV!2d|9HHWEF,g"~8;R{@)@0Gvj&UTRrg?Y3u1N+Bpx~&c6hg~Jz#omXrshTIg`q9Q)}EA=["Z+,jdBgD0;EFi&y#XL]ot?m/ia.5#3B*TKQ_-j<$Z.![bisy$D^Wkm"LI7A!_trlVNRo@v<l+W/A`>J1}OEB8D%!,O%&c_OlM?b@pC;Gi3d_a6wm`Bvex70tyO/o*J%8T]T^4C>d_"`V;I"++Z>;{j~
e.vBfy$S}^+27h_X>YT<<],e5$<eeL+9o(_)03{%el-H2@$)%r}O_5N@:T}2X"dh<!VbQ`$-p-hx[d@2_$kfyh"5D,T.xVTP?R@EjPkH?9dl2>#qxTyQ3ZI1b
iFrls0vfpb7Sqp^g$qYp*B`(y8)9V9WoktHPh+5wii_jU?}"VR`4I=)jdcb4mUwW9Y*eNuI^=?t^zXNxvWq70qT(_<neBla${K1q!lmuuI$6Wn&T
FUB*lS;J_B=K./B*[9Ly7q@gO2U[=
rGjqWr9:u[lOL2<tt7e|.%MBv@Lxc#
+gAsNaGp@V4qNx.$0#(Rj<2C+J|#IZ^9DuP8N?l]7DeTMRerg?1yg+&0b2t5:B"
TTLCWRR;%XP&![`y},]ClQG+E^`_^"Ih8K:5G7>/bFIaFBrU*-p"$)$XVv%(a<|&1o7$L#CNM
PR<=e_YW_Cj0Ppl,/&:oubx+f7rpFOgae/s,#bfp7;/>0HyD^<Z3n/if2eJ1j7NYZ_iH{C]t
Cdp=&vaP18L874glyEnGO)hrcU%>jf2dn6]/s0YW"WH]>Lyr7?i)DN>=.ngK2qOWoOH}/LW}fU;"7BrLqK+}ok<SO9+Eorbh1W4#jn[qyeGA&V#C)^MDqS/?-cb[(UteHFE20q3>Q^!D8S&n<n"RZ{<{iF#c<G;Tk9&>h".dRElZ<F!@I{/R+Lqf%gWGZ<?{w[0DHIOb_RRnfs6c5i(hvHga6BDk:d5Q>J^
C]Ox/$g4B]Q!hb/[<h$*c*)os
NqtfRu__t|9MhSYK@P[CSr25fN6J,DW11wMtM&@IxhWU(^Jm%j$"Ir(/^9VQi&F>E}8<v8v=Bc<uNq!heTq%:;?~DzgE/|1?;w9,&Jr+/xUlJ@3u!Ydi%XQQ>JEm8_SZdH01BM,$F<1>Hakm"7#SQ(<0yO?Nv-YW_9#aGAKSjb7JE:tZRQLW+1Cg*M<O&,D&%C*Y=UmgFw?_fuU2NdVYo@=E,`EN?*f^u^nyx,Qg8BRpz%^i&-4,!(;:q9$k0HOK5#1QW5:Lu=M7P[V@ls+^x]+I7NU+g`puF/<y1J!y:juPNen8/?N?Y,Ze9V65[~cmZ?&U8
;-vX[g]Gxy#VmHVB8Fhg
g>2k8
)0(E@F4["]IniTja,iw"c5*FwbA.0Gf"(0pbO>
wfX*Qm@2^0/keNiXOLT&8(d=xQQIS+/b/n^lxNX8;srTi9Vq(G3q
{u?R;TAba=`<$T2ra@%0?A-9F_s)},94/?x-n?!INI21UvJT8+JZ(K*+;%7^MMZh{EZ73kzm3scf<4TNo1YC<9O?^289J@n_K^oVA[AOt6-U0%lR7CH[D3h&iRNvLML^^wOWqT@h>UaW6RnvAAtEY]C>e4hsRQ0F8/zE5O<&Kg?-<DPF%g8cpB&r45An`BD(]?`7kdh+.99m@wYnp^>5"A@]RX%fK?|@):<4eDisi.cF^0~4XQ{1aKv_9S~:$aO6r]]TJ1YOu!>jqJ;[{Yqg~>X+8y
(wKfP=#`?XX^[!sZ5w&%@5&Y%:sM<(n&dRrPrv`[Fx_fEDAGFl[|`J8)C:eq:Nr}8$@>v>st]9nP,urzZLrq`!6,mn;Nm^jxJ,O3^+4+kJ%2i#IvrwuidmvCIl^Q?|n!,[/B<I$TvuBL`XF<W8:>SFCdLJ]f8u1Lh_J^J_ZJao<d;t#B8ex3=-y:*Gks<H@v!8
Yq;/^.g+)/1GMB`GU>E2bh
Y0./5DfnCJeQ$SGksX^2H(33mPFRn26*vNHo[K;m5!T>[z+ko
g}Rq`CL1:eMTm;;bvR3*9L07`X4<4Kn<?nAhA=N~@^S9-S+G?uuM]GeMuEX+pI`=^(H,K![dc#OEXQ$}vo;B,XPxTIAJ>%gs:mZ#e,nBfw$IixCPYb?gVHY!(}6Dt*yKR_*b(<k
:KPBocC,pse:F]Vv9u3dhTyX;>&#@0h68Stg3./^rt3#+8a)V!jhT^vf%@q-;H31["[aez=`Z?CXO,0)saQL0fd{<3;&j!.a?aTCMVNWh.m#F7BrT(cdD[J!ZqpvPQi[(Nupc)7$iH0[U8#7a9=wYuRM.e>1A,!<i1s;6wx7/5L~,$.<:2@];W9IEomC4V*"mQ*;]XJMBX>S$+x`bE3.9c^T5*kP!zb+JZ8vjg0bZ-$581PG.{:;$*BX-},I."KgZDVO^TO9uU(.4/-~2UQ#*DpcJ8X4k_s0kJX[
6SioVl":+d"=s0{_S%*Elbq.)gyy
v,uJDu=4,g`-6|muT@r76_:gE2,+llq!7_W;w0`S@b<Rld3>?Kbbb,BTQz4)$
C[G8w[T-9iL9xD5*5KD._igL!p4>c1MGW(v]Ph$^it^Nj"vS%;2fg{u[L;B6^3=+Bt<c&JxD7_<*S+cAr@&^H;Fp@s.uyB@Yv+6NNmtgL9s0SRIDdt1oHPf}WfY@1Cl
`pBqej7fDHirqb_BZ9
zgp@vMHiWxmjb[2HcK+IeH#*UKEE*Yh"exzM[/iQ3?G:LHtVg[k$<frV#I56=sK:A=}Yu*,+0EXyW0n6aD1"mLZsyXtted&5@qmS*8%>s.*oUo#48pu0@@DuGLnW?Z`,{`q$14OGB`w
$rLf)HXg.KRU<)<v3[5>kfXXR-!Cu$*]4je020r/!("`M:@O2nyM}C+R#(?Nqg3PpyY!+A`AIW444P0Oc#542lH6z=Ot
p)yh142Zq+w@
LI4"g0:"UD"O<P}={K}3%p!<`8>
KuWvHL=a@(?;3^]S|nJuzFP">Z>(H,oA",pdjKT*Bn[;|vBe4KNy1]C&s.$:|+Lu^oQc(+n(X?,,RZp(TSy!r
Qco[,X@P.W}.%%)khCT"M/y@I=W#=!UWzQK/ViY*r>o-B&;YG<~$IrE3.ZPwAoE%J_L!;7b07=/uy(1h:eGfg"RC1#GBdFbt9u:pA)"`KWTnP.a
b%"G4]&P;p{"Xy.ANtw,2?h6Wp9JUp^Wp^]c7Iaqeb&q[?JkYgJWA-:jXfX-I.dj6GV/Gbz>MV[t)5MbZ?fnZr*!=qQY~):l(8:b&+yDw#q"eYHw5BXUVFXAaRy^CFp/K<jRiNN-:Ua/37of!v#Fh!-_[n`PD9DYD7IE!f6;CIZJN"W$w&AsadJuj4QF!XPP@W?Vwo8--)AV=,m]AWYU;4*[bM-V

fw/v=DXF/,k,+m>b|MAXJ.C:JwqjB
Q7fH>+B/^V:^,^Z-Y.LyCGqskW}E}9Fj+ZR)4DrXI59b;a|SqW%TlY_1vg4.N#-SZ#vdZYk13K;m`C
AIw3M-bb$8
ASM^R_i^Tm[qbpC(_1jkr@6,lL5cW&Bt`mq[$vI%{+)eiet.Wc.]P0%8DPY2YI-ihY*y79CQT<$c"$q>l
DwOGd7(2Zym&;]Qv=R=0On/M-8"@`B|a~6k+.>]I2_md1;%]"X~
w8tcF1H//j/6Cv//=JJ4W^tF3]=y*`EhG^(O7X>3;GT(Oib!eXI$f2P-dPKeASEa)%xeONIIQ4cBns32ff~.ht=9=nT-D+!"EeKX*dbedCU%Q[3)8OTJLPG"E2V;yBMJw`M6I"U%C1}6C.W;,i}
y0}t}v(/vD,th2Ns/;}Hi#s,PQr`;8k<`Z-`0oi=RyC
fC=:%5qJUYzKAE.qLd_aBtF8",?b+]b1xhzB(n<MuNh:]7MVgHb^drW#rJj!jjz*k/1s+1s1}c5>MN"_q2zK&$Ggw9%j:E-4/y@$Iftf.026hiF)@;}vS%!.:1{-
jM:`jc=SFNTO0g)D"E7?=_
m1,r
"UmX"s-59<Fx_HYEU]!L;m*cA;#"W!!>W]kN)=U%rG&*?KEGoaIT@JXL4f2<b_AL989NgQd7@*1j*8xF)4^miu)^I!.-rbLa+TNPPR4y6^]*Fxm^fK&6&t6)(`yn7L+0eVCW=S/EFR
]RQ=Yg6v85w4u^uP8Mq"4RA"1g+Y`$c[S(M,~8/(&;L*F-s!o;Q13&)6v"[E]/e.B)`DH(o"H3mT{FAoDg!CnHDC>yS_QgCiEu@<wFe0-/ieG!uB/^}FZg1FJ?=#9*xo0x/%1[<#),(OW)*s&lDtZJN5CdEw;/J:88[i33HT0/,"*2BC&,HW(GdH7fn><fB5bz%8Wk_n0ua`E^]p6E^Zn],nl`eDg:M<LfJe>nw`OpRC>&Ho@Weh^pp+HiGEzIeN0*f$"+T!z"jCSz"!`Zr<lL+A],~QgWfx26f2rA$Y::lTp=O
to)xqqpnb%MIG?}+Mhtie"LlEypfR*q5C!%5[[Vh:izxox_(Sgg8QN*5*+
8?ab!"uLPhO8Z1P03f2^9w,M8[yKH_jzb4Y_VTp
?@1;CDD>Gj/c%U:G&1Lof
ovc"%WQ|$VPr7DC6w5GPix@E3!Z6CwZJv8E0ORimFV7[0DH*c|Clw()J"`eGX$^c!XVFJH5M6#ExdUyIH0+Eb]_0*bGax?o,I!TG!S<1ZKRx^E>"Dzw-E^mvR|DFZcXT"j;/g,LD&(4I:5q9J]uuLsQgPZ9,Ia*![]2gnRa_SyI{jbn]a5&[?,ENM(B]5T;3!i7q9QAi7^*7bZmtQ:GCy$j;ROIVw.jzgF!5dTxJV+33Il]_h#U%4OhXs[O:qT@eV*Lo+|)Rq*v#<Wv;oR!fsruopLlr:&.8V<[bPgTOp^YV$k*g:1UjBHJe
;A73QRVAOV%Ydu1apY!1W@mBuhVTM
B3p-0)`_3=j3i%*m]9>SKLb$!RE,dJUXBP._y49[*#FWb,5*sQA&,lzqoF+y$1oVY-
z$D%#0cJm:=S-5l$hCwRd3Ef-+F
9v0H=ERkGlF%[6-fs~RDfkSG_"_k"MeRU|8&I*`]d-O?4f.{=aI(:~VBu4)]4i!wR2L]V>L/#UMLDCj}F<^y;@t=`jUC.
rf+R&4Io
VGXLib:T0Rgfg4>/I&F
=)o4[nRxuVJM^h2<bEaB3Ut*AudaO0tB]3yUQq}">u0_>o^T49b5praZ<$yV
u@HD5,X!WqYp3p;F
5;Qu_+^BM=$RZdnm>&"op_zlC9!IEwO7"yR=@p!5e*>C7>wP41s,PAkbbpj#D`bA%FAb21eW
>asR[ewlpZlrS}_#dQSGLSDW
xA)iiKoZ"O~^X7B%%:TYroPCyn&7~^v2+Mm:gEX=_NHX"XpNCp4OE+]gyekbIN][EL9#1,RI
4$dw?Y#>sY&D^ABEp6.TZy=Jsds!8b/e[hweg
=geXDxd~w_<;XjgBTB(l;DOW-R,:g|jH=Dc+tEp2)-lI+qSX"7,^yiu>&N4A!k&a)0bd0(NcW[H@0%/];nZaw!4<q-Rq_D^rkuGJf0;[GQf1xWUlY*Zui~9
&
xF_D)PFZ;`7*:/P8Lrj=V*Ql.EqenrQG+v:t]$DCVkS:>]baH,"xyVeb%UD*,IMZe.;GI+F.9u;9V}L9iq;[8
xo?umSo?mD9N4XTWK5Fta]r?U2TA=)5@kl:Th"ax3E$2+7T&Y#nX<RxCS)_B+r.rWmRP4M1"tUw4`sReEws(?gV`)Lb_w@_*C8trA467!~kC1"oH%Bk}p2j{!ox&`gy,,C4t48QGZ|4Q4g
ZPL(4pG-C"]eX_qmMmE8bODFO;&lD+E$UN;:aYYJ]PDJ>g,e{1NO?-a3"(s"Yn~cWQPr2eALHx;$o]gvV1g29cdZ9dOu8eJSIX#1{cE:6O}5]W1s9XAm
,]=#6$X5k+pSMUHaPXFX=V$lE/j##3637y>Y``P`(ca~%/nvcKI>MTdx+wf">ymZM_Q}AmGZR2rWJ;KW-f!R05W)pu7YP0<tIk(RH
TGQrr[/%oy$pss_e"bI0pDwbS2&2>,*L4z+FgU1o5DRaKKG7fmot4byuJ3Ty0k?5,*[S0,?~u_pY#m0d;.XHs{E)dcf@YlS3,9yZuUwty2*|i{r)XOD2I&$cm^R@X5w#j-
0R@-1Tki}!GEPEvTYdj6.5ct_)Q45x#`qU8;v3]^ZI]#<KO+=*&2qM<kS5qgJpB>4[A
#e.BA>,SXtL^Ko7[*,NY,)Z"@c-I$_<F&]c+{m(SJhJ>O<95hq}Kk$0h`u:ywo9CkQej>/;N`)eJ6Y{cRlNxdXy]
L?ZCpyeaABiM+1&dD5Qi,tbETjW.0^C.N]BiaI$Pf~=-td9^gzawZqM2PZw:;O+NprDB=A8(v_o"pB/@n/v}H2
SvwQ4n|q9jqL}-[pr:ijd;IJibH$m<oZq*5sK,uGF??SD0Yj5$cFZip=o2MIDlR/uglELp).rN*I;yX4uFp)lZMX5nvkl1=Mk6;t=t1^W&zD.LYArx*N2ppfOXPt(446VK=$hK9[">N3lhaazZLF5?JRlcO*;%D+RXlt}![!*IHMG6dD*l5Pw,`Tkyn3dN1Mxe#I$D}mXp,Rj]{D12lG%=z2b:_]qD?kI:Pt2v2fwq2pw3XRjQ#(cMiDyK(7Xsl.EH25]faE
Yu
dbg7r-U$F[&)n8QRO4wGp>
hqCy[i`0]DsCy*W9[an;S.CMRP
[<0$Jx#?+paActuay7#4<;v6s%H8V%xr4%]Ff58A8saW!9.k/r?0.4r,<!`BZ6)@nIOw15(&;Q:2Gbbb,sZd:k|PgxoSiQ<Eg]ByeDQ?$1bMur:IGPaK^_PHP8hnz]B$p,BHu?m*Mu[?*T4&qQB1ClEM8Et]Y5p&>+WRUjQgQvFLZL`1](#!>m4uj1y([#w*"E}xH
FTemqs*7>0+1#YjsY[8oM%(wYM{Q2m%-%(JRqNG[-R&/>pwdKgkt#y8C2K&E?:)wX%u3LR
^E_Pz&8s3Ck(HKT_y]er6U4K
=63yv4l/{Y1o29D_|&|m[Pze(,_mB^<U6h4CEeR;[Muy4OH
gY<+l]r@
KwoTmBw-C%IPb-9AR6PkE2]^:>]X=LMYC^D.?I
#yTW4%%Y,K_WmHOc(-QdU)WDzh38=fa7EN*5=I{iG[}vP4G9)+#nltXwT_ZyDxK,EOTNw.$JC!Vs|Gm(2+ankd-_)#F`F[dC+YCH1k:nE-_/.t6-$?mxd#g9LEqlpu~"-8,)qRr!<G!3(HG(&OxQJZ4N/>p#q%+t!urv[F5`^nS!q-a
>%RVN#y<4=?0$CN-i?#>!SdF/:]"n@W%JjO^XG94(]Z?0!++91dM9/~L6I["e/Gxw3Tp=w<t?0qPu+z3`p12D1f-x)"<;NJ
%E];0Y*WZh,Y%dZF*?{#ua2AXX<jn?FEwxs-o,X4yxP@Ewcqt+zqxj6odTsD#f5G.g1=zg3o3c}=BeyKVM8"yB~GS,#rulKT5O
OjBAgk[}t~i>48A1R+Lr`2cFP)e3%)0x]i-#X+pc6:+CUr;Vd#gONqEdb$vpLHV|20DB3*_,yk8;!4gr]WV4-zX`!G+*GdCXnI?vwhLHVoncJ&b%A$ijR,;1&2a2l:uF[Q#c[*.v*$o.0P^f1:j(EN5-Bc9Bjsn#juj>TIZGZEAgOsRK2N,7?o>;=l@Zq1-O;GT:SpFpHtF$fp4-J6N9&4XgUUTq:W&diJpKZ2-+71,Tj~5MBbaA<YO]]Ks7VS.gR3bG@C"_jM/;
z"oi$fLfMi~u)*_hCV`K}*`"VL=?AFNQ!K#m#x~V6a5.envb*Ed))3|T|4/i8@_T{2xI
2j"Q<;CFk]mo*1EdJ^0Zrm<%]}Q#3mD4"-SUq5.-^h.
IC"V0l6Br3fUCL[2&voi!b+T]y6;)9#-RG-ze)`$"8j#SL]Wp*cO`]a^)<&>JKoWUlU++om21Us
6K6$!#EnX$b4#fTJ^uh<Taiy#B"?$(689w<SxXL&"RA8_~<zMO]PXWbSv+7P1a@"DS;0&}Af"Hhg(?Yu5|Of"Oc2Zl-#;L:ihE@[wPV>o-(E`5P^F/eFqG/<@Ften2Phm
uA/>;N*WTs<v#b)/Gr5)pCiLga"i5?Ch:s>K_oU{"3FL,1;egn=|wu0L"%G5Piq#Qs<K$>WE&rh
Iq0i(e)dZJ^nsU^dOX>@51ic?^FM);J3Xe&19jnlin_|lS<Pf_A"fW$u!UrKRiV{y~rg
PHWc.=i]-kp#mLK"%Zcr(!b/OD:A:p6R>f
]LD*uk:Af/qmV~iGry0<&aP%PwJ{8l#fL(G7pBTd]w9waq@{qa>?[~iqH6Q(hIu5se%XM2pl$Eo^&tK_$inykd/%8dr2yr^<wE(5*!@hTfYY20-`Vd^(r:aRqi]H`YR{`PN&KLy85kUw3VBE.hL.v>mj^^1RST;}1L!!yy]4wl?Rk:f^wVVXTpNAK^)^-I=#
A.fFR2+z%+8,
+,Z.:dv&/rHN<1WDVNH-8nuhG`/<wtgoFDc_V%UADg(d4.,caOq:A|EY6FF%(dJb:WfavR3r1nubdD5+![GF"#,|e_;c-~u4sd!BPHc,Ude&Sfw!Hn4/+{/=s{082<tHn^qBa$[aDC##(P#/h]m2UV1/AK!v&lj%<xQufOttU6PTVuc*=iRk17QBvWh=if$^*;nKq|w5BD?rH;bM;a0UO0^l3:!cxE_mZ&8/h:4{ijSnSgsV5>VrT<wG^#I.+YRKje8}j[qi$C7edi(Vo-C(nsTcJ0d<$T[ev
GC:StimK=jZ1Ik3?;K:PM;<[$RAxxDJcYcv_3(SZUHWZKe;IT3(dU+G325k3WsY"KL$Z-
lj?H4}Gh0aTZKG;b+VN~d/luu@&vEL0}A!G?;BAqedp)+gP3Ts(H"fx1GMtXr3+Fbl5cpX90t3nq_PYuBFmFT2m65NPD?.P^"<;-5.Y:@D:EqA#]>z_^Y>G4O_@,"l8N#kqRvb@21gC)#~PJ$Hl01
<9oa]f&Vh]T>I$C*8d34u!nc-=.TGOlc?>I;Dj0NhDKOKu/a0u/K5R`C
@d*R!3%`z>OU$Rs-9``C4yH7?"LZnA0:j&v%%tx5Aj~@T%>z"f$*m9W+&Z)_|,Vd8P_=DF,RYaI/-Q:RW-&Z05YVaI}8:L
D}e["]BJhFmH-BB7#joQjp7#>sd.O+#<tC(,"vp!"oS&Go&1NeXkELmG1:
dC0=J!/:3J*H(_W(%A&UkOZPO?No:m)?5ZNSv#<.2n*41SSC!g%ycIKTANE4q%T7-/q@tu>:[F/`C<D%<hD+)ks(hW6F_WJ9X%2F"v2-K.(%nl|NAkvW#N#RXv,Z:ZLuiN*<*xp-^__+MsYt`Y^Jtd(b;q5nW"7drYRE6@@_9ER.^,4Mox|h$K&^wgH1W]3C|gV+)lg0:y%Rbe^nCb
Q|E3KH[iEk(|9XCOx%$|L7vK$,>AQ|D8V<[}YI<u4E3{Lc3C9`/-_tq"n$((*((3DOD$hJEt/-VL9M2Y9u?6L6H4AFU37d[
wL3]Mi1i:0qvHyDeGBj|
>4|1(TmQ6<N7kh+UPd1CsfYkgrkD
bU-d(1K>?$n80GHH3D.Vrc9`<tv/.G3tgYfG5<m3P>9;GEL/oV*ddG0i,63Y9v&u^VE0o/RzhChl%+ep*AOdJ+F)<R%kE~S-$TlWCJdn?74_E!e.=;1oE}c)3+8*e>(0wqXum8YdM#/CSelkTdYpF4o`-0c6]HmCqup$(5^-@|T,!=v)_ckv"j)*,NX.=L2/j}anIdkshaDUkwhH@`I7N<QuVUI*rC_LB2sl(5ngiJ9|fCs5_M[*:)VJKxcftFdD,[ekdnVZOiu;iZM;ep;4mK-zkNXj.[Nf?]Rw$I=ju
"CTd5U$AiT/)ET9+-ht7WT#>XFX^R{3>Y;/1AxKfAI4X>K7R$px]"`pd1v30_OSkU,z#3.K=)c#[LA<~V.b9@StXNEK
eMVmCcTT0|pCGEwmgA<(9sGTHICT&wR[6w;GH_e{,bH#B&g1;j7b<H@o0=O;t^T]9}9TeDHB6@Un^~.=%OB="!+r9aZ)eg=J4Dv2hQ![<reVYr5#Ci@o:qSx00%T"Ol8d{;W$@ZkJF.:#yO%)8Ly=c@Ol2DSrI)9cT+yO>w_ASurR(/tS]dAp*nqI(c6NoKf./@b]xA(<zSjY/Y~oyEjINhB7L^olYeZJF)Gg"(1hDP_C,8Zjsn3_T`v9o"+[zPDOA[0:V.NF@N[H@Rs=[h0s!
!_U-sy/_eNkBaors],2oMaWTc2>YB$7kL3LG2l{wU%Js7nR#A41ROyIu5]tV.(q<Ki:Yyf3Us:mwfnL+nPkhd,&d?u,6lm`e/9Ew1YM-g
Ep_q`Pv(Tgld1[g[+oBb#.QE^TRTE!-S*O#[ifwKHIP6=EgWfYIEtJ33D*3#IqX0)Fe*J0ndI$I0-s-K?GO3rTF<_u)"OKZq(;$Mh*Pdr<N.(:gDL:gM4Hx$fw;..]YIP!/_M:=[!>i:X#(0a;=Hb&3mzZV4{Oog}dl<W[N(
7}7d(L1w#J4VSV4*IY$ste-2T8LFm)3nAg=HBpOBCi6?--/u5]-TP/T-Q$/mK55}vP!}c.6v%>DAq($k43GLA&"/0d:7_}NZ#+aY1;3
7HWp2K0QAGH}
c0@:?B_Fc;b0P("H}/@2%YooZJ]gn01Z}]!Vtf?(<QB>,Q+jd7w-kXI0`Ci3hC)p~:,GnE7

.w4!4oV2Eq)LifBe,X`4L693.Mx*aGxW^x(kX!i)._)#JV>"pT1Et|e.3I7rY;:i?+DNh0"PY`aCQ`6^<8#pg~U}Sqoo")feRW%dk0+=:XE

V%GE#__kB/vd^Y0)gaYVtN2wQ#E/bb4U;mXNj`)Y-I$C9jN:#dL8qO}s_0PS&BB-lxNjky^)PYBWBZXE8qsF0_%tNVW.Ef<<[2p3"*p@TLOEE%i-/#l5<IWWe&lTLg{GtpEB|dA(0+8vP"m*rGM]p,(yB3|g_*t,t-2OQ6.M#uuOIqR<
Hd#[o?D4pxr:]kb`(;<<s7rvS.,qZ^^H8l[#l
=Gfc7`.H8ke]Q`WM"z4<r.Dtmj:qm7+[0,]H)r1cRO]1k71O3lf+e8K#h8"`YL?<Em"A]RcPu[r2hDbtuw!35RVj$jJnML.YHW)jSTi`R*^vE/Y6/]MfZv8dTalLt49W$66eh*;IVyrQP)l5G[sK%~g1I;9$gJf<6)E_W=3$3/8lXh6"!Owy@(m/O[4<6u8!&Ix@s0YY(}P_+bT7!t=#*8WSQT%*BoNHdfvnk}<C6VRlSgTIurH~I@j4IhSTLh*miiq<bP[^Tfkd@X]*"4Q3`y53)j:?aHwamWu_cnGW^"?A<9W}e;:dDKA`>!>K
eV;0V(bF?j2!_VJ$(v7(,hTlu0c1EUy*Sy.4`Faxs#=3?)JL/2(ND>9L7Mfi>Z?bI:!D4,Y"pehf%cHxvWQhQC`5$]2)"ko:6W:teI=r@1HQEq
:JnB0`yZuP5Y,u8&"biS]]X)?CQ{Jk"cVh:b99L_GCSsQN%6mF.
5).G$J;5dgq18uS$jLBHgcTqIW[>l>3tLNCHem/OB##wDv!"r"Yr3
8)Xq&>GHF0RTrHXI4mD%6x=,Oa_ET@NA:88v^z6eI5)a[N+Kd{1U!jGgSk,D_
-A.}GuWj5>$r27yp0e
VRG:&a,5#40)IF.IP.!2Br,4O%x4F)rON`P+5$7pip%iDsGmyf91qEF1Jjv#Y9BOHTEQAe5;E495h#j*h;0GJl*Y%J96:rDM~iocss(w",#A%1I#km$6}&@H8mEJXgCA-*oFI^>>t#KOAS
-:,~hkcN>)*VE9To&j6@bJpE[1upFQ>Bpyf!q[E%Q{vp:QZv_L8rJ[YVI@qtPw`nw>KyIy[55Q_7,,bF!a9,Nr4|IJ8rSoQ]hH7Ul,qIZAd*1A*:m]-GarC@chFEaj<QB6H5[lp4bY-HIHqSQ?$UZY9q`d%cGy3~l:vSVVT=iAj-.@9x$,SL+#-Q>@YPc:av
+Aq[%Yp5}_uM8thE.&l)`$ch[9v$U.@)GNp;-#jKLl[NIg^mz!8;"2;C|lvgzB[sRo`"
.PBSb[f*FDj2O5n/A:9r1=8m_T2ghp<Gx)jf"$E=W{:Q5A-@;R)[G:rS%dWCE.;/;?)h#O2]2CHxC{fEn%5O
-P?Ec^m?HO{
Lv$!7/tTf=u#{[b4W[)<wU@kzUJ]6EK(Ha*x~gys5,5;9ck/L*TF
;X#8N|&:fTCnhF&),SD;uOEOf#j2AmMn0hgR*5_,!]g9h*8ulHY1^lmX*c@dGQI?f3EWPZWfJYNS!z<hvV%/&D2]lHy,tRxpNHJRB08^e7k~cGN:a`W.7d<So&3etb_q(8io^Hm/Ig.miM
s#uZnTA@(c]pFOZ_aNa3BPd=:[~6N>D<Fr.Xos{)GA652V`3(=&"4T-UPth@~1R_!uVYpVf3:MV=uISsXI@h:Sw!{Cn5ju-j>b{bNw}p|(IjYXwZa+4t{ZrYh9tTB3M;o6VbhybZ8x6AQip/]PuSr0zqH#A6KG>wz,RjS`$8RlJ(yeZfTBAJ.ToAv5/!&i56!q_+bQMBSqFOfac(gcud)(KxSx`WOqYo8vl;g>sw2P*^oj8rh@9t[L
N~yrq$aJq"0RXrcK<eP)BacuWw@/.Q;{]]$MQ=gI_gBUkN26-R>=TmXQqH-9%Q,_1zWsV#MV9,E^Wua$8XySm!>*hnm1qRb]TeRD3qu9()GB61ng*naWPc`7pu4QcPbJM4anSM+D$h**h[BV:Y@o5`#9sHCQ"QN51KFAMyjA?jpQFw>!p)M_ZQ
tl"-15w[oq2PMQ=B^eRmD5#O8u2_O0J#1?PC$Xp;Aq;aBI,BZH~Bf#^>wGd&chF0YuZ:T
JCB.)F>GB*Iix*8p,C"S.V8e?)Rxp5C5{h7mWR@9=vD.[J^rk6?xx1aVkD9/{&o1HDgEu@<l_E!1BKiR*e**!jVX+-?sv3^9
F46^&hlJ5k;UX3Et)pCQ92HK.|dD^_Qr&A:(NP3R
FL_iQbyn#Y02u)Z8WS%_m`o^wk$u
btlyv-lSni@_.bx1)Y==8Tsk<5>3N{7mTA0a0<=SjAqY={xbhG12JE/=&Vd1w<DmCSin5U#QJ,E@LgT/hd)<=V(vETj9qC%*COrEk?sE=owDCY<UZ!j8mrT75,@oE~vKCx4iSGpkP=ks6Y0]cSe9BYyYc4&_Or*I+u)!-$w;=Yivhxj.-LaR.?$CbVtEQ/Vu+LaX=oFtV7Kv%7O}[z3O0&3PHO)7;-Ay*&fiOrOCbhP1BXU.;XS,R2[K=Mcb!de7c9>"0E8Tm!Yg$Z?]=;o?;F>w2mN1^*>A5.YSkqN@/PU|>;UE!a0gUdf7O"Jhs&8zL+CD9WT576/R
13a>,-*frgIjedwBo4ba0V<(Y]EsXyE%sS+5}gaErW9L*a:JbgC%rBVgws_@Vy!j+sOT9;2:%s<O%5^.@iHrz5,!}s9<y_O*_oH:,><bg$J=7o*/KN^,?PKWDInr
S+lTjBjk)*Q>VU^zW-dx4vX/?MWD!ogPE{s+aUFD)]^0ySG7TbZ.C1`w$OP-OjO912Tj:S)-Pt+2g^:MZjG3)4>_RJX|T(M}bnbmZY4?T_V6C7n)T*uw^Rbmw~1nWQE,;ei*%98[^$&rFx*p#GF((Z>>lHe]+Oih"o^h2vN3%L/M@|TJj&x=Do2rQRk53B.J_-TC6NR>RO<H2eZMdF3>^gV~<|]o?=aI.t1g5[10S9l@j<P4w<xt[ew;=AMcLxa:+wPU!j+=E(`3Q?jG7r-r]<?g(!]J3g@:Hzt4r.S#gcV[>;Y)WRIyVm!CUl#YrwS^0~s1(47Tf~>y46Tc=_u=Z{Cu=L`YHfV#tO6./?!j3FjEs.nS49E]weiPc8B<H*Ms%sZYX0D`9l9Mv|gYJ*qUp`>*NhGV"6DA;"#&:?eEoXXoT?*i7_.hRwR7p>1OGZxZKlu^=#LMKe?r
h4+u]2Um+5C;r#^0WbR*m)qx-<PUP"8a
t@_0?MpX=@,L9s-W@}&|
SscRUkT]MHVr!:5Jo&Fw8wl%FJKdj55?nF;eYAW^vnhlwBM^7*XY7!E97U+kNk^GGNe&iV>=$1e"
6@4jnrIN`#/ELCwhR-86Xd4EfoAJ^W0=_
NM]FYv;PC}2]4e0KXRe#&!,Z;Nn#Q~HEeP&-
4*M7<%d#b!o.x/^YxwH$.uwtr/c/Wm1*a@h7+"PQ8wUDI^5Kc>3`b"O7wJ;ykt_.y7>$P[FNb]R+H`%8j#+YP2+)fZ,
GuO]aSrnTeIX"Q:lIm7Yulc0aU$ITZz&yrFXR]9Q-40JfWL5S&Je~KaM[HN?;RF)i5,oLTO_
Mz/hc<=mSic!fE:!R?^4/194l=&j2-s3?&,r;
9Y%F2kui&k(/oGXT51<lK-f@dZCjrJ->f&id#}e=(xsIr|.$vu]xvXGSTTW~dSTb2)K*-=:CnC41[$1SS[_S=xjy`I&^MC0r]Tv5QD/jvS@xD!>lwb_L:BSU-?%sDhUmC1Nw1$)cjIJg*u<;:pPWp
q0o?NBE4nAb2,3&.].MUVhj59:I=n2;2nxaQuZn6xm1JID0@B@rpc</hlC^Qiw(*IBt"DL>vTQGI31^qruOiB|>mliUa=8Q5@Y(VZJg[h0]d8)afu=*5?jl(;o;ixC,e&ulxUma(#(0Z,yXFPa.OnEIxu1Im7YC2RTI)MTj]-&FOibZ[c(]3WqZg@h*kGu&$GGK5KxWAt9<w!2U@jFK2#hIfVr3-t^TSp6s`!v3!P[KU`t-HGLdt3=m@04v;Iul2olf[np%pw(5|i^bEMI+3Q
v{<@!E^DM&f2y0+DAXyD3eJ2UGGu-fI-0M"`>(kNSGejmmA%19H=sXK%>7`u";c98^;ukWmQhs.VBK8;rT@4/+CVjos@<dpzDz,EN=0Y<[kF-JMzTwdYMXK8,ntf
#
:"6kciT[=rCX)VH^/&>m0Ta0,g)V?4<0rZ%^o5cGSQ?xLW9<&,Vli7jV[[>kZN`X@>f?~`cVye^h.B)"N<;19=^=UhVT:>c`TOe6KN1E(q,Puck[t@+Ns1An<&)BOq.G8J,tynIYmWE>"q5R!4&c1WGSanLsm]W5ntZ
"ajH%xFlshR]SU=!N.[OLmBH
*~q)?aSs26<29csgu1fFPI=?dU/1QIrG6`hb8}XYk:QHYBo<L067ZxeuZsG4laaz#kB6I{l]wQo"h?0;0c4-N7c6v#EnHuK5+_U8W:-uEWeDlOBVvj"[773F[1cDONtF>w4onPM!`NKP?nb/IO:X;Zu&S_E;^R5<ys-HFJUt.B3TiQ#<IA=qSad-v|JY+sb<"AYDxWsQp1@DVcp/wLl/4F*g7D"<E$sWY?CWD,"g#4Y:@xG-Xo=sUoul_U29^a5G>0Z:mQgRN`IG<ExGU*my0l+kDR8U7&OzRQm4;s?"!oRYVH(p4CR/)M>5bRrnF3SIiK*~;][%a)yYAV4bD@%n`(wso1qqc1W-(oayC+>XUb>W/r(][no]F:*unD*vJrAZ,3<PF@C?ZOKD
_X|Q1vcj|j?iQS_,5aIP?JHi#4=CAn:8eRMH;kFy/<q2I%B9[UO4Ml_IGI]GkK<a~*wdVZ^ozAas=Q^KKj}XNsYN;Ac0rDED1X}NYxsnIKO&Paou2.UfFTYFC,pyy8GJP:-DP(gkTNSN<Ru_yZ-_R)qHTY3h$jRrtcH+x_D^:6+p_`A,3j]nKcG"XRlSiXuAN"O=T_vHE?%z%S61"PT1b?"Aj`_RX@F(Wc:/z1fIeGo"51(o
=D@h01>#N=YcO8+_9.Q-Z]u}O5xE%m3&-$0sbUL1wIKOix$}
2JJ0x$0S$j=.I-XnGPkRknooO
bJt4IqTQk=+J-pS9,gS4}e/s6)!V(Hj#Z(;*2P870]x%3rPK/M=ge/S4^aIhO)n<YNpFsp,-NFz.CXQNKWynyrTH8OsXX;.JrY!NiE&.)</0$m/Cc7#4+`ok([FE^x2:_O[1}OW!=6_w/*Pkf[no@VzOXaeqXQ-2X4.UNAfgswt(~sb;qS*W7PbbtkH%gH2w
lvvsECEqokhVhUb<RiIYwm-bDU`aZq2Apige@}c[+H)C].4B?xG<EN3i6B
k&s3W:@(*nsbg*dh8fa#VVJ(n>U>FC!iQ"2R>2ahVfE!60T!(W9OX?!Nk(bG%S.E?Fz/#nl!GuS/e8MBdE0_%#<MUW?VtXrH.C3O
5bF#a?Y[g_$%
%W%8$r
*RJB3NFC*mle`tt"t
Jgj:h:I=3l7DcD.q$(C2okWYq`mCb.wW&d&8GGf:)?LwDWVYYQ)h4+aqGJIV=VQfJPj-NCpr"^;J]~?DEc@`_^@41{[W1yk*#j;qAXWy=6X8/a8pTlCq,++
3Hw9Za(@g]4jG%fqngwWLr=>S.8+TK)]nR$P;7_lW(80k./4%=(8y@[i_AOY6M=@5I2M%gN-*IN-19<V^)wPF;OY%92)#|bE=,mQ@4wclr`R;nQT!I@`*:sTpjkXgajO,Np[gO8v&0Dj_@Kx=r+8`~Fw
&?[k#$Qk=d|f1AM]Sp-Ashywvlc2blIt/A?2s]40V>:+$OoZ($!S@29*+i|FWG*g"%4c%ui8/HPxeP:+heiL,G&tId&q<eeC]ks0EjHcr:r2Gve]h%rGmP`H>]5Yt[C-&(_iQ+dbCoa_D3JDaS0x%BqxT1&08g:)QQci5W-)<nt)E*80!5pm:<ZjLDK]qAWRIWmPCriwZnk*$b;
|<o^;rPJF*4Xk/tMb85C1@%h|7%vy::4*[o>d>zl`//470~F*ne_1-z`f^7O;uX!]a6f7-d^.6Me_Ex95Y6E^RK2]F>mJ.J,V&t!;UJUu*Z3AkDe!/wet2>l{637YKOy:y$`*wLcQ^A:CC
NPvQ[`Hj:<rOfZ*BvCTZ_PHm
%-y5g0|!FnD!4a<#J>y#PFG`+jf#KX!o.VZ?Ku))%e#Oo!{$HGQp:y>)
0(7(ukM:1~xMCgQv(dD5]hR<PlK-qR5?H?Mx!DirxD*OXa#nmRA<uH64]e[*S)+zN`B+N`[$#LfX`|B*pKj2-K>nr!c|qhc
twZlJ:r>Js=r-rarp#5zjD+2;|eyJ3hpm}I)Xv>-#BB-`k4~NOQ|#7X1j9WdxcMlj&Wh7ia@j[#Q:C"yR8Et_Y[%+y9sE"=dJMvxGNc[=9kScY9u6^V
@AeSZ$Zt=~,!o4$By$OJbWvl8GOsBmuGh1f?xi_}q<)i09pJ/-B1ug.d)8Hr(NYQVh>=[I)
;i*$o8%c:(O&)NfI-MuV+s?*VRYvW4&z3C7i!m`ny_^Ff<sXj/xI?&H)Y
vS^3Gf
kO%+DR_?6!6psfWkpC<S8>aw7
?FO_85nwrS0FZPg^A7omgXmkPGPc=./H:]2B0^*)<t-<2^[c$=[@1rP3`)er~rs"|i-o57
X{Y"g~@)<ejus?f0X+l=hiw/kbq#Fx`pw>XPWDnDho$G.>D@63XXQ+tOWh+twq3Qyv&a$3Y};&vB^mT{^/=x4Ug>1g#:qj6$MP21[]pGvi>FN#KtH!p.l_3D>F3pw$&u!O=_aNXV_iu[/M&LjC!*Ycdq:knd:@t>7OBN-(ykl^ou]&P"S;Zi`gI.*nxBe%hOwO7VK3alA=sN@Z0<Hy;yB-W3f/E"C@4-=-MwpVm"5p=#o1Aya(c2ucO3iYGx]r%.r:ZvaJ]hm8*~Y:0B6,Z)5)`um9N@%@bO2u?MGzJ7l{Fhq]MC0nQL]Y_:2KQVFNx"0]*JwX#`#L@|o~QtV0$kma^U9m/#]n@O]qjHdbU<E|
SkxF|or(*kWv]H.s(#E44N]H:*{U.qFF|sf8`m8b
%R[G#2?WZu$)tOvLn[*0ay[32(#S
M,5K3Zdn_yv[XA:KBxZFw`:F9ktF)Rx;i.ew>c0Umx<Ws"]A+fTby<ZKYc
Rlmz@+>Nc41*s_p=]^hnrq@!V~]Uxw;4SJMp<IW7)}%>`YA4,~^Q:O*)rhcb/62hWag{k&z%C9B9@hv}gem-^#ocOlX=./m,br
}w6[O@&h?Xz/Gm!kE]^xBkP<M@y?xn"3~i[F|c?JPMOZhUT6;_ol5<$o/I~S*Q/JTO:?^60rh^#K~:VMZ/?.qRsWPUMneX8#?dhLB_tBee"]Z.zvaA"TNGWNqZ9GF`XSnp4^vm.6j0YFrqP#:eM=^,/f&y3.{f5E,[W
KB!J4:WNLX]]?.4t^dm)JRfRuC74:<[8M)sxwN.WQSj,MCEQh:M3BkG>1/vfvAK5"+N(v```We58&ml]C2lwSbX<>%`(>le
0O^ZcbJFj$@wN:JnM%ww;<q>P0%G2YX-5:IaG+/_b/I_jk3:Vd1_++XnFCVaMSnYc^F/,9*HHC@:gIihi&UU5UwfjDx"f2G?If8d_9#)8%&>@_ldR^|TP
hD-!S@mIyC_SJY/^L;PRy<A$)VcZ(0WQK]<Sz),)xNxeqPd
P8|i[9^4;%1Yl=<Ud"dkGVFF:Hg;b>W#i"},"Z<<g`|@I)3N3J-<13:Sfd1[g*p%`*BkPA=EaZH#z-+8xLB^fZV7>LJn<EJ.{k!gH[xh&ld(g#+>-G*`<]@m-#tFg`/:?=5Ms-?AqPonXs!jiFg/6?oo(
Qg9#HTV4EIknk_Ytl=5C^D}!`h]c2K3`P&@O+fB"TcH@NyMQ|;.RHE-@}GkGY#jI}]!kp_nK^h{npVxb9*8++o~6=PFr0D#r_cC3uU)3E.Hc1kI.Z@jij@2b*8z==9^(x0a_hqql.[lFN-?=zPjO9)ktLm_8zplKvN0vn[-(^ob(ynqw<Rq`>QBk1;J(Go=d6*9N7mWL+D)L>/z(koM:2f$vQ]6=G
SKQIS18@:D_D]mtazwgr+l~l%g|2`
KK%JkHYJm$<]$d8kLhSMQ$[g6V12z%jw";&qrEm4LRk--FRwDjhJV5S<Lh>>Yuk3Us@j}(d<RK}eCI_Qh2RaNYw%BeacF#;3uLe*EGvLi(JJ6W"hgZ%ndv(/_Ny`uIG8irS>?NXcy,LcBk+6xV|TQU3C)BX=>+n"JpYLlb5Yz`R)W>$Qmd~82w@NXC|2=TxLp_M&7Bn$D$_^7ZjC;/m-""IkBa=>
Xj@hc(SUu>U?m6@c0]RJ9=.3V]d+`)TXxg-R.T&^5<$)&1j_p-<ma<,=xP:5fb>$PDQ._HRxepO*g+LFX`h|y&4f&K_JXxL<%"oTB2e}SbR:8w?t/nH]d]q{Y"lh?F7"f1;$[Bc$O?j{-!viR.pg^d.XO-7)^m0fm#EV&_vZ4|!&lpCVs-"xGt-dwC=W,EaG;)&GQ!i(cJrKZR>>C(&o-[3V+.bWW!q|MMdS-5!`AhxX
6Y%S[)n"-&/:O?K]s+8$)K#,$?K.59Q1}*tL`]XB#(Wf"3,u[9C:Ln%LvJJ*YD10_SX:r/Hv07DCN38)l4YY2?Pwg=B4
f|v)(<1D@F%o3uH",@r}2Ylv!{]`Uf_+N;$%GXvf*j&ntiAX67f~X5/14~-VA{Ps!dUJCo5E3?9Iv3^]:#6{VmjC3zKE1/wuF537c.3-I%[X->.<=n8<ChxLDQT>o4ql(B$F.h
~w)w8MDmu":(<H%I6XlM,cS.65_0Z$SH3KwJ:x74|j9[f>`,0JRoNUD-zs!&bv+s`>I);<YUS;iPD,2TX?]<K"`LsJG,c"*F"woKGC36DN-R%[:86el^&!2WUHX!hOPY3kv[Vdf#KEI5VxYjzNW4I"A="vI$YCW^l/+o(&MHu;G6v#h)EiAmJ.JN1&gcXE#nxjse-Z8.rbc0AhHpNhYNR1c;gO=5U0G2c@R<tN"+nx$N7$9X2Mue^jv?Dk<h<T3q.PET:/Kfo"mcV>ENbI
PTR@
F+f?"(d_y5Ror,n@d/z&rj"v6@6#=bsx5*Z"%xx($5|4,f:SzJIsbbN<rBUY7uANmBdBC!QFZO9?;"^>xVxH%m&QTlRp|?OK?tANMMkZu^u2Zu$FDbWu/&^7IDK
Il+FCcswDB3V^a3K|d
Wu8y>D/i9NUuq
$ODS(Jrh.!l,]ZMU1l=w_NG6[&>,eCwEXCbg,vjCrjT^[t^9wDGnLjM0>*4O6.L&&x+Q)[d7<^#/T?gS>xRS]mB-ipr_[fJUv#M.Of$ba>3|Zd@)#C^f.EeS0m_7YJJ}IN7eW56
,88"7LR%?I5kj|SN,jnL(EAul*cN0ncH]_/r
Y1]-ut.Z3Ajxq3nJt]nBOH0ExQYJd0i#,kM7wupV%+LR]:nsq&Y:/5xndH,+wo#v3^5CK^K:JjR:^q7BiHW`Tvco+kJR.W8?jQ*!NfaM+tF@iJm7;_dinJs;NyesM<JJo*5AVHmrAtGX,ed=5K<u*sFdw^yB?ZZ4EL1hJj/*1$~X&Z?50LP
6"Z;RxaFE^@)DuqPO?[JCZ$qi*O]8L2;n#@sn=jX@Uf:HYZ0i`yD$of4vdvk+S%OM3HIZC|Bys[
b.rf_E
YA]L?:Kk2mQ8eU&mNOmXmh6l.!tWyK:jxkcZJm[Vd3-,`8`_*9G)A
pE&}Mzm]w*YB*1W-1p]^Pj+{2.Pi!N7:K-CsQ#"a9F8R"~W[>t1.S2-ByVIS3r6mbd,qc%6#J?T;0]txCgc&[3,>X+XW,<2$!4:@ykKXv)5D6v8+%6abSr#H.!type&X#0ta$3WU6h`X,K+[W%mf#e(jo1,aV^Vj3
yH/cXzi_YnJ};nx$#13c.]fpm$W`#gwJdHv(87(qP!kh+~d&.>F(nYO`;-w#9>r
JOQeo0eJ1%cdY(7]=(b#p~
1-
n$#M0T7[0k8b)j_{Ph)tqF9ZDA._mHD086cN>vy%c@=^<
nr&eO3<v1i8c03.DRBut^WQb4JA`7b_-&]04AEo7n%xdipI$C}Z~iS.<Q;dbWw=`O,1+Wdg]Gs_1T;omYeeTA0:h?`!^BO0"7#Y,%;*&:#-_?;Ct3|cyEWGBKMy:OgN_n=0L
7/sEbl^69Aa)_Ph>TN"9N0@9MQ0DNk&D_:CQuLcDb0U]%L*i+Mt!|kC34IwYox444I.K}Cm+UU>2KN)1=5ApP$ti=U.;y&.9|b"1Gi$4I=~e&N#Fqbtja0&tEA~c^b3[m5}[_:jjf?X$%KhoNCp$inM/nBnjqiNT-t>-D42eaS@CNqiDW4xUuG:=`H50jmUlc!Ml{?4DoAx.+JX8[cXI362er:2]iPdr5;!wc!-33g}Kz9$k5_lX6N]45v;5ak
Li&nyf)GbQRrb&"9d8B?(+XP5;rCT@st3x/y!IpPueSO4aDOTcUJ(3?"T]`|YLJ2=8,LQ$$?!II!S]rPeW:I"e]|$.)$=7BUMpf8$GE/Zn,x/bsuCEJy"
1?PiNMawX/#b,FdPPV(WJs/PeNAmc>S<I_uI/Xi]nTSW55@YbS[:0]cu"*-zlE(&X:mQ1x?WYjo{jCy/]-57P"AdX@]N:Kb8Hs5L=[^yI=!uP(ID#&4I#c3yN*kBKeIGQ3`_>[K:*tW[
6:arcPnoL[E?.CR9ZQm0:8W!/NP"gGskynYD)o*=u&oD3#1KOqo."<6qooYcC${`b9t[2!jo+DTlA;pmH+/miZpSRS:@q8(g$t6=tY-KijvI
F*t_y;ajLdD,$xVIHeuCU{eRFH"Qpb("[-C;vM;C4CPvG2,z,jOg)%RI?^cW/EA51/BJ(7-n,;J/;W3!I3pZ1U.5k4N4Ed;/x[`9T+`fKtZAv.K;THnXB]dwJgHZKWB?Ld)u6kCqPWwo$RnwblJAbWo24AR%Bcj=4:CP#;jmparZ2nKYjAV3Q6e:pe9[CPx})51pPLd~:Qt|MN97PzNCyK$:9Wt`$H_T"iiw?,4#djktv@/&h<dk[1hU/#`Z0isweZUmi[de_;A^9cm!b&y(e79QvvuQb|65nHC-Ga0UMaW=H$0?s&a#]6%:-dTekM
+f
9I!NL:,XD&Fz/]I*ibj6F7-*cX&Hg*9PpUTxKyYh0^0<eX4?lDUZ_%E@CNf^Tfp|$6lk.6(Lg/R7+Eg(c%cx4dL1G3A"Z[tT<
oH9!$=29X2g~ZPTlmXvN4b@8S&if;-
55RCou9W{6R
:,cND1/;CLUPG6SS3,@LV<NGAP:uNR=8}P9ooH%f2!x
.IBnzHzTlO@LvJ@$q#CJ8W:9<//RejE4mvbNXI*>^`ej7K`YkUIWHry-J/Gps5r`OmQT=;I@NHQ>+)m!5h$)&[k[94_GiyrJZmt[Q,m87e^2<,-OEd{D%u)l(6ee%IVlL*Cq/X9ZUsRu
6:gc..xk!;`epN`?xX:3<60SsYS7`v2mRBLER{7%o]T&3mw"E6jFn&F~&kvD6lhB`+_/=M6u>G1h%3"C6`eta0DK9(jfN-!>E>8BP2OIeh4#,0)y:.W}A")6a`Mb"JpR7V8B&Ftx^^^|Dsbn0`,B!&lcV?4:B<"FQB8&l
`l-FxWH6m6U)A<jC/Kf=<W/-r6o}al3[MG4O#WZ|&OCL9nZPxv1@SU$tHP2CR_Vz_?^T/_JMk;]|6)G)-Xv=4,8C,041
="
7)Z_TT:]h>eRb^PzE"A=T(CK<|xr_3d+drJ|GWL8&zyX5S[L&FPh>OiLH*^<17k+<jVh3xgJq4D(&1LwWx*wP|^N&G(7$5,EbuqE,wuEJO7CCU$cIf?DOcqCh5o?XI(thj6Ch!GulyURde#iw%MLU/-LHnN`QSo}@i-},(,2k|"q)yK*gW"R.N8Rk%O"$xr5al^7!3+YR6PI^j>^R%1-rd:``^=pi56Y([D+^f5#S66^.NWt`3-J2kVn3&l,
IwTZag&1?sht(@XR]18Ut)PQf2J%W"Q0x8GVHgbyx#JtJxm@}dJ8"OD[9AXoQekhdyzlC-S]GV-xy=ly]A
)6$0blXDM,-ts[#-tGJ
HBx{k~bzP1uw,z9`hhKG*vx|U7(rKi<EXi""kO!f"isti>tG-wdvpjTnefN[-G<yN_!+jDHFZfAoP#`2qr)@edJZS)V8m,g
^`GNp_L}@Y<<X*^Zi2g)i3V8kfdp8W-P+zS.qU+E"lOi1X.B/[Thme"H1%=b!`b4m"#n+A5KC:o1%
D,%W9("S
AdTd/wSo957_@D(%_H;_;Ht7xc~m_Yvyd7Z)hA|x~B,yH/SGa/f_(hdK}rE6&"~waLjb&
WN</#OM53nbVp%aFT8l8W[WGIh<vP1%h(*vNg(414mBRA6JhTBw/[N?"J.-NnEouHKO-6/N97ShRv=jQP^Jj#mWX.Za1+d@>))pMSq[Q-rq68X{_%LS5BbUp?`{&<qkl/Madit=p/TWweUjk@?z.X,^^Q7"E-m-HS`,7t$qO39,3;G=&|:TLbuD8jnR$dtQNV+uQ8#!_cTfW9=mx-K@P1N0#YE($J
=OuX|?fx//`
%Y{2X.sH~XCy#U-!#O2Zx$t=*(q?-iRT8)@NBrL<i%}5UqIfGfc=FP1?[F[N#/Q>3CU5=5L2!F9:XKli9ZzQM%tV#
UiZXd@/fX0U3R#iiS$8ty5o=6&kNYr~<C3,;??)$d(l9"sB&Axx1|LfQ2E%V_Pjm._sD1U&3;8<XB#wr!<yr46RO}xL;zfGWsPG_*(_g*udpumEO`;}ReS;1)>@<pm}Se
*bAd|)r(G*aPF%mP~h_lrwJV)1>1Ora3lv]0cq;3zu^k|Sfg/ePvt@[h+&Sx2eBf+JHi1=Oa/2LcPOc2+VJI1ucwCv{<?PS@>g@tcX$miWH
@t#^vtI_gseq|=d>nxx;
PxxXuAd?c{F&N<b+a._?NAcS>!cD(NAnSqrF4[x0Nd278,_]MV
b0"jOEnkB3.SAGjvnBDQjgZL?3"IaI+&EviZ-ih
ZHtt4R3;3V/
_kT(mUIq:Wq#76?h:<N8gjt8.WXD7*)z%qCB$D%o)kUQ=C;S)o(4t>|`h*aVqbY6HcPc*w=SqwA)h9UZ$)fN]d>jI5}h#QmOc"%5kuu)ZZJZKS[0NaDCxW$q$u2*JScEmqNoWw<(Wxe<
)qh?
ny)I_M/9/E~r9Xr>a7*K%%
`G)K$tOQYvD_1,T!#O#+`<l5Ql<X0IDfXe8@Q"i+/
I=40M,
3M:CCZJW:RRpRlXD#ZO&MLyYK.MrIj%<k`r/Wnkh!.gdW0T&9O^6
goA[!Qy#wO8N]qfz$Ar@?Gj(^Dm15P)+;fU-HP@NpnoDk;l_o506oa#8>q[dyNdkK
I(INv,a{`pLs
+na!Ew?(8ZyryKM;Y/`ce%OE)GwN/rR_?#SnLbcg!fm%Z?,PU-fl"BUZOgBNZNeJl
7AIDj&gX`;~i32{hVl-mD&eBWO~NWO1+7&AM#!muqCz=q62a[&kD_W=Kp
.ARI,E&bWosxX0dz([7
wB(F8]9I,cAK)n^NC&>oT*!HnafZm`Y-m8,]V@lX4R!Pu8*bBULIDD227gl.PMAwP#@_hE7J|umi]O46M562`n%U
]Cq,>&#xy|DB0~T8.r9)CF&f?>okc=`o8[s42y9OI`9TqQsGmU#!]@9SYJ1rZP,"c;;oA]k)6^sW8m5d:9n6Q.&}4?/vR&[Q-Pu>p~:FhQ82)UkH_}&X2Fy^*L&j-+c1:,h<2XYUQ>QkTo#e?HbSRD6}Asuj@_#&q*b*J&MP..W*Gyy~CoUwA#e8TF!9QPAtkWH:vK*+W!%8td*c1DD@y6(H"T>/.u_oIFo]><9XwReb-4IxA]tdq[^X&.Ex)VosxtLNMiQr/VCW%7Mbnj^q"8bjX*4o8JXOE>yOh^h=_L`FLao6vpM.J+-{D27I[!(#ox]}P{U{$fOUfIf7#t#S-q_=/G9#^xY,!fMIv,j5_6,Df|Sk&rK6i678uYSD8RB5NMVV(_/(o:$>+
=4b8q7FRHkAYgdH6x$SFxdv?0)L$GJvfc7)d^?1U<s2AuL)Bl1S=PETtbd173~.be+pfx8>;<2qNpk8{CPAtw~+NJOZA(cL8?~/$;51`?6:>yOp0`&B`mz1:
NW{#!dQ>UcmDtE.SUxB,
+sUH.=#[(NYhq=iP8q5^BSD<:EHCka>~Q:llyDNVA73^(b,u7T!K&Z`b&_Mk$t
tY9":GXYl:fE,*OvNIW6UrA&j=;xt-T
:/4vWA6A%WDDg!y6gMa$kf<7FV$D(Eq,?&yBy<C2!Th.zy+T4
2S[H$W2e}2&O17h.w2j.|uzJ,.LQ($RW.TE9%_v<O1CvmCmw12;w8xw?R7G[=I
6ysl+OGxYII;>/c#`60S7`Q"M1rDwaiFin(7n6(=U9rw.Dfara*k,JY.vp1>Hb"8I9<Msh9]SLK(-o9|Wbo"jTDHrQnAS
LG8WW?YEWm])WSg~DuK%ZqQ9JHM[fONCS
cRd/DEmT!?jfH+be@<!0fP+e&gFrpB#3FtL#Xs_G:zy?gZTuv,D?#.$^-v
R*x!dtFdE1&=YL!6ecuS]e^PaS;nY!%Rn?;U)(^`k!%PlYO<3r7gZ?k=un`y.:HV8ZRI*3#9K],D97q:nP]ivc`F.+iT0plM(C0kCY!mEE].N@}GuDNMv=cEe%fp=cJq`2OtMl{K6UbG:ooqQ2~8i
H!SMdeY58Oy>v0"hA2DHCI[BE0u.Iv_ySQcJ[:|M*y?unI2o0BbdXo~).J|b?6xt
Nj7vA_dKmyLF4)=Slq80NALM[t%=Qy
f
Vb(x4h{CL%Xeac`Wh97J
ib0&e>YmH2
RX2#@)IDTSD@:3@_dH
/wC#2qCiZ}pMb_,^N]%66<-`s=J`+cHAR2;zrM0%ZU?JIURqPe!Nr22=NAQRH3p}9(P[",3Eb6ZPO"K-n}"kEIDH5?6p=$#07+hd)kKC>b0<
d7|Gh%,7<75jFbFP@Df4c$_p]-#s]U
ET9ZPsVJ^.1?lUf*:7kn;ts?or$3-
NC#YEraz@4`^*|A~[9GZEXj:#1TE!ft3VH;}+5:TP2GITRHFECb;m5;p(IJjv$LP(X+pP!xq+!-w01G~)A(Hk_!aKcC6I:3]vO^Jj;`D
uX|cOq=izIks;U/u"+U-3@+`$u"rYP<>DL{Fz#/(U/hF@!RMV>9x{[IcR2~V&<AYTJL@E)m0KRL*w^zy@f+C_0>oJMVSML%u3A$$R[f@fF:Op!x+8M(NdI`x0x*SRdCh|BX:9giTBcAf2S]`#+Z19Pr"_Uh/fNH</9fK^-F/=K$qR0$1yH-NwJ.=Abb_5gk2@X0i2if`NA:Uv_DE-HiDK_m_qid,r;wmpeAh%lZ"d#pmw-/Njj`C~@MW05g!*e,M*%OiKSyj%trw,.L=XW5eM-8dq#yeecSS:.`BT[MiD"Y/=mvk;rdTmTjc~"
A]oz!"Rf*-<:Ms1g(Mlm6igvXzXDMCY`p(yB$}m]v{Fy@<RK)a?}#/02dPmjby5t;4@_9[2S1`NxQI<N?CUwG8JPQ+(;9quR)q9gy>_]#SM!#z.h55J
]F)rFc5h@pqwo<Pb$3wi(NEI3i!m8-1kjVuTJ?)1;mdwE
?zsV9Hq"Q$`wN#5Nk1[?8-"^eKPn(
A#^[*NYBisN;%gbxJH"Ih.W5pFdPiHbl2CoGP9<0mS0O?wItJ;%8QwR8y^+$d![eN{Am6zI{D}Cim
,_yyG/S5,Ym]&cXL_4C6[L1N=*Z.J`11!CCmsVDYEPugYBJv_njs_PG[kRv=;l
$7^=?P`hKE"Ib`kP>);(btOgt)fTVBM^8pmqhdS,r4H;[8qFD%?$M?32U[]v-9.2@=tgRA
)J_lg^OT(qgeEeE54@M*AgsK")rk$I:Gi
.+qV8vao?8R!;?Rw;A%[:hU$y3:gL@d%KXsMV+)dN+hy0(J1OgcO!vNik`YEIHFKx,sr,iP-2A,#I
/mFQb(Pvwf]][0F/VDcJ9{9;Y9Z&]6f7O5xR$wugqi<pQphZD]v>Q2]PP`NS6XCI6^vR4(CP_k^J&3$:/D"v*+U?n=m&qZ3n^=l~u&d%q]4&x"8N2pURCxRCtoa20Gp<ZR.$:aQi]t[OwbuHD3mVYY/5FSKA`VV*C`o|C;e#D?l]#ll}"I+["#PPkMlAS6fLa^-_"V5Vyum`a!/
E+fP:ct@ttG@PkyV;SV6/hW#ewa,s2>%
+tBJ,z(l3=34sjLe`5lr<C7%8=:7(36[D^x<4dTePPJVz.^I)yaic5`^>a@e;N^tb[*W?ZUSnd,phWzMu;X7Kfw(>H,c9SC?JDp6<)5stAPkrUpog@3LW
|RqGz@{5a^^U7r9AYl^Z>Y@E(:L$2Z<IH7@eXxO+6g99xpll2c*=L*0Xz[2l5,I<QB`!,`ul@S.?yL[@)t2`pP9>ixGQ?jRRB5b+xX.pwnZ>{`/=w0a3mIH7~MgD|^(Po(S>8D44xMTh#eUy]WL^HMu<RcO:}3evoQXqxr&"Qw>J|S^=6YJ*o:]3^VywP-@w)fy5:r081CS%hq&sph~R^0zufxC7ytHS?Mt[ds*s^3/)LjnXnp;cZ!$`3<fiZw@2`<(f;$>O/`;YjurTQG_sbK8-Ry{O7<Ci,:YR+lXnUnmy|BFP;8{bCGI<MX)LP$",8;+&D*,A,+RQlp*N!io_-*dy=/-i7=vC#5lQa^-LvXbC0y>F*rGAM
ktl.X*8m/F+-W[-VqPqR,BHE%EM"76;XMLn?=MRJj!{NonbJ>>kaI/uE
K0Wwh8870SB;;HRbW{T:H,???zhu=OM-uN7munYQHQ@a^KBX*sL5:HtF_BA7Z:%b5&U8J9B5gPkxj/jwKOfnSd<OWkQJ^BMADDEpx4C_f#?l]]a|iE-
)V>#s]L)#(],DO=idwVcX=9;!oY?HDWi]7MjF5]}va*Q6/B(x$P(:72#+p
+#Tu(2#z(Fq_gbT"*FP:T;arX%8#`^P/Ei}&.m$fEYyvcNAsPj{7xN`iN(j&Xf-pC,%b!Mv6nWv%t:$?N!1uC5O>esz"
G
[Be
5tk@99x(.>OG:w*4-D3NF#]7ak5zp24ynqS)Tn2Fo6aQ9IVAUi(C5OrVKTSTRVQrJ@SP0C58%.(3Wo7itk&kBzb@!9ZnPq.OxK)8#mE<#J-c4
Gr7l&Wb7xtGTc;9~i3-mk)S$^_HPp<h>8b&kUy3ydQ
nB)nmj-S"fx85?)N5Wx]86kZ
Y]n#
I,>,;#;I4
&j!"q,^@jV)TuB@S:7cr#.;MGmDfTty"%:$$rBM&@]pyGKuce/$4=Ol
7=7P2x;S3P4x%[0]9OQrG
b?-?]&]q6(TQnp[a:9Ns>@Wh@^!CoH(>nlY_L?,)3D6hz;[_g<uSrWm])HBI:_`JNs+BKAB,dY`t1+^cgwe;:p]QFvcAys>n2p?L5rp+amQ7(.o:}n2Xc)sC)4#p
c/@
M#vYLq#^Iv)XnAgvLTFUVv,`v!:Lnu/".)ogvn<s:&P[W/eoDoHcW?rcG)e?1_nw9W`9]RD|p!<Jd|Ut;:"PHOEE8@ofgxU4o4r>gFln+DxAj!v2Yp%2#}_C7Cc$+n;VB!fws~y0
3M<0-J;l^ttItomqj6|d|PAa>k6iU`ju8J@]TVlw,+RZgyZ(SN~6XorkLG&]BO8b-,ry<X7:}KqtxNK[$02"BlYe$`t_h/CjTWc#wX"0=&,EQ5*C;]=fQbhKgsNj{xa;VYJ8C
^aLbz>N1Z$7jT8,_~#,fg4U$!RU=w9.R2#asUM&V2G@SwKnEANwf5"?f$;ENlQM61#tN+?~p-KLI9`+Lf_dLkKl&];r
vg
-/1aSY?O
}LiR}4)VsOCc!foLUil[RF1R)&i27vvWn(A9
Vq:sRf-MIpExk-hS_]m+NJlyHP6+$Qc;hrOrK*#>rEc@5%<I/40fGxx+mF1;#U;IIDa
K{8e0~2Z5<iG.EN.N-HXmQJ$?(?*et%tp!A&W^aER+G
fojX!b/tpP>c2BK_3japqaHzXkN"SdJ!jFrv/cgJhl(]oXuyMQgfIz3rGy.eoh)W-[.@dTe}dm.pAMs3&#Mu$pJc-F;oHD+gqc^=1&aZrUiEYUw8mosO"9?eB1N>5,;4v2LOEYnxekk?@e0&tU[i+qP!dp3m=rwE;6GQP!cwggZkkm%Y49u?gJ+FsH!8E>+AE-g+4Y:8vr8xJc2ZpVHVx;s&X/FF3=AL/}#oom"&kh-qi}SMf/w.U$H4;cngA:1!Zdf+DgC8Qf/Yv,7^RX?GnfDhi/XrnShTkD_0&yj0HD9Mx-6qUT7B-4<urFH2y)OMqf?v&*u8""(s*,MR1lh}d_c{K3ku)to$?F.>2
LD@,K,t5,3^3c4dU`Sv|fOqGm~A*#{O&nEa6w#`]]j[bJ|nh[
yo+r"T+gOqrsk}=BsRRl]~gca5gulJ529!n`nA

a-HQkJiRL;8Rt1m~:/a+0A>.lRC
tE]cJe]&4~?Wr|+p#/mIToMe^E=JJpj}Cjw8!D#-B]jU?<<BZvqLX2u8srD9>*JcEZ
gNEs`78><,]-b_$6M?9]phobWqEm6*1J_=>2m6Xh8[suNkE(+?HH$,(<WGZC6N+L+?rdY#=6sy@P`En$&arSM4vaTH!5:a6vLR`3bGf?CNGN?s1UKFD)UqoyBWM[bb?4J:*B?=Y#er_hmAEk7Yx%NmGh!K!ZVlpsGAdNeeHbB[da$H@F#)>`<:i^3O.[bD578iC+`]c[[sr$~mi?v9
gI=TKZ+ZN
!Hv[,3YJ!7gg*F33mIrO-ysjja!/d[s
bBBkwoA?XlBBCPF_hD@5r&):G2"-+<H+=Nx_IeVDX7Z(Vggd&iGCju[oi
+hVjF?nV5+]Os8kC2#wx
Dv?Ql6KmESdQ;cD`y4)bVn*rn4K"7]
Ucc`$52(pi_hP-@yB)L;]1*:_W`q?IuQeSOmX_GLVYk9Hba5lnr3-*S>FIL%h4hy<Yj:SsS(=9Ko-j4"^s7q"Zh3=!!.5HmJ;yriT
):%Q"TjH>Sj7KH9,B]m]P:na/lKJbjlVk$lJlxFz+4K6
x^M5
W#c5+8_eXH$.o$a4`zU~H.0`4!GVr4dqTpSt5n6i/n+wFkRcbo]vu
EIElQd<VpO>M.0qv65=?2-PF<hKqF[O5
u0{#{@v>)lCnCa4jJG]mII}TN&jsr:"Q$838}n`>mL}qzh>`:
`G1#V@[&,;XJ5bqGLf%&Hq>Up9[A<<z4SJS];s~=KN+Q03|x
jT"m[h8V3Ra`1&8=*X*WkmU<NS1673H/MYu1eFMaN[78+tO0ltH6W3:k%.efeNprd)(aJ}-z29
0t-m{4upA]ikz2qS>p[v`^nOl.p]!yB_F$1SdlGWQlv>[_dlsdK`Ghgxm^p!6;n(?r-)J_FSKvt"zjOMqB-d,3O"|2YPk,;[ux0oQ7<UjU5H-l2I#_0E`&bW+RcDf-)33-?(P8X]@i42(ismG(NTG&ntg.&J0bHeyF,bYEUYC7yeChW7Z_g1L;^@uE,#*qELVvLcBbY4>b+3qC.
<x,gpL6<6v}5(A"]1hx,fm3p{atfi6AH?ir&>R58zocjL?I>|i};"-K/65EjUa8)F[?2<Eb`zTyb-+/!
i|[bblYdMo*nUT`hGdH7F(Jp&DT=,T4(tUjgfuFROxGtFlkT={<{kVYh6Y6)>_2agQv:p~qGoE+DdnLc2mA[iuFq2ELx?Fm~61Hr2ah9:Zda;W,@0lmzmIqYJtCgK,*ghM4vmx<ZIeAA)>^c?vsiC=b/GLL$!*`Py=/!R3&_f&BdMDj`;&GL=.4C%Sedj@c;LT,E`[alH6mh>#:7F~I^pZ.|c+0gUaU&/K_gG^_Q5ah$ylZ7`Q%kZP9Ltp`2Yg-mGalxP=
_!>=JpcFz*=v_c)<]r3O`]6)<DCS9B#8rvykE4UT5oFv<P*1SDvGOw@F{_=b!1f@2n!!3lEbID><Q0(VF/_djDImJ^Lhjn#1SJ*MO+KgB-n/A
pHk]eYzj^Gg`aL9W$%If#Dig~7n+dNaE%dJ>$&#e&4?1
8qp,bOPVcyTFfUt;(Y"sp@#w=^0q&`SxVktCijEoSu_c%sHTewncd]eRVAO0tsXz
"w5xOINJ(Uy,[12=AnX12u<AD
-_$*05|1,-0$xQdm4u_#~rI2tU@]bPb<1-<NrlZ`mxIPl%AER%{coLBRk(9O6/O:kAY_9K^U6UQ>=F)EB=24%bBjXaLGTMZGE`s(Sm~X_(NZs*ZFra3XLmNglruy`nlal1%;6%:mI=aoYOcl{UzpfYw9b4
JdFLq#,2T1OvWYw2SvQGpMk#DerxS.5*Df!A1h`(5Ht5L]eA$=@}lFg>>gU=#*9@o
X"[m5F)8"@*tvwQhbO`%3NIRkzF[kdtze&jsKcjev&PYt5p:Qg4KR{Ai@J@d?/QpBq+2PFPq/mt_*}t02WrmcgWIk](pi^89]8a!O&Db=mviE/sgTi)oc5i`^62%M?DiX0w]VLMxL{X:]z<]JjOYX6
-L5ML2&TP>npg=[7l0)w`o.xNJH^4Y!xzW<cp^T#|kj$Crbsk+4LMseC
+`r@`kFu$K:L[<lvSO
k*xOkqd`1LkkYlm@-iX`>EMupr@1vYr4qaNe/Zugz3L`x+mp[&a)iQNE:$H,uNm&K
{k3ylr|bf]_r,%s97M$j3yd__x?B67Y)WF)P|@QR8QyH_d#6e8T(x.D=DYv:>#eM$%h]9tJT{^m8F/iKiszJ+M2p^GKnuBN-d73IOK[s@5A/s_`^h"I4U"k1b7gOhOX$2Dd;O1xE
]]sh-FQe^Q>kA,-)WGSU!bD`7GbTeQNO-hLjorvjGR>vV<[/"8#{?c?49I00vX^IcL^r$jQV`DAFfT-#R-?U&ZVp1vQ;E|;&
(1FLaXwdr6q3lS8U3BL!`R!]f@MHmKi192Q6,Sch
>Swn@~`8o+4XC2#3t
MSl_q3CF?Yop
;B;QVy)c~[V!M(mpN==?{%2iSeRjbx//A!&5W?#K4&CnSXY[!T,Vl"9vU]BN//31HwV1{1ls!JUEX^@Ua?g=/r<l,K9M`y3>66kgSwQ5RqLD4m&<U(jP5*Kle*/^(jpUohFDbM[ho:wCDC(2ik+(3"I:6#mJS-O;LV`bm8.G_$q>80@FZUmXGmzB*%~33"]e?VjlW/,>`j~2$@eARQ0CJD5U0*zbUeQ-=3}dQbMaHTVT%lItC<fU20#QncJ(XAGnxnl+SfpTzl(?.,IW6M@qd,FK}j&)PXCYP1X4~vn0*-4J7j&9aGps6yRfE7m.oVXxHg%h|dMqn@mswk].|=jA_Y(%t="618MW3&8!|x/Y{-"7
B?gf={3kv)J90p90[l8Q*3KWEl6@qE=@A@NcxQj(tBp.830pKeVd?KbAcrm@L3dAKk+=vwPRG~?6=0Wgl8oA:7qAFBskCO<wDI7V[Ic,<~S;PlN`?s^0JJDtB6MXJPmp"A2I9yORgwrep~nE=pig4vJ=1:MPO5Lja&:)q]B
Vim42KFwZr9*j_?<Cksc&.I*96[Y,>OT(u5`2IrU
J[qPB/KJ&@:Ooq<H:[#BCrQ&Ped3,jx_EHGqR_%3%vzx$pz`9fbbDhwYJx;B
-Fq!J6lBm`EV>71&d.PMe*a*J7kVk
Er$q[dgQ4m]>?sv3!$qpJf7V]%K(
Y`BBXgy4Xjw+Zc3AjRgM_RLQY
Qd:^)fp)/`CbrY6<.9=9m+}l)tjbEp4a29?au:l.z!9;52U;vO29ZTagm2(+b1{R
nYIhJ-Y?=x$Y+8q0O
7o
{P6eog11y-l.q4`T(hF*Z0;.W-l1{-k"CZ/:r<`P?^jOZ-
1/>H%)*YB55IHv6CjNQ+"-Hi*ZwmZd>+9Oorg5[PDc.!mG>;H"R:hNuUW?0fDS9Q^[&PFd#Ld:Z<>D
49{
t!sY/
HT5(?)9uoQ5.k5H#r-g9tsg(ZYL:bV~+p%Vu4%N6`Po`J)p#_W&+CgS3GVPw[_]>`0`8!6lOY;onk/+f|v03GX89B^I2$QR]V-%ND>j=wxI7R]r#=9o`.oSY{/D9_C/?APEG?8j+;VPIj_Ej<5E[!043z$t1Y.hp/Sg?@@u/b.N+pA8V{RO".=u[|!yT,5DYt=t%5-D2=0X)e`%![%_n(&sXhNVW*jZgoozvID}ueHibV%LKS1(>yQ
15OJ4.wb5wN-=lL2Sq[{yE!#X)8
q@^~P~".?#c/%OI8!Z09[N).]r5hjX]Fi3Pg]C[=4<Yec]m^r%
n_mASE|u9?c,}jjfZW{c4j_WcJB$d5;U15)Y
qti5h_XWHvK+?v5HaHge,*tA,e@s]0YcvG5BA;Av]4?%^/wlb]kbEJ!g607tgr(k=9SGAs
wHA!>-BhUpz#p:nLD/k9"?Fb2r(*c1[*+_$=wd7>+G*W?j8Cm^E6yOvg)"
7,eAL9iAe^fi5bAU0zb}+bJ@!veW7Xn{E@IR,*R!aw6Nv
G.AVJNiU:O8ztF`SE,S4jmj^vCszJ[b;NyUY5`M6+#&B`%B*jg"!t{&rx:dk[%gxvwG_%O0C60g+e_8O;,W8e;CZA~hpJeiVkB8G0X2c$:*{0j;^
-
l57y%X:557~XSZMo;Y]rCYD4{
<SxQ}r+dF;VMrr7CCmyH/DR>a-QTC:j]4=k(wMEjL4ouF
Sj><HFaY<0},)p`soFk[F2TS`aQ(-JQS;kUZq]JU1]YdIX&O2edQk"sGS!.%wirjDe[Qnk@:BJPOV-+Z`d`q29R;Em*jW"{@LE3p8`G7<Ge2GG(<~0o%b:J?##y1QQg=~`=IC>Fn+GFks]o5pK4?[S2I|VsB)
qUd9L.#l1HTH*8eHW
aGY;@j"Pw^+7+8k`00>-NgmBLV*vWrP]f3r(i(D^!Kxc?8]
b"1H#rb[%S><YJK6c.Fpq[*lc[W5Vi>A![UhF*^&w.y@;dJBIYdCZ;}"CY:`,1r^Pcm)(90IT.;L3c]tJ>;IFjVH=!tNf59jHQ
y~,oJL`pMDR|P?79_h&(8^;I;UMEndpgw*4%&hnt
!dBdGjP!}[~HT?{2P4v*CL`r@b
S6A^V`R-"XT.P>*-+Scd>7Lfm$jBxlSvn0d=PwZPk86Yicv.t0/6g
^u8!%4EgT[/68.`3A_&bN0)%3jf:>"#),rq1KY_ZjPR2)CW[ahic&h%t!M*~RDth%LmmH_iBi4j^JO^(lC$*u],$C~Q+jW-xl>=cBmjp%E9fLF_o)&1k7ysAHs3-*KY0Clqb
dadjWc*rpZpB)
*g2=h.|U[8G1fGzCXbxd_i:=hH:]2M/
8f!-)*|FTElFE!xw)><eqi.mq<
vnx]#!Io)qNl/)y"7OkIrE*Ujsl:C6mRhGabf3%/c.Tl?<9wA]#aDn[^x;oawjDd<>.9S]q#.wigRxKDFAMJnU:Gp@-60bR*b`!M;>J9i>)h[eI;L6BFtFKz_prpj:#0:Hj#C9S?]3"f%V*S[]C[>&=]((%-X)DLS>H9LWiRnHk[:1rRQrp`!l_g?,!mpN<9Z)4EFk
.1@PCe~_x0H51bRL!C8rlc9Y"O=!+hbn0k6+%eR`68^e5"5Ad4z$Xcj.VJ:lMAD@5cmDml}GDORG[=mE[llKSh6C^l]nv[I7]Arr3X_jWY_2X+AhCxxwJ^TR6s|d/pm$0NQI}pdk=s(A!vKP^/ECxhuv8XFPJ%~Gw6dkoPI9Rt!mXHqmxR$cT[{"aTu=%+!w
B-,Jm<^}([nkoFnYRb(o%]E>$VHmY3tLt;sd0:NJ/8gr^25@ZG_/w0D5UaxJAYc<QvE_<+IN4~wU9nY._T,^woCqu;_C#|Ik/WqWH)i!bmX%wXL2">`S_c+~)sR!Z
&)ai)NIk/WptH1i!c0X%wXL""A4OspTfo"?~v3uz:D?vn+_k:Vq[n}xO1!a-I~Fjqxf!irR-
>*1Is0*pT7N*s(oJxK+BpnOspFGbQbI2IGUv{c"mzn=Ts4jLNnTjYjPi4
|L+JhF+PGbSfZq@lMbY+ki$,0mZI/sQj5&eJ}S.hW^qK+5XW{6?w*/K>/y<GMmYs~bQpoV9a1f
yDn$6U1=tSn?pCIpG(7wxWw/5jKl0zpdB_hdc@U<LW5}!/I|pEm
uqo!s|X;XkGpt4KxK{Juydf`$B+SyQanZyY"?r1fH&WWwkan`KTs?r1fH&WWwkan`KTs?r1VH4k~odg)`j&
,CbsQ{oCoe6W*Phu]veBT)GM+96eK`)pd]eCK/3"WdAfP[.(lx4PKKuA1bO4P
t<D#5AaN!/:/_iF~tYWw[S(b.VMQ*9_T-b.~<.<$[iuOssayj["HkgO?kkNt)mTd*,r]QM%R@^"<LEQOpQpwG.5VBki2x2?tl2`jby_n[*X12>s"MgmNr6mB;;o#;cn
?OJLc8]HsJG`DSG0/DBf0gyek"J:R`t=/V7hVlmgE8ZwG3v|g?t$[Yosv|1
G3v|g?t$[Yosv|1
G3v|fwm,kxxA]j^1lzhqX1o"kxxA]j^1lzhqX1o"kxxA]j
KlznCTEn/4sk0]h;Hltn?TEn/4sk0]h;Hltn?W.n/_4j1AFAV_nba4s7dM:vUAFAV_nba4s7dM:vMAFBy_Nba2-7cy>vMAFBy_Nba2-7cy>vMAFBy_Nba7
7dy>v]AFBy_nba7
7dy>v]AFBy_nba7
7dy>v>mJ@K_N[l-~Me&ev>mJ@K_N[l-~Me&ev>mJ@K3JYDW.O"AF86O~Z_#r]P#b]P$D1
.~2?ES6E@omyunUzIQiEAXi"M<;|alEb1kqTcaZ}my_tUyIintA8t#Kvh#ahHK1[w&b~q&mw_tUqLRnTIRt#@yh"aq1hvi@u;p*gEN1[qT]?`ORH_jUqIIj(A8]~;FgyahC<1[w&YCq#Au_DUqIij(>P1z@qgz5tF%08)|]Qq#+y`!Uivl_`*gER1[D}Wm`ORH_kUq3OECyjk*2*]k]qA6fal0?.<%k7072Y-
Iv1[ylhQq(mw_8V4Muq=ISyQ@[h!7zI^5iMh]Rq(X@azU17svmuWi%A~g
,yI^woER1~q$Y#arby6PUbuYiUAxi"Kv;t5hER1kqTb~`OWwunUyIIk;A8XO@ug{KfCt1[t=
,q$6t_`UqI)ie>P#p@
gz/"D|
<a4RDp~TT_S?oAQh}IPF8@i
{1hDlak`11EvOTT_KAuA)khIPg?@i0W1dFsakZa1EU@Uy_+Au>AUf;gh"@u1z1T;r`IE"1CV#UmZ|n
3@UeQ}07n+?ZmnS|?t/qm:/s=4jhk
kb]V4c+;V5M?<[C5xEgz<t>tv%y4cZEVW3p;@3];&76s<ZUOu7-hc_Ix[bG
^)$XEDrM6LlIM!Zw0D&)?GO~$.?7`OTDF4J4R/B!g`ss
&_m][6$2d3S@oGo5h,n[bPPfE/)%84v0B3pQGZfy3CHF=AKn
I,*)^d*Q^$7<b8]RA;qi[L&:?v1^UQ,6,=FHh_-!-"Lt2e5]lgg9;I2>@%iY,./!-OZU^Qv3K907+Jf?9OF{hK?@v:Sti.UKu-7s`New_t1P_i&OuV*0dy%^Y=NGb0XBjoVanUHY^&HKmUkpUR4&Jva|]E`j"q,JW,^7-21IL%Xs>1,/QLa4E-KV]W_UbHIV!Fi1;#SL)5S_e"A^)|tkSB0MKo]6Og#.V6]{->H#cDbqRNd0<F]0EoFqx4@~K@*6vFu&JcD4?k"WHQH=&3lyE!1;
S?H"vYll$0V"
<%<c_-9.ne]y:AW~J}aT,]>Di.V4(rk>#x/cu/j63AGh^noC^Q?2c~v.SIk|A
7*QM@v@Mwz9q,Zm+$%v>^$qh@4G^IqQ~C*pZjq6#"nc5p=HE?H2@ABlk^9xsK]Z]xjdCOcMiJ|`#XiO@G1u-GcO!6_7DON%e<n#@rA*T(rH
lAnboprvY)yG[:WXC,jufa!TeHPo&;j%wG#*p4*ca0hTFN,g*>N%N&]T_i!8-:P9%#kn@{t(l7!]TQ#y1,^9v?$jDno|
,gIYCZ2sq02__+uCD#9!TY"/R^^./,yr*Oxdl"SlYo#@uPQn9fcvi2lLgCr[q9B`-*EppK8dvAl)A[+E<`_6RW[0+8stu:87GaIN+?(LG`8/aE]*
Q$X},?aJVKcvJB@zs$y_,{Wc&?$rHbh]V#Pk8/7cTzQI.u:^5W2Ahn62q5m;^7=}[3J$S?4=Lap+J#o.l<GPyx79fb2$xyT{hvPE)/PIOofo7^85:lMb9)#Axr6a2gdteC_K35*Cv-cce2%!#kwmp
o8TFkW98sFc)c_89xH3SZ;?A+Nde[ePbL&1=&7RW7{@yD>+^l2eie)5
)HP&V.K5hKNOVM#<oxaK,[`~SH#$x3Y7&H7kE--baU@!gOK>Y(o_6s<S7wY&7UvVE~*EDlsoGw3
SI=D]?:jB@&U0B#yWglA@NNJhUe~&VyII0"N$xb-0&uT-!]@8"X&6`T_4y2
j8-8pZ6g&M.{x,*f$atTAbC-h{Z7@93H2x[p]8d8M3C5O2.eb*S+:3J7Kro(E$v*Z%$8FLGL(h#"YUDe*yeg]4MTZP;g%5STO+Eov$(8/($L-.18V!-G9d9(pei=3upjCh8RO#Hx<Q-0w{T#jI
$NoL0KUlP$d1q5YRAM)(!!O6N[;g1#g)59X4B.N48M{qU2vLo;<NHyNjYYL7A/djVQTjvM/2suDhG9O;Sw1:Z]N0a)S&r;q,xd5PmL;VQw;j~=GuJLj-JxV*8-8Ky!xuFEw!3/^tZq1;I()sV1pQ$yEf|[DN1EJvGvjCsVCQ:V-/1@is0t>6}4XZxcU5gc./B`y&F)W!_TH+w6EQ18byNSj&FqiXr[non-k;`iuD4<=f?yiWW5{[%+A@*`/8>1u6@96k!0/-d9!?6!BbzSEP5Es"6%LLwg.,@>}
W.(K~T|T`5]Kagq,`+!+f0O%2+YeAOI[dlPrkaK*)ZRSX-cr>2Z
T*[E6Cg:w*P9m4z67D|td.G>Y?+Uij|`/9RhT;g!8nD`;5rU]QuBb&r-0nbp?1PPwB%Bz<}wBP#tWlZ:$gMg0XTAb3bNuhLCzw#WDcPR;S*(MTJ$4`(dWiDYWcqF<ZB$&ccB7w=Cc=u^@$xVB"1no$*!|EfS{03EmR@Q`%+($fqD`#Q`Vf0u{T$!RyqAm^$$,1Pp]HH"[O{Sbm*Ay*[;Ztu-+.70K)aL*6"yRo)');}elseif($_GET["file"]=="logo.png"){header("Content-Type: image/png");echo
base64_decode('iVBORw0KGgoAAAANSUhEUgAAADkAAAA5BAMAAAB+Np62AAAAMFBMVEUAAACDl60rTnZZdJNziaOerr60vszI0tr8jZH8c3X8SUr309T8Ly78Bgf8r7H6/PpDBKXXAAAAAXRSTlMAQObYZgAAAAlwSFlzAAALEwAACxMBAJqcGAAAAbRJREFUOI3VlM1OwkAQx/sGG0Xh7GwTz7b1AaRwNhqIRy4kPRKjpcc+geEJDHc1chYPfYJ6N7I+gJFQE+UjJIyzS6FqqzeN/A/dtr/Mzsx/PzRtlYSI0fd0Ju5+wDMhHjCTMIqaXoS9QWYw3iLlvRHtLMrwKqDnNLyM4m+lReizCOjXWCgqWdPzvLgJNgnvUGNPV6IVyc7cim2SrHKDMMN+L6DhTKgBDVhqCyPWFW3KwfpqwEOAXUembeYAtn0W3ssErN+RdbxBOcBYowrU2Di8VrEdWcQrx0QjqGlx3m5LUThK4DFRNhGy5lkwp2CVHZ9Qs2ICUY1cGmiUfj7zOnBTyYAdo6a8otjzR0X1UT3uSc97kiqfFzPrMqM39woVZcoUTOhCin7QL1IoJLAOKcrniyCXwUhRboBplTYPSrYJPJ3XLS6Wd8fJqmrqVm2r6vxtvz9T3kigm3bDzPvxxqmn3QDg1l7VcasbtgEpqg+X2133ixlVuTky0Sw7/8eNF+4ncPi1oyFYy4Pk2tz/TPFELrt0w6aX/S93FMPT5OwXUvcbnQl3rWTT1nIy78akqjRbPb0DRTX3Uyvxl2MAAAAASUVORK5CYII=');}exit;}if(preg_match('~^/[-\w.]~',$_SERVER["HTTP_X_FORWARDED_PREFIX"]))$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define('Adminer\HTTPS',($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));ini_set("session.use_trans_sid",'0');ini_set("arg_separator.output","&");if(!defined("SID")){session_cache_limiter("");session_name("adminer_sid");session_set_cookie_params(0,cookie_path(),"",HTTPS,true);session_start();}if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){$_GET=remove_slashes($_GET,$yd);$_POST=remove_slashes($_POST,$yd);$_COOKIE=remove_slashes($_COOKIE,$yd);}if(function_exists("get_magic_quotes_runtime")&&get_magic_quotes_runtime())set_magic_quotes_runtime(false);if(function_exists('set_time_limit'))set_time_limit(0);ini_set("precision",'16');function
lang($u,$Kg=null){$za=func_get_args();$za[0]=$u;return
call_user_func_array('Adminer\lang_format',$za);}function
lang_format($Bk,$Kg=null){if(is_array($Bk)){$F=($Kg==1?0:1);$Bk=$Bk[$F];}$Bk=str_replace("'",'’',$Bk);$za=func_get_args();array_shift($za);$Kd=str_replace("%d","%s",$Bk);if($Kd!=$Bk)$za[0]=format_number($Kg);return
vsprintf($Kd,$za);}define('Adminer\LANG','en');abstract
class
SqlDb{static$instance;static$untrusted=false;var$extension;var$flavor='';var$server_info;var$affected_rows=0;var$info='';var$errno=0;var$error='';protected$multi;abstract
function
attach($N,$V,$E);abstract
function
quote($Q);abstract
function
select_db($Zb);abstract
function
query($G,$Pk=false);function
multi_query($G){return$this->multi=$this->query($G);}function
store_result(){return$this->multi;}function
next_result(){return
false;}function
inTransaction(){return
false;}}if(extension_loaded('pdo')){abstract
class
PdoDb
extends
SqlDb{protected$pdo;function
dsn($Gc,$V,$E,array$jh=array()){$jh[\PDO::ATTR_ERRMODE]=\PDO::ERRMODE_SILENT;$jh[\PDO::ATTR_STATEMENT_CLASS]=array('Adminer\PdoResult');try{$this->pdo=new
\PDO($Gc,$V,$E,$jh);}catch(\Exception$ad){return$ad->getMessage();}$this->server_info=@$this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);return'';}function
quote($Q){return$this->pdo->quote($Q);}function
query($G,$Pk=false){$H=$this->pdo->query($G);$this->error="";if(!$H){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error='Unknown error.';return
false;}$this->store_result($H);return$H;}function
store_result($H=null){if(!$H){$H=$this->multi;if(!$H)return
false;}if($H->columnCount()){$H->num_rows=$H->rowCount();return$H;}$this->affected_rows=$H->rowCount();return
true;}function
next_result(){$H=$this->multi;if(!is_object($H))return
false;$H->_offset=0;return@$H->nextRowset();}function
inTransaction(){return$this->pdo->inTransaction();}}class
PdoResult
extends
\PDOStatement{var$_offset=0,$num_rows;function
fetch_assoc(){return$this->fetch_array(\PDO::FETCH_ASSOC);}function
fetch_row(){return$this->fetch_array(\PDO::FETCH_NUM);}private
function
fetch_array($qg){$I=$this->fetch($qg);return($I?array_map(array($this,'unresource'),$I):$I);}private
function
unresource($X){return(is_resource($X)?stream_get_contents($X):$X);}function
fetch_field(){$J=(object)$this->getColumnMeta($this->_offset++);$U=$J->pdo_type;$J->type=($U==\PDO::PARAM_INT?0:15);$J->charsetnr=($U==\PDO::PARAM_LOB||(isset($J->flags)&&in_array("blob",(array)$J->flags))?63:0);return$J;}function
seek($Rg){for($s=0;$s<$Rg;$s++)$this->fetch();}}}function
add_driver($t,$C){SqlDriver::$drivers[$t]=$C;}function
get_driver($t){return
SqlDriver::$drivers[$t];}abstract
class
SqlDriver{static$instance;static$drivers=array();static$extensions=array();static$jush;protected$conn;protected$types=array();var$delimiter=";";var$insertFunctions=array();var$editFunctions=array();var$unsigned=array();var$operators=array();var$functions=array();var$grouping=array();var$onActions="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";var$partitionBy=array();var$inout="IN|OUT|INOUT";var$enumLength="'(?:''|[^'\\\\]|\\\\.)*'";var$generated=array();var$primary="";static
function
connect($N,$V,$E){$f=new
Db;return($f->attach($N,$V,$E)?:$f);}function
__construct(Db$f){$this->conn=$f;}function
types(){return
call_user_func_array('array_merge',array_values($this->types));}function
structuredTypes(){return
array_map('array_keys',$this->types);}function
enumLength(array$m){}function
unconvertFunction(array$m){}function
select($R,array$M,array$Z,array$Xd,array$lh=array(),$z=1,$D=0,$ki=false){$bf=(count($Xd)<count($M));$G=adminer()->selectQueryBuild($M,$Z,$Xd,$lh,$z,$D);if(!$G)$G="SELECT".limit(($_GET["page"]!="last"&&$z&&$Xd&&$bf&&JUSH=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$M)."\nFROM ".table($R),($Z?"\nWHERE ".implode(" AND ",$Z):"").($Xd&&$bf?"\nGROUP BY ".implode(", ",$Xd):"").($lh?"\nORDER BY ".implode(", ",$lh):""),$z,($D?$z*$D:0),"\n");$Jj=microtime(true);$I=$this->conn->query($G,(!$z&&!$ki?1:0));if($ki)echo
adminer()->selectQuery($G,$Jj,!$I);return$I;}function
delete($R,$si,$z=0){$G="FROM ".table($R);return
queries("DELETE".($z?limit1($R,$G,$si):" $G$si"));}function
update($R,array$O,$si,$z=0,$gj="\n"){$pl=array();foreach($O
as$x=>$X)$pl[]="$x = $X";$G=table($R)." SET$gj".implode(",$gj",$pl);return
queries("UPDATE".($z?limit1($R,$G,$si,$gj):" $G$si"));}function
insert($R,array$O){return
queries("INSERT INTO ".table($R).($O?" (".implode(", ",array_keys($O)).")\nVALUES (".implode(", ",$O).")":" DEFAULT VALUES").$this->insertReturning($R));}function
insertReturning($R){return"";}function
insertUpdate($R,array$K,array$ii){foreach($K
as$O){$Z=array();foreach($O
as$x=>$X){if(isset($ii[idf_unescape($x)]))$Z[]="$x = $X";}if(!($Z&&$this->update($R,$O," WHERE ".implode(" AND ",$Z))&&$this->conn->affected_rows)&&!$this->insert($R,$O))return
false;}return
true;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
slowQuery($G,$pk){}function
convertSearch($u,array$X,array$m){return$u;}function
value($X,array$m){return(method_exists($this->conn,'value')?$this->conn->value($X,$m):$X);}function
quoteBinary($Ui){return
q($Ui);}function
typeName(\stdClass$m){return(isset($m->native_type)?$m->native_type:"");}function
warnings(){}function
tableHelp($C,$ff=false){}function
inheritsFrom($R){return
array();}function
inheritedTables($R){return
array();}function
partitionsInfo($R){return
array();}function
hasCStyleEscapes(){return
false;}function
lineComment(){return"--";}function
engines(){return
array();}function
supportsIndex(array$S){return!is_view($S);}function
supportsAlterIndex(array$S){return
true;}function
indexAlgorithms(array$Vj){return
array();}function
indexOpclasses(){return
array();}function
checkConstraints($R){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t
	ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME".($this->conn->flavor=='maria'?" AND c.TABLE_NAME = ".q($R):"")."
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($R).(JUSH=="pgsql"?"
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'":""),$this->conn);}function
allFields(){$I=array();if(DB!=""){foreach(get_rows("SELECT c.TABLE_NAME AS tab, c.COLUMN_NAME AS field, c.IS_NULLABLE AS nullable,
	c.DATA_TYPE AS type, c.CHARACTER_MAXIMUM_LENGTH AS length,
	".(JUSH=='sql'?"c.COLUMN_KEY = 'PRI'":"k.COLUMN_NAME")." AS ".idf_escape("primary")."
FROM INFORMATION_SCHEMA.COLUMNS c".(JUSH=='sql'?"":"
LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.TABLE_SCHEMA = t.TABLE_SCHEMA AND c.TABLE_NAME = t.TABLE_NAME AND t.CONSTRAINT_TYPE = 'PRIMARY KEY'
LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
	ON t.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA AND t.CONSTRAINT_NAME = k.CONSTRAINT_NAME AND c.TABLE_SCHEMA = k.TABLE_SCHEMA AND c.TABLE_NAME = k.TABLE_NAME AND c.COLUMN_NAME = k.COLUMN_NAME")."
WHERE c.TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION",$this->conn)as$J){$J["null"]=($J["nullable"]=="YES");$I[$J["tab"]][]=$J;}}return$I;}}add_driver("pgsql","PostgreSQL");if(isset($_GET["pgsql"])){define('Adminer\DRIVER',"pgsql");if(extension_loaded("pgsql")&&$_GET["ext"]!="pdo"){class
PgsqlDb
extends
SqlDb{var$extension="PgSQL";var$timeout=0;private$link,$string,$database=true;function
_error($Uc,$l){if(ini_bool("html_errors"))$l=html_entity_decode(strip_tags($l));$l=preg_replace('~^[^:]*: ~','',$l);$this->error=$l;}function
attach($N,$V,$E){$j=adminer()->database();set_error_handler(array($this,'_error'));list($se,$Yh)=host_port($N);$this->string="host='$se'".($Yh?" port=$Yh":"")." user='".addcslashes($V,"'\\")."' password='".addcslashes($E,"'\\")."'";$Ij=adminer()->connectSsl();if(isset($Ij["mode"]))$this->string
.=" sslmode=$Ij[mode]";$this->link=@pg_connect("$this->string dbname='".($j!=""?addcslashes($j,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->link&&$j!=""){$this->database=false;$this->link=@pg_connect("$this->string dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}restore_error_handler();if($this->link)pg_set_client_encoding($this->link,"UTF8");return($this->link?'':$this->error);}function
quote($Q){return(function_exists('pg_escape_literal')?pg_escape_literal($this->link,$Q):"'".pg_escape_string($this->link,$Q)."'");}function
value($X,array$m){return($m["type"]=="bytea"&&$X!==null?pg_unescape_bytea($X):$X);}function
select_db($Zb){if($Zb==adminer()->database())return$this->database;$I=@pg_connect("$this->string dbname='".addcslashes($Zb,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($I)$this->link=$I;return$I;}function
close(){$this->link=@pg_connect("$this->string dbname='postgres'");}function
query($G,$Pk=false){if(self::$untrusted)$H=(@pg_query($this->link,"BEGIN READ ONLY")?@pg_query_params($this->link,$G,array()):false);else$H=@pg_query($this->link,$G);$this->error="";if(!$H){$this->error=pg_last_error($this->link);$I=false;}elseif(!pg_num_fields($H)){$this->affected_rows=pg_affected_rows($H);$I=true;}else$I=new
Result($H);if(self::$untrusted)@pg_query($this->link,"COMMIT");if($this->timeout){$this->timeout=0;$this->query("RESET statement_timeout");}return$I;}function
warnings(){if(PHP_VERSION_ID>=70100){$I=implode("\n",pg_last_notice($this->link,PGSQL_NOTICE_ALL));pg_last_notice($this->link,PGSQL_NOTICE_CLEAR);}else$I=pg_last_notice($this->link);return
nl_br(h($I));}function
inTransaction(){$P=pg_transaction_status($this->link);return$P==PGSQL_TRANSACTION_INTRANS||$P==PGSQL_TRANSACTION_INERROR;}function
copyFrom($R,array$K){$this->error='';set_error_handler(function($Uc,$l){$this->error=(ini_bool('html_errors')?html_entity_decode($l):$l);return
true;});$I=pg_copy_from($this->link,$R,$K);restore_error_handler();return$I;}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($H){$this->result=$H;$this->num_rows=pg_num_rows($H);}function
fetch_assoc(){return
pg_fetch_assoc($this->result);}function
fetch_row(){return
pg_fetch_row($this->result);}function
fetch_field(){$d=$this->offset++;$I=new
\stdClass;$I->orgtable=pg_field_table($this->result,$d);$I->name=pg_field_name($this->result,$d);$U=pg_field_type($this->result,$d);$I->native_type=$U;$I->type=(preg_match(number_type(),$U)?0:15);$I->charsetnr=($U=="bytea"?63:0);return$I;}}}elseif(extension_loaded("pdo_pgsql")){class
PgsqlDb
extends
PdoDb{var$extension="PDO_PgSQL";var$timeout=0;function
attach($N,$V,$E){$j=adminer()->database();list($se,$Yh)=host_port($N);$Gc="pgsql:host='$se'".($Yh?" port=$Yh":"")." client_encoding=utf8 dbname='".($j!=""?addcslashes($j,"'\\"):"postgres")."'";$Ij=adminer()->connectSsl();if(isset($Ij["mode"]))$Gc
.=" sslmode=$Ij[mode]";return$this->dsn($Gc,$V,$E);}function
select_db($Zb){return(adminer()->database()==$Zb);}function
query($G,$Pk=false){$I=(self::$untrusted?$this->readOnlyQuery($G):parent::query($G,$Pk));if($this->timeout){$this->timeout=0;parent::query("RESET statement_timeout");}return$I;}private
function
readOnlyQuery($G){$this->error="";if(!$this->pdo->query("BEGIN READ ONLY")){list(,$this->errno,$this->error)=$this->pdo->errorInfo();return
false;}$H=$this->pdo->prepare($G);$I=false;if($H&&$H->execute()){$this->store_result($H);$I=$H;}else{list(,$this->errno,$this->error)=($H?$H->errorInfo():$this->pdo->errorInfo());if(!$this->error)$this->error='Unknown error.';}$this->pdo->query("COMMIT");return$I;}function
warnings(){}function
copyFrom($R,array$K){$I=$this->pdo->pgsqlCopyFromArray($R,$K);$this->error=idx($this->pdo->errorInfo(),2)?:'';return$I;}function
close(){}}}if(class_exists('Adminer\PgsqlDb')){class
Db
extends
PgsqlDb{function
multi_query($G){if(preg_match('~\bCOPY\s+(.+?)\s+FROM\s+stdin;\n?(.*)\n\\\\\.$~is',str_replace("\r\n","\n",$G),$B)){$K=explode("\n",$B[2]);$this->multi=false;$this->affected_rows=count($K);return$this->copyFrom($B[1],$K);}return
parent::multi_query($G);}}}class
Driver
extends
SqlDriver{static$extensions=array("PgSQL","PDO_PgSQL");static$jush="pgsql";var$operators=array("=","<",">","<=",">=","!=","~","~*","!~","LIKE","LIKE %%","ILIKE","ILIKE %%","IN","IS NULL","NOT LIKE","NOT ILIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("char_length","lower","round","to_hex","to_timestamp","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$nsOid="(SELECT oid FROM pg_namespace WHERE nspname = current_schema())";private$userTypes=array();static
function
connect($N,$V,$E){$f=parent::connect($N,$V,$E);if(is_string($f))return$f;$sl=get_val("SELECT version()",0,$f);$f->flavor=(preg_match('~CockroachDB~',$sl)?'cockroach':'');$f->server_info=preg_replace('~^\D*([\d.]+[-\w]*).*~','\1',$sl);if(min_version(9,0,$f))$f->query("SET application_name = 'Adminer'");if($f->flavor=='cockroach')add_driver(DRIVER,"CockroachDB");return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20),'Date and time'=>array("date"=>13,"time"=>17,"timestamp"=>20,"timestamptz"=>21,"interval"=>0),'Strings'=>array("character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0),'Binary'=>array("bit"=>0,"bit varying"=>0,"bytea"=>0),'Network'=>array("cidr"=>43,"inet"=>43,"macaddr"=>17,"macaddr8"=>23,"txid_snapshot"=>0),'Geometry'=>array("box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0),);if(min_version(9.2,0,$f)){$this->types['Strings']["json"]=4294967295;$this->types['Ranges']=array("int4range"=>0,"int8range"=>0,"numrange"=>0,"daterange"=>0,"tsrange"=>0,"tstzrange"=>0);if(min_version(9.4,0,$f))$this->types['Strings']["jsonb"]=4294967295;}$this->insertFunctions=array("char"=>"md5","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",);if(min_version(12,0,$f)){$this->generated[]="STORED";if(min_version(18,0,$f))$this->generated[]="VIRTUAL";}$this->partitionBy=array("RANGE","LIST");if(!$f->flavor)$this->partitionBy[]="HASH";}function
enumLength(array$m){$Sg=$this->userTypes[$m["type"]];return($Sg?type_values($Sg):"");}function
setUserTypes(array$Ok){$this->userTypes=array_flip($Ok);$this->types['User types']=array_fill_keys(array_keys($this->userTypes),0);}function
insertReturning($R){$Ea=array_filter(fields($R),function($m){return$m['auto_increment'];});return(count($Ea)==1?" RETURNING ".idf_escape(key($Ea)):"");}function
insertUpdate($R,array$K,array$ii){$e=array_keys(reset($K));$_b=array();$Yk=array();foreach($e
as$x){if(isset($ii[idf_unescape($x)]))$_b[]=$x;else$Yk[]="$x = EXCLUDED.$x";}if(!$_b||!min_version(9.5)||count($_b)!=count($ii))return
parent::insertUpdate($R,$K,$ii);$fi="INSERT INTO ".table($R)." (".implode(", ",$e).") VALUES\n";$Pj="\nON CONFLICT (".implode(", ",$_b).")".($Yk?" DO UPDATE SET ".implode(", ",$Yk):" DO NOTHING");$pl=array();$y=0;foreach($K
as$O){$Y="(".implode(", ",$O).")";if($pl&&strlen($fi)+$y+strlen($Y)+strlen($Pj)>1e6){if(!queries($fi.implode(",\n",$pl).$Pj))return
false;$pl=array();$y=0;}$pl[]=$Y;$y+=strlen($Y)+2;}return
queries($fi.implode(",\n",$pl).$Pj);}function
slowQuery($G,$pk){$this->conn->query("SET statement_timeout = ".(1000*$pk));$this->conn->timeout=1000*$pk;return$G;}function
convertSearch($u,array$X,array$m){$mk="char|text";if(strpos($X["op"],"LIKE")===false)$mk
.="|date|time(stamp)?|boolean|uuid|inet|cidr|macaddr|range|".number_type();return(preg_match("~$mk~",$m["type"])?$u:"CAST($u AS text)");}function
quoteBinary($Ui){return"'\\x".bin2hex($Ui)."'";}function
warnings(){return$this->conn->warnings();}function
tableHelp($C,$ff=false){$Ff=array("information_schema"=>"infoschema","pg_catalog"=>($ff?"view":"catalog"),);$_=$Ff[$_GET["ns"]];if($_)return"$_-".str_replace("_","-",$C).".html";}function
inheritsFrom($R){return
get_rows("SELECT relname AS table, nspname AS ns FROM pg_class JOIN pg_inherits ON inhparent = oid JOIN pg_namespace ON relnamespace = pg_namespace.oid WHERE inhrelid = ".$this->tableOid($R)." ORDER BY 2, 1");}function
inheritedTables($R){return
get_rows("SELECT relname AS table, nspname AS ns FROM pg_inherits JOIN pg_class ON inhrelid = oid JOIN pg_namespace ON relnamespace = pg_namespace.oid WHERE inhparent = ".$this->tableOid($R)." ORDER BY 2, 1");}function
partitionsInfo($R){$J=(min_version(10)?$this->conn->query("SELECT * FROM pg_partitioned_table WHERE partrelid = ".$this->tableOid($R))->fetch_assoc():null);if($J){$c=get_vals("SELECT attname FROM pg_attribute WHERE attrelid = $J[partrelid] AND attnum IN (".str_replace(" ",", ",$J["partattrs"]).")");$Ua=array('h'=>'HASH','l'=>'LIST','r'=>'RANGE');return
array("partition_by"=>$Ua[$J["partstrat"]],"partition"=>implode(", ",array_map('Adminer\idf_escape',$c)),);}return
array();}function
tableOid($R){return"(SELECT oid FROM pg_class WHERE relnamespace = $this->nsOid AND relname = ".q($R)." AND relkind IN ('r', 'm', 'v', 'f', 'p'))";}function
indexAlgorithms(array$Vj){static$I=array();if(!$I)$I=get_vals("SELECT amname FROM pg_am".(min_version(9.6)?" WHERE amtype = 'i'":"")." ORDER BY amname = '".($this->conn->flavor=='cockroach'?"prefix":"btree")."' DESC, amname");return$I;}function
indexOpclasses(){static$I=array();if(!$I&&$this->conn->flavor!='cockroach')$I=get_vals("SELECT DISTINCT opcname FROM pg_catalog.pg_opclass WHERE NOT opcdefault ORDER BY opcname");return$I;}function
supportsIndex(array$S){return$S["Engine"]!="view";}function
hasCStyleEscapes(){static$Wa;if($Wa===null)$Wa=(get_val("SHOW standard_conforming_strings",0,$this->conn)=="off");return$Wa;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($Fd){return
get_vals("SELECT datname FROM pg_database
WHERE datallowconn = TRUE AND has_database_privilege(datname, 'CONNECT')
ORDER BY datname");}function
limit($G,$Z,$z,$Rg=0,$gj=" "){return" $G$Z".($z?$gj."LIMIT $z".($Rg?" OFFSET $Rg":""):"");}function
limit1($R,$G,$Z,$gj="\n"){return(preg_match('~^INTO~',$G)?limit($G,$Z,1,0,$gj):" $G".(is_view(table_status1($R))?$Z:$gj."WHERE ctid = (SELECT ctid FROM ".table($R).$Z.$gj."LIMIT 1)"));}function
db_collation($j,array$qb){return
get_val("SELECT datcollate FROM pg_database WHERE datname = ".q($j));}function
logged_user(){return
get_val("SELECT user");}function
tables_list(){$G="SELECT table_name, table_type FROM information_schema.tables WHERE table_schema = current_schema()";if(support("materializedview"))$G
.="
UNION ALL
SELECT matviewname, 'MATERIALIZED VIEW'
FROM pg_matviews
WHERE schemaname = current_schema()";$G
.="
ORDER BY 1";return
get_key_vals($G);}function
count_tables(array$i){$I=array();foreach($i
as$j){if(connection()->select_db($j))$I[$j]=count(tables_list());}return$I;}function
table_status($C="",$nd=false){static$ie;if($ie===null)$ie=get_val("SELECT 'pg_table_size'::regproc");$kj=(!$nd&&min_version(10));$I=array();foreach(get_rows("SELECT
	relname AS \"Name\",
	CASE relkind WHEN 'v' THEN 'view' WHEN 'm' THEN 'materialized view' ELSE 'table' END AS \"Engine\"".($ie?",
	pg_table_size(c.oid) AS \"Data_length\",
	pg_indexes_size(c.oid) AS \"Index_length\"":"").",
	obj_description(c.oid, 'pg_class') AS \"Comment\",
	".(min_version(12)?"''":"CASE WHEN relhasoids THEN 'oid' ELSE '' END")." AS \"Oid\",
	reltuples AS \"Rows\",
	".($kj?"seq.last_value":"NULL")." AS \"Auto_increment\",
	".(min_version(10)?"relispartition::int AS partition,":"")."
	current_schema() AS nspname
FROM pg_class c
".($kj?"LEFT JOIN (
	SELECT d.refobjid, max(s.last_value) AS last_value
	FROM pg_depend d
	JOIN pg_class sc ON sc.oid = d.objid AND sc.relkind = 'S' AND sc.relnamespace = ".driver()->nsOid."
	JOIN pg_sequences s ON s.schemaname = current_schema() AND s.sequencename = sc.relname
	WHERE d.classid = 'pg_class'::regclass AND d.refclassid = 'pg_class'::regclass AND d.deptype IN ('a', 'i')
	".($C!=""?"AND d.refobjid = ".driver()->tableOid($C):"")."
	GROUP BY d.refobjid
) seq ON seq.refobjid = c.oid
":"")."WHERE relkind IN ('r', 'm', 'v', 'f', 'p')
AND relnamespace = ".driver()->nsOid."
".($C!=""?"AND relname = ".q($C):"ORDER BY relname"))as$J)$I[$J["Name"]]=$J;return$I;}function
is_view(array$S){return
in_array($S["Engine"],array("view","materialized view"));}function
fk_support(array$S){return
true;}function
fields($R){$I=array();$ta=array('timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz',);foreach(get_rows("SELECT
	a.attname AS field,
	format_type(a.atttypid, a.atttypmod) AS full_type,
	pg_get_expr(d.adbin, d.adrelid) AS default,
	a.attnotnull::int,
	i.indrelid AS primary,
	col_description(a.attrelid, a.attnum) AS comment".(min_version(10)?",
	a.attidentity".(min_version(12)?",
	a.attgenerated":""):"")."
FROM pg_attribute a
LEFT JOIN pg_attrdef d ON a.attrelid = d.adrelid AND a.attnum = d.adnum
LEFT JOIN pg_index i ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey) AND i.indisprimary
WHERE a.attrelid = ".driver()->tableOid($R)."
AND NOT a.attisdropped
AND a.attnum > 0
ORDER BY a.attnum")as$J){preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$J["full_type"],$B);list(,$U,$y,$J["length"],$ma,$_a)=$B;$J["length"].=$_a;$eb=$U.$ma;if(isset($ta[$eb])){$J["type"]=$ta[$eb];$J["full_type"]=$J["type"].$y.$_a;}else{$J["type"]=$U;$J["full_type"]=$J["type"].$y.$ma.$_a;}if(in_array($J['attidentity'],array('a','d')))$J['default']='GENERATED '.($J['attidentity']=='d'?'BY DEFAULT':'ALWAYS').' AS IDENTITY';$J["generated"]=idx(array("s"=>"STORED","v"=>"VIRTUAL"),$J["attgenerated"],"");$J["null"]=!$J["attnotnull"];$J["auto_increment"]=$J['attidentity']||preg_match('~^nextval\(~i',$J["default"])||preg_match('~^unique_rowid\(~',$J["default"]);$J["privileges"]=array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1);if(!$J['generated']&&preg_match('~(.+)::[^,)]+(.*)~',$J["default"],$B))$J["default"]=($B[1]=="NULL"?null:idf_unescape($B[1]).$B[2]);$I[$J["field"]]=$J;}return$I;}function
indexes($R,$g=null){$g=connection($g);$I=array();$Zj=driver()->tableOid($R);$e=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $Zj AND attnum > 0",$g);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption, amname,
	pg_get_expr(indpred, indrelid, true) AS partial, pg_get_expr(indexprs, indrelid) AS indexpr".($g->flavor=='cockroach'?"":",
	(SELECT string_agg(CASE WHEN opcdefault THEN '' ELSE opcname END, ' ' ORDER BY s)
		FROM generate_subscripts(indclass, 1) AS s JOIN pg_catalog.pg_opclass ON pg_opclass.oid = indclass[s]) AS opclasses")."
FROM pg_index
JOIN pg_class ON indexrelid = oid
JOIN pg_am ON pg_am.oid = pg_class.relam
WHERE indrelid = $Zj
ORDER BY indisprimary DESC, indisunique DESC",$g)as$J){$Fi=$J["relname"];$I[$Fi]["type"]=($J["indisprimary"]?"PRIMARY":($J["indisunique"]?"UNIQUE":"INDEX"));$I[$Fi]["columns"]=array();$I[$Fi]["descs"]=array();$I[$Fi]["algorithm"]=$J["amname"];$I[$Fi]["partial"]=$J["partial"];$Ke=preg_split('~(?<=\)), (?=\()~',$J["indexpr"]);foreach(explode(" ",$J["indkey"])as$Le)$I[$Fi]["columns"][]=($Le?$e[$Le]:array_shift($Ke));foreach(explode(" ",$J["indoption"])as$Me)$I[$Fi]["descs"][]=(intval($Me)&1?'1':null);$I[$Fi]["opclasses"]=($J["opclasses"]!=""?explode(" ",$J["opclasses"]):array());$I[$Fi]["lengths"]=array();}return$I;}function
foreign_keys($R){$I=array();foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, condeferred::int AS deferred, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = ".driver()->tableOid($R)."
AND contype = 'f'::char
ORDER BY conkey, conname")as$J){$J['deferrable']=($J['deferrable']?'':'NOT ').'DEFERRABLE'.($J['deferred']?' INITIALLY DEFERRED':'');if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$J['definition'],$B)){$J['source']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$B[1])));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$B[2],$Nf)){$J['ns']=idf_unescape($Nf[2]);$J['table']=idf_unescape($Nf[4]);}$J['target']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$B[3])));$J['on_delete']=(preg_match("~ON DELETE (".driver()->onActions.")~",$B[4],$Nf)?$Nf[1]:'NO ACTION');$J['on_update']=(preg_match("~ON UPDATE (".driver()->onActions.")~",$B[4],$Nf)?$Nf[1]:'NO ACTION');$I[$J['conname']]=$J;}}return$I;}function
view($C){return
array("select"=>trim(get_val("SELECT pg_get_viewdef(".driver()->tableOid($C).")")));}function
collations(){return
array();}function
information_schema($j,$L=""){return
in_array($L!=""?$L:get_schema(),array("information_schema","pg_catalog","pg_toast"));}function
error(){$I=h(connection()->error);if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$I,$B))$I=$B[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($B[3]).'})(.*)~','\1<b>\2</b>',$B[2]).$B[4];return
nl_br($I);}function
create_database($j,$pb){return
queries("CREATE DATABASE ".idf_escape($j).($pb?" ENCODING ".idf_escape($pb):""));}function
drop_databases(array$i){connection()->close();return
apply_queries("DROP DATABASE",$i,'Adminer\idf_escape');}function
rename_database($C,$pb){connection()->close();return!!queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($C));}function
auto_increment(){return"";}function
alter_table($R,$C,array$n,array$Hd,$ub,$Pc,$pb,$Ea,$Mh){$b=array();$ri=array();if($R!=""&&$R!=$C)$ri[]="ALTER TABLE ".table($R)." RENAME TO ".table($C);$hj="";foreach($n
as$m){$d=idf_escape($m[0]);$X=$m[1];if(!$X)$b[]="DROP $d";else{$nl=$X[5];unset($X[5]);if($m[0]==""){if(isset($X[6]))$X[1]=($X[1]==" bigint"?" big":($X[1]==" smallint"?" small":" "))."serial";$b[]=($R!=""?"ADD ":"  ").implode($X);if(isset($X[6]))$b[]=($R!=""?"ADD":" ")." PRIMARY KEY ($X[0])";}else{if($d!=$X[0])$ri[]="ALTER TABLE ".table($C)." RENAME $d TO $X[0]";$b[]="ALTER $d TYPE$X[1]";$ij=$R."_".idf_unescape($X[0])."_seq";$b[]="ALTER $d ".($X[3]?"SET".preg_replace('~GENERATED ALWAYS(.*) (STORED|VIRTUAL)~','EXPRESSION\1',$X[3]):(isset($X[6])?"SET DEFAULT nextval(".q($ij).")":"DROP DEFAULT"));if(isset($X[6]))$hj="CREATE SEQUENCE IF NOT EXISTS ".idf_escape($ij)." OWNED BY ".idf_escape($R).".$X[0]";$b[]="ALTER $d ".($X[2]==" NULL"?"DROP NOT":"SET").$X[2];}if($m[0]!=""||$nl!="")$ri[]="COMMENT ON COLUMN ".table($C).".$X[0] IS ".($nl!=""?substr($nl,9):"''");}}$b=array_merge($b,$Hd);if($R==""){$P="";if($Mh){$lb=(connection()->flavor=='cockroach');$P=" PARTITION BY $Mh[partition_by]($Mh[partition])";if($Mh["partition_by"]=='HASH'){$Nh=+$Mh["partitions"];for($s=0;$s<$Nh;$s++)$ri[]="CREATE TABLE ".idf_escape($C."_$s")." PARTITION OF ".idf_escape($C)." FOR VALUES WITH (MODULUS $Nh, REMAINDER $s)";}else{$hi="MINVALUE";foreach($Mh["partition_names"]as$s=>$X){$Y=$Mh["partition_values"][$s];$Ih=" VALUES ".($Mh["partition_by"]=='LIST'?"IN ($Y)":"FROM ($hi) TO ($Y)");if($lb)$P
.=($s?",":" (")."\n  PARTITION ".(preg_match('~^DEFAULT$~i',$X)?$X:idf_escape($X))."$Ih";else$ri[]="CREATE TABLE ".idf_escape($C."_$X")." PARTITION OF ".idf_escape($C)." FOR$Ih";$hi=$Y;}$P
.=($lb?"\n)":"");}}array_unshift($ri,"CREATE TABLE ".table($C)." (\n".implode(",\n",$b)."\n)$P");}elseif($b)array_unshift($ri,"ALTER TABLE ".table($R)."\n".implode(",\n",$b));if($hj)array_unshift($ri,$hj);if($ub!==null)$ri[]="COMMENT ON TABLE ".table($C)." IS ".q($ub);foreach($ri
as$G){if(!queries($G))return
false;}if($Ea!=""){foreach(fields($C)as$qd=>$m){if($m["auto_increment"])return!!queries("SELECT setval(pg_get_serial_sequence(".q(table($C)).", ".q($qd)."), $Ea)");}}return
true;}function
alter_indexes($R,$b){$h=array();$Bc=array();$ri=array();foreach($b
as$X){if($X[0]!="INDEX")$h[]=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");elseif($X[2]=="DROP")$Bc[]=idf_escape($X[1]);else$ri[]="CREATE INDEX ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R).($X[3]?" USING $X[3]":"")." (".implode(", ",$X[2]).")".($X[4]?" WHERE $X[4]":"");}if($h)array_unshift($ri,"ALTER TABLE ".table($R).implode(",",$h));if($Bc)array_unshift($ri,"DROP INDEX ".implode(", ",$Bc));foreach($ri
as$G){if(!queries($G))return
false;}return
true;}function
truncate_tables(array$T){return!!queries("TRUNCATE ".implode(", ",array_map('Adminer\table',$T)));}function
drop_kinds(array$T){$I=array("MATERIALIZED VIEW"=>array(),"VIEW"=>array(),"TABLE"=>array());foreach($T
as$C=>$S)$I[strtoupper($S["Engine"])][]=idf_escape($S["nspname"]).".".table($C);return
array_filter($I);}function
drop_views(array$ul){return
drop_tables($ul);}function
drop_tables(array$T){$Kj=array();foreach($T
as$R)$Kj[$R]=table_status1($R);foreach(drop_kinds($Kj)as$pf=>$xg){if(!queries("DROP $pf ".implode(", ",$xg)))return
false;}return
true;}function
move_tables(array$T,array$ul,$hk){foreach(array_merge($T,$ul)as$R){$P=table_status1($R);if(!queries("ALTER ".strtoupper($P["Engine"])." ".table($R)." SET SCHEMA ".idf_escape($hk)))return
false;}return
true;}function
trigger($C,$R){if($C=="")return
array("Statement"=>"EXECUTE PROCEDURE ()");$e=array();$Z="WHERE trigger_schema = current_schema() AND event_object_table = ".q($R)." AND trigger_name = ".q($C);foreach(get_rows("SELECT * FROM information_schema.triggered_update_columns $Z")as$J)$e[]=$J["event_object_column"];$I=array();foreach(get_rows('SELECT trigger_name AS "Trigger", action_timing AS "Timing", event_manipulation AS "Event", \'FOR EACH \' || action_orientation AS "Type", action_statement AS "Statement"
FROM information_schema.triggers'."
$Z
ORDER BY event_manipulation DESC")as$J){if($e&&$J["Event"]=="UPDATE")$J["Event"].=" OF";$J["Of"]=implode(", ",$e);if($I)$J["Event"].=" OR $I[Event]";$I=$J;}return$I;}function
triggers($R){$I=array();foreach(get_rows("SELECT * FROM information_schema.triggers WHERE trigger_schema = current_schema() AND event_object_table = ".q($R))as$J){$Fk=trigger($J["trigger_name"],$R);$I[$Fk["Trigger"]]=array($Fk["Timing"],$Fk["Event"]);}return$I;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF",),"Type"=>array("FOR EACH ROW","FOR EACH STATEMENT"),);}function
routine($C,$U){$K=get_rows('SELECT routine_definition AS definition, LOWER(external_language) AS language, *
FROM information_schema.routines
WHERE routine_schema = current_schema() AND specific_name = '.q($C));$I=idx($K,0,array());$I["returns"]=array("type"=>preg_replace('~^_(.*)~','\1[]',"$I[type_udt_name]"));$I["fields"]=get_rows("SELECT COALESCE(parameter_name, ordinal_position::text) AS field,
	CASE data_type WHEN 'USER-DEFINED' THEN udt_name WHEN 'ARRAY' THEN substr(udt_name, 2) || '[]' ELSE data_type END AS type,
	character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = ".q($C)."
ORDER BY ordinal_position");return$I;}function
routines(){return
get_rows('SELECT specific_name AS "SPECIFIC_NAME", routine_type AS "ROUTINE_TYPE", routine_name AS "ROUTINE_NAME", type_udt_name AS "DTD_IDENTIFIER"
FROM information_schema.routines
WHERE routine_schema = current_schema()'.(connection()->flavor=='cockroach'?'':"
AND substring(specific_name, '[0-9]+\$')::oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_proc'::regclass AND deptype = 'e')").'
ORDER BY SPECIFIC_NAME');}function
routine_languages(){return
get_vals("SELECT LOWER(lanname) FROM pg_catalog.pg_language");}function
routine_id($C,array$J){$I=array();foreach($J["fields"]as$m){$y=$m["length"];$I[]=$m["type"].($y?"($y)":"");}return
idf_escape($C)."(".implode(", ",$I).")";}function
last_id($H){$J=(is_object($H)?$H->fetch_row():array());return($J?$J[0]:0);}function
explain(Db$f,$G){return$f->query("EXPLAIN $G");}function
found_rows(array$S,array$Z){if(preg_match("~ rows=([0-9]+)~",get_val("EXPLAIN SELECT * FROM ".idf_escape($S["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$Ei))return$Ei[1];}function
types($jd=false){$lb=connection()->flavor=='cockroach';$qf=($lb?"'e'":"'b','c','d','e'".(min_version(9.2)?",'r'":""));return
get_key_vals("SELECT t.oid, t.typname
FROM pg_type t
WHERE t.typnamespace = ".driver()->nsOid."
AND t.typtype IN ($qf)".($lb?"
AND t.typelem = 0":"
AND (t.typrelid = 0 OR (SELECT c.relkind FROM pg_class c WHERE c.oid = t.typrelid) = 'c')"."
AND NOT EXISTS (SELECT 1 FROM pg_type e WHERE e.typarray = t.oid)".($jd?'':"
AND t.oid NOT IN (SELECT objid FROM pg_catalog.pg_depend WHERE classid = 'pg_type'::regclass AND deptype = 'e')"))."
ORDER BY t.typname");}function
type_values($t){$Tc=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $t ORDER BY enumsortorder");return($Tc?"'".implode("', '",array_map('addslashes',$Tc))."'":"");}function
collation_name($Sg){return(min_version(9.1)?"(SELECT collname FROM pg_collation WHERE oid = $Sg AND collname != 'default')":"NULL");}function
type_definition($t){$U=first(get_rows("SELECT typtype, typisdefined::int AS defined, typrelid FROM pg_type WHERE oid = $t"));$I=array("kind"=>($U?$U["typtype"]:""),"definition"=>"");if(!$U||!$U["defined"])return$I;switch($I["kind"]){case'e':$pl=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $t ORDER BY enumsortorder");$I["definition"]="AS ENUM (".implode(", ",array_map('Adminer\q',$pl)).")";break;case'c':$e=array();foreach(get_rows("SELECT attname, format_type(atttypid, atttypmod) AS full_type, ".collation_name("attcollation")." AS collation
FROM pg_attribute
WHERE attrelid = $U[typrelid] AND attnum > 0 AND NOT attisdropped
ORDER BY attnum")as$J)$e[]=idf_escape($J["attname"])." $J[full_type]".($J["collation"]?" COLLATE ".idf_escape($J["collation"]):"");$I["definition"]="AS (\n\t".implode(",\n\t",$e)."\n)";break;case'd':$zc=first(get_rows("SELECT format_type(typbasetype, typtypmod) AS base, typnotnull::int AS notnull, typdefault, ".collation_name("typcollation")." AS collation
FROM pg_type WHERE oid = $t"));$I["definition"]="AS $zc[base]".($zc["collation"]?" COLLATE ".idf_escape($zc["collation"]):"").($zc["typdefault"]!=""?" DEFAULT $zc[typdefault]":"").($zc["notnull"]?" NOT NULL":"");foreach(get_rows("SELECT conname, pg_get_constraintdef(oid) AS definition FROM pg_constraint WHERE contypid = $t AND contype != 'n' ORDER BY conname")as$J)$I["definition"].=" CONSTRAINT ".idf_escape($J["conname"])." $J[definition]";break;case'r':$vi=first(get_rows("SELECT format_type(rngsubtype, NULL) AS subtype,
(SELECT opcname FROM pg_opclass WHERE oid = rngsubopc) AS subtype_opclass,
".collation_name("rngcollation")." AS collation,
NULLIF(rngcanonical, 0)::regproc::text AS canonical,
NULLIF(rngsubdiff, 0)::regproc::text AS subtype_diff".(min_version(14)?",
(SELECT typname FROM pg_type WHERE oid = rngmultitypid) AS multirange_type_name":"")."
FROM pg_range WHERE rngtypid = $t"));$jh=array();foreach(array("subtype"=>0,"subtype_opclass"=>1,"collation"=>1,"canonical"=>0,"subtype_diff"=>0,"multirange_type_name"=>1)as$x=>$Xc){if($vi[$x]!="")$jh[]=strtoupper($x)." = ".($Xc?idf_escape($vi[$x]):$vi[$x]);}$I["definition"]="AS RANGE (".implode(", ",$jh).")";}return$I;}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){return(string)get_val("SELECT current_schema()");}function
set_schema($L,$g=null){$I=connection($g)->query("SET search_path TO ".idf_escape($L));driver()->setUserTypes(types(true));return!!$I;}function
drop_sql(array$T){$I="";foreach(drop_kinds($T)as$pf=>$xg)$I
.="DROP $pf IF EXISTS ".implode(", ",$xg).";\n";return($I?"$I\n":"");}function
foreign_keys_sql($R){$I="";$P=table_status1($R);$Hg=idf_escape($P['nspname']);$Dd=foreign_keys($R);ksort($Dd);foreach($Dd
as$Cd=>$Bd)$I
.="ALTER TABLE ONLY $Hg.".idf_escape($P['Name'])." ADD CONSTRAINT ".idf_escape($Cd)." ".preg_replace('~( REFERENCES )([^(.]+\()~',"\\1$Hg.\\2",$Bd["definition"]).";\n";return($I?"$I\n":$I);}function
indexes_sql($R,$ii=""){$I="";$G="SELECT indexdef FROM pg_catalog.pg_indexes WHERE schemaname = current_schema() AND tablename = ".q($R).($ii!=""?" AND indexname != ".q($ii):"");foreach(get_rows($G,null,"-- ")as$J)$I
.="\n\n$J[indexdef];";return$I;}function
create_sql($R,$Ea,$Nj){$Li=array();$kj=array();$lj=array();$jj=array();$P=table_status1($R);$Hg=idf_escape($P['nspname']);if(is_view($P)){$tl=view($R);$h="CREATE ".strtoupper($P["Engine"])." $Hg.".idf_escape($R)." AS ".rtrim($tl["select"],";").";";return
rtrim($h.indexes_sql($R),';');}$n=fields($R);if(count($P)<2||empty($n))return"";$I="CREATE TABLE $Hg.".idf_escape($P['Name'])." (\n    ";$Xj=q("$Hg.".idf_escape($P['Name']));foreach($n
as$m){$mj="";if($m['default']=="nextval('$P[Name]_$m[field]_seq')"){$mj="$Hg.".idf_escape("$P[Name]_$m[field]_seq");$m['default']=null;$m['full_type']=preg_replace('~int(eger)?~','serial',$m['full_type']);}$Gh=idf_escape($m['field']).' '.$m['full_type'].preg_replace('~(nextval\(\')([^.\']+\')~','\1'.str_replace("'","''",$P['nspname']).'.\2',default_value($m)).($m['null']?"":" NOT NULL");$Li[]=$Gh;if(preg_match('~nextval\(\'([^\']+)\'\)~',$m['default'],$Of)){$ij=$Of[1];$Bj=first(get_rows((min_version(10)?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q(idf_unescape($ij)):"SELECT * FROM $ij"),null,"-- "));$kj[]=($Nj=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $Hg.$ij;\n":"")."CREATE SEQUENCE $Hg.$ij INCREMENT $Bj[increment_by] MINVALUE $Bj[min_value] MAXVALUE $Bj[max_value]"." CACHE $Bj[cache_value];";if(get_val("SELECT pg_get_serial_sequence($Xj, ".q($m['field']).")"))$lj[]="\n\nALTER SEQUENCE $Hg.$ij OWNED BY $Hg.".idf_escape($P['Name']).".".idf_escape($m['field']).";";if($Ea)$jj[]="$Hg.$ij";}elseif($Ea&&$m['auto_increment'])$jj[]=($mj?:get_val("SELECT pg_get_serial_sequence($Xj, ".q($m['field']).")"));}if(!empty($kj))$I=implode("\n\n",$kj)."\n\n$I";$ii="";foreach(indexes($R)as$Ie=>$v){if($v['type']=='PRIMARY'){$ii=$Ie;$Li[]="CONSTRAINT ".idf_escape($Ie)." PRIMARY KEY (".implode(', ',array_map('Adminer\idf_escape',$v['columns'])).")";}}foreach(driver()->checkConstraints($R)as$Bb=>$Db)$Li[]="CONSTRAINT ".idf_escape($Bb)." CHECK ($Db)";$I
.=implode(",\n    ",$Li)."\n)";$Ih=driver()->partitionsInfo($P['Name']);if($Ih)$I
.="\nPARTITION BY $Ih[partition_by]($Ih[partition])";$I
.="\nWITH (oids = ".($P['Oid']?'true':'false').");";$I
.=implode($lj);if($P['Comment'])$I
.="\n\nCOMMENT ON TABLE $Hg.".idf_escape($P['Name'])." IS ".q($P['Comment']).";";foreach($n
as$qd=>$m){if($m['comment'])$I
.="\n\nCOMMENT ON COLUMN $Hg.".idf_escape($P['Name']).".".idf_escape($qd)." IS ".q($m['comment']).";";}$I
.=indexes_sql($R,$ii);foreach(array_filter($jj)as$hj){$Bj=first(get_rows("SELECT last_value, is_called::int FROM $hj",null,"-- "));if($Bj['is_called'])$I
.="\n\nDO \$\$ BEGIN PERFORM setval(".q($hj).", $Bj[last_value]); END \$\$;";}return
rtrim($I,';');}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
truncate_all_sql(array$T){return($T?"TRUNCATE ".implode(", ",array_map('Adminer\table',$T)).";\n\n":"");}function
trigger_sql($R){$P=table_status1($R);$I="";foreach(triggers($R)as$Ek=>$Dk){$Fk=trigger($Ek,$P['Name']);$I
.="\nCREATE TRIGGER ".idf_escape($Fk['Trigger'])." $Fk[Timing] $Fk[Event] ON ".idf_escape($P["nspname"]).".".idf_escape($P['Name'])." $Fk[Type] $Fk[Statement];;\n";}return$I;}function
use_sql($Zb,$Nj=""){$C=idf_escape($Zb);$I="";if(preg_match('~CREATE~',$Nj)){if($Nj=="DROP+CREATE")$I="DROP DATABASE IF EXISTS $C;\n";$I
.="CREATE DATABASE $C;\n";}return"$I\\connect $C";}function
show_variables(){return
get_rows("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(min_version(9.2)?"pid":"procpid"));}function
convert_field(array$m){}function
unconvert_field(array$m,$I){return$I;}function
support($od){return
preg_match('~^(check|columns|comment|database|drop_col|dump|descidx|fast_status|indexes|kill|partial_indexes|routine|scheme|sequence|sql|table'.'|transaction_ddl|trigger|type|variables|view'.(min_version(9.3)?'|materializedview':'').(min_version(11)?'|procedure':'').(connection()->flavor=='cockroach'?'':'|deferrable').(connection()->flavor=='cockroach'?'':'|processlist').')$~',$od);}function
kill_process($t){return
queries("SELECT pg_terminate_backend(".number($t).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){return
get_val("SHOW max_connections");}}add_driver("sqlite","SQLite");if(isset($_GET["sqlite"])){define('Adminer\DRIVER',"sqlite");if(class_exists("SQLite3")&&$_GET["ext"]!="pdo"){abstract
class
SqliteDb
extends
SqlDb{var$extension="SQLite3";private$link;function
attach($o,$V,$E){$this->link=new
\SQLite3($o);$sl=$this->link->version();$this->server_info=$sl["versionString"];return'';}function
query($G,$Pk=false){$H=@$this->link->query($G);$this->error="";if(!$H){$this->errno=$this->link->lastErrorCode();$this->error=$this->link->lastErrorMsg();return
false;}elseif($H->numColumns())return
new
Result($H);$this->affected_rows=$this->link->changes();return
true;}function
quote($Q){return(is_utf8($Q)?"'".$this->link->escapeString($Q)."'":"x'".bin2hex($Q)."'");}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($H){$this->result=$H;}function
fetch_assoc(){return$this->result->fetchArray(SQLITE3_ASSOC);}function
fetch_row(){return$this->result->fetchArray(SQLITE3_NUM);}function
fetch_field(){$Ok=array(1=>"integer","real","text","blob","null");$d=$this->offset++;$U=$this->result->columnType($d);return(object)array("name"=>$this->result->columnName($d),"type"=>($U==SQLITE3_TEXT?15:0),"native_type"=>$Ok[$U],"charsetnr"=>($U==SQLITE3_BLOB?63:0),);}}}elseif(extension_loaded("pdo_sqlite")){abstract
class
SqliteDb
extends
PdoDb{var$extension="PDO_SQLite";function
attach($o,$V,$E){return$this->dsn(DRIVER.":$o","","");}function
quote($Q){return(is_utf8($Q)?parent::quote($Q):"x'".bin2hex($Q)."'");}}}if(class_exists('Adminer\SqliteDb')){class
Db
extends
SqliteDb{function
attach($o,$V,$E){parent::attach($o,$V,$E);$this->query("PRAGMA foreign_keys = 1");$this->query("PRAGMA busy_timeout = 500");return'';}function
select_db($o){$G="ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$o)?$o:dirname($_SERVER["SCRIPT_FILENAME"])."/$o")." AS a";if(is_readable($o)&&$this->query($G))return!self::attach($o,'','');return
false;}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLite3","PDO_SQLite");static$jush="sqlite";protected$types=array(array("integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0));var$insertFunctions=array();var$editFunctions=array("integer|real|numeric"=>"+/-","text"=>"||",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("hex","length","lower","round","unixepoch","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");static
function
connect($N,$V,$E){if($E!="")return'Database does not support password.';return
parent::connect(":memory:","","");}function
__construct(Db$f){parent::__construct($f);if(min_version(3.31,0,$f))$this->generated=array("STORED","VIRTUAL");if(min_version(3.37,0,$f))$this->types[0]["any"]=0;}function
structuredTypes(){return
array_keys($this->types[0]);}function
quoteBinary($Ui){return"x".q(bin2hex($Ui));}function
engines(){$I=array("table");if(min_version("3.8.2")){if(min_version(3.37)){$I[]="STRICT";$I[]="STRICT, WITHOUT ROWID";}$I[]="WITHOUT ROWID";}return$I;}function
insertUpdate($R,array$K,array$ii){$pl=array();foreach($K
as$O)$pl[]="(".implode(", ",$O).")";return
queries("REPLACE INTO ".table($R)." (".implode(", ",array_keys(reset($K))).") VALUES\n".implode(",\n",$pl));}function
tableHelp($C,$ff=false){if(preg_match('~^sqlite_(seq|stat.)~',$C,$B))return"fileformat2.html#$B[1]tab";if(preg_match('~^sqlite(_temp)?_(master|schema)$~',$C))return"schematab.html";}function
checkConstraints($R){preg_match_all('~ CHECK *(\( *(((?>[^()]*[^() ])|(?1))*) *\))~',get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R),0,$this->conn),$Of);return
array_combine($Of[2],$Of[2]);}function
allFields(){$I=array();foreach(tables_list()as$R=>$U){foreach(fields($R)as$m)$I[$R][]=$m;}return$I;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($Fd){return
array();}function
limit($G,$Z,$z,$Rg=0,$gj=" "){return" $G$Z".($z?$gj."LIMIT $z".($Rg?" OFFSET $Rg":""):"");}function
limit1($R,$G,$Z,$gj="\n"){return(preg_match('~^INTO~',$G)||get_val("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($G,$Z,1,0,$gj):" $G WHERE rowid = (SELECT rowid FROM ".table($R).$Z.$gj."LIMIT 1)");}function
db_collation($j,array$qb){return
get_val("PRAGMA encoding");}function
logged_user(){return
get_current_user();}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view') ORDER BY (name LIKE 'sqlite_%'), name");}function
count_tables(array$i){return
array();}function
db_status(){$Bh=get_val("PRAGMA page_size");$Nd=get_val("PRAGMA freelist_count")*$Bh;return
array("Data_length"=>get_val("PRAGMA page_count")*$Bh-$Nd,"Index_length"=>0,"Data_free"=>$Nd,);}function
table_status($C="",$nd=false){$I=array();$K=array();if(!$nd&&$C==""){connection()->query("PRAGMA optimize = 0x10002");$K=get_key_vals("SELECT tbl, MAX(CAST(stat AS integer)) FROM sqlite_stat1 GROUP BY tbl");}foreach(get_rows("SELECT name AS Name, type AS Engine, sql, 'rowid' AS Oid, '' AS Auto_increment FROM sqlite_master WHERE type IN ('table', 'view') ".($C!=""?"AND name = ".q($C):"ORDER BY (name LIKE 'sqlite_%'), name"))as$J){if($J["Engine"]=="table"){$Pj=preg_replace('~.*\)~s','',$J["sql"]);$J["Engine"]=implode(", ",array_filter(array((preg_match('~\bSTRICT\b~i',$Pj)?"STRICT":0),(preg_match('~\bWITHOUT\s+ROWID\b~i',$Pj)?"WITHOUT ROWID":0),)))?:"table";}unset($J["sql"]);$J["Rows"]=idx($K,$J["Name"],0);$I[$J["Name"]]=$J;}if(!$nd){foreach(get_rows("SELECT * FROM sqlite_sequence".($C!=""?" WHERE name = ".q($C):""),null,"")as$J)$I[$J["name"]]["Auto_increment"]=$J["seq"];}return$I;}function
is_view(array$S){return$S["Engine"]=="view";}function
fk_support(array$S){return!get_val("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
fields($R){$I=array();$Cj=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R));$ni=array("select"=>1,"where"=>1,"order"=>1);if(!preg_match('~^sqlite(_temp)?_(master|schema)$~',$R))$ni+=array("insert"=>1,"update"=>1);foreach(get_rows("PRAGMA table_".(min_version(3.31)?"x":"")."info(".table($R).")")as$J){$C=$J["name"];$U=strtolower($J["type"]);$k=$J["dflt_value"];$I[$C]=array("field"=>$C,"type"=>(preg_match('~int~i',$U)?"integer":(preg_match('~char|clob|text~i',$U)?"text":(preg_match('~blob~i',$U)?"blob":(preg_match('~real|floa|doub~i',$U)?"real":(preg_match('~any~i',$U)?"any":"numeric"))))),"full_type"=>$U,"default"=>(preg_match("~^'(.*)'$~",$k,$B)?str_replace("''","'",$B[1]):($k=="NULL"?null:$k)),"null"=>!$J["notnull"],"privileges"=>$ni,"primary"=>$J["pk"],);if($J["pk"]&&preg_match('~\bAUTOINCREMENT\b~i',$Cj))$I[$C]["auto_increment"]=true;}$u='(("[^"]*+")+|[a-z0-9_]+)';preg_match_all('~'.$u.'\s+text\s+COLLATE\s+(\'[^\']+\'|\S+)~i',$Cj,$Of,PREG_SET_ORDER);foreach($Of
as$B){$C=str_replace('""','"',preg_replace('~^"|"$~','',$B[1]));if($I[$C])$I[$C]["collation"]=trim($B[3],"'");}preg_match_all('~'.$u.'\s.*GENERATED ALWAYS AS \((.+)\) (STORED|VIRTUAL)~i',$Cj,$Of,PREG_SET_ORDER);foreach($Of
as$B){$C=str_replace('""','"',preg_replace('~^"|"$~','',$B[1]));$I[$C]["default"]=$B[3];$I[$C]["generated"]=strtoupper($B[4]);}return$I;}function
indexes($R,$g=null){$g=connection($g);$I=array();$Cj=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R),0,$g);if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$Cj,$B)){$I[""]=array("type"=>"PRIMARY","columns"=>array(),"lengths"=>array(),"descs"=>array());preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$B[1],$Of,PREG_SET_ORDER);foreach($Of
as$B){$I[""]["columns"][]=idf_unescape($B[2]).$B[4];$I[""]["descs"][]=(preg_match('~DESC~i',$B[5])?'1':null);}}if(!$I){foreach(fields($R)as$C=>$m){if($m["primary"])$I[""]=array("type"=>"PRIMARY","columns"=>array($C),"lengths"=>array(),"descs"=>array(null));}}$Hj=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($R),$g);foreach(get_rows("PRAGMA index_list(".table($R).")",$g)as$J){$C=$J["name"];$v=array("type"=>($J["unique"]?"UNIQUE":"INDEX"));$v["lengths"]=array();$v["descs"]=array();foreach(get_rows("PRAGMA index_info(".idf_escape($C).")",$g)as$Ti){$v["columns"][]=$Ti["name"];$v["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($C).' ON '.idf_escape($R),'~').' \((.*)\)$~i',$Hj[$C],$Ei)){preg_match_all('/("[^"]*+")+( DESC)?/',$Ei[2],$Of);foreach($Of[2]as$x=>$X){if($X)$v["descs"][$x]='1';}}if(!$I[""]||$v["type"]!="UNIQUE"||$v["columns"]!=$I[""]["columns"]||$v["descs"]!=$I[""]["descs"]||!preg_match("~^sqlite_~",$C))$I[$C]=$v;}return$I;}function
foreign_keys($R){$I=array();foreach(get_rows("PRAGMA foreign_key_list(".table($R).")")as$J){$p=&$I[$J["id"]];if(!$p)$p=$J;$p["source"][]=$J["from"];$p["target"][]=$J["to"];}return$I;}function
view($C){return
array("select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\s+~iU','',get_val("SELECT sql FROM sqlite_master WHERE type = 'view' AND name = ".q($C))));}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):array());}function
information_schema($j,$L=""){return
false;}function
error(){return
h(connection()->error);}function
check_sqlite_name($C){$jd="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($jd)\$~",$C)){connection()->error=sprintf('Please use one of the extensions %s.',str_replace("|",", ",$jd));return
false;}return
true;}function
create_database($j,$pb){if(file_exists($j)){connection()->error='File exists.';return
false;}if(!check_sqlite_name($j))return
false;try{$_=new
Db();$_->attach($j,'','');}catch(\Exception$ad){connection()->error=$ad->getMessage();return
false;}$_->query('PRAGMA encoding = "UTF-8"');$_->query('CREATE TABLE adminer (i)');$_->query('DROP TABLE adminer');return
true;}function
drop_databases(array$i){connection()->attach(":memory:",'','');foreach($i
as$j){if(!check_sqlite_name($j))return
false;if(!@unlink($j)){connection()->error='File exists.';return
false;}}return
true;}function
rename_database($C,$pb){if(!check_sqlite_name($C))return
false;connection()->attach(":memory:",'','');connection()->error='File exists.';return@rename(DB,$C);}function
auto_increment(){return" PRIMARY KEY AUTOINCREMENT";}function
alter_table($R,$C,array$n,array$Hd,$ub,$Pc,$pb,$Ea,$Mh){$dl=($R==""||$Hd||$Pc);foreach($n
as$m){if($m[0]!=""||!$m[1]||$m[2]){$dl=true;break;}}$b=array();$wh=array();foreach($n
as$m){if($m[1]){$b[]=($dl?$m[1]:"ADD ".implode($m[1]));if($m[0]!="")$wh[$m[0]]=$m[1][0];}}if(!$dl){foreach($b
as$X){if(!queries("ALTER TABLE ".table($R)." $X"))return
false;}if($R!=$C&&!queries("ALTER TABLE ".table($R)." RENAME TO ".table($C)))return
false;}elseif(!recreate_table($R,$C,$b,$wh,$Hd,$Ea,array(),"","",$Pc))return
false;if($Ea){queries("BEGIN");queries("UPDATE sqlite_sequence SET seq = $Ea WHERE name = ".q($C));if(!connection()->affected_rows)queries("INSERT INTO sqlite_sequence (name, seq) VALUES (".q($C).", $Ea)");queries("COMMIT");}return
true;}function
recreate_table($R,$C,array$n,array$wh,array$Hd,$Ea="",$w=array(),$Cc="",$la="",$Pc=""){if($R!=""){if(!$n){foreach(fields($R)as$x=>$m){if($w)$m["auto_increment"]=0;$n[]=process_field($m,$m);$wh[$x]=idf_escape($x);}}$ji=false;foreach($n
as$m){if($m[6])$ji=true;}$Ec=array();foreach($w
as$x=>$X){if($X[2]=="DROP"){$Ec[$X[1]]=true;unset($w[$x]);}}foreach(indexes($R)as$lf=>$v){$e=array();foreach($v["columns"]as$x=>$d){if(!$wh[$d])continue
2;$e[]=$wh[$d].($v["descs"][$x]?" DESC":"");}if(!$Ec[$lf]){if($v["type"]!="PRIMARY"||!$ji)$w[]=array($v["type"],$lf,$e);}}foreach($w
as$x=>$X){if($X[0]=="PRIMARY"){unset($w[$x]);$Hd[]="  PRIMARY KEY (".implode(", ",$X[2]).")";}}foreach(foreign_keys($R)as$lf=>$p){foreach($p["source"]as$x=>$d){if(!$wh[$d])continue
2;$p["source"][$x]=idf_unescape($wh[$d]);}if(!isset($Hd[" $lf"]))$Hd[]=" ".format_foreign_key($p);}queries("BEGIN");}$Za=array();foreach($n
as$m){if(preg_match('~GENERATED~',$m[3]))unset($wh[array_search($m[0],$wh)]);$Za[]="  ".implode($m);}$Za=array_merge($Za,array_filter($Hd));foreach(driver()->checkConstraints($R)as$cb){if($cb!=$Cc)$Za[]="  CHECK ($cb)";}if($la)$Za[]="  CHECK ($la)";$jk=($R!=""&&$R==$C?"adminer_$C":$C);if(!$Pc&&$R!="")$Pc=idx(table_status1($R),"Engine");if(!queries("CREATE TABLE ".table($jk)." (\n".implode(",\n",$Za)."\n)".($Pc!="table"&&in_array($Pc,driver()->engines())?" $Pc":"")))return
false;if($R!=""){if($wh&&!queries("INSERT INTO ".table($jk)." (".implode(", ",$wh).") SELECT ".implode(", ",array_map('Adminer\idf_escape',array_keys($wh)))." FROM ".table($R)))return
false;$Jk=array();foreach(triggers($R)as$Hk=>$qk){$Fk=trigger($Hk,$R);$Jk[]="CREATE TRIGGER ".idf_escape($Hk)." ".implode(" ",$qk)." ON ".table($C)."\n$Fk[Statement]";}$Ea=$Ea?"":get_val("SELECT seq FROM sqlite_sequence WHERE name = ".q($R));if(!queries("DROP TABLE ".table($R))||($R==$C&&!queries("ALTER TABLE ".table($jk)." RENAME TO ".table($C)))||!alter_indexes($C,$w))return
false;if($Ea)queries("UPDATE sqlite_sequence SET seq = $Ea WHERE name = ".q($C));foreach($Jk
as$Fk){if(!queries($Fk))return
false;}queries("COMMIT");}return
true;}function
index_sql($R,$U,$C,$e){return"CREATE $U ".($U!="INDEX"?"INDEX ":"").idf_escape($C!=""?$C:uniqid($R."_"))." ON ".table($R)." $e";}function
alter_indexes($R,$b){foreach($b
as$ii){if($ii[0]=="PRIMARY")return
recreate_table($R,$R,array(),array(),array(),"",$b);}foreach(array_reverse($b)as$X){if(!queries($X[2]=="DROP"?"DROP INDEX ".idf_escape($X[1]):index_sql($R,$X[0],$X[1],"(".implode(", ",$X[2]).")")))return
false;}return
true;}function
truncate_tables(array$T){return
apply_queries("DELETE FROM",$T);}function
drop_views(array$ul){return
apply_queries("DROP VIEW",$ul);}function
drop_tables(array$T){return
apply_queries("DROP TABLE",$T);}function
move_tables(array$T,array$ul,$hk){return
false;}function
trigger($C,$R){if($C=="")return
array("Statement"=>"BEGIN\n\t;\nEND");$u='(?:[^`"\s]+|`[^`]*`|"[^"]*")+';$Ik=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$u\\s*(".implode("|",$Ik["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($u))?\\s+ON\\s*$u\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",get_val("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($C)),$B);$Ng=$B[3];return
array("Timing"=>strtoupper($B[1]),"Event"=>strtoupper($B[2]).($Ng?" OF":""),"Of"=>idf_unescape($Ng),"Trigger"=>$C,"Statement"=>$B[4],);}function
triggers($R){$I=array();$Ik=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R))as$J){preg_match('~^CREATE\s+TRIGGER\s*(?:[^`"\s]+|`[^`]*`|"[^"]*")+\s*('.implode("|",$Ik["Timing"]).')\s*(.*?)\s+ON\b~i',$J["sql"],$B);$I[$J["name"]]=array($B[1],$B[2]);}return$I;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
last_id($H){return
get_val("SELECT LAST_INSERT_ROWID()");}function
explain(Db$f,$G){return$f->query("EXPLAIN QUERY PLAN $G");}function
found_rows(array$S,array$Z){}function
types($jd=false){return
array();}function
create_sql($R,$Ea,$Nj){$I=get_val("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($R));foreach(indexes($R)as$C=>$v){if($C=='')continue;$I
.=";\n\n".index_sql($R,$v['type'],$C,"(".implode(", ",array_map('Adminer\idf_escape',$v['columns'])).")");}return$I;}function
truncate_sql($R){return"DELETE FROM ".table($R);}function
use_sql($Zb,$Nj=""){return"";}function
trigger_sql($R){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R)));}function
show_variables(){$I=array();foreach(get_rows("PRAGMA pragma_list")as$J){$C=$J["name"];if($C!="pragma_list"&&$C!="compile_options"){$I[$C]=array($C,'');foreach(get_rows("PRAGMA $C")as$J)$I[$C][1].=implode(", ",$J)."\n";}}return$I;}function
show_status(){$I=array();foreach(get_vals("PRAGMA compile_options")as$ih)$I[]=explode("=",$ih,2)+array('','');return$I;}function
convert_field(array$m){}function
unconvert_field(array$m,$I){return$I;}function
support($od){return
preg_match('~^(check|columns|database|drop_col|dump|indexes|descidx|move_col|sql|status|table|transaction_ddl|trigger|variables|view|view_trigger)$~',$od);}}add_driver("mssql","MS SQL");if(isset($_GET["mssql"])){define('Adminer\DRIVER',"mssql");if(extension_loaded("sqlsrv")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="sqlsrv";private$link,$result;private
function
get_error(){$this->error="";foreach(sqlsrv_errors()as$l){$this->errno=$l["code"];$this->error
.="$l[message]\n";}$this->error=rtrim($this->error);}function
attach($N,$V,$E){$Cb=array("UID"=>$V,"PWD"=>$E,"CharacterSet"=>"UTF-8");$Ij=adminer()->connectSsl();if(isset($Ij["Encrypt"]))$Cb["Encrypt"]=$Ij["Encrypt"];if(isset($Ij["TrustServerCertificate"]))$Cb["TrustServerCertificate"]=$Ij["TrustServerCertificate"];$j=adminer()->database();if($j!="")$Cb["Database"]=$j;list($se,$Yh)=host_port($N);$this->link=@sqlsrv_connect($se.($Yh?",$Yh":""),$Cb);if($this->link){$Ne=sqlsrv_server_info($this->link);$this->server_info=$Ne['SQLServerVersion'];}else$this->get_error();return($this->link?'':$this->error);}function
quote($Q){$Qk=strlen($Q)!=strlen(utf8_decode($Q));return($Qk?"N":"")."'".str_replace("'","''",$Q)."'";}function
select_db($Zb){return$this->query(use_sql($Zb));}function
query($G,$Pk=false){$H=sqlsrv_query($this->link,$G);$this->error="";if(!$H){$this->get_error();return
false;}return$this->store_result($H);}function
multi_query($G){$this->result=sqlsrv_query($this->link,$G);$this->error="";if(!$this->result){$this->get_error();return
false;}return
true;}function
store_result($H=null){if(!$H)$H=$this->result;if(!$H)return
false;if(sqlsrv_field_metadata($H))return
new
Result($H);$this->affected_rows=sqlsrv_rows_affected($H);return
true;}function
next_result(){return$this->result?!!sqlsrv_next_result($this->result):false;}}class
Result{var$num_rows;private$result,$offset=0,$fields;function
__construct($H){$this->result=$H;}private
function
convert($J){foreach((array)$J
as$x=>$X){if(is_a($X,'DateTime'))$J[$x]=$X->format("Y-m-d H:i:s");}return$J;}function
fetch_assoc(){return$this->convert(sqlsrv_fetch_array($this->result,SQLSRV_FETCH_ASSOC));}function
fetch_row(){return$this->convert(sqlsrv_fetch_array($this->result,SQLSRV_FETCH_NUMERIC));}function
fetch_field(){if(!$this->fields)$this->fields=sqlsrv_field_metadata($this->result);$m=$this->fields[$this->offset++];$I=new
\stdClass;$I->name=$m["Name"];$I->type=($m["Type"]==1?254:15);$I->charsetnr=(in_array($m["Type"],array(-2,-3,-4))?63:0);return$I;}function
seek($Rg){for($s=0;$s<$Rg;$s++)sqlsrv_fetch($this->result);}}function
last_id($H){return(string)get_val("SELECT SCOPE_IDENTITY()");}function
explain(Db$f,$G){$f->query("SET SHOWPLAN_ALL ON");$I=$f->query($G);$f->query("SET SHOWPLAN_ALL OFF");return$I;}}else{abstract
class
MssqlDb
extends
PdoDb{function
select_db($Zb){return$this->query(use_sql($Zb));}function
lastInsertId(){return$this->pdo->lastInsertId();}}function
last_id($H){return
connection()->lastInsertId();}function
explain(Db$f,$G){}if(extension_loaded("pdo_sqlsrv")){class
Db
extends
MssqlDb{var$extension="PDO_SQLSRV";function
attach($N,$V,$E){list($se,$Yh)=host_port($N);return$this->dsn("sqlsrv:Server=$se".($Yh?",$Yh":""),$V,$E);}}}elseif(extension_loaded("pdo_dblib")){class
Db
extends
MssqlDb{var$extension="PDO_DBLIB";function
attach($N,$V,$E){list($se,$Yh)=host_port($N);return$this->dsn("dblib:charset=utf8;host=$se".($Yh?(is_numeric($Yh)?";port=":";unix_socket=").$Yh:""),$V,$E);}}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLSRV","PDO_SQLSRV","PDO_DBLIB");static$jush="mssql";var$insertFunctions=array("date|time"=>"getdate");var$editFunctions=array("int|decimal|real|float|money|datetime"=>"+/-","char|text"=>"+",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");var$functions=array("len","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$generated=array("PERSISTED","VIRTUAL");var$onActions="NO ACTION|CASCADE|SET NULL|SET DEFAULT";static
function
connect($N,$V,$E){if($N=="")$N="localhost:1433";return
parent::connect($N,$V,$E);}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("tinyint"=>3,"smallint"=>5,"int"=>10,"bigint"=>20,"bit"=>1,"decimal"=>0,"real"=>12,"float"=>53,"smallmoney"=>10,"money"=>20),'Date and time'=>array("date"=>10,"smalldatetime"=>19,"datetime"=>19,"datetime2"=>19,"time"=>8,"datetimeoffset"=>10),'Strings'=>array("char"=>8000,"varchar"=>8000,"text"=>2147483647,"nchar"=>4000,"nvarchar"=>4000,"ntext"=>1073741823),'Binary'=>array("binary"=>8000,"varbinary"=>8000,"image"=>2147483647),);}function
insertUpdate($R,array$K,array$ii){$n=fields($R);$Yk=array();$Z=array();$O=reset($K);$e="c".implode(", c",range(1,count($O)));$Va=0;$Te=array();foreach($O
as$x=>$X){$Va++;$C=idf_unescape($x);if(!$n[$C]["auto_increment"])$Te[$x]="c$Va";if(isset($ii[$C]))$Z[]="$x = c$Va";else$Yk[]="$x = c$Va";}$pl=array();foreach($K
as$O)$pl[]="(".implode(", ",$O).")";if($Z){$xe=queries("SET IDENTITY_INSERT ".table($R)." ON");$I=queries("MERGE ".table($R)." USING (VALUES\n\t".implode(",\n\t",$pl)."\n) AS source ($e) ON ".implode(" AND ",$Z).($Yk?"\nWHEN MATCHED THEN UPDATE SET ".implode(", ",$Yk):"")."\nWHEN NOT MATCHED THEN INSERT (".implode(", ",array_keys($xe?$O:$Te)).") VALUES (".($xe?$e:implode(", ",$Te)).");");if($xe)queries("SET IDENTITY_INSERT ".table($R)." OFF");}else$I=queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($O)).") VALUES\n".implode(",\n",$pl));return$I;}function
begin(){return
queries("BEGIN TRANSACTION");}function
quoteBinary($Ui){return"0x".bin2hex($Ui);}function
tableHelp($C,$ff=false){$Ff=array("sys"=>"catalog-views/sys-","INFORMATION_SCHEMA"=>"information-schema-views/",);$_=$Ff[get_schema()];if($_)return"relational-databases/system-$_".preg_replace('~_~','-',strtolower($C))."-transact-sql";}}function
idf_escape($u){return"[".str_replace("]","]]",$u)."]";}function
table($u){return($_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($u);}function
get_databases($Fd){return
get_vals("SELECT name FROM sys.databases WHERE name NOT IN ('master', 'tempdb', 'model', 'msdb')");}function
limit($G,$Z,$z,$Rg=0,$gj=" "){return($z?" TOP (".($z+$Rg).")":"")." $G$Z";}function
limit1($R,$G,$Z,$gj="\n"){return
limit($G,$Z,1,0,$gj);}function
db_collation($j,array$qb){return
get_val("SELECT collation_name FROM sys.databases WHERE name = ".q($j));}function
logged_user(){return
get_val("SELECT SUSER_NAME()");}function
tables_list(){return
get_key_vals("SELECT name, type_desc FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ORDER BY name");}function
count_tables(array$i){$I=array();foreach($i
as$j){connection()->select_db($j);$I[$j]=get_val("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES");}return$I;}function
table_status($C="",$nd=false){$I=array();$vj=array();foreach(get_rows("SELECT object_id, SUM(CASE WHEN index_id < 2 THEN row_count ELSE 0 END) AS [Rows],
SUM(CASE WHEN index_id < 2 THEN used_page_count ELSE 0 END) * 8192 AS Data_length,
SUM(CASE WHEN index_id > 1 THEN used_page_count ELSE 0 END) * 8192 AS Index_length,
SUM(reserved_page_count - used_page_count) * 8192 AS Data_free
FROM sys.dm_db_partition_stats
GROUP BY object_id",null,"")as$J){$Mg=$J["object_id"];unset($J["object_id"]);$vj[$Mg]=$J;}foreach(get_rows("SELECT ao.object_id, ao.name AS Name, ao.type_desc AS Engine,
	(SELECT cast(value as varchar(max)) FROM fn_listextendedproperty(default, 'SCHEMA', schema_name(schema_id), 'TABLE', ao.name, null, null)) AS Comment
FROM sys.all_objects AS ao
WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ".($C!=""?"AND name = ".q($C):"ORDER BY name"))as$J){$Mg=$J["object_id"];unset($J["object_id"]);$I[$J["Name"]]=$J+idx($vj,$Mg,array());}return$I;}function
is_view(array$S){return$S["Engine"]=="VIEW";}function
fk_support(array$S){return
true;}function
fields($R){$wb=get_key_vals("SELECT objname, cast(value as varchar(max)) FROM fn_listextendedproperty('MS_DESCRIPTION', 'schema', ".q(get_schema()).", 'table', ".q($R).", 'column', NULL)");$I=array();$Wj=get_val("SELECT object_id FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') AND name = ".q($R));foreach(get_rows("SELECT c.max_length, c.precision, c.scale, c.name, c.is_nullable, c.is_identity, c.collation_name,
	t.name type, d.definition [default], d.name default_constraint, i.is_primary_key
FROM sys.all_columns c
JOIN sys.types t ON c.user_type_id = t.user_type_id
LEFT JOIN sys.default_constraints d ON c.default_object_id = d.object_id
LEFT JOIN sys.index_columns ic ON c.object_id = ic.object_id AND c.column_id = ic.column_id
LEFT JOIN sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
WHERE c.object_id = ".q($Wj))as$J){$U=$J["type"];$y=(preg_match("~char|binary~",$U)?intval($J["max_length"])/($U[0]=='n'?2:1):($U=="decimal"?"$J[precision],$J[scale]":""));$I[$J["name"]]=array("field"=>$J["name"],"full_type"=>$U.($y?"($y)":""),"type"=>$U,"length"=>$y,"default"=>(preg_match("~^\('(.*)'\)$~",$J["default"],$B)?str_replace("''","'",$B[1]):$J["default"]),"default_constraint"=>$J["default_constraint"],"null"=>$J["is_nullable"],"auto_increment"=>$J["is_identity"],"collation"=>$J["collation_name"],"privileges"=>array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1),"primary"=>$J["is_primary_key"],"comment"=>$wb[$J["name"]],);}foreach(get_rows("SELECT * FROM sys.computed_columns WHERE object_id = ".q($Wj))as$J){$I[$J["name"]]["generated"]=($J["is_persisted"]?"PERSISTED":"VIRTUAL");$I[$J["name"]]["default"]=$J["definition"];}return$I;}function
indexes($R,$g=null){$I=array();foreach(get_rows("SELECT i.name, key_ordinal, is_unique, is_primary_key, c.name AS column_name, is_descending_key
FROM sys.indexes i
INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
WHERE OBJECT_NAME(i.object_id) = ".q($R),$g)as$J){$C=$J["name"];$I[$C]["type"]=($J["is_primary_key"]?"PRIMARY":($J["is_unique"]?"UNIQUE":"INDEX"));$I[$C]["lengths"]=array();$I[$C]["columns"][$J["key_ordinal"]]=$J["column_name"];$I[$C]["descs"][$J["key_ordinal"]]=($J["is_descending_key"]?'1':null);}return$I;}function
view($C){return
array("select"=>preg_replace('~^(?:[^[]|\[[^]]*])*\s+AS\s+~isU','',get_val("SELECT VIEW_DEFINITION FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = SCHEMA_NAME() AND TABLE_NAME = ".q($C))));}function
collations(){$I=array();foreach(get_vals("SELECT name FROM fn_helpcollations()")as$pb)$I[preg_replace('~_.*~','',$pb)][]=$pb;return$I;}function
information_schema($j,$L=""){return
in_array($L!=""?$L:get_schema(),array("INFORMATION_SCHEMA","sys"));}function
error(){return
nl_br(h(preg_replace('~^(\[[^]]*])+~m','',connection()->error)));}function
create_database($j,$pb){return
queries("CREATE DATABASE ".idf_escape($j).(preg_match('~^[a-z0-9_]+$~i',$pb)?" COLLATE $pb":""));}function
drop_databases(array$i){return!!queries("DROP DATABASE ".implode(", ",array_map('Adminer\idf_escape',$i)));}function
rename_database($C,$pb){if(preg_match('~^[a-z0-9_]+$~i',$pb))queries("ALTER DATABASE ".idf_escape(DB)." COLLATE $pb");queries("ALTER DATABASE ".idf_escape(DB)." MODIFY NAME = ".idf_escape($C));return
true;}function
auto_increment(){return" IDENTITY".($_POST["Auto_increment"]!=""?"(".number($_POST["Auto_increment"]).",1)":"")." PRIMARY KEY";}function
alter_table($R,$C,array$n,array$Hd,$ub,$Pc,$pb,$Ea,$Mh){$b=array();$wb=array();$sh=fields($R);foreach($n
as$m){$d=idf_escape($m[0]);$X=$m[1];if(!$X)$b["DROP"][]=" COLUMN $d";else{$X[1]=preg_replace("~( COLLATE )'(\\w+)'~",'\1\2',$X[1]);$wb[$m[0]]=$X[5];unset($X[5]);if(preg_match('~ AS ~',$X[3]))unset($X[1],$X[2]);if($m[0]=="")$b["ADD"][]="\n  ".implode("",$X).($R==""?substr($Hd[$X[0]],16+strlen($X[0])):"");else{$k=$X[3];unset($X[3]);unset($X[6]);if($d!=$X[0])queries("EXEC sp_rename ".q(table($R).".$d").", ".q(idf_unescape($X[0])).", 'COLUMN'");$b["ALTER COLUMN ".implode("",$X)][]="";$rh=$sh[$m[0]];if(default_value($rh)!=$k){if($rh["default"]!==null)$b["DROP"][]=" ".idf_escape($rh["default_constraint"]);if($k)$b["ADD"][]="\n $k FOR $d";}}}}if($R==""){$ka=(array)$b["ADD"];foreach($Hd
as$x=>$X){if(!is_string($x))$ka[]="\n$X";}return
queries("CREATE TABLE ".table($C)." (".implode(",",$ka)."\n)");}if($R!=$C)queries("EXEC sp_rename ".q(table($R)).", ".q($C));if($Hd)$b[""]=$Hd;foreach($b
as$x=>$X){if(!queries("ALTER TABLE ".table($C)." $x".implode(",",$X)))return
false;}foreach($wb
as$x=>$X){$ub=substr($X,9);queries("EXEC sp_dropextendedproperty @name = N'MS_Description', @level0type = N'Schema', @level0name = ".q(get_schema()).", @level1type = N'Table', @level1name = ".q($C).", @level2type = N'Column', @level2name = ".q($x));queries("EXEC sp_addextendedproperty
@name = N'MS_Description',
@value = $ub,
@level0type = N'Schema',
@level0name = ".q(get_schema()).",
@level1type = N'Table',
@level1name = ".q($C).",
@level2type = N'Column',
@level2name = ".q($x));}return
true;}function
alter_indexes($R,$b){$v=array();$Bc=array();foreach($b
as$X){if($X[2]=="DROP"){if($X[0]=="PRIMARY")$Bc[]=idf_escape($X[1]);else$v[]=idf_escape($X[1])." ON ".table($R);}elseif(!queries(($X[0]!="PRIMARY"?"CREATE $X[0] ".($X[0]!="INDEX"?"INDEX ":"").idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R):"ALTER TABLE ".table($R)." ADD PRIMARY KEY")." (".implode(", ",$X[2]).")"))return
false;}return(!$v||queries("DROP INDEX ".implode(", ",$v)))&&(!$Bc||queries("ALTER TABLE ".table($R)." DROP ".implode(", ",$Bc)));}function
found_rows(array$S,array$Z){}function
foreign_keys($R){$I=array();$bh=array("CASCADE","NO ACTION","SET NULL","SET DEFAULT");foreach(get_rows("EXEC sp_fkeys @fktable_name = ".q($R).", @fktable_owner = ".q(get_schema()))as$J){$p=&$I[$J["FK_NAME"]];$p["db"]=$J["PKTABLE_QUALIFIER"];$p["ns"]=$J["PKTABLE_OWNER"];$p["table"]=$J["PKTABLE_NAME"];$p["on_update"]=$bh[$J["UPDATE_RULE"]];$p["on_delete"]=$bh[$J["DELETE_RULE"]];$p["source"][]=$J["FKCOLUMN_NAME"];$p["target"][]=$J["PKCOLUMN_NAME"];}return$I;}function
truncate_tables(array$T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views(array$ul){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$ul)));}function
drop_tables(array$T){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$T)));}function
move_tables(array$T,array$ul,$hk){return
apply_queries("ALTER SCHEMA ".idf_escape($hk)." TRANSFER",array_merge($T,$ul));}function
trigger($C,$R){if($C=="")return
array();$K=get_rows("SELECT s.name [Trigger],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT'
	WHEN OBJECTPROPERTY(s.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE'
	WHEN OBJECTPROPERTY(s.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing],
c.text
FROM sysobjects s
JOIN syscomments c ON s.id = c.id
WHERE s.xtype = 'TR' AND s.name = ".q($C));$I=reset($K);if($I)$I["Statement"]=preg_replace('~^.+\s+AS\s+~isU','',$I["text"]);return$I;}function
triggers($R){$I=array();foreach(get_rows("SELECT sys1.name,
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT'
	WHEN OBJECTPROPERTY(sys1.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE'
	WHEN OBJECTPROPERTY(sys1.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing]
FROM sysobjects sys1
JOIN sysobjects sys2 ON sys1.parent_obj = sys2.id
WHERE sys1.xtype = 'TR' AND sys2.name = ".q($R))as$J)$I[$J["name"]]=array($J["Timing"],$J["Event"]);return$I;}function
trigger_options(){return
array("Timing"=>array("AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("AS"),);}function
schemas(){return
get_vals("SELECT name FROM sys.schemas");}function
get_schema(){if($_GET["ns"]!="")return$_GET["ns"];return
get_val("SELECT SCHEMA_NAME()");}function
set_schema($L,$g=null){$_GET["ns"]=$L;return
true;}function
create_sql($R,$Ea,$Nj){if(is_view(table_status1($R))){$tl=view($R);return"CREATE VIEW ".table($R)." AS $tl[select]";}$n=array();$ii=false;foreach(fields($R)as$C=>$m){$X=process_field($m,$m);if($X[6])$ii=true;$n[]=implode("",$X);}foreach(indexes($R)as$C=>$v){if(!$ii||$v["type"]!="PRIMARY"){$e=array();foreach($v["columns"]as$x=>$X)$e[]=idf_escape($X).($v["descs"][$x]?" DESC":"");$C=idf_escape($C);$n[]=($v["type"]=="INDEX"?"INDEX $C":"CONSTRAINT $C ".($v["type"]=="UNIQUE"?"UNIQUE":"PRIMARY KEY"))." (".implode(", ",$e).")";}}foreach(driver()->checkConstraints($R)as$C=>$cb)$n[]="CONSTRAINT ".idf_escape($C)." CHECK ($cb)";return"CREATE TABLE ".table($R)." (\n\t".implode(",\n\t",$n)."\n)";}function
foreign_keys_sql($R){$n=array();foreach(foreign_keys($R)as$Hd)$n[]=ltrim(format_foreign_key($Hd));return($n?"ALTER TABLE ".table($R)." ADD\n\t".implode(",\n\t",$n).";\n\n":"");}function
truncate_sql($R){return"TRUNCATE TABLE ".table($R);}function
use_sql($Zb,$Nj=""){return"USE ".idf_escape($Zb);}function
trigger_sql($R){$I="";foreach(triggers($R)as$C=>$Fk)$I
.=create_trigger(" ON ".table($R),trigger($C,$R)).";";return$I;}function
convert_field(array$m){}function
unconvert_field(array$m,$I){return$I;}function
support($od){return
preg_match('~^(check|comment|columns|database|drop_col|dump|fast_status|indexes|descidx|scheme|sql|table|transaction_ddl|trigger|view|view_trigger)$~',$od);}}add_driver("oracle","Oracle beta");if(isset($_GET["oracle"])){define('Adminer\DRIVER',"oracle");if(extension_loaded("oci8")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="oci8";var$_current_db;private$link;function
_error($Uc,$l){if(ini_bool("html_errors"))$l=html_entity_decode(strip_tags($l));$l=preg_replace('~^[^:]*: ~','',$l);$this->error=$l;}function
attach($N,$V,$E){$this->link=@oci_new_connect($V,$E,$N,"AL32UTF8");if($this->link){$this->server_info=oci_server_version($this->link);return'';}$l=oci_error();return($l?$l["message"]:'Unknown error.');}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($Zb){$this->_current_db=$Zb;return
true;}function
query($G,$Pk=false){$H=oci_parse($this->link,$G);$this->error="";if(!$H){$l=oci_error($this->link);$this->errno=$l["code"];$this->error=$l["message"];return
false;}set_error_handler(array($this,'_error'));$I=@oci_execute($H);restore_error_handler();if($I){if(oci_num_fields($H))return
new
Result($H);$this->affected_rows=oci_num_rows($H);oci_free_statement($H);}return$I;}function
timeout($rg){return
oci_set_call_timeout($this->link,$rg);}}class
Result{var$num_rows;private$result,$offset=1;function
__construct($H){$this->result=$H;}private
function
convert($J){foreach((array)$J
as$x=>$X){if(is_a($X,'OCILob')||is_a($X,'OCI-Lob'))$J[$x]=$X->load();}return$J;}function
fetch_assoc(){return$this->convert(oci_fetch_assoc($this->result));}function
fetch_row(){return$this->convert(oci_fetch_row($this->result));}function
fetch_field(){$d=$this->offset++;$I=new
\stdClass;$I->name=oci_field_name($this->result,$d);$U=oci_field_type($this->result,$d);$I->native_type=$U;$I->type=$U;$I->charsetnr=(preg_match("~raw|blob|bfile~",$U)?63:0);return$I;}}}elseif(extension_loaded("pdo_oci")){class
Db
extends
PdoDb{var$extension="PDO_OCI";var$_current_db;function
attach($N,$V,$E){return$this->dsn("oci:dbname=//$N;charset=AL32UTF8",$V,$E);}function
select_db($Zb){$this->_current_db=$Zb;return
true;}}}class
Driver
extends
SqlDriver{static$extensions=array("OCI8","PDO_OCI");static$jush="oracle";var$insertFunctions=array("date"=>"current_date","timestamp"=>"current_timestamp",);var$editFunctions=array("number|float|double"=>"+/-","date|timestamp"=>"+ interval/- interval","char|clob"=>"||",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("length","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("number"=>38,"binary_float"=>12,"binary_double"=>21),'Date and time'=>array("date"=>10,"timestamp"=>29,"interval year"=>12,"interval day"=>28),'Strings'=>array("char"=>2000,"varchar2"=>4000,"nchar"=>2000,"nvarchar2"=>4000,"clob"=>4294967295,"nclob"=>4294967295),'Binary'=>array("raw"=>2000,"long raw"=>2147483648,"blob"=>4294967295,"bfile"=>4294967296),);}function
begin(){return
true;}function
quoteBinary($Ui){return"HEXTORAW(".q(bin2hex($Ui)).")";}function
hasCStyleEscapes(){return
true;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($Fd){return
get_vals("SELECT DISTINCT tablespace_name FROM (
SELECT tablespace_name FROM user_tablespaces
UNION SELECT tablespace_name FROM all_tables WHERE tablespace_name IS NOT NULL
)
ORDER BY 1");}function
limit($G,$Z,$z,$Rg=0,$gj=" "){return($Rg?" * FROM (SELECT t.*, rownum AS rnum FROM (SELECT $G$Z) t WHERE rownum <= ".($z+$Rg).") WHERE rnum > $Rg":($z?" * FROM (SELECT $G$Z) WHERE rownum <= ".($z+$Rg):" $G$Z"));}function
limit1($R,$G,$Z,$gj="\n"){return" $G$Z";}function
db_collation($j,array$qb){return
get_val("SELECT value FROM nls_database_parameters WHERE parameter = 'NLS_CHARACTERSET'");}function
logged_user(){return
get_val("SELECT USER FROM DUAL");}function
get_current_db(){$j=connection()->_current_db?:DB;connection()->_current_db=null;return$j;}function
where_owner($fi,$_h="owner"){if(!$_GET["ns"])return'';return"$fi$_h = sys_context('USERENV', 'CURRENT_SCHEMA')";}function
views_table($e){$_h=where_owner('');return"(SELECT $e FROM all_views WHERE ".($_h?:"rownum < 0").")";}function
tables_list(){$tl=views_table("view_name");$_h=where_owner(" AND ");return
get_key_vals("SELECT table_name, 'table' FROM all_tables WHERE tablespace_name = ".q(DB)."$_h
UNION SELECT view_name, 'view' FROM $tl
ORDER BY 1");}function
count_tables(array$i){$I=array();foreach($i
as$j)$I[$j]=get_val("SELECT COUNT(*) FROM all_tables WHERE tablespace_name = ".q($j));return$I;}function
table_status($C="",$nd=false){$I=array();$Zi=q($C);$j=get_current_db();$tl=views_table("view_name");$_h=where_owner(" AND ","t.owner");foreach(get_rows('SELECT t.table_name "Name", \'table\' "Engine", s.bytes "Data_length", i.bytes "Index_length", t.num_rows "Rows"
FROM all_tables t
LEFT JOIN (SELECT segment_name, SUM(bytes) bytes FROM user_segments WHERE segment_type LIKE \'TABLE%\' GROUP BY segment_name) s ON s.segment_name = t.table_name
LEFT JOIN (SELECT i.table_name, SUM(s.bytes) bytes FROM user_indexes i
	JOIN user_segments s ON s.segment_name = i.index_name AND s.segment_type LIKE \'INDEX%\' GROUP BY i.table_name) i ON i.table_name = t.table_name
WHERE t.tablespace_name = '.q($j).$_h.($C!=""?" AND t.table_name = $Zi":"")."
UNION SELECT view_name, 'view', 0, 0, 0 FROM $tl".($C!=""?" WHERE view_name = $Zi":"")."
ORDER BY 1")as$J)$I[$J["Name"]]=$J;return$I;}function
is_view(array$S){return$S["Engine"]=="view";}function
fk_support(array$S){return
true;}function
fields($R){$I=array();$_h=where_owner(" AND ");foreach(get_rows("SELECT * FROM all_tab_columns WHERE table_name = ".q($R)."$_h ORDER BY column_id")as$J){$U=$J["DATA_TYPE"];$y="$J[DATA_PRECISION],$J[DATA_SCALE]";if($y==",")$y=$J["CHAR_COL_DECL_LENGTH"];$I[$J["COLUMN_NAME"]]=array("field"=>$J["COLUMN_NAME"],"full_type"=>$U.($y?"($y)":""),"type"=>strtolower($U),"length"=>$y,"default"=>$J["DATA_DEFAULT"],"null"=>($J["NULLABLE"]=="Y"),"privileges"=>array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1),);}return$I;}function
indexes($R,$g=null){$I=array();$_h=where_owner(" AND ","aic.table_owner");foreach(get_rows("SELECT aic.*, ac.constraint_type, atc.data_default
FROM all_ind_columns aic
LEFT JOIN all_constraints ac ON aic.index_name = ac.constraint_name AND aic.table_name = ac.table_name AND aic.index_owner = ac.owner
LEFT JOIN all_tab_cols atc ON aic.column_name = atc.column_name AND aic.table_name = atc.table_name AND aic.index_owner = atc.owner
WHERE aic.table_name = ".q($R)."$_h
ORDER BY ac.constraint_type, aic.column_position",$g)as$J){$Ie=$J["INDEX_NAME"];$sb=$J["DATA_DEFAULT"];$sb=($sb?trim($sb,'"'):$J["COLUMN_NAME"]);$I[$Ie]["type"]=($J["CONSTRAINT_TYPE"]=="P"?"PRIMARY":($J["CONSTRAINT_TYPE"]=="U"?"UNIQUE":"INDEX"));$I[$Ie]["columns"][]=$sb;$I[$Ie]["lengths"][]=($J["CHAR_LENGTH"]&&$J["CHAR_LENGTH"]!=$J["COLUMN_LENGTH"]?$J["CHAR_LENGTH"]:null);$I[$Ie]["descs"][]=($J["DESCEND"]&&$J["DESCEND"]=="DESC"?'1':null);}return$I;}function
view($C){$tl=views_table("view_name, text");$K=get_rows('SELECT text "select" FROM '.$tl.' WHERE view_name = '.q($C));return
reset($K);}function
collations(){return
array();}function
information_schema($j,$L=""){return($L!=""?$L:get_schema())=="INFORMATION_SCHEMA";}function
error(){return
h(connection()->error);}function
explain(Db$f,$G){$f->query("EXPLAIN PLAN FOR $G");return$f->query("SELECT * FROM plan_table");}function
found_rows(array$S,array$Z){}function
auto_increment(){return"";}function
alter_table($R,$C,array$n,array$Hd,$ub,$Pc,$pb,$Ea,$Mh){$b=$Bc=array();$sh=($R?fields($R):array());foreach($n
as$m){$X=$m[1];if($X&&$m[0]!=""&&idf_escape($m[0])!=$X[0])queries("ALTER TABLE ".table($R)." RENAME COLUMN ".idf_escape($m[0])." TO $X[0]");$rh=$sh[$m[0]];if($X&&$rh){$Tg=process_field($rh,$rh);if($X[2]==$Tg[2])$X[2]="";}if($X)$b[]=($R!=""?($m[0]!=""?"MODIFY (":"ADD ("):"  ").implode($X).($R!=""?")":"");else$Bc[]=idf_escape($m[0]);}if($R=="")return
queries("CREATE TABLE ".table($C)." (\n".implode(",\n",array_merge($b,$Hd))."\n)");return(!$b||queries("ALTER TABLE ".table($R)."\n".implode("\n",$b)))&&(!$Bc||queries("ALTER TABLE ".table($R)." DROP (".implode(", ",$Bc).")"))&&($R==$C||queries("ALTER TABLE ".table($R)." RENAME TO ".table($C)));}function
alter_indexes($R,$b){$Bc=array();$ri=array();foreach($b
as$X){if($X[0]!="INDEX"){$X[2]=preg_replace('~ DESC$~','',$X[2]);$h=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");array_unshift($ri,"ALTER TABLE ".table($R).$h);}elseif($X[2]=="DROP")$Bc[]=idf_escape($X[1]);else$ri[]="CREATE INDEX ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R)." (".implode(", ",$X[2]).")";}if($Bc)array_unshift($ri,"DROP INDEX ".implode(", ",$Bc));foreach($ri
as$G){if(!queries($G))return
false;}return
true;}function
foreign_keys($R){$I=array();$G="SELECT c_list.CONSTRAINT_NAME as NAME,
c_src.COLUMN_NAME as SRC_COLUMN,
c_dest.OWNER as DEST_DB,
c_dest.TABLE_NAME as DEST_TABLE,
c_dest.COLUMN_NAME as DEST_COLUMN,
c_list.DELETE_RULE as ON_DELETE
FROM ALL_CONSTRAINTS c_list, ALL_CONS_COLUMNS c_src, ALL_CONS_COLUMNS c_dest
WHERE c_list.CONSTRAINT_NAME = c_src.CONSTRAINT_NAME
AND c_list.R_CONSTRAINT_NAME = c_dest.CONSTRAINT_NAME
AND c_list.CONSTRAINT_TYPE = 'R'
AND c_src.TABLE_NAME = ".q($R);foreach(get_rows($G)as$J)$I[$J['NAME']]=array("db"=>$J['DEST_DB'],"table"=>$J['DEST_TABLE'],"source"=>array($J['SRC_COLUMN']),"target"=>array($J['DEST_COLUMN']),"on_delete"=>$J['ON_DELETE'],"on_update"=>null,);return$I;}function
truncate_tables(array$T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views(array$ul){return
apply_queries("DROP VIEW",$ul);}function
drop_tables(array$T){return
apply_queries("DROP TABLE",$T);}function
last_id($H){return"0";}function
schemas(){$I=get_vals("SELECT DISTINCT owner FROM dba_segments WHERE owner IN (SELECT username FROM dba_users WHERE default_tablespace NOT IN ('SYSTEM','SYSAUX')) ORDER BY 1");return($I?:get_vals("SELECT DISTINCT owner FROM all_tables WHERE tablespace_name = ".q(DB)." ORDER BY 1"));}function
get_schema(){return
get_val("SELECT sys_context('USERENV', 'SESSION_USER') FROM dual");}function
set_schema($L,$g=null){return!!connection($g)->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($L));}function
show_variables(){return
get_rows('SELECT name, display_value FROM v$parameter');}function
show_status(){$I=array();$K=get_rows('SELECT * FROM v$instance');foreach(reset($K)as$x=>$X)$I[]=array($x,$X);return$I;}function
process_list(){return
get_rows('SELECT
	sess.process AS "process",
	sess.username AS "user",
	sess.schemaname AS "schema",
	sess.status AS "status",
	sess.wait_class AS "wait_class",
	sess.seconds_in_wait AS "seconds_in_wait",
	sql.sql_text AS "sql_text",
	sess.machine AS "machine",
	sess.port AS "port"
FROM v$session sess LEFT OUTER JOIN v$sql sql
ON sql.sql_id = sess.sql_id
WHERE sess.type = \'USER\'
ORDER BY PROCESS
');}function
convert_field(array$m){}function
unconvert_field(array$m,$I){return$I;}function
support($od){return
preg_match('~^(columns|database|drop_col|fast_status|indexes|descidx|processlist|scheme|sql|status|table|variables|view)$~',$od);}}class
Adminer{static$instance;var$error='';function
name(){return"<a href='https://www.adminer.org/'".target_blank()." id='h1'><img src='".h(preg_replace("~\\?.*~","",ME)."?file=logo.png&version=6.0.0")."' width='24' height='24' alt='' id='logo'>Adminer</a>";}function
credentials(){return
array(SERVER,$_GET["username"],get_password());}function
connectSsl(){}function
permanentLogin($h=false){return
password_file($h);}function
bruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
serverName($N){return
h($N);}function
database(){return
DB;}function
databases($Fd=true){return
get_databases($Fd);}function
pluginsLinks(){}function
operators(){return
driver()->operators;}function
schemas(){$I=schemas();if($_GET["ns"]!=""&&!in_array($_GET["ns"],$I))array_unshift($I,$_GET["ns"]);return$I;}function
queryTimeout(){return
2;}function
afterConnect(){}function
headers(){}function
csp(array$Qb){return$Qb;}function
verifyVersion(){return
true;}function
head($Vb=null){return
true;}function
bodyClass(){echo" adminer";}function
css(){$I=array();foreach(array("","-dark")as$qg){$o="adminer$qg.css";if(file_exists($o)){$ud=file_get_contents($o);$I["$o?v=".crc32($ud)]=($qg?"dark":(preg_match('~prefers-color-scheme:\s*dark~',$ud)?'':'light'));}}return$I;}function
loginForm(){echo"<table class='layout'>\n",adminer()->loginFormField('driver','<tr><th>'.'System'.'<td>',html_select("auth[driver]",SqlDriver::$drivers,DRIVER,on('change','loginDriver'))),adminer()->loginFormField('server','<tr><th>'.'Server'.'<td>',"<input name='auth[server]' value='".h(SERVER)."' title='".'hostname[:port] or :socket'."' placeholder='localhost' autocapitalize='off'>"),adminer()->loginFormField('username','<tr><th>'.'Username'.'<td>','<input name="auth[username]" id="username" autofocus value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'.script("fire(qs('#username').form['auth[driver]'], 'change');")),adminer()->loginFormField('password','<tr><th>'.'Password'.'<td>','<input type="password" name="auth[password]" autocomplete="current-password">'),adminer()->loginFormField('db','<tr><th>'.'Database'.'<td>','<input name="auth[db]" value="'.h($_GET["db"]).'" autocapitalize="off">'),"</table>\n","<p><input type='submit' value='".'Login'."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],'Permanent login')."\n";}function
loginFormField($C,$me,$Y){return$me.$Y."\n";}function
login($Jf,$E){if($E==""||!password_required())return
sprintf('Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',target_blank());return
true;}function
tableName(array$Vj){return
h($Vj["Name"]);}function
fieldName(array$m,$lh=0){$U=$m["full_type"].($m["null"]?" NULL":"");$ub=$m["comment"];return'<span title="'.h($U.($ub!=""?($U?": ":"").$ub:'')).'">'.h($m["field"]).'</span>';}function
commentValue($U,$ub){if($ub==""||$U=='TABLE'||$U=='COLUMN')return
h($ub);$ei=function($Ui){return
preg_replace('~^~m','<tr>',preg_replace('~\|~','<td>',preg_replace('~\|$~m',"",rtrim($Ui))));};$R='(\+--[-+]+\+\n)';$J='(\| .* \|\n)';return"<pre>\n".preg_replace_callback("~^$R?$J$R?($J*)$R?~m",function($B)use($ei){$Ad=$ei($B[2]);return"<table>\n".($B[1]?"<thead>$Ad<tbody>\n":$Ad).$ei($B[4])."\n</table>";},preg_replace('~(\n(    -|mysql)&gt; )(.+)~',"\\1<code class='jush-sql'>\\3</code>",preg_replace('~(.+)\n---+\n~',"<b>\\1</b>\n",h($ub))))."</pre>\n";}function
commentInput($U,$c,$ub){$Y=h($ub);return(preg_match('~\n~',$Y)?"<textarea$c rows='2' cols='".($U=='TABLE'?20:30)."' style='vertical-align: bottom;'>\n$Y</textarea>":"<input$c value='$Y'>");}function
selectLinks(array$Vj,$O=""){$C=$Vj["Name"];echo'<p class="links">';$Ff=array("select"=>'Select data');if(support("table")||support("indexes"))$Ff["table"]='Show structure';$ff=false;if(support("table")){$ff=is_view($Vj);if($ff){if(support("view"))$Ff["view"]='Alter view';}elseif(function_exists('Adminer\alter_table')&&$C!="")$Ff["create"]='Alter table';}if($O!==null)$Ff["edit"]='New item';foreach($Ff
as$x=>$X)echo" <a href='".h(ME)."$x=".url_escape($C).($x=="edit"?$O:"")."'".bold(isset($_GET[$x])).">$X</a>";echo
doc_link(array(JUSH=>driver()->tableHelp($C,$ff)),"?"),"\n";}function
foreignKeys($R){return
foreign_keys($R);}function
backwardKeys($R,$Uj){return
array();}function
backwardKeysPrint(array$Ja,array$J){}function
selectQuery($G,$Jj,$md=false){$I="\n";if(!$md&&($xl=driver()->warnings())){$t="warnings";$I=", <a href='#$t' class='toggle'>".'Warnings'."</a>"."$I<div id='$t' class='hidden'>\n$xl</div>\n";}return"<p><code class='jush-".JUSH."'>".h(str_replace("\n"," ",$G))."</code> <span class='time'>(".format_time($Jj).")</span>".(support("sql")?" <a href='".h(ME)."sql=".url_escape($G)."' class='hover'>".'Edit'."</a>":"").$I;}function
sqlCommandQuery($G){return
shorten_utf8(trim($G),1000);}function
sqlPrintAfter(){}function
rowDescription($R){return"";}function
rowDescriptions(array$K,array$Id){return$K;}function
selectLink($X,array$m){}function
selectVal($X,$_,array$m,$vh){$I=($X===null?"<i>NULL</i>":(preg_match("~char|binary|boolean~",$m["type"])&&!preg_match("~var~",$m["type"])?"<code>$X</code>":(preg_match('~^jsonb?$~',$m["full_type"])?"<code class='jush-json'>$X</code>":$X)));if(is_blob($m)&&!is_utf8($X))$I="<i>".lang_format(array('%d byte','%d bytes'),strlen($vh))."</i>";return($_?"<a href='".h($_)."'".(is_url($_)?target_blank():"").">$I</a>":$I);}function
editVal($X,array$m){return$X;}function
config(){return
array();}function
tableStructurePrint(array$n,$Vj=null){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr><th>".'Column'."<td>".'Type'.(support("comment")?"<td>".'Comment':"")."<tbody>\n";$Mj=driver()->structuredTypes();foreach($n
as$m){echo"<tr><th>".h($m["field"]);$U=h($m["full_type"]);$pb=h($m["collation"]);echo"<td><span title='$pb'>".(in_array($U,(array)$Mj['User types'])?"<a href='".h(ME.'type='.url_escape($U))."'>$U</a>":$U.($pb&&isset($Vj["Collation"])&&$pb!=$Vj["Collation"]?" $pb":""))."</span>",($m["null"]?" <i>NULL</i>":""),($m["auto_increment"]?" <i>".'Auto Increment'."</i>":""),(isset($m["default"])?" <span title='".'Default value'."'>[<b>".($m["generated"]?"<code class='jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim($m["default"])),80,"</code>"):h($m["default"]))."</b>]</span>":""),(support("comment")?"<td>".adminer()->commentValue('COLUMN',$m["comment"]):""),"\n";}echo"</table>\n","</div>\n";}function
tableIndexesPrint(array$w,array$Vj){$Hh=false;foreach($w
as$C=>$v)$Hh|=!!$v["partial"];echo"<table>\n";$ec=first(driver()->indexAlgorithms($Vj));foreach($w
as$C=>$v){ksort($v["columns"]);$ki=array();foreach($v["columns"]as$x=>$X)$ki[]="<i>".h($X)."</i>".($v["lengths"][$x]?"(".h($v["lengths"][$x]).")":"").($v["descs"][$x]?" DESC":"");echo"<tr title='".h($C)."'>","<th>".h($v["type"]).($ec&&$v['algorithm']!=$ec?" (".h($v['algorithm']).")":""),"<td>".implode(", ",$ki);if($Hh)echo"<td>".($v['partial']?"<code class='jush-".JUSH."'>WHERE ".h($v['partial']):"");echo"\n";}echo"</table>\n";}function
selectColumnsPrint(array$M,array$e){print_fieldset("select",'Select',$M);$s=0;$M[""]=array();foreach($M
as$x=>$X){$X=idx($_GET["columns"],$x,array());$d=select_input(" name='columns[$s][col]' data-default=''".on('change',($x!==""?'selectFieldChange':'selectAddRow')),$e,$X["col"]);echo"<div>".(driver()->functions||driver()->grouping?html_select("columns[$s][fun]",array(-1=>"")+array_filter(array('Functions'=>driver()->functions,'Aggregation'=>driver()->grouping)),$X["fun"]," data-default=''".on('change',($x!==""?'helpClose':'selectFunAddRow')).on_help_value(' (.*)|$','($1)'))."($d)":$d)."</div>\n";$s++;}echo"</div></fieldset>\n";}function
selectSearchPrint(array$Z,array$e,array$w){print_fieldset("search",'Search',$Z);foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT")echo"<div>(<i>".implode("</i>, <i>",array_map('Adminer\h',$v["columns"]))."</i>) AGAINST"," <input type='search' name='fulltext[$s]' value='".h(idx($_GET["fulltext"],$s))."' data-default=''".on('input','selectFieldChange').">",(JUSH=='sql'?checkbox("boolean[$s]",1,isset($_GET["boolean"][$s]),"BOOL"):''),"</div>\n";}$gh=adminer()->operators();foreach(array_merge((array)$_GET["where"],array(array()))as$s=>$X){if(!$X||("$X[col]$X[val]"!=""&&in_array($X["op"],$gh)))echo"<div>".select_input(" name='where[$s][col]' data-default=''".on('change',($X?'selectFieldChange':'selectAddRow')),$e,$X["col"],"(".'anywhere'.")"),html_select("where[$s][op]",$gh,$X["op"]," data-default='".h(first($gh))."'".on('change','selectFirstChange')),"<input type='search' name='where[$s][val]' value='".h($X["val"])."' data-default=''".on('input','selectFirstChange').on('keydown','selectSearchKeydown').on('search','selectSearchSearch').">","</div>\n";}echo"</div></fieldset>\n";}function
selectOrderPrint(array$lh,array$e,array$w){print_fieldset("sort",'Sort',$lh);$s=0;foreach((array)$_GET["order"]as$x=>$X){if($X!=""){echo"<div>".select_input(" name='order[$s]' data-default=''".on('change','selectFieldChange'),$e,$X),checkbox("desc[$s]",1,isset($_GET["desc"][$x]),'descending')."</div>\n";$s++;}}echo"<div>".select_input(" name='order[$s]' data-default=''".on('change','selectAddRow'),$e),checkbox("desc[$s]",1,false,'descending')."</div>\n","</div></fieldset>\n";}function
selectLimitPrint($z){echo"<fieldset><legend>".'Limit'."</legend><div>","<input type='number' name='limit' class='size' value='".h($z?:"")."' data-default='50'".on('input','selectFieldChange').">","</div></fieldset>\n";}function
selectLengthPrint($nk){echo"<fieldset><legend>".'Text length'."</legend><div>","<input type='number' name='text_length' class='size' value='".h($nk)."' data-default='100'>","</div></fieldset>\n";}function
selectActionPrint(array$w){echo"<fieldset><legend>".'Action'."</legend><div>","<input type='submit' value='".'Select'."'>"," <span id='noindex' title='".'Full table scan'."'></span>","<script".nonce().">\n","const indexColumns = ";$e=array();foreach($w
as$v){$Ub=reset($v["columns"]);if($v["type"]!="FULLTEXT"&&$Ub)$e[$Ub]=1;}$e[""]=1;foreach($e
as$x=>$X)json_row($x);echo";\n","selectFieldChange.call(qs('#form')['select']);\n","</script>\n","</div></fieldset>\n";}function
selectCommandPrint(){return!information_schema(DB);}function
selectImportPrint(){return!information_schema(DB);}function
selectEmailPrint(array$Mc,array$e){}function
selectColumnsProcess(array$e,array$w){$M=array();$Xd=array();foreach((array)$_GET["columns"]as$x=>$X){if($X["fun"]=="count"||($X["col"]!=""&&(!$X["fun"]||in_array($X["fun"],driver()->functions)||in_array($X["fun"],driver()->grouping)))){$M[$x]=apply_sql_function($X["fun"],($X["col"]!=""?idf_escape($X["col"]):"*"));if(!in_array($X["fun"],driver()->grouping))$Xd[]=$M[$x];}}return
array($M,$Xd);}function
selectSearchProcess(array$n,array$w){$I=array();foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT"&&idx($_GET["fulltext"],$s)!="")$I[]="MATCH (".implode(", ",array_map('Adminer\idf_escape',$v["columns"])).") AGAINST (".q($_GET["fulltext"][$s]).(isset($_GET["boolean"][$s])?" IN BOOLEAN MODE":"").")";}$gh=adminer()->operators();foreach((array)$_GET["where"]as$x=>$X){$X+=array("col"=>"","op"=>first($gh),"val"=>"");$_GET["where"][$x]=$X;$nb=$X["col"];if("$nb$X[val]"!=""&&in_array($X["op"],$gh)){if($X["op"]=="SQL"&&(!$_POST||!verify_token()))SqlDb::$untrusted=true;$zb=array();foreach(($nb!=""?array($nb=>$n[$nb]):$n)as$C=>$m){$fi="";$yb=" $X[op]";if(preg_match('~IN$~',$X["op"])){$Be=process_length($X["val"]);$yb
.=" ".($Be!=""?$Be:"(NULL)");}elseif($X["op"]=="SQL")$yb=" $X[val]";elseif(preg_match('~^(I?LIKE) %%$~',$X["op"],$B))$yb=" $B[1] ".adminer()->processInput($m,"%$X[val]%");elseif($X["op"]=="FIND_IN_SET"){$fi="$X[op](".q($X["val"]).", ";$yb=")";}elseif(!preg_match('~NULL$~',$X["op"]))$yb
.=" ".adminer()->processInput($m,$X["val"]);if($nb!=""||(isset($m["privileges"]["where"])&&(preg_match('~^[-\d.'.(preg_match('~IN$~',$X["op"])?',':'').']+$~',$X["val"])||!preg_match('~'.number_type().'|bit~',$m["type"]))&&(!preg_match("~[\x80-\xFF]~",$X["val"])||preg_match('~char|text|enum|set~',$m["type"]))&&(!preg_match('~date|timestamp~',$m["type"])||preg_match('~^\d+-\d+-\d+~',$X["val"]))))$zb[]=$fi.driver()->convertSearch(idf_escape($C),$X,$m).$yb;}$I[]=(count($zb)==1?$zb[0]:($zb?"(".implode(" OR ",$zb).")":"1 = 0"));}}return$I;}function
selectOrderProcess(array$n,array$w){$I=array();foreach((array)$_GET["order"]as$x=>$X){if($X!="")$I[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$X)?$X:idf_escape($X)).(isset($_GET["desc"][$x])?" DESC".(JUSH=='pgsql'&&idx($n[$X],"null")?" NULLS LAST":""):"");}return$I;}function
selectLimitProcess(){return(isset($_GET["limit"])?intval($_GET["limit"]):50);}function
selectLengthProcess(){return(isset($_GET["text_length"])?"$_GET[text_length]":"100");}function
selectEmailProcess(array$Z,array$Id){return
false;}function
selectQueryBuild(array$M,array$Z,array$Xd,array$lh,$z,$D){return"";}function
messageQuery($G,$ok,$md=false){restart_session();$pe=&get_session("queries");if(!idx($pe,$_GET["db"]))$pe[$_GET["db"]]=array();if(strlen($G)>1e6)$G=preg_replace('~[\x80-\xFF]+$~','',substr($G,0,1e6))."\n…";$pe[$_GET["db"]][]=array($G,time(),$ok);$Ej="sql-".count($pe[$_GET["db"]]);$I="<a href='#$Ej' class='toggle'>".'SQL command'."</a> ".copy_icon()."\n";if(!$md&&($xl=driver()->warnings())){$t="warnings-".count($pe[$_GET["db"]]);$I="<a href='#$t' class='toggle'>".'Warnings'."</a>, $I<div id='$t' class='hidden'>\n$xl</div>\n";}return" <span class='time'>".@date("H:i:s")."</span>"." $I<div id='$Ej' class='hidden'><pre><code class='jush-".JUSH."'>".shorten_utf8($G,1e4)."</code></pre>".($ok?" <span class='time'>($ok)</span>":'').(support("sql")?'<p><a href="'.h(str_replace("db=".url_escape(DB),"db=".url_escape($_GET["db"]),ME).'sql=&history='.(count($pe[$_GET["db"]])-1)).'">'.'Edit'.'</a>':'').'</div>';}function
editRowPrint($R,array$n,$J,$Yk){}function
editFunctions(array$m){$I=($m["null"]?"NULL/":"");$je=isset($_GET["select"])||where($_GET);foreach(array(driver()->insertFunctions,driver()->editFunctions)as$x=>$Sd){if(!$x||(!isset($_GET["call"])&&$je)){foreach($Sd
as$Rh=>$X){if(!$Rh||preg_match("~$Rh~",$m["type"]))$I
.="/$X";}}if($x&&$Sd&&!preg_match('~set|bool~',$m["type"])&&!is_blob($m))$I
.="/SQL";}if($m["auto_increment"]&&!$je)$I='Auto Increment';return
explode("/",$I);}function
editInput($R,array$m,$c,$Y){if($m["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$c value='orig' checked><i>".'original'."</i></label> ":"").enum_input("radio",$c,$m,$Y,"NULL");return"";}function
editHint($R,array$m,$Y){return"";}function
processInput(array$m,$Y,$r=""){if($r=="SQL")return$Y;$C=$m["field"];$I=q($Y);if(preg_match('~^(now|getdate|uuid)$~',$r))$I="$r()";elseif(preg_match('~^current_(date|timestamp)$~',$r))$I=$r;elseif(preg_match('~^([+-]|\|\|)$~',$r))$I=idf_escape($C)." $r $I";elseif(preg_match('~^[+-] interval$~',$r))$I=idf_escape($C)." $r ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$Y)&&JUSH!="pgsql"?$Y:$I);elseif(preg_match('~^(addtime|subtime|concat)$~',$r))$I="$r(".idf_escape($C).", $I)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$r))$I="$r($I)";return
unconvert_field($m,$I);}function
dumpOutput(){$I=array('text'=>'open','file'=>'save');if(function_exists('gzencode'))$I['gz']='gzip';return$I;}function
dumpFormat(){return(support("dump")?array('sql'=>'SQL'):array())+array('csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV');}function
dumpDatabase($j){}function
dumpTable($R,$Nj,$ff=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($Nj)dump_csv(array_keys(fields($R)));}else{if($ff==2){$n=array();foreach(fields($R)as$C=>$m)$n[]=idf_escape($C)." $m[full_type]";$h="CREATE TABLE ".table($R)." (".implode(", ",$n).")";}else$h=create_sql($R,$_POST["auto_increment"],$Nj);set_utf8mb4($h);if($Nj&&$h){if(($Nj=="DROP+CREATE"&&!function_exists('Adminer\drop_sql'))||$ff==1)echo"DROP ".($ff==2?"VIEW":"TABLE")." IF EXISTS ".table($R).";\n";if($ff==1)$h=remove_definer($h);echo"$h;\n\n";}}}function
dumpData($R,$Nj,$G,array$M=array(),array$Z=array(),array$Xd=array(),array$lh=array()){if($Nj){$Uf=(JUSH=="sqlite"?0:1048576);$n=array();$ye=false;if($_POST["format"]=="sql"){if($Nj=="TRUNCATE+INSERT"&&!function_exists('Adminer\truncate_all_sql'))echo
truncate_sql($R).";\n";$n=fields($R);if(JUSH=="mssql"){foreach($n
as$m){if($m["auto_increment"]){echo"SET IDENTITY_INSERT ".table($R)." ON;\n";$ye=true;break;}}}}$H=($G!=""?connection()->query($G,1):driver()->select($R,($M?:array("*")),$Z,$Xd,$lh,0));if($H){$Te="";$Ta="";$mf=array();$Td=array();$Pj="";$pd=($R!=''?'fetch_assoc':'fetch_row');$Mb=0;while($J=$H->$pd()){if(!$mf){$pl=array();foreach($J
as$X){$m=$H->fetch_field();if(idx($n[$m->name],'generated')){$Td[$m->name]=true;continue;}$mf[]=$m->name;$x=idf_escape($m->name);$pl[]="$x = VALUES($x)";}$Pj=($Nj=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$pl):"").";\n";}if($_POST["format"]!="sql"){if($Nj=="table"){dump_csv($mf);$Nj="INSERT";}dump_csv($J);}else{if(!$Te)$Te="INSERT INTO ".table($R)." (".implode(", ",array_map('Adminer\idf_escape',$mf)).") VALUES";foreach($J
as$x=>$X){if($Td[$x]){unset($J[$x]);continue;}$m=$n[$x];$J[$x]=($X===null?"NULL":($X===false?0:unconvert_field($m,preg_match(number_type(),$m["type"])&&!preg_match('~\[~',$m["full_type"])&&is_numeric($X)?$X:(!is_blob($m)||is_utf8($X)?q($X):driver()->quoteBinary($X)))));}$Ui=($Uf?"\n":" ")."(".implode(",\t",$J).")";if(!$Ta)$Ta=$Te.$Ui;elseif(JUSH=='mssql'?$Mb%1000!=0:strlen($Ta)+4+strlen($Ui)+strlen($Pj)<$Uf)$Ta
.=",$Ui";else{echo$Ta.$Pj;$Ta=$Te.$Ui;}}$Mb++;}if($Ta)echo$Ta.$Pj;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",connection()->error)."\n";if($ye)echo"SET IDENTITY_INSERT ".table($R)." OFF;\n";}}function
dumpFilename($we){return
friendly_url($we!=""?$we:(SERVER?:"localhost"));}function
dumpHeaders($we,$tg=false){$zh=$_POST["output"];$hd=(preg_match('~sql~',$_POST["format"])?"sql":($tg?"tar":"csv"));header("Content-Type: ".($zh=="gz"?"application/x-gzip":($hd=="tar"?"application/x-tar":($hd=="sql"||$zh!="file"?"text/plain":"text/csv")."; charset=utf-8")));if($zh=="gz"){ob_start(function($Q){return
gzencode($Q);},1e6);}return$hd;}function
dumpFooter(){if($_POST["format"]=="sql")echo"-- ".gmdate("Y-m-d H:i:s e")."\n";}function
importServerPath(){return"adminer.sql";}function
importPrint(){}function
importProcess(){return
false;}function
homepage(){echo'<p class="links">'.($_GET["ns"]==""&&support("database")?'<a href="'.h(ME).'database=">'.'Alter database'."</a>\n":""),(support("scheme")?"<a href='".h(ME)."scheme='>".($_GET["ns"]!=""?'Alter schema':'Create schema')."</a>\n":""),($_GET["ns"]!==""?'<a href="'.h(ME).'schema=">'.'Database schema'."</a>\n":""),(support("privileges")?"<a href='".h(ME)."privileges='>".'Privileges'."</a>\n":"");if($_GET["ns"]!=="")echo(support("routine")?"<a href='#routines'>".'Routines'."</a>\n":""),(support("sequence")?"<a href='#sequences'>".'Sequences'."</a>\n":""),(support("type")?"<a href='#user-types'>".'User types'."</a>\n":""),(support("event")?"<a href='#events'>".'Events'."</a>\n":"");return
true;}function
navigation($pg){echo"<h1>".adminer()->name()." <span class='version'>".VERSION;$Eg=$_COOKIE["adminer_version"];echo" <a href='https://www.adminer.org/#download'".target_blank()." id='version'>".(version_compare(VERSION,$Eg)<0?h($Eg):"").version_iframe()."</a>","</span></h1>\n";if($pg=="auth"){$zh="";foreach((array)$_SESSION["pwds"]as$rl=>$oj){foreach($oj
as$N=>$kl){$C=h(get_setting("vendor-$rl-$N")?:get_driver($rl));foreach($kl
as$V=>$E){if($C&&$E!==null){$cc=$_SESSION["db"][$rl][$N][$V];foreach(($cc?array_keys($cc):array(""))as$j)$zh
.="<li><a href='".h(auth_url($rl,$N,$V,$j))."'>($C) ".h("$V@").($N!=""?adminer()->serverName($N):"").h($j!=""?" - $j":"")."</a>\n";}}}}if($zh)echo"<ul id='logins'".on('mouseover','menuOver').on('mouseout','menuOut').">\n$zh</ul>\n";}else{$T=array();if($_GET["ns"]!==""&&!$pg&&DB!=""){connection()->select_db(DB);$T=table_status('',true);}adminer()->syntaxHighlighting($T);adminer()->databasesPrint($pg);$ja=array();if(DB==""||!$pg){if(support("sql")){$ja['sql']="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".'SQL command'."</a>";$ja['import']="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".'Import'."</a>";}$ja['dump']="<a href='".h(ME)."dump=".url_escape(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".'Export'."</a>";}$Ce=$_GET["ns"]!==""&&!$pg&&DB!="";if($Ce&&function_exists('Adminer\alter_table'))$ja['create']='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".'Create table'."</a>";$ja=adminer()->menuActions($ja,$pg);echo($ja?"<p class='links'>\n".implode("\n",$ja)."\n":"");if($Ce){if($T)adminer()->tablesPrint($T);else
echo"<p class='message'>".'No tables.'."</p>\n";}}}function
syntaxHighlighting(array$T){echo
script_src(preg_replace("~\\?.*~","",ME)."?file=jush.js&version=6.0.0",true);if(support("sql")){$jf="adminer-plugins/jush-".JUSH.".js";echo(file_exists($jf)?script_src($jf,true):""),"<script".nonce().">\n";if($T){$Ff=array();foreach($T
as$R=>$U)$Ff[]=js_escape_re($R);echo"var jushLinks = { ".JUSH.":";json_row(js_escape(ME).(support("table")?"table":"select").'=$&','/\b(?<!\$)('.implode('|',$Ff).')(?!\$)\b/g',false);$Gj=array("sql","check","event","procedure","trigger","view","type","table","processlist");if(support("routine")&&array_intersect_key($_GET,array_flip($Gj))){foreach(routines()as$J)json_row(js_escape(ME).'function='.url_escape($J["SPECIFIC_NAME"]).'&name=$&','/\b'.js_escape_re($J["ROUTINE_NAME"]).'(?=["`\]]?\()/g',false);}json_row('');echo"};\n";foreach(array("bac","bra","sqlite_quo","mssql_bra")as$X)echo"jushLinks.$X = jushLinks.".JUSH.";\n";if(isset($_GET["sql"])||isset($_GET["trigger"])||isset($_GET["check"])){$dk=array_fill_keys(array_keys($T),array());foreach(driver()->allFields()as$R=>$n){foreach($n
as$m)$dk[$R][]=$m["field"];}echo"addEventListener('DOMContentLoaded', () => { autocompleter = jush.autocompleteSql('".idf_escape("")."', ".json_encode($dk)."); });\n";}}echo"</script>\n";}echo
script("syntaxHighlighting('".(preg_match('~^\d\.?\d~',connection()->server_info,$B)?$B[0]:"")."', '".connection()->flavor."');");}function
databasesPrint($pg){$i=adminer()->databases();if(DB&&$i&&!in_array(DB,$i))array_unshift($i,DB);echo"<form action=''>\n<p id='dbs'>\n";hidden_fields_get();$ac=on('mousedown','dbMouseDown').on('change','dbChange');echo"<label title='".'Database'."'>".'DB'.": ".($i?html_select("db",array(""=>"")+$i,DB,$ac):"<input name='db' value='".h(DB)."' autocapitalize='off' size='19'>\n")."</label>","<input type='submit' value='".'Use'."'".($i?" class='hidden'":"").">\n";if(support("scheme")){if($pg!="db"&&DB!=""&&connection()->select_db(DB)){echo"<br><label>".'Schema'.": ".html_select("ns",array(""=>"")+adminer()->schemas(),$_GET["ns"],$ac)."</label>";if($_GET["ns"]!="")set_schema($_GET["ns"]);}}foreach(array("import","sql","schema","dump","privileges")as$X){if(isset($_GET[$X])){echo
input_hidden($X);break;}}echo"</p></form>\n";}function
menuActions(array$ja,$pg){return$ja;}function
tablesPrint(array$T){echo"<ul id='tables'".on('mouseover','menuOver').on('mouseout','menuOut').">";foreach($T
as$R=>$P){$R="$R";$C=adminer()->tableName($P);if($C!=""&&!$P["partition"])echo'<li><a href="'.h(ME).'select='.url_escape($R).'"'.bold($_GET["select"]==$R||$_GET["edit"]==$R,"select hover")." title='".'Select data'."'>".'select'."</a> ",(support("table")||support("indexes")?'<a href="'.h(ME).'table='.url_escape($R).'"'.bold(in_array($R,array($_GET["table"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"])),(is_view($P)?"view":"structure"))." title='".'Show structure'."'>$C</a>":"<span>$C</span>")."\n";}echo"</ul>\n";}function
showVariables(){return
show_variables();}function
showStatus(){return
show_status();}function
processList(){return
process_list();}function
killProcess($t){return
kill_process($t);}}class
Plugins{private
static$append=array('dumpFormat'=>true,'dumpOutput'=>true,'editRowPrint'=>true,'editFunctions'=>true,'config'=>true);var$plugins;var$drivers=array();var$driverFiles=array();var$error='';private$hooks=array();function
__construct($Xh){$Ac=SqlDriver::$drivers;$ne=" href='https://www.adminer.org/plugins/#use'".target_blank();if($Xh===null){$Xh=array();$Na="adminer-plugins";if(is_dir($Na)){foreach(glob("$Na/*.php")as$o){$vd=SqlDriver::$drivers;$this->includeOnce($o);foreach(array_diff_key(SqlDriver::$drivers,$vd)as$t=>$C)$this->driverFiles[$t]=$o;}}if(file_exists("$Na.php")){$Ee=$this->includeOnce("$Na.php");if(is_array($Ee)){foreach($Ee
as$x=>$Vh)$Xh[is_object($Vh)?get_class($Vh):$x]=$Vh;}else$this->error
.=sprintf('%s must <a%s>return an array</a>.',"<b>$Na.php</b>",$ne)."<br>";}foreach(get_declared_classes()as$kb){if(!$Xh[$kb]&&(preg_match('~^Adminer\w~i',$kb)||is_subclass_of($kb,'Adminer\Plugin'))){$Bi=new
\ReflectionClass($kb);$Eb=$Bi->getConstructor();if($Eb&&$Eb->getNumberOfRequiredParameters())$this->error
.=sprintf('<a%s>Configure</a> %s in %s.',$ne,"<b>$kb</b>","<b>$Na.php</b>")."<br>";else$Xh[$kb]=new$kb;}}}$Xe=array_filter($Xh,function($Vh){return!is_object($Vh);});if($Xe){$this->error
.=sprintf('Every plugin must <a%s>be an object</a>.',$ne)."<br>";$Xh=array_diff_key($Xh,$Xe);}$this->drivers=array_diff_key(SqlDriver::$drivers,$Ac);$this->plugins=$Xh;$na=new
Adminer;$Xh[]=$na;$Bi=new
\ReflectionObject($na);foreach($Bi->getMethods()as$ng){foreach($Xh
as$Vh){$C=$ng->getName();if(method_exists($Vh,$C))$this->hooks[$C][]=$Vh;}}}function
includeOnce($o){return
include_once"./$o";}static
function
checksum($o){$ud=str_replace("\r","",file_get_contents($o));$ud=preg_replace('~\n\tprotected \$translations = array\(.*?\n\t\);~s','',$ud);return
dechex(crc32($ud));}function
checksums(){$wd=array_values($this->driverFiles);foreach($this->plugins
as$Vh){$Bi=new
\ReflectionObject($Vh);$wd[]=$Bi->getFileName();}$I=array();foreach($wd
as$o)$I[basename($o,'.php')]=self::checksum($o);return$I;}static
function
officialChecksums(){return
array('adminer.js'=>'a0599090','backward-keys'=>'afce3b7d','before-unload'=>'48618ca0','config'=>'f49cc617','dark-switcher'=>'3d490dea','database-hide'=>'90c6c0dc','designs'=>'56f1c186','dump-alter'=>'d078b2db','dump-bz2'=>'f0d0e336','dump-date'=>'adc7f1c7','dump-json'=>'767dd321','dump-xml'=>'9f039895','dump-zip'=>'93817d96','edit-foreign'=>'8c874a58','edit-textarea'=>'a24c3cc','editor-setup'=>'a7dc3a37','editor-views'=>'5c12b185','enum-option'=>'a2563959','file-upload'=>'235eaa7a','foreign-system'=>'ebb4c654','frames'=>'b0e1d11a','highlight-codemirror'=>'f1a34275','highlight-monaco'=>'6a92cc58','highlight-prism'=>'4c12cf3','import-csv'=>'1d174088','login-ip'=>'b4766b62','login-otp'=>'62c517c0','login-passkey'=>'f69f2f06','login-password-less'=>'97c37010','login-reverse-proxy'=>'7bb63f11','login-servers'=>'f9ac2f28','login-ssl'=>'6ed147bc','login-table'=>'7b15c3cd','menu-links'=>'f1f86a60','remote-color'=>'33a766c2','row-numbers'=>'eec8698c','select-email'=>'ead22272','select-image'=>'f55c0231','slugify'=>'4d5adde6','sql-gemini'=>'fabc3537','sql-log'=>'b4355039','table-indexes-structure'=>'a90cc0c9','table-structure'=>'a8458e02','tables-filter'=>'f8f51976','timeout'=>'90597366','version-github'=>'497af47b','version-noverify'=>'966937e9','clickhouse'=>'5bb80dfb','elastic'=>'f7017c4','firebird'=>'5499d1a','igdb'=>'170d083','imap'=>'ac143217','mongo'=>'c3b8f5a4','redis'=>'12f1a73b','simpledb'=>'79488f8b',);}function
__call($C,array$Eh){$za=array();foreach($Eh
as$x=>$X)$za[]=&$Eh[$x];$I=null;foreach($this->hooks[$C]as$Vh){$Y=call_user_func_array(array($Vh,$C),$za);if($Y!==null){if(!self::$append[$C])return$Y;$I=$Y+(array)$I;}}return$I;}}abstract
class
Plugin{protected$translations=array();function
description(){return$this->lang('');}function
screenshot(){return"";}protected
function
lang($u,$Kg=null){$za=func_get_args();$za[0]=idx($this->translations[LANG],$u)?:$u;return
call_user_func_array('Adminer\lang_format',$za);}}Adminer::$instance=(function_exists('adminer_object')?adminer_object():(is_dir("adminer-plugins")||file_exists("adminer-plugins.php")?new
Plugins(null):new
Adminer));SqlDriver::$drivers=array("server"=>"MySQL / MariaDB")+SqlDriver::$drivers;if(!defined('Adminer\DRIVER')){define('Adminer\DRIVER',"server");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){class
Db
extends
\MySQLi{static$instance;var$extension="MySQLi",$flavor='';function
__construct(){parent::init();}function
attach($N,$V,$E){mysqli_report(MYSQLI_REPORT_OFF);list($se,$Yh)=host_port($N);$Ij=adminer()->connectSsl();$gl=($Ij&&($Ij['key']||$Ij['cert']||$Ij['ca']||isset($Ij['verify'])));if($gl)$this->ssl_set($Ij['key'],$Ij['cert'],$Ij['ca'],'','');$I=@$this->real_connect(($N!=""?$se:ini_get("mysqli.default_host")),($N.$V!=""?$V:ini_get("mysqli.default_user")),($N.$V.$E!=""?$E:ini_get("mysqli.default_pw")),null,(is_numeric($Yh)?intval($Yh):ini_get("mysqli.default_port")),(is_numeric($Yh)?null:$Yh),($gl?($Ij['verify']!==false?MYSQLI_CLIENT_SSL:64):0));$this->options(MYSQLI_OPT_LOCAL_INFILE,0);return($I?'':$this->error);}function
set_charset($bb){if(parent::set_charset($bb))return
true;parent::set_charset('utf8');return$this->query("SET NAMES $bb");}function
next_result(){return
self::more_results()&&parent::next_result();}function
quote($Q){return"'".$this->escape_string($Q)."'";}function
inTransaction(){return
false;}}}elseif(extension_loaded("mysql")&&!((ini_bool("sql.safe_mode")||ini_bool("mysql.allow_local_infile"))&&extension_loaded("pdo_mysql"))){class
Db
extends
SqlDb{private$link;function
attach($N,$V,$E){if(ini_bool("mysql.allow_local_infile"))return
sprintf('Disable %s or enable %s or %s extensions.',"'mysql.allow_local_infile'","MySQLi","PDO_MySQL");$this->link=@mysql_connect(($N!=""?$N:ini_get("mysql.default_host")),($N.$V!=""?$V:ini_get("mysql.default_user")),($N.$V.$E!=""?$E:ini_get("mysql.default_password")),true,131072);if(!$this->link)return
mysql_error();$this->server_info=mysql_get_server_info($this->link);return'';}function
set_charset($bb){return
mysql_set_charset($bb,$this->link)||mysql_set_charset('utf8',$this->link);}function
quote($Q){return"'".mysql_real_escape_string($Q,$this->link)."'";}function
select_db($Zb){return
mysql_select_db($Zb,$this->link);}function
query($G,$Pk=false){$H=@($Pk?mysql_unbuffered_query($G,$this->link):mysql_query($G,$this->link));$this->error="";if(!$H){$this->errno=mysql_errno($this->link);$this->error=mysql_error($this->link);return
false;}if($H===true){$this->affected_rows=mysql_affected_rows($this->link);$this->info=mysql_info($this->link);return
true;}return
new
Result($H);}}class
Result{var$num_rows;private$result;private$offset=0;function
__construct($H){$this->result=$H;$this->num_rows=mysql_num_rows($H);}function
fetch_assoc(){return
mysql_fetch_assoc($this->result);}function
fetch_row(){return
mysql_fetch_row($this->result);}function
fetch_field(){$I=mysql_fetch_field($this->result,$this->offset++);$I->orgtable=$I->table;$I->charsetnr=($I->blob?63:0);return$I;}}}elseif(extension_loaded("pdo_mysql")){class
Db
extends
PdoDb{var$extension="PDO_MySQL";function
attach($N,$V,$E){$jh=array(\PDO::MYSQL_ATTR_LOCAL_INFILE=>false);if(isset($_GET["select"]))$jh[\PDO::MYSQL_ATTR_MULTI_STATEMENTS]=false;$Ij=adminer()->connectSsl();if($Ij){if($Ij['key'])$jh[\PDO::MYSQL_ATTR_SSL_KEY]=$Ij['key'];if($Ij['cert'])$jh[\PDO::MYSQL_ATTR_SSL_CERT]=$Ij['cert'];if($Ij['ca'])$jh[\PDO::MYSQL_ATTR_SSL_CA]=$Ij['ca'];if(isset($Ij['verify']))$jh[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=$Ij['verify'];}list($se,$Yh)=host_port($N);return$this->dsn("mysql:charset=utf8".($se!=""?";host=$se":'').($Yh?(is_numeric($Yh)?";port=":";unix_socket=").$Yh:""),$V,$E,$jh);}function
set_charset($bb){return$this->query("SET NAMES $bb");}function
select_db($Zb){return$this->query("USE ".idf_escape($Zb));}function
query($G,$Pk=false){$this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$Pk);return
parent::query($G,$Pk);}}}class
Driver
extends
SqlDriver{static$extensions=array("MySQLi","MySQL","PDO_MySQL");static$jush="sql";var$unsigned=array("unsigned","zerofill","unsigned zerofill");var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","REGEXP","IN","FIND_IN_SET","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL");var$functions=array("char_length","date","from_unixtime","lower","round","floor","ceil","sec_to_time","time_to_sec","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");var$partitionBy=array("HASH","LINEAR HASH","KEY","LINEAR KEY","RANGE","LIST");static
function
connect($N,$V,$E){$f=parent::connect($N,$V,$E);if(is_string($f)){if(function_exists('iconv')&&!is_utf8($f)&&strlen($Ui=iconv("windows-1252","utf-8//IGNORE",$f))>strlen($f))$f=$Ui;return$f;}$f->set_charset(charset($f));$f->query("SET sql_quote_show_create = 1, autocommit = 1");$f->flavor=(preg_match('~MariaDB~',$f->server_info)?'maria':'mysql');add_driver(DRIVER,($f->flavor=='maria'?"MariaDB":"MySQL"));return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21),'Date and time'=>array("date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4),'Strings'=>array("char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295),'Lists'=>array("enum"=>65535,"set"=>64),'Binary'=>array("bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295),'Geometry'=>array("geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0),);$this->insertFunctions=array("char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",);if(min_version('5.7.8',10.2,$f))$this->types['Strings']["json"]=4294967295;if(min_version('',10.7,$f)){$this->types['Strings']["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if(min_version('',10.5,$f)){$this->types['Network']["inet6"]=39;if(min_version('','10.10',$f))$this->types['Network']["inet4"]=15;}if(min_version(9,11.7,$f))$this->types['Numbers']["vector"]=16383;if(min_version(5.7,10.2,$f))$this->generated=array("STORED","VIRTUAL");}function
unconvertFunction(array$m){return(preg_match("~binary~",$m["type"])?"<code class='jush-sql'>UNHEX</code>":($m["type"]=="bit"?doc_link(array('sql'=>'bit-value-literals.html'),"<code>b''</code>"):($m["type"]=="vector"?"<code class='jush-sql'>".($this->conn->flavor=='maria'?"VEC_FromText":"STRING_TO_VECTOR")."</code>":(preg_match("~geometry|point|linestring|polygon~",$m["type"])?"<code class='jush-sql'>GeomFromText</code>":""))));}function
insert($R,array$O){return($O?parent::insert($R,$O):queries("INSERT INTO ".table($R)." ()\nVALUES ()"));}function
insertUpdate($R,array$K,array$ii){$e=array_keys(reset($K));$fi="INSERT INTO ".table($R)." (".implode(", ",$e).") VALUES\n";$pl=array();foreach($e
as$x)$pl[$x]="$x = VALUES($x)";$Pj="\nON DUPLICATE KEY UPDATE ".implode(", ",$pl);$pl=array();$y=0;foreach($K
as$O){$Y="(".implode(", ",$O).")";if($pl&&(strlen($fi)+$y+strlen($Y)+strlen($Pj)>1e6)){if(!queries($fi.implode(",\n",$pl).$Pj))return
false;$pl=array();$y=0;}$pl[]=$Y;$y+=strlen($Y)+2;}return
queries($fi.implode(",\n",$pl).$Pj);}function
slowQuery($G,$pk){if(min_version('5.7.8','10.1.2')){if($this->conn->flavor=='maria')return"SET STATEMENT max_statement_time=$pk FOR $G";elseif(preg_match('~^(SELECT\b)(.+)~is',$G,$B))return"$B[1] /*+ MAX_EXECUTION_TIME(".($pk*1000).") */ $B[2]";}}function
convertSearch($u,array$X,array$m){return(preg_match('~char|text|enum|set~',$m["type"])&&!preg_match("~^utf8~",$m["collation"])&&preg_match('~[\x80-\xFF]~',$X['val'])?"CONVERT($u USING ".charset($this->conn).")":$u);}function
typeName(\stdClass$m){$Ok=array("decimal","tinyint","smallint","int","float","double",7=>"timestamp","bigint","mediumint","date","time","datetime","year",15=>"varchar","bit",242=>"vector",245=>"json","decimal","enum","set","tinytext","mediumtext","longtext","text","varchar","char","geometry",);$I=idx($Ok,$m->type,"");return
parent::typeName($m)?:($m->charsetnr==63?str_replace(array("text","varchar","char"),array("blob","varbinary","binary"),$I):$I);}function
quoteBinary($Ui){return"X".q(bin2hex($Ui));}function
warnings(){$H=$this->conn->query("SHOW WARNINGS");if($H&&$H->num_rows){ob_start();print_select_result($H);return
ob_get_clean();}}function
tableHelp($C,$ff=false){$Lf=($this->conn->flavor=='maria');if(information_schema(DB))return
strtolower(str_replace("_","-",DB)."-".($Lf?"$C-table/":str_replace("_","-",$C)."-table.html"));if(DB=="sys")return($Lf?"sys-schema/":strtolower("sys-".str_replace("_","-",preg_replace('~^x\$~','',$C)).".html"));if(DB=="mysql")return($Lf?"mysql$C-table/":"system-schema.html");}function
partitionsInfo($R){$Od="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($R);$H=$this->conn->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $Od ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1");$J=($H?$H->fetch_row():null);if(!$J)return
array();$I=array();list($I["partition_by"],$I["partition"],$I["partitions"])=$J;$Nh=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $Od AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$I["partition_names"]=array_keys($Nh);$I["partition_values"]=array_values($Nh);return$I;}function
hasCStyleEscapes(){static$Wa;if($Wa===null){$Fj=get_val("SHOW VARIABLES LIKE 'sql_mode'",1,$this->conn);$Wa=(strpos($Fj,'NO_BACKSLASH_ESCAPES')===false);}return$Wa;}function
lineComment(){return"#|-- ";}function
engines(){$I=array();foreach(get_rows("SHOW ENGINES")as$J){if(preg_match("~YES|DEFAULT~",$J["Support"]))$I[]=$J["Engine"];}return$I;}function
indexAlgorithms(array$Vj){return(preg_match('~^(MEMORY|NDB)$~',$Vj["Engine"])?array("HASH","BTREE"):array());}}function
idf_escape($u){return"`".str_replace("`","``",$u)."`";}function
table($u){return
idf_escape($u);}function
get_databases($Fd){$I=get_session("dbs");if($I===null){$G="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$Jj=microtime(true);$I=($Fd?slow_query($G):get_vals($G));if(microtime(true)-$Jj>0.1){restart_session();set_session("dbs",$I);stop_session();}}return$I;}function
limit($G,$Z,$z,$Rg=0,$gj=" "){return" $G$Z".($z?$gj."LIMIT $z".($Rg?" OFFSET $Rg":""):"");}function
limit1($R,$G,$Z,$gj="\n"){return
limit($G,$Z,1,0,$gj);}function
db_collation($j,array$qb){$I=null;$h=get_val("SHOW CREATE DATABASE ".idf_escape($j),1);if(preg_match('~ COLLATE ([^ ]+)~',$h,$B))$I=$B[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$h,$B))$I=$qb[$B[1]][-1];return$I;}function
logged_user(){return
get_val("SELECT USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables(array$i){$I=array();foreach($i
as$j)$I[$j]=count(get_vals("SHOW TABLES IN ".idf_escape($j)));return$I;}function
table_status($C="",$nd=false){$I=array();foreach(get_rows($nd?"SELECT TABLE_NAME AS Name, ENGINE AS Engine, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($C!=""?"AND TABLE_NAME = ".q($C):"ORDER BY Name"):"SHOW TABLE STATUS".($C!=""?" LIKE ".q(addcslashes($C,"%_\\")):""))as$J){if($J["Engine"]=="InnoDB")$J["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$J["Comment"]);if(!isset($J["Engine"]))$J["Comment"]="";if($C!="")$J["Name"]=$C;$I[$J["Name"]]=$J;}return$I;}function
is_view(array$S){return$S["Engine"]===null;}function
fk_support(array$S){return
preg_match('~InnoDB|IBMDB2I'.(min_version(5.6)?'|NDB':'').'~i',$S["Engine"]);}function
parse_type($Qd){preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$Qd,$B);return
array($B[1],$B[2],ltrim($B[3].$B[4]));}function
fields($R){$Lf=(connection()->flavor=='maria');$I=array();foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($R)." ORDER BY ORDINAL_POSITION")as$J){$m=$J["COLUMN_NAME"];$U=$J["COLUMN_TYPE"];$Ud=$J["GENERATION_EXPRESSION"];$kd=$J["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$kd,$Td);list($Nk,$y,$Wk)=parse_type($U);$k=$J["COLUMN_DEFAULT"];if($k!=""){$ef=preg_match('~text|json~',$Nk);if(!$Lf&&$ef)$k=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($k));if($Lf||$ef){$k=($k=="NULL"?null:preg_replace_callback("~^'(.*)'$~",function($B){return
stripslashes(str_replace("''","'",$B[1]));},$k));}if(!$Lf&&preg_match('~binary~',$Nk)&&preg_match('~^0x(\w*)$~',$k,$B))$k=pack("H*",$B[1]);}$I[$m]=array("field"=>$m,"full_type"=>$U,"type"=>$Nk,"length"=>$y,"unsigned"=>$Wk,"default"=>($Td?($Lf?$Ud:stripslashes($Ud)):$k),"null"=>($J["IS_NULLABLE"]=="YES"),"auto_increment"=>($kd=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$kd,$B)?$B[1]:""),"collation"=>$J["COLLATION_NAME"],"privileges"=>array_flip(explode(",","$J[PRIVILEGES],where,order")),"comment"=>$J["COLUMN_COMMENT"],"primary"=>($J["COLUMN_KEY"]=="PRI"),"generated"=>($Td[1]=="PERSISTENT"?"STORED":$Td[1]),);}return$I;}function
indexes($R,$g=null){$I=array();foreach(get_rows("SHOW INDEX FROM ".table($R),$g)as$J){$C=$J["Key_name"];$I[$C]["type"]=($C=="PRIMARY"?"PRIMARY":($J["Index_type"]=="FULLTEXT"?"FULLTEXT":($J["Non_unique"]?(preg_match('~^(SPATIAL|VECTOR)$~',$J["Index_type"])?$J["Index_type"]:"INDEX"):"UNIQUE")));$I[$C]["columns"][]=$J["Column_name"];$I[$C]["lengths"][]=($J["Index_type"]=="SPATIAL"?null:$J["Sub_part"]);$I[$C]["descs"][]=null;$I[$C]["algorithm"]=$J["Index_type"];}return$I;}function
foreign_keys($R){static$Rh='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$I=array();$Nb=get_val("SHOW CREATE TABLE ".table($R),1);if($Nb){preg_match_all("~CONSTRAINT ($Rh) FOREIGN KEY ?\\(((?:$Rh,? ?)+)\\) REFERENCES ($Rh)(?:\\.($Rh))? \\(((?:$Rh,? ?)+)\\)(?: ON DELETE (".driver()->onActions."))?(?: ON UPDATE (".driver()->onActions."))?~",$Nb,$Of,PREG_SET_ORDER);foreach($Of
as$B){preg_match_all("~$Rh~",$B[2],$_j);preg_match_all("~$Rh~",$B[5],$hk);$I[idf_unescape($B[1])]=array("db"=>idf_unescape($B[4]!=""?$B[3]:$B[4]),"table"=>idf_unescape($B[4]!=""?$B[4]:$B[3]),"source"=>array_map('Adminer\idf_unescape',$_j[0]),"target"=>array_map('Adminer\idf_unescape',$hk[0]),"on_delete"=>($B[6]?:"RESTRICT"),"on_update"=>($B[7]?:"RESTRICT"),);}}return$I;}function
view($C){return
array("select"=>preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU','',get_val("SHOW CREATE VIEW ".table($C),1)));}function
collations(){$I=array();foreach(get_rows("SHOW COLLATION")as$J){if($J["Default"])$I[$J["Charset"]][-1]=$J["Collation"];else$I[$J["Charset"]][]=$J["Collation"];}ksort($I);foreach($I
as$x=>$X)sort($I[$x]);return$I;}function
information_schema($j,$L=""){return($j=="information_schema")||(min_version(5.5)&&$j=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",connection()->error));}function
create_database($j,$pb){return
queries("CREATE DATABASE ".idf_escape($j).($pb?" COLLATE ".q($pb):""));}function
drop_databases(array$i){$I=apply_queries("DROP DATABASE",$i,'Adminer\idf_escape');restart_session();set_session("dbs",null);return$I;}function
rename_database($C,$pb){$I=false;if(create_database($C,$pb)){$T=array();$ul=array();foreach(tables_list()as$R=>$U){if($U=='VIEW')$ul[]=$R;else$T[]=$R;}$I=(!$T&&!$ul)||move_tables($T,$ul,$C);drop_databases($I?array(DB):array());}return$I;}function
auto_increment(){$Fa=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$v){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$v["columns"],true)){$Fa="";break;}if($v["type"]=="PRIMARY")$Fa=" UNIQUE";}}return" AUTO_INCREMENT$Fa";}function
alter_table($R,$C,array$n,array$Hd,$ub,$Pc,$pb,$Ea,$Mh){$b=array();foreach($n
as$m){if($m[1]){$k=$m[1][3];if(preg_match('~ GENERATED~',$k)){$m[1][3]=(connection()->flavor=='maria'?"":$m[1][2]);$m[1][2]=$k;}$b[]=($R!=""?($m[0]!=""?"CHANGE ".idf_escape($m[0]):"ADD"):" ")." ".implode($m[1]).($R!=""?$m[2]:"");}else$b[]="DROP ".idf_escape($m[0]);}$b=array_merge($b,$Hd);$P=($ub!==null?" COMMENT=".q($ub):"").($Pc?" ENGINE=".q($Pc):"").($pb?" COLLATE ".q($pb):"").($Ea!=""?" AUTO_INCREMENT=$Ea":"");if($Mh){$Nh=array();if($Mh["partition_by"]=='RANGE'||$Mh["partition_by"]=='LIST'){foreach($Mh["partition_names"]as$x=>$X){$Y=$Mh["partition_values"][$x];$Nh[]="\n  PARTITION ".idf_escape($X)." VALUES ".($Mh["partition_by"]=='RANGE'?"LESS THAN":"IN").($Y!=""?" ($Y)":" MAXVALUE");}}$P
.="\nPARTITION BY $Mh[partition_by]($Mh[partition])";if($Nh)$P
.=" (".implode(",",$Nh)."\n)";elseif($Mh["partitions"])$P
.=" PARTITIONS ".(+$Mh["partitions"]);}elseif($Mh===null)$P
.="\nREMOVE PARTITIONING";if($R=="")return
queries("CREATE TABLE ".table($C)." (\n".implode(",\n",$b)."\n)$P");if($R!=$C)$b[]="RENAME TO ".table($C);if($P)$b[]=ltrim($P);return($b?queries("ALTER TABLE ".table($R)."\n".implode(",\n",$b)):true);}function
alter_indexes($R,$b){$Za=array();foreach($b
as$X)$Za[]=($X[2]=="DROP"?"\nDROP INDEX ".idf_escape($X[1]):"\nADD $X[0] ".($X[0]=="PRIMARY"?"KEY ":"").($X[1]!=""?idf_escape($X[1])." ":"")."(".implode(", ",$X[2]).")");return
queries("ALTER TABLE ".table($R).implode(",",$Za));}function
truncate_tables(array$T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views(array$ul){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$ul)));}function
drop_tables(array$T){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$T)));}function
move_tables(array$T,array$ul,$hk){$Gi=array();foreach($T
as$R)$Gi[]=table($R)." TO ".idf_escape($hk).".".table($R);if(!$Gi||queries("RENAME TABLE ".implode(", ",$Gi))){$ic=array();foreach($ul
as$R)$ic[table($R)]=view($R);connection()->select_db($hk);$j=idf_escape(DB);foreach($ic
as$C=>$tl){if(!queries("CREATE VIEW $C AS ".str_replace(" $j."," ",$tl["select"]))||!queries("DROP VIEW $j.$C"))return
false;}return
true;}return
false;}function
copy_tables(array$T,array$ul,$hk){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($T
as$R){$C=($hk==DB?table("copy_$R"):idf_escape($hk).".".table($R));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $C"))||!queries("CREATE TABLE $C LIKE ".table($R))||!queries("INSERT INTO $C SELECT * FROM ".table($R)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$J){$Fk=$J["Trigger"];if(!queries("CREATE TRIGGER ".($hk==DB?idf_escape("copy_$Fk"):idf_escape($hk).".".idf_escape($Fk))." $J[Timing] $J[Event] ON $C FOR EACH ROW\n$J[Statement];"))return
false;}}foreach($ul
as$R){$C=($hk==DB?table("copy_$R"):idf_escape($hk).".".table($R));$tl=view($R);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $C"))||!queries("CREATE VIEW $C AS $tl[select]"))return
false;}return
true;}function
trigger($C,$R){if($C=="")return
array();$K=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($C));return
reset($K);}function
triggers($R){$I=array();foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$J)$I[$J["Trigger"]]=array($J["Timing"],$J["Event"]);return$I;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
routine($C,$U){$K=get_rows("SELECT PARAMETER_NAME, DTD_IDENTIFIER, PARAMETER_MODE, CHARACTER_SET_NAME
FROM information_schema.PARAMETERS
WHERE SPECIFIC_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$U' AND SPECIFIC_NAME = ".q($C)."
ORDER BY ORDINAL_POSITION");$n=array();foreach($K
as$J){$Qd=$J["DTD_IDENTIFIER"];list($Nk,$y,$Wk)=parse_type($Qd);$n[]=array("field"=>$J["PARAMETER_NAME"],"type"=>$Nk,"length"=>$y,"unsigned"=>$Wk,"null"=>true,"full_type"=>$Qd,"inout"=>($U=="FUNCTION"?"":$J["PARAMETER_MODE"]),"collation"=>$J["CHARACTER_SET_NAME"],);}$I=connection()->query("SELECT
	ROUTINE_COMMENT comment,
	CONCAT(IF(IS_DETERMINISTIC = 'YES', 'DETERMINISTIC\\n', ''), IF(SQL_DATA_ACCESS != 'CONTAINS SQL', CONCAT(SQL_DATA_ACCESS, '\\n'), ''), ROUTINE_DEFINITION) definition,
	'SQL' language
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = '$U' AND ROUTINE_NAME = ".q($C))->fetch_assoc();if($n&&$n[0]['field']=='')$I['returns']=array_shift($n);$I['fields']=$n;return$I;}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return
array();}function
routine_id($C,array$J){return
idf_escape($C);}function
last_id($H){return
get_val("SELECT LAST_INSERT_ID()");}function
explain(Db$f,$G){return$f->query("EXPLAIN ".(min_version(5.7)?"":"PARTITIONS ").$G);}function
found_rows(array$S,array$Z){return($Z||$S["Engine"]!="InnoDB"?null:$S["Rows"]);}function
create_sql($R,$Ea,$Nj){$I=get_val("SHOW CREATE TABLE ".table($R),1);if(!$Ea)$I=preg_replace('~(\n\)[^\n]*?) AUTO_INCREMENT=\d+~','\1',$I);return$I;}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
use_sql($Zb,$Nj=""){$C=idf_escape($Zb);$I="";if(preg_match('~CREATE~',$Nj)&&($h=get_val("SHOW CREATE DATABASE $C",1))){set_utf8mb4($h);if($Nj=="DROP+CREATE")$I="DROP DATABASE IF EXISTS $C;\n";$I
.="$h;\n";}return$I."USE $C";}function
trigger_sql($R){$I="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")),null,"-- ")as$J)$I
.="\nCREATE TRIGGER ".idf_escape($J["Trigger"])." $J[Timing] $J[Event] ON ".table($J["Table"])." FOR EACH ROW\n$J[Statement];;\n";return$I;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$m){if(preg_match("~binary~",$m["type"]))return"HEX(".idf_escape($m["field"]).")";if($m["type"]=="bit")return"BIN(".idf_escape($m["field"])." + 0)";if($m["type"]=="vector")return(connection()->flavor=='maria'?"VEC_ToText":"VECTOR_TO_STRING")."(".idf_escape($m["field"]).")";if(preg_match("~geometry|point|linestring|polygon~",$m["type"]))return(min_version(8)?"ST_":"")."AsWKT(".idf_escape($m["field"]).")";}function
unconvert_field(array$m,$I){if(preg_match("~binary~",$m["type"]))$I="UNHEX($I)";if($m["type"]=="bit")$I="CONVERT(b$I, UNSIGNED)";if($m["type"]=="vector")$I=(connection()->flavor=='maria'?"VEC_FromText":"STRING_TO_VECTOR")."($I)";if(preg_match("~geometry|point|linestring|polygon~",$m["type"])){$fi=(min_version(8)?"ST_":"");$I=$fi."GeomFromText($I, $fi"."SRID($m[field]))";}return$I;}function
support($od){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|event|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(min_version(8)?'|descidx':'').(min_version('8.0.16','10.2.1')?'|check':'').(min_version(8,99)?'|fast_status':'').')$~',$od);}function
kill_process($t){return
queries("KILL ".number($t));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return
get_val("SELECT @@max_connections");}function
types($jd=false){return
array();}function
type_values($t){return"";}function
type_definition($t){return
array("kind"=>"","definition"=>"");}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($L,$g=null){return
true;}}define('Adminer\JUSH',Driver::$jush);define('Adminer\SERVER',"".$_GET[DRIVER]);define('Adminer\DB',"$_GET[db]");define('Adminer\ME',preg_replace('~\?.*~','',relative_uri()).'?'.(sid()?SID.'&':'').($_GET["ext"]?"ext=".url_escape($_GET["ext"]).'&':'').(isset($_GET[DRIVER])?DRIVER."=".url_escape(SERVER).'&':'').(isset($_GET["username"])?"username=".url_escape($_GET["username"]).'&':'').(DB!=""?'db='.url_escape(DB).'&'.(isset($_GET["ns"])?"ns=".url_escape($_GET["ns"])."&":""):''));function
page_header($rk,$l="",$Sa=array(),$sk=""){page_headers();if(is_ajax()&&$l){page_messages($l);exit;}if(!ob_get_level())ob_start('ob_gzhandler',4096);$tk=$rk.($sk!=""?": $sk":"");$uk=strip_tags($tk.(SERVER!=""&&SERVER!="localhost"?h(" - ".SERVER):"")." - ".adminer()->name());echo'<!DOCTYPE html>
<html lang=\'en\' dir=\'ltr\' class=\'ltr nojs\'>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>',$uk,'</title>
<link rel="stylesheet" href="',h(preg_replace("~\\?.*~","",ME)."?file=default.css&version=6.0.0"),'">
';$Rb=adminer()->css();if(is_int(key($Rb)))$Rb=array_fill_keys($Rb,'light');$ge=in_array('light',$Rb)||in_array('',$Rb);$ee=in_array('dark',$Rb)||in_array('',$Rb);$Vb=($ge?($ee?null:false):($ee?:null));$cg=" media='(prefers-color-scheme: dark)'";if($Vb!==false)echo"<link rel='stylesheet'".($Vb?"":$cg)." href='".h(preg_replace("~\\?.*~","",ME)."?file=dark.css&version=6.0.0")."'>\n";echo"<meta name='color-scheme' content='".($Vb===null?"light dark":($Vb?"dark":"light"))."'>\n",script_src(preg_replace("~\\?.*~","",ME)."?file=functions.js&version=6.0.0");if(adminer()->head($Vb))echo"<link rel='icon' href='data:image/gif;base64,"."R0lGODlhEAAQAJEAAAQCBPz+/PwCBAROZCH5BAEAAAAALAAAAAAQABAAAAI2hI+pGO1rmghihiUdvUBnZ3XBQA7f05mOak1RWXrNq5nQWHMKvuoJ37BhVEEfYxQzHjWQ5qIAADs='>\n","<link rel='apple-touch-icon' href='".h(preg_replace("~\\?.*~","",ME)."?file=logo.png&version=6.0.0")."'>\n";foreach($Rb
as$bl=>$qg){$c=($qg=='dark'&&!$Vb?$cg:($qg=='light'&&$ee?" media='(prefers-color-scheme: light)'":""));echo"<link rel='stylesheet'$c href='".h($bl)."'>\n";}echo"\n<body class='";adminer()->bodyClass();echo"'>\n",script((isset($_COOKIE["adminer_version"])||!adminer()->verifyVersion()?"":"onload = partial(verifyVersion, '".VERSION."');\n")."
const offlineMessage = '".js_escape('You are offline.')."';
const thousandsSeparator = '".js_escape(',')."';
const urlSeparators = '".js_escape(ini_get("arg_separator.input"))."';"),"<div id='help' class='jush-".JUSH." jsonly hidden'".on('mouseover','helpKeep').on('mouseout','helpMouseout')."></div>\n","<div id='content'>\n","<span id='menuopen' class='jsonly'".on('click','menuToggle')."><button title='".'Menu'."' class='icon icon-move' aria-expanded='false'></button></span>\n";if($Sa!==null){$_=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb"><a href="'.h($_?:".").'">'.get_driver(DRIVER).'</a> » ';$_=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);$N=adminer()->serverName(SERVER);$N=($N!=""?$N:'Server');if($Sa===false)echo"$N\n";else{echo"<a href='".h($_)."' accesskey='1' title='Alt+Shift+1'>$N</a> » ";if($_GET["ns"]!=""||(DB!=""&&is_array($Sa)))echo'<a href="'.h($_."&db=".url_escape(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a> » ';if(is_array($Sa)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a> » ';foreach($Sa
as$x=>$X){$kc=(is_array($X)?$X[1]:h($X));if($kc!="")echo"<a href='".h(ME."$x=").url_escape(is_array($X)?$X[0]:$X)."'>$kc</a> » ";}}echo"$rk\n";}}echo"<h2>$tk</h2>\n","<div id='ajaxstatus' role='status' class='jsonly'></div>\n";restart_session();page_messages($l);$i=&get_session("dbs");if(DB!=""&&$i&&!in_array(DB,$i,true))$i=null;stop_session();define('Adminer\PAGE_HEADER',1);ob_flush();flush();}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-Frame-Options: deny");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");foreach(adminer()->csp(csp())as$Qb){$ke=array();foreach($Qb
as$x=>$X)$ke[]="$x $X";header("Content-Security-Policy: ".implode("; ",$ke));}adminer()->headers();}function
csp(){return
array(array("script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self' https://www.adminer.org","frame-src"=>"https://www.adminer.org","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",),);}function
design_checksums(){$hl=array();foreach(array_keys(adminer()->css())as$bl)$hl[preg_replace('~\?.*~','',$bl)]=true;$I=array();foreach(array("adminer.css","adminer-dark.css")as$o){if($hl[$o]&&file_exists($o)){preg_match('~^/\* Adminer design ([-\w]+) \*/~',file_get_contents($o),$B);$I[$o]=array((string)$B[1],Plugins::checksum($o));}}return$I;}function
official_design_checksums(){return
array('adminer-border/adminer-dark.css'=>'b2527e3','adminer-border/adminer.css'=>'430977ad','adminer-dark/adminer-dark.css'=>'a26bcd7b','brade/adminer.css'=>'be4161f0','bueltge/adminer.css'=>'1a8f00b4','dracula/adminer-dark.css'=>'cfaf61dd','esterka/adminer.css'=>'1f805f36','flat/adminer.css'=>'49a61af9','galkaev/adminer-dark.css'=>'16c46f94','haeckel/adminer.css'=>'147a3565','hever/adminer.css'=>'78b8cd43','konya/adminer.css'=>'3cc606c5','lavender-light/adminer.css'=>'bf03f5d7','lucas-sandery/adminer.css'=>'6596353','mancave/adminer-dark.css'=>'e1ac813d','mvt/adminer.css'=>'ebd3afdc','nette/adminer.css'=>'5ab360e7','ng9/adminer.css'=>'488583cf','nicu/adminer.css'=>'ecb9bd1e','pappu687/adminer.css'=>'b58d128c','paranoiq/adminer.css'=>'64d27e5','pepa-linha/adminer.css'=>'baf25f0','pokorny/adminer.css'=>'ee9eea6d','price/adminer.css'=>'b3c939b2','rmsoft/adminer.css'=>'391d54ad','rmsoft_blue-dark/adminer.css'=>'17714d77','rmsoft_blue/adminer.css'=>'c0f192ea','win98/adminer.css'=>'e82d63c3',);}function
version_iframe(){return(isset($_COOKIE["adminer_version"])||!adminer()->verifyVersion()?"":"<noscript><iframe sandbox src='https://www.adminer.org/version/?current=".VERSION."&amp;noscript=1'></iframe></noscript>");}function
get_nonce(){static$Gg;if(!$Gg)$Gg=base64_encode(rand_string());return$Gg;}function
page_messages($l){$al=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$jg=idx($_SESSION["messages"],$al);if($jg){echo"<div class='message'>".implode("</div>\n<div class='message'>",$jg)."</div>".script("messagesPrint();");unset($_SESSION["messages"][$al]);}if($l)echo"<div class='error'>$l</div>\n";if(adminer()->error)echo"<div class='error'>".adminer()->error."</div>\n";}function
page_footer($pg=""){echo"</div>\n\n<div id='foot' class='foot'>\n<div id='menu'>\n";adminer()->navigation($pg);echo"</div>\n";if($pg!="auth")echo'<form action="" method="post">
<p class="logout">
<span title="Username">',h($_GET["username"])."\n",'</span>
<input type=\'submit\' name=\'logout\' value=\'Logout\' id=\'logout\'>
',input_token(),'</form>
';echo"</div>\n\n",script("setupSubmitHighlight(document);");}function
int32($vg){while($vg>=2147483648)$vg-=4294967296;while($vg<=-2147483649)$vg+=4294967296;return(int)$vg;}function
long2str(array$W,$wl){$Ui='';foreach($W
as$X)$Ui
.=pack('V',$X);if($wl)return
substr($Ui,0,end($W));return$Ui;}function
str2long($Ui,$wl){$W=array_values(unpack('V*',str_pad($Ui,4*ceil(strlen($Ui)/4),"\0")));if($wl)$W[]=strlen($Ui);return$W;}function
xxtea_mx($Dl,$Cl,$Qj,$kf){return
int32((($Dl>>5&0x7FFFFFF)^$Cl<<2)+(($Cl>>3&0x1FFFFFFF)^$Dl<<4))^int32(($Qj^$Cl)+($kf^$Dl));}function
encrypt_string($Lj,$x){if($Lj=="")return"";$x=array_values(unpack("V*",pack("H*",md5($x))));$W=str2long($Lj,true);$vg=count($W)-1;$Dl=$W[$vg];$Cl=$W[0];$qi=floor(6+52/($vg+1));$Qj=0;while($qi-->0){$Qj=int32($Qj+0x9E3779B9);$Hc=$Qj>>2&3;for($Ah=0;$Ah<$vg;$Ah++){$Cl=$W[$Ah+1];$ug=xxtea_mx($Dl,$Cl,$Qj,$x[$Ah&3^$Hc]);$Dl=int32($W[$Ah]+$ug);$W[$Ah]=$Dl;}$Cl=$W[0];$ug=xxtea_mx($Dl,$Cl,$Qj,$x[$Ah&3^$Hc]);$Dl=int32($W[$vg]+$ug);$W[$vg]=$Dl;}return
long2str($W,false);}function
decrypt_string($Lj,$x){if($Lj=="")return"";if(!$x)return
false;$x=array_values(unpack("V*",pack("H*",md5($x))));$W=str2long($Lj,false);$vg=count($W)-1;$Dl=$W[$vg];$Cl=$W[0];$qi=floor(6+52/($vg+1));$Qj=int32($qi*0x9E3779B9);while($Qj){$Hc=$Qj>>2&3;for($Ah=$vg;$Ah>0;$Ah--){$Dl=$W[$Ah-1];$ug=xxtea_mx($Dl,$Cl,$Qj,$x[$Ah&3^$Hc]);$Cl=int32($W[$Ah]-$ug);$W[$Ah]=$Cl;}$Dl=$W[$vg];$ug=xxtea_mx($Dl,$Cl,$Qj,$x[$Ah&3^$Hc]);$Cl=int32($W[0]-$ug);$W[0]=$Cl;$Qj=int32($Qj-0x9E3779B9);}return
long2str($W,true);}$Th=array();if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$X){list($x)=explode(":",$X);$Th[$x]=$X;}}function
add_invalid_login(){$La=get_temp_dir()."/adminer-invalid";foreach(glob("$La*")?:array($La)as$o){$q=file_open_lock($o);if($q)break;}if(!$q)$q=file_open_lock("$La-".rand_string());if(!$q)return;$Ye=json_decode(stream_get_contents($q),true);$ok=time();if($Ye){foreach($Ye
as$Ze=>$X){if($X[0]<$ok)unset($Ye[$Ze]);}}$Xe=&$Ye[adminer()->bruteForceKey()];if(!$Xe)$Xe=array($ok+30*60,0);$Xe[1]++;file_write_unlock($q,json_encode($Ye));}function
check_invalid_login(array&$Th){$Ye=array();foreach(glob(get_temp_dir()."/adminer-invalid*")as$o){$q=file_open_lock($o);if($q){$Ye=json_decode(stream_get_contents($q),true);file_unlock($q);break;}}$x=adminer()->bruteForceKey();$Xe=idx($Ye,$x,array());$Fg=($Xe[1]>29?$Xe[0]-time():0);if($Fg>0){$l=lang_format(array('Too many unsuccessful logins, try again in %d minute.','Too many unsuccessful logins, try again in %d minutes.'),ceil($Fg/60));if($_SERVER["HTTP_X_FORWARDED_FOR"]!=""&&$x==$_SERVER["REMOTE_ADDR"])$l
.='<br>'.sprintf('Use the %s <a%s>plugin</a> if Adminer runs behind a reverse proxy.','<b>login-reverse-proxy</b>'," href='https://www.adminer.org/plugins/?version=".VERSION."'".target_blank());auth_error($l,$Th);}}function
password_required(){static$I;if($I===null){$I=(bool)get_session("password_required");if(!$I){$Pb=adminer()->credentials();$I=!is_object(Driver::connect($Pb[0],$Pb[1],""));if($I)set_session("password_required",true);}}return$I;}$Da=$_POST["auth"];if($Da){session_regenerate_id();$rl=$Da["driver"];$N=$Da["server"];$V=$Da["username"];$E=(string)$Da["password"];$j=$Da["db"];set_password($rl,$N,$V,$E);$_SESSION["db"][$rl][$N][$V][$j]=true;if($Da["permanent"]){$x=implode("-",array_map('base64_encode',array($rl,$N,$V,$j)));$li=adminer()->permanentLogin(true);$Th[$x]="$x:".base64_encode($li?encrypt_string($E,$li):"");cookie("adminer_permanent",implode(" ",$Th));}if(count($_POST)==1||DRIVER!=$rl||SERVER!=$N||$_GET["username"]!==$V||DB!=$j)redirect(auth_url($rl,$N,$V,$j));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(array("pwds","db","dbs","queries")as$x)set_session($x,null);unset_permanent($Th);redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),'Logout successful.'.' '.'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.');}elseif($Th&&!$_SESSION["pwds"]){session_regenerate_id();$li=adminer()->permanentLogin();foreach($Th
as$x=>$X){list(,$jb)=explode(":",$X);list($rl,$N,$V,$j)=array_map('base64_decode',explode("-",$x));set_password($rl,$N,$V,decrypt_string(base64_decode($jb),$li));$_SESSION["db"][$rl][$N][$V][$j]=true;}}function
unset_permanent(array&$Th){foreach($Th
as$x=>$X){list($rl,$N,$V,$j)=array_map('base64_decode',explode("-",$x));if($rl==DRIVER&&$N==SERVER&&$V==$_GET["username"]&&$j==DB)unset($Th[$x]);}cookie("adminer_permanent",implode(" ",$Th));}function
auth_error($l,array&$Th){$pj=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$pj]||$_GET[$pj])&&!$_SESSION["token"])$l='Session expired, please login again.';else{restart_session();add_invalid_login();$E=get_password();if($E!==null){if($E===false)$l
.=($l?'<br>':'').sprintf('Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',target_blank(),'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);}unset_permanent($Th);}}if(!$_COOKIE[$pj]&&$_GET[$pj]&&ini_bool("session.use_only_cookies"))$l='Session support must be enabled.';$Eh=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?:rand_string()),$Eh["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header('Login',$l,null);echo"<form action='' method='post'>\n","<div>";if(hidden_fields($_POST,array("auth")))echo"<p class='message'>".'The action will be performed after successful login with the same credentials.'."\n";echo"</div>\n";adminer()->loginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!class_exists('Adminer\Db')){unset($_SESSION["pwds"][DRIVER]);unset_permanent($Th);page_header('No extension',sprintf('None of the supported PHP extensions (%s) are available.',implode(", ",Driver::$extensions)),false);page_footer("auth");exit;}$f='';if(isset($_GET["username"])&&is_string(get_password())){list($se,$Yh)=host_port(SERVER);if(preg_match('~[^-\w.:/]~',$se.$Yh))auth_error('Invalid server.',$Th);if(preg_match('~^-?\d+~',$Yh,$B)&&($B[0]<1024||$B[0]>65535))auth_error('Connecting to privileged ports is not allowed.',$Th);check_invalid_login($Th);$Pb=adminer()->credentials();$f=Driver::connect($Pb[0],$Pb[1],$Pb[2]);if(is_object($f)){Db::$instance=$f;Driver::$instance=new
Driver($f);if($f->flavor)save_settings(array("vendor-".DRIVER."-".SERVER=>get_driver(DRIVER)));}}$Jf=null;if(!is_object($f)||($Jf=adminer()->login($_GET["username"],get_password()))!==true){$l=(is_string($f)?nl_br(h($f)):(is_string($Jf)?$Jf:'Invalid credentials.')).(preg_match('~^ | $~',get_password())?'<br>'.'There is a space in the input password which might be the cause.':'');auth_error($l,$Th);}if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){page_header('Logout','Invalid CSRF token. Send the form again.');page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($Da&&$_POST["token"])$_POST["token"]=get_token();$l='';if($_POST){if(!verify_token())$l='Invalid CSRF token. Send the form again.'.' '.'If you did not send this request from Adminer then close this page.';}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$l=sprintf('Too big POST data. Reduce the data or increase the %s configuration directive.',"<b>post_max_size</b>'");if(isset($_GET["sql"]))$l
.=' '.'You can upload a big SQL file via FTP and import it from server.';}function
print_select_result($H,$g=null,array$ph=array(),&$z=0){$Ff=array();$w=array();$e=array();$Qa=array();$Ok=array();$I=array();for($s=0;(!$z||$s<$z)&&($J=$H->fetch_row());$s++){if(!$s){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr>";for($gf=0;$gf<count($J);$gf++){$m=$H->fetch_field();$C=$m->name;$oh=(isset($m->orgtable)?$m->orgtable:"");$nh=(isset($m->orgname)?$m->orgname:$C);if($ph&&JUSH=="sql")$Ff[$gf]=($C=="table"?"table=":($C=="possible_keys"?"indexes=":null));elseif($oh!=""){if(isset($m->table))$I[$m->table]=$oh;if(!isset($w[$oh])){$w[$oh]=array();foreach(indexes($oh,$g)as$v){if($v["type"]=="PRIMARY"){$w[$oh]=array_flip($v["columns"]);break;}}$e[$oh]=$w[$oh];}if(isset($e[$oh][$nh])){unset($e[$oh][$nh]);$w[$oh][$nh]=$gf;$Ff[$gf]=$oh;}}if($m->charsetnr==63)$Qa[$gf]=true;$Ok[$gf]=$m->type;echo"<th title='".h(trim(($oh!=""?"$oh.$nh":($m->name!=$nh?$nh:""))." ".driver()->typeName($m)))."'>".h($C).($ph?doc_link(array('sql'=>"explain-output.html#explain_".strtolower($C),'mariadb'=>"explain/#the-columns-in-explain-select",)):"");}echo"<tbody>\n";}echo"<tr>";foreach($J
as$x=>$X){$_="";if(isset($Ff[$x])&&!$e[$Ff[$x]]){if($ph&&JUSH=="sql"){$R=$J[array_search("table=",$Ff)];$_=ME.$Ff[$x].url_escape($ph[$R]!=""?$ph[$R]:$R);}else{$_=ME."edit=".url_escape($Ff[$x]);foreach($w[$Ff[$x]]as$nb=>$gf){if($J[$gf]===null){$_="";break;}$_
.="&where[".url_escape(bracket_escape($nb))."]=".url_escape($J[$gf]);}}}$m=array('type'=>($Qa[$x]?'blob':($Ok[$x]==254?'char':'')),);$X=select_value($X,$_,$m,null);echo"<td".($Ok[$x]<=9||$Ok[$x]==246?" class='number'":"").">$X";}}$z=$s;echo($s?"</table>\n</div>":"<p class='message'>".'No rows.')."\n";return$I;}function
referencable_primary($ej){$I=array();foreach(table_status('',true)as$Yj=>$R){if($Yj!=$ej&&fk_support($R)){foreach(fields($Yj)as$m){if($m["primary"]){if($I[$Yj]){unset($I[$Yj]);break;}$I[$Yj]=$m;}}}}return$I;}function
textarea($C,$Y,$K=10,$rb=80){echo"<textarea name='".h($C)."' rows='$K' cols='$rb' class='sqlarea jush-".JUSH."' spellcheck='false' wrap='off'>";if(is_array($Y)){foreach($Y
as$X)echo
h($X[0])."\n\n\n";}else
echo
h($Y);echo"</textarea>";}function
select_input($c,array$jh,$Y="",$Uh=""){if($jh&&$Y!=""&&!isset($jh[$Y]))$jh=array($Y=>$Y)+$jh;$gk=($jh?"select":"input");return"<$gk$c".($jh?"><option value=''>$Uh".optionlist($jh,$Y,true)."</select>":" size='10' value='".h($Y)."' placeholder='$Uh'>");}function
json_row($x,$X=null,$Xc=true){static$_d=true;if($_d)echo"{";if($x!=""){echo($_d?"":",")."\n\t\"".addcslashes($x,"\r\n\t\"\\/").'": '.($X!==null?($Xc?'"'.addcslashes($X,"\r\n\"\\/").'"':$X):'null');$_d=false;}else{echo"\n}\n";$_d=true;}}function
edit_type($x,array$m,array$qb,array$Jd=array(),array$ld=array()){$U=(string)$m["type"];echo"<td><select name='".h($x)."[type]' class='type' aria-labelledby='label-type'".on_help_value().">";if($U&&!array_key_exists($U,driver()->types())&&!isset($Jd[$U])&&!in_array($U,$ld))$ld[]=$U;$Mj=driver()->structuredTypes();if($Jd)$Mj['Foreign keys']=$Jd;echo
optionlist(array_merge($ld,$Mj),$U),"</select><td>","<input name='".h($x)."[length]' value='".h($m["length"])."' size='3'".(!$m["length"]&&preg_match('~var(char|binary)$~',$U)?" class='required'":"")." aria-labelledby='label-length'>","<td class='options'>",($qb?"<input list='collations' name='".h($x)."[collation]'".option_types($U,'(char|text|enum|set)$')." value='".h($m["collation"])."' placeholder='(".'collation'.")'>":''),(driver()->unsigned?"<select name='".h($x)."[unsigned]'".option_types($U,'^$|'.number_type()).'><option>'.optionlist(driver()->unsigned,$m["unsigned"]).'</select>':''),(isset($m['on_update'])?"<select name='".h($x)."[on_update]'".option_types($U,'timestamp|datetime').'>'.optionlist(array(""=>"(".'ON UPDATE'.")","CURRENT_TIMESTAMP"),(preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"CURRENT_TIMESTAMP":$m["on_update"])).'</select>':''),($Jd?"<select name='".h($x)."[on_delete]'".option_types($U,'`')."><option value=''>(".'ON DELETE'.")".optionlist(explode("|",driver()->onActions),$m["on_delete"])."</select> ":" ");}function
option_types($U,$Ok){return" data-types='".h($Ok)."'".(preg_match("~$Ok~",$U)?"":" class='hidden'");}function
process_length($y){$Sc=driver()->enumLength;return(preg_match("~^\\s*\\(?\\s*$Sc(?:\\s*,\\s*$Sc)*+\\s*\\)?\\s*\$~",$y)&&preg_match_all("~$Sc~",$y,$Of)?"(".implode(",",$Of[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$y)));}function
process_type(array$m,$ob="COLLATE"){return" $m[type]".process_length($m["length"]).(preg_match(number_type(),$m["type"])&&in_array($m["unsigned"],driver()->unsigned)?" $m[unsigned]":"").(preg_match('~char|text|enum|set~',$m["type"])&&$m["collation"]?" $ob ".(JUSH=="mssql"?$m["collation"]:q($m["collation"])):"");}function
process_field(array$m,array$Mk){if($m["on_update"])$m["on_update"]=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$m["on_update"]);return
array(idf_escape(trim($m["field"])),process_type($Mk),($m["null"]?" NULL":" NOT NULL"),default_value($m),(preg_match('~timestamp|datetime~',$m["type"])&&$m["on_update"]?" ON UPDATE $m[on_update]":""),(support("comment")&&$m["comment"]!=""?" COMMENT ".q($m["comment"]):""),($m["auto_increment"]?auto_increment():null),);}function
default_value(array$m){if($m["default"]===null)return"";$k=str_replace("\r","",$m["default"]);$Td=$m["generated"];return(in_array($Td,driver()->generated)?(JUSH=="mssql"?" AS ($k)".($Td=="VIRTUAL"?"":" $Td"):" GENERATED ALWAYS AS ($k) $Td"):(preg_match('~^GENERATED ~i',$k)?" $k":" DEFAULT ".(preg_match('~char|binary|text|json|enum|set|String~',$m["type"])||preg_match('~^(?![a-z])~i',$k)?(JUSH=="sql"&&preg_match('~text|json~',$m["type"])?"(".q($k).")":q($k)):str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",(JUSH=="sqlite"?"($k)":$k)))));}function
type_class($U){foreach(array('char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',)as$x=>$X){if(preg_match("~$x|$X~",$U))return" class='$x'";}}function
edit_fields(array$n,array$qb,$U="TABLE",array$Jd=array()){$n=array_values($n);$fc=(($_POST?$_POST["defaults"]:get_setting("defaults"))?"":" class='hidden'");$vb=(($_POST?$_POST["comments"]:get_setting("comments"))?"":" class='hidden'");echo"<thead><tr>\n",($U=="PROCEDURE"?"<td>":""),"<th id='label-name'>".($U=="TABLE"?'Column name':'Parameter name'),"<td id='label-type'>".'Type'."<textarea id='enum-edit' rows='4' cols='12' wrap='off' hidden></textarea>".script("qs('#enum-edit').onblur = editingLengthBlur;"),"<td id='label-length'>".'Length',"<td>".'Options';if($U=="TABLE")echo"<td id='label-null'>NULL\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='".'Auto Increment'."'>AI</abbr>",doc_link(array('sql'=>"example-auto-increment.html",'mariadb'=>"auto_increment/",'sqlite'=>"autoinc.html",'pgsql'=>"datatype-numeric.html#DATATYPE-SERIAL",'mssql'=>"t-sql/statements/create-table-transact-sql-identity-property",)),"<td id='label-default'$fc>".'Default value',(support("comment")?"<td id='label-comment'$vb>".'Comment':"");$vf=!support("move_col");echo"<td>".icon("plus","add[".($vf?count($n):0)."]","+",'Add next',($vf?on('click','editingAddLastRow'):"")),"<tbody".on('click','editingClick').on('input','editingInput').on('keydown','editingKeydown').">\n";foreach($n
as$s=>$m){$s++;$qh=$m[($_POST?"orig":"field")];$rc=(isset($_POST["add"][$s-1])||(isset($m["field"])&&!idx($_POST["drop_col"],$s)))&&(support("drop_col")||$qh=="");echo"<tr".($rc?"":" hidden").">\n",($U=="PROCEDURE"?"<td>".html_select("fields[$s][inout]",explode("|",driver()->inout),$m["inout"]):"")."<th>",(support("move_col")?icon("move","","↕",'Move')." ":"");if($rc)echo"<input name='fields[$s][field]' value='".h($m["field"])."' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name'".(isset($_POST["add"][$s-1])?" autofocus":"").">";echo
input_hidden("fields[$s][orig]",$qh);edit_type("fields[$s]",$m,$qb,$Jd);if($U=="TABLE"){echo"<td><label class='block'>".checkbox("fields[$s][null]",1,$m["null"],"","","","label-null")."</label>","<td><label class='block'><input type='radio' name='auto_increment_col' value='$s'".($m["auto_increment"]?" checked":"")." aria-labelledby='label-ai'></label>","<td$fc>".(driver()->generated?html_select("fields[$s][generated]",array_merge(array("","DEFAULT"),driver()->generated),$m["generated"])." ":checkbox("fields[$s][generated]",1,$m["generated"],"","","","label-default"));$c=" name='fields[$s][default]' aria-labelledby='label-default'";$Y=h($m["default"]);echo(preg_match('~\n~',$m["default"])?"<textarea$c rows='2' cols='30' style='vertical-align: bottom;'>\n$Y</textarea>":"<input$c value='$Y'>");if(support("comment")){$c=" name='fields[$s][comment]' data-maxlength='".(min_version(5.5)?1024:255)."' aria-labelledby='label-comment'";echo"<td$vb>".adminer()->commentInput('COLUMN',$c,$m["comment"]);}}echo"<td>",(support("move_col")?icon("plus","add[$s]","+",'Add next')." ":""),($qh==""||support("drop_col")?icon("cross","drop_col[$s]","x",'Remove'):"");}}function
process_fields(array&$n){if($_POST["add"]){$n=array_values($n);array_splice($n,key($_POST["add"]),0,array(array()));}return$_POST["add"]||$_POST["drop_col"];}function
normalize_enum(array$B){$X=$B[0];return"'".str_replace("'","''",addcslashes(stripcslashes(str_replace($X[0].$X[0],$X[0],substr($X,1,-1))),'\\'))."'";}function
grant($Vd,array$ni,$e,$Zg){if(!$ni)return
true;if($ni==array("ALL PRIVILEGES","GRANT OPTION"))return($Vd=="GRANT"?queries("$Vd ALL PRIVILEGES$Zg WITH GRANT OPTION"):queries("$Vd ALL PRIVILEGES$Zg")&&queries("$Vd GRANT OPTION$Zg"));return
queries("$Vd ".preg_replace('~(GRANT OPTION)\([^)]*\)~','\1',implode("$e, ",$ni).$e).$Zg);}function
drop_create($Bc,$h,$Dc,$kk,$Fc,$A,$ig,$gg,$hg,$Wg,$Ag){if($_POST["drop"])query_redirect($Bc,$A,$ig);elseif($Wg=="")query_redirect($h,$A,$hg);elseif(support("transaction_ddl")){driver()->begin();queries_redirect($A,$gg,queries($Bc)&&queries($h)&&driver()->commit());driver()->rollback();}elseif($Wg!=$Ag){$Ob=queries($h);queries_redirect($A,$gg,$Ob&&queries($Bc));if($Ob)queries($Dc);}else
queries_redirect($A,$gg,queries($kk)&&queries($Fc)&&queries($Bc)&&queries($h));}function
create_trigger($Zg,array$J){$qk=" $J[Timing] $J[Event]".(preg_match('~ OF~',$J["Event"])?" $J[Of]":"");return"CREATE TRIGGER ".idf_escape($J["Trigger"]).(JUSH=="mssql"?$Zg.$qk:$qk.$Zg).rtrim(" $J[Type]\n$J[Statement]",";").";";}function
q_dollar($Q){$jc='$$';while(strpos($Q.$jc,$jc)!=strlen($Q))$jc='$_'.substr($jc,1);return$jc.$Q.$jc;}function
create_routine($Pi,array$J){$O=array();$n=(array)$J["fields"];ksort($n);foreach($n
as$m){if($m["field"]!="")$O[]=(preg_match("~^(".driver()->inout.")\$~",$m["inout"])?"$m[inout] ":"").idf_escape($m["field"]).process_type($m,"CHARACTER SET");}$hc=rtrim($J["definition"],";");return"CREATE $Pi ".idf_escape(trim($J["name"]))." (".implode(", ",$O).")".($Pi=="FUNCTION"?" RETURNS".process_type($J["returns"],"CHARACTER SET"):"").($J["language"]?" LANGUAGE $J[language]":"").(JUSH=="pgsql"?" AS ".q_dollar("\n".trim($hc)."\n"):"\n$hc;");}function
remove_definer($G){return
preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~','`@`(%|\1)',logged_user()).'`~','\1',$G);}function
format_foreign_key(array$p){$j=$p["db"];$Hg=$p["ns"];return" FOREIGN KEY (".implode(", ",array_map('Adminer\idf_escape',$p["source"])).") REFERENCES ".($j!=""&&$j!=$_GET["db"]?idf_escape($j).".":"").($Hg!=""&&$Hg!=$_GET["ns"]?idf_escape($Hg).".":"").idf_escape($p["table"])." (".implode(", ",array_map('Adminer\idf_escape',$p["target"])).")".(preg_match("~^(".driver()->onActions.")\$~",$p["on_delete"])?" ON DELETE $p[on_delete]":"").(preg_match("~^(".driver()->onActions.")\$~",$p["on_update"])?" ON UPDATE $p[on_update]":"").($p["deferrable"]?" $p[deferrable]":"");}function
tar_file($o,$vk){$I=pack("a100a8a8a8a12a12",$o,644,0,0,decoct($vk->size),decoct(time()));$hb=8*32;for($s=0;$s<strlen($I);$s++)$hb+=ord($I[$s]);$I
.=sprintf("%06o",$hb)."\0 ";echo$I,str_repeat("\0",512-strlen($I));$vk->send();echo
str_repeat("\0",511-($vk->size+511)%512);}function
doc_link(array$Qh,$lk="<sup>?</sup>"){$nj=connection()->server_info;$sl=preg_replace('~^(\d\.?\d).*~s','\1',$nj);$cl=array('sql'=>"https://dev.mysql.com/doc/refman/$sl/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(connection()->flavor=='cockroach'?"current":$sl)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://www.oracle.com/pls/topic/lookup?ctx=db".preg_replace('~^.* (\d+)\.(\d+)\.\d+\.\d+\.\d+.*~s','\1\2',$nj)."&id=",);if(connection()->flavor=='maria'){$cl['sql']="https://mariadb.com/kb/en/";$Qh['sql']=(isset($Qh['mariadb'])?$Qh['mariadb']:str_replace(".html","/",$Qh['sql']));}return($Qh[JUSH]?"<a href='".h($cl[JUSH].$Qh[JUSH].(JUSH=='mssql'?"?view=sql-server-ver$sl":""))."'".target_blank().">$lk</a>":"");}function
db_size($j){if(!connection()->select_db($j))return"?";$I=0;foreach(table_status()as$S)$I+=$S["Data_length"]+$S["Index_length"];return
format_number($I);}function
set_utf8mb4($h){static$O=false;if(!$O&&preg_match('~\butf8mb4~i',$h)){$O=true;echo"SET NAMES ".charset(connection()).";\n\n";}}if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];if(DB==""&&isset($_GET["ns"]))redirect(remove_from_uri('ns'));if(!(DB!=""?connection()->select_db(DB):isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill")){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!=""){header("HTTP/1.1 404 Not Found");page_header('Database'.": ".h(DB),'Invalid database.',true);}else{if($_POST["db"]&&!$l)queries_redirect(substr(ME,0,-1),'Databases have been dropped.',drop_databases($_POST["db"]));page_header('Select database',$l,false);echo"<p class='links'>\n";foreach(array('database'=>'Create database','privileges'=>'Privileges','processlist'=>'Process list','variables'=>'Variables','status'=>'Status',)as$x=>$X){if(support($x))echo"<a href='".h(ME)."$x='>$X</a>\n";}echo"<p>".sprintf('%s version: %s through PHP extension %s',get_driver(DRIVER),"<b>".h(connection()->server_info)."</b>","<b>".connection()->extension."</b>")."\n","<p>".sprintf('Logged as: %s',"<b>".h(logged_user())."</b>")."\n";$i=adminer()->databases();if($i){$Xi=support("scheme");$qb=collations();echo"<form action='' method='post'>\n","<table class='checkable odds'".on('click','tableClick').on('dblclick','tableClick').">\n","<thead><tr>".(support("database")?"<td class='hover'>":"")."<th".(JUSH!='mssql'?" aria-sort='ascending'":"").">".'Database'.(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".'Refresh'."</a>":"")."<td>".'Collation'."<td>".'Tables'."<td>".'Size'." - <a href='".h(ME)."dbsize=1'".on('click','ajaxSetHtml',ME."script=connect").">".'Compute'."</a>"."<tbody>\n";$i=($_GET["dbsize"]?count_tables($i):array_flip($i));foreach($i
as$j=>$T){$Oi=h(ME)."db=".url_escape($j);$t=h("Db-".$j);echo"<tr>".(support("database")?"<td class='hover'>".checkbox("db[]",$j,in_array($j,(array)$_POST["db"]),"","","",$t):""),"<th><a href='$Oi' id='$t'>".h($j)."</a>";$pb=h(db_collation($j,$qb));echo"<td>".(support("database")?"<a href='$Oi".($Xi?"&amp;ns=":"")."&amp;database=' title='".'Alter database'."'>$pb</a>":$pb),"<td align='right'><a href='$Oi&amp;schema=' id='tables-".h($j)."' title='".'Database schema'."'>".($_GET["dbsize"]?$T:"?")."</a>","<td align='right' id='size-".h($j)."'>".($_GET["dbsize"]?db_size($j):"?"),"\n";}echo"</table>\n",(support("database")?"<div class='footer'><div>\n"."<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>\n"."<input type='hidden' name='all' value=''".on('click','countDbs').">\n"."<input type='submit' name='drop' value='".'Drop'."'".confirm().">\n"."</div></fieldset>\n"."</div></div>\n":""),input_token(),"</form>\n",script("tableCheck();");}$na=adminer();$Xh=($na
instanceof
Plugins?$na->plugins:array());$Ac=($na
instanceof
Plugins?$na->drivers:array());$oc=design_checksums();if($Xh||$Ac||$oc){$ib=($na
instanceof
Plugins?$na->checksums():array());$Og=Plugins::officialChecksums();$Zk=function($bl){return" (<a href='$bl'".target_blank()." class='update'>".VERSION."</a>)";};$Wh=function($ud)use($ib,$Og,$Zk){return($ib[$ud]&&$Og[$ud]&&$ib[$ud]!==$Og[$ud]?$Zk("https://www.adminer.org/plugins/?version=".VERSION):"");};echo"<div class='plugins'>\n","<h3>".'Loaded plugins'."</h3>\n<ul>\n";foreach($Xh
as$Vh){$Bi=new
\ReflectionObject($Vh);$lc=(method_exists($Vh,'description')?$Vh->description():"");if(!$lc){if(preg_match('~^/[\s*]+(.+)~',$Bi->getDocComment(),$B))$lc=$B[1];}$Yi=(method_exists($Vh,'screenshot')?$Vh->screenshot():"");echo"<li><b>".get_class($Vh)."</b>".h($lc?": $lc":"").($Yi?" (<a href='".h($Yi)."'".target_blank().">".'screenshot'."</a>)":"").$Wh(basename((string)$Bi->getFileName(),'.php'))."\n";}foreach($Ac
as$t=>$C)echo"<li><b>".h($t)."</b>: ".h($C).$Wh(basename((string)$na->driverFiles[$t],'.php'))."\n";if($oc){$Qg=official_design_checksums();foreach($oc
as$o=>$nc){list($C,$hb)=$nc;$Pg=$Qg["$C/$o"];echo"<li><b>".h($o)."</b>".h($C?": $C":"").($Pg&&$Pg!==$hb?$Zk("https://www.adminer.org/?version=".VERSION."#extras"):"")."\n";}}echo"</ul>\n";adminer()->pluginsLinks();echo"</div>\n";}}page_footer("db");exit;}if(support("scheme")){if(DB!=""&&$_GET["ns"]!==""){if(!isset($_GET["ns"]))redirect(preg_replace('~&db=[^&]+~','\0&ns='.url_escape(get_schema()),relative_uri()));if(!set_schema($_GET["ns"])){header("HTTP/1.1 404 Not Found");page_header('Schema'.h(": $_GET[ns]"),'Invalid schema.',true);page_footer("ns");exit;}}}adminer()->afterConnect();class
TmpFile{private$handler;var$size=0;function
__construct(){$this->handler=tmpfile();}function
write($Gb){$this->size+=strlen($Gb);fwrite($this->handler,$Gb);}function
send(){fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$n=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$M=array(idf_escape($_GET["field"]));$H=driver()->select($a,$M,array(where($_GET,$n)),$M);$J=($H?$H->fetch_row():array());echo
driver()->value($J[0],$n[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$n=fields($a);if(!$n)$l=error()?:'No tables.';$S=table_status1($a);$C=adminer()->tableName($S);page_header(($n&&is_view($S)?$S['Engine']=='materialized view'?'Materialized view':'View':'Table').": ".($C!=""?$C:h($a)),$l);$Ni=array();foreach($n
as$x=>$m)$Ni+=$m["privileges"];adminer()->selectLinks($S,(isset($Ni["insert"])||!support("table")?"":null));$ub=$S["Comment"];if($ub!="")echo"<p class='nowrap'>".'Comment'.": ".adminer()->commentValue('TABLE',$ub)."\n";if($n)adminer()->tableStructurePrint($n,$S);function
tables_links(array$T){echo"<ul>\n";foreach($T
as$J){$_=preg_replace('~ns=[^&]*~',"ns=".url_escape($J["ns"]),ME);echo"<li><a href='".h($_."table=".url_escape($J["table"]))."'>".($J["ns"]!=$_GET["ns"]?"<b>".h($J["ns"])."</b>.":"").h($J["table"])."</a>";}echo"</ul>\n";}$Pe=driver()->inheritsFrom($a);if($Pe){echo"<h3>".'Inherits from'."</h3>\n";tables_links($Pe);}if(support("indexes")&&driver()->supportsIndex($S)){echo"<div>\n","<h3 id='indexes'>".'Indexes'."</h3>\n";$w=indexes($a);if($w)adminer()->tableIndexesPrint($w,$S);if(driver()->supportsAlterIndex($S))echo'<p class="links hover"><a href="'.h(ME).'indexes='.url_escape($a).'">'.'Alter indexes'."</a>\n";echo"</div>\n";}if(!is_view($S)){if(fk_support($S)){echo"<div>\n","<h3 id='foreign-keys'>".'Foreign keys'."</h3>\n";$Jd=foreign_keys($a);if($Jd){echo"<table>\n","<thead><tr><th>".'Source'."<td>".'Target'."<td>".'ON DELETE'."<td>".'ON UPDATE'."<td class='hover'><tbody>\n";foreach($Jd
as$C=>$p){echo"<tr title='".h($C)."'>","<th><i>".implode("</i>, <i>",array_map('Adminer\h',$p["source"]))."</i>";$_=($p["db"]!=""?preg_replace('~db=[^&]*~',"db=".url_escape($p["db"]),ME):($p["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".url_escape($p["ns"]),ME):ME));echo"<td><a href='".h($_."table=".url_escape($p["table"]))."'>".($p["db"]!=""&&$p["db"]!=DB?"<b>".h($p["db"])."</b>.":"").($p["ns"]!=""&&$p["ns"]!=$_GET["ns"]?"<b>".h($p["ns"])."</b>.":"").h($p["table"])."</a>","(<i>".implode("</i>, <i>",array_map('Adminer\h',$p["target"]))."</i>)","<td>".h($p["on_delete"]),"<td>".h($p["on_update"]),'<td class="hover"><a href="'.h(ME.'foreign='.url_escape($a).'&name='.url_escape($C)).'">'.'Alter'.'</a>',"\n";}echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'foreign='.url_escape($a).'">'.'Create foreign key'."</a>\n","</div>\n";}if(support("check")){echo"<div>\n","<h3 id='checks'>".'Checks'."</h3>\n";$db=driver()->checkConstraints($a);if($db){echo"<table>\n";foreach($db
as$x=>$X)echo"<tr title='".h($x)."'>","<td><code class='jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim($X)),80,"</code>"),"<td class='hover'><a href='".h(ME.'check='.url_escape($a).'&name='.url_escape($x))."'>".'Alter'."</a>","\n";echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'check='.url_escape($a).'">'.'Create check'."</a>\n","</div>\n";}}if(support(is_view($S)?"view_trigger":"trigger")){echo"<div>\n","<h3 id='triggers'>".'Triggers'."</h3>\n";$Jk=triggers($a);if($Jk){echo"<table>\n";foreach($Jk
as$x=>$X)echo"<tr valign='top'><td>".h($X[0])."<td>".h($X[1])."<th>".h($x)."<td class='hover'><a href='".h(ME.'trigger='.url_escape($a).'&name='.url_escape($x))."'>".'Alter'."</a>\n";echo"</table>\n";}echo'<p class="links hover"><a href="'.h(ME).'trigger='.url_escape($a).'">'.'Create trigger'."</a>\n","</div>\n";}$Oe=driver()->inheritedTables($a);if($Oe){echo"<h3 id='partitions'>".'Inherited by'."</h3>\n";$Ih=driver()->partitionsInfo($a);if($Ih)echo"<p><code class='jush-".JUSH."'>BY ".h("$Ih[partition_by]($Ih[partition])")."</code>\n";tables_links($Oe);}}elseif(isset($_GET["schema"])){page_header('Database schema',"",array(),h(DB.($_GET["ns"]?".$_GET[ns]":"")));$ak=array();$bk=array();$rd=array();$ca=($_GET["schema"]?:$_COOKIE["adminer_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$ca,$Of,PREG_SET_ORDER);foreach($Of
as$s=>$B){$ak[$B[1]]=array((float)$B[2],(float)$B[3]);$bk[]="\n\t'".js_escape($B[1])."': [ $B[2], $B[3] ]";}$yk=0;$Ma=-1;$L=array();$Ai=array();$zf=array();$ua=driver()->allFields();foreach(table_status('',true)as$R=>$S){if(is_view($S))continue;$F=0;$L[$R]["fields"]=array();foreach($ua[$R]as$m){$F+=1.25;$rd[$R][$m["field"]]=$F;$L[$R]["fields"][$m["field"]]=$m;}$L[$R]["pos"]=($ak[$R]?:array($yk,0));foreach(adminer()->foreignKeys($R)as$X){if(!$X["db"]){$xf=$Ma;if(idx($ak[$R],1)||idx($ak[$X["table"]],1))$xf=min(idx($ak[$R],1,0),idx($ak[$X["table"]],1,0))-1;else$Ma-=.1;while($zf[(string)$xf])$xf-=.0001;$L[$R]["references"][$X["table"]][(string)$xf]=array($X["source"],$X["target"]);$Ai[$X["table"]][$R][(string)$xf]=$X["target"];$zf[(string)$xf]=true;}}$yk=max($yk,$L[$R]["pos"][0]+2.5+$F);}echo'<div id="schema" style="height: ',$yk,'em;">
<script',nonce(),'>
const tablePos = {',implode(",",$bk)."\n",'};
const em = qs(\'#schema\').offsetHeight / ',$yk,';
document.onmousemove = schemaMousemove;
document.onmouseup = event => schemaMouseup(event, \'',js_escape(DB),'\');
</script>
';foreach($L
as$C=>$R){echo"<div class='table'".on('mousedown','schemaMousedown')." style='top: ".$R["pos"][0]."em; left: ".$R["pos"][1]."em;'>",'<a href="'.h(ME).'table='.url_escape($C).'"><b>'.h($C)."</b></a>";foreach($R["fields"]as$m){$X='<span'.type_class($m["type"]).' title="'.h($m["type"].($m["length"]?"($m[length])":"").($m["null"]?" NULL":'')).'">'.h($m["field"]).'</span>';echo"<br>".($m["primary"]?"<i>$X</i>":$X);}foreach((array)$R["references"]as$ik=>$Ci){foreach($Ci
as$xf=>$yi){$yf=$xf-idx($ak[$C],1);$s=0;foreach($yi[0]as$_j)echo"\n<div class='references' title='".h($ik)."' id='refs$xf-".($s++)."' style='left: $yf"."em; top: ".$rd[$C][$_j]."em; padding-top: .5em;'>"."<div style='border-top: 1px solid gray; width: ".(-$yf)."em;'></div></div>";}}foreach((array)$Ai[$C]as$ik=>$Ci){foreach($Ci
as$xf=>$e){$yf=$xf-idx($ak[$C],1);$s=0;foreach($e
as$hk)echo"\n<div class='references arrow' title='".h($ik)."' id='refd$xf-".($s++)."' style='left: $yf"."em; top: ".$rd[$C][$hk]."em;'>"."<div style='height: .5em; border-bottom: 1px solid gray; width: ".(-$yf)."em;'></div>"."</div>";}}echo"\n</div>\n";}foreach($L
as$C=>$R){foreach((array)$R["references"]as$ik=>$Ci){if($L[$ik]){foreach($Ci
as$xf=>$yi){$og=$yk;$Wf=-10;foreach($yi[0]as$x=>$_j){$Zh=$R["pos"][0]+$rd[$C][$_j];$ai=$L[$ik]["pos"][0]+$rd[$ik][$yi[1][$x]];$og=min($og,$Zh,$ai);$Wf=max($Wf,$Zh,$ai);}echo"<div class='references' id='refl$xf' style='left: $xf"."em; top: $og"."em; padding: .5em 0;'><div style='border-right: 1px solid gray; margin-top: 1px; height: ".($Wf-$og)."em;'></div></div>\n";}}}}echo'</div>
<p class="links"><a href="',h(ME."schema=".url_escape($ca)),'" id="schema-link">Permanent link</a>
';}elseif(isset($_GET["dump"])){$a=$_GET["dump"];if($_POST&&!$l){$k=array("auto_increment"=>'');foreach(array("type","routine","event","trigger")as$Sj){if(support($Sj))$k[$Sj."s"]='';}save_settings(array_intersect_key($_POST+$k,array_flip(array("output","format","db_style","table_style","data_style"))+$k),"adminer_export");$T=array_flip((array)$_POST["tables"])+array_flip((array)$_POST["data"]);$hd=dump_headers((count($T)==1?key($T):DB),(DB==""||$_GET["ns"]===""||count($T)>1));$df=preg_match('~sql~',$_POST["format"]);if($df){echo"-- Adminer ".VERSION." ".get_driver(DRIVER)." ".str_replace("\n"," ",connection()->server_info)." dump\n\n";if(JUSH=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";connection()->query("SET time_zone = '+00:00'");connection()->query("SET sql_mode = ''");}}$Nj=$_POST["db_style"];$i=array(DB);if(DB==""){$i=$_POST["databases"];if(is_string($i))$i=explode("\n",rtrim(str_replace("\r","",$i),"\n"));}foreach((array)$i
as$j){adminer()->dumpDatabase($j);if(connection()->select_db($j)){if($df&&$Nj)echo
use_sql($j,$Nj).";\n\n";foreach(($_GET["ns"]===""?(array)$_POST["schemas"]:(DB!=""||!support("scheme")?array(""):adminer()->schemas()))as$L){if($L!=""){if(DB==""&&information_schema(DB,$L))continue;set_schema($L);}$Kj=($_POST["table_style"]||$_POST["data_style"]?table_status('',true):array());$gd=array();$Yb=array();foreach($Kj
as$C=>$S){if(DB==""||$_GET["ns"]===""||in_array($C,(array)$_POST["tables"]))$gd[$C]=$S;if(DB==""||$_GET["ns"]===""||in_array($C,(array)$_POST["data"]))$Yb[$C]=$S;}if($df){if($_POST["table_style"]=="DROP+CREATE"&&function_exists('Adminer\drop_sql'))echo
drop_sql($gd);if($_POST["data_style"]=="TRUNCATE+INSERT"&&function_exists('Adminer\truncate_all_sql')){$Kk=array();foreach($Yb
as$C=>$S){if(!is_view($S)&&!($_POST["table_style"]=="DROP+CREATE"&&isset($gd[$C])))$Kk[]=$C;}echo
truncate_all_sql($Kk);}$yh="";if($_POST["types"]){foreach(types()as$t=>$U){$hc=type_definition($t);$Lg=($hc["kind"]=='d'?"DOMAIN":"TYPE");if($hc["definition"])$yh
.=($Nj!='DROP+CREATE'?"DROP $Lg IF EXISTS ".idf_escape($U).";;\n":"")."CREATE $Lg ".idf_escape($U)." $hc[definition];\n\n";else$yh
.="-- Could not export type $U\n\n";}}if($_POST["routines"]){foreach(routines()as$J){$C=$J["ROUTINE_NAME"];$Pi=$J["ROUTINE_TYPE"];$h=create_routine($Pi,array("name"=>$C)+routine($J["SPECIFIC_NAME"],$Pi));set_utf8mb4($h);$yh
.=($Nj!='DROP+CREATE'?"DROP $Pi IF EXISTS ".idf_escape($C).";;\n":"")."$h;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$J){$h=remove_definer(get_val("SHOW CREATE EVENT ".idf_escape($J["Name"]),3));set_utf8mb4($h);$yh
.=($Nj!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($J["Name"]).";;\n":"")."$h;;\n\n";}}echo($yh&&JUSH=='sql'?"DELIMITER ;;\n\n$yh"."DELIMITER ;\n\n":$yh);}if($_POST["table_style"]||$_POST["data_style"]){$ul=array();foreach($Kj
as$C=>$S){$R=array_key_exists($C,$gd);$Wb=array_key_exists($C,$Yb);if($R||$Wb){$vk=null;if($hd=="tar"){$vk=new
TmpFile;ob_start(array($vk,'write'),1e5);}adminer()->dumpTable($C,($R?$_POST["table_style"]:""),(is_view($S)?2:0));if(is_view($S))$ul[]=$C;elseif($Wb){$n=fields($C);$M=array("*");$Jb=convert_fields($n,$n);if($Jb)$M[]=substr($Jb,2);adminer()->dumpData($C,$_POST["data_style"],"",$M);}if($df&&$_POST["triggers"]&&$R&&($Jk=trigger_sql($C)))echo"\nDELIMITER ;;\n$Jk\nDELIMITER ;\n";if($hd=="tar"){ob_end_flush();tar_file((DB!=""?"":"$j/")."$C.csv",$vk);}elseif($df)echo"\n";}}if($df&&$_POST["table_style"]&&function_exists('Adminer\foreign_keys_sql')){foreach($gd
as$C=>$S){if(!is_view($S))echo
foreign_keys_sql($C);}}if($df){foreach($ul
as$tl)adminer()->dumpTable($tl,$_POST["table_style"],1);}if($hd=="tar")echo
pack("x1024");}}}}adminer()->dumpFooter();exit;}page_header('Export',$l,($_GET["export"]!=""?array("table"=>$_GET["export"]):array()),h(DB));echo'
<form action="" method="post">
<table class="layout">
';$bc=array('','USE','DROP+CREATE','CREATE');$ck=array('','DROP+CREATE','CREATE');$Xb=array('','TRUNCATE+INSERT','INSERT');if(JUSH=="sql")$Xb[]='INSERT+UPDATE';$J=get_settings("adminer_export");if(!$J)$J=array("output"=>"text","format"=>"sql","db_style"=>(DB!=""?"":"CREATE"),"table_style"=>"DROP+CREATE","data_style"=>"INSERT");echo"<tr><th>".'Output'."<td>".html_radios("output",adminer()->dumpOutput(),$J["output"])."\n","<tr><th>".'Format'."<td>".html_radios("format",adminer()->dumpFormat(),$J["format"])."\n",(JUSH=="sqlite"?"":"<tr><th>".'Database'."<td>".html_select('db_style',$bc,$J["db_style"]).(support("type")?checkbox("types",1,$J["types"],'User types'):"").(support("routine")?checkbox("routines",1,$J["routines"],'Routines'):"").(support("event")?checkbox("events",1,$J["events"],'Events'):"")),"<tr><th>".'Tables'."<td>".html_select('table_style',$ck,$J["table_style"]).checkbox("auto_increment",1,$J["auto_increment"],'Auto Increment').(support("trigger")?checkbox("triggers",1,$J["triggers"],'Triggers'):""),"<tr><th>".'Data'."<td>".html_select('data_style',$Xb,$J["data_style"]),'</table>
<p><input type=\'submit\' value=\'Export\'>
',input_token(),'
<table',on('click','dumpClick'),'>
';$gi=array();if($_GET["ns"]===""){echo"<thead><tr><th style='text-align: left;'>","<label class='block'><input type='checkbox' id='check-schemas' checked class='jsonly' title='".'All'."'".on('click','formCheck','^schemas\[').">".'Schema'."</label>","<tbody>\n";foreach(adminer()->schemas()as$L){if(!information_schema(DB,$L))echo"<tr><td>".checkbox("schemas[]",$L,true,$L,"","block")."\n";}}elseif(DB!=""){$fb=($a!=""?"":" checked");echo"<thead><tr>","<th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables'$fb class='jsonly' title='".'All'."'".on('click','formCheck','^tables\[').">".'Table'."</label>","<th style='text-align: right;'><label class='block'>".'Data'."<input type='checkbox' id='check-data'$fb class='jsonly' title='".'All'."'".on('click','formCheck','^data\[')."></label>","<tbody>\n";$ul="";$ek=tables_list();foreach($ek
as$C=>$U){$fi=preg_replace('~_.*~','',$C);$fb=($a==""||$a==(substr($a,-1)=="%"?"$fi%":$C));$ki="<tr><td>".checkbox("tables[]",$C,$fb,$C,"","block");if($U!==null&&!preg_match('~table~i',$U))$ul
.="$ki\n";else
echo"$ki<td align='right'><label class='block'><span id='Rows-".h($C)."'></span>".checkbox("data[]",$C,$fb)."</label>\n";$gi[$fi]++;}echo$ul;if($ek)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{$i=adminer()->databases();echo"<thead><tr><th style='text-align: left;'>","<label class='block'>".($i?"<input type='checkbox' id='check-databases'".($a==""?" checked":"")." class='jsonly' title='".'All'."'".on('click','formCheck','^databases\[').">":"").'Database'."</label>","<tbody>\n";if($i){foreach($i
as$j){if(!information_schema($j)){$fi=preg_replace('~_.*~','',$j);echo"<tr><td>".checkbox("databases[]",$j,$a==""||$a=="$fi%",$j,"","block")."\n";$gi[$fi]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo'</table>
</form>
';$_d=true;foreach($gi
as$x=>$X){if($x!=""&&$X>1){echo($_d?"<p>":" ")."<a href='".h(ME)."dump=".url_escape("$x%")."'>".h($x)."</a>";$_d=false;}}}elseif(isset($_GET["privileges"])){page_header('Privileges');echo'<p class="links"><a href="'.h(ME).'user=">'.'Create user'."</a>";$H=connection()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$Vd=$H;if(!$H)$H=connection()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''><p>\n";hidden_fields_get();echo
input_hidden("db",DB),($Vd?"":input_hidden("grant")),"<table class='odds'>\n","<thead><tr><th>".'Username'."<th>".'Server'."<td class='hover'><tbody>\n";while($J=$H->fetch_assoc())echo'<tr><td>'.h($J["User"]),"<td>".h($J["Host"]),'<td class="hover"><a href="'.h(ME.'user='.url_escape($J["User"]).'&host='.url_escape($J["Host"])).'">'.'Edit'."</a>\n";if(!$Vd||DB!="")echo"<tr><td><input name='user' autocapitalize='off'><td><input name='host' value='localhost' autocapitalize='off'><td><input type='submit' value='".'Edit'."'>\n";echo"</table>\n","</form>\n";}elseif(isset($_GET["sql"])){if(!$l&&$_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers("sql");if($_POST["format"]=="sql")echo"$_POST[query]\n";else{adminer()->dumpTable("","");adminer()->dumpData("","table",$_POST["query"]);adminer()->dumpFooter();}exit;}restart_session();$qe=&get_session("queries");$pe=&$qe[DB];if(!$l&&$_POST["clear"]){$pe=array();redirect(remove_from_uri("history"));}stop_session();$oa=get_settings("adminer_import");if($_POST&&$oa)save_settings($oa,"adminer_import");page_header((isset($_GET["import"])?'Import':'SQL command'),$l);$Ef=driver()->lineComment();if(!$l&&$_POST&&!(isset($_GET["import"])&&adminer()->importProcess())){$jc=driver()->delimiter;$q=false;if(!isset($_GET["import"]))$G=$_POST["query"];elseif($_POST["webfile"]){$Dj=adminer()->importServerPath();$q=@fopen((file_exists($Dj)?$Dj:"compress.zlib://$Dj.gz"),"rb");$G=($q?fread($q,1e6):false);}else$G=get_file("sql_file",true,$jc);if(is_string($G)){if(($dg=ini_bytes("memory_limit"))!="-1")ini_set("memory_limit",max($dg,strval(2*strlen($G)+memory_get_usage()+8e6)));if($G!=""&&strlen($G)<1e6){$qi=$G.(preg_match("~$jc\\s*\$~",$G)?"":$jc);if(!$pe||first(end($pe))!=$qi){restart_session();$pe[]=array($qi,time());set_session("queries",$qe);stop_session();}}$Aj="(?:\\s|/\\*[\s\S]*?\\*/|(?:$Ef)[^\n]*\n?|--\r?\n)";$Rg=0;$Oc=true;$Lb=false;$g=connect();if($g&&DB!=""){$g->select_db(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$g);}$tb=0;$Vc=array();$Fh='[\'"'.(JUSH=="sql"?'`':(JUSH=="sqlite"?'`[':(JUSH=="mssql"?'[':''))).']|/\*|'.$Ef.'|$'.(JUSH=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$zk=microtime(true);while($G!=""){if(!$Rg&&preg_match("~^$Aj*+DELIMITER\\s+(\\S+)~i",$G,$B)){$jc=preg_quote($B[1]);$G=substr($G,strlen($B[0]));}elseif(!$Rg&&JUSH=='pgsql'&&preg_match("~^($Aj*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$G,$B)){$jc="\n\\\\\\.\r?\n";$Lb=true;$Rg=strlen($B[0]);}else{preg_match("($jc\\s*|$Fh)",$G,$B,PREG_OFFSET_CAPTURE,$Rg);list($Ld,$F)=$B[0];if(!$Ld&&$q&&!feof($q))$G
.=fread($q,1e5);else{if(!$Ld&&rtrim($G)=="")break;$Rg=$F+strlen($Ld);if($Ld&&!preg_match("(^$jc)",$Ld)){$Xa=driver()->hasCStyleEscapes()||(JUSH=="pgsql"&&($F>0&&strtolower($G[$F-1])=="e"));$Rh=($Ld=='/*'?'\*/':($Ld=='['?']':(preg_match("~^(?:$Ef)~",$Ld)?"\n":preg_quote($Ld).($Xa?'|\\\\.':''))));while(preg_match("($Rh|\$)s",$G,$B,PREG_OFFSET_CAPTURE,$Rg)){$Ui=$B[0][0];if(!$Ui&&$q&&!feof($q))$G
.=fread($q,1e5);else{$Rg=$B[0][1]+strlen($Ui);if(!$Ui||$Ui[0]!="\\")break;}}}else{$Oc=false;$qi=substr($G,0,$F+($Lb?3:0));$tb++;$ki="<pre id='sql-$tb'><code class='jush-".JUSH."'>".adminer()->sqlCommandQuery($qi)."</code></pre>\n";if(JUSH=="sqlite"&&preg_match("~^$Aj*+(ATTACH|VACUUM\\b.*\\bINTO)\\b~is",$qi,$B)!==0){echo$ki,"<p class='error'>".sprintf('%s queries are not supported.',preg_match('~ATTACH~i',$B[1])?'ATTACH':'VACUUM INTO')."\n";$Vc[]=" <a href='#sql-$tb'>$tb</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$ki;ob_flush();flush();}$Jj=microtime(true);if(connection()->multi_query($qi)&&$g&&preg_match("~^$Aj*+USE\\b~i",$qi))$g->query($qi);do{$H=connection()->store_result();if(connection()->error){echo($_POST["only_errors"]?$ki:""),"<p class='error'>".'Error in query'.(connection()->errno?" (".connection()->errno.")":"").": ".error()."\n";$Vc[]=" <a href='#sql-$tb'>$tb</a>";if($_POST["error_stops"])break
2;}else{$_=ME."sql=".url_escape(trim($qi));$ok=" <span class='time'>(".format_time($Jj).")</span>".(strlen($_)<1900?" <a href='".h($_)."'>".'Edit'."</a>":"");$qa=connection()->affected_rows;$xl=($_POST["only_errors"]?"":driver()->warnings());$yl="warnings-$tb";if($xl)$ok
.=", <a href='#$yl' class='toggle'>".'Warnings'."</a>";$ed=null;$ph=null;$fd="explain-$tb";if(is_object($H)){$z=$_POST["limit"];$Jg=$z;$ph=print_select_result($H,$g,array(),$Jg);if(!$_POST["only_errors"]){echo"<form action='' method='post'>\n";$Jg=max($H->num_rows,$Jg);echo"<p class='sql-footer'>".($Jg?($z&&$Jg>$z?sprintf('%d / ',$z):"").lang_format(array('%d row','%d rows'),$Jg):""),$ok;if($g&&preg_match("~^($Aj|\\()*+SELECT\\b~i",$qi)&&($ed=explain($g,$qi)))echo", <a href='#$fd' class='toggle'>Explain</a>";$t="export-$tb";echo", <a href='#$t' class='toggle'>".'Export'."</a><span id='$t' class='hidden'>: ".html_select("output",adminer()->dumpOutput(),$oa["output"])." ".html_select("format",adminer()->dumpFormat(),$oa["format"]).input_hidden("query",$qi)."<input type='submit' name='export' value='".'Export'."'".($z?"":on('click','sqlExport')).">".input_token()."</span>\n"."</form>\n";}}else{if(preg_match("~^$Aj*+(CREATE|DROP|ALTER)$Aj++(DATABASE|SCHEMA)\\b~i",$qi)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"])echo"<p class='message' title='".h(connection()->info)."'>".lang_format(array('Query executed OK, %d row affected.','Query executed OK, %d rows affected.'),$qa)."$ok\n";}echo($xl?"<div id='$yl' class='hidden'>\n$xl</div>\n":"");if($ed){echo"<div id='$fd' class='hidden explain'>\n";print_select_result($ed,$g,$ph);echo"</div>\n";}}$Jj=microtime(true);}while(connection()->next_result());}$G=substr($G,$Rg);$Rg=0;if($Lb){$jc=driver()->delimiter;$Lb=false;}}}}}if($Oc)echo"<p class='message'>".'No commands to execute.'."\n";else{$De=connection()->inTransaction();driver()->rollback();if($De)echo"<pre><code class='jush-".JUSH."'>ROLLBACK -- Adminer</code></pre>\n";if($_POST["only_errors"])echo"<p class='message'>".lang_format(array('%d query executed OK.','%d queries executed OK.'),$tb-count($Vc))," <span class='time'>(".format_time($zk).")</span>\n";elseif($Vc&&$tb>1)echo"<p class='error'>".'Error in query'.": ".implode("",$Vc)."\n";}}else
echo"<p class='error'>".upload_error($G)."\n";}echo'
<form action="" method="post" enctype="multipart/form-data" id="form"',(isset($_GET["import"])?"":on('submit','sqlSubmit',remove_from_uri("sql|limit|error_stops|only_errors|history"))),'>
';$cd="<input type='submit' value='".'Execute'."' title='Ctrl+Enter'>";if(!isset($_GET["import"])){$qi=$_GET["sql"];if($_POST)$qi=$_POST["query"];elseif($_GET["history"]=="all")$qi=$pe;elseif($_GET["history"]!="")$qi=idx($pe[$_GET["history"]],0);echo"<p>";textarea("query",$qi,20);echo($_POST?"":script("qs('textarea').focus();")),"<p>";adminer()->sqlPrintAfter();echo"$cd\n",'Limit rows'.": <input type='number' name='limit' class='size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{$be=(extension_loaded("zlib")?"[.gz]":"");echo"<fieldset><legend>".'File upload'."</legend><div>","SQL$be: ".file_input(" name='sql_file[]' multiple","\n$cd"),"</div></fieldset>\n";$Ae=adminer()->importServerPath();if($Ae)echo"<fieldset><legend>".'From server'."</legend><div>",sprintf('Webserver file %s',"<code>".h($Ae)."$be</code>")," <input type='submit' name='webfile' value='".'Run file'."'>","</div></fieldset>\n";adminer()->importPrint();echo"<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:isset($_GET["import"])||$_GET["error_stops"]),'Stop on error')."\n",checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),'Show only errors')."\n",input_token();if(!isset($_GET["import"])&&$pe){print_fieldset("history",'History',$_GET["history"]!="");for($X=end($pe);$X;$X=prev($pe)){$x=key($pe);list($qi,$ok,$Kc)=$X;echo'<div><a href="'.h(ME."sql=&history=$x").'" class="hover">'.'Edit'."</a>"." <span class='time' title='".@date('Y-m-d',$ok)."'>".@date("H:i:s",$ok)."</span>"." <code class='jush-".JUSH."'>".shorten_utf8(preg_replace('~\s+~',' ',ltrim(preg_replace("~^(?:$Ef).*~m",'',$qi))),80,"</code>").($Kc?" <span class='time'>($Kc)</span>":"")."</div>\n";}echo"<input type='submit' name='clear' value='".'Clear'."'>\n","<a href='".h(ME."sql=&history=all")."'>".'Edit all'."</a>\n","</div></fieldset>\n";}echo'</form>
';}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$n=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$n):""):where($_GET,$n));$Yk=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($n
as$C=>$m){if((!$Yk&&!isset($m["privileges"]["insert"]))||adminer()->fieldName($m)=="")unset($n[$C]);}if($_POST&&!$l&&!isset($_GET["select"])){$A=$_POST["referer"];if($_POST["insert"])$A=($Yk?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$A))$A=ME."select=".url_escape($a);$w=indexes($a);$Sk=unique_array($_GET["where"],$w);$ti="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($A,'Item has been deleted.',driver()->delete($a,$ti,$Sk?0:1));else{$O=array();foreach($n
as$C=>$m){$X=process_input($m);if($X!==false&&$X!==null)$O[idf_escape($C)]=$X;}if($Yk){if(!$O)redirect($A);queries_redirect($A,'Item has been updated.',driver()->update($a,$O,$ti,$Sk?0:1));if(is_ajax()){page_headers();page_messages($l);exit;}}else{$H=driver()->insert($a,$O);$wf=($H?last_id($H):0);queries_redirect($A,sprintf('Item%s has been inserted.',($wf?" $wf":"")),$H);}}}$J=null;if($Z){$M=array();foreach($n
as$C=>$m){if(isset($m["privileges"]["select"])){$Aa=($_POST["clone"]&&$m["auto_increment"]?"''":convert_field($m));$M[]=($Aa?"$Aa AS ":"").idf_escape($C);}}$J=array();if(!support("table"))$M=array("*");if($M){$H=driver()->select($a,$M,array($Z),$M,array(),(isset($_GET["select"])?2:1));if(!$H)$l=error();else{$J=$H->fetch_assoc();if(!$J)$J=false;}if(isset($_GET["select"])&&(!$J||$H->fetch_assoc()))$J=null;}}if(!$n&&driver()->primary!=""){if(!$Z){$H=driver()->select($a,array("*"),array(),array("*"));$J=($H?$H->fetch_assoc():false);if(!$J)$J=array(driver()->primary=>"");}if($J){foreach($J
as$x=>$X){if(!$Z)$J[$x]=null;$n[$x]=array("field"=>$x,"null"=>($x!=driver()->primary),"auto_increment"=>($x==driver()->primary));}}}if($_POST["save"]){$bi=array();foreach((array)$_POST["fields"]as$x=>$X)$bi[bracket_escape($x,true)]=$X;$J=$bi+($J?$J:array());}edit_form($a,$n,$J,$Yk,$l);}elseif(isset($_GET["create"])){$a=$_GET["create"];$Kh=driver()->partitionBy;$Oh=($Kh&&$a!=""?driver()->partitionsInfo($a):array());$_i=referencable_primary($a);$Jd=array();foreach($_i
as$Yj=>$m)$Jd[str_replace("`","``",$Yj)."`".str_replace("`","``",$m["field"])]=$Yj;$sh=array();$S=array();if($a!=""){$sh=fields($a);$S=table_status1($a);if(count($S)<2)$l='No tables.';}$J=$_POST;$J["fields"]=(array)$J["fields"];if($J["auto_increment_col"])$J["fields"][$J["auto_increment_col"]]["auto_increment"]=true;if($_POST&&!$l)save_settings(array("comments"=>$_POST["comments"],"defaults"=>$_POST["defaults"]));if($_POST&&!process_fields($J["fields"])&&!$l){if($_POST["drop"])queries_redirect(substr(ME,0,-1),'Table has been dropped.',drop_tables(array($a)));else{$n=array();$ua=array();$dl=false;$Hd=array();$rh=reset($sh);$sa=" FIRST";foreach($J["fields"]as$x=>$m){$p=$Jd[$m["type"]];$Mk=($p!==null?$_i[$p]:$m);if($m["field"]!=""){if(!$m["generated"])$m["default"]=null;$pi=process_field($m,$Mk);$ua[]=array($m["orig"],$pi,$sa);if(!$rh||$pi!==process_field($rh,$rh)){$n[]=array($m["orig"],$pi,$sa);if($m["orig"]!=""||$sa)$dl=true;}if($p!==null)$Hd[idf_escape($m["field"])]=($a!=""&&JUSH!="sqlite"?"ADD":" ").format_foreign_key(array('table'=>$Jd[$m["type"]],'source'=>array($m["field"]),'target'=>array($Mk["field"]),'on_delete'=>$m["on_delete"],));$sa=" AFTER ".idf_escape($m["field"]);}elseif($m["orig"]!=""){$dl=true;$n[]=array($m["orig"]);}if($m["orig"]!=""){$rh=next($sh);if(!$rh)$sa="";}}$Mh=array();if(in_array($J["partition_by"],$Kh)){foreach($J
as$x=>$X){if(preg_match('~^partition~',$x))$Mh[$x]=$X;}foreach($Mh["partition_names"]as$x=>$C){if($C==""){unset($Mh["partition_names"][$x]);unset($Mh["partition_values"][$x]);}}$Mh["partition_names"]=array_values($Mh["partition_names"]);$Mh["partition_values"]=array_values($Mh["partition_values"]);if($Mh==$Oh)$Mh=array();}elseif(preg_match("~partitioned~",$S["Create_options"]))$Mh=null;$fg='Table has been altered.';if($a==""){cookie("adminer_engine",$J["Engine"]);$fg='Table has been created.';}$C=trim($J["name"]);$A=ME.(support("table")?"table=":"select=").url_escape($C);$H=alter_table($a,$C,(JUSH=="sqlite"&&($dl||$Hd)?$ua:$n),$Hd,($J["Comment"]!=$S["Comment"]?$J["Comment"]:null),($J["Engine"]&&$J["Engine"]!=$S["Engine"]?$J["Engine"]:""),($J["Collation"]&&$J["Collation"]!=$S["Collation"]?$J["Collation"]:""),($J["Auto_increment"]!=""?number($J["Auto_increment"]):""),$Mh);if($H&&!Queries::$queries)redirect($A);queries_redirect($A,$fg,$H);}}page_header(($a!=""?'Alter table':'Create table'),$l,array("table"=>$a),h($a));if(!$_POST){$Ok=driver()->types();$J=array("Engine"=>$_COOKIE["adminer_engine"],"fields"=>array(array("field"=>"","type"=>(isset($Ok["int"])?"int":(isset($Ok["integer"])?"integer":"")),"on_update"=>"")),"partition_names"=>array(""),);if($a!=""){$J=$S;$J["name"]=$a;$J["fields"]=array();if(!$_GET["auto_increment"])$J["Auto_increment"]="";foreach($sh
as$m){if($m["generated"])$m["default"]=ltrim($m["default"]);$m["generated"]=$m["generated"]?:(isset($m["default"])?"DEFAULT":"");$J["fields"][]=$m;}if($Kh){$J+=$Oh;$J["partition_names"][]="";$J["partition_values"][]="";}}}$qb=collations();if(is_array(reset($qb)))$qb=call_user_func_array('array_merge',array_values($qb));$Qc=driver()->engines();foreach($Qc
as$Pc){if(!strcasecmp($Pc,$J["Engine"])){$J["Engine"]=$Pc;break;}}$Rf=max_input_vars(12,20);if($Rf){$oe=(count($J["fields"])>$Rf?"":" hidden");echo"<p".($oe?" id='max-fields' data-columns='$Rf'":"")." class='error$oe'>".max_input_vars_error()."\n";}echo'
<form action="" method="post" id="form">
<p>
';if(support("columns")||$a==""){echo'Table name'.": <input name='name'".($a==""&&!$_POST?" autofocus":"")." data-maxlength='64' value='".h($J["name"])."' autocapitalize='off'>\n",($Qc?html_select("Engine",array(""=>"(".'engine'.")")+$Qc,$J["Engine"],on('change','helpClose').on_help_value())."\n":"");if($qb)echo"<datalist id='collations'>".optionlist($qb)."</datalist>\n",(preg_match("~sqlite|mssql~",JUSH)?"":"<input list='collations' name='Collation' value='".h($J["Collation"])."' placeholder='(".'collation'.")'>\n");echo"<input type='submit' value='".'Save'."'>\n";}if(support("columns")){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($J["fields"],$qb,"TABLE",$Jd);echo"</table>\n",script("editFields();"),"</div>\n<p>\n",'Auto Increment'.": <input type='number' name='Auto_increment' class='size' value='".h($J["Auto_increment"])."'>\n",checkbox("defaults",1,($_POST?$_POST["defaults"]:get_setting("defaults")),'Default values',on('click','columnShowClick',5),"jsonly");$wb=($_POST?$_POST["comments"]:get_setting("comments"));if(support("comment")){echo
checkbox("comments",1,$wb,'Comment',on('click','editingCommentsClick',true),"jsonly").' ';$c=" name='Comment' data-maxlength='".(min_version(5.5)?2048:60)."'".($wb?"":" class='hidden'");echo
adminer()->commentInput('TABLE',$c,$J["Comment"]);}echo'<p>
<input type=\'submit\' value=\'Save\'>
';}echo'
';if($a!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$a)),'>
';if($Kh&&(JUSH=='sql'||$a=="")){$Lh=preg_match('~RANGE|LIST~',$J["partition_by"]);print_fieldset("partition",'Partition by',$J["partition_by"]);echo"<p>".html_select("partition_by",array_merge(array(""),$Kh),$J["partition_by"],on('change','partitionByChange').on_help_value('.','PARTITION BY $&'))."\n","(<input name='partition' value='".h($J["partition"])."'>)\n",'Partitions'.": <input type='number' name='partitions' class='size".($Lh||!$J["partition_by"]?" hidden":"")."' value='".h($J["partitions"])."'>\n","<table id='partition-table'".($Lh?"":" class='hidden'").">\n","<thead><tr><th>".'Partition name'."<th>".'Values'."<tbody>\n";foreach($J["partition_names"]as$x=>$X)echo'<tr>','<td><input name="partition_names[]" value="'.h($X).'" autocapitalize="off"'.($x==count($J["partition_names"])-1?on('input','partitionNameChange'):'').'>','<td><input name="partition_values[]" value="'.h(idx($J["partition_values"],$x)).'">';echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$Je=array("PRIMARY","UNIQUE","INDEX");$S=table_status1($a,true);$Ge=driver()->indexAlgorithms($S);if(preg_match('~MyISAM|M?aria'.(min_version(5.6,'10.0.5')?'|InnoDB':'').'~i',$S["Engine"]))$Je[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.(min_version(5.7,'10.2.2')?'|InnoDB':'').'~i',$S["Engine"]))$Je[]="SPATIAL";if(min_version('',11.7)&&preg_match('~MyISAM|InnoDB~i',$S["Engine"]))$Je[]="VECTOR";$w=indexes($a);$n=fields($a);$ii=array();if(JUSH=="mongo"){$ii=$w["_id_"];unset($Je[0]);unset($w["_id_"]);}$J=$_POST;if($J)save_settings(array("index_options"=>$J["options"]));if($_POST&&!$l&&!$_POST["add"]&&!$_POST["drop_col"]){$b=array();foreach($J["indexes"]as$v){$C=$v["name"];if(in_array($v["type"],$Je)){$e=array();$Cf=array();$mc=array();$eh=array();$He=(support("partial_indexes")?$v["partial"]:"");$Fe=(in_array($v["algorithm"],$Ge)?$v["algorithm"]:"");$O=array();ksort($v["columns"]);foreach($v["columns"]as$x=>$d){if($d!=""){$y=idx($v["lengths"],$x);$kc=idx($v["descs"],$x);$dh=idx($v["opclasses"],$x);$O[]=($n[$d]?idf_escape($d):$d).($y?"(".(+$y).")":"").($dh!=""?" ".idf_escape($dh):"").($kc?" DESC":"");$e[]=$d;$Cf[]=($y?:null);$mc[]=$kc;$eh[]="$dh";}}$dd=$w[$C];if($dd){ksort($dd["columns"]);ksort($dd["lengths"]);ksort($dd["descs"]);if($v["type"]==$dd["type"]&&array_values($dd["columns"])===$e&&(!$dd["lengths"]||array_values($dd["lengths"])===$Cf)&&array_values($dd["descs"])===$mc&&(!$dd["opclasses"]||array_values($dd["opclasses"])===$eh)&&$dd["partial"]==$He&&(!$Ge||$dd["algorithm"]==$Fe)){unset($w[$C]);continue;}}if($e)$b[]=array($v["type"],$C,$O,$Fe,$He);}}foreach($w
as$C=>$dd)$b[]=array($dd["type"],$C,"DROP");if(!$b)redirect(ME."table=".url_escape($a));queries_redirect(ME."table=".url_escape($a),'Indexes have been altered.',alter_indexes($a,$b));}page_header('Indexes',$l,array("table"=>$a),h($a));$td=array_keys($n);if($_POST["add"]){foreach($J["indexes"]as$x=>$v){if($v["columns"][count($v["columns"])]!="")$J["indexes"][$x]["columns"][]="";}$v=end($J["indexes"]);if($v["type"]||array_filter($v["columns"],'strlen'))$J["indexes"][]=array("columns"=>array(1=>""));}if(!$J){foreach($w
as$x=>$v){$w[$x]["name"]=$x;$w[$x]["columns"][]="";}$w[]=array("columns"=>array(1=>""));$J["indexes"]=$w;}$Cf=(JUSH=="sql"||JUSH=="mssql");$eh=driver()->indexOpclasses();$sj=($_POST?$_POST["options"]:get_setting("index_options"));echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap odds">
<thead><tr>
<th id="label-type">Index Type
';$ze=" class='idxopts".($sj?"":" hidden")."'";if($Ge)echo"<th id='label-algorithm'$ze>".'Algorithm'.doc_link(array('sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'storage-engine-index-types/','pgsql'=>'indexes-types.html',));echo'<th><input type="submit" hidden>','Columns'.($Cf?"<span$ze> (".'length'.")</span>":"");if($Cf||support("descidx"))echo
checkbox("options",1,$sj,'Options',on('click','indexOptionsShow'),"jsonly")."\n";echo'<th id="label-name">Name
';if(support("partial_indexes"))echo"<th id='label-condition'$ze>".'Condition';echo'<th><noscript>',icon("plus","add[0]","+",'Add next'),'</noscript>
<tbody>
';if($ii){echo"<tr><td>PRIMARY<td>";foreach($ii["columns"]as$x=>$d)echo
select_input(" disabled",array_combine($td,$td),$d),"<label><input disabled type='checkbox'>".'descending'."</label> ";echo"<td><td>\n";}$gf=1;foreach($J["indexes"]as$v){if(!$_POST["drop_col"]||$gf!=key($_POST["drop_col"])){echo"<tr><td>".html_select("indexes[$gf][type]",array(-1=>"")+$Je,$v["type"],($gf==count($J["indexes"])?on('change','indexesAddRow'):""),"label-type");if($Ge)echo"<td$ze>".html_select("indexes[$gf][algorithm]",array_merge(array(""),$Ge),$v['algorithm'],"","label-algorithm");echo"<td>";ksort($v["columns"]);$s=1;foreach($v["columns"]as$x=>$d){echo"<span>".select_input(" name='indexes[$gf][columns][$s]' title='".'Column'."'".on('change','indexesChangeColumn',(JUSH=="sql"?"":$_GET["indexes"]."_")),($n&&($d==""||$n[$d])?array_combine($td,$td):array()),$d)," <span$ze>",($Cf?"<input type='number' name='indexes[$gf][lengths][$s]' class='size' value='".h(idx($v["lengths"],$x))."' title='".'Length'."'>":"");if($eh){$dh=idx($v["opclasses"],$x);echo
html_select("indexes[$gf][opclasses][$s]",array(""=>"(".'operator class'.")")+array_combine($eh,$eh)+($dh!=""?array($dh=>$dh):array()),$dh),doc_link(array('pgsql'=>'indexes-opclass.html'));}echo(support("descidx")?checkbox("indexes[$gf][descs][$s]",1,idx($v["descs"],$x),'descending'):""),"<br>","</span></span>";$s++;}echo"<td><input name='indexes[$gf][name]' value='".h($v["name"])."' autocapitalize='off' aria-labelledby='label-name'>\n";if(support("partial_indexes"))echo"<td$ze><input name='indexes[$gf][partial]' value='".h($v["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>".icon("cross","drop_col[$gf]","x",'Remove',on('click','editingRemoveRow','indexes$1[type]'));}$gf++;}echo'</table>
</div>
<p>
<input type=\'submit\' value=\'Save\'>
',input_token(),'</form>
';}elseif(isset($_GET["database"])){$J=$_POST;if($_POST&&!$l&&!$_POST["add"]){$C=trim($J["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),'Database has been dropped.',drop_databases(array(DB)));}elseif(DB!==$C){if(DB!=""){$_GET["db"]=$C;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".url_escape($C),'Database has been renamed.',rename_database($C,(string)$J["collation"]));}else{$i=explode("\n",str_replace("\r","",$C));$Oj=true;$uf="";foreach($i
as$j){if(count($i)==1||$j!=""){if(!create_database($j,(string)$J["collation"]))$Oj=false;$uf=$j;}}restart_session();set_session("dbs",null);queries_redirect(ME."db=".url_escape($uf),'Database has been created.',$Oj);}}else{if(!$J["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($C).(preg_match('~^[a-z0-9_]+$~i',$J["collation"])?" COLLATE $J[collation]":""),substr(ME,0,-1),'Database has been altered.');}}page_header(DB!=""?'Alter database':'Create database',$l,array(),h(DB));$qb=collations();$C=DB;if($_POST)$C=$J["name"];elseif(DB!="")$J["collation"]=db_collation(DB,$qb);elseif(JUSH=="sql"){foreach(get_vals("SHOW GRANTS")as$Vd){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$Vd,$B)&&$B[1]){$C=stripcslashes(idf_unescape("`$B[2]`"));break;}}}echo'
<form action="" method="post">
<p>
',($_POST["add"]||strpos($C,"\n")?'<textarea autofocus name="name" rows="10" cols="40">'.h($C).'</textarea><br>':'<input name="name" autofocus value="'.h($C).'" data-maxlength="64" autocapitalize="off">')."\n",($qb?html_select("collation",array(""=>"(".'collation'.")")+$qb,$J["collation"]).doc_link(array('sql'=>"charset-charsets.html",'mariadb'=>"supported-character-sets-and-collations/",'mssql'=>"relational-databases/system-functions/sys-fn-helpcollations-transact-sql",)):"")."\n",'<input type=\'submit\' value=\'Save\'>
';if(DB!="")echo"<input type='submit' name='drop' value='".'Drop'."'".confirm(sprintf('Drop %s?',DB)).">\n";elseif(!$_POST["add"]&&$_GET["db"]=="")echo
icon("plus","add[0]","+",'Add next')."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["scheme"])){$J=$_POST;if($_POST&&!$l){$_=preg_replace('~ns=[^&]*&~','',ME)."ns=";if($_POST["drop"])query_redirect("DROP SCHEMA ".idf_escape($_GET["ns"]),$_,'Schema has been dropped.');else{$C=trim($J["name"]);$_
.=url_escape($C);if($_GET["ns"]=="")query_redirect("CREATE SCHEMA ".idf_escape($C),$_,'Schema has been created.');elseif($_GET["ns"]!=$C)query_redirect("ALTER SCHEMA ".idf_escape($_GET["ns"])." RENAME TO ".idf_escape($C),$_,'Schema has been altered.');else
redirect($_);}}page_header($_GET["ns"]!=""?'Alter schema':'Create schema',$l);if(!$J)$J["name"]=$_GET["ns"];echo'
<form action="" method="post">
<p><input name="name" autofocus value="',h($J["name"]),'" autocapitalize="off">
<input type=\'submit\' value=\'Save\'>
';if($_GET["ns"]!="")echo"<input type='submit' name='drop' value='".'Drop'."'".confirm(sprintf('Drop %s?',$_GET["ns"])).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["call"])){$ba=($_GET["name"]?:$_GET["call"]);page_header('Call'.": ".h($ba),$l);$Ri=(isset($_GET["callf"])?"FUNCTION":"PROCEDURE");$Pi=routine($_GET["call"],$Ri);$Be=array();$yh=array();foreach($Pi["fields"]as$s=>$m){if(substr($m["inout"],-3)=="OUT"&&JUSH=='sql')$yh[$s]="@".idf_escape($m["field"])." AS ".idf_escape($m["field"]);if(!$m["inout"]||substr($m["inout"],0,2)=="IN")$Be[]=$s;}if(!$l&&$_POST){$Ya=array();foreach($Pi["fields"]as$x=>$m){$X="";if(in_array($x,$Be)){$X=process_input($m);if($X===false)$X="''";if(isset($yh[$x]))connection()->query("SET @".idf_escape($m["field"])." = $X");}if(isset($yh[$x]))$Ya[]="@".idf_escape($m["field"]);elseif(in_array($x,$Be))$Ya[]=$X;}$G=(isset($_GET["callf"])?"SELECT ":"CALL ").(idx($Pi["returns"],"type")=="record"?"* FROM ":"").table($ba)."(".implode(", ",$Ya).")";$Jj=microtime(true);$H=connection()->multi_query($G);$qa=connection()->affected_rows;echo
adminer()->selectQuery($G,$Jj,!$H);if(!$H)echo"<p class='error'>".error()."\n";else{$g=connect();if($g)$g->select_db(DB);do{$H=connection()->store_result();if(is_object($H))print_select_result($H,$g);else
echo"<p class='message'>".lang_format(array('Routine has been called, %d row affected.','Routine has been called, %d rows affected.'),$qa)." <span class='time'>".@date("H:i:s")."</span>\n";}while(connection()->next_result());if($yh)print_select_result(connection()->query("SELECT ".implode(", ",$yh)));}}echo'
<form action="" method="post">
';if($Be){echo"<table class='layout'>\n";foreach($Be
as$x){$m=$Pi["fields"][$x];$C=$m["field"];echo"<tr><th>".adminer()->fieldName($m);$Y=idx($_POST["fields"],$C);if($Y!=""){if($m["type"]=="set")$Y=implode(",",$Y);}input($m,$Y,idx($_POST["function"],$C,""));echo"\n";}echo"</table>\n";}echo'<p>
<input type=\'submit\' value=\'Call\'>
',input_token(),'</form>

',adminer()->commentValue($Ri,$Pi['comment']);}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$C=$_GET["name"];$J=$_POST;if($_POST&&!$l&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$J["source"]=array_filter($J["source"],'strlen');ksort($J["source"]);$hk=array();foreach($J["source"]as$x=>$X)$hk[$x]=$J["target"][$x];$J["target"]=$hk;}if(JUSH=="sqlite")$H=recreate_table($a,$a,array(),array(),array(" $C"=>($J["drop"]?"":" ".format_foreign_key($J))));else{$b="ALTER TABLE ".table($a);$H=($C==""||queries("$b DROP ".(JUSH=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($C)));if(!$J["drop"])$H=queries("$b ADD".format_foreign_key($J));}queries_redirect(ME."table=".url_escape($a),($J["drop"]?'Foreign key has been dropped.':($C!=""?'Foreign key has been altered.':'Foreign key has been created.')),$H);if(!$J["drop"])$l='Source and target columns must have the same data type, there must be an index on the target columns and referenced data must exist.';}page_header(($C!=""?'Alter foreign key':'Create foreign key'),$l,array("table"=>$a),h($C!=""?$C:$a));if($_POST){ksort($J["source"]);if($_POST["change"]||$_POST["change-js"])$J["target"]=array();else$J["source"][]="";}elseif($C!=""){$Jd=foreign_keys($a);$J=$Jd[$C];$J["source"][]="";}else{$J["table"]=$a;$J["source"]=array("");}echo'
<form action="" method="post">
';$_j=array_keys(fields($a));if($J["db"]!="")connection()->select_db($J["db"]);if($J["ns"]!=""){$th=get_schema();set_schema($J["ns"]);}$zi=array_keys(array_filter(table_status('',true),'Adminer\fk_support'));$hk=array_keys(fields(in_array($J["table"],$zi)?$J["table"]:reset($zi)));$c=on('change','foreignChange');echo"<p><label>".'Target table'.": ".html_select("table",$zi,$J["table"],$c)."</label>\n";if(support("scheme")){$Wi=array_filter(adminer()->schemas(),function($L){return!information_schema(DB,$L);});echo"<label>".'Schema'.": ".html_select("ns",$Wi,$J["ns"]!=""?$J["ns"]:$_GET["ns"],$c)."</label>";if($J["ns"]!="")set_schema($th);}elseif(JUSH!="sqlite"){$cc=array();foreach(adminer()->databases()as$j){if(!information_schema($j))$cc[]=$j;}echo"<label>".'DB'.": ".html_select("db",$cc,$J["db"]!=""?$J["db"]:$_GET["db"],$c)."</label>";}echo
input_hidden("change-js"),'<noscript><p><input type=\'submit\' name=\'change\' value=\'Change\'></noscript>
<table>
<thead><tr><th id="label-source">Source<th id="label-target">Target<tbody>
';$gf=0;foreach($J["source"]as$x=>$X){echo"<tr>","<td>".html_select("source[".(+$x)."]",array(-1=>"")+$_j,$X,($gf==count($J["source"])-1?on('change','foreignAddRow'):""),"label-source"),"<td>".html_select("target[".(+$x)."]",$hk,idx($J["target"],$x),"","label-target");$gf++;}echo'</table>
<p>
<label>ON DELETE: ',html_select("on_delete",array(-1=>"")+explode("|",driver()->onActions),$J["on_delete"]),'</label>
<label>ON UPDATE: ',html_select("on_update",array(-1=>"")+explode("|",driver()->onActions),$J["on_update"]),'</label>
',(support("deferrable")?html_select("deferrable",array('NOT DEFERRABLE','DEFERRABLE','DEFERRABLE INITIALLY DEFERRED'),$J["deferrable"]).' ':''),doc_link(array('sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"foreign-keys/",'pgsql'=>"sql-createtable.html#SQL-CREATETABLE-PARMS-REFERENCES",'mssql'=>"t-sql/statements/create-table-transact-sql",'oracle'=>"SQLRF01111",)),'<p>
<input type=\'submit\' value=\'Save\'>
<noscript><p><input type=\'submit\' name=\'add\' value=\'Add column\'></noscript>
';if($C!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$C)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["view"])){$a=$_GET["view"];$J=$_POST;$uh="VIEW";if(JUSH=="pgsql"&&$a!=""){$P=table_status1($a);$uh=strtoupper($P["Engine"]);}if($_POST&&!$l){$C=trim($J["name"]);$Aa=" AS\n$J[select]";$A=ME."table=".url_escape($C);$fg='View has been altered.';$U=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$C&&JUSH!="sqlite"&&$U=="VIEW"&&$uh=="VIEW")query_redirect((JUSH=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($C).$Aa,$A,$fg);else{$jk="adminer_".uniqid();drop_create("DROP $uh ".table($a),"CREATE $U ".table($C).$Aa,"DROP $U ".table($C),"CREATE $U ".table($jk).$Aa,"DROP $U ".table($jk),($_POST["drop"]?substr(ME,0,-1):$A),'View has been dropped.',$fg,'View has been created.',$a,$C);}}if(!$_POST&&$a!=""){$J=view($a);$J["name"]=$a;$J["materialized"]=($uh!="VIEW");if(!$l)$l=error();}page_header(($a!=""?'Alter view':'Create view'),$l,array("table"=>$a),h($a));echo'
<form action="" method="post">
<p>Name: <input name="name" value="',h($J["name"]),'" data-maxlength="64" autocapitalize="off">
',(support("materializedview")?" ".checkbox("materialized",1,$J["materialized"],'Materialized view'):""),'<p>';textarea("select",$J["select"]);echo'<p>
<input type=\'submit\' value=\'Save\'>
';if($a!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$a)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["event"])){$aa=$_GET["event"];$We=array("YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND");$Kj=array("ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE");$J=$_POST;if($_POST&&!$l){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($aa),substr(ME,0,-1),'Event has been dropped.');elseif(in_array($J["INTERVAL_FIELD"],$We)&&isset($Kj[$J["STATUS"]])){$Vi="\nON SCHEDULE ".($J["INTERVAL_VALUE"]?"EVERY ".q($J["INTERVAL_VALUE"])." $J[INTERVAL_FIELD]".($J["STARTS"]?" STARTS ".q($J["STARTS"]):"").($J["ENDS"]?" ENDS ".q($J["ENDS"]):""):"AT ".q($J["STARTS"]))." ON COMPLETION".($J["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($aa!=""?'Event has been altered.':'Event has been created.'),queries(($aa!=""?"ALTER EVENT ".idf_escape($aa).$Vi.($aa!=$J["EVENT_NAME"]?"\nRENAME TO ".idf_escape($J["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($J["EVENT_NAME"]).$Vi)."\n".$Kj[$J["STATUS"]]." COMMENT ".q($J["EVENT_COMMENT"]).rtrim(" DO\n$J[EVENT_DEFINITION]",";").";"));}}page_header(($aa!=""?'Alter event'.": ".h($aa):'Create event'),$l);if(!$J&&$aa!=""){$K=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($aa));$J=reset($K);}echo'
<form action="" method="post">
<table class="layout">
<tr><th>Name<td><input name="EVENT_NAME" value="',h($J["EVENT_NAME"]),'" data-maxlength="64" autocapitalize="off">
<tr><th title="datetime">Start<td><input name="STARTS" value="',h("$J[EXECUTE_AT]$J[STARTS]"),'">
<tr><th title="datetime">End<td><input name="ENDS" value="',h($J["ENDS"]),'">
<tr><th>Every
<td><input type="number" name="INTERVAL_VALUE" value="',h($J["INTERVAL_VALUE"]),'" class="size"> ',html_select("INTERVAL_FIELD",$We,$J["INTERVAL_FIELD"]),'<tr><th>Status<td>',html_select("STATUS",$Kj,$J["STATUS"]),'<tr><th>Comment<td><input name="EVENT_COMMENT" value="',h($J["EVENT_COMMENT"]),'" data-maxlength="64">
<tr><th><td>',checkbox("ON_COMPLETION","PRESERVE",$J["ON_COMPLETION"]=="PRESERVE",'On completion preserve'),'</table>
<p>';textarea("EVENT_DEFINITION",$J["EVENT_DEFINITION"]);echo'<p>
<input type=\'submit\' value=\'Save\'>
';if($aa!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$aa)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["procedure"])){$ba=($_GET["name"]?:$_GET["procedure"]);$Pi=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$J=$_POST;$J["fields"]=(array)$J["fields"];if($_POST&&!process_fields($J["fields"])&&!$l){foreach($J["fields"]as$x=>$m){if($m["field"]=="")unset($J["fields"][$x]);}$Vg=routine_id($ba,routine($_GET["procedure"],$Pi));$_g=routine_id($J["name"],$J);$h=create_routine($Pi,$J);$A=substr(ME,0,-1);$fg='Routine has been altered.';if(!$_POST["drop"]&&$Vg==$_g&&connection()->flavor!="mysql")query_redirect(substr_replace($h,' OR REPLACE',6,0),$A,$fg);else{$jk="adminer_".uniqid();drop_create("DROP $Pi $Vg",$h,"DROP $Pi $_g",create_routine($Pi,array("name"=>$jk)+$J),"DROP $Pi ".routine_id($jk,$J),$A,'Routine has been dropped.',$fg,'Routine has been created.',$ba,$J["name"]);}}page_header(($ba!=""?(isset($_GET["function"])?'Alter function':'Alter procedure').": ".h($ba):(isset($_GET["function"])?'Create function':'Create procedure')),$l);if(!$_POST){if($ba=="")$J["language"]="sql";else{$J=routine($_GET["procedure"],$Pi);$J["name"]=$ba;}}$qb=get_vals("SHOW CHARACTER SET");sort($qb);$Qi=routine_languages();echo($qb?"<datalist id='collations'>".optionlist($qb)."</datalist>":""),'
<form action="" method="post" id="form">
<p>Name: <input name="name" value="',h($J["name"]),'" data-maxlength="64" autocapitalize="off">
',($Qi?"<label>".'Language'.": ".html_select("language",$Qi,$J["language"])."</label>\n":""),'<input type=\'submit\' value=\'Save\'>
<div class="scrollable">
<table id="edit-fields" class="nowrap">
';edit_fields($J["fields"],$qb,$Pi);if(isset($_GET["function"])){echo"<tr><td>".'Return type';edit_type("returns",(array)$J["returns"],$qb,array(),(JUSH=="pgsql"?array("void","trigger"):array()));}echo'</table>
',script("editFields();"),'</div>
<p>';textarea("definition",$J["definition"],20);echo'<p>
<input type=\'submit\' value=\'Save\'>
';if($ba!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$ba)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["sequence"])){$da=$_GET["sequence"];$J=$_POST;if($_POST&&!$l){$_=substr(ME,0,-1);$C=trim($J["name"]);if($_POST["drop"])query_redirect("DROP SEQUENCE ".idf_escape($da),$_,'Sequence has been dropped.');elseif($da=="")query_redirect("CREATE SEQUENCE ".idf_escape($C),$_,'Sequence has been created.');elseif($da!=$C)query_redirect("ALTER SEQUENCE ".idf_escape($da)." RENAME TO ".idf_escape($C),$_,'Sequence has been altered.');else
redirect($_);}page_header($da!=""?'Alter sequence'.": ".h($da):'Create sequence',$l);if(!$J)$J["name"]=$da;echo'
<form action="" method="post">
<p><input name="name" value="',h($J["name"]),'" autocapitalize="off">
<input type=\'submit\' value=\'Save\'>
';if($da!="")echo"<input type='submit' name='drop' value='".'Drop'."'".confirm(sprintf('Drop %s?',$da)).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["type"])){function
enum_values($hc){$Y="'(?:[^']|'')*'";if(!preg_match('~^AS\s+ENUM\s*\(\s*('.$Y.'(?:\s*,\s*'.$Y.')*)\s*\)$~i',$hc,$B))return
null;preg_match_all('~'.$Y.'~',$B[1],$Of);return$Of[0];}function
add_enum_values($U,$Tg,$yg){$Yg=enum_values($Tg);$Dg=enum_values($yg);if($Yg===null||$Dg===null)return
null;$I=array();$s=0;foreach($Dg
as$Y){if($Y===idx($Yg,$s))$s++;else$I[]="ALTER TYPE ".idf_escape($U)." ADD VALUE $Y".($s<count($Yg)?" BEFORE ".$Yg[$s]:"");}return($s==count($Yg)?$I:null);}$ea=$_GET["type"];$J=$_POST;$U=($ea!=""?type_definition(+array_search($ea,types(true))):array());$Lg=($U["kind"]=='d'?"DOMAIN":"TYPE");if($_POST&&!$l){$_=substr(ME,0,-1);$C=trim($J["name"]);$Aa=trim($J["as"]);$Bg=(preg_match('~^AS\s+(?!ENUM\b|RANGE\b|\()~i',$Aa)?"DOMAIN":"TYPE");$fg='Type has been altered.';$b=(!$_POST["drop"]&&$ea!=""&&$Bg==$Lg?($Aa==$U["definition"]?array():add_enum_values($ea,$U["definition"],$Aa)):null);if($b!==null){if($ea!=$C)$b[]="ALTER $Lg ".idf_escape($ea)." RENAME TO ".idf_escape($C);if(!$b)redirect($_);$md=false;foreach($b
as$G){if(!queries($G)){$md=true;break;}}queries_redirect($_,$fg,!$md);}else
drop_create("DROP $Lg ".idf_escape($ea),"CREATE $Bg ".idf_escape($C)." $Aa","","","",$_,'Type has been dropped.',$fg,'Type has been created.',$ea,$C);}page_header($ea!=""?'Alter type'.": ".h($ea):'Create type',$l);if(!$J){$J["name"]=$ea;$J["as"]=($ea!=""?$U["definition"]:"AS ");}echo'
<form action="" method="post">
<p>
','Name'.": <input name='name' value='".h($J['name'])."' autocapitalize='off'>\n",doc_link(array('pgsql'=>"sql-createtype.html",),"?");textarea("as",$J["as"]);echo"<p><input type='submit' value='".'Save'."'>\n";if($ea!="")echo"<input type='submit' name='drop' value='".'Drop'."'".confirm(sprintf('Drop %s?',$ea)).">\n";echo
input_token(),'</form>
';}elseif(isset($_GET["check"])){$a=$_GET["check"];$C=$_GET["name"];$J=$_POST;if($J&&!$l){if(JUSH=="sqlite")$H=recreate_table($a,$a,array(),array(),array(),"",array(),"$C",($J["drop"]?"":$J["clause"]));else{$H=($C==""||queries("ALTER TABLE ".table($a)." DROP CONSTRAINT ".idf_escape($C)));if(!$J["drop"])$H=queries("ALTER TABLE ".table($a)." ADD".($J["name"]!=""?" CONSTRAINT ".idf_escape($J["name"]):"")." CHECK ($J[clause])");}queries_redirect(ME."table=".url_escape($a),($J["drop"]?'Check has been dropped.':($C!=""?'Check has been altered.':'Check has been created.')),$H);}page_header(($C!=""?'Alter check':'Create check'),$l,array("table"=>$a),h($C!=""?$C:$a));if(!$J){$gb=driver()->checkConstraints($a);$J=array("name"=>$C,"clause"=>$gb[$C]);}echo'
<form action="" method="post">
<p>';if(JUSH!="sqlite")echo'Name'.': <input name="name" value="'.h($J["name"]).'" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(array('sql'=>"create-table-check-constraints.html",'mariadb'=>"constraint/",'pgsql'=>"ddl-constraints.html#DDL-CONSTRAINTS-CHECK-CONSTRAINTS",'mssql'=>"relational-databases/tables/create-check-constraints",'sqlite'=>"lang_createtable.html#check_constraints",),"?"),'<p>';textarea("clause",$J["clause"]);echo'<p><input type=\'submit\' value=\'Save\'>
';if($C!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$C)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$C="$_GET[name]";$Ik=trigger_options();$J=(array)trigger($C,$a)+array("Trigger"=>$a."_bi");if($_POST){if(!$l&&in_array($_POST["Timing"],$Ik["Timing"])&&in_array($_POST["Event"],$Ik["Event"])&&in_array($_POST["Type"],$Ik["Type"])){$Zg=" ON ".table($a);$Bc="DROP TRIGGER ".idf_escape($C).(JUSH=="pgsql"?$Zg:"");$A=ME."table=".url_escape($a);if($_POST["drop"])query_redirect($Bc,$A,'Trigger has been dropped.');else{if($C!="")queries($Bc);queries_redirect($A,($C!=""?'Trigger has been altered.':'Trigger has been created.'),queries(create_trigger($Zg,$_POST)));if($C!="")queries(create_trigger($Zg,$J+array("Type"=>reset($Ik["Type"]))));}}$J=$_POST;}page_header(($C!=""?'Alter trigger':'Create trigger'),$l,array("table"=>$a),h($C!=""?$C:$a));$Gk=on('change','triggerChange',"^".preg_quote($a,"/")."_[ba][iud]$",$a);echo'
<form action="" method="post" id="form">
<table class="layout">
<tr><th>Time
<td>',html_select("Timing",$Ik["Timing"],$J["Timing"],$Gk),'<tr><th>Event<td>',html_select("Event",$Ik["Event"],$J["Event"],$Gk),(in_array("UPDATE OF",$Ik["Event"])?" <input name='Of' value='".h($J["Of"])."' class='hidden'>":""),'<tr><th>Type<td>',html_select("Type",$Ik["Type"],$J["Type"]),'</table>
<p>Name: <input name="Trigger" value="',h($J["Trigger"]),'" data-maxlength="64" autocapitalize="off">
',script("fire(qs('#form')['Timing'], 'change');"),'<p>';textarea("Statement",$J["Statement"]);echo'<p>
<input type=\'submit\' value=\'Save\'>
';if($C!="")echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',$C)),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["user"])){$fa=$_GET["user"];$ni=array(""=>array("All privileges"=>""));foreach(get_rows("SHOW PRIVILEGES")as$J){foreach(explode(",",($J["Privilege"]=="Grant option"?"":$J["Context"]))as$Hb)$ni[$Hb=="File access on server"?"Server Admin":$Hb][$J["Privilege"]]=$J["Comment"];}unset($ni["Server Admin"]["Usage"]);foreach($ni["Tables"]as$x=>$X)unset($ni["Databases"][$x]);$zg=array();if($_POST){foreach($_POST["objects"]as$x=>$X)$zg[$X]=(array)$zg[$X]+idx($_POST["grants"],$x,array());}$Wd=array();if(isset($_GET["host"])&&($H=connection()->query("SHOW GRANTS FOR ".q($fa)."@".q($_GET["host"])))){while($J=$H->fetch_row()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$J[0],$B)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$B[1],$Of,PREG_SET_ORDER)){foreach($Of
as$X){if($X[1]!="USAGE")$Wd["$B[2]$X[2]"][$X[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$J[0]))$Wd["$B[2]$X[2]"]["GRANT OPTION"]=true;}}}}if($_POST&&!$l){$Xg=(isset($_GET["host"])?q($fa)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $Xg",ME."privileges=",'User has been dropped.');else{$Cg=q($_POST["user"])."@".q($_POST["host"]);$Ph=$_POST["pass"];$Ob=false;$H=true;if($Xg!=$Cg){$Ob=queries("CREATE USER $Cg IDENTIFIED BY ".($_POST["hashed"]?"PASSWORD ":"").q($Ph));$H=$Ob;}elseif($Ph!="")$H=queries("SET PASSWORD FOR $Cg = ".(min_version(8,99)||$_POST["hashed"]?q($Ph):"PASSWORD(".q($Ph).")"));if($H){$Mi=array();foreach($zg
as$Lg=>$Vd){if(isset($_GET["grant"]))$Vd=array_filter($Vd);$Vd=array_keys($Vd);if(isset($_GET["grant"]))$Mi=array_diff(array_keys(array_filter($zg[$Lg],'strlen')),$Vd);elseif($Xg==$Cg){$Ug=array_keys((array)$Wd[$Lg]);$Mi=array_diff($Ug,$Vd);$Vd=array_diff($Vd,$Ug);unset($Wd[$Lg]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$Lg,$B)&&(!grant("REVOKE",$Mi,$B[2]," ON $B[1] FROM $Cg")||!grant("GRANT",$Vd,$B[2]," ON $B[1] TO $Cg"))){$H=false;break;}}}if($H&&isset($_GET["host"])){if($Xg!=$Cg)queries("DROP USER $Xg");elseif(!isset($_GET["grant"])){foreach($Wd
as$Lg=>$Mi){if(preg_match('~^(.+)(\(.*\))?$~U',$Lg,$B))grant("REVOKE",array_keys($Mi),$B[2]," ON $B[1] FROM $Cg");}}}if($H&&!Queries::$queries)redirect(ME."privileges=");queries_redirect(ME."privileges=",(isset($_GET["host"])?'User has been altered.':'User has been created.'),$H);if($Ob)connection()->query("DROP USER $Cg");}}page_header((isset($_GET["host"])?'Username'.": ".h("$fa@$_GET[host]"):'Create user'),$l,array("privileges"=>array('','Privileges')));$J=$_POST;if($J)$Wd=$zg;else{$J=$_GET+array("host"=>get_val("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)"));$Wd[(DB==""||$Wd?"":idf_escape(addcslashes(DB,"%_\\"))).".*"]=array();}echo'<form action="" method="post">
<table class="layout">
<tr><th>Server<td><input name="host" data-maxlength="60" value="',h($J["host"]),'" autocapitalize="off">
<tr><th>Username<td><input name="user" data-maxlength="80" value="',h($J["user"]),'" autocapitalize="off">
<tr><th>Password<td><input name="pass" id="pass" value="',h($J["pass"]),'" autocomplete="new-password">
',($J["hashed"]?"":script("typePassword(qs('#pass'));")),(min_version(8,99)?"":checkbox("hashed",1,$J["hashed"],'Hashed',on('click','hashedClick'))),'</table>

',"<table class='odds'>\n","<thead><tr><th colspan='2'>".'Privileges'.doc_link(array('sql'=>"grant.html#priv_level"));$s=0;foreach($Wd
as$Lg=>$Vd){echo'<th>'.($Lg!="*.*"?"<input name='objects[$s]' value='".h($Lg)."' size='10' autocapitalize='off'>":input_hidden("objects[$s]","*.*")."*.*");$s++;}echo"<tbody>\n";foreach(array(""=>"","Server Admin"=>'Server',"Databases"=>'Database',"Tables"=>'Table',"Procedures"=>'Routine',)as$Hb=>$kc){foreach((array)$ni[$Hb]as$mi=>$ub){echo"<tr><td".($kc?">$kc<td":" colspan='2'").' lang="en" title="'.h($ub).'">'.h($mi);$s=0;foreach($Wd
as$Lg=>$Vd){$C="'grants[$s][".h(strtoupper($mi))."]'";$Y=$Vd[strtoupper($mi)];if($Hb=="Server Admin"&&$Lg!=(isset($Wd["*.*"])?"*.*":".*"))echo"<td>";elseif(isset($_GET["grant"]))echo"<td><select name=$C><option><option value='1'".($Y?" selected":"").">".'Grant'."<option value='0'".($Y=="0"?" selected":"").">".'Revoke'."</select>";else
echo"<td align='center'><label class='block'>","<input type='checkbox' name=$C value='1'".($Y?" checked":"").($mi=="All privileges"?" id='grants-$s-all'":($mi=="Grant option"?"":on('click','grantsClick',"grants-$s-all"))).">","</label>";$s++;}}}echo"</table>\n",'<p>
<input type=\'submit\' value=\'Save\'>
';if(isset($_GET["host"]))echo'<input type=\'submit\' name=\'drop\' value=\'Drop\'',confirm(sprintf('Drop %s?',"$fa@$_GET[host]")),'>
';echo
input_token(),'</form>
';}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST&&!$l){$of=0;foreach((array)$_POST["kill"]as$X){if(adminer()->killProcess($X))$of++;}queries_redirect(ME."processlist=",lang_format(array('%d process has been killed.','%d processes have been killed.'),$of),$of||!$_POST["kill"]);}}page_header('Process list',$l);echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap checkable odds"',on('click','tableClick').on('dblclick','tableClick'),'>
';$s=-1;foreach(adminer()->processList()as$s=>$J){if(!$s){echo"<thead><tr lang='en'>".(support("kill")?"<td class='hover'>":"");foreach($J
as$x=>$X)echo"<th>$x".doc_link(array('sql'=>"show-processlist.html#processlist_".strtolower($x),'pgsql'=>"monitoring-stats.html#PG-STAT-ACTIVITY-VIEW",'oracle'=>"REFRN30223",));echo"<tbody>\n";}echo"<tr>".(support("kill")?"<td class='hover'>".checkbox("kill[]",$J[JUSH=="sql"?"Id":"pid"],0):"");foreach($J
as$x=>$X)echo"<td>".($X!=""&&((JUSH=="sql"&&$x=="Info"&&preg_match("~Query|Killed~",$J["Command"]))||(JUSH=="pgsql"&&$x=="query")||(JUSH=="oracle"&&$x=="sql_text"))?"<code class='jush-".JUSH."' data-full='".h($X)."'>".shorten_utf8($X,100,"</code>").' <a href="'.h(ME.($J["db"]!=""?"db=".url_escape($J["db"])."&":"")."sql=".url_escape($X)).'">'.'Clone'.'</a>'.' '.copy_icon():h($X));echo"\n";}echo'</table>
</div>
<p>
',script("copyCode(qsl('table'));");if(support("kill"))echo($s+1)."/".sprintf('%d in total',max_connections()),"<p><input type='submit' value='".'Kill'."'>\n";echo
input_token(),'</form>
',script("tableCheck();");}elseif(isset($_GET["select"])){$a=$_GET["select"];$S=table_status1($a);$w=indexes($a);$n=fields($a);$Jd=column_foreign_keys($a);$Sg=$S["Oid"];$pa=get_settings("adminer_import");$Ni=array();$e=array();$aj=array();$mh=array();$nk=null;foreach($n
as$x=>$m){$C=adminer()->fieldName($m);$wg=html_entity_decode(strip_tags($C),ENT_QUOTES);if(isset($m["privileges"]["select"])&&$C!=""){$e[$x]=$wg;if(is_shortable($m))$nk=adminer()->selectLengthProcess();}if(isset($m["privileges"]["where"])&&$C!="")$aj[$x]=$wg;if(isset($m["privileges"]["order"])&&$C!="")$mh[$x]=$wg;$Ni+=$m["privileges"];}list($M,$Xd)=adminer()->selectColumnsProcess($e,$w);$M=array_unique($M);$Xd=array_unique($Xd);$bf=count($Xd)<count($M);$Z=adminer()->selectSearchProcess($n,$w);$lh=adminer()->selectOrderProcess($n,$w);$z=adminer()->selectLimitProcess();if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$Tk=>$J){$Aa=convert_field($n[key($J)]);$M=array($Aa?:idf_escape(key($J)));$Z[]=where_check(bracket_escape($Tk,true),$n);$I=driver()->select($a,$M,$Z,$M);if($I)echo
first($I->fetch_row());}exit;}$ii=$Vk=array();foreach($w
as$v){if($v["type"]=="PRIMARY"){$ii=array_flip($v["columns"]);$Vk=($M?$ii:array());foreach($Vk
as$x=>$X){if(in_array(idf_escape($x),$M))unset($Vk[$x]);}break;}}if($Sg&&!$ii){$ii=$Vk=array($Sg=>0);$w[]=array("type"=>"PRIMARY","columns"=>array($Sg));}if($_POST&&!$l){$_l=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$gb=array();foreach($_POST["check"]as$cb)$gb[]=where_check($cb,$n);$_l[]="((".implode(") OR (",$gb)."))";}$Bl=$_l;$_l=($_l?"\nWHERE ".implode(" AND ",$_l):"");if($_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers($a);adminer()->dumpTable($a,"");$cj=($M?:array("*"));$Jb=convert_fields($e,$n,$M);if($Jb)$cj[]=substr($Jb,2);$G="";if(is_array($_POST["check"])&&!$ii){$Od=implode(", ",$cj)."\nFROM ".table($a);$Zd=($Xd&&$bf?"\nGROUP BY ".implode(", ",$Xd):"").($lh?"\nORDER BY ".implode(", ",$lh):"");$Rk=array();foreach($_POST["check"]as$X)$Rk[]="(SELECT".limit($Od,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$n).$Zd,1).")";$G=implode(" UNION ALL ",$Rk);}adminer()->dumpData($a,"table",$G,$cj,$Bl,($bf?$Xd:array()),$lh);adminer()->dumpFooter();exit;}if(!adminer()->selectEmailProcess($Z,$Jd)){if($_POST["save"]||$_POST["delete"]){$H=true;$qa=0;$O=array();if(!$_POST["delete"]){foreach($n
as$C=>$X){$u=bracket_escape($C);if(isset($_POST["fields"][$u])||$_FILES["fields-$u"]){$X=process_input($n[$C]);if($X!==null&&($_POST["clone"]||$X!==false))$O[idf_escape($C)]=($X!==false?$X:idf_escape($C));}}}if($_POST["delete"]||$O){$G=($_POST["clone"]?"INTO ".table($a)." (".implode(", ",array_keys($O)).")\nSELECT ".implode(", ",$O)."\nFROM ".table($a):"");if($_POST["all"]||($ii&&is_array($_POST["check"]))||$bf){$H=($_POST["delete"]?driver()->delete($a,$_l):($_POST["clone"]?queries("INSERT $G$_l".driver()->insertReturning($a)):driver()->update($a,$O,$_l)));$qa=connection()->affected_rows;if(is_object($H))$qa+=$H->num_rows;}else{foreach((array)$_POST["check"]as$X){$zl="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$n);$H=($_POST["delete"]?driver()->delete($a,$zl,1):($_POST["clone"]?queries("INSERT".limit1($a,$G,$zl)):driver()->update($a,$O,$zl,1)));if(!$H)break;$qa+=connection()->affected_rows;}}}$fg=lang_format(array('%d item has been affected.','%d items have been affected.'),$qa);if($_POST["clone"]&&$H&&$qa==1){$wf=last_id($H);if($wf)$fg=sprintf('Item%s has been inserted.'," $wf");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page|next":""),$fg,$H);if(!$_POST["delete"]){$bi=(array)$_POST["fields"];edit_form($a,array_intersect_key($n,$bi),$bi,!$_POST["clone"],$l);page_footer();exit;}}elseif(!$_POST["import"]){$H=true;$qa=0;foreach((array)$_POST["val"]as$Tk=>$J){$O=array();foreach($J
as$x=>$X){$x=bracket_escape($x,true);$O[idf_escape($x)]=(preg_match('~char|text~',$n[$x]["type"])||$X!=""?adminer()->processInput($n[$x],$X):"NULL");}$H=driver()->update($a,$O," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check(bracket_escape($Tk,true),$n),($bf||$ii?0:1)," ");if(!$H)break;$qa+=connection()->affected_rows;}queries_redirect(remove_from_uri(),lang_format(array('%d item has been affected.','%d items have been affected.'),$qa),$H);}elseif(!is_string($ud=get_file("csv_file",true)))$l=upload_error($ud);elseif(!preg_match('~~u',$ud))$l='File must be in UTF-8 encoding.';else{save_settings(array("output"=>$pa["output"],"format"=>$_POST["separator"]),"adminer_import");$rb=array_keys($n);$gj=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$Sb=parse_csv($ud,$gj);$qa=count($Sb);driver()->begin();$K=array();foreach($Sb
as$x=>$pl){if(!$x&&!array_diff($pl,$rb)){$rb=$pl;$qa--;}else{$O=array();foreach($pl
as$s=>$nb)$O[idf_escape($rb[$s])]=($nb==""&&$n[$rb[$s]]["null"]?"NULL":q(csv_value($nb)));$K[]=$O;}}$H=(!$K||driver()->insertUpdate($a,$K,$ii));if($H)driver()->commit();queries_redirect(remove_from_uri("page|next"),lang_format(array('%d row has been imported.','%d rows have been imported.'),$qa),$H);driver()->rollback();}}}$Yj=adminer()->tableName($S);if(is_ajax()){page_headers();ob_start();}else
page_header('Select'.": $Yj",$l);$O=null;if(isset($Ni["insert"])||!support("table")){$O="";foreach((array)$_GET["where"]as$X){$Y=$X["val"];if(is_array($Y))$Y=(count($Y)==1&&preg_match('~^val-(.*)~s',reset($Y),$B)?$B[1]:"");if($X["col"]!=""&&$Y!=""&&($X["op"]=="="||(!$X["op"]&&(is_array($X["val"])||!preg_match('~[_%]~',$Y)))))$O
.="&set[".url_escape(bracket_escape($X["col"]))."]=".url_escape($Y);}}adminer()->selectLinks($S,$O);if(!$e&&support("table"))echo"<p class='error'>".'Unable to select the table'.($n?".":": ".error())."\n";else{echo"<form action='' id='form'>\n","<div hidden>";hidden_fields_get();echo(DB!=""?input_hidden("db",DB).(isset($_GET["ns"])?input_hidden("ns",$_GET["ns"]):""):""),input_hidden("select",$a),"</div>\n";adminer()->selectColumnsPrint($M,$e);adminer()->selectSearchPrint($Z,$aj,$w);adminer()->selectOrderPrint($lh,$mh,$w);adminer()->selectLimitPrint($z);if($nk!==null)adminer()->selectLengthPrint($nk);adminer()->selectActionPrint($w);echo"</form>\n";foreach((array)$_GET["where"]as$X){if($X["op"]=="SQL"&&!in_array($_SERVER["HTTP_SEC_FETCH_SITE"],array("","same-origin"))){echo"<p class='error'>".'Invalid CSRF token. Send the form again.'.' '.'If you did not send this request from Adminer then close this page.'."\n";page_footer();exit;}}$D=$_GET["page"];$Md=null;if($D=="last"){$Md=get_val(count_rows($a,$Z,$bf,$Xd));$D=floor(max(0,intval($Md)-1)/$z);}$bj=$M;$Yd=$Xd;if(!$bj){$bj[]="*";$Jb=convert_fields($e,$n,$M);if($Jb)$bj[]=substr($Jb,2);}foreach($M
as$x=>$X){$m=$n[idf_unescape($X)];if($m&&($Aa=convert_field($m)))$bj[$x]="$Aa AS $X";}if(JUSH=="pgsql"||JUSH=="mssql"){foreach((array)$_GET["columns"]as$x=>$X){if(isset($bj[$x])&&$X["fun"])$bj[$x].=" AS ".idf_escape(apply_sql_function($X["fun"],($X["col"]!=""?$X["col"]:"*")));}}if(!$bf&&$Vk){foreach($Vk
as$x=>$X){$bj[]=idf_escape($x);if($Yd)$Yd[]=idf_escape($x);}}$H=driver()->select($a,$bj,$Z,$Yd,$lh,$z,$D,true);if(!is_object($H))echo"<p class='error'>".(error()?:'Unknown error.')."\n";else{if(JUSH=="mssql"&&$D)$H->seek($z*$D);$Nc=array();$K=array();while($J=$H->fetch_assoc()){if($D&&JUSH=="oracle")unset($J["RNUM"]);$K[]=$J;}$he=($z&&(support("cursor")?$_GET["next"]!="":count($K)>=$z));if(is_ajax()&&$he)header("X-Next-Page: ".pagination_href($D+1));if($_GET["modify"]&&$K){$Xf=max_input_vars(count($K[0])+1,20);echo($Xf&&count($K)>$Xf?"<p class='error'>".max_input_vars_error()."\n":"");}echo"<form action='' method='post' enctype='multipart/form-data'>\n";if($_GET["page"]!="last"&&$z&&$Xd&&$bf&&JUSH=="sql")$Md=get_val(" SELECT FOUND_ROWS()");if(!$K)echo"<p class='message'>".'No rows.'."\n";else{$Ka=adminer()->backwardKeys($a,$Yj);echo"<div class='scrollable'>","<table id='table' class='nowrap checkable odds'".on('click','tableClick').on('dblclick','tableClick').on('keydown','editingKeydown').">\n","<thead><tr>".(!$Xd&&$M?"":"<td class='hover check'><input type='checkbox' id='all-page' class='jsonly' title='".'All rows on this page'."'".on('click','formCheck','^check').">");$xg=array();$Sd=array();reset($M);$wi=1;foreach($K[0]as$x=>$X){if(!isset($Vk[$x])){$X=idx($_GET["columns"],key($M))?:array();$m=$n[$M?($X?$X["col"]:current($M)):$x];$C=($m?adminer()->fieldName($m,$wi):($X["fun"]?"*":h($x)));if($C!=""){$wi++;$xg[$x]=$C;$d=idf_escape($x);$te=remove_from_uri('(order|desc)[^=]*|page|next').'&order[0]='.url_escape($x);$kc="&desc[0]=1";$xj=preg_replace('~ DESC( NULLS LAST)?$~','',$lh[0]);$zj=($xj==$d||$xj==$x);echo"<th id='th[".h(bracket_escape($x))."]'".($zj?" aria-sort='".($xj==$lh[0]?"ascending":"descending")."'":"").">";$Rd=apply_sql_function($X["fun"],$C);$yj=isset($m["privileges"]["order"])||$Rd!=$C;echo($yj?"<a href='".h($te.($zj&&$xj==$lh[0]?$kc:''))."'>$Rd</a>":$Rd);$eg=($yj?"<a href='".h($te.$kc)."' title='".'descending'."' class='text'> ↓</a>":'');if(!$X["fun"]&&isset($m["privileges"]["where"]))$eg
.="<a href='#fieldset-search' title='".'Search'."' class='text jsonly'".on('click','selectSearch',$x)."> =</a>";echo($eg?"<span class='column'>$eg</span>":"");}$Sd[$x]=$X["fun"];next($M);}}$Cf=array();if($_GET["modify"]){foreach($K
as$J){foreach($J
as$x=>$X)$Cf[$x]=max($Cf[$x],min(40,strlen(utf8_decode($X))));}}echo($Ka?"<th>".'Relations':"")."<tbody>\n";if(is_ajax())ob_end_clean();foreach(adminer()->rowDescriptions($K,$Jd)as$vg=>$J){$Sk=unique_array($K[$vg],$w);if(!$Sk){$Sk=array();reset($M);foreach($K[$vg]as$x=>$X){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($M)))$Sk[$x]=$X;next($M);}}$Tk="";foreach($Sk
as$x=>$X){$m=(array)$n[$x];$af=is_blob($m);if((JUSH=="sql"||JUSH=="pgsql")&&($af||preg_match('~char|text|enum|set~',$m["type"]))&&strlen($X)>64){$x=(strpos($x,'(')?$x:idf_escape($x));$x="MD5(".($af||JUSH!='sql'||preg_match("~^utf8~",$m["collation"])?$x:"CONVERT($x USING ".charset(connection()).")").")";$X=md5($af?(string)driver()->value($X,$m):$X);}$Tk
.="&".($X!==null?"where[".url_escape(bracket_escape($x))."]=".url_escape($X===false?"f":$X):"null[]=".url_escape($x));}echo"<tr>".(!$Xd&&$M?"":"<td class='hover check'>".($bf||information_schema(DB)?"":"<a href='".h(ME."edit=".url_escape($a).$Tk)."' class='edit'>".'edit'."</a> ").checkbox("check[]",substr($Tk,1),in_array(substr($Tk,1),(array)$_POST["check"])));reset($M);foreach($J
as$x=>$X){if(isset($xg[$x])){$d=current($M);$m=(array)$n[$x];if($X!=""&&(!isset($Nc[$x])||$Nc[$x]!=""))$Nc[$x]=(is_mail($X)?$xg[$x]:"");$_="";if(is_blob($m)&&$X!="")$_=ME.'download='.url_escape($a).'&field='.url_escape($x).$Tk;if(!$_&&$X!==null){foreach((array)$Jd[$x]as$p){if(count($Jd[$x])==1||end($p["source"])==$x){$_="";foreach($p["source"]as$s=>$_j)$_
.=where_link($s,$p["target"][$s],$K[$vg][$_j]);$_=($p["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.url_escape($p["db"]),ME):ME).'select='.url_escape($p["table"]).$_;if($p["ns"])$_=preg_replace('~([?&]ns=)[^&]+~','\1'.url_escape($p["ns"]),$_);if(count($p["source"])==1)break;}}}if($d=="COUNT(*)"){$_=ME."select=".url_escape($a);$s=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$Sk))$_
.=where_link($s++,$W["col"],$W["val"],$W["op"]);}foreach($Sk
as$kf=>$W)$_
.=where_link($s++,$kf,$W);}$ue=select_value($X,$_,$m,$nk);$u=bracket_escape($Tk);$t=h("val[$u][".bracket_escape($x)."]");$di=idx(idx($_POST["val"],$u),bracket_escape($x));$Yk=idx($m["privileges"],"update");$Jc=!is_array($J[$x])&&!is_blob($m)&&is_utf8($X)&&$K[$vg][$x]==$X&&!$Sd[$x]&&!$m["generated"]&&$Yk;$U=(preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$d,$B)?$n[idf_unescape($B[2])]["type"]:$m["type"]);$lk=preg_match('~text|json|lob~',$U);$cf=preg_match(number_type(),$U)||preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|TIME_TO_SEC|COUNT|SUM)\(~',$d);echo"<td id='$t'".($cf&&($X===null||is_numeric(strip_tags($ue))||$U=="money")?" class='number'":"");if(($_GET["modify"]&&$Jc&&$X!==null)||$di!==null){$ce=h($di!==null?$di:$X);echo">".($lk?"<textarea name='$t' cols='30' rows='".(substr_count($X,"\n")+1)."'>$ce</textarea>":"<input name='$t' value='$ce' size='$Cf[$x]'>");}else{$Kf=strpos($ue,"<i>…</i>");echo($Yk?" data-text='".($Kf?2:($lk?1:0))."'".($Jc?"":" data-warning='".'Use edit link to modify this value.'."'"):"").">$ue";}}next($M);}if($Ka)echo"<td>";adminer()->backwardKeysPrint($Ka,$K[$vg]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n","</div>\n";}if(!is_ajax()){if($K||$D||$he){$bd=true;if($_GET["page"]!="last"){if(!$z||(count($K)<$z&&($K||!$D)))$Md=($D?$D*$z:0)+count($K);elseif(JUSH!="sql"||!$bf){$Md=($bf?false:found_rows($S,$Z));if(intval($Md)<max(1e4,2*($D+1)*$z))$Md=first(slow_query(count_rows($a,$Z,$bf,$Xd)));elseif(JUSH=='sql'||JUSH=='pgsql')$bd=false;}}if(!support("cursor"))$he=(($Md===false?count($K)+1:$Md-$D*$z)>$z);$Ch=($z&&($he||$D));if($Ch)echo($he?'<p><a href="'.h(pagination_href($D+1)).'" class="loadmore"'.on('click','selectLoadMore','Loading…').'>'.'Load more data'.'</a>':''),"\n";echo"<div class='footer'><div>\n";if($Ch){$Vf=($Md===false?$D+($K?(count($K)>=$z?2:1):0):floor(($Md-1)/$z));echo"<fieldset><legend>".'Page'."</legend>";if(!support("cursor")){echo
pagination(0,$D).($D>5?" …":"");for($s=max(1,$D-4);$s<min($Vf,$D+5);$s++)echo
pagination($s,$D);if($Vf>0)echo($D+5<$Vf?" …":""),($bd&&$Md!==false?pagination($Vf,$D):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$Vf'>".'last'."</a>");}else
echo
pagination(0,$D).($D>1?" …":""),($D?pagination($D,$D):""),($he?pagination($D+1,$D)." …":"");echo"</fieldset>\n";}echo"<fieldset>","<legend>".'Whole result'."</legend>";$sc=($bd?"":"~ ").$Md;$rf=($Md!==false?($bd?"":"~ ").lang_format(array('%d row','%d rows'),$Md):"");echo
checkbox("all",1,0,$rf,on('click','countRows',$sc))."\n","</fieldset>\n";if(adminer()->selectCommandPrint())echo'<fieldset',($_GET["modify"]?'':" title='".'Ctrl+click on a value to modify it.'."'"),'>
<legend><a href=\'',h($_GET["modify"]?remove_from_uri("modify"):relative_uri()."&modify=1"),'\'>Modify</a></legend><div>
<input type=\'submit\' id=\'save\' value=\'Save\'',($_GET["modify"]?'':" class='jsonly' disabled"),'>
</div></fieldset>

<fieldset><legend>Selected <span id="selected"></span></legend><div>
<input type=\'submit\' name=\'edit\' value=\'Edit\'>
<input type=\'submit\' name=\'clone\' value=\'Clone\'>
<input type=\'submit\' name=\'delete\' value=\'Delete\'',confirm(),'>
</div></fieldset>
';$Kd=adminer()->dumpFormat();foreach((array)$_GET["columns"]as$d){if($d["fun"]){unset($Kd['sql']);break;}}if($Kd){print_fieldset("export",'Export'." <span id='selected2'></span>");$zh=adminer()->dumpOutput();echo($zh?html_select("output",$zh,$pa["output"])." ":""),html_select("format",$Kd,$pa["format"])," <input type='submit' name='export' value='".'Export'."'>\n","</div></fieldset>\n";}adminer()->selectEmailPrint(array_filter($Nc,'strlen'),$e);echo"</div></div>\n";}if(adminer()->selectImportPrint())echo"<p>","<a href='#import' class='toggle'>".'Import'."</a>","<span id='import'".($_POST["import"]?"":" class='hidden'").">: ",file_input(" name='csv_file'"," ".html_select("separator",array("csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"),$pa["format"])." <input type='submit' name='import' value='".'Import'."'>"),"</span>";echo
input_token(),"</form>\n",(!$Xd&&$M?"":script("tableCheck();"));}}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$P=isset($_GET["status"]);page_header($P?'Status':'Variables');$ql=($P?adminer()->showStatus():adminer()->showVariables());if(!$ql)echo"<p class='message'>".'No rows.'."\n";else{echo"<table>\n";foreach($ql
as$J){echo"<tr>";$x=array_shift($J);echo"<th><code class='jush-".JUSH.($P?"status":"set")."'>".h($x)."</code>";foreach($J
as$X)echo"<td>".nl_br(h($X));}echo"</table>\n";}}elseif(isset($_GET["script"])){header("Content-Type: application/json; charset=utf-8");if($_GET["script"]=="db"){$Rj=array("Data_length"=>0,"Index_length"=>0,"Data_free"=>0);foreach(table_status()as$C=>$S){json_row("Comment-$C",h($S["Comment"]));if(!is_view($S)||preg_match('~materialized~i',$S["Engine"])){foreach(array("Engine","Collation")as$x)json_row("$x-$C",h($S[$x]));foreach(array_keys($Rj+array("Auto_increment"=>0,"Rows"=>0))as$x){if(array_key_exists($x,$S))json_row("$x-$C",format_status($S,$x));if($S[$x]!=""&&isset($Rj[$x]))$Rj[$x]+=($S["Engine"]!="InnoDB"||$x!="Data_free"?$S[$x]:0);}}}if(function_exists('Adminer\db_status'))$Rj=db_status();foreach($Rj
as$x=>$X)json_row("sum-$x",format_number($X));json_row("");}elseif($_GET["script"]=="kill")connection()->query("KILL ".number($_POST["kill"]));else{foreach(count_tables(adminer()->databases(false))as$j=>$X){json_row("tables-$j",$X);json_row("size-$j",db_size($j));}json_row("");}exit;}else{$fk=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($fk&&!$l&&!$_POST["search"]){$H=true;$fg="";if(JUSH=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$H=truncate_tables($_POST["tables"]);$fg='Tables have been truncated.';}elseif($_POST["move"]){$H=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$fg='Tables have been moved.';}elseif($_POST["copy"]){$H=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$fg='Tables have been copied.';}elseif($_POST["drop"]){if($_POST["views"])$H=drop_views($_POST["views"]);if($H&&$_POST["tables"])$H=drop_tables($_POST["tables"]);$fg='Tables have been dropped.';}elseif(JUSH=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$R){foreach(get_rows("PRAGMA integrity_check(".q($R).")")as$J)$fg
.="<b>".h($R)."</b>: ".h($J["integrity_check"])."<br>";}}elseif(JUSH!="sql"){$H=(JUSH=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?" ANALYZE":""),(array)$_POST["tables"]));$fg='Tables have been optimized.';}elseif(!$_POST["tables"])$fg='No tables.';elseif($H=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('Adminer\idf_escape',$_POST["tables"])))){while($J=$H->fetch_assoc())$fg
.="<b>".h($J["Table"])."</b>: ".h($J["Msg_text"])."<br>";}queries_redirect($_SERVER["REQUEST_URI"],$fg,$H);}page_header(($_GET["ns"]==""?'Database'.": ".h(DB):'Schema'.": ".h($_GET["ns"])),$l,true);if(adminer()->homepage()){if($_GET["ns"]!==""){$lh=$_GET["order"];$Pd=($lh||support("fast_status"));echo"<div>\n","<h3 id='tables-views'>".'Tables and views'."</h3>\n";$ek=($Pd?table_status():tables_list());if(!$ek)echo"<p class='message'>".'No tables.'."\n";else{echo"<form action='' method='post'>\n";if(support("table")){echo"<fieldset><legend>".'Search data in tables'." <span id='selected2'></span></legend><div>",html_select("op",adminer()->operators(),idx($_POST,"op",JUSH=="elastic"?"should":"LIKE %%"))," <input type='search' name='query' value='".h($_POST["query"])."'".on('keydown','submitKeydown','search').">"," <input type='submit' name='search' value='".'Search'."'>\n","</div></fieldset>\n";if(!$l&&$_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable odds'".on('click','tableClick').on('dblclick','tableClick').">\n",'<thead><tr class="wrap">','<td class="hover"><input id="check-all" type="checkbox" class="jsonly" title="'.'All'.'"'.on('click','formCheck','^(tables|views)\[').'>','<th'.(!$lh&&JUSH!='sqlite'?" aria-sort='ascending'":'').'><a href="'.h(substr(ME,0,-1)).'">'.'Table'.'</a>';$e=array("Engine"=>array('Engine'.doc_link(array('sql'=>'storage-engines.html'))));if(collations())$e["Collation"]=array('Collation'.doc_link(array('sql'=>'charset-charsets.html','mariadb'=>'supported-character-sets-and-collations/')));if(function_exists('Adminer\alter_table'))$e["Data_length"]=array('Data Length'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'REFRN20286')),"create",'Alter table',);if(support("indexes"))$e["Index_length"]=array('Index Length'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT')),"indexes",'Alter indexes',);$e["Data_free"]=array('Data Free'.doc_link(array('sql'=>'show-table-status.html')),"edit",'New item');if(function_exists('Adminer\alter_table'))$e["Auto_increment"]=array('Auto Increment'.doc_link(array('sql'=>'example-auto-increment.html','mariadb'=>'auto_increment/')),"auto_increment=1&create",'Alter table',);$e["Rows"]=array('Rows'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'REFRN20286')),"select",'Select data',);if(support("comment"))$e["Comment"]=array('Comment'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE')));$Ba=array('Engine','Collation','Comment');foreach($e
as$x=>$d)echo"<th".($lh==$x?" aria-sort='".(in_array($x,$Ba)?"ascending":"descending")."'":"")."><a href='".h(ME)."order=$x'>$d[0]</a>";echo"<tbody>\n";if($lh){uasort($ek,function($ia,$Ha)use($lh,$Ba){$I=($ia[$lh]<$Ha[$lh]?-1:($ia[$lh]>$Ha[$lh]?1:0));return(in_array($lh,$Ba)?$I:-$I);});}$T=0;$Rj=array("Data_length"=>0,"Index_length"=>0,"Data_free"=>0);foreach($ek
as$C=>$P){$tl=($Pd?is_view($P):$P!==null&&!preg_match('~table|sequence~i',$P));$P=($Pd?$P:array('Engine'=>$P));$t=h("Table-".$C);echo'<tr><td class="hover">'.checkbox(($tl?"views[]":"tables[]"),$C,in_array("$C",$fk,true),"","","",$t),'<th>'.(support("table")||support("indexes")?"<a href='".h(ME)."table=".url_escape($C)."' title='".'Show structure'."' id='$t'>".h($C).'</a>':h($C));if($tl&&!preg_match('~materialized~i',$P['Engine'])){$rk='View';echo'<td colspan="'.(count($e)-(support("comment")?2:1)).'">'.(support("view")?"<a href='".h(ME)."view=".url_escape($C)."' title='".'Alter view'."'>$rk</a>":$rk),"<td align='right'><a href='".h(ME)."select=".url_escape($C)."' title='".'Select data'."'>?</a>";if(support("comment"))echo'<td>'.h($P['Comment']);}else{if($Pd){foreach(array_keys($Rj)as$x)$Rj[$x]+=($P["Engine"]!="InnoDB"||$x!="Data_free"?idx($P,$x):0);}foreach($e
as$x=>$d){$t=" id='$x-".h($C)."'";echo($d[1]?"<td align='right'><a href='".h(ME."$d[1]=").url_escape($C)."'$t title='$d[2]'>".format_status($P,$x)."</a>":"<td$t>".h(idx($P,$x,'?')));}$T++;}echo"\n";}echo"<tr><td class='hover'><th>".sprintf('%d in total',count($ek)),"<td>".h(JUSH=="sql"?get_val("SELECT @@default_storage_engine"):""),(collations()?"<td>".h(db_collation(DB,collations())):'');if($Pd&&function_exists('Adminer\db_status'))$Rj=db_status();foreach($Rj
as$x=>$Qj)echo($e[$x]?"<td align='right' id='sum-$x'>".($Pd?format_number($Qj):""):"");echo"\n","</table>\n",($Pd?'':script("ajaxSetHtml('".js_escape(ME)."script=db');")),"</div>\n";if(!information_schema(DB)){$ml="<input type='submit' value='".'Vacuum'."'".on_help("VACUUM")."> ";$hh="<input type='submit' name='optimize' value='".'Optimize'."'".on_help(JUSH=="sql"?"OPTIMIZE TABLE":"VACUUM ANALYZE")."> ";$ki=(JUSH=="sqlite"?$ml."<input type='submit' name='check' value='".'Check'."'".on_help("PRAGMA integrity_check")."> ":(JUSH=="pgsql"?$ml.$hh:(JUSH=="sql"?"<input type='submit' value='".'Analyze'."'".on_help("ANALYZE TABLE")."> ".$hh."<input type='submit' name='check' value='".'Check'."'".on_help("CHECK TABLE")."> "."<input type='submit' name='repair' value='".'Repair'."'".on_help("REPAIR TABLE")."> ":""))).(function_exists('Adminer\truncate_tables')?"<input type='submit' name='truncate' value='".'Truncate'."'".confirm().on_help(JUSH=="sqlite"?"DELETE":"TRUNCATE".(JUSH=="pgsql"?"":" TABLE"))."> ":"").(function_exists('Adminer\drop_tables')?"<input type='submit' name='drop' value='".'Drop'."'".confirm().on_help("DROP TABLE").">":"");echo($ki?"<div class='footer'><div>\n<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>$ki\n</div></fieldset>\n":"");$i=(support("scheme")?adminer()->schemas():adminer()->databases());if(count($i)!=1&&function_exists('Adminer\move_tables')){echo"<fieldset><legend>".'Move to other database'." <span id='selected3'></span></legend><div>";$j=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($i?html_select("target",$i,$j):'<input name="target" value="'.h($j).'" autocapitalize="off">'),"</label> <input type='submit' name='move' value='".'Move'."'>",(support("copy")?" <input type='submit' name='copy' value='".'Copy'."'> ".checkbox("overwrite",1,$_POST["overwrite"],'overwrite'):""),"</div></fieldset>\n";}echo"<input type='hidden' name='all' value=''".on('click','countTables',$T).">\n",input_token(),"</div></div>\n";}echo"</form>\n",script("tableCheck();");}echo(function_exists('Adminer\alter_table')?"<p class='links hover'><a href='".h(ME)."create='>".'Create table'."</a>\n":''),(support("view")?"<a href='".h(ME)."view='>".'Create view'."</a>\n":""),"</div>\n";if(support("routine")){echo"<div>\n","<h3 id='routines'>".'Routines'."</h3>\n";$Si=routines();if($Si){echo"<table class='odds'>\n",'<thead><tr><th>'.'Name'.'<td>'.'Type'.'<td>'.'Return type'."<td class='hover'><tbody>\n";foreach($Si
as$J){$C=($J["SPECIFIC_NAME"]==$J["ROUTINE_NAME"]?"":"&name=".url_escape($J["ROUTINE_NAME"]));echo'<tr>','<th><a href="'.h(ME.($J["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').url_escape($J["SPECIFIC_NAME"]).$C).'">'.h($J["ROUTINE_NAME"]).'</a>','<td>'.h($J["ROUTINE_TYPE"]),'<td>'.h($J["DTD_IDENTIFIER"]),'<td class="hover"><a href="'.h(ME.($J["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').url_escape($J["SPECIFIC_NAME"]).$C).'">'.'Alter'."</a>";}echo"</table>\n";}echo'<p class="links hover">'.(support("procedure")?'<a href="'.h(ME).'procedure=">'.'Create procedure'.'</a>':'').'<a href="'.h(ME).'function=">'.'Create function'."</a>\n","</div>\n";}if(support("sequence")){echo"<div>\n","<h3 id='sequences'>".'Sequences'."</h3>\n";$kj=get_vals("SELECT relname FROM pg_class WHERE relkind = 'S' AND relnamespace = ".driver()->nsOid." ORDER BY relname");if($kj){echo"<table class='odds'>\n","<thead><tr><th>".'Name'."<tbody>\n";foreach($kj
as$X)echo"<tr><th><a href='".h(ME)."sequence=".url_escape($X)."'>".h($X)."</a>\n";echo"</table>\n";}echo"<p class='links hover'><a href='".h(ME)."sequence='>".'Create sequence'."</a>\n","</div>\n";}if(support("type")){echo"<div>\n","<h3 id='user-types'>".'User types'."</h3>\n";$jl=types();if($jl){echo"<table class='odds'>\n","<thead><tr><th>".'Name'."<tbody>\n";foreach($jl
as$X)echo"<tr><th><a href='".h(ME)."type=".url_escape($X)."'>".h($X)."</a>\n";echo"</table>\n";}echo"<p class='links hover'><a href='".h(ME)."type='>".'Create type'."</a>\n","</div>\n";}if(support("event")){echo"<div>\n","<h3 id='events'>".'Events'."</h3>\n";$K=get_rows("SHOW EVENTS");if($K){echo"<table>\n","<thead><tr><th>".'Name'."<td>".'Schedule'."<td>".'Start'."<td>".'End'."<td><tbody>\n";foreach($K
as$J)echo"<tr>","<th>".h($J["Name"]),"<td>".($J["Execute at"]?'At given time'."<td>".h($J["Execute at"]):'Every'." ".h($J["Interval value"])." ".h($J["Interval field"])."<td>".h($J["Starts"])),"<td>".h($J["Ends"]),'<td><a href="'.h(ME).'event='.url_escape($J["Name"]).'">'.'Alter'.'</a>';echo"</table>\n";$Zc=get_val("SELECT @@event_scheduler");if($Zc&&$Zc!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($Zc)."\n";}echo'<p class="links hover"><a href="'.h(ME).'event=">'.'Create event'."</a>\n","</div>\n";}}}}page_footer();