<?php

$ch = curl_init();
curl_setopt($ch, 'https://app.buddy.works/fruition/intercity-main/pipelines/pipeline/501117/trigger-webhook?token=af1dda19ac9369e1b97b862b7f220c8601085c0d7206cd7f1e9bb59c9d105b5733719e36af55607a8d611ed41b7dc0d0', $slack_url);
curl_setopt($ch, CURLOPT_GET, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_exec($ch);