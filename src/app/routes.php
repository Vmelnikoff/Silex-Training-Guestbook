<?php

$app->get('/', function () use ($app) {

    $notes = $app['db']->fetchAll('SELECT * FROM reviews');

    $cnt = count($notes);
    for ($i = 0; $i < $cnt; $i++) {
        $notes[$i]['note'] = substr($notes[$i]['note'], 0, 256) . '...';
    }

    return $app['twig']->render('index.twig', [
        'notes' => $notes,
    ]);

});

$app->get('/add', function () use ($app) {
    return $app['twig']->render('form.twig');
});

$app->post('/add', function () use ($app) {

    $name = $_POST['name'];
    $note = strip_tags($_POST['text']);
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
    {
        $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
    {
        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $sql = "INSERT INTO reviews (name, note, date, ip) VALUES (?, ?, CURDATE(), INET_ATON('$ip'))";
    $stmt = $app['db']->prepare($sql);
    $stmt->bindValue(1, $name);
    $stmt->bindValue(2, $note);
    $stmt->execute();

    return $app['twig']->render('form-success.twig');
});

$app->get('/note/{id}', function ($id) use ($app) {

    $sql = 'SELECT * FROM reviews WHERE id = ?';
    $note = $app['db']->fetchAssoc($sql, [(int)$id]);
    $prev = $id - 1;
    $next = $id + 1;

    return $app['twig']->render('note.twig', [
        'note' => $note, 'prev' => $prev, 'next' => $next
    ]);

});
