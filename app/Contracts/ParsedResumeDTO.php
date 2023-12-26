<?php

namespace App\Contracts;

class ParsedResumeDTO
{
    public string $name;
    public string $email;
    public string $phone;
    public string $address;
    public string $summary;
    public array $skills;

    public array $worksExperience;
    public array $education;
    public array $certifications;
    public array $projects;
    public array $languages;
    public array $interests;
    public array $references;

    // to array
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
//            'address' => $this->address,
//            'summary' => $this->summary,
//            'skills' => $this->skills,
            'worksExperience' => [],
//            'education' => $this->education,
//            'certifications' => $this->certifications,
//            'projects' => $this->projects,
//            'languages' => $this->languages,
//            'interests' => $this->interests,
//            'references' => $this->references,
        ];

        /** @var ResumeWorkExperienceDTO $workExperience */
        foreach ($this->worksExperience as $workExperience) {
            $data['worksExperience'][] = $workExperience->toArray();
        }

        return $data;
    }
}
