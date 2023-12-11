<?php
$module = array(
	'name' => __( 'Widget – Beitragsliste', 'wmd_msreader' ),
	'description' => __( 'Ermöglicht die Verwendung des Seitenleisten-Widgets, das die neuesten Beiträge anzeigt', 'wmd_msreader' ),
	'slug' => 'widget_recent_posts', 
	'class' => 'WMD_MSReader_Module_WidgetRecentPosts',
    'can_be_default' => false,
    'type' => 'wp-widget'
);

class WMD_MSReader_Module_WidgetRecentPosts extends WMD_MSReader_Modules {

	function init() {
        add_action('widgets_init', function() {
            return register_widget('wmd_msreader_post_list');
        });
    
        add_action('admin_footer-widgets.php', array($this, 'add_js'));
    }

    function add_js() {
        ?>
        <script type="text/javascript">
        (function($) {
            $(document).ready(function() {
                $('.widget-content').on('change', '.msreader_widget_recent_posts_select', function(event){
                    var parent = $(this).parents('.msreader_widget_recent_posts');

                    if(parent.find('.msreader_widget_recent_posts_select').val() == 'myclass')
                        parent.find('.msreader_widget_recent_posts_privacy_warning').show();
                    else
                        parent.find('.msreader_widget_recent_posts_privacy_warning').hide();
                })
            });
        })(jQuery);
        </script>
        <?php
    }

    function widget( $args, $instance ) {
        global $wmd_msreader;
        include_once($wmd_msreader->plugin['dir'].'includes/query.php');

        extract( $args );

        $title = isset($instance['title']) ? apply_filters( 'widget_title', $instance['title'] ) : '';
        $number = (is_numeric($instance['number']) ) ? $instance['number'] : 7;
        $show_date = $instance['show_date'] == 'on' ? true : false;
        $show_excerpt = (isset($instance['show_excerpt']) && $instance['show_excerpt'] == 'on') ? true : false;
        $show_author = (isset($instance['show_author']) && $instance['show_author'] == 'on') ? true : false;

        $query = new WMD_MSReader_Query();

        $arg_module = explode('|', $instance['module']);
        if(isset($arg_module[1])) {
            $instance['module'] = $arg_module[0];
            $instance['args'] = array($arg_module[1]);
        }
        else
            $instance['args'] = array();

        if(isset($wmd_msreader->modules[$instance['module']]) && isset($instance['user_id']) && $instance['user_id']) {
            $query->limit = $number;
            $query->user = $instance['user_id'];
            $query->args = $instance['args'];
            $query->load_module($wmd_msreader->modules[$instance['module']]);

            $posts = $query->get_posts();

            if(is_array($posts) && count($posts) > 0) {
                if(isset($before_widget))
                    echo $before_widget;

                if(isset($title) && isset($before_title))
                    echo $before_title;

                if ( $title )
                    echo $title;
                if(isset($title) && isset($after_title))
                    echo $after_title;

                    if(!isset($instance['remove_widget_class']) || !$instance['remove_widget_class'])
                        echo '<div class="widget_recent_entries">';
                            echo '<ul>';

                    foreach ($posts as $post) {
                        if(!$post)
                            continue;
                            
                        $time = strtotime($post->post_date_gmt) ? $post->post_date_gmt : $post->post_date;
                        $time = mysql2date(get_option('date_format'), $time, true);

                        echo '<li>';
                            echo '<a target="_blank" href="'.$wmd_msreader->modules['widget_recent_posts']->get_site_post_link($post->BLOG_ID, $post->ID).'">'.$post->post_title.'</a>';
                            if($show_date)
                                echo ' <span class="post-date">'.$time.'</span>';
                            if($show_excerpt) {
                                $content = wp_html_excerpt($post->post_excerpt, 55, '[...]');
                                if($content)
                                    echo ' <div class="post-excerpt rssSummary">'.$content.'</div>';
                                else
                                    echo '<br/>';
                            }
                            if($show_author)
                                echo ' <cite class="post-author"><small>'.__( 'Von ', 'wmd_msreader' ).$post->post_author_display_name.'</small></cite>';
                        echo '</li>';
                    }

                            echo '</ul>';
                    if(!isset($instance['remove_widget_class']) || !$instance['remove_widget_class'])
                        echo '</div>';
                if(isset($after_widget))
                	echo $after_widget;
            }
        } 
    }
}

// Widget for Subscribe
class wmd_msreader_post_list extends WP_Widget {
    //constructor
    function __construct() {
        $widget_ops = array( 'description' => __( 'Liste der neuesten Beiträge', 'wmd_msreader') );
        parent::__construct( false, __( 'Reader: Neueste Beiträge', 'wmd_msreader' ), $widget_ops );
    }

    /** @see WP_Widget::widget */
    function widget( $args, $instance ) {
        global $msreader_modules;

        $msreader_modules['widget_recent_posts']->widget( $args, $instance );
    }

