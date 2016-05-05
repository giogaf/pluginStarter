<?php

/*
Plugin Name: portafolio
Plugin URI: http://giovanygafaro.com/
Description: testing custom post
Version: 0.1
Author: Giogaf
Author URI: http://giovanygafaro.com/
License: GPLv2 or later

*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

function portafolio_register()
{

    $labels = array(
        'name' => __('Portafolios', 'encuesta'),
        'singular_name' => __('portafolio', 'encuesta'),
        'add_new'=>_x('Nuevo','item del portafolio'),
		'add_new_item' => __('Nuevo item Portafolio '),
		'edit_item' => __('Editar Item Portafolio'),
		'new_item' => __('New Portfolio Item'),
		'view_item' => __('View Portfolio Item'),
		'search_items' => __('Search Portfolio'),
		'not_found' =>  __('Nothing found'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => '',
        'name_admin_bar'=>'Portafolio'
    );

    $arg = array(
        'labels'=>$labels,
        'public'=>true,
        'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		//'menu_icon' => get_stylesheet_directory_uri() . '/article16.png',
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => 81,
		'supports' => array('title','editor','thumbnail')
    );
    register_post_type('portafolio',$arg);
    //flush_rewrite_rules(); // soluciión a problema de permalink para carga de post

function cuenta_vistas_post($postID){
    $key='numero_vistas';
    $total=get_post_meta($postID, $key,true);
    if($total == ''){
        add_post_meta($postID, $key,'1'); // no 0, cuenta la primera vez
    }
    else{
        $total++;
        update_post_meta($postID, $key,$total);
    }
}


function conta_post_populares($post_id){
    if(is_single()){
        if(empty( $post_id)){
            global $post;
            $post_id=$post->ID;
        }
    }
    cuenta_vistas_post($post_id);

}

    add_filter('manage_posts_columns', "add_columna");// add columns
    add_action("manage_posts_custom_column", 'vista_columna');

    function add_columna($columna){
        $columna['vistas']= '# Vistas';
        return $columna;
    }

    function vista_columna($name){
        if($name='vistas'){
            echo (int) get_post_meta(get_the_ID(),'numero_vistas',true);
    }

    }


add_action('wp_head','conta_post_populares');

    $argTaxTecno = array(
        "hierarchical" => true,
        "label" => "Tecnologias",
        "singular_label" =>"Tecnología",
        "rewrite" => true
    );
    $argTaxAmb = array(
        "hierarchical" => true,
        "label" => "Ámbitos",
        "singular_label" =>"ámbito",
        "rewrite" => true
    );

    register_taxonomy('tecnologias','portafolio',$argTaxTecno );
    register_taxonomy('ambito','portafolio',$argTaxAmb );

}

add_action('init','portafolio_register');
add_action("admin_init", "campos_portafolio");

function campos_portafolio(){
    add_meta_box("year_completed-meta", "Año Realización", "pfolio_anio_realizado", "portafolio", "side", "low");
    add_meta_box("credits_meta", "Créditos", "pfolio_creditos", "portafolio", "normal", "low");
}

function pfolio_anio_realizado(){
    global $post;
    $custom = get_post_custom($post->ID);
  $year_completed = $custom["year_completed"][0];
  ?>
  <label>Year:</label>
  <input name="year_completed" value="<?php echo $year_completed; ?>" />
  <?php
}

function pfolio_creditos() {
    global $post;
    $custom = get_post_custom($post->ID);
  $designers = $custom["designers"][0];
  $developers = $custom["developers"][0];
  $producers = $custom["producers"][0];
  ?>
  <p><label>Designed By:</label><br />
  <textarea cols="50" rows="5" name="designers"><?php echo $designers; ?></textarea></p>
  <p><label>Built By:</label><br />
  <textarea cols="50" rows="5" name="developers"><?php echo $developers; ?></textarea></p>
  <p><label>Produced By:</label><br />
  <textarea cols="50" rows="5" name="producers"><?php echo $producers; ?></textarea></p>
  <?php
}

add_action('save_post', 'pfolio_guardar');

function pfolio_guardar(){
    global $post;
    update_post_meta($post->ID, "year_completed", $_POST["year_completed"]);
  update_post_meta($post->ID, "designers", $_POST["designers"]);
  update_post_meta($post->ID, "developers", $_POST["developers"]);
  update_post_meta($post->ID, "producers", $_POST["producers"]);
}


add_filter('manage_portafolio_posts_columns', "pfolio_columnas_head");// add columns
add_action("manage_portafolio_posts_custom_column", 'pfolio_columnas_content');

function pfolio_columnas_head($columns){
    $columns = array(
        "cb" => "<input type='checkbox' />",
        "title" => "Nombre del proyecto",
        "descripcion" => "Descripción de Proyecto",
        "anio_fin" => "Año de terminación",
        "tecnologias" => "Tecnologias",
  );

  return $columns;
}
function pfolio_columnas_content($column){
    global $post;

    switch ($column) {
        case "descripcion":
            the_excerpt();
            break;
        case "anio_fin":
            $custom = get_post_custom();
            echo $custom["year_completed"][0];
            break;
        case "tecnologias":
            echo get_the_term_list($post->ID, 'tecnologias', '', ', ','');
      break;
    }
}




class post_popular_widget extends WP_Widget
{

    // widget constructor
    public function __construct()
    {
        parent::__construct(
            'post_popular_widget',
            __('Post Popular Widget', 'tutsplustextdomain'),
            array(
                'classname' => 'tutsplustext_widget',
                'description' => __('Prueba con widgets, ver número vistas post populares', 'tutsplustextdomain')
            )
        );

        load_plugin_textdomain('tutsplustextdomain', false, basename(dirname(__FILE__)) . '/languages');

    }


    public function widget($args, $instance)
    {
        // outputs the content of the widget
    }

    public function form($instance)
    {
        $title = esc_attr($instance['title']);
        $message = esc_attr($instance['message']);
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('message'); ?>"><?php _e('Simple Message'); ?></label>
            <textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id('message'); ?>"
                      name="<?php echo $this->get_field_name('message'); ?>"><?php echo $message; ?></textarea>
        </p>

    <?php
    }
}
/*
    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        // processes widget options on save
    }
    */


function register_post_populares(){
    register_widget('post_popular_widget');
}

add_action('widgets_init','register_post_populares');



/*
register_activation_hook( __FILE__, array( 'Akismet', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'Akismet', 'plugin_deactivation' ) );

require_once( AKISMET__PLUGIN_DIR . 'class.portafolio.php' );
require_once( AKISMET__PLUGIN_DIR . 'class.akismet-widget.php' );

add_action( 'init', array( 'Akismet', 'init' ) );

if ( is_admin() ) {
	require_once( AKISMET__PLUGIN_DIR . 'class.akismet-admin.php' );
	add_action( 'init', array( 'Akismet_Admin', 'init' ) );
}

//add wrapper class around deprecated akismet functions that are referenced elsewhere
require_once( AKISMET__PLUGIN_DIR . 'wrapper.php' );

*/