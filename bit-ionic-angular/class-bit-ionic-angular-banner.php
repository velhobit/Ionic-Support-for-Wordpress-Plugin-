<?php
class BitIonicAngularBanner
{
  public function __construct()
  {
    add_action("admin_menu", [$this, "add_banner_management_page"]);
    add_action("admin_enqueue_scripts", [$this, "load_media_files"]);
  }

  /**
   * Adiciona o submenu para gerenciamento de banners
   */
  public function add_banner_management_page()
  {
    add_menu_page(
      "Gerenciar Banners",
      "Banners",
      "manage_options",
      "bit-ionic-angular-banner",
      [$this, "render_banner_management_page"],
      "dashicons-images-alt2",
      6
    );
    remove_menu_page("bit-ionic-angular-banner");
  }

  /**
   * Função para renderizar a página de gerenciamento de banners
   */
  public function render_banner_management_page()
  {
    $post_id = $_GET["post_id"] ?? 0;
    $banners = get_post_meta($post_id, "banners", true) ?: [];
    $links = get_post_meta($post_id, "links", true) ?: [];

    $post = get_post($post_id);
    $group_title = $post ? $post->post_title : "";
    $group_slug = $post ? $post->post_name : "";
    ?>
   <div class="wrap bit-banners-wrap">
	 <h1>Banners</h1>
	 <form method="post" action="<?php echo admin_url("admin-post.php"); ?>">
	   <?php
    settings_fields("bit_ionic_angular_banners_group");
    do_settings_sections("bit-ionic-angular-banner");
    ?>
	   <?php wp_nonce_field("bit_ionic_angular_banners_group"); ?>
	   
	   <div class="input-item">
		 <h2>Title</h2>
		 <input type="text" name="group_title" value="<?php echo esc_attr(
     $group_title
   ); ?>" />
	   </div>
	   <div class="input-item">
		 <h3>slug</h3>
		 <input type="text" name="group_slug" value="<?php echo esc_attr(
     $group_slug
   ); ?>" />
	   </div>
	   <div class="bit-buttons-group">
		 <button type="button" class="add-banner button-secondary">Add Banner</button>
		 <button type="submit" class="button-primary">Save Banners Group</button>
	   </div>
	   <div id="banner-list" class="banner-list">
		 <?php if (!empty($post_id)) {
     foreach ($banners as $index => $banner_id) {
       $banner_url = wp_get_attachment_url($banner_id);
       $link_url = $links[$index] ?? "";

       echo '<div class="banner-item">';
       echo '<div class="image">';
       echo '<img src="' . esc_url($banner_url) . '" />';
       echo "</div>";
       echo '<div class="content">';
       echo "<h4>" . esc_html(get_the_title($banner_id)) . "</h4>"; // Exibindo o título do banner
       echo "<p>alt: " .
         esc_html(get_post_meta($banner_id, "_wp_attachment_image_alt", true)) .
         "</p>"; // Exibindo o atributo alt da imagem
       echo "<p>caption: " .
         esc_html(get_post_field("post_excerpt", $banner_id)) .
         "</p>"; // Exibindo a legenda do banner
       echo '<input type="text" name="bit_ionic_angular_banners[]" value="' .
         esc_attr($banner_id) .
         '" class="banner-input" />';
       echo "<b>Link Url:</b>";
       echo '<input type="text" name="link[]" value="' .
         esc_attr($link_url) .
         '" class="link-input" />';
       echo '<button type="button" class="remove-banner">Remover</button>';
       echo "</div>";
       echo "</div>";
     }
   } ?>
	   </div>
	   <div class="bit-buttons-group">
		 <button type="button" class="add-banner button-secondary">Add Banner</button>
		 <button type="submit" class="button-primary">Save Banners Group</button>
	   </div>
	   <input type="hidden" name="post_id" value="<?php echo isset($_GET["post_id"])
      ? intval($_GET["post_id"])
      : ""; ?>" />
	   <input type="hidden" name="action" value="save_banner_group">
	 </form>
   </div>
	<style>
		.bit-banners-wrap .banner-item .image{
			margin-right: 10px;
			padding-right: 10px;
			border-right: 1px solid #cecece;
		}
		.bit-banners-wrap .banner-item .image img{
			max-height: 100px;
			width: auto;
		}
		.bit-banners-wrap .banner-item{
			padding: 10px;
			margin-bottom: 10px;
			border-bottom: 1px solid #cecece;
			display: flex;
		}
		.bit-banners-wrap .banner-item:last-child{
			border-bottom: none;
		}
		.bit-banners-wrap .banner-list{
			margin: 10px 0;
			padding: 5px 0;
			background-color: white;
			border: 1px solid #cecece;
			border-radius: 10px;
			min-height: 24px;
		}
		
		.bit-banners-wrap .banner-item .content p,
		.bit-banners-wrap .banner-item .content h4{
			margin:0;
		}
		.bit-banners-wrap .banner-item .content b,
		.bit-banners-wrap .banner-item .content input,
		.bit-banners-wrap .input-item input{
			display:block;
			width: 100%;
		}
		.bit-banners-wrap .input-item {
			margin-bottom: 10px;
		}
		.bit-banners-wrap .input-item label,
		.bit-banners-wrap .input-item h3,
		.bit-banners-wrap .input-item h2{
			margin: 0;
			font-weight: normal;
		}
		.bit-banners-wrap .banner-item .content input.banner-input{
			display: none;
		}
		.bit-banners-wrap .banner-item .remove-banner{
			background: transparent;
			border: none;
			color: red;
			border-bottom: red 1px solid;
			margin-top: 10px;
			cursor: pointer;
		}
	</style>
	<?php
  }

  /**
   * Função para carregar os scripts e o uploader de mídia
   */
  public function load_media_files()
  {
    wp_enqueue_media(); ?>
	<script>
	  document.addEventListener('DOMContentLoaded', function() {
		// Abrir o uploader de mídia quando clicar no botão
		jQuery('.add-banner').click(function(e) {
		  e.preventDefault();
		  var mediaUploader = wp.media({
			title: 'Select Images',
			button: { text: 'Choose Image' },
			multiple: true
		  }).on('select', function() {
			var selection = mediaUploader.state().get('selection');
			selection.each(function(attachment) {
			  console.log(attachment);
			  // Adiciona um novo banner na lista
			  var newBannerHTML = `
				<div class="banner-item">
					<div class="image">
				    	<img src="${attachment.attributes.url}" />
				    </div>
					<div class="content">
						<h4>${attachment.attributes.title}</h4>
						<p>alt: ${attachment.attributes.alt}</p>
						<p>caption: ${attachment.attributes.caption}</p>
				    	<input type="text" name="bit_ionic_angular_banners[]" value="${attachment.id}" class="banner-input" />
						<b>Link Url:</b>
						<input type="text" name="link[]" value="" class="link-input" />
				  	  	<button type="button" class="remove-banner">Remover</button>
				    </div>
				</div>`;
			  jQuery('#banner-list').append(newBannerHTML);
			});
		  }).open();
		});
	  });
	</script>
	<?php
  }
}
