<h1>100 canciones m&aacute;s populares</h1>
<small><p>Las 100 canciones m&aacute;s pedidas en la radio de EU. Hacer click en el nombre devolver&aacute; la letra, pero <font color="red">como muchas son recientes, puede que a&uacute;n no tengan letra</font>.</p></small>
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


