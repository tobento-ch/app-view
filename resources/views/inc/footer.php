<?php
// handle active menu:
$view->menu('footer')->on($routeName, function($item, $menu) {
    
    $item->itemTag()->class('active');
    
    if ($item->getTreeLevel() > 0) {
        $item->parentTag()->class('active');
    }
    
    if ($item instanceof \Tobento\Service\Tag\Taggable) {
        $item->tag()->class('active');
    }
    
    if ($item instanceof Tobento\Service\Menu\Link) {
        $item = $item->withUrl('#');
    }
    
    return $item;
});

// add classes for design:
$view->menu('footer')
    ->tag('ul')
    ->level(0)->class('menu-h spaced menu-footer');
?>
<footer class="page-footer">
    <?php if ($view->menu('footer')->hasItems()) { ?>
        <nav><?= $view->menu('footer') ?></nav>
    <?php } ?>
</footer>