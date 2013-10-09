<?php

require_once 'job.php';

$job = new JobTest1(array(), null);

try
{
    call_user_func_array(array(), param_arr)
}
catch (\Exception $e)
{
    echo 'Fuck';
}