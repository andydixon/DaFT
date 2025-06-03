<?php

namespace DaFT\Dataformats;

/**
 * WebexAvailabilityHandler
 *
 * This class connects to the WebEx API and fetches the availability status
 * of users based on their email addresses. It maps the textual status
 * into numeric values for easier handling.
 * The config.ini needs to have the type as WebexAvailability and the config should comprise of:
 * botToken = <bot Token from developer.webex.com>
 * query = <comma-separated list of email addresses of webex users> 
 * identifier = <identifier> - standard identifier for the scrape
 */
class WebexAvailabilityHandler
{
    /**
     * @var array Configuration including query emails and botToken.
     */
    protected $config;

    /**
     * Status to numeric state mapping.
     *
     * @var array
     */
    protected $statusMap = [
        'OUTOFOFFICE' => 1,
        'ACTIVE' => 2,
        'DONOTDISTURB' => -1,
        'INACTIVE' => -10,
        'CALL' => 10,
        'MEETING' => 11,
        'PRESENTING' => 12,
        'UNKNOWN' => 0,
        'PENDING' => 0,
    ];

    /**
     * Constructor
     *
     * @param array $config Configuration array with 'query' and 'botToken' keys
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Handle the request to retrieve WebEx availability status for users.
     *
     * @return array Returns an array of users with their email and numeric state
     */
    public function handle()
    {
        $emails = array_map('trim', explode(',', $this->config['query']));
        $token = $this->config['botToken'];
        $results = [];

        foreach ($emails as $email) {
            $statusText = $this->getUserStatusFromWebex($email, $token);
            $statusKey = strtoupper($statusText);
            $numericState = $this->statusMap[$statusKey] ?? 0;
            $results[] = [
                'user' => $email,
                'state' => $numericState,
            ];
        }

        return $results;
    }

    /**
     * Connect to WebEx API and retrieve the availability status for a user.
     *
     * @param string $email The email of the user
     * @param string $token The WebEx bot token for authentication
     * @return string The status text returned by WebEx API, or 'UNKNOWN' on failure
     */
    protected function getUserStatusFromWebex(string $email, string $token): string
    {
        $url = 'https://webexapis.com/v1/people/status?email=' . urlencode($email);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            return strtoupper($data['status']) ?? 'UNKNOWN';
        }

        return 'UNKNOWN';
    }
}
