<?php
// ======================================================================================
// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or(at your option) any later version.
//
// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.
// ======================================================================================


/*
Plugin Name: Wordpress Uploaded Files Cleaner
Plugin URI: 
Description: Manage Upload folder to remove unused files and save disk space
Author: Christophe Perinaud
Version: 1.2
Author URI: 
*/

// Wordpress Uploaded Files Cleaner - Version
$WUFC_Version = "1.2";

Class File_in_uploads_dir {
	var $filename;
	var $filetype;
	var $full_filename;
	var $filesize;
	var $posts;
	var $posts_types;
	function File_in_uploads_dir() {
		$filename = "";
		$filetype = "";
		$full_filename = "";
		$filesize = "";
		$posts = array();
		$posts_types = array();
	}
}

// Where the selected files will be moved to
$destination_folder = "WUFC_moved_files";

/**
 * Main routine that will be used to add the menu entry and load the css file for the table result
 *
 * @param  	none
 * @return  none
 */
function WUFC_admin() {
	// Add menu entry
	add_options_page("Wordpress Uploaded Files Cleaner", "Wordpress Uploaded Files Cleaner", 1, "wordpress-uploaded-files-cleaner", "WUFC_param");
	// Load CSS file
	wp_register_style('WUFC-style-1', plugins_url('wordpress-uploaded-files-cleaner/theme/style.css'));
	wp_enqueue_style( 'WUFC-style-1');
	wp_register_style('WUFC-style-2', plugins_url('wordpress-uploaded-files-cleaner/css/style.css'));
	wp_enqueue_style( 'WUFC-style-2');
	wp_register_style('WUFC-style-3', plugins_url('wordpress-uploaded-files-cleaner/theme/skin-vista/ui.dynatree.css'));
	wp_enqueue_style( 'WUFC-style-3');
	if ( isset($_POST['folders_to_check']) ) {
		// Load jQuery script
		wp_enqueue_script( 'jquery');
		// Load jQuery UI script
		wp_enqueue_script( 'jquery-ui-core' );
		// Load table sorter scripts
		wp_register_script('WUFC-scripts-sort-1', plugins_url('wordpress-uploaded-files-cleaner/javascript/jquery-latest.js'));
		wp_enqueue_script( 'WUFC-scripts-sort-1');
		wp_register_script('WUFC-scripts-sort-2', plugins_url('wordpress-uploaded-files-cleaner/javascript/jquery.tablesorter.js'));
		wp_enqueue_script( 'WUFC-scripts-sort-2');
		wp_register_script('WUFC-scripts-select', plugins_url('wordpress-uploaded-files-cleaner/javascript/tableselect.js'));
		wp_enqueue_script( 'WUFC-scripts-select');
	} else if (isset($_POST['moving_files'])) {
	} else {
		wp_register_script('WUFC-scripts-tree-1', plugins_url('wordpress-uploaded-files-cleaner/javascript/jquery/jquery.js'));
		wp_enqueue_script( 'WUFC-scripts-tree-1');
		wp_register_script('WUFC-scripts-tree-2', plugins_url('wordpress-uploaded-files-cleaner/javascript/jquery/jquery-ui.custom.js'));
		wp_enqueue_script( 'WUFC-scripts-tree-2');
		wp_register_script('WUFC-scripts-tree-3', plugins_url('wordpress-uploaded-files-cleaner/javascript/jquery/jquery.cookie.js'));
		wp_enqueue_script( 'WUFC-scripts-tree-3');
		wp_register_script('WUFC-scripts-tree-20', plugins_url('wordpress-uploaded-files-cleaner/javascript/jquery.dynatree.js'));
		wp_enqueue_script( 'WUFC-scripts-tree-20');
	}	
}

/**
 * Will display the title of the page
 *
 * @param  	none
 * @return  none
 */
function WUFC_Display_Title() {
	echo '<h2>Wordpress Uploaded Files Cleaner</h2>';
	echo '<h3>Move all unused files from your <em>uploads</em> folder to a dedicated folder</h3>';
	echo '<br/>';
}

/**
 * Will display the footer of the page
 *
 * @param  	none
 * @return  none
 */
function WUFC_Display_Footer() {
	global $WUFC_Version;
	echo '<h3>Version '.$WUFC_Version.' by <a href="mailto:christophe.perinaud@free.fr">Christophe Périnaud</a></h3>';
}

