<?php 

function QPlayer_install(){
    add_option('autoPlay', false);
    add_option('rotate', false);
    add_option('css', '');
    add_option('js', 
'//改变列表的背景颜色(错开颜色)，开启请删除注释
/*
function bgChange(){
	var lis= $(".lib");
	for(var i=0; i<lis.length; i+=2)
	lis[i].style.background = "rgba(246, 246, 246, 0.5)";
}
window.onload = bgChange;
*/');
    add_option('musicType', 'song');
    add_option('neteaseID','');
    add_option('musicList', '
{
    title: "叫做你的那个人",
    artist: "Jessica",
    cover: "https://obw915dkh.qnssl.com/cover/%E5%8F%AB%E5%81%9A%E4%BD%A0%E7%9A%84%E9%82%A3%E4%B8%AA%E4%BA%BA.jpg",
    mp3: "https://obw92zax9.qnssl.com/%E5%8F%AB%E5%81%9A%E4%BD%A0%E7%9A%84%E9%82%A3%E4%B8%AA%E4%BA%BA.mp3",
},
{
    title: "如果",
    artist: "金泰妍",
    cover: "https://obw915dkh.qnssl.com/cover/%E5%A6%82%E6%9E%9C.jpg",
    mp3: "https://obw92zax9.qnssl.com/%E5%A6%82%E6%9E%9C.mp3",
}');
}

function QPlayer_uninstall(){
	delete_option('autoPlay');
	delete_option('rotate');
	delete_option('css');
	delete_option('js');
    delete_option('musicType');
    delete_option('neteaseID');
	delete_option('musicList');
}

/**
 * 从netease中获取歌曲信息
 * 
 * @link https://github.com/webjyh/WP-Player/blob/master/include/player.php
 * @param unknown $id 
 * @param unknown $type 获取的id的类型，song:歌曲,album:专辑,artist:艺人,collect:歌单
 */
function get_netease_music($id, $type = 'song'){
    $return = false;
    switch ( $type ) {
        case 'song': $url = "http://music.163.com/api/song/detail/?ids=[$id]"; $key = 'songs'; break;
        case 'album': $url = "http://music.163.com/api/album/$id?id=$id"; $key = 'album'; break;
        case 'artist': $url = "http://music.163.com/api/artist/$id?id=$id"; $key = 'artist'; break;
        case 'collect': $url = "http://music.163.com/api/playlist/detail?id=$id"; $key = 'result'; break;
        default: $url = "http://music.163.com/api/song/detail/?ids=[$id]"; $key = 'songs';
    }

    if (!function_exists('curl_init')) return false;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Cookie: appver=2.0.2' ));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_REFERER, 'http://music.163.com/;');
    $cexecute = curl_exec($ch);
    curl_close($ch);

    if ( $cexecute ) {
        $result = json_decode($cexecute, true);
        if ( $result['code'] == 200 && $result[$key] ){

            switch ( $key ){
                case 'songs' : $data = $result[$key]; break;
                case 'album' : $data = $result[$key]['songs']; break;
                case 'artist' : $data = $result['hotSongs']; break;
                case 'result' : $data = $result[$key]['tracks']; break;
                default : $data = $result[$key]; break;
            }

            //列表
            $list = array();
            foreach ( $data as $keys => $data ){

                $list[$data['id']] = array(
                        'title' => $data['name'],
                        'artist' => $data['artists'][0]['name'],
                        'location' => str_replace('http://m', 'http://p', $data['mp3Url']),
                        'pic' => $data['album']['blurPicUrl'].'?param=106x106'
                );
            }
            //修复一次添加多个id的乱序问题
            if ($type = 'song' && strpos($id, ',')) {
                $ids = explode(',', $id);
                $r = array();
                foreach ($ids as $v) {
                    if (!empty($list[$v])) {
                        $r[] = $list[$v];
                    }
                }
                $list = $r;
            }
            //最终播放列表
            $return = $list;
        }
    } else {
        $return = array('status' =>  false, 'message' =>  '非法请求');
    }
    return $return;
}



function parse($id, $type) {
    $resultList = explode(",", $id);
    $result="\n";
    foreach ($resultList as $key => $value) {
        $musicList = get_netease_music($value,$type);
        foreach($musicList as $x=>$x_value) {
            $result .= "{";
            foreach ($x_value as $key => $value) {
                if ($key == 'location') {
                    $key = 'mp3';
                }
                if ($key == 'pic') {
                    $key = 'cover';
                }
                if (strpos($value, '"') !== false) {
                    $value = addcslashes($value, '"');
                }
                $result .= "$key:\"". $value."\",";
            }
            $result .= "},\n";
        }
    }
    return $result;
}


function QPlayer_page() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    } 
    if (isset($_POST['submit']) && $_SERVER['REQUEST_METHOD']=='POST'){
        update_option('autoPlay',$_POST['autoPlay']=='false'?false:true);
        update_option('rotate',$_POST['rotate']=='false'?false:true);
        update_option('css',$_POST['css'] ? stripcslashes($_POST['css']) : '');
        update_option('js',$_POST['js'] ? stripcslashes($_POST['js']) : '');
        update_option('musicType',$_POST['musicType']);
        update_option('neteaseID',$_POST['neteaseID'] ? $_POST['neteaseID'] : '');
        update_option('musicList',$_POST['musicList'] ? stripcslashes($_POST['musicList']) : '');
        echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
<p><strong>设置已保存。</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">忽略此通知。</span></button></div>';
    } 
    if (isset($_POST['addMusic']) && $_SERVER['REQUEST_METHOD']=='POST') {
    	update_option('musicType',$_POST['musicType']);
        update_option('neteaseID',$_POST['neteaseID'] ? $_POST['neteaseID'] : '');
    	$musicResult = parse(get_option('neteaseID'), get_option('musicType'));
    	$deal = get_option('musicList');
    	if ($deal != '' && substr(trim($deal), -1) != ','){
    		$deal .= ',';
    	}
    	update_option('musicList', $deal.$musicResult);
    	echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
<p><strong>音乐已添加到音乐列表。</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">忽略此通知。</span></button></div>';
    }
?>
    <style>
        body {
        	font-family: 'Merriweather','Open Sans',"PingFang SC",'Hiragino Sans GB','Microsoft Yahei','WenQuanYi Micro Hei','Segoe UI Emoji','Segoe UI Symbol',Helvetica,Arial,sans-serif;
        }
    	.title {
    		font-size: 15px;
    		font-weight:bold;
    		margin-bottom: 5px;
    	}
    	.tip,#addMusic {
    		margin-top: 0;
    	}
    	#addMusic, #submit{
    		font-weight: 500;
            font-size: 13px;
            font-family: "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
            border-radius: 10px;
            background-color: #1b9af7;
            border-color: #4cb0f9;
            color: #FFF;
            border:0;
            padding: 6px 13px;
            outline:none;
    	}
    	#addMusic:hover, #submit:hover {
            background-color: #4cb0f9;
            cursor:pointer;
        }
        #inputID{
            width:300px;
        }
    </style>
    <div class="QPlayer">  
      <h1>QPlayer设置</h1><br>
        <form method="post">  
			<div><div class="title">自动播放</div>
			  <input type="radio" name="autoPlay" value="false" <?php if (get_option('autoPlay') == false) echo "checked";?>>否
  			  <input type="radio" name="autoPlay" value="true" <?php if (get_option('autoPlay')) echo "checked";?>>是
			</div><br>
			<div><div class="title">封面旋转</div>
			  <input type="radio" name="rotate" value="false" <?php if (get_option('rotate') == false) echo "checked";?>>否
  			  <input type="radio" name="rotate" value="true" <?php if (get_option('rotate')) echo "checked";?>>是
			</div><br>
			<div><div class="title">自定义CSS</div>
			  <textarea rows="6" cols="100" name="css"><?php echo get_option('css') ?></textarea>
			</div><br>
			<div><div class="title">自定义JS</div>
			  <textarea rows="6" cols="100" name="js"><?php echo get_option('js') ?></textarea>
			</div><br>
            <div class="title">添加网易云音乐(需主机支持curl扩展)</div>
            <div>id类型
                <input type="radio" name="musicType"  value="song"  <?php if (get_option('musicType') == 'song') echo "checked";?>>单曲
                <input type="radio" name="musicType" value="album" <?php if (get_option('musicType') == 'album') echo "checked";?>>专辑
                <input type="radio" name="musicType" value="artist" <?php if (get_option('musicType') == 'artist') echo "checked";?>>艺人
                <input type="radio" name="musicType" value="collect" <?php if (get_option('musicType') == 'collect') echo "checked";?>>歌单
            </div>
            <div>id输入
                <input type="text" id="inputID" onclick="clickAnimation()" placeholder="多个id用英文,分隔开" name="neteaseID" value="<?php echo get_option('neteaseID') ?>">
                <p class="tip" style="margin-bottom: 0;">请自行去网易云音乐网页版获取音乐id(具体在每个音乐项目的网址最后会有个id)。</p>
            </div>
			<input type="submit" name="addMusic" id="addMusic" value="添加到歌曲列表"  /><br><br>
			<div><div class="title">歌曲列表</div>
			  <textarea rows="8" cols="100" name="musicList"><?php echo get_option('musicList') ?></textarea>
  			  <p class="tip">格式: {title:"xxx", artist:"xxx", cover:"http:xxxx", mp3:"http:xxxx"} ，每个歌曲之间用英文,隔开。请保证歌曲列表里至少有一首歌！</p>
			</div>
			<input type="submit" name="submit" id="submit" value="<?php _e('Save Changes') ?>"  />  
            </p>  
        </form>  
    </div>  
<?php
}
?>