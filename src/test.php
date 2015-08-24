<?php

include 'NotifyCurl.php';

$ncurl = new NotifyCurl();

$d = $ncurl->send('http://trc.adsage.com/trc/xxxxx/x.gif?lpg=http://www.baidu.com/', 5, 1, TRUE);
var_dump($d);

$d = $ncurl->send('http://baidu.com', 5, 1, TRUE);
var_dump($d);

$d = $ncurl->send('http://trc.adsage.com/?', 5, 1, TRUE);
var_dump($d);

//echo $d;
echo 'pass!';

apc_store('xxx', 'bbbb');
echo apc_fetch('xxx');