/**
 * Main action routine. Will detect blog urls and based on POST params will start be set
 *
 * @param  	none
 * @return  none
 */
function WUFC_param() {
	// Look for blog URL
	//$blog_url = get_site_url();
	// Look for Uploads folder
	$upload_dir = wp_upload_dir();
	// Display the plugin page title
	WUFC_Display_Title();
	if ( isset($_POST['folders_to_check']) ) {
		// Action to do if there is a list of folders to control
		WUFC_List_And_Display_Selected_Folders_Content($upload_dir);
	} else if (isset($_POST['moving_files'])) {
		// Action to do if files have to be moved
		WUFC_Move_files($upload_dir);
	} else {
		/*
		echo 'Blog URL : '. $blog_url . '<br />';
		echo 'path     : ' . $upload_dir['path'] . '<br />';
		echo 'url      : ' . $upload_dir['url'] . '<br />';
		echo 'basedir  : ' . $upload_dir['basedir'] . '<br />';
		echo 'baseurl  : ' . $upload_dir['baseurl'] . '<br />';	
		echo '<br /><br />';
		*/
		// Default action if no parameters are set. It will list subfolders of 'upload' folder.
		WUFC_List_Folders_In_UploadDir($upload_dir);
	}
	// Display the footer
	WUFC_Display_Footer();
}

/**
 * Will move files from original folder to 'destination_folder'
 *
 * @param  	start_folder : array of all url/path of upload folders
 * @return  none
 */
function WUFC_Move_files($start_folder) {
	// Use global variable
	global $destination_folder;
	echo '<h3>Step 3 of 3</h3>';
	echo '<div class="metabox-holder"><div class="postbox">';
	echo '<h3><span class="global-settings">&nbsp;</span>Actions performed</h3>';
	echo '<ul class="result_actions">';
	// Create destination folder (even if it already exists)
	echo '<li>&nbsp;Create folder '.$start_folder['basedir'].'/'.$destination_folder.' if does not already exist : ';
	$destination_folder_exists = false;
	if (!is_dir($start_folder['basedir'].'/'.$destination_folder)) {
		if (!mkdir($start_folder['basedir'].'/'.$destination_folder,0755,true)) {
			echo 'error</li>';
		} else {
			$destination_folder_exists = true;
		}
	} else {
		$destination_folder_exists = true;
	}
	// Move each file in the list
	if ($destination_folder_exists) {
		echo 'done</li>';
		foreach($_POST as $key => $val) {
			if ( strpos($key, "file_") !== false ) {
				// Get all subfolders of the file path and create them one by one
				$subfolders = explode('/', $val);
				// don't parse first cell, always empty
				// don't parse last cell, always filename
				for ($i=1 ; $i<count($subfolders)-1 ; $i++) {
					// Build subfolder
					$folder_to_create = '';
					for ($j=0 ; $j<=$i ; $j++) {
						$folder_to_create = $folder_to_create.$subfolders[$j].'/';
					}
					// Create subfolder
					mkdir($start_folder['basedir'].'/'.$destination_folder.$folder_to_create);
				}
				// Move the file
				echo '<li>&nbsp;Move file '.$val.' : ';
				if (!rename($start_folder['basedir'].$val, $start_folder['basedir'].'/'.$destination_folder.$val)) {
					echo 'error</li>';
				} else {
					echo 'done</li>';
				}
			}
		}
	}
	echo '</ul></div></div>';
	echo '<br/><br/><form action="" method="post"><input type="submit" name="back" value="Go back to step 1" /></form>';
}

/**
 * Will display a tool bar at the top of the table
 *
 * @param  	none
 * @return  none
 */
function WUFC_Display_Toolbar() {
	echo '<div class="filters">';
	echo '<a href="javascript:Check_All_Box(\'files_table\')">Select all</a> - ';
	echo '<a href="javascript:Uncheck_All_Box(\'files_table\')">Select none</a> - ';
	echo '<a href="javascript:Invert_All_Box(\'files_table\')">Invert selection</a> - ';
	echo '<a href="javascript:Unused_All(\'files_table\')">All unused files</a> - ';
	echo '<a href="javascript:Used_All(\'files_table\')">All used files</a>';
	echo '</div>';
}

/**
 * Will display a fixed area that will show a summary of selected files
 *
 * @param  	none
 * @return  none
 */
