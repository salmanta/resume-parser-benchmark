<?php

use Illuminate\Support\Facades\Route;
use Swaggest\JsonDiff\JsonDiff;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/admin');
//    $resume = \App\Models\Resume::latest()->first();
//    $resumeFile = \Illuminate\Support\Facades\Storage::disk('public')->path($resume->id . '.pdf');
//
//    $affindaParser = new \App\Parser\OpenAIParser();
//    $affindaData = $affindaParser->parse($resumeFile);
//
//    \App\Models\ParserResult::create([
//        'resume_id' => $resume->id,
//        'name' => $affindaParser->getName(),
//        'data' => $affindaData,
//    ]);

    $originalJson = (\App\Models\ParserResult::find(4))->data;
    $newJson = (\App\Models\ParserResult::find(5))->data;

//    $r = new JsonDiff(
//        $originalJson,
//        $newJson,
//        JsonDiff::REARRANGE_ARRAYS
//    );

//    ddd($originalJson->diff($newJson));


    $differences = compare_arrays($originalJson, $newJson);
    ddd($originalJson, $differences);
});


function compare_arrays($array1, $array2, $path = '') {
    $differences = [];

    foreach ($array1 as $key => $value) {
        $currentPath = $path ? $path . '.' . $key : $key;

        if (!array_key_exists($key, $array2)) {
            $differences['missing_in_second'][$currentPath] = $value;
        } elseif (is_array($value)) {
            if (!is_array($array2[$key])) {
                $differences['mismatch'][$currentPath] = ['expected' => $value, 'actual' => $array2[$key]];
            } else {
                $subDifferences = compare_arrays($value, $array2[$key], $currentPath);
                $differences = array_merge_recursive($differences, $subDifferences);
            }
        } elseif ($value !== $array2[$key]) {
            $differences['mismatch'][$currentPath] = ['expected' => $value, 'actual' => $array2[$key]];
        }
    }

    foreach ($array2 as $key => $value) {
        $currentPath = $path ? $path . '.' . $key : $key;

        if (!array_key_exists($key, $array1)) {
            $differences['missing_in_first'][$currentPath] = $value;
        }
    }

    return $differences;
}



