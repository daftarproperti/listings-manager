import PublicLayout from '@/Layouts/PublicLayout';
import Slider from 'react-slick';
import { Listing, PageProps } from '@/types';

import 'slick-carousel/slick/slick.css';
import 'slick-carousel/slick/slick-theme.css';

export default function PublicListing({ listing }: PageProps<{ listing: Listing }>) {
    const slickSettings = {
        dots: true,
        infinite: true,
        speed: 500,
        slidesToShow: 1,
        slidesToScroll: 1,
    };

    return (
        <PublicLayout>
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div className="col-span-2 bg-gray-100 p-4 rounded-lg shadow-md">
                                    {listing.pictureUrls && listing.pictureUrls.length > 0 && (
                                        <Slider {...slickSettings}>
                                            {listing.pictureUrls.map((url, index) => (
                                                <div key={index}>
                                                    <img src={url} alt={`Slide ${index}`} className="w-full" />
                                                </div>
                                            ))}
                                        </Slider>
                                    )}

                                    <div className="mt-4">
                                        <h3 className="text-xl font-semibold mb-2">{listing.title}</h3>
                                        <div className="space-y-2">
                                            <div className="flex items-center mb-2">
                                                <span className="font-semibold w-24">Description:</span>
                                                <span>{listing.description || 'N/A'}</span>
                                            </div>
                                            <div className="flex items-center mb-2">
                                                <span className="font-semibold w-24">City:</span>
                                                <span>{listing.city || 'N/A'}</span>
                                            </div>
                                            <div className="flex items-center mb-2">
                                                <span className="font-semibold w-24">Address:</span>
                                                <span>{listing.address || 'N/A'}</span>
                                            </div>
                                            <div className="flex items-center mb-2">
                                                <span className="font-semibold w-24">Price:</span>
                                                <span>{listing.price ? `Rp ${listing.price.toLocaleString()}` : 'N/A'}</span>
                                            </div>
                                            <div className="flex items-center mb-2">
                                                <span className="font-semibold w-24">Lot Size:</span>
                                                <span>{listing.lotSize || 'N/A'} m2</span>
                                            </div>
                                            <div className="flex items-center mb-2">
                                                <span className="font-semibold w-24">Building Size:</span>
                                                <span>{listing.address || 'N/A'} m2</span>
                                            </div>
                                            <div className="flex items-center mb-2">
                                                <span className="font-semibold w-24">Bedroom:</span>
                                                <span>{listing.bedroomCount || 'N/A'}</span>
                                            </div>
                                            <div className="flex items-center mb-2">
                                                <span className="font-semibold w-24">Bathroom:</span>
                                                <span>{listing.bathroomCount || 'N/A'}</span>
                                            </div>
                                            <div className="flex items-center mb-2">
                                                <span className="font-semibold w-24">Facing:</span>
                                                <span>{listing.facing || 'N/A'}</span>
                                            </div>
                                            <div className="flex items-center mb-2">
                                                <span className="font-semibold w-24">Ownership:</span>
                                                <span>{listing.ownership || 'N/A'}</span>
                                            </div>
                                            <div className="flex items-center mb-2">
                                                <span className="font-semibold w-24">Floor Count:</span>
                                                <span>{listing.floorCount || 'N/A'}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </PublicLayout>
    );
}