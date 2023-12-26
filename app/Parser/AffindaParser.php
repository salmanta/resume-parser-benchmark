<?php

namespace App\Parser;

use CURLFile;
use Illuminate\Support\Facades\Log;

class AffindaParser implements \App\Contracts\ResumeParserContract
{

    private $BASE_URL = 'https://resume-parser.affinda.com';
    private $api_key = '317630611ea53068647fb105e852bd5faf171ace'; // TODO: use ExternalCredentials
    private $session; // This will be a cURL handle
    private $dropzone_id;
    private $credential_identifier_value;

    public function __construct()
    {
        $this->session = curl_init(); // Initialize the cURL session
        $this->dropzone_id = time() . '-0'; // PHP time() returns current time in seconds
        $this->credential_identifier_value = $this->__create_identifier()['identifier'];
    }

    public function getName(): string
    {
        return 'affinda';
    }

    public function parse(string $filePath): array
    {
        $data = $this->parse_resume_by_url($filePath);
        $data = $data['resumes'][0]['data'];
        $parsedResume = new \App\Contracts\ParsedResumeDTO();

        $parsedResume->name = $data['name']['raw'];
        $parsedResume->email = $data['emails'][0];
        $parsedResume->phone = $data['phoneNumbers'][0];

        $parsedResume->worksExperience = [];
        foreach ($data['workExperience'] as $workExperience) {
            $parsedWorkExperience = new \App\Contracts\ResumeWorkExperienceDTO();
            $parsedWorkExperience->company = $workExperience['organization'];
            $parsedWorkExperience->position = $workExperience['jobTitle'];
            $parsedWorkExperience->startDate = $workExperience['dates']['startDate'];
            $parsedWorkExperience->endDate = $workExperience['dates']['endDate'] ?? null;
            $parsedWorkExperience->summary = $workExperience['jobDescription'];
            $parsedResume->worksExperience[] = $parsedWorkExperience;
        }

        return $parsedResume->toArray();
    }


    public function parse_resume_by_url($resume_s3_url)
    {
        $this->__send_resume($resume_s3_url);
        $this->__request_parse_resume();

        sleep(2); // Wait for 2 seconds
        for ($i = 0; $i < 6; $i++) {
            usleep(870000); // Wait for 0.87 seconds
            $result = $this->__get_resume();
            if ($result['ready']) {
                return $result;
            }
        }
        return null;
    }

    private function __request_parse_resume()
    {
        $body = ['dropzoneIds' => [$this->dropzone_id]];
        $headers = ['Content-Type: application/json'];

        $response = $this->makePostRequest("{$this->BASE_URL}/public/run_parse_request/{$this->credential_identifier_value}", $body, $headers);
        // Handle response...
    }

    private function __send_resume($resume_s3_url)
    {
        $fileContent = file_get_contents($resume_s3_url);
        $filename = basename(parse_url($resume_s3_url, PHP_URL_PATH));
        $tmpFilePath = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($tmpFilePath, $fileContent);

        $data = [
            'dropzoneId' => $this->dropzone_id,
            'file' => new CURLFile($tmpFilePath, 'application/octet-stream', $filename)
        ];

        $response = $this->makePutRequest("{$this->BASE_URL}/public/api/v1/parse_requests/{$this->credential_identifier_value}", $data);

        unlink($tmpFilePath);

    }

    private function __create_identifier()
    {
        $response = $this->makePostRequest("{$this->BASE_URL}/public/api/v1/parse_requests");
        return json_decode($response, true);
    }

    private function __get_resume()
    {
        $response = $this->makeGetRequest("{$this->BASE_URL}/public/api/v1/parse_requests/{$this->credential_identifier_value}?format=json");
        return json_decode($response, true);
    }

    private function makePostRequest($url, $body = [], $headers = [])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    private function makePutRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    private function makeGetRequest($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
