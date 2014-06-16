<div id="disguised">
    <?= sprintf('Eingeloggt als %s (%s)',
                htmlReady($GLOBALS['user']->getFullName()),
                htmlReady($GLOBALS['user']->username)) ?>
</div>