function WUFC_Display_Fixed_Div() {
	echo '<div id="fixed-div"></div>';
}

/**
 * Will display all files in selected folders
 *
 * @param  	folders_content : files that have to be displayed
 * @return  none
 */
function WUFC_Display_Selected_Folders_Content($folders_content) {
	global $destination_folder;
	WUFC_Display_Fixed_Div();
	// Header of the table
	echo '<h3>Step 2 of 3</h3>';
	WUFC_Display_Toolbar();
	echo '<form action="" method="post">';
	echo '<table id="files_table" class="tablesorter">';
	echo '<thead> ';
	echo '<tr> ';
	echo '    <th></th> ';
	echo '    <th>Filename</th> ';
	echo '    <th>Filetype</th> ';
	echo '    <th>Filesize</th> ';
	echo '    <th>Posts using the file</th> ';
	echo '</tr> ';
	echo '</thead> ';
	echo '<tbody>';
	// Body of the table
	$folders_content_size = count($folders_content);
	for($i=0; $i<=$folders_content_size; $i++) {
		if ($folders_content[$i]->filename != "") {
			// For each file, build line by line of the table
			echo '<tr>';
		    echo '  <td><input onclick="UpdateCheck_Click(\'files_table\','.$i.');" name="file_'.$i.'" id="file_'.$i.'" type="checkbox" value="'.$folders_content[$i]->filename.'"></td>';
      		echo '  <td>'.$folders_content[$i]->filename.'</td>';
      		echo '  <td>'.$folders_content[$i]->filetype.'</td>';
      		echo '  <td>'.$folders_content[$i]->filesize.'</td>';
      		$posts_count = count($folders_content[$i]->posts);
      		if ($posts_count > 0) {
      			// If some posts or pages use the file, then display links
      			$posts_titles = array_keys($folders_content[$i]->posts);
			    echo '  <td>';
			    foreach($posts_titles as $key) {
				    echo '<a href="'.$folders_content[$i]->posts[$key].'">'.$key.'</a> ('.$folders_content[$i]->posts_types[$key].')<br />';
			    }
			    echo '</td>';
      		} else {
		    	echo '  <td>&nbsp;</td>';
		    }
    		echo '</tr>';		
		}
	}	
	echo '</tbody>';
	echo '</table>';
	echo '<input type="hidden" id="table_length" value="'.$folders_content_size.'" />';
	echo 'The destination folder is '.$destination_folder.'<br/>';
	echo '<p><input type="submit" name="moving_files" value="Go to step 3 : Move selected files" /><input type="submit" name="back" value="Go back to step 1" /></p>';
	echo '</form>';
}

/**
 * Will display all folders that are in the 'upload' folder
 *
 * @param  	start_folder : array of all url/path of upload folders
 * @return  none
 */
function WUFC_List_Folders_In_UploadDir($start_folder) {
	// List folders
	echo '<h3>Step 1 of 3</h3>';
	//echo 'This plugin will list all files that are in selected subfolders and check if they are used in a post or a page.<br/><br/>';
	
	echo '<form action="" method="post">';
	echo '<div class="metabox-holder"><div class="postbox">';
	echo '<h3><span class="global-settings">&nbsp;</span>Please select folders that you want to analyse</h3>';
	echo '<input type="hidden" name="redirect" value="true" />';
	//echo '<input type="text" name="nb_folders_to_check" value="'.$nb_folders_to_check.'" />';
	echo '<input type="hidden" name="folders_to_check" id="folders_to_check" value="" />';
	echo '<div id="tree"></div>';
	echo '</div></div>';
	echo '<input type="submit" name="submit" value="&nbsp;Go to step 2 : Analyse selected folders&nbsp;" />';
	echo '</form>';
	echo '<script type="text/javascript">';
	echo '    $(function(){
				$("#tree").dynatree({
					checkbox: true,
					selectMode: 3,
					onSelect: function(select, node) {
						var selKeys = $.map(node.tree.getSelectedNodes(), function(node){
						  return node.data.key;
						});
						var oField = document.getElementById(\'folders_to_check\');
						oField.value = selKeys.join(",");
					},
					persist: true,
					children: [';
	WUFC_Get_Folders_To_Scan($start_folder['basedir'], 0, "");
	echo '			]
				});
			});';
    echo '</script>';
}

