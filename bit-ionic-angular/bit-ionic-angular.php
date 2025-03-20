<?php

/**
 * Plugin Name: Bit Ionic Angular
 * Description: Optmize ionic theme base
 * Author: R Portillo
 * Version: 0.5b
 */

date_default_timezone_set("America/Sao_Paulo");
include_once "class-bit-ionic-angular-banners.php";
include_once "class-bit-ionic-angular-banner.php";

if (!defined("ABSPATH")) {
  exit(); // Exit if accessed directly.
}

include "functions.php";

if (!class_exists("BitIonicAngular")) {
  class BitIonicAngular
  {
    private $options;
    function __construct()
    {
      add_action("admin_menu", [$this, "register_menu_page"]);
      add_action("admin_menu", [$this, "add_plugin_page"]);
      add_action("admin_init", [$this, "page_init"]);
      add_action("admin_enqueue_scripts", [$this, "load_media_files"]);
    }

    /*
     * Menu info
     */
    function register_menu_page()
    {
      add_menu_page(
        "BitIonicAngular",
        "Ion Themes",
        "manage_options",
        "bit-ionic-angular-start",
        "get_option",
        "",
        69.9
      );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
      add_options_page(
        "Main",
        "Details",
        "manage_options",
        "bit-ionic-angular-start",
        [$this, "create_admin_page"]
      );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
      // Set class property
      $this->options = get_option("bit_ionic_angular"); ?>
            <div class="wrap">
                <h1 style="margin-bottom: 0;">Ionic Angular Theme</h1>
                <div class="bit-endpoint-list">
                    <h2>Endpoint List</h2>
                    <table class="endpoint-list-bit">
                        <tr>
                            <th>Menu</th>
                          <td>
                            <code>
                            <?php echo get_site_url(); ?>/wp-json/custom/v1/menus
                            </code>
                          </td>
                        </tr>
                        <tr>
                          <th>Posts</th>
                          <td>
                            <code>
                            <?php echo get_site_url(); ?>/wp-json/custom/v1/posts
                            </code>
                            <code>
                            <?php echo get_site_url(); ?>/wp-json/custom/v1/posts/${page}/${size}
                            </code>
                            <code>
                            <?php echo get_site_url(); ?>/wp-json/custom/v1/posts/${id}
                            </code>
                            <code>
                            <?php echo get_site_url(); ?>/wp-json/custom/v1/posts/${category_name}/${post_name}
                            </code>
                            <code>
                            <?php echo get_site_url(); ?>/wp-json/custom/v1/posts?per_page=10&page=${page}&categories_exclude=${excluded}&order=${order}
                            </code>
                            <code>
                            <?php echo get_site_url(); ?>/wp-json/custom/v1/posts?categories=${categoryId}&slug=${slug}
                            </code>
                          </td>
                        </tr>
                        <tr>
                            <th>Media</th>
                          <td>
                            <code>
                            <?php echo get_site_url(); ?>/wp-json/custom/v1/media/${postId}
                            </code>
                          </td>
                        </tr>
                        <tr>
                            <th>Banners</th>
                            <td>
                              <code>
                              <?php echo get_site_url(); ?>/wp-json/custom/v1/banners/}
                              </code>
                            <code>
                            <?php echo get_site_url(); ?>/wp-json/custom/v1/banner/${slug}
                            </code>
                          </td>
                        </tr>
                        <tr>
                            <th>Categories</th>
                            <td>
                              <code>
                              <?php echo get_site_url(); ?>/wp-json/custom/v1/categories/}
                              </code>
                            <code>
                            <?php echo get_site_url(); ?>/wp-json/custom/v1/categories/${id}
                            </code>
                          </td>
                        </tr>
                    </table>
                </div>
            </div>
            <style>
            .endpoint-list-bit td,
            .endpoint-list-bit th{
              padding: 10px 0;
              border-bottom: 1px solid grey;
              vertical-align: top;
            }
            
            .endpoint-list-bit code{
              display: block;
              margin-bottom: 1px;
            }
            </style>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
      register_setting(
        "bit_ionic_angular_group", // Option group
        "bit_ionic_angular", // Option name
        [$this, "sanitize"] // Sanitize
      );

      add_settings_section(
        "bit_ionic_angular_settings", // ID
        "Definir campos", // Title
        [$this, "print_section_info"], // Callback
        "bit-ionic-angular-start"
        // Page
      );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
      $new_input = [];

      return $new_input;
    }

    /*
     * Load Media Uploader
     */
    function load_media_files()
    {
      wp_enqueue_media();
    }
  }
}

if (is_admin()):
  $bit_ionic_angular = new BitIonicAngular();
  $bit_ionic_angular_banners = new BitIonicAngularBanners();
  $bit_ionic_angular_banner = new BitIonicAngularBanner();
endif;
?>
