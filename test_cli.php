<?php

function prompt(string $message): string {
    echo $message;
    return trim(fgets(STDIN));
}

prompt('This is a test prompt. Please enter something: ');