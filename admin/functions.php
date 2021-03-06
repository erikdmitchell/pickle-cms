<?php
/**
 * pickle_cms_get_admin_page function.
 * 
 * @access public
 * @param bool $template_name (default: false)
 * @param mixed $attributes (default: null)
 * @return void
 */
function pickle_cms_get_admin_page($template_name=false, $attributes=null) {
	if (!$attributes )
		$attributes = array();

	if (!$template_name)
		return false;

	include(PICKLE_CMS_PATH.'admin/pages/'.$template_name.'.php');

	$html=ob_get_contents();

	if (ob_get_length()) ob_end_clean();

	return $html;
}

/**
 * is_pickle_cms_admin_page function.
 *
 * @access public
 * @return void
 */
function is_pickle_cms_admin_page() {
	if (isset($_GET['page']) && $_GET['page']=='pickle-cms')
		return true;

	return false;
}

/**
 * get_pickle_cms_admin_tab function.
 *
 * @access public
 * @return void
 */
function get_pickle_cms_admin_tab() {
	if (isset($_GET['page']) && $_GET['page']=='pickle-cms' && isset($_GET['tab']))
		return $_GET['tab'];

	return false;
}
?>