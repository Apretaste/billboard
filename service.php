<?php

use Goutte\Client;

class Billboard extends Service
{
	/**
	 * Function executed when the service is called
	 *
	 * @param Request
	 * @return Response
	 * */
	public function _main(Request $request)
	{
		// create a new client
		$client = new Client();
		$guzzle = $client->getClient();
		$guzzle->setDefaultOption('verify', false);
		$client->setClient($guzzle);

		// create a crawler
		$crawler = $client->request('GET', "http://www.billboard.com/rss/charts/hot-100");

		// search for result
		$site_title = $crawler->filter('title')->text();

		// get tracks into array
		$tracks = array();
		$crawler->filter('item')->each(function($x) use(&$tracks){
			$tracks[] = array(
				"song_title" => explode(": ",$x->filter('title')->text())[1],
				"artist" => $x->filter('artist')->text(),
				"rank_last_week" => $x->filter('rank_last_week')->text()			);
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
}
