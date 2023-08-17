<?php


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CharactersControllerTest extends WebTestCase
{
    public function testHomePage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('p', 'Welcome to the Rick and Morty App');
    }

    public function testSearchByCharacterName(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $form = $crawler->filter('form')->form();

        $form['searchTerm'] = 'rick';
        $crawler = $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', '107 characters found');
    }
}
