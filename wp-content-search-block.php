<?php
/*
Plugin Name: WP Content Search Block
Description: o block search engine bots, specifically, Google Bot from indexing/scanning the wp-content (wp-themes & wp-plugins) folder of Wordpress.
Version: 1.0.0
Author: Acutweb
Author URI: http://acutweb.com
*/
//https://stackoverflow.com/questions/10735766/block-all-bots-crawlers-spiders-for-a-special-directory-with-htaccess
if(!defined('ABSPATH'))
{
	exit;
}

$wpsecurepluginpath = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/';

$siteUrl = $_SERVER['HTTP_HOST'];

$siteUrl = str_replace('http://','',$siteUrl);

add_option('wpcsb_tre', false);

$wpcsb_tre = get_option('wpcsb_tre');

/**
 * This function makes sure Sociable is able to load the different language files from
 * the i18n subfolder of the Sociable directory
 **/
function wpsecure_init_locale(){
	global $wpsecurepluginpath;
	load_plugin_textdomain('wp-csb-directory', false, 'i18n');
}
add_filter('init', 'wpsecure_init_locale');

/**
 * Add the WpSecure menu to the Settings menu
 */
function wpsecure_admin_menu() {
	add_options_page('WP Content Search Block', 'WP Content Search Block', 8, 'wp-csb-directory', 'wpsafe_submenu');
}
add_action('admin_menu', 'wpsecure_admin_menu');

function wpsecure_write_htaccess($tre){
	global $siteUrl;
	$filename = ABSPATH.'/wp-content/.htaccess';
	
	/* 1. Protect .htaccess From Outside Access - wpsecuno */

	/* 3. Disable Directory Browsing  - wpsafe */
	$ht3 = '# Disable directory browsing - wpcsb'."\r\n";
	$ht3 .= 'RewriteEngine On'."\r\n";
	$ht3 .= 'RewriteCond %{HTTP_USER_AGENT} (googlebot|bingbot) [NC]'."\r\n";
	$ht3 .= 'RewriteRule .* - [R=403,L]'."\r\n";
	
	$wpsecure_msg = '';
	if (file_exists($filename)) {
		if (is_writable($filename)) {
			
			$stringafileht = file_get_contents($filename);
			if (preg_match("/\bwpcsb\b/i", $stringafileht)){ 
			$tre = false; 
			}

			$fp = fopen($filename, 'a');
			//fwrite($fp, "# BEGIN Safe directory\r\n");
			
			if ($tre) fwrite($fp, $ht3."\r\n");
			//fwrite($fp, "# END Safe directory\r\n");
			fclose($fp);
			//$wpsecure_msg = "The file $filename modified correctly";
		} else { $wpsecure_msg = "The file $filename is not writable"; }
	} else { 
		// This is the case where file doesn't exist
		$fp = fopen($filename, 'w');

		if ($tre) fwrite($fp, $ht3."\r\n");

		fclose($fp);
	}
	return $wpsecure_msg;
}

function wpsafe_submenu() {
	global $wpsecurepluginpath;
		$msg = "";
		// Check form submission and update options
		if ('wpsecure_submit' == $_POST['wpsecure_submit']) {

			update_option('wpcsb_tre', $_POST['wpcsb_tre']);
			
			$wpcsb_tre = get_option('wpcsb_tre');
			
			$msg = wpsecure_write_htaccess($wpcsb_tre);
		}
			// Carico i valori
			$wpcsb_tre = get_option('wpcsb_tre');	
?>
<div class="wrap" id="sm_div">
    <h2>WP Content disable directory from search engine bot</h2>
<?php
if ($msg) {
	?>
	<div id="message" class="error"><p><strong><?php echo $msg; ?></strong></p>
	</div>
<?php
	}	
?>
    <div style="clear:both";></div> 
</div>

<div id="poststuff" class="metabox-holder has-right-sidebar"> 

<div class="has-sidebar sm-padded" > 
					
		<div id="post-body-content" class="has-sidebar-content"> 

		<div class="meta-box-sortabless"> 
										
		<div id="sm_rebuild" class="postbox">
			<h3 class="hndle">
				<span>Safely disable directory browsing</span>
			</h3>
			<div class="inside">
					<form name="form1" method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>&amp;updated=true">
						<input type="hidden" name="wpsecure_submit" value="wpsecure_submit" />
						<ul>
							<li>
							<label for="wpcsb_tre">
								<input name="wpcsb_tre" type="checkbox" id="wpcsb_tre" value="1" <?php echo $wpcsb_tre?'checked="checked"':''; ?> />
								Disable Directory Browsing of the directory: wp-content/ (Recommended)
							</label>
							</li>
						</ul>
						<p>To remove the plugin's modification you need to browse to these directories and empty the .htaccess manually</p>
					   <p class="submit"> <input type="submit" value="Save &amp; Write" class="sm_button"/></p>
					</form>
					</div>
				</div>
			</div>
		</div>
</div> 
</div>
<?php
	}
?>