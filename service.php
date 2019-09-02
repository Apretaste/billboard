<?php

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;

class BillboardService extends ApretasteService
{

    public $client;

    /**
     * Function executed when the service is called
     */
    public function _main()
    {
        // create a crawler
        $url = "http://www.billboard.com/charts/hot-100";

        $crawler = $this->getCrawler($url);

        // get tracks into array
        $tracks = [];

        $tracks[] = [
            "rank"       => "1",
            "song_title" => $crawler->filter('div.chart-number-one__title')->text(),
            "artist"     => $crawler->filter('div.chart-number-one__artist')->text(),
            "link"       => $crawler->filter('div.chart-number-one__lyrics > a')->attr('href')
        ];


        $crawler->filter('div.chart-list-item')->each(function ($x) use (&$tracks) {
            $link =

            $tracks[] = [
                "rank"       => $x->attr('data-rank'),
                "song_title" => $x->attr('data-title'),
                "artist"     => $x->attr('data-artist'),
                "link"       => ($x->filter('div.chart-list-item__lyrics > a')->count() > 0) ? $x->filter('div.chart-list-item__lyrics > a')->attr('href') : false
            ];
        });

        // create a json object to send to the template
        $responseContent = ["tracks" => $tracks];

        // create the response
        $this->response->setCache("day");
        $this->response->setLayout('billboard.ejs');
        $this->response->setTemplate("basic.ejs", $responseContent);
    }


    /**
     * Subservice to return the leter of a song in the billboard list
     */
    public function _letra()
    {

        $query = $this->request->input->data->query;
        if (strpos($query, 'www.billboard.com')) {
            $crawler = $this->getCrawler($query);
            if ($crawler->filter('div.lyrics')->count() < 1) {
                $this->simpleMessage("No se pudo procesar la letra de la canci&oacute;n",
                    "Lo sentimos, la letra que usted busca no esta en un formato que podamos procesar");

                return;
            }

            $song = $crawler->filter('div.lyrics')->attr('data-lyric-title');
            $artist = $crawler->filter('div.lyrics')->attr('data-lyric-artist');
            $lyrics = $crawler->filter('div.lyrics')->html();

            $this->response->setCache("year");
            $this->response->setLayout('billboard.ejs');
            $this->response->setTemplate('letra.ejs', [
                'song' => $song,
                'artist' => $artist,
                'lyrics' => $lyrics
            ]);

            return;
        }

        $this->simpleMessage("Letra no encontrada", "Lo sentimos, la letra que usted busca no se encuentra");
    }

    /**
     * Crawler client
     *
     * @return \Goutte\Client
     */
    public function getClient()
    {
        if (is_null($this->client)) {
            $this->client = new Client();
            $guzzle = new GuzzleClient(["verify" => false]);
            $this->client->setClient($guzzle);
        }

        return $this->client;
    }

    /**
     * Get crawler for URL
     *
     * @param string $url
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    protected function getCrawler($url = "")
    {
        $url = trim($url);
        if ($url != '' && $url[0] == '/') {
            $url = substr($url, 1);
        }

        $crawler = $this->getClient()->request("GET", $url);

        return $crawler;
    }
}
