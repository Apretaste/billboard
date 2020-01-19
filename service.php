<?php

use Apretaste\Request;
use Apretaste\Response;
use Apretaste\Challenges;
use Framework\Crawler;

class Service
{
	private $client;

	/**
	 * Function executed when the service is called
	 *
	 * @param \Apretaste\Request $request
	 * @param \Apretaste\Response $response
	 *
	 * @throws \Framework\Alert
	 */
	public function _main(Request $request, Response &$response)
	{
		// load from cache if exists
		$cache = TEMP_PATH.date('Ym').'_billboard.tmp';
		if (file_exists($cache)) {
			$content = unserialize(file_get_contents($cache));
		} // get data from the internet
		else {
			// create a crawler
			Crawler::start('http://www.billboard.com/charts/hot-100');

			// get tracks into an array
			$tracks = [];
			Crawler::filter('.chart-element__information')->each(function ($x) use (&$tracks) {
				$tracks[] = [
						'title' => $x->filter('.chart-element__information__song')->text(),
						'artist' => $x->filter('.chart-element__information__artist')->text()
				];
			});

			// create a json object to send to the template
			$content = ['tracks' => $tracks];

			// save cache file
			file_put_contents($cache, serialize($content));
		}

		// create the response
		$response->setLayout('billboard.ejs');
		$response->setCache('month');
		$response->setTemplate('basic.ejs', $content);

		Challenges::complete('view-billboard', $request->person->id);
	}
}
