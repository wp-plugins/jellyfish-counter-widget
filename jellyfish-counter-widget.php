<?php
/*
	Plugin Name: Jellyfish Counter Widget
	Plugin URI: http://strawberryjellyfish.com/wordpress-plugin-jellyfish-counter-widget/
	Description: Creates a widget with an odometer style counter that displays either a static number or animates up to a predefined value.
	Author: Rob Miller
	Version: 1.0
	Author URI: http://strawberryjellyfish.com/
*/

/*
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
	Online: http://www.gnu.org/licenses/gpl.txt
*/
?>
<?php

add_action('init', 'jellyfish_cw_action_init');
add_action('widgets_init', 'jellyfish_cw_create_widgets');

function jellyfish_cw_create_widgets() {
	register_widget('Jellyfish_Counter_Widget');
}

function jellyfish_cw_action_init() {
	load_plugin_textdomain('jellyfish_cw', false, dirname(plugin_basename(__FILE__)));
}

// Counter Widget class
class Jellyfish_Counter_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
				'counter_widget', 'Counter Widget', array('description' => 'Show an odometer style counter')
		);
	}

	// options form
	function form($instance) {
		// Retrieve previous values from instance or set default values if new
		$disable_title = $instance['disable_title'];
		$disable_depth = $instance['disable_depth'];
		$disable_tenths = $instance['disable_tenths'];
		$persist = ($instance['persist'] == 'true' || $instance['persist'] == 'on') ? 'on' : null;
		$init_timestamp = $instance['init_timestamp'];

		$start_value = (is_numeric($instance['start_value']) ? $instance['start_value'] : 0 );
		$end_value = (is_numeric($instance['end_value']) ? $instance['end_value'] : 100 );
		$animate_speed = (is_numeric($instance['animate_speed']) ? $instance['animate_speed'] : 50 );
		$direction = (!empty($instance['direction']) ? $instance['direction'] : 'up' );
		$persist_interval = (is_numeric($instance['persist_interval']) ? $instance['persist_interval'] : 1 );
		$number_of_digits = (is_numeric($instance['number_of_digits']) ? $instance['number_of_digits'] : 5 );
		$digit_height = (is_numeric($instance['digit_height']) ? $instance['digit_height'] : 40 );
		$digit_width = (is_numeric($instance['digit_width']) ? $instance['digit_width'] : 30 );
		$digit_padding = (is_numeric($instance['digit_padding']) ? $instance['digit_padding'] : 0 );
		$digit_bustedness = (is_numeric($instance['digit_bustedness']) ? $instance['digit_bustedness'] : 2 );

		$digit_style = (!empty($instance['digit_style']) ? $instance['digit_style'] : 'font-family: Courier New, Courier, monospace; font-weight: 900;' );
		$widget_title = (!empty($instance['widget_title']) ? $instance['widget_title'] : 'Counter' );
		$before_text = $instance['before_text'];
		$after_text = $instance['after_text'];

		// get the current count of an active continuous counter
		if (($persist == 'on') && !empty($init_timestamp)) {
			if ( $direction == 'down') {
				$current_value = $start_value - round((time() - $init_timestamp) / $persist_interval);
				if ($current_value < $end_value) {
					$current_value = $end_value;
				}
			} elseif ( $direction == 'up') {
				$current_value = $start_value + round((time() - $init_timestamp) / $persist_interval);
				if ($current_value > $end_value) {
					$current_value = $end_value;
				}
			}
		}

		?>
		<p>
			<label for="<?php echo $this->get_field_id('start_value'); ?>">
				<?php echo _e('Start Value:', 'jellyfish_cw'); ?>
				<input type="text"
					   id="<?php echo $this->get_field_id('start_value'); ?>"
					   name="<?php echo $this->get_field_name('start_value'); ?>"
					   value="<?php echo $start_value; ?>"
					   class="widefat"
					   />
			</label>
		<?php if (($persist == 'on') && (isset($current_value))) { ?>
			<span class="description">
				<?php _e('This counter is active, the current count is', 'jellyfish_cw'); ?> <?php echo $current_value; ?>.
				<?php _e('Changing the start value will restart the counter.', 'jellyfish_cw'); ?>
			</span>
		<?php } ?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('end_value'); ?>">
				<?php _e('End Value:', 'jellyfish_cw'); ?>
				<input type="text"
					   id="<?php echo $this->get_field_id('end_value'); ?>"
					   name="<?php echo $this->get_field_name('end_value'); ?>"
					   value="<?php echo $end_value; ?>"
					   class="widefat"
					   />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('direction'); ?>">
				<?php _e('Counter Type:', 'jellyfish_cw'); ?>
				<select id="<?php echo $this->get_field_id('direction'); ?>"
						name="<?php echo $this->get_field_name('direction'); ?>">
					<option value="up"
							<?php selected($direction, 'up'); ?>>
						<?php _e('Count Up', 'jellyfish_cw'); ?></option>
					<option value="static"
							<?php selected($direction, 'static'); ?>>
						<?php _e('Static', 'jellyfish_cw'); ?></option>
					<option value="down"
							<?php selected($direction, 'down'); ?>>
						<?php _e('Count Down', 'jellyfish_cw'); ?></option>
				</select>
			</label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $persist, 'on'); ?>
				id="<?php echo $this->get_field_id( 'persist' ); ?>"
				name="<?php echo $this->get_field_name( 'persist' ); ?>" />

			<label for="<?php echo $this->get_field_id('persist'); ?>">
				<?php _e('Continuous Counter', 'jellyfish_cw'); ?>
			</label>
			<br/>
			<span class="description">
				<?php _e('Counts continuously in the background, starts as soon as this widget is saved.', 'jellyfish_cw'); ?>
			</span>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('persist_interval'); ?>">
				<?php _e('Continuous Interval:', 'jellyfish_cw'); ?>
				<input type="text"
					   id="<?php echo $this->get_field_id('persist_interval'); ?>"
					   name="<?php echo $this->get_field_name('persist_interval'); ?>"
					   value="<?php echo $persist_interval; ?>"
					   size=6
					   />
				 <?php _e('seconds', 'jellyfish_cw'); ?>
			</label>
			<br/>
			<span class="description">
				<?php _e('How often a continuous style counter updates', 'jellyfish_cw'); ?>
			</span>
		</p>
		<hr>
		<h3 class="title"><?php _e('Appearance', 'jellyfish_cw'); ?></h3>
		<p>
			<label for="<?php echo $this->get_field_id('widget_title'); ?>">
				<?php _e('Widget Title:', 'jellyfish_cw'); ?>
				<input type="text"
					   id="<?php echo $this->get_field_id('widget_title'); ?>"
					   name="<?php echo $this->get_field_name('widget_title'); ?>"
					   value="<?php echo $widget_title; ?>"
					   class="widefat"
				/>
			</label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $disable_title, 'on'); ?>
				id="<?php echo $this->get_field_id( 'disable_title' ); ?>"
				name="<?php echo $this->get_field_name( 'disable_title' ); ?>" />
			<label for="<?php echo $this->get_field_id('disable_title'); ?>">
				<?php _e('Hide Title', 'jellyfish_cw'); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('before_text'); ?>">
				<?php _e('Text to display before counter:', 'jellyfish_cw'); ?></label>
			<textarea id="<?php echo $this->get_field_id('before_text'); ?>" class="widefat" name="<?php echo $this->get_field_name('before_text'); ?>"><?php echo $before_text; ?></textarea>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('after_text'); ?>">
				<?php _e('Text to display after counter:', 'jellyfish_cw'); ?></label>
			<textarea id="<?php echo $this->get_field_id('after_text'); ?>" class="widefat" name="<?php echo $this->get_field_name('after_text'); ?>"><?php echo $after_text; ?></textarea>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number_of_digits'); ?>">
				<?php _e('Number of Digits:', 'jellyfish_cw'); ?>
				<input type="text"
					   id="<?php echo $this->get_field_id('number_of_digits'); ?>"
					   name="<?php echo $this->get_field_name('number_of_digits'); ?>"
					   value="<?php echo $number_of_digits; ?>"
					   size=3
					   />
			</label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $disable_tenths, 'on'); ?>
				id="<?php echo $this->get_field_id( 'disable_tenths' ); ?>"
				name="<?php echo $this->get_field_name( 'disable_tenths' ); ?>" />
			<label for="<?php echo $this->get_field_id('disable_tenths'); ?>">
				<?php _e('Disable Tenths', 'jellyfish_cw'); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('animate_speed'); ?>">
				<?php _e('Animation Speed:', 'jellyfish_cw'); ?>
				<input type="text"
					   id="<?php echo $this->get_field_id('animate_speed'); ?>"
					   name="<?php echo $this->get_field_name('animate_speed'); ?>"
					   value="<?php echo $animate_speed; ?>"
					   size=3
				/>
			</label>
			<br/>
			<span class="description">
				<?php _e('A value (1-100). Not used for continuous style counters', 'jellyfish_cw'); ?>
			</span>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('digit_height'); ?>">
				<?php _e('Digit Height:', 'jellyfish_cw'); ?>
				<input type="text"
					   id="<?php echo $this->get_field_id('digit_height'); ?>"
					   name="<?php echo $this->get_field_name('digit_height'); ?>"
					   value="<?php echo $digit_height; ?>"
					   size=3
				/>
				<?php echo ' px'; ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('digit_width'); ?>">
				<?php _e('Digit Width:', 'jellyfish_cw'); ?>
				<input type="text"
					   id="<?php echo $this->get_field_id('digit_width'); ?>"
					   name="<?php echo $this->get_field_name('digit_width'); ?>"
					   value="<?php echo $digit_width; ?>"
					   size=3
				/>
				<?php echo ' px'; ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('digit_padding'); ?>">
				<?php _e('Digit Padding:', 'jellyfish_cw'); ?>
				<input type="text"
					   id="<?php echo $this->get_field_id('digit_padding'); ?>"
					   name="<?php echo $this->get_field_name('digit_padding'); ?>"
					   value="<?php echo $digit_padding; ?>"
					   size=3
				/>
				<?php echo ' px'; ?>
			</label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $disable_depth, 'on'); ?>
				id="<?php echo $this->get_field_id( 'disable_depth' ); ?>"
				name="<?php echo $this->get_field_name( 'disable_depth' ); ?>" />
			<label for="<?php echo $this->get_field_id('disable_depth'); ?>">
				<?php _e('Disable 3D effect', 'jellyfish_cw'); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('digit_bustedness'); ?>">
				<?php _e('Bustedness:', 'jellyfish_cw'); ?>
				<input type="text"
					   id="<?php echo $this->get_field_id('digit_bustedness'); ?>"
					   name="<?php echo $this->get_field_name('digit_bustedness'); ?>"
					   value="<?php echo $digit_bustedness; ?>"
					   size=3
				/>
			</label>
			<br/>
			<span class="description">Amount of digit misalignment</span>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('digit_style'); ?>">
				<?php _e('Digit Style:', 'jellyfish_cw'); ?>
				<input type="text"
					   id="<?php echo $this->get_field_id('digit_style'); ?>"
					   name="<?php echo $this->get_field_name('digit_style'); ?>"
					   value="<?php echo $digit_style; ?>"
					   class="widefat"
				/>
			</label>
			<br/>
			<span class="description">
				<?php _e('CSS entered here will alter the appearance of the digits', 'jellyfish_cw'); ?>
			</span>
		</p>
		<?php
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;

		// string values
		$instance['digit_style'] = sanitize_text_field($new_instance['digit_style']);
		$instance['widget_title'] = sanitize_text_field($new_instance['widget_title']);
		$instance['before_text'] = sanitize_text_field($new_instance['before_text']);
		$instance['after_text'] = sanitize_text_field($new_instance['after_text']);
		$instance['direction'] = sanitize_text_field($new_instance['direction']);

		// boolean values
		$instance['disable_title'] = $new_instance['disable_title'];
		$instance['persist'] = $new_instance['persist'];
		$instance['disable_tenths'] = $new_instance['disable_tenths'];
		$instance['disable_depth'] = $new_instance['disable_depth'];

		// numeric values
		if (is_numeric($new_instance['number_of_digits'])) {
			$instance['number_of_digits'] = intval($new_instance['number_of_digits']);
		}

		if (is_numeric($new_instance['digit_height'])) {
			$instance['digit_height'] = intval($new_instance['digit_height']);
		}

		if (is_numeric($new_instance['digit_width'])) {
			$instance['digit_width'] = intval($new_instance['digit_width']);
		}

		if (is_numeric($new_instance['digit_padding'])) {
			$instance['digit_padding'] = intval($new_instance['digit_padding']);
		}

		if (is_numeric($new_instance['digit_bustedness'])) {
			$instance['digit_bustedness'] = intval($new_instance['digit_bustedness']);
		}

		if (is_numeric($new_instance['end_value'])) {
			$instance['end_value'] = intval($new_instance['end_value']);
		}

		if (is_numeric($new_instance['animate_speed'])) {
			$instance['animate_speed'] = min(intval($new_instance['animate_speed']), 100);
		}

		if (is_numeric($new_instance['persist_interval'])) {
			$instance['persist_interval'] = intval($new_instance['persist_interval']);
		}

		if (is_numeric($new_instance['start_value']) && ($new_instance['start_value'] != $instance['start_value'])) {
			// start value has changed, time to restart the counter
			$instance['init_timestamp'] = time();
			$instance['start_value'] = $new_instance['start_value'];
		}

		if (empty($instance['init_timestamp'])) {
			$instance['init_timestamp'] = time();
		}
		return $instance;
	}

	function widget($args, $instance) {
		// queue javascript if widget is used
		if (is_active_widget(false, false, $this->id_base))
			wp_enqueue_script('odometer', plugins_url('js/odometer.js', __FILE__), array('jquery'), '', true);

		// Extract members of args array as individual variables
		extract($args);

		// these options were not in the first release so to play nice
		// we'll add some defaults here to avoid any undefined indexes

		$persist_interval = (isset($instance['persist_interval']) ?
						$instance['persist_interval'] : 1 );

		$init_timestamp = (isset($instance['init_timestamp']) ?
						$instance['init_timestamp'] : time() );
		//

		$disable_title = isset($instance['disable_title']) ? 'true' : 'false';
		$disable_tenths = isset($instance['disable_tenths']) ? 'true' : 'false';
		$tenths = $disable_tenths == 'true' ? 'false' : 'true' ;
		$disable_depth = isset($instance['disable_depth']) ? 'true' : 'false';
		$persist = isset($instance['persist']) ? 'true' : 'false';

		$number_of_digits = $instance['number_of_digits'];
		$start_value = $instance['start_value'];
		$end_value = $instance['end_value'];

		$animate_speed = $instance['animate_speed'];
		$wait_time = max(0, (100 - $animate_speed));

		$digit_height = $instance['digit_height'];
		$digit_width = $instance['digit_width'];
		$digit_padding = $instance['digit_padding'];
		$digit_bustedness = $instance['digit_bustedness'];
		$digit_style = $instance['digit_style'];
		$widget_title = $instance['widget_title'];
		$before_text = esc_attr($instance['before_text']);
		$after_text = esc_attr($instance['after_text']);
		$direction = $instance['direction'];

		if ($persist == 'true') {
			// calculate how may 'counts' have passed since initializing the counter widget
			// and update the start_value appropriately. If we have already passed the end_value
			// then we don't want to continue counting.
			if ( $direction == 'down') {
				$start_value = $start_value - round((time() - $init_timestamp) / $persist_interval);
				if ($start_value < $end_value) {
					$start_value = $end_value;
				}
			} elseif ( $direction == 'up') {
				$start_value = $start_value + round((time() - $init_timestamp) / $persist_interval);
				if ($start_value > $end_value) {
					$start_value = $end_value;
				}
			}
			$animate_speed = 100;
			$tenths = 'false';
		} else {
			$persist_interval = 1;
		}

		$persist_interval_ms = $persist_interval * 1000;

		if ($direction == 'static') {
			$end_value = $start_value;
		}

		// Begin widget output
		echo $before_widget;
		if ($disable_title == 'false') {
			echo $before_title;
			echo apply_filters('widget_title', $widget_title);
			echo $after_title;
		}
		if ($before_text) {
			echo '<div class="odometer-description">';
			echo apply_filters('the_content', $before_text);
			echo '</div>';
		}
		echo '<div id="odometer-' . $args['widget_id'] . '" class="odometer-widget"></div>';
		if ($after_text) {
			echo '<div class="odometer-description">';
			echo apply_filters('the_content', $after_text);
			echo '</div>';
		}
		// output javascript
		echo "<script type='text/javascript'>
				jQuery(document).ready(function() {
					var waitTime = $wait_time;
					var counterStartValue = $start_value;
					var counterEndValue = $end_value;
					var counterNow = $start_value;
					var direction = '$direction';
					var wholeNumber = 0;
					var persist = $persist;
					var div = document.getElementById('odometer-" . $args['widget_id'] . "');
					var myOdometer = new Odometer(div, {
						digits: $number_of_digits,
						tenths: $tenths,
						digitHeight: $digit_height,
						digitWidth: $digit_width,
						digitPadding: $digit_padding,
						fontStyle: '$digit_style',
						bustedness: $digit_bustedness,
						disableHighlights: $disable_depth
					});

					function updateOdometer() {
						if (persist) {
							if (direction =='down') {
								counterNow=counterNow-0.15;
							} else {
								counterNow=counterNow+0.15;
							}
							wholeNumber=wholeNumber+0.15;
							if (wholeNumber >= 1) {
								wholeNumber = 0;
								counterNow = Math.round(counterNow);
								waitTime = $persist_interval_ms;
							} else {
								waitTime = 1;
							}
						} else {
							if (direction =='down') {
								counterNow=counterNow-0.01;
							} else {
								counterNow=counterNow+0.01;
							}
						}
						if (( direction !='down' && (counterNow < counterEndValue)) || (direction =='down' && (counterNow > counterEndValue))) {
							myOdometer.set(counterNow);
							window.setTimeout(function() {
								updateOdometer();
							}, waitTime);
						}
					}

					if ( counterEndValue != counterStartValue) {
						myOdometer.set(counterStartValue);
						updateOdometer();
					} else {
						myOdometer.set(counterStartValue);
					}
				});
			</script>";
		// finish off widget
		echo $after_widget;
	}
}
?>