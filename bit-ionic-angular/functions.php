<?php
function bit_add_supports()
{
  add_theme_support("menus");
}
add_action("after_setup_theme", "bit_add_supports");

add_action("rest_api_init", function () {
  register_rest_route("custom/v1", "/categories", [
    "methods" => "GET",
    "callback" => "bit_get_all_categories",
  ]);
  register_rest_route("custom/v1", "/categories/(?P<id>\d+)", [
    "methods" => "GET",
    "callback" => "bit_get_single_category",
    "args" => [
      "id" => [
        "required" => true,
        "validate_callback" => function ($param) {
          return is_numeric($param);
        },
      ],
    ],
  ]);

  register_rest_route("custom/v1", "/menus", [
    "methods" => "GET",
    "callback" => "bit_get_all_menus",
  ]);
  register_rest_route("custom/v1", "/banners", [
    "methods" => "GET",
    "callback" => "bit_get_all_banners_groups",
  ]);

  register_rest_route("custom/v1", "/banners/(?P<slug>[a-zA-Z0-9-]+)", [
    "methods" => "GET",
    "callback" => "bit_get_banners_by_slug",
  ]);

  register_rest_route("custom/v1", "/posts/(?P<page>[0-9]+)/(?P<size>[0-9]+)", [
    "methods" => "GET",
    "callback" => "bit_get_posts_lists",
  ]);

  register_rest_route(
    "custom/v1",
    "/posts/(?P<category_slug>[a-zA-Z0-9-]+)/(?P<slug>[a-zA-Z0-9-]+)",
    [
      "methods" => "GET",
      "callback" => "bit_get_post",
    ]
  );

  register_rest_route(
    "custom/v1",
    "/categories/(?P<category>[a-zA-Z0-9-]+)/posts/(?P<slug>[a-zA-Z0-9-]+)/(?P<size>[0-9]+)",
    [
      "methods" => "GET",
      "callback" => "bit_get_posts_lists_by_category",
    ]
  );
});

function bit_get_post(WP_REST_Request $request)
{
  $slug = $request->get_param("slug");
  $category_slug = $request->get_param("category_slug");

  $args = [
    "post_type" => "post",
    "name" => $slug,
    "category_name" => $category_slug,
    "post_status" => "publish",
    "numberposts" => 1,
  ];

  $query = new WP_Query($args);

  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      $categories = get_the_category();
      $categories_list = [];
      if (!empty($categories)) {
        foreach ($categories as $category) {
          $categories_list[] = [
            "id" => $category->term_id,
            "name" => $category->name,
            "slug" => $category->slug,
          ];
        }
      }
      $thumbnail_id = get_post_thumbnail_id(get_the_ID());
      $thumbnail = $thumbnail_id
        ? wp_get_attachment_image_src($thumbnail_id, "full")
        : null;
      $thumbnail_alt = $thumbnail_id
        ? get_post_meta($thumbnail_id, "_wp_attachment_image_alt", true)
        : null;
      $thumbnail_title = $thumbnail_id ? get_the_title($thumbnail_id) : null;

      $post = [
        "id" => get_the_ID(),
        "title" => get_the_title(),
        "slug" => get_post_field("post_name", get_the_ID()),
        "description" => get_the_excerpt(),
        "content" => get_the_content(),
        "link" => get_permalink(),
        "categories" => $categories_list,
        "published_date" => get_the_date("Y-m-d H:i:s"),
        "updated_date" => get_the_modified_date("Y-m-d H:i:s"),
        "thumbnail" => $thumbnail
          ? [
            "url" => $thumbnail[0],
            "width" => $thumbnail[1],
            "height" => $thumbnail[2],
            "alt" => $thumbnail_alt,
            "title" => $thumbnail_title,
          ]
          : null,
      ];
    }
    wp_reset_postdata();
  }

  return new WP_REST_Response($post, 200);
}

