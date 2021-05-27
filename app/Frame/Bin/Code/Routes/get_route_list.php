<?php
/**
NOTES

Remove all route without {pc?} first then run this code.
 *
 * */
$data = file_get_contents('web.php');
$listRoute = getStringBetweenCharacters($data, 'Route::match([\'get\', \'post\'], \'/', "/{pc?}");
$listPath = getStringBetweenCharacters($data, 'doControl($pc, \'', "');");

$lengthRoute = count($listRoute);
$result = '$routes = [<br/>';
for($i = 0; $i < $lengthRoute; $i++) {
    $result .= "['route' => '".$listRoute[$i]."', 'path' => '".$listPath[$i]."'],<br/>";
}
$result .= '];';
echo $result;
exit;
function getStringBetweenCharacters($string, $prefix, $postfix) {
    $results = [];
    $indexPrefix = getAllIndexOfCharacter($string, $prefix, 0, true);
    if(empty($indexPrefix) === false) {
        $indexPostfix = getAllIndexOfCharacter($string, $postfix, $indexPrefix[0], false);
        $lengthPrefix = count($indexPrefix);
        $lengthPostfix = count($indexPostfix);
        for ($i = 0; $i < $lengthPrefix; $i++) {
            if($i < $lengthPostfix) {
                $startIndex = $indexPrefix[$i];
                $endIndex = $indexPostfix[$i];
                $lengthCharacter = $endIndex - $startIndex;
                $results[] = substr($string, $startIndex, $lengthCharacter);
            }
        }
    }
    return $results;
}

function getAllIndexOfCharacter($string, $needle, $index = 0, $afterNeedle = false) {
    $results = [];
    $lengthNeedle = strlen($needle);
    while (($index = strpos($string, $needle, $index))!== false) {
        if($afterNeedle === true) {
            $index += $lengthNeedle;
            $results[] = $index;
        } else {
            $results[] = $index;
            $index += $lengthNeedle;
        }
    }
    return $results;
}