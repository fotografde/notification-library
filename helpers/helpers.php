<?php

/**
 * helpers for the testing and debugging
 */

function d()
{
    array_map(
        function($x)
        {
            var_dump($x);
        },
        func_get_args()
    );
}

function dd()
{
    array_map(
        function($x)
        {
            var_dump($x);
        },
        func_get_args()
    );

    die;
}