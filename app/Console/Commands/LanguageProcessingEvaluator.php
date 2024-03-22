<?php

namespace App\Console\Commands;

use App\Helpers\Extractor;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class LanguageProcessingEvaluator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:language-processing-evaluator {case?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Utility to evaluate LLM and/or other prompts accuracy';

    /**
     * Execute the console command.
     */
    public function handle(Extractor $extractor): void
    {
        $totalAccuracy = 0;

        $dataFiles = $this->getMessageFiles('language-processing-evaluator');

        $case = $this->argument('case');
        if ($case) {
            $dataFiles = [
                storage_path('language-processing-evaluator') . "/$case.txt"
            ];
        }

        foreach ($dataFiles as $dataFile) {
            $this->line("Evaluating $dataFile");
            $rawMessage = file_get_contents($dataFile);
            if (!$rawMessage) {
                $this->error("Can not read raw messages. Aborting...");
                return;
            }

            $listings = $extractor->extractListingFromMessage($rawMessage);
            if (empty($listings)) {
                $this->error("Error extracting listings from raw message. Skipping...");
                continue;
            }

            // Accessing the expected generated listing indicated by json extension
            $jsonDataFile = substr($dataFile, 0, -4) . '.json';

            $jsonData = file_get_contents($jsonDataFile);
            if ($jsonData === false) {
                $this->error("Error reading JSON file: $jsonDataFile. Aborting...");
                return;
            }

            $expectedListings = json_decode($jsonData, true);
            if ($expectedListings === null) {
                $this->error("Error decoding JSON from file: $jsonDataFile. Aborting...");
                return;
            }

            $expectedListings = is_array($expectedListings) ? $expectedListings : [];
            if ($listings == [] || $expectedListings == []) {
                continue;
            }

            $accuracy = $this->calculateAccuracy($listings, $expectedListings);
            $totalAccuracy += $accuracy;

            $fileParts = explode('/', $dataFile);
            $fileName = end($fileParts);
            $this->info("Accuracy for $fileName is $accuracy%.\n");
        }

        $avgAccuracy = $totalAccuracy / count($dataFiles);
        $this->info("Avg accuracy is $avgAccuracy.\n");
    }

    /**
     * @param array<int,mixed> $listings
     * @param array<int,mixed> $expectedListings
     */
    private function calculateAccuracy($listings, $expectedListings): float
    {
        $fields = [
            'title', 'address', 'description', 'facing', 'ownership', 'city',
            'contact.name', 'contact.phoneNumber', 'contact.profilePictureURL',
            'contact.sourceURL', 'contact.provider',
            'coordinate.latitude', 'coordinate.longitude',
            'price', 'lotSize', 'buildingSize', 'carCount', 'bedroomCount',
            'bathroomCount', 'floorCount', 'electricPower'
        ];

        $iterationAccuracy = 0;

        // Iterate through smallest number of objects to avoid out of index
        $iteration = min(count($listings), count($expectedListings));
        for($idx = 0; $idx < $iteration; $idx++) {
            $totalAccuracy = 0;

            foreach ($fields as $field) {
                $listing = Arr::get($listings, $idx, []);
                $expectedListing = Arr::get($expectedListings, $idx, []);

                $listing = is_array($listing) ? $listing : [];
                $expectedListing = is_array($expectedListing) ? $expectedListing : [];
                if ($listing == [] || $expectedListing == []) {
                    continue;
                }
                $accuracy = $this->calculateFieldAccuracy($field, $listing, $expectedListing);
                $totalAccuracy += $accuracy;

                $this->line("Accuracy for field <$field> is $accuracy%.");
            }
            $iterationAccuracy += $totalAccuracy / count($fields);
        }

        return $iterationAccuracy / count($expectedListings);
    }

    /**
     * @param string $field
     * @param array<int,mixed> $listing
     * @param array<int,mixed> $expectedListing
     */
    private function calculateFieldAccuracy($field, $listing, $expectedListing): float
    {
        $guessedValue = Arr::get($listing, $field, '');
        $correctValue = Arr::get($expectedListing, $field, '');

        if (is_string($guessedValue) && is_string($correctValue)) {
            return $this->calculateStringAcc($guessedValue, $correctValue);
        }
        elseif (is_numeric($guessedValue) && is_numeric($correctValue)) {
            return $this->calculateNumberAcc($guessedValue, $correctValue);
        }

        return 0;
    }

    /**
     * @param string $guessedString
     * @param string $correctString
     */
    private function calculateStringAcc($guessedString, $correctString): float
    {
        if ($guessedString == '' && $correctString == '') {
            return 100;
        }

        similar_text($guessedString, $correctString, $percentage);
        return $percentage;
    }

    /**
     * @param mixed $guessedNumber
     * @param mixed $correctNumber
     */
    private function calculateNumberAcc($guessedNumber, $correctNumber): float
    {
        // Handle if correct number is 0
        if ($correctNumber == 0) {
            if ($guessedNumber == 0) {
                return 100;
            }

            return 0;
        }

        $numberAccuracy = (1 - abs($guessedNumber - $correctNumber) / $correctNumber) * 100;

        // Handle if accuracy exceed -100 or 100
        // This means that correct and guessed number are too far apart and hence is equal 0 percent accuracy
        if (abs($numberAccuracy) > 100) {
            return 0;
        }

        return $numberAccuracy;
    }

    /**
     * @param string $path
     * @return array<int,string>
     */
    private function getMessageFiles($path): array
    {
        $messageFiles = glob(storage_path($path) . '/*.txt');
        if (!$messageFiles) {
            return [];
        }

        return $messageFiles;
    }
}
