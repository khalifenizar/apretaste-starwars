<h1>latino.StarWars.com</h1>
<h2>{$article["title"]}</h2>
<small>{$article["date"]}</small>

{space15}

<div>
	{foreach from=$article["content"] item=p}
		<p>{$p}</p>
	{/foreach}
</div>