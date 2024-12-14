<?php
// handle active menu:
$menu->on($activeMenuId, function($item, $menu) {

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
$menu->sort(fn ($a, $b) => $a->getOrder() <=> $b->getOrder());

// change to ol tag
$menu->tag('ul')->handle(
    fn($t) => (new \Tobento\Service\Menu\Tag('ol'))->level($t->getLevel())
);

// add classes for design:
$menu->tag('ul')->level(0)->class('menu-breadcrumb');
?>
<?php if ($menu->hasItems()) { ?>
    <div class="page-breadcrumb">
        <nav><?= $menu ?></nav>
    </div>
<?php } ?>