/**
 * Get folders structure from uploads folder
 *
 * @param  	start_folder : array of all url/path of upload folders
 * @return  folders structure
 */

function WUFC_Get_Folders_To_Scan( $path, $level, $start_path){
	$scan = scandir($path);
	foreach ($scan as $fileFound) {
		if ($fileFound != "." && $fileFound != "..") {
			if (is_Dir($path.'/'.$fileFound)) {
				if ($level == 0) {
					$new_path = $fileFound;
				} else {
					$new_path = $start_path.'/'.$fileFound;
				}
				$dir_size = sizeof(scandir($path.'/'.$fileFound)) - 2; // Remove . and ..
				echo '{title: "'.$fileFound.' ('.$dir_size.' elements)", key:"'.$new_path.'", isFolder: true,';
				echo 'children: [';
				WUFC_Get_Folders_To_Scan($path.'/'.$fileFound,$level+1, $new_path);
				echo ']},';
			} else {
				
			}
		}
	}	
}

/**
 * First will search for files in all subfolders and then call function to display the list in a table
 * Once the table will be displayed, it will load style and script for the sortable table.
 *
 * @param  	start_folder : array of all url/path of upload folders
 * @return  none
 */
function WUFC_List_And_Display_Selected_Folders_Content($start_folder) {
	$folders_content = array();
	$folders_to_check = explode(",", $_POST['folders_to_check']);
	for($i=0; $i<count($folders_to_check); $i++) {
		$folder_content = WUFC_List_Folder_Content($start_folder, $folders_to_check[$i]);
		$folders_content = array_merge($folders_content, $folder_content);
	}
	WUFC_Display_Selected_Folders_Content($folders_content);
}

/**
 * 
 * Build a list of files found in the folder 'folder_to_list'
 *
 * @param  	start_folder : array of all url/path of upload folders
 * @param  	folder_to_list : folder to list
 * @return  folder_content : a list of files in the folder to list
 */
function WUFC_List_Folder_Content($start_folder, $folder_to_list) {
	global $wpdb;
	$folder_content = array();
	/*
	$iterator_uploads = new RecursiveDirectoryIterator($start_folder['basedir'] . '/' . $folder_to_list);
	$iterator_files = new RecursiveIteratorIterator($iterator_uploads);	
	*/
	$iterator_files = new DirectoryIterator($start_folder['basedir'] . '/' . $folder_to_list);

	foreach ($iterator_files as $full_filename) {
		if (! $full_filename->isDir()) {
			$current_file = new File_in_uploads_dir();
			$current_file->filename = str_replace($start_folder['basedir'],"",$full_filename);
			$current_file->full_filename = str_replace($start_folder['basedir'],"",$full_filename->getPath()).'/'.$current_file->filename;
			$current_file->filename = $current_file->full_filename;
			$current_file->filesize = $full_filename->getSize();
			$current_file->filetype = get_mime_type($full_filename);
			// Search for links in Blog's posts and pages
			$search_string = 's=' . $current_file->filename;
			$search_query = new WP_Query(); 
			$search_posts = $search_query->query($search_string); 
			if (count($search_posts) > 0) {
				foreach($search_posts as $search_post) {
					$current_file->posts[$search_post->post_title] = get_permalink( $search_post->ID );
					$current_file->posts_types[$search_post->post_title] = "link";
				}
			}
			// Gallery : wp_posts -> guid = URL and post_type = attachment
			$gallery_items = $wpdb->get_results("SELECT ID, post_title, post_parent FROM wp_posts WHERE guid LIKE '%".$current_file->filename."' AND post_type= 'attachment'");
			if (count($gallery_items) > 0) {
				foreach($gallery_items as $gallery_item) {
					$current_file->posts[$gallery_item->post_title] = get_permalink( $gallery_item->post_parent );
					$current_file->posts_types[$gallery_item->post_title] = "attachment";
				}
			}
			// wp_postmeta : meta_value = URL
			$postmeta_items = $wpdb->get_results("SELECT wp_posts.id, wp_posts.post_title FROM wp_posts, wp_postmeta WHERE wp_postmeta.meta_value LIKE '%".$current_file->filename."' AND wp_postmeta.post_id = wp_posts.ID");
			if (count($postmeta_items) > 0) {
				foreach($postmeta_items as $postmeta_item) {
					$current_file->posts[$postmeta_item->post_title] = get_permalink( $postmeta_item->id );
					$current_file->posts_types[$postmeta_item->post_title] = "postmeta";
				}
			}
			$folder_content[] = $current_file;
		}
	}
	return $folder_content;
}

