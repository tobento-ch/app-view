<?php
// handle active menu:
$view->menu('breadcrumb')->on($routeName, function($item, $menu) {
    
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

// sort menu by its order:
$view->menu('breadcrumb')
    ->sort(fn ($a, $b) => $a->getOrder() <=> $b->getOrder());

// change to ol tag
$view->menu('breadcrumb')->tag('ul')->handle(
    fn($t) => (new \Tobento\Service\Menu\Tag('ol'))->level($t->getLevel())
);

// add classes for design:
$view->menu('breadcrumb')
    ->tag('ul')
    ->level(0)->class('menu-breadcrumb');
?>
<?php if ($view->menu('breadcrumb')->hasItems()) { ?>
    <div class="page-breadcrumb">
        <nav><?= $view->menu('breadcrumb') ?></nav>
    </div>
<?php } ?>