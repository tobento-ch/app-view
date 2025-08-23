<?php foreach($messages as $message) { ?>
<p data-message="<?= $view->esc($message->level()) ?>" class="message <?= $view->esc($message->level()) ?>"><?= $view->esc($message->message()) ?></p>
<?php } ?>