// Add the plugin menu in the extensions admin menu
add_action( 'admin_menu', 'WUFC_admin' );

// Use built-in mime type detector to avoid errors using finfo function
function get_mime_type($file) {
	$mime_types = array("323" => "text/h323",
		"acx" => "application/internet-property-stream",
		"ai" => "application/postscript",
		"aif" => "audio/x-aiff",
		"aifc" => "audio/x-aiff",
		"aiff" => "audio/x-aiff",
		"asf" => "video/x-ms-asf",
		"asr" => "video/x-ms-asf",
		"asx" => "video/x-ms-asf",
		"au" => "audio/basic",
		"avi" => "video/x-msvideo",
		"axs" => "application/olescript",
		"bas" => "text/plain",
		"bcpio" => "application/x-bcpio",
		"bin" => "application/octet-stream",
		"bmp" => "image/bmp",
		"c" => "text/plain",
		"cat" => "application/vnd.ms-pkiseccat",
		"cdf" => "application/x-cdf",
		"cer" => "application/x-x509-ca-cert",
		"class" => "application/octet-stream",
		"clp" => "application/x-msclip",
		"cmx" => "image/x-cmx",
		"cod" => "image/cis-cod",
		"cpio" => "application/x-cpio",
		"crd" => "application/x-mscardfile",
		"crl" => "application/pkix-crl",
		"crt" => "application/x-x509-ca-cert",
		"csh" => "application/x-csh",
		"css" => "text/css",
		"dcr" => "application/x-director",
		"der" => "application/x-x509-ca-cert",
		"dir" => "application/x-director",
		"dll" => "application/x-msdownload",
		"dms" => "application/octet-stream",
		"doc" => "application/msword",
		"dot" => "application/msword",
		"dvi" => "application/x-dvi",
		"dxr" => "application/x-director",
		"eps" => "application/postscript",
		"etx" => "text/x-setext",
		"evy" => "application/envoy",
		"exe" => "application/octet-stream",
		"fif" => "application/fractals",
		"flr" => "x-world/x-vrml",
		"gif" => "image/gif",
		"gtar" => "application/x-gtar",
		"gz" => "application/x-gzip",
		"h" => "text/plain",
		"hdf" => "application/x-hdf",
		"hlp" => "application/winhlp",
		"hqx" => "application/mac-binhex40",
		"hta" => "application/hta",
		"htc" => "text/x-component",
		"htm" => "text/html",
		"html" => "text/html",
		"htt" => "text/webviewhtml",
		"ico" => "image/x-icon",
		"ief" => "image/ief",
		"iii" => "application/x-iphone",
		"ini" => "text/plain",
		"ins" => "application/x-internet-signup",
		"isp" => "application/x-internet-signup",
		"jfif" => "image/pipeg",
		"jpe" => "image/jpeg",
		"jpeg" => "image/jpeg",
		"jpg" => "image/jpeg",
		"js" => "application/x-javascript",
		"latex" => "application/x-latex",
		"lha" => "application/octet-stream",
		"lsf" => "video/x-la-asf",
		"lsx" => "video/x-la-asf",
		"lzh" => "application/octet-stream",
		"m13" => "application/x-msmediaview",
		"m14" => "application/x-msmediaview",
		"m3u" => "audio/x-mpegurl",
		"man" => "application/x-troff-man",
		"mdb" => "application/x-msaccess",
		"me" => "application/x-troff-me",
		"mht" => "message/rfc822",
		"mhtml" => "message/rfc822",
		"mid" => "audio/mid",
		"mny" => "application/x-msmoney",
		"mov" => "video/quicktime",
		"movie" => "video/x-sgi-movie",
		"mp2" => "video/mpeg",
		"mp3" => "audio/mpeg",
		"mpa" => "video/mpeg",
		"mpe" => "video/mpeg",
		"mpeg" => "video/mpeg",
		"mpg" => "video/mpeg",
		"mpp" => "application/vnd.ms-project",
		"mpv2" => "video/mpeg",
		"ms" => "application/x-troff-ms",
		"mvb" => "application/x-msmediaview",
		"nws" => "message/rfc822",
		"oda" => "application/oda",
		"p10" => "application/pkcs10",
		"p12" => "application/x-pkcs12",
		"p7b" => "application/x-pkcs7-certificates",
		"p7c" => "application/x-pkcs7-mime",
		"p7m" => "application/x-pkcs7-mime",
		"p7r" => "application/x-pkcs7-certreqresp",
		"p7s" => "application/x-pkcs7-signature",
		"pbm" => "image/x-portable-bitmap",
		"pdf" => "application/pdf",
		"pfx" => "application/x-pkcs12",
		"pgm" => "image/x-portable-graymap",
		"pko" => "application/ynd.ms-pkipko",
		"pma" => "application/x-perfmon",
		"pmc" => "application/x-perfmon",
		"pml" => "application/x-perfmon",
		"pmr" => "application/x-perfmon",
		"pmw" => "application/x-perfmon",
		"png" => "image/png",
		"pnm" => "image/x-portable-anymap",
		"pot" => "application/vnd.ms-powerpoint",
		"ppm" => "image/x-portable-pixmap",
		"pps" => "application/vnd.ms-powerpoint",
		"ppt" => "application/vnd.ms-powerpoint",
		"prf" => "application/pics-rules",
		"ps" => "application/postscript",
		"pub" => "application/x-mspublisher",
		"qt" => "video/quicktime",
		"ra" => "audio/x-pn-realaudio",
		"ram" => "audio/x-pn-realaudio",
		"ras" => "image/x-cmu-raster",
		"rgb" => "image/x-rgb",
		"rmi" => "audio/mid",
		"roff" => "application/x-troff",
		"rtf" => "application/rtf",
		"rtx" => "text/richtext",
		"scd" => "application/x-msschedule",
		"sct" => "text/scriptlet",
		"setpay" => "application/set-payment-initiation",
		"setreg" => "application/set-registration-initiation",
		"sh" => "application/x-sh",
		"shar" => "application/x-shar",
		"sit" => "application/x-stuffit",
		"snd" => "audio/basic",
		"spc" => "application/x-pkcs7-certificates",
		"spl" => "application/futuresplash",
		"src" => "application/x-wais-source",
		"sst" => "application/vnd.ms-pkicertstore",
		"stl" => "application/vnd.ms-pkistl",
		"stm" => "text/html",
		"svg" => "image/svg+xml",
		"sv4cpio" => "application/x-sv4cpio",
		"sv4crc" => "application/x-sv4crc",
		"t" => "application/x-troff",
		"tar" => "application/x-tar",
		"tcl" => "application/x-tcl",
		"tex" => "application/x-tex",
		"texi" => "application/x-texinfo",
		"texinfo" => "application/x-texinfo",
		"tgz" => "application/x-compressed",
		"tif" => "image/tiff",
		"tiff" => "image/tiff",
		"tr" => "application/x-troff",
		"trm" => "application/x-msterminal",
		"tsv" => "text/tab-separated-values",
		"txt" => "text/plain",
		"uls" => "text/iuls",
		"ustar" => "application/x-ustar",
		"vcf" => "text/x-vcard",
		"vrml" => "x-world/x-vrml",
		"wav" => "audio/x-wav",
		"wcm" => "application/vnd.ms-works",
		"wdb" => "application/vnd.ms-works",
		"wks" => "application/vnd.ms-works",
		"wmf" => "application/x-msmetafile",
		"wps" => "application/vnd.ms-works",
		"wri" => "application/x-mswrite",
		"wrl" => "x-world/x-vrml",
		"wrz" => "x-world/x-vrml",
		"xaf" => "x-world/x-vrml",
		"xbm" => "image/x-xbitmap",
		"xla" => "application/vnd.ms-excel",
		"xlc" => "application/vnd.ms-excel",
		"xlm" => "application/vnd.ms-excel",
		"xls" => "application/vnd.ms-excel",
		"xlt" => "application/vnd.ms-excel",
		"xlw" => "application/vnd.ms-excel",
		"xof" => "x-world/x-vrml",
		"xpm" => "image/x-xpixmap",
		"xwd" => "image/x-xwindowdump",
		"z" => "application/x-compress",
		"zip" => "application/zip");
	$extension = strtolower(end(explode('.',$file)));
	return $mime_types[$extension];
}

?>