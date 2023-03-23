<?php
$table = $view->table(name: 'test');
$table->row([
    'title' => 'Title',
    'description' => 'Description',
])->heading();

$table->row([
    'title' => 'Lorem',
    'description' => 'Lorem ipsum',
]);
?>
<?= $table ?>