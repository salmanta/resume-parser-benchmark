<?php

namespace App\Parser;

use App\Contracts\ResumeParserContract;

class DaxtraParser implements ResumeParserContract
{

    private $BASE_URL = 'https://cvxdemo.daxtra.com';
    private $ACCOUNT = 'Attesto';

    public function getName(): string
    {
        return 'daxtra';
    }

    public function parse(string $filePath): array
    {
        $data = $this->parse_resume_by_url($filePath);

        $data = $data['Resume'];
        $parsedResume = new \App\Contracts\ParsedResumeDTO();

        $parsedResume->name = $data['StructuredResume']['PersonName']['FormattedName'] ?? '';
        $parsedResume->email = $data['StructuredResume']['ContactMethod']['InternetEmailAddress_main'] ?? '';
        $parsedResume->phone = $data['StructuredResume']['ContactMethod']['Telephone_mobile'] ?? '';

        $parsedResume->worksExperience = [];
        foreach ($data['StructuredResume']['EmploymentHistory']??[] as $workExperience) {

            $parsedWorkExperience = new \App\Contracts\ResumeWorkExperienceDTO();
            $parsedWorkExperience->company = $workExperience['EmployerOrgName'] ?? ($workExperience['OrgName'] ?? '');
            $parsedWorkExperience->position = $workExperience['Title'][0] ?? '';
            $parsedWorkExperience->startDate = $workExperience['StartDate'] ?? '';
            $parsedWorkExperience->endDate = $workExperience['EndDate'] ?? '';
            $parsedWorkExperience->summary = $workExperience['Description'] ?? '';
            $parsedResume->worksExperience[] = $parsedWorkExperience;
        }

        return $parsedResume->toArray();
    }

    public function parse_resume_by_url($resume_s3_url)
    {
        $fileContent = file_get_contents($resume_s3_url);
        $encodedFile = base64_encode($fileContent);

        $data = [
            'account' => $this->ACCOUNT,
            'file' => $encodedFile,
        ];

        echo 'sending resume';
        $response = $this->makePostRequest("{$this->BASE_URL}/cvx/rest/api/v1/profile/full/json", $data);
        return json_decode($response, true);
    }

    private function makePostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Encode data as a query string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
