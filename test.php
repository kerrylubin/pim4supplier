<?php

$string = '/b/c/bc31a1cd3e445eb8c41adac1f5c72061f50d3d4b_MOUTHGARD_PREDATOR_BLACK_BLACK_SD_02.jpg';
echo ltrim($string, '/');
echo substr_replace($string, "https://wholesale.venum.com/media/catalog/product/", 0, 1);
echo '<br/>';
// abcd-efg-sms-3-topper-2
echo rtrim($string, '/');
echo '<br/>';
// /abcd-efg-sms-3-topper-2
echo trim($string, '/');
// abcd-efg-sms-3-topper-2
