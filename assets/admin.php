<?php

$postlink = array();

// Add feed as admin notice
add_action( 'admin_notices', 'writing_prompt_notice' );
function writing_prompt_notice() {
	global $pagenow;
	if ( $pagenow == 'post-new.php' ) {
		include_once( ABSPATH . WPINC . '/feed.php' );

		// Create refresh time filter
		function refresh_interval( $seconds ) {
			return 7200;
		}

		// Add refresh time filter
		add_filter( 'wp_feed_cache_transient_lifetime' , 'refresh_interval' );

		// Fetch RSS feed
		$rss = fetch_feed( 'http://thewritepractice.com/feed/rss' );

		// Parse RSS feed
		if ( ! is_wp_error( $rss ) )  {
			$maxitems = $rss->get_item_quantity( 1 );
			$rss_items = $rss->get_items( 0, $maxitems );
		}

		function get_string_between($string, $start, $end){
			$string = " ".$string;
			$ini = strpos($string,$start);
			if ($ini == 0) return "";
			$ini += strlen($start);   
			$len = strpos($string,$end,$ini) - $ini;
			return substr($string,$ini,$len);
		}

		foreach ( $rss_items as $item ) {
			global $postlink;

			$title = $item->get_title();
			$content = $item->get_content();
			$link = $item->get_permalink();
			$practice = get_string_between($content, '<h2><b>PRACTICE</b></h2>', '</div>');
			echo "<div class='writing-prompt'><a href='http://thewritepractice.com' title='The Write Practice'><img class='wp-pen' src='" . plugins_url( '/images/The-Write-Practice-Pen-Logo.png' , __FILE__ ) . "' /></a><h2>The Write Practice's Daily Prompt</h2>";
			echo $practice;
			echo '<strong><a href="' . $link . '">' . $title . '</a></strong>';
			echo '<br><label class="link-check"><input type="checkbox" name="link" value="include" checked>Link to the original post?</label>';
			echo '</div>';

			$postlink[] = $title;
			$postlink[] = $link;
			print_r($postlink);

			return $postlink;

		}

		// Remove refresh time filter
		remove_filter( 'wp_feed_cache_transient_lifetime' , 'refresh_interval' );

	}
}

// If box is checked filter saved post and add link
function writing_prompt_save_link( $content ) {

  $content = $content . '<a href="' . $postlink[1] . '">' . $postlink[0] . '</a>';
  return $content;
}

add_filter( 'content_save_pre', 'writing_prompt_save_link', 10, 1 );
