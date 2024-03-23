<?php foreach($messages as $message) { ?>
<p class="message <?= $view->esc($message->level()) ?>"><?= $view->esc($message->message()) ?></p>
<?php } ?>