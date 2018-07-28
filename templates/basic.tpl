<h1>100 canciones m&aacute;s populares</h1>
<small><p>Las 100 canciones m&aacute;s escuchadas en EU. <font color="red">Como muchas son recientes, puede que a&uacute;n no tengan letra</font>.</p></small>
<ol>
{foreach $tracks as $track}
	<li>
		<strong>{$track['song_title']}</strong><br/>
		<small>by {$track["artist"]}</small>
		{if $track['link']}
			<br/>
			{link href="BILLBOARD LETRA {$track['link']}" caption="<small>Letra</small>"}
		{/if}
		{space10}
	</li>
{/foreach}
</ol>
