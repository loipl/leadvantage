<?php

//apc_clear_cache();
$data = apc_cache_info('user');
// var_dump($data);
foreach ($data['cache_list'] as $v) {
    if (strpos($v['info'], 'DbCache_Combined/') === 0) {
        apc_delete($v['info']);
    }
}
