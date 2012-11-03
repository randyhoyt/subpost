<?php

require_once('../../../wp-admin/admin.php');

if (isset($_REQUEST["action"]) && $_REQUEST["action"]=="save") {

	$nonce = $_REQUEST['_wpnonce'];
	if (! wp_verify_nonce($nonce, 'subpost') ) wp_die("You do not have permission to do that.");

	if ($_POST['form_submit']=="Move to Trash") {

		$post_id = (int) $_POST["ID"]; 
		if ($post_id > 0) {
			$post = get_post($post_id);
		}
		$result = wp_trash_post($post->ID);		

	} else {

		// save and return with JavaScript
		$post_id = (int) $_POST["ID"]; 
		if ($post_id > 0) {
			$post = get_post($post_id);
		}

		if($post->ID == 0) {
			$post = new stdClass;
			$post->post_status = 'publish';
		}

		$post->post_title = $_POST["post_title"];
		$post->post_content = $_POST["post_content"];
		$post->post_type = $_POST["post_type"];
		$post->post_parent = $_POST["post_parent"];	
		$post->ID = wp_insert_post($post);

		// TO DO -- save custom fields

		do_action('subpost_save_form_fields',$post);

	}

?><!DOCTYPE html>
<html>
<head>
	<script>
		var display_html = '<?php echo str_replace("'","\\\'",subpost_display_all_children($_POST["post_parent"], $post->post_type)); ?>';
		var win = window.dialogArguments || opener || parent || top;
		win.subpost_list_children(display_html,"<?php echo $post->post_type; ?>");			
	</script>
</head>
<body>	
</body>
</html><?php

	exit();

} else {

	$mode = "add";

	$child = new stdClass;
	$child->ID = 0;
	$child->post_title = "";	
	$child->post_content = "";	
	$post_id = 0;

	if (isset($_REQUEST['post'])) {
	   $post_id = (int) trim($_REQUEST['post']);
    }
	if ($post_id > 0) {
		$child = get_post($post_id);
	}

	if ($child->ID > 0) {
		$mode = "edit";
	} else {
		$post_parent = (int) $_REQUEST['post_parent'];
		$child->post_parent = $post_parent;
		$child->post_type = $_REQUEST['post_type'];	
	}

}

?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="<?php echo get_bloginfo('url') . ('/wp-admin/load-styles.php?c=1&dir=ltr&load=admin-bar,wp-admin,media'); ?>" />	
	<link rel="stylesheet" type="text/css" href="<?php echo get_bloginfo('url') . ('/wp-admin/css/colors-fresh.css'); ?>" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<style>
		.deletelink {color: red !important; border: 0 !important; font-weight: normal !important;}
		.deletelink:hover {color: white !important; background: red; cursor: pointer; text-decoration: underline; }
	</style>		
</head>
<body style="min-height: 0px; height: auto;">

	<?php
		$form_title = $_REQUEST['form_title'];
		if ($form_title!="") {
	?><h3 style="padding-left: 1.25em;" class="media-title"><?php echo $form_title; ?></h3><?php
		}
	?>

	<form method="post" class="media-upload-form"><div id="media-items"><div style="margin: 1em;" class="media-item"><div style="padding: 1em;">

		<input type="hidden" name="action" value="save" />
		<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('subpost'); ?>" />
		<input type="hidden" name="post_parent" value="<?php echo $child->post_parent; ?>" />
		<input type="hidden" name="post_type" value="<?php echo $child->post_type; ?>" />
		<?php if ($mode=="edit") { ?><input type="hidden" name="ID" value="<?php echo $child->ID; ?>" /><?php } ?>

		<table class="describe">
			<tbody style="border: 1px solid white;">
				<?php // TO DO -- check if it supports the title ?? { ?>				
				<tr>
					<th valign="top" scope="row" class="label" style="width:130px;">
						<span class="alignleft"><label for="src">Title</label></span>
						<span class="alignright"><abbr id="status_img" title="required" class="required">*</abbr></span>
					</th>
					<td class="field"><input type="text" name="post_title" autofocus value="<?php echo esc_attr($child->post_title); ?>"></td>
				</tr>
				<?php // } ?>
				<?php // TO DO -- check if it supports the title ?? { ?>				
				<tr>
					<th valign="top" scope="row" class="label" style="width:130px;">
						<span class="alignleft"><label for="src">Content</label></span>
						<span class="alignright"></span>
					</th>
					<td class="field"><textarea name="post_content"><?php echo $child->post_content; ?></textarea></td>
				</tr>
				<?php // } ?>

				<?php echo apply_filters('subpost_form_fields','',$child); ?>

				<tr>
					<th valign="top" scope="row" class="label" style="width:130px; padding-top: 1.5em;">
						
					</th>
					<td style="padding-top: 1.5em;" class="image-only">
						<input type="submit" class="button-secondary" value="Save" />
						<span class="alignright"><?php if ($mode=="edit") { ?><input name="form_submit" type="submit" class="deletelink" value="Move to Trash" /><?php } ?></span>
					</td>
				</tr>
			</tbody>
		</table>

	</div></div></div></form>

</body>
</html>