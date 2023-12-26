<?php

namespace App\Contracts;

class ResumeWorkExperienceDTO
{
    public string $company;
    public string $position;
    public string $startDate;
    public ?string $endDate = null;
    public string $summary = '';
    public array $highlights;

    public function toArray(): array
    {
        return [
            'company' => $this->company,
            'position' => $this->position,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
//            'summary' => $this->summary,
//            'highlights' => $this->highlights,
        ];
    }
}
