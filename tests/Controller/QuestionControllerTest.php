<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class QuestionControllerTest extends WebTestCase
{
    /**
     * Test homepage
     */
    public function testHomepage()
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Questions posées');
    }

    /**
     * Test affiche question
     * pour s'assurer que la question n'est pas bloquée
     * on suit le premier lien de la home
     */
    public function testQuestionShow()
    {
        $client = static::createClient();

        // On navigue sur la home
        // On récupère un objet $crawler pour naviguer dans le DOM
        $crawler = $client->request('GET', '/');

        // On accède via un sélecteur CSS au premier lien de la liste
        // cette question ne sera pas bloquée
        $link = $crawler->filter('.question-container h2 a')->eq(0)->link();
        // On mémorise le titre du lien
        $title = $link->getNode()->textContent;
        // On clique sur le lien
        $client->click($link);
        // La page s'affiche-t-elle correctement ?
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        // On vérifie que c'est la bonne page qui s'est afichée après le clic
        $this->assertSelectorTextContains('.question-container h2', $title);
    }

    public function testUserButtons()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'GET',
            '/',
            [],
            [],
            ['PHP_AUTH_USER' => 'Michel', 'PHP_AUTH_PW' => 'michel']
        );
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(0, $crawler->filter('a:contains("Poser une question")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Mon compte")')->count());

        // Alt
        // $this->assertSelectorTextContains('#right a:nth-child(2)', 'Poser une question');
        // $this->assertSelectorTextContains('#right a:nth-child(3)', 'Mon compte');
    }

    public function testAdminUserAsAnon()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/user');
        
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminUserAsUser()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'Michel',
            'PHP_AUTH_PW'   => 'michel',
        ]);

        $client->request('GET','/admin/user');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testAdminUserAsAdmin()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'Claire',
            'PHP_AUTH_PW'   => 'claire',
        ]);

        $client->request('GET', '/admin/user');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