function bit_get_posts_lists(WP_REST_Request $request)
{
  $page = (int) $request->get_param("page");
  $size = (int) $request->get_param("size");

  $args = [
    "post_type" => "post",
    "posts_per_page" => $size,
    "paged" => $page,
    "orderby" => "date",
    "order" => "DESC",
  ];

  $query = new WP_Query($args);

  $posts = [];
  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      $categories = get_the_category();
      $categories_list = [];
      if (!empty($categories)) {
        foreach ($categories as $category) {
          $categories_list[] = [
            "id" => $category->term_id,
            "name" => $category->name,
            "slug" => $category->slug,
          ];
        }
      }
      $thumbnail_id = get_post_thumbnail_id(get_the_ID());
      $thumbnail = $thumbnail_id
        ? wp_get_attachment_image_src($thumbnail_id, "full")
        : null;
      $thumbnail_alt = $thumbnail_id
        ? get_post_meta($thumbnail_id, "_wp_attachment_image_alt", true)
        : null;
      $thumbnail_title = $thumbnail_id ? get_the_title($thumbnail_id) : null;

      $posts[] = [
        "id" => get_the_ID(),
        "title" => get_the_title(),
        "slug" => get_post_field("post_name", get_the_ID()),
        "description" => get_the_excerpt(),
        "link" => get_permalink(),
        "categories" => $categories_list,
        "published_date" => get_the_date("Y-m-d H:i:s"),
        "updated_date" => get_the_modified_date("Y-m-d H:i:s"),
        "thumbnail" => $thumbnail
          ? [
            "url" => $thumbnail[0],
            "width" => $thumbnail[1],
            "height" => $thumbnail[2],
            "alt" => $thumbnail_alt,
            "title" => $thumbnail_title,
          ]
          : null,
      ];
    }
    wp_reset_postdata();
  }

  return new WP_REST_Response(
    [
      "page" => $page,
      "size" => $size,
      "total" => $query->found_posts,
      "totalPages" => $query->max_num_pages,
      "posts" => $posts,
    ],
    200
  );
}

function bit_get_posts_lists_by_category(WP_REST_Request $request)
{
  $category = $request->get_param("category");
  $page = (int) $request->get_param("page");
  $size = (int) $request->get_param("size");

  if (empty($category)) {
    return new WP_Error("missing_category", "Category parameter is required", [
      "status" => 400,
    ]);
  }

  $args = [
    "post_type" => "post",
    "category_name" => $category,
    "posts_per_page" => $size,
    "paged" => $page,
    "orderby" => "date",
    "order" => "DESC",
  ];

  $query = new WP_Query($args);

  $posts = [];
  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      $categories = get_the_category();
      $categories_list = [];
      if (!empty($categories)) {
        foreach ($categories as $category) {
          $categories_list[] = [
            "id" => $category->term_id,
            "name" => $category->name,
            "slug" => $category->slug,
          ];
        }
      }
      $thumbnail_id = get_post_thumbnail_id(get_the_ID());
      $thumbnail = $thumbnail_id
        ? wp_get_attachment_image_src($thumbnail_id, "full")
        : null;
      $thumbnail_alt = $thumbnail_id
        ? get_post_meta($thumbnail_id, "_wp_attachment_image_alt", true)
        : null;
      $thumbnail_title = $thumbnail_id ? get_the_title($thumbnail_id) : null;

      $posts[] = [
        "id" => get_the_ID(),
        "title" => get_the_title(),
        "slug" => get_post_field("post_name", get_the_ID()),
        "description" => get_the_excerpt(),
        "link" => get_permalink(),
        "categories" => $categories_list,
        "published_date" => get_the_date("Y-m-d H:i:s"),
        "updated_date" => get_the_modified_date("Y-m-d H:i:s"),
        "thumbnail" => $thumbnail
          ? [
            "url" => $thumbnail[0],
            "width" => $thumbnail[1],
            "height" => $thumbnail[2],
            "alt" => $thumbnail_alt,
            "title" => $thumbnail_title,
          ]
          : null,
      ];
    }
    wp_reset_postdata();
  }

  return new WP_REST_Response(
    [
      "page" => $page,
      "size" => $size,
      "total" => $query->found_posts,
      "totalPages" => $query->max_num_pages,
      "posts" => $posts,
    ],
    200
  );
}

function bit_get_all_categories()
{
  $categories = get_categories([
    "orderby" => "name",
    "order" => "ASC",
    "hide_empty" => false,
  ]);

  $result = [];

  foreach ($categories as $category) {
    $result[] = [
      "id" => $category->term_id,
      "name" => $category->name,
      "slug" => $category->slug,
      "description" => $category->description,
      "count" => $category->count,
    ];
  }

  return $result;
}

