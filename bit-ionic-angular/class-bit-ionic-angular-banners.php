<?php

class BitIonicAngularBanners
{
  public function __construct()
  {
    add_action("admin_menu", [$this, "add_banner_management_page"]);
    add_action("admin_init", [$this, "page_init_banners"]);
    add_action("admin_enqueue_scripts", [$this, "load_media_files"]);
    add_action("init", [$this, "register_banners_post_type"]);
  }

  public function register_banners_post_type()
  {
    $labels = [
      "name" => "Grupos de Banners",
      "singular_name" => "Grupo de Banner",
      "add_new" => "Adicionar Novo",
      "add_new_item" => "Adicionar Novo Grupo de Banner",
      "edit_item" => "Editar Grupo de Banner",
      "new_item" => "Novo Grupo de Banner",
      "view_item" => "Ver Grupo de Banner",
      "all_items" => "Todos os Grupos de Banners",
      "search_items" => "Buscar Grupos de Banners",
      "not_found" => "Nenhum grupo de banner encontrado",
      "not_found_in_trash" => "Nenhum grupo de banner encontrado na lixeira",
      "menu_name" => "Grupos de Banners",
    ];

    $args = [
      "labels" => $labels,
      "public" => true,
      "has_archive" => true,
      "show_in_menu" => true,
      //"show_ui" => false,
      "show_in_menu" => false,
      "menu_icon" => "dashicons-images-alt2",
      "supports" => ["title", "editor"],
      "capability_type" => "post",
      "show_in_rest" => true,
    ];

    register_post_type("banners_group", $args);
  }

  public function add_banner_management_page()
  {
    add_submenu_page(
      "bit-ionic-angular-start",
      "Gerenciar Grupos de Banners",
      "Grupos de Banners",
      "manage_options",
      "bit-ionic-angular-banners",
      [$this, "render_banner_management_page"]
    );
  }

  public function render_banner_management_page()
  {
    ?>
	 <div class="wrap">
		 <h1>Gerenciar Grupos de Banners</h1>
		 <a href="<?php echo admin_url(
     "admin.php?page=bit-ionic-angular-banner"
   ); ?>" class="page-title-action">Adicionar Novo Grupo</a>
		 <table class="wp-list-table widefat fixed striped posts">
			 <thead>
				 <tr>
					 <th scope="col" class="manage-column">Título</th>
					 <th scope="col" class="manage-column">Slug</th>
					 <th scope="col" class="manage-column">Ações</th>
				 </tr>
			 </thead>
			 <tbody>
				 <?php
     $args = [
       "post_type" => "banners_group",
       "posts_per_page" => -1,
     ];
     $banners_query = new WP_Query($args);

     if ($banners_query->have_posts()):
       while ($banners_query->have_posts()):

         $banners_query->the_post();
         $edit_url = admin_url(
           "admin.php?page=bit-ionic-angular-banner&post_id=" . get_the_ID()
         );
         $delete_url = get_delete_post_link(get_the_ID());
         ?>
						 <tr>
							 <td><strong><?php the_title(); ?></strong></td>
							 <td><?php echo esc_html(get_post_field("post_name", get_the_ID())); ?></td>
							 <td>
								 <a href="<?php echo esc_url($edit_url); ?>" class="edit">Editar</a> | 
								 <a href="<?php echo esc_url(
           $delete_url
         ); ?>" class="delete" onclick="return confirm('Tem certeza que deseja excluir este grupo de banners?');">Excluir</a>
							 </td>
						 </tr>
						 <?php
       endwhile;
     else:
       echo '<tr><td colspan="3">Nenhum grupo de banners encontrado.</td></tr>';
     endif;
     wp_reset_postdata();?>
			 </tbody>
		 </table>
	 </div>
	 <?php
  }

  public function page_init_banners()
  {
    register_setting(
      "bit_ionic_angular_banners_group",
      "bit_ionic_angular_banners",
      [$this, "sanitize_banners"]
    );
    add_settings_section(
      "bit_ionic_angular_banners_settings",
      "Configuração de Banners",
      null,
      "bit-ionic-angular-banners"
    );
  }

  public function sanitize_banners($input)
  {
    $new_input = [];
    return $new_input;
  }

  public function load_media_files()
  {
    wp_enqueue_media(); ?>
	<script>
	  jQuery(document).ready(function($) {
		$('button.upload-banner-image').click(function(e) {
		  e.preventDefault();
		  var button = $(this);
		  var custom_uploader = wp.media({
			title: 'Selecione a Imagem',
			button: {
			  text: 'Escolher Imagem'
			},
			multiple: false
		  }).on('select', function() {
			var attachment = custom_uploader.state().get('selection').first().toJSON();
			button.siblings('input').val(attachment.url);
		  }).open();
		});
	  });
	</script>
	<?php
  }
}
