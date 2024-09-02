<?php

namespace App\Helpers;


class AiReviewPrompt
{
    /**
    * @param array<mixed> $dataToAnalyze
    * @return array<mixed>
    */
    private static function prompTemplate(array $dataToAnalyze): array
    {
        return [
            "rules" => [
                "title" => "Use your judgement for a suitable short one-line summary for this listing.",
                "description" => [
                    "The description is in Bahasa Indonesia.",
                    "The description cannot contain personal information",
                    "Sometimes, phone numbers are hidden in the description as text (e.g., 'Kosong Delapan Satu Tiga' with the number spelled out). This is not allowed, and you should return an error if you find it."
                ],
                "propertyType" => "Use enum for property types: house, apartment, warehouse, etc.",
                "listingType" => "Use enum for listing types : sale, rent.",
                "address" => [
                    "In Indonesian, exact address often represented in street name + street number + house number.",
                    "The input pattern is {Jl/Jalan/Jln} {street name} {street number} {house number} [RT number RW number] [City]",
                    "Examples of exact address:
- Jln. Agung Barat 5 no. 8
- Jl. Sulaiman no. 11 RT 10 RW 15
- Jalan Wicaksono no. 12 RT 10, Surabaya",
                    "Examples of non-exact address:
- Jl. Wiyung Surabaya Barat (not exact because there is no house number)
- Jalan Sulaiman VI (not exact because there is no house number, only street number)",
                    "Note a common mistake is that 'nol jalan' is a specific term in Indonesian listing text, this does not indicate the number of the street or house.",
                    "Please check if the address is correct."
                ],
                "price" => "Extract the canonical integer representation of the price. Sometimes, the price is written as a string in the description (e.g., 1,7M or 1 Jt). This is acceptable. Just compare the number with the description, and if it does not match, return an error.",
                "rentPrice" => "Only fill this if the property is rented; otherwise, enter 0.",
                "lotSize" => "Extract as a number. Ignore the unit. Usually written as Luas tanah.",
                "buildingSize" => "Extract as a number. Ignore the unit. Usually written as Luas bangunan. Save only the number in the data (e.g., X m² will be saved as X in the data).",
                "carCount" => "Extract the number of parking spaces.",
                "bedroomCount" => "Extract the number of bedrooms. Usually written as X kamar tidur or X KT. ",
                "additionalBedroomCount" => "Extract the number of additional bedrooms. Usually written as X kamar tidur tambahan or X KT tambahan. ",
                "bathroomCount" => "Extract the number of bathrooms. Usually written as X kamar mandi or X KM. ",
                "additionalBathroomCount" => "Extract the number of additional bathrooms. Usually written as X kamar mandi tambahan or X KM tambahan. ",
                "floorCount" => "Extract as a number.",
                "electricPower" => "Extract as a number. Ignore the unit. Usually written as X watt. Save only the number in the data (X only).",
                "facing" => "Use enum for directions => north, east, south, west, etc.",
                "ownership" => "Use enum for ownership types => shm, hgb, strata, girik."
            ],
            "dataToAnalyze" => $dataToAnalyze,
            "outputFormat" => [
                "results" => [
                    "<please translate the output to indonesian language> and show the pointer which part of the description is wrong. Example => 'Jumlah kamar tidak sesuai di deskripsi tertera X kamar tetapi di data ada Y kamar.'"
                ]
            ]
        ];
    }

    /**
     * @param array<mixed> $reviewData
     */

    public static function generatePrompt(array $reviewData): string
    {
        $template = self::prompTemplate($reviewData);

        $mainItemToAnalyze = json_encode($template, JSON_PRETTY_PRINT);

        return $mainItemToAnalyze."\n"."\n".
        'Please learn the "rules" and use that as your guidance to analyze the "dataToAnalyze" value'."\n".
        'Give me the json results using format from "outputFormat".'."\n".
        'I will directly feed your output into a program, so please reply directly in JSON without any message for human.';
    }
}
