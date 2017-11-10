<?php
for($i = 0; $i<10; $i++) {
    echo 'This is a test<br>';
}

function test1($i) {
    test2($i+5);
}

function test2($i) {
    test3($i+7);
}

function test3($i) {
    $i =  1 / 0; // Division by Zero
}

test1(42);
