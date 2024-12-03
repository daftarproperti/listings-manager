<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />

    <title>Daftar Properti - Essay</title>

    @vite('resources/css/app.css')
</head>

<body>
    <div class="min-h-screen mx-auto w-full font-inter">
        @include('partials.header')
        <main>
            <div class="max-w-6xl mx-auto px-4 pb-12 pt-4 bg-white min-h-96">
                <article class="prose">
<x-markdown>
# Classifieds Model of Property Listings is Obsolete

In many property markets, especially in Indonesia, there is a pressing need for a better solution to spread information about real estate properties for sale. Currently, the dominant approach relies on "advertisement boards" (often called classifieds), where property owners or agents pay to post listings. While this method helped in the past, when information moved slowly before the digital age, it now creates more problems than solutions.

One key issue is that this system discourages transparency. Agents often hide the exact address or location of a property due to a "prisoner's dilemma" scenario: if one agent provides complete information, other agents can easily copy it and duplicate the listing, taking away their competitive edge. As a result, potential buyers face a frustrating experience with advertisements that are often duplicated and lack critical details like precise location—ironically, one of the most important factors in deciding on a property.

The problem is compounded by the incentive model of the classifieds providers. These platforms profit from high volumes of advertisements, regardless of whether they are duplicated or transparent. They also keep listings closed to their own systems, which stifles technological innovation that could otherwise improve the clarity and accessibility of listings. Ultimately, this outdated approach leads to inefficiencies: property owners fail to reach potential buyers, agents struggle to compete effectively, and buyers find it challenging to get a clear view of the market. These inefficiencies result in properties lingering on the market for months or even years before finding a buyer.

## Proposed Solution

The industry needs reform, and we propose an alternative: an open, shared registry of properties on the market. Unlike an advertisement board, this registry would only accept verified listings, ensuring that each property is transparent, with details like the exact address, coordinates, and unique identifying photos. There would be no duplication, as the first registrant would "own" the listing for a specific property. While the technical details of this system are beyond the scope of this essay, modern technology—such as integration with AI tools like Large Language Models—could make the verification process efficient and cost-effective.

This open registry would be fundamentally different from existing ad boards because it would not keep data confined to a single platform. Instead, it would encourage other businesses to access and use the high-quality data to build various services. For example, a company could develop a search engine to help buyers find properties with complete transparency, or create market analysis tools for property developers, city planners, or retail site selectors. Modern technology makes it feasible to create such a registry and make it accessible for these diverse applications.

To ensure the sustainability of this registry, we propose an incentive model that rewards stakeholders fairly. The key incentive is for marketers to earn a commission for successful referrals. For instance, the property owner or agent must agree upfront to pay a 0.5% commission to the marketer who successfully refers a buyer, as determined by the registry. Importantly, the registry itself would not earn commission, allowing it to focus solely on being a neutral and fair arbiter for determining successful referrals.

To foster trust, we would use an open, verifiable referral tracking log built on blockchain technology. This transparency would assure all parties that the system is fair and resistant to manipulation. Instead of charging marketers for access to the data, the registry would charge them for logging each referral, thereby discouraging spammy tactics like excessive clicks and providing a revenue model for the registry. Additionally, the registry would be open-source to encourage collaboration rather than competition among different registries.

For this referral tracking to work effectively, registrants must agree to report successful closings back to the registry, so the system can determine who referred the buyer. While this level of cooperation might seem challenging, a small-scale version of this system has already shown that registrants are willing to comply, largely because the registry protects their transparency, doesn't charge for visibility, and operates openly—unlike traditional ad boards.

Our proposed system benefits not only property owners, agents, and buyers but also buyer's agents. It offers a new way for buyer's agents to provide value without compromising transparency. Currently, buyer's agents often hide details and insist on meeting at properties in person because they fear not being fairly compensated for their efforts. With this system, buyer's agents can confidently offer their clients full transparency, knowing they will be rewarded fairly.

When fully implemented, this new registry model would allow property owners to connect with potential buyers more effectively, create healthy competition among agents, and provide buyers with the information they need to make informed decisions. Moreover, the open data provided by this registry could have far-reaching positive effects beyond the real estate industry, aiding decision-making in urban planning, business development, and more.

## Appendix
The proposed blockchain system is detailed in [this whitepaper](/whitepaper).

*This essay has been assisted by a Large Language Model (LLM) to enhance clarity, structure, and flow.
The original version can be accessed [here](/docs/essay.txt).*

</x-markdown>

                </article>
            </div>
        </main>
        @include('partials.footer')
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>
