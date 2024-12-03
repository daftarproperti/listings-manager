<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />

    <title>Daftar Properti - Developer Guide</title>

    @vite('resources/css/app.css')
</head>

<body>
    <div class="min-h-screen mx-auto w-full font-inter">
        @include('partials.header')
        <main>
            <div class="max-w-6xl mx-auto px-4 pb-12 pt-4 bg-white min-h-96">
                <article class="prose">
<x-markdown>
# Daftar Properti Developer Guide

This document describes the basics of developing applications using Daftar Properti's open data. Some common types of applications include (but are not limited to):

* Property Search Engine/Portal
* Market Analysis and Real Estate Insights
* Urban Planning and Policy Making
* Financial Services and Mortgage Providers
* Property Valuation and Appraisal Services
* Real Estate Research and Academic Studies
* Retail and Business Location Planning

Applications are typically web or mobile applications, but are not limited to those options and can include any technological product, such as background analyses or desktop software.

Daftar Properti, as an open data platform, is currently in the Alpha stage. Therefore, applications may encounter some instability, but early adopters gain a head start advantage and receive close, hands-on support from the Daftar Properti team.

# Core Concepts

## How Daftar Properti Shares Its Data Publicly

Daftar Properti shares its open data on a blockchain network (EVM-based). The following events are published for developers to listen to:

* `NewListing`: Triggered when a listing is submitted, passes [verification](https://daftarproperti.org/checklist), and becomes available on the market for sale or rent.
* `ListingUpdated`: Triggered when a listing is updated.
* `ListingDeleted`: Triggered when a listing is no longer on the market.

Developers can subscribe to these events using any web3 library that supports EVM (Ethereum Virtual Machine).

Note: Upon reaching the Beta stage, we plan to publish more events related to sales closings and referral logging.

## Blockchain Details

Currently, during the Alpha stage, we use our own test network with the endpoint `ganache.daftarproperti.org`. During this phase, development is rapid, and we may frequently switch contract addresses. We publish the currently active contract address and ABI version at [https://daftarproperti.org/_blockchain](https://daftarproperti.org/_blockchain).

Early adopters also gain access to our Discord server, where changes are communicated promptly.

Once we reach GA (Generally Available), we will migrate to the live [Polygon PoS network](https://polygon.technology/polygon-pos).

The rationale behind using blockchain to share data instead of serving it via a traditional API is to:

* Ensure transparency and establish trust in the ecosystem, particularly regarding Referral Tracking's verifiable open logs.
* Leverage the robustness of an existing network, ensuring high availability for applications.
* Provide flexibility for developers to use their preferred technology stack.

# Synchronizing Data from Daftar Properti Blockchain

The first step in building an application with Daftar Properti is to synchronize the data from the blockchain. There are several ways an application can do this:

* Use our supported high-level library [`daftar-properti-sync`](https://github.com/daftarproperti/daftar-properti-sync). This is our recommended approach, as the library exposes high-level concepts so developers do not need to handle low-level blockchain operations. Official documentation and sample code are available in the [`dev-samples`](https://github.com/daftarproperti/dev-samples) repository for this approach.
* Use any low-level web3 library. This approach may be suitable for lightweight applications, such as this [Listings Stats](https://codepen.io/Sonny-Budiman/pen/XJrbroe). This requires an understanding of EVM ABI, which we publish [here](https://github.com/daftarproperti/listings-manager/tree/main/storage/blockchain).
* Use [The Graph](https://thegraph.com/) by defining a subgraph according to Daftar Properti's ABI, then using its built-in GraphQL API.

Once the data is synchronized with the application's own database, developers have flexibility regarding what to build next.

# Revealing Contact Numbers

Publicly available listings data do not contain plain-text contact numbers. The reasons for this are:

* The public listings data should not be used for "phone number" farming, which might lead to misuse, such as spamming.
* Referral Tracking requires a way to indicate whether a user is interested in connecting with a listing registrant. Therefore, the act of revealing a phone number can serve as this signal.
* Phone numbers are not directly relevant when deciding whether a property is suitable for a potential buyer or renter.

Contact numbers can be revealed using Daftar Properti's Contact Reveal Protocol with the following steps:

1. The marketer's app displays Daftar Properti's Reveal witness endpoint `https://reveal.daftarproperti.org/witness?listingId=xx&referrerId=yy`, where `listingId` is the Daftar Properti Listing ID and `referrerId` is the application's domain name.
2. Daftar Properti's Reveal witness endpoint verifies that a specific user, identified by their phone number, is interested in contacting a particular listing.
3. The proof of this witnessed event is then returned to the app as a Receipt, containing information about the potential buyer, the listing, and the referrer.
4. The Receipt does not contain the plain-text phone number of the potential buyer, as it will be inserted into the public blockchain and privacy should be protected. However, Referral Tracking can verify whether a particular phone number matches the one in this Receipt by checking the cryptographic hash.
5. The application can then send the Receipt to the decrypt endpoint (`https://reveal.daftarproperti.org/decrypt`) to get the contact number decrypted. The decrypt endpoint ensures the cryptographic signature of the witness Receipt and verifies its recency, ensuring trust among participating applications.
6. Once the application retrieves the decrypted phone number from the decrypt endpoint, it can display the listing contact number as needed.

If the application participates in the Referral Tracking program:

* During the Alpha stage, the application should store the Receipt in its own database for claiming referrals when closings are published by Daftar Properti.
* At the Beta and GA stages, the application should log the Receipt on the blockchain Referral Tracking log, along with the payment of the Referral Tracking fee.

# Participating in Referral Tracking

Applications that wish to participate in Referral Tracking must pay a Referral Tracking fee for each entry of the reveal log, with the following details:

* The fee for each entry is currently set at 1/500 of the projected commission. For example, if a listing is priced at IDR 1 billion, the projected commission is IDR 5 million, and the referral tracking fee is IDR 10,000 per entry.
* Payments are not made upfront but are tracked within the blockchain contract as a credit. When the application receives a commission due to a successful referral, the actual commission will be subtracted by the credit accumulated by the application.
* Aside from subtracting from commission, the credit does not represent a liability for the application to pay back to Daftar Properti.

# Other Monetization Strategies

While applications are facilitated with Referral Tracking, Daftar Properti does not restrict other monetization strategies or enforce participation in Referral Tracking. Some applications may have their own monetization strategies or no monetization at all due to other aligned incentives, and these are permitted use cases for Daftar Properti's open data.

</x-markdown>

                </article>
            </div>
        </main>
        @include('partials.footer')
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>
