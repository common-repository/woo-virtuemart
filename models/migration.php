<?php
/**
 * Created by PhpStorm.
 * User: ali
 * Date: 12/8/18
 * Time: 9:57 AM
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class VTOW_migration
{
    protected $_link;
    protected $_result;
    protected $_numRows;
    private $vtmHost;
    private $vtmDB;
    private $vtmUsr;
    private $vtmPass;
    private $vrm_prefix;
    function __construct(){
        $this->vtmHost = get_option('vtm_Host');
        $this->vtmDB = get_option('vtm_db');
        $this->vtmUsr = get_option('vtm_dbUser');
        $this->vtmPass = get_option('vtm_dbPass');
        $this->vrm_prefix = get_option('vrm_prefix');
        $this->_link = new mysqli($this->vtmHost, $this->vtmUsr, $this->vtmPass, $this->vtmDB);
        mysqli_set_charset($this->_link,'utf8');
        if(mysqli_connect_errno()) {
            echo "Virtuemart database connection error: " . mysqli_connect_errno();
            exit();
        }


        /*add_action('init',array($this,'notice'));
        add_action( 'admin_notices', array($this, 'show_notic') );*/
    }
    /*function notice(){
        if(isset($_POST['submit'])) {
            add_action( 'admin_notices', array($this, 'show_notic') );
        }
    }
    function show_notic() {
        ?><!--
        <div class="notice notice-success is-dismissible">
            <p>اکی شد.</p>
        </div>
        --><?php
/*    }*/
    private function categories(){
        global $wpdb;
        $query = "SELECT * FROM {$this->vrm_prefix}virtuemart_categories_fa_ir  ";
        $result = mysqli_query($this->_link, $query);
        $term_ids = array();
        $term_ids[0] = 0;
        while($row = mysqli_fetch_object($result)){
            //if(2 == $row->virtuemart_category_id)
            $wpdb->insert(
                $wpdb->prefix.'terms',
                array(
                    /*'term_id' => $row->virtuemart_category_id,*/
                    'name' => $row->category_name,
                    'slug' => $row->slug,
                ),
                array(
                    '%s',
                    '%s',
                )
            );
            $term_ids[$row->virtuemart_category_id] = $wpdb->insert_id;
        }
        echo 'product categories synced.<br>';
        echo "--------------------------------------------------------------------<br>";
        $tax_ids = $this->categories_relations($term_ids);
        update_option('tax_ids',$tax_ids);
        return $tax_ids;
    }
    private function categories_relations($term_ids){
        global $wpdb;
        $query = "SELECT * FROM {$this->vrm_prefix}virtuemart_category_categories";
        $result = mysqli_query($this->_link, $query);
        $tax_ids = array();
        while($row = mysqli_fetch_object($result)){


            //if(2 == $row->id)
            $wpdb->insert(
                $wpdb->prefix.'term_taxonomy',
                array(
                    'parent' => $term_ids[$row->category_parent_id],
                    'term_id' => $term_ids[$row->category_child_id],
                    'taxonomy' => 'product_cat',
                    'description' => '',
                    'count' => 0,
                ),
                array(
                    '%d',
                    '%d',
                    '%s',
                    '%s',
                    '%d',
                )
            );
            //print_r($wpdb->show_errors());
            $tax_ids[$row->category_child_id] = $wpdb->insert_id;
        }
        delete_option("product_cat_children");
        echo 'category relations synced.<br>';
        echo "--------------------------------------------------------------------<br>";
        return $tax_ids;
    }
    private function product_parent_attributes(){
        global $wpdb;
        $query = "SELECT * FROM {$this->vrm_prefix}virtuemart_customs";
        $result = mysqli_query($this->_link, $query);
        while($row = mysqli_fetch_object($result)){
            //if(2 == $row->id)
            $wpdb->insert(
                $wpdb->prefix.'woocommerce_attribute_taxonomies',
                array(
                    'attribute_name' => $row->custom_title,
                    'attribute_label' => $row->custom_title,
                    'attribute_type' => 'select',
                    'attribute_orderby' => 'menu_order',
                    'attribute_public' => 0,

                ),
                array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                )
            );
            $attribute_ids[] = $wpdb->insert_id;
            $this->product_sub_attributes($row->virtuemart_custom_id,$row->custom_title);
        }
        echo 'product attributes synced.<br>';
        echo "--------------------------------------------------------------------<br>";
        delete_option('_transient_wc_attribute_taxonomies');
        update_option('attribute_ids',$attribute_ids);
        return $attribute_ids;
    }
    private function product_sub_attributes($virtuemart_custom_id,$custom_title){
        global $wpdb;
        $query = "SELECT * FROM {$this->vrm_prefix}virtuemart_product_customfields WHERE virtuemart_custom_id = '{$virtuemart_custom_id}' GROUP BY customfield_value";
        $result = mysqli_query($this->_link, $query);
        while($row = mysqli_fetch_object($result)){
                $wpdb->insert(
                    $wpdb->prefix . 'terms',
                    array(
                        /*'term_id' => $row->virtuemart_customfield_id,*/
                        'name' => $row->customfield_value,
                        'slug' => urlencode($row->customfield_value),
                        'term_group' => 0,

                    ),
                    array(
                        '%s',
                        '%s',
                        '%d',
                    )
                );
                $term_id = $wpdb->insert_id;
                // مشکلی که به وجود میاد اینه که ممکنه با term_id که دارم وارد میکنیم قبلا خود وردپرس و ووکامرس برای نوع محصولات یا ... ساخته باشن. در این صورت تداخل پیش میاد و اونها هم درون زیر ویژگی ها نشون داده میشن.
                $wpdb->insert(
                    $wpdb->prefix . 'term_taxonomy',
                    array(
                        /*'term_id' => $row->virtuemart_customfield_id,*/
                        'term_id' => $term_id,
                        'taxonomy' => 'pa_' . strtolower($custom_title),
                        'description' => '',
                        'parent' => 0,
                        'count' => 0,
                    ),
                    array(
                        '%d',
                        '%s',
                        '%s',
                        '%d',
                        '%d',
                    )
                );


        }
    }

    private function _uploadImageToMediaLibrary($postID, $url, $alt = "blabla") {

        require_once(ABSPATH."wp-load.php");
        require_once(ABSPATH."wp-admin/includes/image.php");
        require_once(ABSPATH."wp-admin/includes/file.php");
        require_once(ABSPATH."wp-admin/includes/media.php");

        $tmp = download_url( $url );
        $desc = $alt;
        $file_array = array();

        // Set variables for storage
        // fix file filename for query strings
        preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches);
        $file_array['name'] = basename($matches[0]);
        $file_array['tmp_name'] = $tmp;

        // If error storing temporarily, unlink
        if ( is_wp_error( $tmp ) ) {
            @unlink($file_array['tmp_name']);
            $file_array['tmp_name'] = '';
        }

        // do the validation and storage stuff
        $id = media_handle_sideload( $file_array, $postID, $desc);

        // If error storing permanently, unlink
        if ( is_wp_error($id) ) {
            @unlink($file_array['tmp_name']);
            return null;
        }

        return $id;
    }
    private function products($start,$count){
        global $wpdb;
        $query = "SELECT * FROM {$this->vrm_prefix}virtuemart_products_fa_ir LIMIT $start,$count";
        $result = mysqli_query($this->_link, $query);
        $product_ids = array();
        while($row = mysqli_fetch_object($result)){
            if(!isset($product_ids['wp'][$row->virtuemart_product_id])) {
                $query = "SELECT * FROM {$this->vrm_prefix}virtuemart_products WHERE virtuemart_product_id = '{$row->virtuemart_product_id}'";
                $meta_result = mysqli_query($this->_link, $query);
                $meta = mysqli_fetch_object($meta_result);

                $query = "SELECT * FROM {$this->vrm_prefix}virtuemart_product_prices WHERE virtuemart_product_id = '{$row->virtuemart_product_id}'";
                $price_result = mysqli_query($this->_link, $query);
                $price = mysqli_fetch_object($price_result);

                $post_status = ($meta->published == 1)?'publish':'draft';
                $_manage_stock = ($meta->product_in_stock > 0)?'yes':'no';
                $args = array(
                    'post_author' => 1,
                    'post_date' => $meta->created_on,
                    'post_modified' => $meta->modified_on,
                    'post_content' => $row->product_desc,
                    'post_title' => $row->product_name,
                    'post_name' => $row->slug.'-detail',
                    'post_excerpt' => $row->product_s_desc,
                    'post_status' => $post_status,
                    'post_type' => 'product',
                    'meta_input' => array(
                        '_thumbnail_id' => 1,
                        '_stock_status' => 'instock',
                        '_regular_price' => intval($price->product_price),
                        '_sku' => $meta->product_sku,
                        '_price' => intval($price->product_price),
                        'total_sales' => $meta->product_ordered,
                        '_manage_stock' => $_manage_stock,
                        '_stock' => $meta->product_in_stock,
                    ),
                );
                $post_id = wp_insert_post($args);

                $query = "SELECT * FROM {$this->vrm_prefix}virtuemart_product_medias RIGHT JOIN {$this->vrm_prefix}virtuemart_medias ON {$this->vrm_prefix}virtuemart_medias.virtuemart_media_id = {$this->vrm_prefix}virtuemart_product_medias.virtuemart_media_id WHERE {$this->vrm_prefix}virtuemart_product_medias.virtuemart_product_id = '{$row->virtuemart_product_id}'";
                $media_result = mysqli_query($this->_link, $query);
                $media_ids = array();
                while($media = mysqli_fetch_object($media_result)){
                    $tmp = $this->_uploadImageToMediaLibrary($post_id,$media->file_url,$media->file_meta);
                    if($tmp)
                    $media_ids[] = $tmp;
                }
                update_post_meta($post_id,'_product_image_gallery',implode(',',$media_ids));
                update_post_meta($post_id,'_thumbnail_id',$media_ids[0]);
                set_post_thumbnail( $post_id, $media_ids[0] );
                $post_ids[$row->virtuemart_product_id] = $wpdb->insert_id;
                $product_ids['wp'][$row->virtuemart_product_id] = $post_id;
                $product_ids['joomla'][$row->virtuemart_product_id] = $row->virtuemart_product_id;
            }

        }
        $count += $start;
        echo "products $start to $count synced.<br>";
        echo "--------------------------------------------------------------------<br>";
        return $product_ids;
    }
    
    private function products_categories($tax_ids,$product_ids){
        global $wpdb;
        $array = implode("','",$product_ids['joomla']);
        $query = "SELECT * FROM {$this->vrm_prefix}virtuemart_product_categories WHERE virtuemart_product_id IN ('".$array."') ";
        $result = mysqli_query($this->_link, $query);
        while($row = mysqli_fetch_object($result)){
            //if(2 == $row->id)
            $wpdb->insert(
                $wpdb->prefix.'term_relationships',
                array(
                    'object_id' => $product_ids['wp'][$row->virtuemart_product_id],
                    'term_taxonomy_id' => $tax_ids[$row->virtuemart_category_id],
                    'term_order' => 0,

                ),
                array(
                    '%d',
                    '%d',
                    '%d',
                )
            );
            //print_r($wpdb->show_errors());
            if($wpdb->show_errors()) $wpdb->show_errors();
        }
    }
    private function inputError(){
        echo 'your input is wrong. please enter valid numbers.';
        exit;
    }
    function migration(){
        if(!is_numeric($_POST['offset']) || !is_numeric($_POST['count'])) $this->inputError();
        $tax_ids = get_option('tax_ids');
        if(!$tax_ids)
            $tax_ids = $this->categories();
        $attribute_ids = get_option('attribute_ids');
        if(!$attribute_ids)
            $attribute_ids = $this->product_parent_attributes();
        $offset = (isset($_POST['offset']))?$_POST['offset']:0;
        $count = (isset($_POST['count']))?$_POST['count']:30;
        $product_ids = $this->products($offset,$count);
        $this->products_categories($tax_ids,$product_ids);


    }


}



