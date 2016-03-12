<h1>latino.StarWars.com</h1>
<p>Noticias del mundo de Star Wars.</p>

{space15}


<ul>
	{foreach from=$sections item=s}
		<li>
			<h2>{$s["title"]}</h3>

			<ul>
				{foreach from=$s["articles"] item=a}
					<li>
						<h3>{link href=$a["url"] caption=$a["title"]}</h3>
						<p>{$a["description"]}</p>
						<small>Categoría: {$a["category"]}</small>
					</li>
				{/foreach}
			</ul>
		</li>
		{space15}
	{/foreach}
</ul>