function bit_get_single_category($data)
{
  $category_id = intval($data["id"]);
  $category = get_term($category_id, "category");

  if (is_wp_error($category) || !$category) {
    return new WP_Error("category_not_found", "Not Found", [
      "status" => 404,
    ]);
  }

  return [
    "id" => $category->term_id,
    "name" => $category->name,
    "slug" => $category->slug,
    "description" => $category->description,
    "count" => $category->count,
  ];
}

function bit_get_all_menus()
{
  $menus = wp_get_nav_menus();
  $result = [];

  foreach ($menus as $menu) {
    $menu_items = wp_get_nav_menu_items($menu->term_id);
    $formatted_items = [];

    if ($menu_items) {
      foreach ($menu_items as $item) {
        $formatted_items[] = [
          "id" => $item->ID,
          "title" => $item->title,
          "url" => $item->url,
          "parent" => $item->menu_item_parent,
        ];
      }
    }

    $result[] = [
      "id" => $menu->term_id,
      "name" => $menu->name,
      "slug" => $menu->slug,
      "items" => $formatted_items,
    ];
  }

  return $result;
}

function bit_get_all_banners_groups()
{
  $args = [
    "post_type" => "banners_group",
    "posts_per_page" => -1,
  ];

  $posts = get_posts($args);
  $result = [];

  foreach ($posts as $post) {
    $group_title = $post->post_title;
    $group_slug = $post->post_name;

    if ($group_title && $group_slug) {
      $result[] = [
        "title" => $group_title,
        "slug" => $group_slug,
      ];
    }
  }

  return $result;
}

function bit_get_banners_by_slug(WP_REST_Request $request)
{
  $slug = $request->get_param("slug");

  $args = [
    "post_type" => "banners_group",
    "posts_per_page" => -1,
    "name" => $slug,
  ];

  $posts = get_posts($args);
  $result = [];

  foreach ($posts as $post) {
    $banners = get_post_meta($post->ID, "banners", true) ?: [];
    $links = get_post_meta($post->ID, "links", true) ?: [];
    $formatted_banners = [];
    foreach ($banners as $index => $banner_id) {
      $banner_url = wp_get_attachment_url($banner_id);
      $link_url = $links[$index] ?? "";

      $formatted_banners[] = [
        "banner" => [
          "id" => $banner_id,
          "url" => esc_url($banner_url),
          "title" => get_the_title($banner_id),
          "alt" => get_post_meta($banner_id, "_wp_attachment_image_alt", true),
          "caption" => get_post_field("post_excerpt", $banner_id),
          //"media_details" => wp_get_attachment_metadata($banner_id),
        ],
        "link_url" => esc_url($link_url),
      ];
    }

    if (!empty($formatted_banners)) {
      $result = [
        "post_id" => $post->ID,
        "post_title" => $post->post_title,
        "banners" => $formatted_banners,
      ];
    }
  }

  if (empty($result)) {
    return new WP_Error("no_banners_found", "No banners found for this slug.", [
      "status" => 404,
    ]);
  }

  return $result;
}

add_action("admin_post_save_banner_group", function () {
  if (!check_admin_referer("bit_ionic_angular_banners_group")) {
    wp_die();
  }

  if (!current_user_can("manage_options")) {
    wp_die("Unauthorized");
  }

  $post_id = intval($_POST["post_id"] ?? 0);
  $title = sanitize_text_field($_POST["group_title"] ?? "");
  $slug = sanitize_title($_POST["group_slug"] ?? "");
  $banners = $_POST["bit_ionic_angular_banners"] ?? [];
  $links = $_POST["link"] ?? [];

  if (!$post_id) {
    $post_id = wp_insert_post([
      "post_type" => "banners_group",
      "post_title" => $title,
      "post_name" => $slug,
      "post_status" => "publish",
    ]);
  } else {
    wp_update_post([
      "ID" => $post_id,
      "post_title" => $title,
      "post_name" => $slug,
    ]);
  }

  update_post_meta($post_id, "banners", array_map("intval", $banners));
  update_post_meta($post_id, "links", array_map("sanitize_text_field", $links));

  wp_redirect(
    admin_url("admin.php?page=bit-ionic-angular-banner&post_id=" . $post_id)
  );
  exit();
});
