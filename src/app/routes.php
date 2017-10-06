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

$app->get('/note/{id}', function ($id) use ($app) {

    $sql = 'SELECT * FROM reviews WHERE id = ?';
    $note = $app['db']->fetchAssoc($sql, [(int)$id]);
    $prev = $id - 1;
    $next = $id + 1;

    return $app['twig']->render('note.twig', [
        'note' => $note, 'prev' => $prev, 'next' => $next
    ]);

});
