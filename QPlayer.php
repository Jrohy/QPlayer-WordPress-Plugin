<?php
/*
Plugin Name: QPlayer
Plugin URI: https://github.com/Jrohy/QPlayer-WordPress-Plugin
Version: 1.3.4.1
Author: Jrohy
Author URI: https://32mb.space
Description:简洁美观非常Qの悬浮音乐播放器，支持网易云音乐解析
*/

define('QPlayer_URL', plugins_url('', __FILE__));
define('QPlayer_VER', '1.3.4.1');

require dirname(__FILE__) . '/option.php';

register_deactivation_hook(__FILE__, 'QPlayer_uninstall');
register_activation_hook(__FILE__, 'QPlayer_install');

add_action( 'init', 'QPlayer_add_jquery' );
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
    wp_enqueue_style( 'player', QPlayer_URL.'/css/player.css','',QPlayer_VER);

	echo '
		<div id="QPlayer" style="z-index:2016">
		<div id="pContent">
			<div id="player">
				<span class="cover"></span>
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
    echo '
        <script>
          var autoplay = '.(get_option('autoPlay')?1:0).';
          var playlist = ['.get_option('musicList').'];
          var isRotate = '.(get_option('rotate')?1:0).';
        </script> ' . "\n";
    wp_enqueue_script( 'marquee', QPlayer_URL.'/js/jquery.marquee.min.js','jquery',QPlayer_VER, true);
    wp_enqueue_script( 'player', QPlayer_URL.'/js/player.js','jquery',QPlayer_VER, true);

    if (get_option('js') != '') {
        echo '<script>'.get_option('js').'</script>' . "\n";
    }
}

?>