<?php

namespace App\Helpers;

class ClassificationKeyword
{
    /**
     * @return array<string>
     */
    public static function PropertyKeywords(): array
    {
        return [
            'rumah',
            'house',
            'listing',
            'apartemen',
            'apartement',
            'bedroom',
            'kamar tidur',
            'km tidur',
            'bathroom',
            'kamar mandi',
            'km mandi',
            'price',
            'harga',
            'garasi',
            'carport',
            'm2',
            'listrik',
            'watt',
            'luas',
            'luas tanah',
            'luas bangunan',
            'lebar muka',
            'sertifikat',
            'shm',
            'hgb',
            'imb',
            'fasilitas',
            'dijual',
            'disewakan',
            'alamat',
            'kamar',
            '2 lantai',
            'lantai',
            'jumlah lantai',
            'dekat kota',
            'asri',
            'menghadap',
            'hadap',
            'alamat',
        ];
    }

    /**
     * @return array<string>
     */
    public static function BuyerRequestKeywords(): array
    {
        return [
            'dicari',
            'cari',
            'huni',
            'permintaan',
            'buyer',
            'renter',
            'request',
            'budget'
        ];
    }
}
