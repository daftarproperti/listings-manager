<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />

    <title>Daftar Properti - Whitepaper</title>

    @vite('resources/css/app.css')
</head>

<body>
    <div class="min-h-screen mx-auto w-full font-inter">
        @include('partials.header')
        <main>
            <div class="max-w-6xl mx-auto px-4 py-12 bg-white min-h-96">
                <article class="prose">
<x-markdown>
# A Blockchain-based Real Estate Listing and Referral System for Increased Market Efficiency and Transparency

## Abstract

This whitepaper proposes a blockchain-based solution to address the inefficiencies and lack of transparency in the real estate market, particularly in Indonesia and other countries with similar challenges. By leveraging the immutability and decentralized nature of blockchain technology, we introduce a system that aims to benefit all stakeholders, including potential buyers, property owners, agents/brokers, and the general public. The proposed system consists of three main components: a Listing Registry, Marketers, and a Referral Tracking System. The Listing Registry maintains quality and moderated listings, ensuring uniqueness, single representation, public availability, and open reward agreements for referrers. Marketers actively seek matching buyers/renters and are incentivized by the reward agreements stated in the Listing Registry. The Referral Tracking System provides a transparent, verifiable, and privacy-protected mechanism for determining the involvement of Marketers in successful transactions. By implementing this system, we aim to increase market efficiency, transparency, and provide valuable public information to strengthen the real estate industry.

## 1. Introduction

The real estate market in Indonesia and many other countries often suffers from inefficiencies due to a lack of transparency and misaligned incentives among stakeholders. Potential buyers struggle to find accurate and comprehensive information about properties, while property owners face difficulties in accurately valuing their properties and experience long waiting periods before closing deals. Agents and brokers, who play a crucial role in facilitating transactions, are often unable to serve both owners and buyers effectively due to the current market mechanics.

To address these challenges, we propose a blockchain-based system that aims to create a more efficient and transparent real estate ecosystem. The proposed system consists of three main components: a Listing Registry, Marketers, and a Referral Tracking System. By leveraging the immutability and decentralized nature of blockchain technology, we aim to provide a solution that benefits all stakeholders and strengthens the real estate industry as a whole.

## 2. System Components

### 2.1 Listing Registry

The Listing Registry serves as a central component of the proposed system, maintaining quality and moderated listings shared within the ecosystem. Unlike existing property marketplaces, the Listing Registry's role is limited to ensuring the following characteristics of the listings:

1. Uniqueness: There are no duplicate listings referring to the same property.
2. Single representation: Each listing is represented by a single entity, which may be the owner or an agent.
3. Public availability: No entity monopolizes the listing information.
4. Open reward agreements: Each listing contains a clear agreement about the reward for the referrer of a future closing (sale/rent), motivating marketers to find the right buyers/renters.

To achieve these goals, we propose the use of blockchain technology to store and manage the listing information. The blockchain will contain the following data:

- List of Listings
- Basic information for each Listing (e.g., location/city) and an off-chain link to the detailed specification
- Encrypted contact number of the representative in the detailed specification, with the decryption key held by the Listing Manager
- Emit events for important milestones (e.g., "new Listing added," "Listing updated," "Listing becoming verified," "Listing rejected," "Listing removed," "Listing closing")
- Unlock Service for each Listing, an off-chain service to unlock the encrypted contact number (elaborated in the Referral Tracking section)

New Listing submissions and information updates can be performed by any Listing Manager (third-party) that pays the blockchain network cost. The Listing Registry controls which listings are verified and should be active at any given time.

The choice of blockchain technology over traditional databases and APIs offers several benefits that support our goals:

1. Public availability and immutability: Listings are publicly available and will always remain so, preventing the Listing Registry from monopolizing the data.
2. High reliability: Blockchain's decentralized nature ensures high reliability and low downtime, which is crucial for the Listing Registry to serve as the "operating system" of the industry.
3. Easy data synchronization: Marketers and other participants can easily sync data with the Listing Registry due to its availability on the blockchain.

### 2.2 Marketers

Marketers are entities that actively seek matching buyers/renters for the listings in the Listing Registry. They are incentivized by the reward agreements explicitly stated by the Listing representatives in the Listing Registry. We propose a standard compensation of 0.5% for sales and 2% for rentals.

In the proposed system, Marketers' involvement is determined by their role as referrers, maintaining high transparency for potential buyers. This is achieved through the Referral Tracking System, which is transparent, verifiable, and privacy-protected.

### 2.3 Referral Tracking System

The Referral Tracking System consists of two main processes: Marketer registration and the Verifiable Transparent Log for referral tracking.

#### 2.3.1 Marketer Registration

1. A Marketer registers themselves to the blockchain contract, providing the following information:
  - Name: Agent/broker name if individual, or company name if a business entity
  - Contact Number: Publicly available business phone number
  - Public key: Used for cryptographic purposes, such as verification of signed messages
2. The Marketer pays only the blockchain network cost and no registration cost.

#### 2.3.2 Verifiable Transparent Log for Referral Tracking

1. The Marketer displays a Listing sourced from the Listing Registry to a potential buyer/renter.
2. The contact number of the Listing is not shown, but the potential buyer can unlock it in real-time if they are interested in proceeding with the interaction.
3. When the potential buyer clicks "Unlock Contact," they are directed to the Listing Registry's interface, which authenticates their phone number and presents a confirmation that they are unlocking the contact number through the mentioned Marketer.
4. Upon confirmation, the Listing Registry returns a signed log that the Marketer posts to the blockchain, containing:
  - SHA256 hash of the Marketer's random number + phone number ID (assigned by Listing Registry as a blinding factor) + phone number
  - Marketer ID
  - Listing ID
  - Unlocked contact number, encrypted using the Marketer's public key
  - Timestamp
5. The Marketer posts this log to the blockchain, ensuring their involvement is recorded and cannot be cheated.
6. The Marketer displays the decrypted contact number to the potential buyer to continue interaction with the Listing representative.

#### 2.3.3 Closing Process

1. Closing is reported by either the Listing representative, the buyer/renter, or a third party.
2. Upon confirmation by the Listing Registry, the closing is logged to the blockchain, containing:
  - Hash of phone number ID + phone number as the buyer identity
  - Closing amount (publicly available)
3. An event is emitted, allowing Marketers to check if they were involved in the referral.
4. The Marketer checks their log for a match with their Unlock log, which only they can confirm due to the random number attached to the Unlock log.
5. If a match is found, the Marketer posts a Claim to the blockchain, mentioning:
  - Unlock log ID
  - Random number prefix (to verify the hash with the phone number ID + phone number of the potential buyer matches the Closing log's buyer/renter)
6. After a given time, all Claims are cryptographically confirmed to be true without revealing private information.
7. The Listing Registry sends reward obligation documents to the Listing representative and the Claimers.

## 3. Conclusion

The proposed blockchain-based real estate listing and referral system aims to address the inefficiencies and lack of transparency in the real estate market. By leveraging the immutability and decentralized nature of blockchain technology, the system incentivizes each participant to perform their roles in the most effective manner while maintaining transparency, ensuring efficiency, and providing useful public information. Over time, this system has the potential to strengthen the real estate industry and benefit all stakeholders involved.
</x-markdown>

                </article>
            </div>
        </main>
        @include('partials.footer')
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>
