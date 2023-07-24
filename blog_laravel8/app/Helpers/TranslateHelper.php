<?php

use Stichoza\GoogleTranslate\GoogleTranslate;

function translate($language, $data)
{
    $tr = new GoogleTranslate();
    $translated = $tr->setSource('en')->setTarget($language)->translate($data);

    return $translated;
}
