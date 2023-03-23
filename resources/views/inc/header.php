<?php
// handle active menu item.
// We could do it also outside the view.

// handle active menu:
$view->menu('header')->on($routeName, function($item, $menu) {
    
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

// add menu link used for mobile breakpoint:
if ($view->menu('header')->hasItems()) {
    $view->menu('header')
        ->link('#menu-main', 'Menu')
        ->order(100)
        ->itemTag()
        ->class('menu-link');
}

// sort menu by its order:
$view->menu('header')
    ->sort(fn ($a, $b) => $b->getOrder() <=> $a->getOrder());

// add classes for design:
$view->menu('header')
    ->tag('ul')
    ->level(0)->class('menu-h spaced menu-header');
?>
<header class="page-header">
    <?php if ($view->menu('header')->hasItems()) { ?>
        <nav><?= $view->menu('header') ?></nav>
    <?php } ?>
</header>