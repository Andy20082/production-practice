<?php
$d = json_decode($json, true);
$r = [];
foreach($d['call'] as $n=>$p) {
    if($p['tradeble']=='true') {
        $f = 'images/'.$p['image_name'].'.jpeg';
        file_put_contents($f, base64_decode(explode('base64,',$p['image']['base64'])[1]));
        $r[] = ['image_name'=>$p['image_name'],'link'=>$p['image']['link'],'file_path'=>$f,'name'=>$p['name']??$n];
    }
}
print_r($r);
?>