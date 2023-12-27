<?php

namespace App\Filament\Resources\ResumeResource\Pages;

use App\Contracts\ResumeParserContract;
use App\Filament\Resources\ResumeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CreateResume extends CreateRecord
{
    protected static string $resource = ResumeResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['name'] = $data['original_file_name'];
        $fileName = $data['file'];
        $model = parent::handleRecordCreation($data);

        $moveSuccess = Storage::disk('public')->move($fileName, $model->id . '.pdf');
        if (!$moveSuccess) {
            throw new \Exception('Failed to move file');
        }

        $parsers = config('parsers');
        foreach ($parsers as $parser) {
            /** @var ResumeParserContract $parserClass */
            $parserClass = new ($parser['class']);
            $filePath = Storage::disk('public')->path($model->id . '.pdf');
            $data = $parserClass->parse($filePath);

            $model->parserResults()->create([
                'name' => $parserClass->getName(),
                'data' => $data,
            ]);
        }

        return $model;
    }

//    protected function getRedirectUrl(): string
//    {
//        return $this->getResource()::getUrl('view', ['record' => $this->record]);
//    }
}
