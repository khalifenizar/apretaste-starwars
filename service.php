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
		$article_url = $request->query;
		$response = new Response();
		$template_variables = array();

		if (empty($article_url)) {
			$starWarsSections = $this->starWarsContentSections();
	
			$template_variables["sections"] = $starWarsSections;

			$response->setResponseSubject("latino.StarWars.com: Página principal");
			$response->createFromTemplate("home.tpl", $template_variables);
		} else {
			$article = $this->starWarsArticleContent($article_url);

			$template_variables["article"] = $article;

			$response->setResponseSubject("latino.StarWars.com: " . $article["title"]);
			$response->createFromTemplate("article.tpl", $template_variables);
		}

		return $response;
	}


	protected static $base_url = "http://latino.starwars.com";

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
		$crawler = $client->request("GET", self::$base_url);

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
		$prefix = self::$base_url;

		if (strpos($url, $prefix) !== false) {
			$url = "STARWARS " . substr($url, strlen($prefix));
		} else {
			$url = "WEB " . $url;
		}

		return urlencode($url);
	}


	protected function starWarsArticleContent ($url) {
		// create a new client
		$client = new Client();
		$guzzle = $client->getClient();
		$guzzle->setDefaultOption("verify", false);
		$client->setClient($guzzle);

		// create a crawler
		$crawler = $client->request("GET", self::$base_url . $url);

		$category_and_date = explode(" // ", $crawler->filter(".article-date")->text());
		$content = array();

		$crawler->filter(".entry-content p")->each(function ($p) use (&$content) {
			$content[] = $p->text();
		});

		return array(
			"title" => $crawler->filter(".entry-title")->text(),
			"content" => $content,
			"category" => $category_and_date[0],
			"date" => $category_and_date[1]
		);
	}
}
