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

		if (empty($article_url)) {
			$starWarsSections = $this->starWarsContentSections();
	
			$subject = "latino.StarWars.com: Página principal";
			$template_name = "home.tpl";
			$template_variables = array("sections" => $starWarsSections);
		}
		elseif (strpos($article_url, "/banco-de-datos/") === false) {
			$article = $this->starWarsArticleContent($article_url);

			$subject = "latino.StarWars.com: " . $article["title"];
			$template_name = "article.tpl";
			$template_variables = array("article" => $article);
		}
		else {
			$entry = $this->starWarsDatabaseContent($article_url);

			$subject = "latino.StarWars.com: " . $entry["name"];
			$template_name = "database_entry.tpl";
			$template_variables = array("entry" => $entry);
		}

		$response = new Response();
		$response->setResponseSubject($subject);
		$response->createFromTemplate($template_name, $template_variables);
		return $response;
	}


	protected static $base_url = "http://latino.starwars.com";

	/**
	 * Crawls http://latino.starwars.com and returns article sections.
	 * 
	 * @return array[]
	 * */
	protected function starWarsContentSections() {
		$crawler = $this->getCrawler();

		// search for result
		$sections = array();
		$current_section = null;

		$crawler
			->filter(".module.bound")
			->each(function ($section) use (&$sections, &$current_section) {
				$title = $section->filter(".module_title");

				if ($title->count()) {
					if ($current_section !== null && $current_section["title"] != "EVENTOS //") {
						$sections[] = $current_section;
					}

					$current_section = array(
						"title" => $title->text(),
						"articles" => array()
					);
				}

				if ($current_section["title"] == "EVENTOS //") {
					return;
				}

				$section
					->filter(".building-block")
					->reduce(function ($article) {
						$category = $article->filter(".category-info .category-name")->text();
						return $category != "Video";
					})
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

		if ($current_section["title"] != "EVENTOS //") {
			$sections[] = $current_section;
		}

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
		$crawler = $this->getCrawler($url);

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


	protected function starWarsDatabaseContent ($url) {
		$crawler = $this->getCrawler($url);

		$featured = $crawler->filter(".featured_single");

		$stats = array();

		$crawler->filter(".stats-container .category")->each(function ($cat) use (&$stats, $labels) {
			$category_values = array();

			$cat->filter("li")->each(function ($item) use (&$category_values) {
				$category_values[] = trim($item->text(), " ,\n\r");
			});

			$heading = $cat->filter(".heading")->text();

			$stats[$heading] = $category_values;
		});

		return array(
			"name" => $featured->filter(".title")->text(),
			"description" => $featured->filter(".desc")->text(),
			"stats" => $stats
		);
	}


	protected function getCrawler ($url = "") {
		$client = new Client();
		$guzzle = $client->getClient();
		$guzzle->setDefaultOption("verify", false);
		$client->setClient($guzzle);

		// create a crawler
		$crawler = $client->request("GET", self::$base_url . $url);

		return $crawler;
	}
}
