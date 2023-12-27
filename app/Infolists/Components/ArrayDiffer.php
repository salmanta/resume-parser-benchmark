<?php

namespace App\Infolists\Components;

use Filament\Infolists\Components\Entry;

class ArrayDiffer extends Entry
{
    protected string $view = 'infolists.components.array-differ';

    public function generateDiff(){
        $openAIParse = $this->getRecord()->parserResults()->where('name', 'openai')->first()->data ?? [];
        $affindaParse = $this->getRecord()->parserResults()->where('name', 'affinda')->first()->data ?? [];
        $daxtraParse = $this->getRecord()->parserResults()->where('name', 'daxtra')->first()->data??[];

        $return = 'OpenAI vs Affinda';
        $return .= '<br>';
        $return .= '<pre>';
        $return .= print_r($this->compare_arrays($openAIParse, $affindaParse), true);
        $return .= '</pre>';
        $return .= '<br>';
        $return .= 'OpenAI vs Daxtra';
        $return .= '<br>';
        $return .= '<pre>';
        $return .= print_r($this->compare_arrays($openAIParse, $daxtraParse), true);
        $return .= '</pre>';
        $return .= '<br>';
        $return .= 'Affinda vs Daxtra';
        $return .= '<br>';
        $return .= '<pre>';
        $return .= print_r($this->compare_arrays($affindaParse, $daxtraParse), true);
        $return .= '</pre>';
        $return .= '<br>';

        return $return;
    }

    private function compare_arrays($array1, $array2, $path = '') {
        $differences = [];

        // Convert keys of both arrays to lower case
        $lowercaseKeysArray1 = array_change_key_case($array1, CASE_LOWER);
        $lowercaseKeysArray2 = array_change_key_case($array2, CASE_LOWER);

        foreach ($lowercaseKeysArray1 as $key => $value) {
            $currentPath = $path ? $path . '.' . $key : $key;

            // Check if the key exists in the second array
            if (!array_key_exists($key, $lowercaseKeysArray2)) {
                $differences['missing_in_second'][$currentPath] = $value;
            } else {
                if (is_array($value)) {
                    // Recursive call for nested arrays
                    if (!is_array($lowercaseKeysArray2[$key])) {
                        $differences['mismatch'][$currentPath] = ['expected' => $value, 'actual' => $lowercaseKeysArray2[$key]];
                    } else {
                        $subDifferences = compare_arrays($value, $lowercaseKeysArray2[$key], $currentPath);
                        $differences = array_merge_recursive($differences, $subDifferences);
                    }
                } elseif (is_string($value) && strtolower($value) !== strtolower($lowercaseKeysArray2[$key])) {
                    // Case-insensitive comparison for string values
                    $differences['mismatch'][$currentPath] = ['expected' => $value, 'actual' => $lowercaseKeysArray2[$key]];
                } elseif ($value !== $lowercaseKeysArray2[$key]) {
                    // Comparison for non-string values
                    $differences['mismatch'][$currentPath] = ['expected' => $value, 'actual' => $lowercaseKeysArray2[$key]];
                }
            }
        }

        // Check for keys in the second array that are not in the first
        foreach ($lowercaseKeysArray2 as $key => $value) {
            $currentPath = $path ? $path . '.' . $key : $key;

            if (!array_key_exists($key, $lowercaseKeysArray1)) {
                $differences['missing_in_first'][$currentPath] = $value;
            }
        }

        return $differences;
    }
}
