<?php
namespace express;

$related = get_related_entries( $entry );
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?= $entry->title ?> - David Högberg</title>
<?php
include 'head.html.php';
?>
</head>
<body>
<div id="doc">
	<div id="hd">
<?php
	include 'sidebar.html.php';
?>
	</div>

	<div id="bd">
		<h1 class="title main-title"><a href="<?= $entry->url ?>" title="Permanent link to this entry."><?= $entry->title ?></a></h1>

		<date class="date"><?= date( 'F d, Y', $entry->created_on ) ?></date>

		<section class="body">
<?= $entry->body ?>
		</section>

		<p class="separator">★</p>

<?php
if ( $related ):
?>
	<section class="related">
		<h2 class="subtitle">Possibly related</h2>
		<ul>
<?php
	foreach ( $related as $e ):
?>
		<li>
			<a href="<?= $e->url ?>"><?= $e->title ?> </a>
			<date class="date">– <?= date( 'F d, Y', $e->created_on ) ?></date>
		</li>
<?php
	endforeach;
?>
		</ul>
	</section>
<?php
endif;
?>
		<p class="all">
			Want more? See the <a href="archive.html">complete archives</a>.
		</p>
	</div>

	<div id="ft"></div>
</div>
</body>
</html>