    /** @see WP_Widget::update */
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title']  = strip_tags($new_instance['title']);
        $instance['number'] = strip_tags($new_instance['number']);
        $instance['number'] = $instance['number'] > 20 ? 20 : ($instance['number'] < 1 ? 1 : $instance['number']);
        $instance['show_date']  = strip_tags($new_instance['show_date']);
        $instance['show_excerpt']  = strip_tags($new_instance['show_excerpt']);
        $instance['show_author']  = strip_tags($new_instance['show_author']);
        $instance['module']  = strip_tags($new_instance['module']);
        if(!$instance['user_id'])
            $instance['user_id'] = get_current_user_id();

        return $instance;
    }

    /** @see WP_Widget::form */
    function form( $instance ) {
        global $msreader_helpers, $wmd_msreader;
        $options = $wmd_msreader->plugin['site_options'];

        $title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        $number = (isset( $instance['number'] ) && is_numeric($instance['number'])) ? esc_attr( $instance['number'] ) : 7;
        $show_date = isset( $instance['show_date'] ) ? esc_attr( $instance['show_date'] ) : '';
        $show_excerpt = isset( $instance['show_excerpt'] ) ? esc_attr( $instance['show_excerpt'] ) : '';
        $show_author = isset( $instance['show_author'] ) ? esc_attr( $instance['show_author'] ) : '';
        $current_module = isset( $instance['module'] ) ? esc_attr( $instance['module'] ) : '';

        $user_id = (isset($instance['user_id']) && $instance['user_id']) ? $instance['user_id'] : get_current_user_id();
        $user_name = get_userdata($user_id);
        $user_name = $user_name->user_login; 
        ?>
        <div class="msreader_widget_recent_posts">
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Titel', 'wmd_msreader' ) ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Anzahl der anzuzeigenden Beiträge:', 'wmd_msreader' ) ?></label>
                <input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" min="1" max="20" value="<?php echo $number; ?>" size="3">
            </p>
            <p>
                <input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" <?php checked( 'on', $show_date);?>>
                <label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Beitragsdatum anzeigen?', 'wmd_msreader' ) ?></label>
            </p>
            <p>
                <input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'show_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>" <?php checked( 'on', $show_excerpt);?>>
                <label for="<?php echo $this->get_field_id( 'show_excerpt' ); ?>"><?php _e( 'Auszug aus dem Beitrag anzeigen?', 'wmd_msreader' ) ?></label>
            </p>
            <p>
                <input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'show_author' ); ?>" name="<?php echo $this->get_field_name( 'show_author' ); ?>" <?php checked( 'on', $show_author);?>>
                <label for="<?php echo $this->get_field_id( 'show_author' ); ?>"><?php _e( 'Autor des Beitrags anzeigen?', 'wmd_msreader' ) ?></label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'module' ); ?>"><?php _e( "Reader's Beitragsquelle:", 'wmd_msreader' ) ?></label>
                <?php
                $blocked_modules = apply_filters('msreader_widget_recent_posts_blocked_modules', array());

                if($options['modules'] && is_array($options['modules'])) {
                    echo '<select id="'.$this->get_field_id( 'module' ).'" class="msreader_widget_recent_posts_select" name="'.$this->get_field_name( 'module' ).'">';
                    foreach ($wmd_msreader->modules as $slug => $module) {
                        if(in_array('query', $module->details['type']) && !in_array('query_args_required', $module->details['type']) && !in_array($module->details['slug'], $blocked_modules)) {
                            $module_title = isset($module->details['menu_title']) ? $module->details['menu_title'] : $module->details['name'];

                            echo '<option class="msreader_widget_recent_posts_select_option_'.$module->details['slug'].'" value="'.$module->details['slug'].'" '.selected( $current_module, $module->details['slug'], false ).'>'.$module_title.'</option>';
                        }
                    }

                    $arg_modules = apply_filters('msreader_widget_recent_posts_arg_modules', array());
                    foreach ($arg_modules as $key => $arg_module_details) {
                        echo '<option class="msreader_widget_recent_posts_select_option_'.$arg_module_details['class'].'" value="'.$arg_module_details['value'].'" '.selected( $current_module, $arg_module_details['value'], false ).'>'.$arg_module_details['title'].'</option>';
                    }
                    echo '</select>';
                }
                ?>
                <br/>
                <small><?php printf(__( 'Aus dem Reader von %s', 'wmd_msreader' ), $user_name); ?>.</small>
            </p>
            <p class="msreader_widget_recent_posts_privacy_warning" style="color:red;<?php echo $current_module != 'myclass' ? 'display:none' : '';?>">
                <?php _e( 'Bitte beachte die Privatsphäre der Benutzer.', 'wmd_msreader' ) ?>
            </p>
        </div>
        <?php
    }
}