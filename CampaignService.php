<?php

/**
 * Campaign service class to manage blog posts and products.
 *
 * @author Robert Kovacs
 */
class CampaignService
{
    /**
     * Collection of all campaigns.
     * @var array
     */
    public array $campaigns;

    /**
     * Collection of campaigns to post.
     * @var array
     */
    public array $campaignsToPost;

    /**
     * Construct method to initiate campaign arrays.
     */
    public function __construct()
    {
        $this->campaigns = array();
        $this->campaignsToPost = array();
    }

    /**
     * Method to create campaign and generate unique identifier for individual campaigns.
     *
     * @param string $name Name of the product or the post.
     * @param string $entityType Type of the associated entity, e.g. Product|Post
     * @param array $entityDetails Associated array with blog post or product information.
     * @param string $startDate Start date of the campaign.
     * @param string $endDate End date of the campaign.
     *
     * @return string|false The unique identifier of the campaign or false.
     */
    public function createCampaign(string $name, string $entityType, array $entityDetails, string $startDate, string $endDate): string|false
    {
        $campaignId = $this->getCampaignId($name, $entityType, $startDate, $endDate);

        if (!$this->isCampaignExists($campaignId)) {
            // Create the campaign array
            $campaign = array(
                'id' => $campaignId,
                'name' => $name,
                'type' => $entityType,
                'entity' => $entityDetails,
                'start_date' => $startDate,
                'end_date' => $endDate
            );

            // Append single campaign to the campaigns array
            $this->campaigns[$campaignId] = $campaign;

            return $campaignId;
        }

        return false;
    }

    /**
     * Method to update the campaign details, selected by the campaign ID
     *
     * @param string $campaignId Unique identifier of associated campaign.
     * @param string $name Name of the product or the post.
     * @param string $entityType Type of the associated entity.
     * @param array $entityDetails Associated array with blog post or product information.
     * @param string $startDate Start date of the campaign.
     * @param string $endDate End date of the campaign.
     *
     * @return bool True or false.
     */
    public function updateCampaign(string $campaignId, string $name, string $entityType, array $entityDetails, string $startDate, string $endDate): bool
    {
        if ($this->isCampaignExists($campaignId)) {
            $campaign = $this->campaigns[$campaignId];

            $campaign['name'] = $name;
            $campaign['type'] = $entityType;
            $campaign['entity'] = $entityDetails;
            $campaign['start_date'] = $startDate;
            $campaign['end_date'] = $endDate;

            // Store the updated campaign
            $this->campaigns[$campaignId] = $campaign;

            return true;
        }

        return false;
    }

    /**
     * Method create campaign and generate unique identifier for individual campaigns.
     *
     * @param string $name Name of the product or the post.
     * @param string $type Type of the associated entity.
     * @param string $startDate Start date of the campaign.
     * @param string $endDate End date of the campaign.
     *
     * @return string The unique identifier of the campaign.
     */
    public function getCampaignId(string $name, string $type, string $startDate, string $endDate): string
    {
        return 'bioTech_' . md5($name . $type . $startDate . $endDate);
    }

    /**
     * Method to set an array of valid entities that can be posted.
     *
     * @return void
     */
    public function getCampaignsToPost(): void
    {
        date_default_timezone_set('UTC');
        $today = strtotime(date("Y-m-d"));

        foreach ($this->campaigns as $campaign) {
            $id = $campaign['id'];
            $type = $campaign['type'];
            $startDate = strtotime($campaign['start_date']);
            $endDate = strtotime($campaign['end_date']);

            if ($startDate > $today) {
                continue;
            }

            if ($endDate <= $today) {
                continue;
            }

            if ($type === 'Post') {
                if ($this->isWeekend()) {
                    continue;
                }
            }

            $this->campaignsToPost[$id] = $campaign;
        }
    }

    /**
     * Method to validate if the actual campaign already exists or not.
     *
     * @param string $campaignId Unique identifier of associated campaign.
     *
     * @return bool True or false.
     */
    public function isCampaignExists(string $campaignId): bool
    {
        if (!array_key_exists($campaignId, $this->campaigns)) {

            return false;
        }

        return true;
    }

    /**
     * Method to check if the blog post is not published on a weekend.
     *
     * @return bool True or false.
     */
    private function isWeekend(): bool
    {
        date_default_timezone_set('UTC');

        $today = strtotime(date("Y-m-d"));
        $dayOfWeek = date('N', $today);

        if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {

            return false;
        }

        return true;
    }
}

// Usage example:

// Create a new blog post and product instances of CampaignService
$campaign = new CampaignService();

$postId = $campaign->createCampaign(
    'New BioTechUSA Blog Post',
    'Post',
    [
        'entity_title' => 'Title of the new BioTech USA Blog Post',
        'entity_description' => 'The article related to the posted BioTechUSA blog entry.',
        'entity_image' => 'https://url.to.the.blog.post.image.com',
        'entity_price' => null
    ],
    '2023-06-01',
    '2023-06-30'
);

$productId = $campaign->createCampaign(
    'New BioTechUSA Product',
    'Product',
    [
        'entity_title' => 'Name of the Product',
        'entity_description' => 'The description related to the product',
        'entity_image' => 'https://url.to.the.product.image.com',
        'entity_price' => 1200
    ],
    '2023-06-01',
    '2023-06-30'
);

// Update campaign details
$updatedDetails = [
    'entity_title' => 'Updated of the Product',
    'entity_description' => 'The updated description related to the product',
    'entity_image' => 'https://url.to.the.new.product.image.com',
    'entity_price' => 1300
];

$campaign->updateCampaign(
    $productId,
    'New BioTechUSA Product',
    'Product',
    $updatedDetails,
    '2023-07-01',
    '2023-07-31'
);

// Get all eligible campaigns to post
$campaign->getCampaignsToPost();

print('<pre>');
print_r($campaign->campaigns);
print_r($campaign->campaignsToPost);
