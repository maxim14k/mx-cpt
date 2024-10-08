<?php

// Custom Post Type car
function create_car_post_type() {
    $labels = array(
        'name' => 'Cars',
        'singular_name' => 'Car',
        'menu_name' => 'Cars',
        'name_admin_bar' => 'Car',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Car',
        'new_item' => 'New Car',
        'edit_item' => 'Edit Car',
        'view_item' => 'View Car',
        'all_items' => 'All Cars',
        'search_items' => 'Search Cars',
        'not_found' => 'No cars found',
        'not_found_in_trash' => 'No cars found in Trash',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-car',
        'supports' => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => true,
    );

    register_post_type('car', $args);

}

add_action('init', 'create_car_post_type');


// Enable custom fields support for the Car post type
add_action('admin_init', function() {
    add_post_type_support('car', 'custom-fields');
});


// Register custom taxonomy 'Body' for the custom post type 'Car'
function create_body_taxonomy() {
    $labels = array(
        'name'              => 'Body',
        'singular_name'     => 'Body',
        'search_items'      => 'Search Body',
        'all_items'         => 'All Body',
        'parent_item'       => 'Parent Body',
        'parent_item_colon' => 'Parent Body:',
        'edit_item'         => 'Edit Body',
        'update_item'       => 'Update Body',
        'add_new_item'      => 'Add New Body',
        'new_item_name'     => 'New Body Name',
        'menu_name'         => 'Body',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'body' ),
    );

    register_taxonomy('body', array('car'), $args);
}
add_action('init', 'create_body_taxonomy');


// Shortcode for displaying car list with brand filtering and post limit selection
function cars_list_shortcode($atts) {
    // Set default attributes and get user-defined attributes
    $atts = shortcode_atts(
        array(
            'posts_per_page' => 10, // Default number of cars to display if not set in the shortcode
        ),
        $atts,
        'cars_list'
    );

    // Get brand and posts_per_page from query or shortcode attribute
    $brand = isset($_GET['brand']) ? sanitize_text_field($_GET['brand']) : '';
    $posts_per_page = isset($_GET['posts_per_page']) ? intval($_GET['posts_per_page']) : intval($atts['posts_per_page']);

    // Display filter form
    $output = '<form method="GET" action="">';

    // Brand filter
    $output .= '<label for="brand">Filter by Brand:</label>';
    $output .= '<select name="brand" id="brand" onchange="this.form.submit()">';
    $output .= '<option value="">All Brands</option>';

    // Get all cars to extract unique brand values
    $cars_query = new WP_Query(array(
        'post_type' => 'car',
        'posts_per_page' => -1, // Get all cars
        'meta_key' => 'brand', // Only fetch cars with this meta key
    ));

    $brands = array(); // To store unique brands
    if ($cars_query->have_posts()) {
        while ($cars_query->have_posts()) {
            $cars_query->the_post();
            $current_brand = get_post_meta(get_the_ID(), 'brand', true); // Using 'Brand' as the meta key
            if ($current_brand && !in_array($current_brand, $brands)) {
                $brands[] = $current_brand;
            }
        }
        wp_reset_postdata(); // Reset the post data
    }

    // Populate the select dropdown with brands
    foreach ($brands as $available_brand) {
        $selected = ($available_brand == $brand) ? 'selected' : '';
        $output .= '<option value="' . esc_attr($available_brand) . '" ' . $selected . '>' . esc_html($available_brand) . '</option>';
    }

    $output .= '</select>';

    // Posts per page filter
    $output .= '<label for="posts_per_page">Number of Cars:</label>';
    $output .= '<select name="posts_per_page" id="posts_per_page" onchange="this.form.submit()">';
    
    // Array of possible values for posts per page
    $posts_options = array(-1, 1, 2, 3); // -1 means "All"
    foreach ($posts_options as $option) {
        $selected = ($option == $posts_per_page) ? 'selected' : '';
        $label = ($option == -1) ? 'All' : $option;
        $output .= '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html($label) . '</option>';
    }

    $output .= '</select>';
    $output .= '</form>';

    // Query arguments for filtered cars
    $args = array(
        'post_type' => 'car',
        'posts_per_page' => $posts_per_page, // Use selected number of posts per page
    );

    // If a brand is selected, add it to the query
    if ($brand) {
        $args['meta_query'] = array(
            array(
                'key'   => 'brand', // Using 'Brand' as the meta key
                'value' => $brand,
                'compare' => '='
            ),
        );
    }

    $cars_query = new WP_Query($args);

    if ($cars_query->have_posts()) {
        $output .= '<ul class="cars-list">';
        while ($cars_query->have_posts()) {
            $cars_query->the_post();
            $output .= '<li>';
            $output .= '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
            $output .= '</li>';
        }
        $output .= '</ul>';
        wp_reset_postdata(); // Reset the post data again after the loop
    } else {
        $output .= '<p>No cars found.</p>';
    }

    return $output;
}
add_shortcode('cars_list', 'cars_list_shortcode');


// Shortcode to display cars and the number of body associated with each car
function cars_with_body_shortcode() {
    global $wpdb;

    // SQL query to get car titles and count the number of Body associated with each car
    $query = "
        SELECT p.ID, p.post_title, COUNT(t.term_id) as body_count
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
        LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
        WHERE p.post_type = 'car'
        AND tt.taxonomy = 'body'
        GROUP BY p.ID
    ";

    // Execute the query
    $results = $wpdb->get_results($query);

    // Start output
    $output = '<ul>';

    // Loop through each car and output its title and the number of Body
    if (!empty($results)) {
        foreach ($results as $row) {
            $output .= '<li>';
            $output .= '<strong>' . esc_html($row->post_title) . '</strong> - ' . intval($row->body_count) . ' Body';
            $output .= '</li>';
        }
    } else {
        $output .= '<li>No cars found.</li>';
    }

    $output .= '</ul>';

    return $output;
}
add_shortcode('cars_with_body', 'cars_with_body_shortcode');


// Add a meta box for the custom post type 'Car'
function add_car_meta_box() {
    add_meta_box(
        'car_brand_meta_box', // Meta box ID
        'Car Brand',          // Meta box title
        'car_brand_meta_box_callback', // Callback function to display the meta box
        'car',                // Post type where the meta box should appear
        'normal',             // Context where the meta box should appear
        'high'                // Priority of the meta box
    );
}
add_action('add_meta_boxes', 'add_car_meta_box');

// Callback function to display the meta box content
function car_brand_meta_box_callback($post) {
    $value = get_post_meta($post->ID, 'brand', true); // Get the current value of the 'brand' meta field
    ?>
    <label for="brand">Brand:</label>
    <input type="text" id="brand" name="brand" value="<?php echo esc_attr($value); ?>" />
    <?php
}

// Save the 'brand' meta field value when the post is saved
function save_car_brand_meta_box($post_id) {
    if (array_key_exists('brand', $_POST)) {
        update_post_meta(
            $post_id,
            'brand', // Save the meta field using 'brand' as the key
            sanitize_text_field($_POST['brand']) // Sanitize and save the input value
        );
    }
}
add_action('save_post', 'save_car_brand_meta_box');
