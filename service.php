<?php

use Goutte\Client;

class StarWars extends Service
{
	/**
	 * Function executed when the service is called
	 * 
	 * @param Request
	 * @return Response
	 * */
	public function _main(Request $request)
	{
		$starWarsSections = $this->starWarsContentSections();

		$template_variables = array("sections" => $starWarsSections);

		// create the response
		$response = new Response();
		$response->setResponseSubject("StarWars.com: Página principal");
		$response->createFromTemplate("basic.tpl", $template_variables);
		return $response;
	}


	/**
	 * Crawls http://latino.starwars.com and returns article sections.
	 * 
	 * @return array[]
	 * */
	protected function starWarsContentSections() {
		// create a new client
		$client = new Client();
		$guzzle = $client->getClient();
		$guzzle->setDefaultOption("verify", false);
		$client->setClient($guzzle);

		// create a crawler
		$crawler = $client->request("GET", "http://latino.starwars.com");

		// search for result
		$sections = array();
		$current_section = null;

		$crawler
			->filter(".module.bound")
			->each(function ($section) use (&$sections, &$current_section) {
				$title = $section->filter(".module_title");

				if ($title->count()) {
					if ($current_section !== null) {
						$sections[] = $current_section;
					}

					$current_section = array(
						"title" => $title->text(),
						"articles" => array()
					);
				}

				$section
					->filter(".building-block")
					->each(function ($article) use (&$current_section) {
						$desc_html = $article->filter(".desc-sizer .desc");
						$title_html = $article->filter(".title a");

						$description = "";

						if ($desc_html->count()) {
							$description = $desc_html->text();
						}

						$current_section["articles"][] = array(
							"title" => $title_html->text(),
							"description" => $description,
							"category" => $article->filter(".category-info .category-name")->text(),
							"url" => $this->getArticleUrl($title_html->attr("href"))
						);
					});
			});

		$sections[] = $current_section;

		return $sections;
  }


	/**
	 * Returns Apretaste subject for article URL (either WEB service or internal).
	 * 
	 * @param string
	 * @return string
	 * */
	protected function getArticleUrl ($url) {
		$prefix = "http://latino.starwars.com";

		if (strpos($url, $prefix) !== false) {
			$url = "STARWARS " . substr($url, strlen($prefix));
		} else {
			$url = "WEB " . $url;
		}

		return urlencode($url);
	}
}
