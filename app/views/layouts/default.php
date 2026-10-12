<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie-edge">
    <?=$this->getMeta(); ?>

</head>
<body>

    <h1>Шаблон DEFAULT</h1>

<?= $content; ?>


<?php

$logs = \R::getDatabaseAdapter()
    ->getDatabase()
    ->getLogger();

// журнал SQL есть только в режиме отладки (R::debug включается в Db при DEBUG)
if (DEBUG && $logs) debug( $logs->grep( 'SELECT' ) );


?>
</body>
</html>