<?php
// A small SVG chart uses saved totals, without a chart library.
$series=array_reverse(array_slice(page_records('actions-by-month'),0,6));
if (!$series) {
    echo '<svg class="line-chart" viewBox="0 0 600 190" role="img" aria-label="No approved activities"><text x="300" y="95" text-anchor="middle" fill="currentColor">No approved activities yet</text></svg>';
} else {
    $maximum=max(array_column($series,'actions')) ?: 1;
    echo '<svg class="line-chart" viewBox="0 0 600 190" role="img" aria-label="Approved activities by month"><title>Approved activities by month</title>';
    foreach ($series as $index=>$point) {
        $x=30+$index*(540/count($series));
        $height=120*$point['actions']/$maximum;
        echo '<rect x="' . $x . '" y="' . (145-$height) . '" width="40" height="' . $height . '" fill="currentColor" opacity="0.65"></rect>';
        echo '<text x="' . $x . '" y="170" font-size="12" fill="currentColor">' . e($point['period']) . '</text>';
        echo '<text x="' . $x . '" y="' . (135-$height) . '" font-size="12" fill="currentColor">' . (int)$point['actions'] . '</text>';
    }
    echo '</svg>';
}
