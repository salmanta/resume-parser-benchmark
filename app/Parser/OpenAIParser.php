<?php

namespace App\Parser;

use App\Contracts\ResumeParserContract;
use CURLFile;

class OpenAIParser implements ResumeParserContract
{
    private $OCR_API_URL = 'https://api.ocr.space/parse/image';
    private $OCR_API_KEY = 'K83468122788957'; // Use appropriate mechanism to secure the API key
    private $OPENAI_API_URL = 'https://api.openai.com/v1/chat/completions';
    private $OPENAI_API_KEY = '';


    public function __construct()
    {
        $this->OPENAI_API_KEY = env('OPENAI_API_KEY');
    }

    public function getName(): string
    {
        return 'openai';
    }

    public function parse(string $filePath): array
    {
        $data = $this->parse_resume_by_url($filePath);
        $data = json_decode($data, true);

        $parsedResume = new \App\Contracts\ParsedResumeDTO();

        $parsedResume->name = $data['full_name'] ?? '';
        $parsedResume->email = $data['email'] ?? '';
        $parsedResume->phone = $data['phone_number'] ?? '';

        $parsedResume->worksExperience = [];
        foreach ($data['candidate_experiences'] as $workExperience) {
            // "effective_date_range": "2021-06-01 to PRESENT"
            $startDate = explode(' to ', $workExperience['effective_date_range'])[0] ?? '';
            $endDate = explode(' to ', $workExperience['effective_date_range'])[1] ?? '';


            $parsedWorkExperience = new \App\Contracts\ResumeWorkExperienceDTO();
            $parsedWorkExperience->company = $workExperience['company_name'] ?? '';
            $parsedWorkExperience->position = $workExperience['title'] ?? '';
            $parsedWorkExperience->startDate = $startDate ?? '';
            $parsedWorkExperience->endDate = $endDate ?? '';
            $parsedWorkExperience->summary = $workExperience['description'] ?? '';
            $parsedResume->worksExperience[] = $parsedWorkExperience ?? '';
        }

        return $parsedResume->toArray();
    }

    public function parse_resume_by_url($resume_s3_url) {
        $fileContent = file_get_contents($resume_s3_url);
        $encodedFile = base64_encode($fileContent);

        // Post request to OCR API
        $ocrData = [
            'apikey' => $this->OCR_API_KEY,
            'filetype' => 'PDF',
            'OCREngine' => '2',
            'file' => new CURLFile($resume_s3_url)
        ];

        $ocrResponse = $this->makePostRequest($this->OCR_API_URL, $ocrData, true);
        $ocrResponseJson = json_decode($ocrResponse, true);

        $parsedText = '';
        foreach ($ocrResponseJson['ParsedResults'] as $parsedResult) {
            $parsedText .= $parsedResult['ParsedText'];
        }

        // Construct LLMPrompt
        $llmPrompt = 'This is a text extracted from a resume. generate a JSON based on the following JSON schema:\n\n';
        $llmPrompt .= '
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "properties": {
    "full_name": { "type": "string" },
    "email": { "type": "string", "format": "email" },
    "location": { "type": "string" },
    "years_of_experience": {
      "type": "string",
      "description": "total number of years of experience, integer"
    },
    "main_profession": { "type": "string" },
    "languages": {
      "type": "array",
      "items": { "type": "string" },
      "description": "List of human spoken languages. Programming languages are not to be included."

    },
    "phone_number": { "type": "string" },
    "publications": {
      "type": "array",
      "items": { "type": "object" }
    },
    "referees": {
      "type": "array",
      "items": { "type": "object" }
    },
    "external_urls_hash": {
      "type": "object",
      "properties": {
        "github_url": { "type": "string" },
        "linkedin_url": { "type": "string" },
        "portfolio_url": { "type": "string" }
      }
    },
    "candidate_experiences": {
      "type": "array",
      "items": {
        "type": "object",
        "properties": {
          "title": { "type": "string" },
          "description": { "type": "string" },
          "region_name": { "type": "string" },
          "company_name": { "type": "string" },
          "experience_type": { "type": "string" },
          "effective_date_range": {
            "type": "string",
            "description": "YYYY-MM-DD to YYYY-MM-DD. If the initial date format is in human readable format, convert it to YYYY-MM-DD."
          }
        }
      }
    },
    "candidate_educations": {
      "type": "array",
      "items": {
        "type": "object",
        "properties": {
          "level": { "type": "string" },
          "major_name": { "type": "string" },
          "degree_name": { "type": "string" },
          "grade_value": { "type": "string" },
          "grade_metric": { "type": "string" },
          "education_name": { "type": "string" },
          "gpa": { "type": "number", "description": "Grade Point Average" },
          "effective_date_range": {
            "type": "string",
            "description": "YYYY-MM-DD to YYYY-MM-DD. If the initial date format is in human readable format, convert it to YYYY-MM-DD."
          }
        },
      }
    },
    "awards": {
      "type": "array",
      "items": {
        "type": "object",
        "properties": {
          "award_name": { "type": "string" },
          "date_received": { "type": "string", "format": "date" },
          "description": { "type": "string" }
        }
      }
    },

    "scholarships": {
      "type": "array",
      "items": {
        "type": "object",
        "properties": {
          "scholarship_name": { "type": "string" },
          "amount": { "type": "number" },
          "date_awarded": { "type": "string", "format": "date" },
          "description": { "type": "string" }
        }
      }
    }
    "candidate_skills": {
      "type": "array",
      "items": {
        "type": "object",
        "properties": {
          "skill_name": { "type": "string" },
          "skill_type": { "type": "string" }
        },
      }
    }
  }
}
--------
';
        $llmPrompt .= $parsedText;

            // Post request to OpenAI API
        $openAIData = [
            'model' => 'gpt-3.5-turbo-1106',
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant designed to output JSON'],
                ['role' => 'user', 'content' => $llmPrompt]
            ]
        ];

        $client = new \GuzzleHttp\Client(
            [
                'base_uri' => 'https://api.openai.com/v1/chat/completions',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->OPENAI_API_KEY,
//                    'OpenAI-Organization' => 'org-Yh8SxQaKtLM1bYMU2E6fzuCB'
                ],
            ]
        );

        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'json' => $openAIData,
        ]);

        $openAIResponse = $response->getBody()->getContents();



//        $openAIResponse = $this->makePostRequest($this->OPENAI_API_URL, json_encode($openAIData), false, [
//            'Authorization: Bearer ' . $this->OPENAI_API_KEY,
//            'OpenAI-Organization: org-Yh8SxQaKtLM1bYMU2E6fzuCB'
//        ]);

        $openAIResponseJson = json_decode($openAIResponse, true);
        return $openAIResponseJson['choices'][0]['message']['content'];
    }

    private function makePostRequest($url, $data, $isMultipart = false, $additionalHeaders = []) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $isMultipart ? $data : $data);
        $headers = $isMultipart ? [] : ['Content-Type: application/x-www-form-urlencoded'];
        $headers = array_merge($headers, $additionalHeaders);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
