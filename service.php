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
        $url = "http://www.billboard.com/charts/hot-100";

        $crawler = $this->getCrawler($url);

		// get tracks into array
        $tracks = [];

        $tracks[] = [
            "rank" => "1",
            "song_title" => $crawler->filter('div.chart-number-one__title')->text(),
            "artist" => $crawler->filter('div.chart-number-one__artist')->text(),
            "link" =>  $crawler->filter('div.chart-number-one__lyrics > a')->attr('href')];

        

		$crawler->filter('div.chart-list-item')->each(function($x) use(&$tracks){
            $link=

            $tracks[] = [
                "rank" => $x->attr('data-rank'),
                "song_title" => $x->attr('data-title'),
                "artist" => $x->attr('data-artist'),
                "link" => ($x->filter('div.chart-list-item__lyrics > a')->count()>0)?$x->filter('div.chart-list-item__lyrics > a')->attr('href'):false
            ];
        });

		// create a json object to send to the template
		$responseContent = array("tracks" => $tracks);

		// create the response
		$response = new Response();
		$response->setCache("day");
		$response->setResponseSubject("El Billboard");
		$response->createFromTemplate("basic.tpl", $responseContent);
		return $response;
	}


    /**
     * Subservice to return the leter of a song in the billboard list
     * @param Request
     * @return Response
     */

     public function _letra(Request $request){
         
         if (strpos($request->query,'www.billboard.com')){
            $crawler = $this->getCrawler($request->query);
            $song = $crawler->filter('div.lyrics')->attr('data-lyric-title');
            $artist = $crawler->filter('div.lyrics')->attr('data-lyric-artist');
            $lyrics = $crawler->filter('div.lyrics')->html();
            
            $response=new Response();
            $response->subject="Letra de $song - $artist";
            $response->setCache("year");
            $response->createFromTemplate('letra.tpl',array('song'=>$song,'artist'=>$artist,'lyrics'=>$lyrics));
            return $response;
         }
         else{
            $reponse=new Response();
            $response->createFromText("Lo sentimos, la letra que usted busca no se encuentra");
            return $response;
         }
        
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
