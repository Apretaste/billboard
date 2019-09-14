<?php

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\DomCrawler\Crawler;

class BillboardService extends ApretasteService
{

    public $client;

    /**
     * Function executed when the service is called
     */
    public function _main()
    {
        // load from cache if exists
        $cache = Utils::getTempDir().date("Ym")."_billboard.tmp";
        if (file_exists($cache)) {
            $content = unserialize(file_get_contents($cache));
        } // get data from the internet
        else {
            // create a crawler
            $crawler = $this->getCrawler("http://www.billboard.com/charts/hot-100");

            // get tracks into an array
            $tracks = [];
            $crawler->filter('div.chart-list-item')->each(function ($x) use (&$tracks) {
                $tracks[] = [
                    "rank"       => $x->attr('data-rank'),
                    "song_title" => $x->attr('data-title'),
                    "artist"     => $x->attr('data-artist'),
                    "link"       => ($x->filter('div.chart-list-item__lyrics > a')->count() > 0) ? $x->filter('div.chart-list-item__lyrics > a')->attr('href') : false
                ];
            });

            // create a json object to send to the template
            $content = ["tracks" => $tracks];

            // save cache file
            file_put_contents($cache, serialize($content));
        }

        // create the response
        $this->response->setCache("month");
        $this->response->setTemplate("basic.ejs", $content);
    }

    /**
     * Subservice to return the leter of a song in the billboard list
     */
    public function _letra()
    {
        // get the query
        $query = $this->request->input->data->query;
        $number = $this->request->input->data->number;

        // do not allow lyrics with problems
        if (!strpos($query, 'www.billboard.com')) {
            $this->simpleMessage("Letra no encontrada", "Lo sentimos, la letra que usted busca no se encuentra");

            return;
        }

        // load from cache if exists
        $cache = Utils::getTempDir().date("Y")."_billboard_".md5($query)."_".md5_file(__FILE__).".tmp";
        if (file_exists($cache)) {
            $content = unserialize(file_get_contents($cache));
        } // get data from the internet
        else {
            // connect to crawler
            $crawler = $this->getCrawler($query);

            // do not allow bad lyrics
            if ($crawler->filter('div.embedded-content')->count() < 1 && $crawler->filter('div.lyrics')->count() < 1 ) {
                $this->simpleMessage("No se pudo procesar la letra de la canción", "Lo sentimos, la letra que usted busca no esta en un formato que podamos procesar");

                return;
            }

            // get params from the song
            if ($crawler->filter('div.lyrics')->count() > 1) {
                $song = strip_tags($crawler->filter('div.lyrics')->attr('data-lyric-title'));
                $artist = $crawler->filter('div.lyrics')->attr('data-lyric-artist');
                $lyrics = $crawler->filter('div.lyrics')->html();
            } else {
                $artist = $crawler->filter('figcaption > div.media__caption')->text();
                $song = $crawler->filter('h1.article__headline')->text();
                $lyrics = "";
                $crawler->filter('div.article__body p')->each(function(Crawler $element) use (&$lyrics){
                    $lyrics .= strip_tags($element->text())."\n";
                });
            }

            $p = strpos($lyrics, '<a ');
            if ($p !== false) {
                $lyrics = substr($lyrics, $p);
            }

            $lyrics = nl2br(strip_tags($lyrics));

            // create object for the view
            $content = [
                'number' => $number,
                'song'   => $song,
                'artist' => $artist,
                'lyrics' => $lyrics
            ];

            // save cache file
            file_put_contents($cache, serialize($content));
        }

        // send data to the view
        ///$this->response->setCache("year");
        $this->response->setTemplate('letra.ejs', $content);
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
