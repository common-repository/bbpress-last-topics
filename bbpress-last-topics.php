<?php
/*
Plugin Name: BBpress last topic
Plugin URI: http://eris.nu/wordpress/bbpress/
Description: Shows last topic of your BBpress blog 
Author: Jaap Marcus
Version: 0.2.0
Author URI: http://www.schipbreukeling.nl

/*  Copyright 2008 - 2010  Jaap Marcus  (email : http://schipbreukeling.nl/contact/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
	function register_bb_last(){
		register_setting( 'bb_last', 'bb_last_settings' );
		register_setting( 'bb_last', 'bb_last_url' );
		register_setting( 'bb_last', 'bb_last_prefix' );
		register_setting( 'bb_last', 'bb_last_host' );
		register_setting( 'bb_last', 'bb_last_user' );
		register_setting( 'bb_last', 'bb_last_password' );
		register_setting( 'bb_last', 'bb_last_name' );		

	}
	
	include(dirname(__FILE__).'/function.bb_since.php');
	
	
	class bb_last_topics extends WP_widget{
		function bb_last_topics(){
		$widget_ops = array( 'classname' => 'bb_last_topics', 'description' => 'Shows last created new topic' );
		$this->WP_Widget( 'bb_last_topics', 'BBpress last topic', $widget_ops);
		}
		
		function widget($args,$instance){
		global $wpdb;
		if($instance['topics'] == 0){
			$instance['topics'] = 5;
		}
		if(empty($instance['showdate'])){
			$instance['showdate'] = 0;
		}
		if(empty($instance['dateformat'])){
			$instance['dateformat'] = 'Y-m-d h:i';
		}		
		if(empty($instance['title'])){
			$instance['title'] = 'Last forum topics';
		}
		
			//get all the options first
			$database = get_option('bb_last_settings');
			if($database == 1){
				//use the same version as wordpress
				$prefix = get_option('bb_last_prefix');
				$url = get_option('bb_last_url');
			   	$sql = 'SELECT topic_id, topic_title, topic_poster_name, topic_poster, topic_start_time FROM '.$prefix.'topics WHERE topic_status = 0 ORDER BY topic_start_time DESC LIMIT 0 ,'.$instance['topics'];
				$topics = $wpdb -> get_results ($sql, ARRAY_A);
			}else{
				//connect to a external database
				$username = get_option('bb_last_user');
				$password = get_option('bb_last_password');
				$host = get_option('bb_last_host');
				$name = get_option('bb_last_name');
				$prefix = get_option('bb_last_prefix');
				$url = get_option('bb_last_url');
				// $dbuser, $dbpassword, $dbname, $dbhost 
				$bbdb = new wpdb($username,$password,$name,$host);
				if($bbdb){
			   	$sql = 'SELECT topic_id, topic_title, topic_poster_name, topic_poster, topic_start_time FROM '.$prefix.'topics WHERE topic_status = 0 ORDER BY topic_start_time DESC LIMIT 0 ,'.$instance['topics'];
					$topics = $bbdb -> get_results ($sql, ARRAY_A);				
				}else{
					$topics = array();
				}
			}
    	echo $args['before_widget'].$args['before_title'].$instance['title'].$args['after_title'].'
   		<ul>';
		
		foreach($topics as $topic){
			$text = '<li>'.$instance['template'].'</li>';
			if($instance['showdate'] == 0){
				$date = bb_since($topic['topic_start_time']);
			}else{
				$date = get_date_from_gmt($topic['topic_start_time']);
			}
			$text = str_replace(
			array('%url%', '%topic%' ,'%name%', '%date%','%profile%'),
			array($url.'topic.php?id='.$topic['topic_id'], $topic['topic_title'], $topic['topic_poster_name'], 
			$date, $url.'profile.php?id='.$topic['topic_poster']),
			$text);
			echo $text;
		}
   		echo '
    	</ul>'.$args['after_widget'];
	
		}
		
		function update( $new_instance, $old_instance ) {			
			$old_instance['title'] = $new_instance['title'];	
			if(is_numeric($new_instance['topics'])){
			$old_instance['topics'] = $new_instance['topics'];
			}
			$old_instance['showdate'] = $new_instance['showdate'];			
			$old_instance['dateformat'] = trim($new_instance['dateformat']);
			$old_instance['template'] = trim($new_instance['template']);
			return $old_instance;
		}
		
		function form($instance){
		if(empty($instance['title'])){
			$instance['title'] = 'Last forum topics';
		}
		if(empty($instance['topics'])){
			$instance['topics'] = 5;
		}
		if($instance['topics'] == 0){
			$instance['topics'] = 5;
		}
		if(empty($instance['showdate'])){
			$instance['showdate'] = 0;
		}
		if(empty($instance['dateformat'])){
			$instance['dateformat'] = 'Y-m-d h:i';
		}
		if(empty($instance['template'])){
			$instance['template'] = '<a href="%url%">%topic%</a> %name% %date% ago';
		}
		//print_r($instance);
		?>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:<br /> 
		<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" 
		value="<?php echo $instance['title']; ?>" /></label><br />

		<label for="<?php echo $this->get_field_id( 'topics' ); ?>">Number of topics: 
		<input type="text" id="<?php echo $this->get_field_id( 'topics' ); ?>" name="<?php echo $this->get_field_name( 'topics' ); ?>" 
		value="<?php echo $instance['topics']; ?>" /></label><br />
		<label for="<?php echo $this->get_field_id( 'showdate' ); ?>">Show date as:<br /> 
		<select name="<?php echo $this->get_field_name( 'showdate' ); ?>" id="<?php echo $this->get_field_id( 'showdate' ); ?>">
		<?php 
		$options = array(0 => 'Fressness', 1 => 'String');
		for($i = 0; $i < count($options); $i++){
		//echo $option;
			if($i == $instance['showdate']){
				echo '<option value="'.$i.'" selected="selected">'.$options[$i].'</option>';
			}else{
				echo '<option value="'.$i.'">'.$options[$i].'</option>';
			}
		}

		?>
		</select></label><br />		
		<label for="<?php echo $this->get_field_id( 'dateformat' ); ?>">Date string: <br />
		<input type="text" id="<?php echo $this->get_field_id( 'dateformat' ); ?>" name="<?php echo $this->get_field_name( 'dateformat' ); ?>" 
		value="<?php echo $instance['dateformat']; ?>" /></label><br />
		<label for="<?php echo $this->get_field_id( 'template' ); ?>">Template: <br />
		<input type="text" id="<?php echo $this->get_field_id( 'template' ); ?>" size="40" name="<?php echo $this->get_field_name( 'template' ); ?>" 
		value="<?php echo htmlentities($instance['template']);?>"/></label><br />
		<table>
			<tr><td>%url%</td><td>Url of topic</td></tr>
			<tr><td>%topic%</td><td>Title of topic</td></tr>
			<tr><td>%name%</td><td>Topic poster</td></tr>
			<tr><td>%profile%</td><td>Profile of topic poster</td></tr>
			<tr><td>%date%</td><td>Date or freshness</td></tr>
		</table>
<?php
		}

	}
	
	class bb_last_posts extends WP_widget{
		function bb_last_posts(){
		$widget_ops = array( 'classname' => 'bb_last_posts', 'description' => 'Shows last active topic' );
		$this->WP_Widget( 'bb_last_posts', 'BBpress last posts', $widget_ops);
		}
		
		function widget($args,$instance){
		global $wpdb;
		if($instance['topics'] == 0){
			$instance['topics'] = 5;
		}
		if(empty($instance['showdate'])){
			$instance['showdate'] = 0;
		}
		if(empty($instance['dateformat'])){
			$instance['dateformat'] = 'Y-m-d h:i';
		}		
		if(empty($instance['title'])){
			$instance['title'] = 'Last forum posts';
		}		
			//get all the options first
			$database = get_option('bb_last_settings');
			if($database == 1){
				//use settings of wordpress cheap and easy
				$prefix = get_option('bb_last_prefix');
				$url = get_option('bb_last_url');
				$sql = 'SELECT topic_id, topic_title, topic_last_poster_name, topic_last_poster, topic_time FROM '.$prefix.'topics WHERE topic_status = 0 ORDER BY topic_time DESC LIMIT 0 ,'.$instance['topics'];
				$topics = $wpdb -> get_results ($sql, ARRAY_A);
			}else{
				//use settings of bbpress and connect to external database
				$username = get_option('bb_last_user');
				$password = get_option('bb_last_password');
				$host = get_option('bb_last_host');
				$name = get_option('bb_last_name');
				$prefix = get_option('bb_last_prefix');
				$url = get_option('bb_last_url');
				// $dbuser, $dbpassword, $dbname, $dbhost 
				$bbdb = new wpdb($username,$password,$name,$host);
				if($bbdb){
				$sql = 'SELECT topic_id, topic_title, topic_last_poster_name, topic_last_poster, topic_time FROM '.$prefix.'topics WHERE topic_status = 0 ORDER BY topic_time DESC LIMIT 0 ,'.$instance['topics'];
					$topics = $bbdb -> get_results ($sql, ARRAY_A);
					$topics = $bbdb -> get_results ($sql, ARRAY_A);				
				}else{
					$topics = array();
				}
			}	
    	echo $args['before_widget'].$args['before_title'].$instance['title'].$args['after_title'].'
   		<ul>';
		
		foreach($topics as $topic){
			$text = '<li>'.$instance['template'].'</li>';
			if($instance['showdate'] == 0){
				$date = bb_since($topic['topic_time']);
			}else{
				$date = get_date_from_gmt($topic['topic_time']);
			}
			$text = str_replace(
			array('%url%', '%topic%' ,'%name%', '%date%','%profile%'),
			array($url.'topic.php?id='.$topic['topic_id'], $topic['topic_title'], $topic['topic_last_poster_name'], 
			$date, $url.'profile.php?id='.$topic['topic_last_poster']),
			$text);
			echo $text;
		}
   		echo '</ul>'.$args['after_widget'];
		}
		
		function update( $new_instance, $old_instance ) {
			$old_instance['title'] = $new_instance['title'];
			if(is_numeric($new_instance['topics'])){
			$old_instance['topics'] = $new_instance['topics'];
			}
			$old_instance['showdate'] = $new_instance['showdate'];			
			$old_instance['dateformat'] = trim($new_instance['dateformat']);
			$old_instance['template'] = trim($new_instance['template']);
			return $old_instance;
		}
		
		function form($instance){
		if(empty($instance['title'])){
			$instance['title'] = 'Last forum posts';
		}
		if(empty($instance['topics'])){
			$instance['topics'] = 5;
		}
		if($instance['topics'] == 0){
			$instance['topics'] = 5;
		}
		if(empty($instance['showdate'])){
			$instance['showdate'] = 0;
		}
		if(empty($instance['dateformat'])){
			$instance['dateformat'] = 'Y-m-d h:i';
		}
		if(empty($instance['template'])){
			$instance['template'] = '<a href="%url%">%topic%</a> %date% ago';
		}
		//print_r($instance);
		?>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:<br /> 
		<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" 
		value="<?php echo $instance['title']; ?>" /></label><br />

		<label for="<?php echo $this->get_field_id( 'topics' ); ?>">Number of topics: 
		<input type="text" id="<?php echo $this->get_field_id( 'topics' ); ?>" name="<?php echo $this->get_field_name( 'topics' ); ?>" 
		value="<?php echo $instance['topics']; ?>" /></label><br />
		<label for="<?php echo $this->get_field_id( 'showdate' ); ?>">Show date as:<br /> 
		<select name="<?php echo $this->get_field_name( 'showdate' ); ?>" id="<?php echo $this->get_field_id( 'showdate' ); ?>">
		<?php 
		$options = array(0 => 'Fressness', 1 => 'String');
		for($i = 0; $i < count($options); $i++){
		//echo $option;
			if($i == $instance['showdate']){
				echo '<option value="'.$i.'" selected="selected">'.$options[$i].'</option>';
			}else{
				echo '<option value="'.$i.'">'.$options[$i].'</option>';
			}
		}

		?>
		</select></label><br />		
		<label for="<?php echo $this->get_field_id( 'dateformat' ); ?>">Date string: <br />
		<input type="text" id="<?php echo $this->get_field_id( 'dateformat' ); ?>" name="<?php echo $this->get_field_name( 'dateformat' ); ?>" 
		value="<?php echo $instance['dateformat']; ?>" /></label><br />
		<label for="<?php echo $this->get_field_id( 'template' ); ?>">Template: <br />
		<input type="text" id="<?php echo $this->get_field_id( 'template' ); ?>" size="40" name="<?php echo $this->get_field_name( 'template' ); ?>" 
		value="<?php echo htmlentities($instance['template']);?>"/></label><br />
		<table>
			<tr><td>%url%</td><td>Url of topic</td></tr>
			<tr><td>%topic%</td><td>Title of topic</td></tr>
			<tr><td>%name%</td><td>Topic poster</td></tr>
			<tr><td>%profile%</td><td>Profile of topic poster</td></tr>
			<tr><td>%date%</td><td>Date or freshness</td></tr>
		</table>
		<?php
		}
	}

function bb_last_topic_admin(){
?>
<div class="wrap">
<h2><?php _e('Settings');?></h2>
		<form method="post" action="options.php">
		<table class="form-table">
			<tr><td  colspan="2"><?php _e('When database of BB press is the same database as this wordpress please check the checkbox. This plugin will use the settings of the wordpress install');?></td></tr>
			<tr valign="top">
				<th scope="row"><?php _e('Use same database settings');?></th>
				<td><input type="checkbox" name="bb_last_settings" value="1"  <?php if(get_option('bb_last_settings') == 1){ echo 'checked="checked"';} ?>/></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('URL BBpress install');?>:</th>
				<td><input type="text" name="bb_last_url" value="<?php echo get_option('bb_last_url');?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('BBpress prefix');?>:</th>
				<td><input type="text" name="bb_last_prefix" value="<?php echo get_option('bb_last_prefix');?>" /></td>
			</tr>
			<tr><td colspan="2"><?php _e('When database settings are not the same as this wordpress install please put the settings down');?></td></tr>			
			<tr valign="top">
				<th scope="row"><?php _e('Database host');?>:</th>
				<td><input type="text" name="bb_last_host" value="<?php echo get_option('bb_last_host');?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Database username');?>:</th>
				<td><input type="text" name="bb_last_user" value="<?php echo get_option('bb_last_user');?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Database password');?>:</th>
				<td><input type="text" name="bb_last_password" value="<?php echo get_option('bb_last_password');?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Database name');?>:</th>
				<td><input type="text" name="bb_last_name" value="<?php echo get_option('bb_last_name');?>" /></td>
			</tr>

		</table>
		<?php settings_fields( 'bb_last' );?>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
		</p>

</form>
</div>
<?
}
	function load_bb_last_topic_admin_menu(){
	add_options_page('BB press last topic', 'BBpress Last topics', 'manage_options', 'bb-last', 'bb_last_topic_admin');
	add_action( 'admin_init', 'register_bb_last' );
	}


/* Add our function to the widgets_init hook. */
add_action( 'admin_menu', 'load_bb_last_topic_admin_menu');
add_action( 'widgets_init', 'load_bb_last' );

/* Function that registers our widget. */
function load_bb_last() {
	register_widget( 'bb_last_topics' );
	register_widget( 'bb_last_posts' );	
}
?>