<?php
include_once '../libs/simple_html_dom.php';
$html = file_get_html('nav.xml');
$navStr = '';
$mainCss = '';

foreach($html->find('main') as $main) {
	$mainItem = $main->find('item',0)->plaintext;
	$mainCss = (strtolower($pageItemName) == strtolower($mainItem)) ? 'active' : '';
    $subMenu = '';

	if ($main->find('sub', 0)) {
        // No sub menu
        $subMenu .= '<ul class="dropdown-menu">';

        foreach ($main->find('sub', 0)->find('subItem') as $sub) {
            $subMenu .= sprintf('<li><a href="%s">%s</a></li>',
                $sub->find('url', 0)->plaintext,
                $sub->find('title', 0)->plaintext);
        }

        $subMenu .= '</ul>';

		$navStr .= sprintf('<li class="%s"><a href="#" data-toggle="dropdown">%s</a>%s',
			$mainCss,
            $main->find('title', 0)->plaintext,
            $subMenu);
	} else {
        // Has sub menu
		$navStr .= sprintf('<li class="%s"><a href="%s">%s</a></li>',
			$mainCss,
			$main->find('url', 0)->plaintext,
			$main->find('title', 0)->plaintext);
	}
}
$html->clear();

$smarty->assign('nav', $navStr);
?>
