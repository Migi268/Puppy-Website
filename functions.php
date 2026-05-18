<?php
/**
 * Glassmorphism Panels - Remote Bookkeeping Block Theme functions
 *
 * @package glassmorphism-panels---remote-bookkeeping
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Theme setup
 */
function glassmorphism_panels___remote_bookkeeping_setup() {
    // Add support for block styles
    add_theme_support( 'wp-block-styles' );
    
    // Add support for editor styles
    add_theme_support( 'editor-styles' );
    
    // Add support for responsive embeds
    add_theme_support( 'responsive-embeds' );
    
    // Add support for custom logo
    add_theme_support( 'custom-logo', array(
        'height'      => 150,
        'width'       => 400,
        'flex-width'  => true,
        'flex-height' => true,
    ) );
    
    // Enqueue editor styles
    add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'glassmorphism_panels___remote_bookkeeping_setup' );

/**
 * Enqueue theme styles for frontend and block editor (Site Editor)
 */
function glassmorphism_panels___remote_bookkeeping_styles() {
    // Enqueue main stylesheet
    wp_enqueue_style(
        'glassmorphism-panels---remote-bookkeeping-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get( 'Version' )
    );
}
add_action( 'wp_enqueue_scripts', 'glassmorphism_panels___remote_bookkeeping_styles' );
add_action( 'enqueue_block_assets', 'glassmorphism_panels___remote_bookkeeping_styles' ); // Also load in Site Editor

/**
 * Add a per-page body class (pmgpt-page-<slug>) so per-page scoped CSS in
 * style.css matches. Uses the _pressmegpt_page_slug post-meta written at import.
 */
function glassmorphism_panels___remote_bookkeeping_pressmegpt_page_body_class( $classes ) {
    if ( is_singular() ) {
        $slug = get_post_meta( get_the_ID(), '_pressmegpt_page_slug', true );
        if ( $slug ) {
            $classes[] = 'pmgpt-page-' . sanitize_html_class( $slug );
        }
    }
    return $classes;
}
add_filter( 'body_class', 'glassmorphism_panels___remote_bookkeeping_pressmegpt_page_body_class' );

/**
 * Enqueue Google Fonts for frontend and editor
 */
function glassmorphism_panels___remote_bookkeeping_fonts() {
    $body_font = 'Source Sans 3';
    $heading_font = 'Source Serif 4';
    
    $fonts = array();
    if ( ! empty( $body_font ) ) {
        $fonts[] = str_replace( ' ', '+', $body_font ) . ':wght@400;500;600;700';
    }
    if ( ! empty( $heading_font ) && $heading_font !== $body_font ) {
        $fonts[] = str_replace( ' ', '+', $heading_font ) . ':wght@400;500;600;700';
    }
    
    if ( ! empty( $fonts ) ) {
        $fonts_url = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $fonts ) . '&display=swap';
        wp_enqueue_style( 'glassmorphism-panels---remote-bookkeeping-fonts', esc_url( $fonts_url ), array(), null );
    }
}
add_action( 'wp_enqueue_scripts', 'glassmorphism_panels___remote_bookkeeping_fonts' );
add_action( 'enqueue_block_editor_assets', 'glassmorphism_panels___remote_bookkeeping_fonts' );

/**
 * Enqueue Font Awesome for icons
 */
function glassmorphism_panels___remote_bookkeeping_icons() {
    wp_enqueue_style(
        'glassmorphism-panels---remote-bookkeeping-fontawesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        array(),
        '6.5.1'
    );
}
add_action( 'wp_enqueue_scripts', 'glassmorphism_panels___remote_bookkeeping_icons' );
add_action( 'enqueue_block_editor_assets', 'glassmorphism_panels___remote_bookkeeping_icons' );

/**
 * Enqueue scroll-reveal animation script
 */
