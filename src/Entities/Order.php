<?php

namespace MGGFLOW\FlowShop\Entities;

class Order
{
    const CODE_MIN = 0;
    const CODE_MAX = 2 ** 32 - 1;
    const CODE_ALPHABET = [
        'a', 'b', 'c', 'd',
        'e', 'f', 'g', 'h',
        'i', 'j', 'k', 'l',
        'm', 'n', 'o', 'p',
        'q', 'r', 's', 't',
        'u', 'v', 'w',
        'x', 'y', 'z',
        '0', '1', '2', '3',
        '4', '5', '6', '7',
        '8', '9'
    ];
    const CODE_LENGTH = 8;
}