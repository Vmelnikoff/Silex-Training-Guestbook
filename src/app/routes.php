<?php

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

$app->get('/', function () use ($app) {

    $notes = $app['db']->fetchAll('SELECT * FROM reviews');

    $cnt = count($notes);
    for ($i = 0; $i < $cnt; $i++) {
        $notes[$i]['note'] = substr($notes[$i]['note'], 0, 256) . '...';
    }

    return $app['twig']->render('index.twig', [
        'notes' => $notes, 'sortvote' => 'DESC', 'sortdate' => 'DESC',
    ]);

});

$app->post('/', function () use ($app) {

    $orderby = '';
    if (!empty($_POST['sortvote'])) {
        $orderby = ' ORDER BY likes ' . $_POST['sortvote'];
        $sortvote = ($_POST['sortvote'] == 'ASC') ? 'DESC' : 'ASC';
        $sortdate = 'ASC';
    } elseif (!empty($_POST['sortdate'])) {
        $orderby = ' ORDER BY date ' . $_POST['sortdate'];
        $sortdate = ($_POST['sortdate'] == 'ASC') ? 'DESC' : 'ASC';
        $sortvote = 'ASC';
    }

    $sql = 'SELECT * FROM reviews' . $orderby;
    $notes = $app['db']->fetchAll($sql);

    $cnt = count($notes);
    for ($i = 0; $i < $cnt; $i++) {
        $notes[$i]['note'] = substr($notes[$i]['note'], 0, 256) . '...';
    }


    return $app['twig']->render('index.twig', [
        'notes' => $notes, 'sortdate' => $sortdate, 'sortvote' => $sortvote,
    ]);

});

$app->get('/vote/{id}', function ($id) use ($app) {

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    if ($app['session']->get('user')[$id] != $ip) {
        $likes = $app['db']->fetchColumn('SELECT likes FROM reviews WHERE id = ?', [(int)$id], 0);
        $likes++;
        $app['db']->update('reviews', ['likes' => $likes], ['id' => $id]);
        $app['session']->set('user', [$id => $ip]);
    }

    return $app->redirect('/');
});

$app->get('/add', function () use ($app) {
    if (empty($data)) {
        $data = [
            'name' => '',
            'note' => '',
        ];
        $title = '';
    }
    return $app['twig']->render('form.twig', [
        'data' => $data, 'title' => $title,
    ]);
});

$app->post('/add', function () use ($app) {

    $app->register(new Silex\Provider\ValidatorServiceProvider());

    $constraint = new Collection([
        'name' => [new Length(['min' => 5]),
            new NotBlank()
        ],
        'note' => [new Length(['min' => 5]),
            new NotBlank()
        ]
    ]);

    $data = [
        'name' => $_POST['name'],
        'note' => strip_tags($_POST['text']),
    ];

    $errors = $app['validator']->validate($data, $constraint);

    if (count($errors) === 0) {

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $sql = "INSERT INTO reviews (name, note, date, ip) VALUES (?, ?, CURDATE(), INET_ATON('$ip'))";
        $stmt = $app['db']->prepare($sql);
        $stmt->bindValue(1, $data['name']);
        $stmt->bindValue(2, $data['note']);
        $stmt->execute();

        return $app['twig']->render('form-success.twig');

    } else {

        $title = 'Данные были заполнены не верно!';

        return $app['twig']->render('form.twig', [
            'data' => $data, 'title' => $title
        ]);

    }

});

$app->get('/note/{id}', function ($id) use ($app) {

    $sql = 'SELECT * FROM reviews WHERE id = ?';
    $note = $app['db']->fetchAssoc($sql, [(int)$id]);
    if ($id == 1) {
        $prev = 1;
    } else {
        $prev = $id - 1;
    }
    if ($id < count($note)) {
        $next = $id + 1;
    } else {
        $next = $id;
    }


    return $app['twig']->render('note.twig', [
        'note' => $note, 'prev' => $prev, 'next' => $next
    ]);

});
