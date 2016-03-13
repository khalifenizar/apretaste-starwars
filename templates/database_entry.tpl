<h1>latino.StarWars.com</h1>
<h2>{$entry["name"]}</h2>
<p>{$entry["description"]}</p>

{space15}

<dl>
	{foreach from=$entry["stats"] key=label item=data}
		<dt>
			<h3>{$label}</h3>
		</dt>
		<dd>
			<ul>
				{foreach from=$data item=datum}
					<li>{$datum}</li>
				{/foreach}
			</ul>
		</dd>
	{/foreach}
</dl>