function glassmorphism_panels___remote_bookkeeping_animations() {
    wp_enqueue_script(
        'glassmorphism-panels---remote-bookkeeping-animations',
        get_template_directory_uri() . '/assets/js/animations.js',
        array(),
        wp_get_theme()->get( 'Version' ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'glassmorphism_panels___remote_bookkeeping_animations' );

/**
 * Theme activation hook.
 * Homepage content lives in templates/front-page.html directly, so no page seeding is needed.
 * This hook only: sets show_on_front, imports the design logo, sets site title, and creates the blog page.
 */
function glassmorphism_panels___remote_bookkeeping_activation() {
    // WordPress stores Site Editor customizations in wp_template / wp_template_part posts.
    // These DB entries survive theme deletion and override the file-based templates on
    // reinstall. Clear them on activation so the fresh file-based templates are used.
    //
    // IMPORTANT: WordPress stores these with post_name = 'front-page' (just the slug),
    // NOT 'theme-slug//front-page'. Theme association is via the wp_theme taxonomy.
    $stylesheet = get_stylesheet();
    $slugs_to_clear = array( 'front-page', 'header', 'footer', 'index', 'page', 'single', 'archive', '404' );
    foreach ( array( 'wp_template', 'wp_template_part' ) as $post_type ) {
        $posts = get_posts( array(
            'post_type'      => $post_type,
            'post_status'    => array( 'publish', 'auto-draft', 'draft', 'trash' ),
            'posts_per_page' => -1,
            'post_name__in'  => $slugs_to_clear,
            'tax_query'      => array( array(
                'taxonomy' => 'wp_theme',
                'field'    => 'name',
                'terms'    => $stylesheet,
            ) ),
        ) );
        foreach ( $posts as $post ) {
            wp_delete_post( $post->ID, true );
        }
    }

    // Read design blocks from the bundled pattern file and seed them into post_content.
    // This is the only approach that works across all three surfaces:
    // frontend, Site Editor overview iframe, and Page Editor canvas.
    $pattern_file = get_template_directory() . '/patterns/homepage-content.php';
    $homepage_content = '';
    if ( file_exists( $pattern_file ) ) {
        $raw = file_get_contents( $pattern_file );
        // Strip the PHP opening tag and docblock header — keep only the block markup
        $homepage_content = preg_replace( '/^<\?php[\s\S]*?\?>\s*/i', '', $raw );
        $homepage_content = '<!-- pressmegpt:seeded-homepage -->' . "
" . trim( $homepage_content );
    }

    $seeded_marker = 'pressmegpt:seeded-homepage';
    $home_id = null;

    // Only look for OUR home page (post_name = 'home').
    // Never reuse a previous theme's front page — it belongs to a different design
    // and won't have the seeded marker, so the old content would be kept unchanged.
    $existing = get_page_by_path( 'home' );
    // get_page_by_path() returns trashed pages too. If trashed, permanently delete it
    // so we can create a fresh published page — a trashed page causes page_on_front
    // to point to an ID that the REST API returns 404 for (Site Editor canvas breaks).
    if ( $existing && $existing->post_status === 'trash' ) {
        wp_delete_post( $existing->ID, true );
        $existing = null;
    }
    // Skip pages in any other non-published state (draft, private, etc.)
    if ( $existing && $existing->post_status !== 'publish' ) {
        $existing = null;
    }

    if ( $existing ) {
        $home_id = $existing->ID;
        $current = $existing->post_content;
        // Overwrite if empty OR still contains our seeded marker (user hasn't customised it)
        if ( $homepage_content && ( empty( trim( $current ) ) || strpos( $current, $seeded_marker ) !== false ) ) {
            wp_update_post( array( 'ID' => $home_id, 'post_content' => $homepage_content ) );
        }
    } else {
        // No 'home' page exists — always create a fresh one with the design blocks
        $home_id = wp_insert_post( array(
            'post_title'   => 'Home',
            'post_name'    => 'home',
            'post_content' => $homepage_content,
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ) );
    }

    if ( $home_id && ! is_wp_error( $home_id ) ) {
        update_post_meta( $home_id, '_wp_page_template', 'front-page' );
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $home_id );
        flush_rewrite_rules();
    }

    // Auto-import design logo (URL extracted at export time from raw design HTML, before any path rewrites)
    $logo_url = 'https://placehold.co/200x60/EEE/999?text=Logo';
    $site_title = 'Remote Bookkeeping';

    if ( ! empty( $logo_url ) && ! get_theme_mod( 'custom_logo' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        $attachment_id = media_sideload_image( $logo_url, 0, null, 'id' );
        if ( ! is_wp_error( $attachment_id ) ) {
            set_theme_mod( 'custom_logo', $attachment_id );
        }
    }

    if ( ! empty( $site_title ) ) {
        update_option( 'blogname', $site_title );
    }

    // Create blog page if needed
    if ( ! get_page_by_path( 'blog' ) ) {
        $blog_id = wp_insert_post( array(
            'post_title'   => 'Blog',
            'post_name'    => 'blog',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ) );
        if ( $blog_id && ! is_wp_error( $blog_id ) ) {
            update_option( 'page_for_posts', $blog_id );
        }
    }
    // Create additional designed pages
    glassmorphism_panels___remote_bookkeeping_sync_designed_pages();

    // Create menus with all pages
    glassmorphism_panels___remote_bookkeeping_create_default_menus();
}
add_action( 'after_switch_theme', 'glassmorphism_panels___remote_bookkeeping_activation' );

/**
 * Sync designed pages from PressMeGPT export.
 * Runs on activation and on admin_init when export hash changes.
 */
function glassmorphism_panels___remote_bookkeeping_sync_designed_pages() {
    $pages_data = glassmorphism_panels___remote_bookkeeping_get_designed_pages_data();
    
    foreach ( $pages_data as $slug => $page_info ) {
        if ( empty( $slug ) || $slug === 'home' ) {
            continue;
        }
        if ( ! empty( $page_info['is_template'] ) ) {
            continue;
        }
        
        $post_type = isset( $page_info['type'] ) && $page_info['type'] === 'post' ? 'post' : 'page';
        
        // Look up by meta key first (most reliable), then fall back to slug lookup
        $existing = null;
        $meta_q = new WP_Query( array(
            'post_type'      => $post_type,
            'meta_key'       => '_pressmegpt_page_slug',
            'meta_value'     => $slug,
            'posts_per_page' => 1,
            'post_status'    => 'any',
            'fields'         => 'ids',
        ) );
        if ( ! empty( $meta_q->posts ) ) {
            $existing = get_post( $meta_q->posts[0] );
        }
        wp_reset_postdata();
        if ( ! $existing ) {
            $existing = get_page_by_path( $slug, OBJECT, $post_type );
        }
        
        // Convert page HTML to block content
        $content = $page_info['content'];
        
        if ( $existing ) {
            wp_update_post( array(
                'ID'           => $existing->ID,
                'post_content' => $content,
            ) );
            update_post_meta( $existing->ID, '_pressmegpt_page_slug', $slug );
        } else {
            $new_id = wp_insert_post( array(
                'post_title'   => $page_info['title'],
                'post_name'    => $slug,
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_type'    => $post_type,
            ) );
            if ( $new_id && ! is_wp_error( $new_id ) ) {
                update_post_meta( $new_id, '_pressmegpt_page_slug', $slug );
            }
        }
    }
    
    // Store export hash so admin_init can detect changes
    update_option( 'glassmorphism_panels___remote_bookkeeping_export_hash', 'glassmorphism_panels___remote_bookkeeping_pages_hash_v1.0_tpl5' );
}

/**
 * Get designed pages data (embedded at build time)
 */
function glassmorphism_panels___remote_bookkeeping_get_designed_pages_data() {
    return array(
        // No additional pages
    );
}

/**
 * Look up a PressMeGPT-imported page/post by its original slug meta key.
 */
function glassmorphism_panels___remote_bookkeeping_find_page_by_slug( $slug, $post_type = 'page' ) {
    $q = new WP_Query( array(
        'post_type'      => $post_type,
        'meta_key'       => '_pressmegpt_page_slug',
        'meta_value'     => $slug,
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
    ) );
    $ids = $q->posts;
    wp_reset_postdata();
    if ( ! empty( $ids ) ) {
        return (int) $ids[0];
    }
    $p = get_page_by_path( $slug, OBJECT, $post_type );
    return $p ? (int) $p->ID : 0;
}

/**
 * Create default menus on theme activation
 */
function glassmorphism_panels___remote_bookkeeping_create_default_menus() {
    $primary_menu_name = 'Primary Menu';
    $primary_menu_exists = wp_get_nav_menu_object( $primary_menu_name );
    
    if ( ! $primary_menu_exists ) {
        $primary_menu_id = wp_create_nav_menu( $primary_menu_name );
        
        // Home link — prefer front page object
        $front_page_id = (int) get_option( 'page_on_front' );
        if ( $front_page_id ) {
            wp_update_nav_menu_item( $primary_menu_id, 0, array(
                'menu-item-title'     => 'Home',
                'menu-item-type'      => 'post_type',
                'menu-item-object'    => 'page',
                'menu-item-object-id' => $front_page_id,
                'menu-item-status'    => 'publish',
            ) );
        } else {
            wp_update_nav_menu_item( $primary_menu_id, 0, array(
                'menu-item-title'   => 'Home',
                'menu-item-url'     => home_url( '/' ),
                'menu-item-type'    => 'custom',
                'menu-item-status'  => 'publish',
            ) );
        }
        
        // No additional pages for menu
        
        $locations = get_theme_mod( 'nav_menu_locations' );
        $locations['primary'] = $primary_menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );
    }
}

/**
 * Re-sync pages on admin_init if export hash changed (theme update without reactivation).
 * Also clears wp_template / wp_template_part DB overrides so file-based templates take effect.
 */
function glassmorphism_panels___remote_bookkeeping_check_page_sync() {
    $stored_hash = get_option( 'glassmorphism_panels___remote_bookkeeping_export_hash', '' );
    $current_hash = 'glassmorphism_panels___remote_bookkeeping_pages_hash_v1.0_tpl5';
    if ( $stored_hash !== $current_hash ) {
        // Clear stored template overrides so updated file-based templates are used
        $stylesheet = get_stylesheet();
        $slugs_to_clear = array( 'front-page', 'header', 'footer', 'index', 'page', 'single', 'archive', '404' );
        foreach ( array( 'wp_template', 'wp_template_part' ) as $post_type ) {
            $posts = get_posts( array(
                'post_type'      => $post_type,
                'post_status'    => array( 'publish', 'auto-draft', 'draft', 'trash' ),
                'posts_per_page' => -1,
                'post_name__in'  => $slugs_to_clear,
                'tax_query'      => array( array(
                    'taxonomy' => 'wp_theme',
                    'field'    => 'name',
                    'terms'    => $stylesheet,
                ) ),
            ) );
            foreach ( $posts as $post ) {
                wp_delete_post( $post->ID, true );
            }
        }
        glassmorphism_panels___remote_bookkeeping_sync_designed_pages();
    }
}
add_action( 'admin_init', 'glassmorphism_panels___remote_bookkeeping_check_page_sync' );
