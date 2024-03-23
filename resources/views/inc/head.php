<meta name="theme-color" content="#bbfffd">

<?php if ($view->data()->get('csrfToken')) { ?>
    <meta name="csrf-token" content="<?= $view->esc($view->data()->get('csrfToken')) ?>">
<?php } ?>

<link rel="shortcut icon" href="favicon.ico">

<?php
$view->asset('css/basis.css');
$view->asset('css/app.css');
?>