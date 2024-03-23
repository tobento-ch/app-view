<?php
// handle active menu:
$view->menu('main')->on($routeName, function($item, $menu) {
    $item->itemTag()->class('active');
    
    if ($item->getTreeLevel() > 0) {
        $item->parentTag()->class('active');
    }
    
    $item->tag()->class('active');
    
    return $item;
});

// show only active items tree:
$view->menu('main')->active($routeName);
$view->menu('main')->subitems(false);

// add classes for design:
$view->menu('main')
    ->tag('ul')
    ->level(0)->class('menu-v spaced menu-main');
?>
<div class="page-nav">
<?php if ($view->menu('main')->hasItems()) { ?>
    <nav id="menu-main"><?= $view->menu('main') ?></nav>
<?php } ?>
</div>