<?php

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;

class Billboard extends Service
{
    public $client;
	/**
	 * Function executed when the service is called
	 *
	 * @param Request
	 * @return Response
	 * */
	public function _main(Request $request)
	{
		// create a crawler
		$crawler = $this->getCrawler("http://www.billboard.com/rss/charts/hot-100");

		// search for result
		$site_title = $crawler->filter('title')->text();

		// get tracks into array
		$tracks = [];
		$crawler->filter('item')->each(function($x) use(&$tracks){

		    $title = $x->filter('title');
		    $artist = $x->filter('artist');
		    $rank_last_week = $x->filter('rank_last_week');

		    if ($title->count() > 0)
            {
                if ($artist->count() > 0) $artist = $artist->text(); else  $artist = "";
                if ($rank_last_week->count() > 0) $rank_last_week = $rank_last_week->text(); else  $rank_last_week = "";

                $tracks[] = [
                    "song_title" => explode(": ",$title->text())[1],
                    "artist" => $artist,
                    "rank_last_week" => $rank_last_week
                ];
            }

		});

		// create a json object to send to the template
		$responseContent = array("tracks" => $tracks);

		// create the response
		$response = new Response();
		$response->setCache("month");
		$response->setResponseSubject("El Billboard");
		$response->createFromTemplate("basic.tpl", $responseContent);
		return $response;
	}


    /**
     * Crawler client
     *
     * @return \Goutte\Client
     */
    public function getClient()
    {
        if (is_null($this->client))
        {
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
    protected function getCrawler ($url = "") {
        $url = trim($url);
        if ($url != '' && $url[0] == '/') $url = substr($url, 1);

        $crawler = $this->getClient()->request("GET", $url);

        return $crawler;
    }
}
