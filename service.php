<?php

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;

class Billboard extends Service
{
    public $client;

    /**
     * Clear a text
     *
     * @param string $txt Source
     * @param string $chars List of chars to delete/keep
     * @param bool $direction True for keep, false for delete
     * @return string
     */
    function cleanTxt($txt, $chars, $direction = true)
    {
        $new_txt = '';
        $l = strlen($txt);
        for($i = 0; $i < $l; $i++)
            if ((strpos($chars, $txt[$i])!== false) == $direction)
                $new_txt .= $txt[$i];

        return $new_txt;
    }

	/**
	 * Function executed when the service is called
	 *
	 * @param Request
	 * @return Response
	 * */
	public function _main(Request $request)
	{
		// create a crawler
        $url = "http://www.billboard.com/charts/hot-100";

        $crawler = $this->getCrawler($url);

		// get tracks into array
		$tracks = [];
		$crawler->filter('article.chart-row')->each(function($x) use(&$tracks){

		    $rank = $x->filter('.chart-row__current-week');
		    $title = $x->filter('.chart-row__song');
		    $artist = $x->filter('.chart-row__artist');
		    $rank_last_week = $x->filter('.chart-row__last-week');

		    if ($title->count() > 0)
            {
                if ($artist->count() > 0) $artist =  $this->cleanTxt($artist->text(). "", "\n\r", false); else  $artist = "";
                if ($rank_last_week->count() > 0) {
                    $rank_last_week = $this->cleanTxt( $rank_last_week->text(). "", "1234567890");
                }  else  $rank_last_week = "";
                if ($rank->count() > 0) $rank = $this->cleanTxt($rank->text(). "", "1234567890"); else  $rank = "";

                $title = explode(": ",$title->text());
                $title = $title [0];
                $tracks[] = [
                    "rank" => $rank,
                    "song_title" => $title,
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
