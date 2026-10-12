<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ошибка</title>
</head>
<body>

<h1>Произошла ошибка</h1>
<p><b>Код ошибки:</b> <?= isset($errno) ? $errno : 0; ?></p>
<p><b>Текст ошибки:</b> <?= isset($errstr) ? $errstr : 'Unknown error'; ?></p>
<p><b>Файл, в котором произошла ошибка:</b> <?= isset($errfile) ? $errfile : 'Unknown file'; ?></p>
<p><b>Строка, в которой произошла ошибка:</b> <?= isset($errline) ? $errline : '-'; ?></p>

</body>
</html>