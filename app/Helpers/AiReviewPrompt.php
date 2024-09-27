<?php

namespace App\Helpers;

class AiReviewPrompt
{
    /**
     * @return string
     */
    public static function validationPrompt(): string
    {
        $prompt = <<<EOD
I need you to do more validation of the free-text description with this rules:

- Description cannot contain personal information.
- Sometimes, phone numbers are hidden in the description as text (e.g., 'Kosong Delapan Satu Tiga' with the number
   spelled out). This is not allowed, and you should return an error if you find it.

title: Use your judgement for a suitable short one-line summary for this listing.

propertyType: Use enum for property types: house, apartment, warehouse, etc.

listingType: Use enum for listing types: sale, rent.

address:
- In Indonesian, exact address often represented in street name + street number + house number.
- The input pattern is {Jl/Jalan/Jln} {street name} {street number} {house number} [RT number RW number] [City].
- Examples of exact address:
  - Jln. Agung Barat 5 no. 8
  - Jl. Sulaiman no. 11 RT 10 RW 15
  - Jalan Wicaksono no. 12 RT 10, Surabaya
- Examples of non-exact address:
  - Jl. Wiyung Surabaya Barat (not exact because there is no house number)
  - Jalan Sulaiman VI (not exact because there is no house number, only street number)
- Note a common mistake is that 'nol jalan' is a specific term in Indonesian listing text, this does not indicate the
  number of the street or house.
- Please check if the address is correct.

price: In Indonesian numerical notation, \"M\" indeed represents \"Milyar,\" which is equivalent to \"billion\" in
    English.
    Sometimes the value is in decimal format.
    The input pattern is "{number} {notation}"
    Examples:
    - 5 Milyar or 5 Miliar or 5M is 5000000000
    - 1.7 M or 1,7 M is 1700000000
    - 1.27 M or 1,27 M is 1270000000
    - 2 Triliun or 2T is 2000000000000
    - 2.3 Triliun or 2.3T is 2300000000000
    - 2 Juta or 2JT is 2000000
    - 40.3 Juta or 40.3JT is 40300000
    Please pay attention to the price format especialy: 1.7 M or 1,7 M is not 1.7 but 1700000000
    Remember that in Indonesian locale, sometimes comma is used for decimal point, e.g. 1,050 Milyar is 1.05 billion and
    not 1050 billion
    Please extract this to be the canonical integer number representation, not containing letter suffixes anymore

rentPrice: Only fill this if the property is rented; otherwise, enter 0.

lotSize: Extract as a number. Ignore the unit. Usually written as Luas tanah.

buildingSize: Extract as a number. Ignore the unit. Usually written as Luas bangunan. Save only the number in the data
(e.g., X m² will be saved as X in the data).

carCount: Extract the number of parking spaces.

bedroomCount: Extract the number of bedrooms. Usually written as X kamar tidur or X KT.

additionalBedroomCount: Extract the number of additional bedrooms. Usually written as X kamar tidur tambahan or X KT
tambahan.

bathroomCount: Extract the number of bathrooms. Usually written as X kamar mandi or X KM.

additionalBathroomCount: Extract the number of additional bathrooms. Usually written as X kamar mandi tambahan or X KM
tambahan.

floorCount: Extract as a number.

electricPower: Extract as a number. Ignore the unit. Usually written as X watt. Save only the number in the data
(X only).

facing: Use enum for directions: north, east, south, west, etc.

ownership: Use enum for ownership types: shm, hgb, strata, girik.

Each information in the free-text description should be align with the JSON data.

Ignore if the description is missing some of the structured data.

Show me which not matching fields only.

EOD;
        return $prompt;
    }

    /**
     * @param array<mixed> $dataToReview
     * @param string $description
     * @return string
     */

    public static function generatePrompt(array $dataToReview, string $description): string
    {

        $prompt = 'I need to compare a JSON structured data and its corresponding free-text description.' . "\n" .
            'I need you to highlight inaccuracies in the text description that does not match the JSON data.' . "\n" .
            'It is okay for the description to be missing some of the structured data information and vice versa.'
            . "\n" .
            'I need you to reply with the following format in Bahasa Indonesia, for example:' . "\n" .
            '{"results":["Jumlah kamar tidak sesuai di deskripsi tertera X kamar tetapi di data ada Y kamar",' .
            '"Jumlah kamar mandi tidak sesuai di deskripsi tertera X kamar mandi tetapi di data ada Y kamar mandi"]}'
            . "\n" .
            'Here is the JSON data (we call it as detail listing) to analyze:' . "\n" .
            json_encode($dataToReview, JSON_PRETTY_PRINT) .
            "\n\n" .
            'Here is the free-text description:' . "\n" .
            $description;

        return $prompt;
    }
}
