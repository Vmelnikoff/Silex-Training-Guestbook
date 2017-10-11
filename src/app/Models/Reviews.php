<?php

namespace App\Models;


class Reviews
{
    public $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function mainPage()
    {
        $notes = $this->app['db']->fetchAll('SELECT * FROM reviews');

        $cnt = count($notes);
        for ($i = 0; $i < $cnt; $i++) {
            $notes[$i]['note'] = substr($notes[$i]['note'], 0, 256) . '...';
        }

        return $notes;
    }

    public function postMainPage($orderby)
    {
        $sql = 'SELECT * FROM reviews' . $orderby;
        $notes = $this->app['db']->fetchAll($sql);

        $cnt = count($notes);
        for ($i = 0; $i < $cnt; $i++) {
            $notes[$i]['note'] = substr($notes[$i]['note'], 0, 256) . '...';
        }

        return $notes;
    }

    public function votePage($id)
    {
        $likes = $this->app['db']->fetchColumn('SELECT likes FROM reviews WHERE id = ?', [(int)$id], 0);
        $likes++;
        $this->app['db']->update('reviews', ['likes' => $likes], ['id' => $id]);
    }

    public function postAddPage($ip, $name, $note)
    {
        $sql = "INSERT INTO reviews (name, note, date, ip) VALUES (?, ?, CURDATE(), INET_ATON('$ip'))";
        $stmt = $this->app['db']->prepare($sql);
        $stmt->bindValue(1, $name);
        $stmt->bindValue(2, $note);
        $stmt->execute();
    }

    public function notePage($id)
    {
        $sql = 'SELECT * FROM reviews WHERE id = ?';
        return $this->app['db']->fetchAssoc($sql, [(int)$id]);
    }
}