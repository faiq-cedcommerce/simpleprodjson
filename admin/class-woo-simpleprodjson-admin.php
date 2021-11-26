<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.cedcommerce.com
 * @since      1.0.0
 *
 * @package    Woo_Simpleprodjson
 * @subpackage Woo_Simpleprodjson/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Simpleprodjson
 * @subpackage Woo_Simpleprodjson/admin
 * @author     Faiq Masood <faiqmasood@cedcommerce.com>
 */
class Woo_Simpleprodjson_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_Simpleprodjson_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Simpleprodjson_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-simpleprodjson-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_Simpleprodjson_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Simpleprodjson_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-simpleprodjson-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function my_admin_menu() {
		add_menu_page( 'JSON Product Importer', 'JSON Product Importer', 'manage_options', 'ced_json_prodimport', array( $this, 'ced_json_product_show'), 'dashicons-tickets', 6  );
	}

	public function ced_json_product_show(){
	?>	
		<form action="" method="post" enctype="multipart/form-data">
			<input type="file" name="fileToUpload" id="fileToUpload">
			<input type="submit" value="Upload File" name="submittheform">
		</form>
		<?php	
			global $wp_filesystem;
			WP_Filesystem();
			$content_directory = $wp_filesystem->wp_content_dir() . 'uploads/';
			$wp_filesystem->mkdir( $content_directory . 'JSONCustomDirectory' );
			$target_dir_location = $content_directory . 'JSONCustomDirectory/';
	
			if(isset($_POST["submittheform"]) && isset($_FILES['fileToUpload'])) {
			
				$name_file = $_FILES['fileToUpload']['name'];
				$tmp_name = $_FILES['fileToUpload']['tmp_name'];
			
				if( move_uploaded_file( $tmp_name, $target_dir_location.$name_file ) ) {
					echo "File was successfully uploaded";
				} else {
					echo "The file was not uploaded";
				}
			
			}

			define( 'FILE_TO_IMPORT', $target_dir_location.$name_file );
			if ( ! file_exists( FILE_TO_IMPORT ) ) :
				die( 'Unable to find ' . FILE_TO_IMPORT );
			endif;	
			
			$content 			= file_get_contents(FILE_TO_IMPORT);
			$products_data 		= json_decode($content,true);
			// echo '<pre>';
			// print_r($products_data['ActiveList']['ItemArray']['Item']);
			// echo '</pre>';
			$product = [];
			for($i=0;$i<count($products_data['ActiveList']['ItemArray']['Item']);$i++){
				$product_id 				= 	wc_get_product_id_by_sku($products_data['ActiveList']['ItemArray']['Item'][$i]['SKU']);
				$product['name']			=	$products_data['ActiveList']['ItemArray']['Item'][$i]['Title'];
				$product['description']		=	'';
				$product['sku']				=	$products_data['ActiveList']['ItemArray']['Item'][$i]['SKU'];
				$product['selling_price'] 	=	$products_data['ActiveList']['ItemArray']['Item'][$i]['SellingStatus']['CurrentPrice'];
				$product['quantity'] 		=	$products_data['ActiveList']['ItemArray']['Item'][$i]['Quantity'];
				$product['img_url']			=	$products_data['ActiveList']['ItemArray']['Item'][$i]['PictureDetails']['GalleryURL'];
				if( $product_id > 0){
					break;
				}else{
						if(count($products_data['ActiveList']['ItemArray']['Item'][$i]['Variations']['Variation'])>0){
							$name 		= array();
							$value 		= array();
							$sku		= array();
							$quantity 	= array();
							$price		= array();
							$var_title	= array();
							if($products_data['ActiveList']['ItemArray']['Item'][$i]['Variations']['Variation']['VariationSpecifics']['NameValueList']['Name']==""){
								for($j=0; $j<count($products_data['ActiveList']['ItemArray']['Item'][$i]['Variations']['Variation']); $j++){
									array_push($name, $products_data['ActiveList']['ItemArray']['Item'][$i]['Variations']['Variation'][$j]['VariationSpecifics']['NameValueList']['Name']);
									array_push($value, $products_data['ActiveList']['ItemArray']['Item'][$i]['Variations']['Variation'][$j]['VariationSpecifics']['NameValueList']['Value']);	
									array_push($sku, $products_data['ActiveList']['ItemArray']['Item'][$i]['Variations']['Variation'][$j]['SKU']);
									array_push($quantity, $products_data['ActiveList']['ItemArray']['Item'][$i]['Variations']['Variation'][$j]['Quantity']);
									array_push($price, $products_data['ActiveList']['ItemArray']['Item'][$i]['Variations']['Variation'][$j]['StartPrice']);array_push($title, $products_data['ActiveList']['ItemArray']['Item'][$i]['Variations']['Variation'][$j]['VariationTitle']);													
								}
							}
							else{
								array_push($name, $products_data['ActiveList']['ItemArray']['Item'][$i]['Variations']['Variation']['VariationSpecifics']['NameValueList']['Name']);
								array_push($value, $products_data['ActiveList']['ItemArray']['Item'][$i]['Variations']['Variation']['VariationSpecifics']['NameValueList']['Value']);	
								array_push($sku, $products_data['ActiveList']['ItemArray']['Item'][$i]['Variations']['Variation']['SKU']);				array_push($quantity, $products_data['ActiveList']['ItemArray']['Item'][$i]['Variations']['Variation']['Quantity']);
								array_push($price, $products_data['ActiveList']['ItemArray']['Item'][$i]['Variations']['Variation']['StartPrice']);	
								array_push($title, $products_data['ActiveList']['ItemArray']['Item'][$i]['Variations']['Variation'][$j]['VariationTitle']);		
							}	
							$availableValue = $value;
							$availableKey   = array_unique($name);	
							$variation_data = array(
								'name'		=> $availableKey,
								'value'		=> $availableValue,
								'sku'		=> $sku,
								'quantity'	=> $quantity,
								'price'		=> $price,
								'title'		=> $title,
							);			
							$this->insert_variable_product( $product, $variation_data);								
						}else{
							$this->insert_simple_product( $product );	
						}
				}				
			}
			
	}
	public function insert_simple_product( $product){
		
		$post_id = wp_insert_post( 
			  array(
				  'post_title'   => $product['name'],
				  'post_type'    => 'product',
				  'post_status'  => 'publish',
				  'post_excerpt' => $product['description'],
				  'post_content' => $product['description'],
			  )
		  ); 
		  
		wp_set_object_terms( $post_id, 'simple', 'product_type' );
		update_post_meta($post_id, '_sku', $product['sku']);
		update_post_meta($post_id, '_regular_price', $product['selling_price']);
		update_post_meta($post_id, '_price', $product['selling_price']);
		if($product['quantity']>0){
			update_post_meta($post_id, '_stock', $product['quantity']);
			update_post_meta($post_id, '_stock_status', 'instock');
			update_post_meta($post_id, '_manage_stock', 'yes');
		}	
		if($product['img_url']!=""){
			$img_url = $product['img_url'];
			$this->insert_picture($post_id, $img_url);
		}

	}	
	public function insert_variable_product( $product, $variation_data){	
		$post_id = wp_insert_post( 
			  array(
				  'post_title'   => $product['name'],
				  'post_type'    => 'product',
				  'post_status'  => 'publish',
				  'post_excerpt' => $product['description'],
				  'post_content' => $product['description'],
			  )
		  ); 
		  
		wp_set_object_terms( $post_id, 'variable', 'product_type' );
		update_post_meta($post_id, '_sku', $product['sku']);
		update_post_meta($post_id, '_regular_price', $product['selling_price']);
		update_post_meta($post_id, '_price', $product['selling_price']);
		if($product['quantity']>0){
			update_post_meta($post_id, '_stock', $product['quantity']);
			update_post_meta($post_id, '_stock_status', 'instock');
			update_post_meta($post_id, '_manage_stock', 'yes');
		}	

		$product_attributes = array();
		$product_attributes[strtolower($variation_data['name'][0])] = array(
			'name' => strtolower($variation_data['name'][0]),
			'value' => implode('|', $variation_data['value']),
			'position' => 0,
			'is_visible' => 0,
			'is_variation' => 1,
			'is_taxonomy' => 0
		);
		update_post_meta($post_id, '_product_attributes', $product_attributes);
		for($i=0;$i<count($variation_data['value']);$i++) {
			$valueClean = preg_replace("/[^0-9a-zA-Z_-] +/", "", $variation_data['value'][$i]);
			$post_name = 'product-' . $post_id . '-variation-' . $valueClean;
			$my_post = array(
				'post_title' 	=> $variation_data['title'][$i],
				'post_name' 	=> $variation_data['title'][$i],
				'post_status' 	=> 'publish',
				'post_parent' 	=> $post_id,
				'post_type' 	=> 'product_variation',
				'guid' 			=> home_url() . '/?product_variation=' . $post_name
			);
			
			$attID = wp_insert_post($my_post);
			update_post_meta($attID, 'attribute_'.strtolower($variation_data['name'][0]), $valueClean);
			update_post_meta($attID, '_price', $variation_data['price'][$i]);
			update_post_meta($attID, '_regular_price', $variation_data['price'][$i]);
			update_post_meta($attID, '_sku', $variation_data['sku'][$i]);
			update_post_meta($attID, '_virtual', 'no');
			update_post_meta($attID, '_downloadable', 'no');
			if($variation_data['quantity'][$i]>0){
				update_post_meta($attID, '_manage_stock', 'yes');
				update_post_meta($attID, '_stock_status', 'instock');
				update_post_meta($attID, '_stock', $variation_data['quantity'][$i]);
			}
			
		}
		if($product['img_url']!=""){
			$img_url = $product['img_url'];
			$this->insert_picture($post_id, $img_url);
		}			
	}
	public function insert_picture($post_id, $img_url){
		$image_url        = $img_url; 
		$pathinfo 		  = pathinfo($image_url);
		$image_name       = $pathinfo['filename'].'.'.$pathinfo['extension'];
		$upload_dir       = wp_upload_dir(); 
		$image_data       = file_get_contents($image_url); 
		$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); 
		$filename         = basename( $unique_file_name ); 

		if( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}	
		file_put_contents( $file, $image_data );
		$wp_filetype = wp_check_filetype( $filename, null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		set_post_thumbnail( $post_id, $attach_id );				
	}
	
}

