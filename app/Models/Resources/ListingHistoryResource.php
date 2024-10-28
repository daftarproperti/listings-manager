<?php

namespace App\Models\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $changes
 * @property string $listingId
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ListingHistoryResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @var array<string, string>
     */
    protected $fieldMap = [
        'address' => 'alamat',
        'adminNote' => 'catatan admin',
        'listingType' => 'tipe listing',
        'price' => 'harga',
        'status' => 'status',
        'bedroomCount' => 'kamar tidur',
        'additionalBedroomCount' => 'tambahan kamar tidur',
        'bathroomCount' => 'kamar mandi',
        'additionalBathroomCount' => 'tambahan kamar mandi',
        'lotSize' => 'luas tanah',
        'buildingSize' => 'luas bangunan',
        'facing' => 'menghadap',
        'ownership' => 'kepemilikan',
        'city' => 'kota',
        'electricPower' => 'daya listrik',
        'description' => 'deskripsi',
        'coordinate' => 'koordinat',
        'admin note' => 'catatan admin',
        'verifyStatus' => 'status verifikasi',
        'activeStatus' => 'status aktif',
        'cancellationNote' => 'status batal',
    ];

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $formattedChanges = [];

        $changes = json_decode($this->changes, true);
        if (isset($changes) && is_array($changes)) {
            foreach ($changes as $field => $change) {
                if (!isset($this->fieldMap[$field])) {
                    continue;
                }

                $humanReadableField = $this->fieldMap[$field];

                // Format the change into readable structure
                $formattedChanges[$humanReadableField] = [
                    'before' => $change['before'] ?? null,
                    'after' => $change['after'] ?? null,
                ];
            }
        }

        return [
            'listingId' => $this->listingId,
            'changes' => $formattedChanges,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
