<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RoutesTestController extends WebTestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful($url)
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'Claire',
            'PHP_AUTH_PW'   => 'claire',
        ]);

        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }
    public function urlProvider()
    {
        return array(
            array('/'),
            array('/question/1'),
            array('/question/add'),
            array('/question/edit/1'),
            // array('/admin/question/toggle/1'), 302
            // array('/answer/validate/1'), User = author only
            // array('/admin/answer/toggle/1'), 302
            array('/admin/tag/new'),
            array('/admin/tag/1'),
            array('/admin/tag/1/edit'),
            // ...
        );
    }
}
