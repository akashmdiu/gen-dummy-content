
<?php
/*
Plugin Name: Dynamic Content Generator
Description: Generate posts and taxonomies with dynamic content based on user-defined parameters.
Version: 4.0
Author: Your Name
*/

define('BATCH_SIZE', 10); // Number of posts/taxonomies to process in each batch


if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Enqueue JavaScript for AJAX
function dlg_enqueue_scripts($hook) {
    if ($hook !== 'toplevel_page_dynamic-content-generator') return;
    wp_enqueue_script('dlg-ajax-script', plugins_url('/ajax.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('dlg-ajax-script', 'dlg_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('admin_enqueue_scripts', 'dlg_enqueue_scripts');

// Register the plugin menu in the admin area
function dlg_register_menu() {
    add_menu_page(
        'Content Generator',
        'Content Generator',
        'manage_options',
        'dynamic-content-generator',
        'dlg_generate_content',
        'dashicons-admin-tools',
        80
    );
}
add_action('admin_menu', 'dlg_register_menu');

// Display the form for options and handle content generation
function dlg_generate_content() {
    $post_types = get_post_types(['public' => true], 'objects'); // Fetch public post types
    $taxonomies = get_taxonomies(['public' => true], 'objects'); // Fetch public taxonomies
    ?>
    <div class="wrap">
        <h1>Generate Posts and Taxonomies with Dynamic Content</h1>

        <h2>Generate Posts</h2>
        <form id="dlg-generate-posts-form">
            <table class="form-table">
                <tr>
                    <th>Select Post Type:</th>
                    <td>
                        <select name="dlg_post_type" required>
                            <?php foreach ($post_types as $post_type): ?>
                                <option value="<?php echo esc_attr($post_type->name); ?>">
                                    <?php echo esc_html($post_type->label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Number of Posts to Create:</th>
                    <td><input type="number" name="dlg_number_of_posts" value="10" min="1" required></td>
                </tr>
                <tr>
                    <th>Number of Words for Post Title:</th>
                    <td><input type="number" name="dlg_title_word_count" value="3" min="1" required></td>
                </tr>
                <tr>
                    <th>Number of Words for Post Content:</th>
                    <td><input type="number" name="dlg_post_word_count" value="100" min="10" required></td>
                </tr>
                <tr>
                    <th>Select Taxonomy Terms:</th>
                    <td>
                        <select name="dlg_tax_terms[]" multiple required>
                            <?php foreach ($taxonomies as $taxonomy): ?>
                                <?php
                                $terms = get_terms(['taxonomy' => $taxonomy->name, 'hide_empty' => false]);
                                if (!empty($terms)):
                                ?>
                                    <optgroup label="<?php echo esc_attr($taxonomy->label); ?>">
                                        <?php foreach ($terms as $term): ?>
                                            <option value="<?php echo esc_attr($term->term_id); ?>">
                                                <?php echo esc_html($term->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <input type="submit" class="button button-primary" value="Generate Dummy Posts">
            <div id="dlg-ajax-message" style="margin-top: 10px;"></div>
        </form>

        <h2>Create Taxonomies</h2>
        <form id="dlg-create-taxonomies-form">
            <table class="form-table">
                <tr>
                    <th>Select Existing Taxonomy:</th>
                    <td>
                        <select name="dlg_taxonomy_name" required>
                            <option value="">Select Taxonomy</option>
                            <?php foreach ($taxonomies as $taxonomy): ?>
                                <option value="<?php echo esc_attr($taxonomy->name); ?>">
                                    <?php echo esc_html($taxonomy->label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Number of Taxonomies to Create:</th>
                    <td><input type="number" name="dlg_taxonomy_count" value="1" min="1" required></td>
                </tr>
                <tr>
                    <th>Number of Words for Taxonomy Title:</th>
                    <td><input type="number" name="dlg_taxonomy_title_word_count" value="2" min="1" required></td>
                </tr>
                <tr>
                    <th>Number of Words for Taxonomy Description:</th>
                    <td><input type="number" name="dlg_taxonomy_desc_word_count" value="20" min="1" required></td>
                </tr>
            </table>
            <input type="submit" class="button button-primary" value="Create Taxonomies">
            <div id="dlg-ajax-message-tax" style="margin-top: 10px;"></div>
        </form>

        <h2>Delete Generated Content</h2>
        <form id="dlg-delete-content-form">
            <table class="form-table">
                <tr>
                    <th>Select Post Type to Delete:</th>
                    <td>
                        <select name="dlg_delete_post_type" required>
                            <?php foreach ($post_types as $post_type): ?>
                                <option value="<?php echo esc_attr($post_type->name); ?>">
                                    <?php echo esc_html($post_type->label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <input type="submit" class="button button-danger" value="Delete Generated Content">
            <div id="dlg-ajax-delete-message" style="margin-top: 10px;"></div>
        </form>

        <h2>Delete Taxonomies</h2>
        <form id="dlg-delete-taxonomies-form">
            <table class="form-table">
                <tr>
                    <th>Select Taxonomy to Delete:</th>
                    <td>
                        <select name="dlg_delete_taxonomy_name" required>
                            <option value="">Select Taxonomy</option>
                            <?php foreach ($taxonomies as $taxonomy): ?>
                                <option value="<?php echo esc_attr($taxonomy->name); ?>">
                                    <?php echo esc_html($taxonomy->label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <input type="submit" class="button button-danger" value="Delete All Terms in Taxonomy">
            <div id="dlg-ajax-delete-tax-message" style="margin-top: 10px;"></div>
        </form>
    </div>
    <?php
}

function dlg_ajax_generate_posts() {
    $post_type = sanitize_text_field($_POST['dlg_post_type']);
    $number_of_posts = intval($_POST['dlg_number_of_posts']);
    $post_word_count = intval($_POST['dlg_post_word_count']);
    $title_word_count = intval($_POST['dlg_title_word_count']);
    $selected_tax_terms = $_POST['dlg_tax_terms'] ?? [];
    $offset = intval($_POST['offset']); // Get offset

    dlg_generate_posts($post_type, $post_word_count, $title_word_count, $number_of_posts, $selected_tax_terms, $offset);
    wp_die();
}
add_action('wp_ajax_dlg_generate_posts', 'dlg_ajax_generate_posts');

function dlg_ajax_create_taxonomies() {
    $taxonomy_name = sanitize_text_field($_POST['dlg_taxonomy_name']);
    $taxonomy_count = intval($_POST['dlg_taxonomy_count']);
    $desc_word_count = intval($_POST['dlg_taxonomy_desc_word_count']);
    $title_word_count = intval($_POST['dlg_taxonomy_title_word_count']);
    $offset = intval($_POST['offset']); // Get offset

    dlg_create_taxonomies($taxonomy_name, $taxonomy_count, $desc_word_count, $title_word_count, $offset);
    wp_die();
}
add_action('wp_ajax_dlg_create_taxonomies', 'dlg_ajax_create_taxonomies');


// AJAX handler for deleting posts
function dlg_ajax_delete_content() {
    $post_type = sanitize_text_field($_POST['dlg_delete_post_type']);
    dlg_delete_content($post_type);
    wp_die();
}
add_action('wp_ajax_dlg_delete_content', 'dlg_ajax_delete_content');

// AJAX handler for deleting taxonomies
function dlg_ajax_delete_taxonomy() {
    $taxonomy = sanitize_text_field($_POST['dlg_delete_taxonomy_name']);
    dlg_delete_taxonomy($taxonomy);
    wp_die();
}
add_action('wp_ajax_dlg_delete_taxonomy', 'dlg_ajax_delete_taxonomy');

// Generate dynamic posts with selected terms
function dlg_generate_posts($post_type, $word_count, $title_word_count, $number_of_posts, $tax_terms, $offset = 0) {
    $limit = min(BATCH_SIZE, $number_of_posts - $offset);

    for ($i = 0; $i < $limit; $i++) {
        $post_id = wp_insert_post([
            'post_title' => dlg_generate_real_title($title_word_count),
            'post_content' => dlg_generate_dynamic_content($word_count, $tax_terms),
            'post_status' => 'publish',
            'post_type' => $post_type,
        ]);
    }

    $remaining = $number_of_posts - ($offset + $limit);
    echo json_encode(['success' => true, 'remaining' => $remaining]);
}


// Create taxonomies with dynamic names
function dlg_create_taxonomies($taxonomy_name, $taxonomy_count, $desc_word_count, $title_word_count, $offset = 0) {
    if (taxonomy_exists($taxonomy_name)) {
        $limit = min(BATCH_SIZE, $taxonomy_count - $offset);

        for ($i = 0; $i < $limit; $i++) {
            $term_name = dlg_generate_real_title($title_word_count);
            $term_description = dlg_generate_dynamic_content($desc_word_count, []);
            wp_insert_term($term_name, $taxonomy_name, ['description' => $term_description]);
        }

        $remaining = $taxonomy_count - ($offset + $limit);
        echo json_encode(['success' => true, 'remaining' => $remaining]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Selected taxonomy does not exist!']);
    }
}


// Delete all posts of the specified post type
function dlg_delete_content($post_type) {
    $args = [
        'post_type' => $post_type,
        'post_status' => 'publish',
        'numberposts' => -1,
    ];

    $posts = get_posts($args);
    foreach ($posts as $post) {
        wp_delete_post($post->ID, true); // true for force deletion
    }

    echo json_encode(['success' => true, 'message' => 'All generated posts deleted successfully!']);
}

// Delete all terms in the specified taxonomy
function dlg_delete_taxonomy($taxonomy) {
    $terms = get_terms($taxonomy, ['hide_empty' => false]);
    foreach ($terms as $term) {
        wp_delete_term($term->term_id, $taxonomy); // Delete each term
    }

    echo json_encode(['success' => true, 'message' => 'All terms in the selected taxonomy deleted successfully!']);
}

// Generate dynamic content for posts
function dlg_generate_dynamic_content($max_words, $terms) {
    $dynamic_content = '';

    // Get random words from a third-party API
    $response = wp_remote_get('https://random-word-api.herokuapp.com/word?number=' . $max_words,  array(
        'timeout' => 500000,
        // Set timeout to 15 seconds
    ));
    if (is_wp_error($response)) {
        return 'Error fetching words.';
    }
    $words = json_decode(wp_remote_retrieve_body($response));

    // Generate content with random words
    $dynamic_content .= implode(' ', (array)$words);

    // Add random terms
    if (!empty($terms)) {
        shuffle($terms);
        $term_ids = array_rand($terms, rand(1, min(5, count($terms)))); // Randomly select 1 to 5 terms
        foreach ((array)$term_ids as $term_id) {
            $term = get_term($terms[$term_id]);
            if ($term) {
                $dynamic_content .= ' ' . esc_html($term->name); // Add term name to content
            }
        }
    }

    return $dynamic_content;
}

// Generate random title using third-party API
function dlg_generate_real_title($max_words) {
    // Fetch random words for the title
    $response = wp_remote_get('https://random-word-api.herokuapp.com/word?number=' . $max_words, array(
        'timeout' => 500000, // Set timeout to 15 seconds
    ));
    if (is_wp_error($response)) {
        return 'Error fetching title.';
    }
    $words = json_decode(wp_remote_retrieve_body($response));
    return implode(' ', (array)$words);
}
?>
