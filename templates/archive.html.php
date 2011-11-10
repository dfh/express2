<?php
namespace express;

$entries = sort_entries_by_date( $entries );
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Archives - David Högberg</title>
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

	<div id="bd" class="archive">
		<h1 class="title main-title">All entries, by date</h1>
		<ul>
<?php
$month = $year = $prev_month = $prev_year = null;
foreach ( $entries as $e ):
	$month = date( 'F', $e->created_on );
	$year = date( 'Y', $e->created_on );
	if ( $year != $prev_year ) {
		echo '<h2 class="subtitle">' . $year . '</h2>';
		$prev_year = $year;
	}
	if ( $month != $prev_month ) {
		echo '<h3 class="subtitle">' . $month . '</h2>';
		$prev_month = $month;
	}
?>
			<li>
				<a href="<?= $e->url ?>"><?= $e->title ?></a>
				<date class="date"><?= date( 'F d, Y', $e->created_on ) ?></date>
			</li>
<?php
endforeach;
?>
		</ul>
	</div>

	<div id="ft"></div>
</div>
</body>
</html>
