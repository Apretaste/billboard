<?php

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\DomCrawler\Crawler;

class Service
{
	private $client;

	/**
	 * Function executed when the service is called
	 */
	public function _main(Request $request, Response $response)
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
			$crawler->filter('.chart-element__information')->each(function ($x) use (&$tracks) {
				$tracks[] = [
					"title" => $x->filter('.chart-element__information__song')->text(),
					"artist" => $x->filter('.chart-element__information__artist')->text()
				];
			});

			// create a json object to send to the template
			$content = ["tracks" => $tracks];

			// save cache file
			file_put_contents($cache, serialize($content));
		}

		// create the response
		$response->setLayout('billboard.ejs');
		$response->setCache("month");
		$response->setTemplate("basic.ejs", $content);
	}

	/**
	 * Get crawler for URL
	 *
	 * @param string $url
	 * @return \Symfony\Component\DomCrawler\Crawler
	 */
	private function getCrawler($url = "")
	{
		$url = trim($url);
		if ($url != '' && $url[0] == '/') {
			$url = substr($url, 1);
		}

		if (is_null($this->client)) {
			$this->client = new Client();
			$guzzle = new GuzzleClient(["verify" => false]);
			$this->client->setClient($guzzle);
		}

		$crawler = $this->client->request("GET", $url);
		return $crawler;
	}
}
