<?php
if(PHP_SAPI!=='cli'){
    http_response_code(404);
    exit;
}
require __DIR__.'/../app/bootstrap.php';
query('DELETE FROM events WHERE created_at < ?',[gmdate('Y-m-d H:i:s',time()-90*86400)]);
query('DELETE FROM rate_limits WHERE expires_at < ?',[time()]);
echo "Expired rate limits and analytics older than 90 days removed.\n";
