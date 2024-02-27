import { Link } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';
import { Agent, Listing, PageProps } from '@/types';

export default function PublicAgent({ agent, listings }: PageProps<{ agent: Agent, listings: Array<Listing>}>) {
    const { first_name, last_name, profile } = agent;

    return (
        <PublicLayout>
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="flex items-center">
                                <img src={profile.picture} alt={`${first_name} ${last_name}`} className="w-16 h-16 rounded-full mr-4" />
                                <div>
                                    <h2 className="text-xl font-bold">{`${first_name} ${last_name}`}</h2>
                                    <div className="grid grid-cols-2 gap-4 mt-2">
                                        <div className="font-semibold">Name:</div>
                                        <div>{profile.name}</div>
                                        <div className="font-semibold">Phone Number:</div>
                                        <div>{profile.phoneNumber}</div>
                                        <div className="font-semibold">City:</div>
                                        <div>{profile.city}</div>
                                        <div className="font-semibold">Company:</div>
                                        <div>{profile.company}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
                <h2 className="text-2xl font-bold mb-4">Listings</h2>
            </div>

            {listings.map((listing, index) => (
                <div key={index} className="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
                    <Link href={`/public/listing/${listing.id}`}>
                        <a>
                            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg cursor-pointer">
                                <div className="p-6 text-gray-900">
                                    <div className="flex items-center">
                                        {listing.pictureUrls && listing.pictureUrls.length > 0 && (
                                            <img src={listing.pictureUrls[0]} alt={listing.title} className="w-16 h-16 rounded-full mr-4" />
                                        )}
                                        <div>
                                            <h2 className="text-xl font-bold">{listing.title}</h2>
                                            <p className="text-gray-600">{listing.description}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </Link>
                </div>
            ))}
        </PublicLayout>
    )
}