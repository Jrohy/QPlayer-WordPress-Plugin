<?php
/*
Plugin Name: QPlayer
Plugin URI: https://github.com/Jrohy/QPlayer-WordPress-Plugin
Version: 1.3.2
Author: Jrohy
Author URI: https://32mb.space
Description:简洁美观非常Qの悬浮音乐播放器，支持网易云音乐解析
*/

define('QPlayer_URL', plugins_url('', __FILE__));

require dirname(__FILE__) . '/option.php';

register_deactivation_hook(__FILE__, 'QPlayer_uninstall');
register_activation_hook(__FILE__, 'QPlayer_install');

add_action('admin_menu', 'QPlayer_menu');
add_action('wp_footer', 'footer');
add_filter('plugin_action_links', 'QPlayer_plugin_setting', 10, 2);

function QPlayer_menu() {
    add_options_page('QPlayer', 'QPlayer','manage_options', 'QPlayer_page', 'QPlayer_page');
}

//设置link
function QPlayer_plugin_setting( $links, $file )
{
    if($file == 'QPlayer/QPlayer.php'){
        $settings_link = '<a href="' . admin_url( 'options-general.php?page=QPlayer_page' ) . '">' . __('Settings') . '</a>';
        array_unshift( $links, $settings_link ); // before other links
    }
    return $links;
}


function footer(){
	echo '<link rel="stylesheet" href="'.QPlayer_URL.'/css/player.css">';

	echo '
		<div id="QPlayer" style="z-index:2016">
		<div id="pContent">
			<div id="player">
				<span class="cover" title="点击开启随机播放"></span>
				<div class="ctrl">
					<div class="musicTag marquee">
						<strong>Title</strong>
						 <span> - </span>
						<span class="artist">Artist</span>
					</div>
					<div class="progress">
						<div class="timer left">0:00</div>
						<div class="contr">
							<div class="rewind icon"></div>
							<div class="playback icon"></div>
							<div class="fastforward icon"></div>
						</div>
						<div class="right">
							<div class="liebiao icon"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="ssBtn">
			        <div class="adf"></div>
		    </div>
		</div>
		<ol id="playlist"></ol>
		</div>
         ';
         
    if(get_option('color') != '') {
        echo '<style>
        #pContent .ssBtn {
            background-color:'.get_option('color').';
        }
        #playlist li.playing, #playlist li:hover{
            border-left-color:'.get_option('color').';
        }
        </style>';
    }
    if (get_option('css') != '') {
        echo '<style>'.get_option('css').'</style>' . "\n";
    }
    echo '<script src="'.QPlayer_URL. '/js/jquery.min.js"></script>';
    echo '
        <script>
          var autoplay = '.(get_option('autoPlay')?1:0).';
          var playlist = ['.get_option('musicList').'];
          var isRotate = '.(get_option('rotate')?1:0).';
        </script> ' . "\n";
    echo '<script  src="'.QPlayer_URL.'/js/jquery.marquee.min.js"></script>' . "\n";
    echo '<script  src="'.QPlayer_URL.'/js/player.js"></script>' . "\n";
    if (get_option('js') != '') {
        echo '<script>'.get_option('js').'</script>' . "\n";
    }
}

?>