<?php
	namespace express;
	echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<title>Entries</title>
	<link rel="self" href="<?= base_url() . 'atom.xml' ?>" type="application/atom+xml" />
	<updated><?= date( 'c', time() ) ?></updated>
	<id><?= base_url() . 'atom.xml' ?></id>

	<author>
		<name>David Högberg</name>
		<uri>http://david.hgbrg.se</uri>
	</author>

<?php
	foreach( $entries as $entry ):
?>
	<entry>
		<title><?= $entry->title ?></title>
		<link href="<?= base_url() . $entry->url . '.html' ?>" rel="alternate" type="text/html" />
		<id><?= base_url() . $entry->url . '.html' ?></id>
		<updated><?= date( 'c', $entry->updated_on ) ?></updated>
		<content type="html">
			<?= htmlspecialchars( $entry->body ); ?>
		</content>
	</entry>	
<?php
	endforeach;
?>
</feed>
