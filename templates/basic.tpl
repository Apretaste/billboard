<h1>100 canciones m&aacute;s populares</h1>

<ol>
{foreach from=$tracks item=v}
	<li>
		<strong>{link href="LETRA {$v["song_title"]}" caption="{$v["song_title"]}"}</strong><br/>
		<small>by {$v["artist"]}</small><br/>
		<small><font color="gray">Visitas &uacute;ltima semana: {$v["rank_last_week"]}</font></small>
		{space10}
	</li>
{/foreach}
</ol>


