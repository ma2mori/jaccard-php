<?
ini_set("date.timezone", "Asia/Tokyo");

$redis = new redis();
$redis->connect('127.0.0.1', 6379);

$item_id = 'a1';
$user_id = 'u3';

$redis->lRem('Viewer:Item:' . $item_id, $user_id);
$redis->lPush('Viewer:Item:' . $item_id, $user_id);
$redis->lTrim('Viewer:Item:' . $item_id, 0, 999);


$item_ids = ['a1','a2','a3','a4','b1','b2','b3'];

foreach ($item_ids as $item_id1) {
	$base = $redis->lRange('Viewer:Item:' . $item_id1, 0, 999);
	if (!count($base)) {
		continue;
	}
	foreach ($item_ids as $item_id2) {
		if ($item_id1 === $item_id2) {
			continue;
		}
		$target = $redis->lRange('Viewer:Item:' . $item_id2, 0, 999);
		if (!count($target)) {
			continue;
		}

		$join = floatval(count(array_unique(array_merge($base, $target))));
		$intersect = floatval(count(array_intersect($base, $target)));
		if (!$intersect || !$join) {
			continue;
		}
		$jaccard = $intersect / $join;

		$redis->zAdd('Jaccard:Item:' . $item_id1, $jaccard, $item_id2);
	}
}

echo '<pre>';
print_r($redis->zRevRange('Jaccard:Item:' . $item_id, 0, -1));
echo '</pre>';
